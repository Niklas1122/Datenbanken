<?php
// Niklas

// Klasse repräsentiert die Auswertung eines einzelnen Fahrers
class FahrerAuswertung
{
    // Private Eigenschaften – von außen nur über Getter/Setter zugänglich
    private $mitarbeiterID; // eindeutige ID des Fahrers
    private $name;          // Name des Fahrers
    private $trainingsziel; // Filter: nur dieses Trainingsziel auswerten (leer = alle)
    private $von;           // Filter: Startdatum (leer = kein Limit)
    private $bis;           // Filter: Enddatum (leer = kein Limit)
    private $monate = [];   // Ergebnis-Array: Monat => Statistik-Werte

    // Konstruktor befüllt alle Eigenschaften beim Erstellen des Objekts
    public function __construct($mitarbeiterID, $name, $trainingsziel, $von, $bis)
    {
        $this->mitarbeiterID = $mitarbeiterID;
        $this->name          = $name;
        $this->trainingsziel = $trainingsziel;
        $this->von           = $von;
        $this->bis           = $bis;
    }

    // Getter und Setter für alle Eigenschaften (Kapselung: direkter Zugriff auf private Vars ist gesperrt)
    public function getMitarbeiterID() { return $this->mitarbeiterID; }
    public function setMitarbeiterID($id) { $this->mitarbeiterID = $id; }
    public function getName() { return $this->name; }
    public function setName($n) { $this->name = $n; }
    public function getTrainingsziel() { return $this->trainingsziel; }
    public function setTrainingsziel($z) { $this->trainingsziel = $z; }
    public function getVon() { return $this->von; }
    public function setVon($v) { $this->von = $v; }
    public function getBis() { return $this->bis; }
    public function setBis($b) { $this->bis = $b; }

    // Lädt Trainingsdaten aus der DB und berechnet Statistiken pro Monat
    public function berechne($connection)
    {
        // Eingabe absichern, um SQL-Injection zu verhindern
        $id  = mysqli_real_escape_string($connection, $this->mitarbeiterID);

        // DATE_FORMAT gruppiert das Datum auf Jahr-Monat, z. B. "2024-03"
        $sql = "SELECT DATE_FORMAT(Datum, '%Y-%m') AS Monat, Kilometer
                FROM TRAINING WHERE MitarbeiterID = '$id'";

        // Filter werden nur angehängt, wenn sie gesetzt sind
        if ($this->trainingsziel !== '') {
            $ziel = mysqli_real_escape_string($connection, $this->trainingsziel);
            $sql .= " AND TrainingszielBezeichnung = '$ziel'";
        }
        if ($this->von !== '') {
            $von = mysqli_real_escape_string($connection, $this->von);
            $sql .= " AND Datum >= '$von'"; // nur Trainings ab diesem Datum
        }
        if ($this->bis !== '') {
            $bis = mysqli_real_escape_string($connection, $this->bis);
            $sql .= " AND Datum <= '$bis'"; // nur Trainings bis zu diesem Datum
        }
        $sql .= " ORDER BY Datum"; // chronologisch sortieren

        $ergebnis = mysqli_query($connection, $sql); // SQL-Abfrage ausführen
        $rohdaten = []; // Zwischenspeicher: Monat => [km1, km2, ...]

        if ($ergebnis) {
            while ($zeile = mysqli_fetch_assoc($ergebnis)) {
                // Kilometerwert dem jeweiligen Monat zuordnen (als float für Rechenoperationen)
                $rohdaten[$zeile['Monat']][] = (float)$zeile['Kilometer'];
            }
        }

        $this->monate = [];
        foreach ($rohdaten as $monat => $werte) {
            sort($werte); // aufsteigend sortieren (Voraussetzung für Median-Berechnung)
            $n     = count($werte);     // Anzahl der Trainings in diesem Monat
            $summe = array_sum($werte); // Gesamtkilometer des Monats
            $this->monate[$monat] = [
                'summe'        => round($summe, 2),
                'durchschnitt' => round($summe / $n, 2),          // Summe geteilt durch Anzahl
                'minimum'      => round(min($werte), 2),           // kleinster Wert
                'maximum'      => round(max($werte), 2),           // größter Wert
                'median'       => round($this->berechneMedian($werte), 2),
                'stdabw'       => round($this->berechneStdabw($werte), 2),
            ];
        }
    }

    // Berechnet den Median (mittlerer Wert) eines bereits sortierten Arrays
    private function berechneMedian($werte)
    {
        $n     = count($werte);
        if ($n === 0) return 0;
        $mitte = intdiv($n, 2); // ganzzahlige Division liefert den mittleren Index
        // bei gerader Anzahl: Durchschnitt der zwei mittleren Werte
        return $n % 2 === 0
            ? ($werte[$mitte - 1] + $werte[$mitte]) / 2
            : $werte[$mitte]; // bei ungerader Anzahl: der direkte Mittelwert
    }

    // Berechnet die Standardabweichung (Streuung der Werte um den Durchschnitt)
    private function berechneStdabw($werte)
    {
        $n             = count($werte);
        if ($n === 0) return 0;
        $avg           = array_sum($werte) / $n; // Durchschnitt
        // quadratische Abweichung jedes Wertes vom Durchschnitt aufsummieren
        $summeQuadrate = array_sum(array_map(fn($w) => ($w - $avg) ** 2, $werte));
        return sqrt($summeQuadrate / $n); // Wurzel aus dem Durchschnitt der Quadrate
    }

    // Gibt die Statistik eines einzelnen Monats zurück (null wenn nicht vorhanden)
    public function getMonat($monat)
    {
        return $this->monate[$monat] ?? null;
    }

    // Gibt alle berechneten Monate mit ihren Statistiken zurück
    public function getMonate()
    {
        return $this->monate;
    }
}
