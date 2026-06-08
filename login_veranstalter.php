<?php
// Jonas
require_once __DIR__ . '/connection.php';

if (isset($_POST['form_typ']) && $_POST['form_typ'] === 'veranstalter_login') {
    $loginname = trim($_POST['veranstalter_login'] ?? '');
    $passwort  = trim($_POST['veranstalter_passwort_login'] ?? '');
    // Prüft, ob das Formular abgeschickt wurde und ob es sich um den Veranstalter-Login handelt.
    if ($loginname === '' || $passwort === '') {
        $meldung = "Bitte alles ausfüllen.";
    } else {
        $login    = mysqli_real_escape_string($connection, $loginname);
        $ergebnis = mysqli_query($connection, "SELECT Passwort FROM RENNVERANSTALTER WHERE Loginname = '$login'"); 
        $zeile    = $ergebnis ? mysqli_fetch_assoc($ergebnis) : null; // Holt die erste gefundene Zeile aus dem Ergebnis.

        if ($zeile && password_verify($passwort, $zeile['Passwort'])) {    // Passwort Überprüfung
            $_SESSION['veranstalter_login'] = $loginname;
            header("Location: rennveranstalter_dashboard.php");  // Weiterleitung         
             exit;
        } else {
            $meldung = "Loginname oder Passwort ist falsch.";
        }
    }
}
