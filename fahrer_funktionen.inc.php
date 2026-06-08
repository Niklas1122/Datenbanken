<?php
// Bedi
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
    $stmt = mysqli_prepare($connection, "SELECT MitarbeiterID, Name, PLZ, Ort, Strasse, Hausnr, TelNr FROM FAHRER WHERE Teamname = ? ORDER BY MitarbeiterID");
    mysqli_stmt_bind_param($stmt, 's', $teamname);
    mysqli_stmt_execute($stmt);
    $ergebnis = mysqli_stmt_get_result($stmt);
    $liste = [];
    if ($ergebnis) {
        while ($zeile = mysqli_fetch_assoc($ergebnis)) {
            $liste[] = $zeile;
        }
    }
    return $liste;
}

function holeFahrerZumBearbeiten($connection, $teamname, $mitarbeiter_id)
{
    $stmt = mysqli_prepare($connection, "SELECT MitarbeiterID, Name, PLZ, Ort, Strasse, Hausnr, TelNr FROM FAHRER WHERE MitarbeiterID = ? AND Teamname = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'ss', $mitarbeiter_id, $teamname);
    mysqli_stmt_execute($stmt);
    $ergebnis = mysqli_stmt_get_result($stmt);
    $liste = [];
    if ($ergebnis) {
        while ($zeile = mysqli_fetch_assoc($ergebnis)) {
            $liste[] = $zeile;
        }
    }
    return count($liste) > 0 ? $liste[0] : leeresFahrerFormular();
}

function loescheFahrer($connection, $teamname, $mitarbeiter_id)
{
    $id = trim($mitarbeiter_id);
    if ($id === '') return false;

    $stmt = mysqli_prepare($connection, "DELETE FROM FAHRER WHERE MitarbeiterID = ? AND Teamname = ?");
    mysqli_stmt_bind_param($stmt, 'ss', $id, $teamname);
    return mysqli_stmt_execute($stmt);
}

function speichereFahrer($connection, $teamname, $fahrer_form, $modus)
{
    if ($fahrer_form['MitarbeiterID'] === '' || $fahrer_form['Name'] === '') {
        return ['meldung' => '', 'fehler' => 'Mitarbeiter-ID und Name müssen ausgefüllt sein.', 'bearbeiten' => $modus === 'bearbeiten', 'formular_zuruecksetzen' => false];
    }

    $id      = $fahrer_form['MitarbeiterID'];
    $name    = $fahrer_form['Name'];
    $plz     = $fahrer_form['PLZ'];
    $ort     = $fahrer_form['Ort'];
    $strasse = $fahrer_form['Strasse'];
    $hausnr  = $fahrer_form['Hausnr'];
    $telnr   = $fahrer_form['TelNr'];

    if ($modus === 'bearbeiten') {
        $stmt = mysqli_prepare($connection, "UPDATE FAHRER SET Name=?, PLZ=?, Ort=?, Strasse=?, Hausnr=?, TelNr=? WHERE MitarbeiterID=? AND Teamname=?");
        mysqli_stmt_bind_param($stmt, 'ssssssss', $name, $plz, $ort, $strasse, $hausnr, $telnr, $id, $teamname);
        $ok = mysqli_stmt_execute($stmt);
        return $ok
            ? ['meldung' => 'Fahrer wurde geändert.', 'fehler' => '', 'bearbeiten' => false, 'formular_zuruecksetzen' => true]
            : ['meldung' => '', 'fehler' => 'Änderung konnte nicht gespeichert werden.', 'bearbeiten' => true, 'formular_zuruecksetzen' => false];
    }

    mysqli_begin_transaction($connection);
    try {
        $stmt = mysqli_prepare($connection, "SELECT MitarbeiterID FROM FAHRER WHERE MitarbeiterID=? AND Teamname=?");
        mysqli_stmt_bind_param($stmt, 'ss', $id, $teamname);
        mysqli_stmt_execute($stmt);
        $check = mysqli_stmt_get_result($stmt);
        if ($check && mysqli_num_rows($check) > 0) {
            mysqli_rollback($connection);
            return ['meldung' => '', 'fehler' => 'Diese Mitarbeiter-ID gibt es im Team bereits.', 'bearbeiten' => false, 'formular_zuruecksetzen' => false];
        }

        $stmt2 = mysqli_prepare($connection, "INSERT INTO FAHRER (MitarbeiterID, Name, PLZ, Ort, Strasse, Hausnr, TelNr, Teamname) VALUES (?,?,?,?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt2, 'ssssssss', $id, $name, $plz, $ort, $strasse, $hausnr, $telnr, $teamname);
        mysqli_stmt_execute($stmt2);
        mysqli_commit($connection);
        return ['meldung' => 'Fahrer wurde angelegt.', 'fehler' => '', 'bearbeiten' => false, 'formular_zuruecksetzen' => true];
    } catch (mysqli_sql_exception $e) {
        mysqli_rollback($connection);
        return ['meldung' => '', 'fehler' => 'Neuer Fahrer konnte nicht gespeichert werden.', 'bearbeiten' => false, 'formular_zuruecksetzen' => false];
    }
}

function holeZukuenftigeRennen($connection)
{
    return abfrageAlsListe($connection, "SELECT RennID, Datum, Standort FROM RENNEN WHERE Datum >= CURDATE() ORDER BY Datum");
}

function holeRennenMitTeilnahmen($connection, $teamname)
{
    $stmt = mysqli_prepare($connection, "SELECT DISTINCT r.RennID, r.Datum, r.Standort FROM RENNEN r JOIN TEILNAHME t ON t.RennID = r.RennID JOIN FAHRER f ON f.MitarbeiterID = t.MitarbeiterID WHERE f.Teamname = ? ORDER BY r.Datum DESC");
    mysqli_stmt_bind_param($stmt, 's', $teamname);
    mysqli_stmt_execute($stmt);
    $ergebnis = mysqli_stmt_get_result($stmt);
    $liste = [];
    if ($ergebnis) {
        while ($zeile = mysqli_fetch_assoc($ergebnis)) {
            $liste[] = $zeile;
        }
    }
    return $liste;
}

function meldeFahrerZuRennen($connection, $rennen_id, $fahrer_ids)
{
    $renn_id = trim($rennen_id);
    if ($renn_id === '') return 0;

    $gespeichert = 0;
    $stmt = mysqli_prepare($connection, "INSERT INTO TEILNAHME (RennID, MitarbeiterID) VALUES (?, ?)");

    foreach ($fahrer_ids as $fahrer_id) {
        $id = trim($fahrer_id);
        if ($id === '') continue;

        try {
            mysqli_stmt_bind_param($stmt, 'ss', $renn_id, $id);
            if (mysqli_stmt_execute($stmt)) {
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
    $quell = trim($quell_id);
    $ziel  = trim($ziel_id);

    if ($quell === '' || $ziel === '' || $quell === $ziel) return 0;

    $stmt = mysqli_prepare($connection, "SELECT t.MitarbeiterID FROM TEILNAHME t JOIN FAHRER f ON f.MitarbeiterID = t.MitarbeiterID WHERE t.RennID = ? AND f.Teamname = ?");
    mysqli_stmt_bind_param($stmt, 'ss', $quell, $teamname);
    mysqli_stmt_execute($stmt);
    $ergebnis = mysqli_stmt_get_result($stmt);
    $fahrer = [];
    if ($ergebnis) {
        while ($zeile = mysqli_fetch_assoc($ergebnis)) {
            $fahrer[] = $zeile;
        }
    }

    if (count($fahrer) === 0) return 0;

    $gespeichert = 0;
    mysqli_begin_transaction($connection);
    try {
        $stmt2 = mysqli_prepare($connection, "INSERT INTO TEILNAHME (RennID, MitarbeiterID) VALUES (?, ?)");
        foreach ($fahrer as $zeile) {
            $id = $zeile['MitarbeiterID'];
            mysqli_stmt_bind_param($stmt2, 'ss', $ziel, $id);
            mysqli_stmt_execute($stmt2);
            $gespeichert++;
        }
        mysqli_commit($connection);
    } catch (mysqli_sql_exception $e) {
        mysqli_rollback($connection);
        return 0;
    }

    return $gespeichert;
}
