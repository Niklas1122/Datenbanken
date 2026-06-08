<?php
// Bedi

function teamExistiert($connection, $teamname)
{
    $stmt = mysqli_prepare($connection, "SELECT Teamname FROM TEAM WHERE Teamname = ?");
    mysqli_stmt_bind_param($stmt, 's', $teamname);
    mysqli_stmt_execute($stmt);
    $ergebnis = mysqli_stmt_get_result($stmt);
    return $ergebnis && mysqli_num_rows($ergebnis) > 0;
}

function speichereTeam($connection, $teamname, $vorname, $nachname, $loginname, $passwort)
{
    $stmt = mysqli_prepare($connection, "CALL TeamRegistrieren(?,?,?,?,?,@fehler)");
    mysqli_stmt_bind_param($stmt, 'sssss', $teamname, $vorname, $nachname, $loginname, $passwort);
    mysqli_stmt_execute($stmt);
    $zeile = mysqli_fetch_assoc(mysqli_query($connection, "SELECT @fehler AS fehler"));
    return $zeile['fehler'];
}

function holeTeamnameZumLogin($connection, $teamchef_login)
{
    $stmt = mysqli_prepare($connection, "SELECT Teamname FROM TEAM WHERE Loginname = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $teamchef_login);
    mysqli_stmt_execute($stmt);
    $abfrage = mysqli_stmt_get_result($stmt);

    if ($abfrage && mysqli_num_rows($abfrage) > 0) {
        return mysqli_fetch_assoc($abfrage)['Teamname'];
    }

    return '';
}

function tabelleExistiert($connection, $tabellenname)
{
    $name = mysqli_real_escape_string($connection, $tabellenname);
    $ergebnis = mysqli_query($connection, "SHOW TABLES LIKE '$name'");

    return $ergebnis && mysqli_num_rows($ergebnis) > 0;
}

function holeTrainingsziele($connection)
{
    if (!tabelleExistiert($connection, 'TRAININGSZIEL')) {
        return ['Ausdauer', 'Sprintkraft', 'Steigungen'];
    }

    try {
        $ergebnis = mysqli_query($connection, "SELECT Bezeichnung FROM TRAININGSZIEL ORDER BY Bezeichnung");
    } catch (mysqli_sql_exception $e) {
        return ['Ausdauer', 'Sprintkraft', 'Steigungen'];
    }

    $ziele = [];
    if ($ergebnis) {
        while ($zeile = mysqli_fetch_assoc($ergebnis)) {
            $ziele[] = $zeile['Bezeichnung'];
        }
    }

    return count($ziele) > 0 ? $ziele : ['Ausdauer', 'Sprintkraft', 'Steigungen'];
}

function speichereTraining($connection, $mitarbeiter_id, $datum, $kilometer, $trainingsziel)
{
    if (!tabelleExistiert($connection, 'TRAINING')) {
        return "Die Tabelle TRAINING fehlt noch.";
    }

    $id   = trim($mitarbeiter_id);
    $dat  = trim($datum);
    $km   = trim($kilometer);
    $ziel = trim($trainingsziel);

    try {
        $stmt = mysqli_prepare($connection, "SELECT MitarbeiterID FROM TRAINING WHERE MitarbeiterID = ? AND Datum = ?");
        mysqli_stmt_bind_param($stmt, 'ss', $id, $dat);
        mysqli_stmt_execute($stmt);
        $pruefung = mysqli_stmt_get_result($stmt);

        if ($pruefung && mysqli_num_rows($pruefung) > 0) {
            return "Für diesen Fahrer gibt es an diesem Tag schon ein Training.";
        }

        $stmt2 = mysqli_prepare($connection, "INSERT INTO TRAINING (Datum, Kilometer, MitarbeiterID, TrainingszielBezeichnung) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt2, 'ssss', $dat, $km, $id, $ziel);
        mysqli_stmt_execute($stmt2);
        return "";
    } catch (mysqli_sql_exception $e) {
        return "Training konnte nicht gespeichert werden.";
    }
}

function holeTrainingsliste($connection)
{
    if (!tabelleExistiert($connection, 'TRAINING')) {
        return [];
    }

    try {
        $ergebnis = mysqli_query($connection, "SELECT MitarbeiterID, Datum, Kilometer, TrainingszielBezeichnung FROM TRAINING ORDER BY Datum DESC");
    } catch (mysqli_sql_exception $e) {
        return [];
    }

    $liste = [];
    if ($ergebnis) {
        while ($zeile = mysqli_fetch_assoc($ergebnis)) {
            $liste[] = $zeile;
        }
    }

    return $liste;
}
