<?php

// Führt eine SELECT-Abfrage aus und gibt alle Zeilen als Array zurück.
function abfrageAlsListe($connection, $sql)
{
    $liste = [];
    try {
        $ergebnis = mysqli_query($connection, $sql);
    } catch (mysqli_sql_exception $e) {
        return $liste;
    }

    if ($ergebnis) {
        while ($zeile = mysqli_fetch_assoc($ergebnis)) {
            $liste[] = $zeile;
        }
    }

    return $liste;
}

// Gibt die nächste freie Startnummer für ein Rennen zurück.
function naechsteStartnummer($connection, $renn_id)
{
    $abfrage = mysqli_query($connection, "SELECT MAX(Startnr) AS max_nr FROM TEILNAHME WHERE RennID = '$renn_id'");

    if ($abfrage && ($zeile = mysqli_fetch_assoc($abfrage)) && $zeile['max_nr'] !== null) {
        return (int)$zeile['max_nr'] + 1;
    }

    return 1;
}

function leeresFahrerFormular()
{
    return ['MitarbeiterID' => '', 'Name' => '', 'PLZ' => '', 'Ort' => '', 'Strasse' => '', 'Hausnr' => '', 'TelNr' => ''];
}

function liesFahrerFormularAusPost()
{
    return [
        'MitarbeiterID' => trim($_POST['mitarbeiter_id'] ?? ''),
        'Name'          => trim($_POST['fahrer_name'] ?? ''),
        'PLZ'           => trim($_POST['plz'] ?? ''),
        'Ort'           => trim($_POST['ort'] ?? ''),
        'Strasse'       => trim($_POST['strasse'] ?? ''),
        'Hausnr'        => trim($_POST['hausnr'] ?? ''),
        'TelNr'         => trim($_POST['telnr'] ?? '')
    ];
}

function holeFahrerListe($connection, $teamname)
{
    $team = mysqli_real_escape_string($connection, $teamname);
    return abfrageAlsListe($connection, "SELECT MitarbeiterID, Name, PLZ, Ort, Strasse, Hausnr, TelNr FROM FAHRER WHERE Teamname = '$team' ORDER BY MitarbeiterID");
}

function holeFahrerZumBearbeiten($connection, $teamname, $mitarbeiter_id)
{
    $team = mysqli_real_escape_string($connection, $teamname);
    $id   = mysqli_real_escape_string($connection, $mitarbeiter_id);
    $liste = abfrageAlsListe($connection, "SELECT MitarbeiterID, Name, PLZ, Ort, Strasse, Hausnr, TelNr FROM FAHRER WHERE MitarbeiterID = '$id' AND Teamname = '$team' LIMIT 1");

    return count($liste) > 0 ? $liste[0] : leeresFahrerFormular();
}

function loescheFahrer($connection, $teamname, $mitarbeiter_id)
{
    $team = mysqli_real_escape_string($connection, $teamname);
    $id   = mysqli_real_escape_string($connection, trim($mitarbeiter_id));

    if ($id === '') return false;

    return mysqli_query($connection, "DELETE FROM FAHRER WHERE MitarbeiterID = '$id' AND Teamname = '$team'") !== false;
}

function speichereFahrer($connection, $teamname, $fahrer_form, $modus)
{
    if ($fahrer_form['MitarbeiterID'] === '' || $fahrer_form['Name'] === '') {
        return ['meldung' => '', 'fehler' => 'Mitarbeiter-ID und Name müssen ausgefüllt sein.', 'bearbeiten' => $modus === 'bearbeiten', 'formular_zuruecksetzen' => false];
    }

    $team    = mysqli_real_escape_string($connection, $teamname);
    $id      = mysqli_real_escape_string($connection, $fahrer_form['MitarbeiterID']);
    $name    = mysqli_real_escape_string($connection, $fahrer_form['Name']);
    $plz     = mysqli_real_escape_string($connection, $fahrer_form['PLZ']);
    $ort     = mysqli_real_escape_string($connection, $fahrer_form['Ort']);
    $strasse = mysqli_real_escape_string($connection, $fahrer_form['Strasse']);
    $hausnr  = mysqli_real_escape_string($connection, $fahrer_form['Hausnr']);
    $telnr   = mysqli_real_escape_string($connection, $fahrer_form['TelNr']);

    if ($modus === 'bearbeiten') {
        $ok = mysqli_query($connection, "UPDATE FAHRER SET Name='$name', PLZ='$plz', Ort='$ort', Strasse='$strasse', Hausnr='$hausnr', TelNr='$telnr' WHERE MitarbeiterID='$id' AND Teamname='$team'");
        return $ok
            ? ['meldung' => 'Fahrer wurde geändert.', 'fehler' => '', 'bearbeiten' => false, 'formular_zuruecksetzen' => true]
            : ['meldung' => '', 'fehler' => 'Änderung konnte nicht gespeichert werden.', 'bearbeiten' => true, 'formular_zuruecksetzen' => false];
    }

    $check = mysqli_query($connection, "SELECT MitarbeiterID FROM FAHRER WHERE MitarbeiterID='$id' AND Teamname='$team'");
    if ($check && mysqli_num_rows($check) > 0) {
        return ['meldung' => '', 'fehler' => 'Diese Mitarbeiter-ID gibt es im Team bereits.', 'bearbeiten' => false, 'formular_zuruecksetzen' => false];
    }

    $ok = mysqli_query($connection, "INSERT INTO FAHRER (MitarbeiterID, Name, PLZ, Ort, Strasse, Hausnr, TelNr, Teamname) VALUES ('$id','$name','$plz','$ort','$strasse','$hausnr','$telnr','$team')");
    return $ok
        ? ['meldung' => 'Fahrer wurde angelegt.', 'fehler' => '', 'bearbeiten' => false, 'formular_zuruecksetzen' => true]
        : ['meldung' => '', 'fehler' => 'Neuer Fahrer konnte nicht gespeichert werden.', 'bearbeiten' => false, 'formular_zuruecksetzen' => false];
}

function holeZukuenftigeRennen($connection)
{
    return abfrageAlsListe($connection, "SELECT RennID, Datum, Standort FROM RENNEN WHERE Datum >= CURDATE() ORDER BY Datum");
}

function holeRennenMitTeilnahmen($connection, $teamname)
{
    $team = mysqli_real_escape_string($connection, $teamname);
    return abfrageAlsListe($connection, "SELECT DISTINCT r.RennID, r.Datum, r.Standort FROM RENNEN r JOIN TEILNAHME t ON t.RennID = r.RennID JOIN FAHRER f ON f.MitarbeiterID = t.MitarbeiterID WHERE f.Teamname = '$team' ORDER BY r.Datum DESC");
}

function meldeFahrerZuRennen($connection, $rennen_id, $fahrer_ids)
{
    $renn_id = mysqli_real_escape_string($connection, trim($rennen_id));
    if ($renn_id === '') return 0;

    $startnr = naechsteStartnummer($connection, $renn_id);
    $gespeichert = 0;

    foreach ($fahrer_ids as $fahrer_id) {
        $id = mysqli_real_escape_string($connection, trim($fahrer_id));
        if ($id === '') continue;

        try {
            if (mysqli_query($connection, "INSERT INTO TEILNAHME (RennID, MitarbeiterID, Startnr) VALUES ('$renn_id', '$id', '$startnr')")) {
                $startnr++;
                $gespeichert++;
            }
        } catch (mysqli_sql_exception $e) {
            continue;
        }
    }

    return $gespeichert;
}

function kopiereTeilnahmen($connection, $teamname, $quell_id, $ziel_id)
{
    $quell = mysqli_real_escape_string($connection, trim($quell_id));
    $ziel  = mysqli_real_escape_string($connection, trim($ziel_id));
    $team  = mysqli_real_escape_string($connection, $teamname);

    if ($quell === '' || $ziel === '' || $quell === $ziel) return 0;

    $fahrer = abfrageAlsListe($connection, "SELECT t.MitarbeiterID FROM TEILNAHME t JOIN FAHRER f ON f.MitarbeiterID = t.MitarbeiterID WHERE t.RennID = '$quell' AND f.Teamname = '$team'");

    if (count($fahrer) === 0) return 0;

    $startnr = naechsteStartnummer($connection, $ziel);
    $gespeichert = 0;

    foreach ($fahrer as $zeile) {
        $id = mysqli_real_escape_string($connection, $zeile['MitarbeiterID']);
        try {
            if (mysqli_query($connection, "INSERT INTO TEILNAHME (RennID, MitarbeiterID, Startnr) VALUES ('$ziel', '$id', '$startnr')")) {
                $startnr++;
                $gespeichert++;
            }
        } catch (mysqli_sql_exception $e) {
            continue;
        }
    }

    return $gespeichert;
}
