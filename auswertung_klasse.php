<?php

class FahrerAuswertung
{
    private $mitarbeiterID;
    private $name;
    private $trainingsziel;
    private $von;
    private $bis;
    private $monate = [];

    public function __construct($mitarbeiterID, $name, $trainingsziel, $von, $bis)
    {
        $this->mitarbeiterID = $mitarbeiterID;
        $this->name          = $name;
        $this->trainingsziel = $trainingsziel;
        $this->von           = $von;
        $this->bis           = $bis;
    }

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

    public function berechne($connection)
    {
        $id  = mysqli_real_escape_string($connection, $this->mitarbeiterID);
        $sql = "SELECT DATE_FORMAT(Datum, '%Y-%m') AS Monat, Kilometer
                FROM TRAINING WHERE MitarbeiterID = '$id'";

        if ($this->trainingsziel !== '') {
            $ziel = mysqli_real_escape_string($connection, $this->trainingsziel);
            $sql .= " AND TrainingszielBezeichnung = '$ziel'";
        }
        if ($this->von !== '') {
            $von = mysqli_real_escape_string($connection, $this->von);
            $sql .= " AND Datum >= '$von'";
        }
        if ($this->bis !== '') {
            $bis = mysqli_real_escape_string($connection, $this->bis);
            $sql .= " AND Datum <= '$bis'";
        }
        $sql .= " ORDER BY Datum";

        $ergebnis = mysqli_query($connection, $sql);
        $rohdaten = [];

        if ($ergebnis) {
            while ($zeile = mysqli_fetch_assoc($ergebnis)) {
                $rohdaten[$zeile['Monat']][] = (float)$zeile['Kilometer'];
            }
        }

        $this->monate = [];
        foreach ($rohdaten as $monat => $werte) {
            sort($werte);
            $n     = count($werte);
            $summe = array_sum($werte);
            $this->monate[$monat] = [
                'summe'        => round($summe, 2),
                'durchschnitt' => round($summe / $n, 2),
                'minimum'      => round(min($werte), 2),
                'maximum'      => round(max($werte), 2),
                'median'       => round($this->berechneMedian($werte), 2),
                'stdabw'       => round($this->berechneStdabw($werte), 2),
            ];
        }
    }

    private function berechneMedian($werte)
    {
        $n     = count($werte);
        if ($n === 0) return 0;
        $mitte = intdiv($n, 2);
        return $n % 2 === 0
            ? ($werte[$mitte - 1] + $werte[$mitte]) / 2
            : $werte[$mitte];
    }

    private function berechneStdabw($werte)
    {
        $n             = count($werte);
        if ($n === 0) return 0;
        $avg           = array_sum($werte) / $n;
        $summeQuadrate = array_sum(array_map(fn($w) => ($w - $avg) ** 2, $werte));
        return sqrt($summeQuadrate / $n);
    }

    public function getMonat($monat)
    {
        return $this->monate[$monat] ?? null;
    }

    public function getMonate()
    {
        return $this->monate;
    }
}
