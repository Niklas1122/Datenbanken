<?php
require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/funktionen.php';

if (isset($_POST['form_typ']) && $_POST['form_typ'] === 'team_registrierung') {
    $teamname  = trim($_POST['team_name'] ?? '');
    $vorname   = trim($_POST['teamchef_vorname'] ?? '');
    $nachname  = trim($_POST['teamchef_name'] ?? '');
    $loginname = trim($_POST['teamchef_login'] ?? '');
    $passwort  = trim($_POST['teamchef_passwort'] ?? '');

    if ($teamname === '' || $vorname === '' || $nachname === '' || $loginname === '' || $passwort === '') {
        $meldung = "Bitte alles ausfüllen.";
    } elseif (teamExistiert($connection, $teamname)) {
        $meldung = "Teamname ist schon vergeben.";
    } else {
        $fehler = speichereTeam($connection, $teamname, $vorname, $nachname, $loginname, password_hash($passwort, PASSWORD_DEFAULT));
        $meldung = $fehler === '' ? "Team wurde gespeichert." : $fehler;
    }
}
