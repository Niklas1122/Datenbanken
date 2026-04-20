<?php
require_once __DIR__ . '/Connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_name'], $_POST['password'])) {
    $loginname = $_POST['login_name'];
    $passwort = $_POST['password'];

    if ($loginname != "" && $passwort != "") {
        $sql = "SELECT * FROM TEAMCHEF WHERE Loginname = '$loginname' AND Passwort = '$passwort'";
        $ergebnis = mysqli_query($connection, $sql);

        if (mysqli_num_rows($ergebnis) > 0) {
            $meldung = "Teamchef Anmeldung erfolgreich.";
        } else {
            $meldung = "Loginname oder Passwort ist falsch.";
        }
    } else {
        $meldung = "Bitte alles ausfüllen.";
    }
}
?>
