<?php

function leeresFahrerFormular()
{
    return array(
        'MitarbeiterID' => '',
        'Name' => '',
        'PLZ' => '',
        'Ort' => '',
        'Strasse' => '',
        'Hausnr' => '',
        'TelNr' => ''
    );
}

function liesFahrerFormularAusPost()
{
    return array(
        'MitarbeiterID' => trim($_POST['mitarbeiter_id'] ?? ''),
        'Name' => trim($_POST['fahrer_name'] ?? ''),
        'PLZ' => trim($_POST['plz'] ?? ''),
        'Ort' => trim($_POST['ort'] ?? ''),
        'Strasse' => trim($_POST['strasse'] ?? ''),
        'Hausnr' => trim($_POST['hausnr'] ?? ''),
        'TelNr' => trim($_POST['telnr'] ?? '')
    );
}

function loescheFahrer($connection, $teamname, $mitarbeiter_id)
{
    $team_sicher = mysqli_real_escape_string($connection, $teamname);
    $id_sicher = mysqli_real_escape_string($connection, trim($mitarbeiter_id));

    if ($id_sicher === '') {
        return false;
    }

    return mysqli_query(
        $connection,
        "DELETE FROM FAHRER WHERE MitarbeiterID = '$id_sicher' AND Teamname = '$team_sicher'"
    ) !== false;
}

function speichereFahrer($connection, $teamname, $fahrer_form, $modus)
{
    if ($fahrer_form['MitarbeiterID'] === '' || $fahrer_form['Name'] === '') {
        return array(
            'meldung' => '',
            'fehler' => 'Mitarbeiter-ID und Name müssen ausgefüllt sein.',
            'bearbeiten' => $modus === 'bearbeiten',
            'formular_zuruecksetzen' => false
        );
    }

    $team_sicher = mysqli_real_escape_string($connection, $teamname);
    $id_sicher = mysqli_real_escape_string($connection, $fahrer_form['MitarbeiterID']);
    $name_sicher = mysqli_real_escape_string($connection, $fahrer_form['Name']);
    $plz_sicher = mysqli_real_escape_string($connection, $fahrer_form['PLZ']);
    $ort_sicher = mysqli_real_escape_string($connection, $fahrer_form['Ort']);
    $strasse_sicher = mysqli_real_escape_string($connection, $fahrer_form['Strasse']);
    $hausnr_sicher = mysqli_real_escape_string($connection, $fahrer_form['Hausnr']);
    $telnr_sicher = mysqli_real_escape_string($connection, $fahrer_form['TelNr']);

    if ($modus === 'bearbeiten') {
        $sql = "UPDATE FAHRER
                SET Name = '$name_sicher', PLZ = '$plz_sicher', Ort = '$ort_sicher', Strasse = '$strasse_sicher', Hausnr = '$hausnr_sicher', TelNr = '$telnr_sicher'
                WHERE MitarbeiterID = '$id_sicher' AND Teamname = '$team_sicher'";

        if (mysqli_query($connection, $sql)) {
            return array(
                'meldung' => 'Fahrer wurde geändert.',
                'fehler' => '',
                'bearbeiten' => false,
                'formular_zuruecksetzen' => true
            );
        }

        return array(
            'meldung' => '',
            'fehler' => 'Änderung konnte nicht gespeichert werden.',
            'bearbeiten' => true,
            'formular_zuruecksetzen' => false
        );
    }

    $check = mysqli_query(
        $connection,
        "SELECT MitarbeiterID FROM FAHRER WHERE MitarbeiterID = '$id_sicher' AND Teamname = '$team_sicher'"
    );

    if ($check && mysqli_num_rows($check) > 0) {
        return array(
            'meldung' => '',
            'fehler' => 'Diese Mitarbeiter-ID gibt es im Team bereits.',
            'bearbeiten' => false,
            'formular_zuruecksetzen' => false
        );
    }

    $sql = "INSERT INTO FAHRER (MitarbeiterID, Name, PLZ, Ort, Strasse, Hausnr, TelNr, Teamname)
            VALUES ('$id_sicher', '$name_sicher', '$plz_sicher', '$ort_sicher', '$strasse_sicher', '$hausnr_sicher', '$telnr_sicher', '$team_sicher')";

    if (mysqli_query($connection, $sql)) {
        return array(
            'meldung' => 'Fahrer wurde angelegt.',
            'fehler' => '',
            'bearbeiten' => false,
            'formular_zuruecksetzen' => true
        );
    }

    return array(
        'meldung' => '',
        'fehler' => 'Neuer Fahrer konnte nicht gespeichert werden.',
        'bearbeiten' => false,
        'formular_zuruecksetzen' => false
    );
}

function holeFahrerZumBearbeiten($connection, $teamname, $mitarbeiter_id)
{
    $team_sicher = mysqli_real_escape_string($connection, $teamname);
    $id_sicher = mysqli_real_escape_string($connection, $mitarbeiter_id);
    $abfrage = mysqli_query(
        $connection,
        "SELECT MitarbeiterID, Name, PLZ, Ort, Strasse, Hausnr, TelNr
         FROM FAHRER
         WHERE MitarbeiterID = '$id_sicher' AND Teamname = '$team_sicher'
         LIMIT 1"
    );

    if ($abfrage && mysqli_num_rows($abfrage) > 0) {
        return mysqli_fetch_assoc($abfrage);
    }

    return leeresFahrerFormular();
}

function holeFahrerListe($connection, $teamname)
{
    $liste = array();
    $team_sicher = mysqli_real_escape_string($connection, $teamname);
    $abfrage = mysqli_query(
        $connection,
        "SELECT MitarbeiterID, Name, PLZ, Ort, Strasse, Hausnr, TelNr
         FROM FAHRER
         WHERE Teamname = '$team_sicher'
         ORDER BY MitarbeiterID"
    );

    if ($abfrage) {
        while ($fahrer = mysqli_fetch_assoc($abfrage)) {
            $liste[] = $fahrer;
        }
    }

    return $liste;
}
