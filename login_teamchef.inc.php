<?php
// Bedi
require_once __DIR__ . '/connection.inc.php';

// Prüft ob das Login-Formular abgeschickt wurde
if (isset($_POST['form_typ'], $_POST['login_name'], $_POST['password'])) {
    $loginname = trim($_POST['login_name']);
    $passwort  = trim($_POST['password']);

    // Meldung falls Eingaben fehlen
    if ($loginname === '' || $passwort === '') {
        $meldung = "Bitte alles ausfüllen.";
    } else {
        // Holt das gespeicherte Passwort zum angegebenen Loginnamen
        $stmt = mysqli_prepare($connection, "SELECT Passwort FROM TEAMCHEF WHERE Loginname = ?");
        mysqli_stmt_bind_param($stmt, 's', $loginname);
        mysqli_stmt_execute($stmt);
        $ergebnis = mysqli_stmt_get_result($stmt);
        $zeile    = $ergebnis ? mysqli_fetch_assoc($ergebnis) : null;

        // Bei richtigem Passwort wird die Session gesetzt und weitergeleitet
        if ($zeile && password_verify($passwort, $zeile['Passwort'])) {
            $_SESSION['teamchef_login'] = $loginname;
            header("Location: teamchef_dashboard.php");
            exit;
        } else {
            $meldung = "Loginname oder Passwort ist falsch.";
        }
    }
}
