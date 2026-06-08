<?php
// Jonas
require_once __DIR__ . '/connection.inc.php';

if (isset($_POST['form_typ']) && $_POST['form_typ'] === 'veranstalter_login') {
    $loginname = trim($_POST['veranstalter_login'] ?? '');
    $passwort  = trim($_POST['veranstalter_passwort_login'] ?? '');

    if ($loginname === '' || $passwort === '') {
        $meldung = "Bitte alles ausfüllen.";
    } else {
        $stmt = mysqli_prepare($connection, "SELECT Passwort FROM RENNVERANSTALTER WHERE Loginname = ?");
        mysqli_stmt_bind_param($stmt, 's', $loginname);
        mysqli_stmt_execute($stmt);
        $ergebnis = mysqli_stmt_get_result($stmt);
        $zeile    = $ergebnis ? mysqli_fetch_assoc($ergebnis) : null;

        if ($zeile && password_verify($passwort, $zeile['Passwort'])) {
            $_SESSION['veranstalter_login'] = $loginname;
            header("Location: rennveranstalter_dashboard.php");
            exit;
        } else {
            $meldung = "Loginname oder Passwort ist falsch.";
        }
    }
}
