<?php
require_once __DIR__ . '/connection.php';

if (isset($_POST['form_typ']) && $_POST['form_typ'] === 'veranstalter_login') {
    $loginname = trim($_POST['veranstalter_login'] ?? '');
    $passwort  = trim($_POST['veranstalter_passwort_login'] ?? '');

    if ($loginname === '' || $passwort === '') {
        $meldung = "Bitte alles ausfüllen.";
    } else {
        $login = mysqli_real_escape_string($connection, $loginname);
        $pass  = mysqli_real_escape_string($connection, $passwort);
        $ergebnis = mysqli_query($connection, "SELECT Loginname FROM RENNVERANSTALTER WHERE Loginname = '$login' AND Passwort = '$pass'");

        if ($ergebnis && mysqli_num_rows($ergebnis) > 0) {
            $_SESSION['veranstalter_login'] = $loginname;
            header("Location: rennveranstalter_dashboard.php");
            exit;
        } else {
            $meldung = "Loginname oder Passwort ist falsch.";
        }
    }
}
