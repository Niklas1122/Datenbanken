<?php
require_once __DIR__ . '/connection.php';

if (isset($_POST['form_typ']) && $_POST['form_typ'] === 'veranstalter_registrierung') {
    $loginname = trim($_POST['veranstalter_name'] ?? '');
    $passwort  = trim($_POST['veranstalter_passwort'] ?? '');

    if ($loginname === '' || $passwort === '') {
        $meldung = "Bitte alles ausfüllen.";
    } else {
        $login = mysqli_real_escape_string($connection, $loginname);
        $pass  = mysqli_real_escape_string($connection, password_hash($passwort, PASSWORD_DEFAULT));
        $ergebnis = mysqli_query($connection, "SELECT Loginname FROM RENNVERANSTALTER WHERE Loginname = '$login'");

        if ($ergebnis && mysqli_num_rows($ergebnis) > 0) {
            $meldung = "Loginname ist schon vergeben.";
        } elseif (mysqli_query($connection, "INSERT INTO RENNVERANSTALTER (Loginname, Passwort) VALUES ('$login', '$pass')")) {
            $meldung = "Rennveranstalter wurde registriert.";
        } else {
            $meldung = "Fehler beim Speichern.";
        }
    }
}
