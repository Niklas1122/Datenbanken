<?php
require_once __DIR__ . '/connection.php';

if (isset($_POST['form_typ'], $_POST['login_name'], $_POST['password'])) {
    $loginname = trim($_POST['login_name']);
    $passwort  = trim($_POST['password']);

    if ($loginname === '' || $passwort === '') {
        $meldung = "Bitte alles ausfüllen.";
    } else {
        $login    = mysqli_real_escape_string($connection, $loginname);
        $ergebnis = mysqli_query($connection, "SELECT Passwort FROM TEAMCHEF WHERE Loginname = '$login'");
        $zeile    = $ergebnis ? mysqli_fetch_assoc($ergebnis) : null;

        if ($zeile && password_verify($passwort, $zeile['Passwort'])) {
            $_SESSION['teamchef_login'] = $loginname;
            header("Location: teamchef_dashboard.php");
            exit;
        } else {
            $meldung = "Loginname oder Passwort ist falsch.";
        }
    }
}
