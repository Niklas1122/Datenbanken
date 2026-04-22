<?php
require_once __DIR__ . '/../funktionen/Connection.php';

if (
    isset($_POST['form_typ'], $_POST['login_name'], $_POST['password']) &&
    $_POST['form_typ'] === 'teamchef_login'
) {
    $loginname = trim($_POST['login_name']);
    $passwort = trim($_POST['password']);

    if ($loginname !== "" && $passwort !== "") {
        $loginname_sicher = mysqli_real_escape_string($connection, $loginname);
        $passwort_sicher = mysqli_real_escape_string($connection, $passwort);
        $sql = "SELECT * FROM TEAMCHEF WHERE Loginname = '$loginname_sicher' AND Passwort = '$passwort_sicher'";
        $ergebnis = mysqli_query($connection, $sql);

        if (mysqli_num_rows($ergebnis) > 0) {
            $_SESSION['teamchef_login'] = $loginname;
            header("Location: ../projekt/teamchef_dashboard.php");
            exit;
        } else {
            $meldung = "Loginname oder Passwort ist falsch.";
        }
    } else {
        $meldung = "Bitte alles ausfüllen.";
    }
}
