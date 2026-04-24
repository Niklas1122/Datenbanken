<?php
require_once __DIR__ . '/connection.php';

if (
    isset($_POST['form_typ'], $_POST['veranstalter_name'], $_POST['veranstalter_passwort'])
) {
    $loginname = trim($_POST['veranstalter_name']);
    $passwort = trim($_POST['veranstalter_passwort']);

    if ($loginname !== "" && $passwort !== "") {
        $abfrage = "SELECT * FROM RENNVERANSTALTER WHERE Loginname = '$loginname'";
        $ergebnis = mysqli_query($connection, $abfrage);

        if (mysqli_num_rows($ergebnis) > 0) {
            $meldung = "Loginname ist schon vergeben.";
        } else {
            $sql = "INSERT INTO RENNVERANSTALTER (Loginname, Passwort) VALUES ('$loginname', '$passwort')";

            if (mysqli_query($connection, $sql)) {
                $meldung = "Rennveranstalter wurde registriert.";
            } else {
                $meldung = "Fehler beim Speichern.";
            }
        }
    } else {
        $meldung = "Bitte alles ausfüllen.";
    }
}
