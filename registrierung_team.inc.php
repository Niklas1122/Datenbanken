<?php
// Bedi
require_once __DIR__ . '/connection.inc.php';
require_once __DIR__ . '/funktionen.inc.php';

// Prüft ob das Formular zur Team-Registrierung abgesendet wurde
if (isset($_POST['form_typ']) && $_POST['form_typ'] === 'team_registrierung') {
    $teamname  = trim($_POST['team_name'] ?? '');
    $vorname   = trim($_POST['teamchef_vorname'] ?? '');
    $nachname  = trim($_POST['teamchef_name'] ?? '');
    $loginname = trim($_POST['teamchef_login'] ?? '');
    $passwort  = trim($_POST['teamchef_passwort'] ?? '');

    // Prüft ob alle Pflichtfelder ausgefüllt wurden
    if ($teamname === '' || $vorname === '' || $nachname === '' || $loginname === '' || $passwort === '') {
        $meldung = "Bitte alles ausfüllen.";
    } elseif (teamExistiert($connection, $teamname)) {
        // Verhindert doppelte Teamnamen
        $meldung = "Teamname ist schon vergeben.";
    } else {
        // Speichert das neue Team und verschlüsselt vorher das Passwort
        $fehler = speichereTeam($connection, $teamname, $vorname, $nachname, $loginname, password_hash($passwort, PASSWORD_DEFAULT));
        $meldung = $fehler === '' ? "Team wurde gespeichert." : $fehler;
    }
}
