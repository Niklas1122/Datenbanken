<?php

function teamExistiert($connection, $teamname)
{
    $team = mysqli_real_escape_string($connection, $teamname);
    $ergebnis = mysqli_query($connection, "SELECT Teamname FROM TEAM WHERE Teamname = '$team'");
    return $ergebnis && mysqli_num_rows($ergebnis) > 0;
}

function speichereTeam($connection, $teamname, $vorname, $nachname, $loginname, $passwort)
{
    $team  = mysqli_real_escape_string($connection, $teamname);
    $vn    = mysqli_real_escape_string($connection, $vorname);
    $nn    = mysqli_real_escape_string($connection, $nachname);
    $login = mysqli_real_escape_string($connection, $loginname);
    $pass  = mysqli_real_escape_string($connection, $passwort);

    $login_check = mysqli_query($connection, "SELECT Loginname FROM TEAMCHEF WHERE Loginname = '$login'");
    if ($login_check && mysqli_num_rows($login_check) > 0) {
        return "Loginname ist schon vergeben.";
    }

    if (
        mysqli_query($connection, "INSERT INTO TEAMCHEF (Loginname, Vorname, Nachname, Passwort) VALUES ('$login', '$vn', '$nn', '$pass')") &&
        mysqli_query($connection, "INSERT INTO TEAM (Teamname, Loginname) VALUES ('$team', '$login')")
    ) {
        return "";
    }

    return "Fehler beim Speichern.";
}

function holeTeamnameZumLogin($connection, $teamchef_login)
{
    $login = mysqli_real_escape_string($connection, $teamchef_login);
    $abfrage = mysqli_query($connection, "SELECT Teamname FROM TEAM WHERE Loginname = '$login' LIMIT 1");

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

    $id   = mysqli_real_escape_string($connection, trim($mitarbeiter_id));
    $dat  = mysqli_real_escape_string($connection, trim($datum));
    $km   = mysqli_real_escape_string($connection, trim($kilometer));
    $ziel = mysqli_real_escape_string($connection, trim($trainingsziel));

    try {
        $pruefung = mysqli_query($connection, "SELECT MitarbeiterID FROM TRAINING WHERE MitarbeiterID = '$id' AND Datum = '$dat'");

        if ($pruefung && mysqli_num_rows($pruefung) > 0) {
            return "Für diesen Fahrer gibt es an diesem Tag schon ein Training.";
        }

        mysqli_query($connection, "INSERT INTO TRAINING (Datum, Kilometer, MitarbeiterID, TrainingszielBezeichnung)
                                   VALUES ('$dat', '$km', '$id', '$ziel')");
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
