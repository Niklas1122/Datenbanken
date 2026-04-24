<?php
require_once __DIR__ . '/connection.php';

if (
    isset(
        $_POST['form_typ'],
        $_POST['team_name'],
        $_POST['teamchef_vorname'],
        $_POST['teamchef_name'],
        $_POST['teamchef_login'],
        $_POST['teamchef_passwort']
    )
) {
    $teamname = trim($_POST['team_name']);
    $vorname = trim($_POST['teamchef_vorname']);
    $nachname = trim($_POST['teamchef_name']);
    $loginname = trim($_POST['teamchef_login']);
    $passwort = trim($_POST['teamchef_passwort']);

    if ($teamname !== "" && $vorname !== "" && $nachname !== "" && $loginname !== "" && $passwort !== "") {
        $team_check = "SELECT * FROM TEAM WHERE Teamname = '$teamname'";
        $teamchef_check = "SELECT * FROM TEAMCHEF WHERE Loginname = '$loginname'";

        $team_ergebnis = mysqli_query($connection, $team_check);
        $teamchef_ergebnis = mysqli_query($connection, $teamchef_check);

        if (mysqli_num_rows($team_ergebnis) > 0) {
            $meldung = "Teamname ist schon vergeben.";
        } elseif (mysqli_num_rows($teamchef_ergebnis) > 0) {
            $meldung = "Loginname ist schon vergeben.";
        } else {
            $sql_teamchef = "INSERT INTO TEAMCHEF (Loginname, Vorname, Nachname, Passwort) VALUES ('$loginname', '$vorname', '$nachname', '$passwort')";
            $sql_team = "INSERT INTO TEAM (Teamname, Loginname) VALUES ('$teamname', '$loginname')";

            if (mysqli_query($connection, $sql_teamchef) && mysqli_query($connection, $sql_team)) {
                $meldung = "Team wurde gespeichert.";
            } else {
                $meldung = "Fehler beim Speichern.";
            }
        }
    } else {
        $meldung = "Bitte alles ausfüllen.";
    }
}
