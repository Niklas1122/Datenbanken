<?php
// Jonas
require_once __DIR__ . '/connection.inc.php';

if (isset($_POST['form_typ']) && $_POST['form_typ'] === 'veranstalter_registrierung') {
    $loginname = trim($_POST['veranstalter_name'] ?? '');
    $passwort  = trim($_POST['veranstalter_passwort'] ?? '');

    if ($loginname === '' || $passwort === '') {
        $meldung = "Bitte alles ausfüllen.";
    } else {
        $stmt = mysqli_prepare($connection, "SELECT Loginname FROM RENNVERANSTALTER WHERE Loginname = ?");
        mysqli_stmt_bind_param($stmt, 's', $loginname);
        mysqli_stmt_execute($stmt);
        $ergebnis = mysqli_stmt_get_result($stmt);

        if ($ergebnis && mysqli_num_rows($ergebnis) > 0) {
            $meldung = "Loginname ist schon vergeben.";
        } else {
            $passwort_hash = password_hash($passwort, PASSWORD_DEFAULT);
            $stmt2 = mysqli_prepare($connection, "INSERT INTO RENNVERANSTALTER (Loginname, Passwort) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt2, 'ss', $loginname, $passwort_hash);
            if (mysqli_stmt_execute($stmt2)) {
                $meldung = "Rennveranstalter wurde registriert.";
            } else {
                $meldung = "Fehler beim Speichern.";
            }
        }
    }
}
