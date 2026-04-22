<?php
require_once __DIR__ . '/Connection.php';

if (isset($_POST['veranstalter_login'], $_POST['veranstalter_passwort_login'])) {
    $loginname = $_POST['veranstalter_login'];
    $passwort = $_POST['veranstalter_passwort_login'];

    if ($loginname != "" && $passwort != "") {
        $sql = "SELECT * FROM RENNVERANSTALTER WHERE Loginname = '$loginname' AND Passwort = '$passwort'";
        $ergebnis = mysqli_query($connection, $sql);

        if (mysqli_num_rows($ergebnis) > 0) {
            $meldung = "Anmeldung erfolgreich.";
        } else {
            $meldung = "Loginname oder Passwort ist falsch.";
        }
    } else {
        $meldung = "Bitte alles ausfüllen.";
    }
}
?>
