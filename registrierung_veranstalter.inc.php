<?php
// Jonas
require_once __DIR__ . '/connection.inc.php';
// Prüfen, ob das Formular zur Veranstalter-Registrierung abgesendet wurde
if (isset($_POST['form_typ']) && $_POST['form_typ'] === 'veranstalter_registrierung') {
// Eingaben aus dem Formular holen   
$loginname = trim($_POST['veranstalter_name'] ?? '');
    $passwort  = trim($_POST['veranstalter_passwort'] ?? '');
// Kontrolle, ob beide Felder ausgefüllt wurde
    if ($loginname === '' || $passwort === '') {
        $meldung = "Bitte alles ausfüllen.";
    } else {
        // Prüfen, ob der Loginname bereits existiert
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
            //Rückmeldung, ob Speichern erfolgreich war oder nicht
            if (mysqli_stmt_execute($stmt2)) {
                $meldung = "Rennveranstalter wurde registriert.";
            } else {
                $meldung = "Fehler beim Speichern.";
            }
        }
    }
}
