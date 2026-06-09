<?php
// Niklas
session_start(); // Session starten, damit $_SESSION-Variablen verfügbar sind

$meldung = "";   // Variable für Erfolgs- oder Fehlermeldungen, die im HTML angezeigt werden
$label_stil = 'display:block; margin-bottom:10px;'; // CSS-Stil für alle Formular-Labels

// Formularverarbeitungs-Dateien einbinden, BEVOR HTML ausgegeben wird (wegen header()-Aufrufen)
include __DIR__ . '/registrierung_team.inc.php';        // verarbeitet Team-Registrierung
include __DIR__ . '/login_teamchef.inc.php';            // verarbeitet Teamchef-Login
include __DIR__ . '/registrierung_veranstalter.inc.php'; // verarbeitet Veranstalter-Registrierung
include __DIR__ . '/login_veranstalter.inc.php';        // verarbeitet Veranstalter-Login

// Ist ein Teamchef bereits angemeldet, direkt zum Teamchef-Dashboard weiterleiten
if (isset($_SESSION['teamchef_login'])) {
    header("Location: teamchef_dashboard.php");
    exit;
}

// Ist ein Veranstalter bereits angemeldet, direkt zum Veranstalter-Dashboard weiterleiten
if (isset($_SESSION['veranstalter_login'])) {
    header("Location: rennveranstalter_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"> <!-- Zeichenkodierung für Umlaute im Browser -->
    <title>RennradSV</title>
</head>
<body>
    <div>
        <h1>RennradSV</h1>
        <?php if ($meldung !== ""): ?> <!-- Meldung nur anzeigen, wenn eine vorhanden ist -->
            <p><?= $meldung; ?></p>
        <?php endif; ?>

        <!-- Formular 1: Neues Team anlegen -->
        <div>
            <h2>Neues Team anlegen</h2>
            <form action="#" method="post"> <!-- action="#" = Formular an dieselbe Seite senden -->
                <input type="hidden" name="form_typ" value="team_registrierung"> <!-- kennzeichnet welches Formular abgeschickt wurde -->
                <label style="<?= $label_stil; ?>">
                    Teamname<br>
                    <input type="text" name="team_name">
                </label>
                <label style="<?= $label_stil; ?>">
                    Vorname Teamchef<br>
                    <input type="text" name="teamchef_vorname">
                </label>
                <label style="<?= $label_stil; ?>">
                    Nachname Teamchef<br>
                    <input type="text" name="teamchef_name">
                </label>
                <label style="<?= $label_stil; ?>">
                    Loginname<br>
                    <input type="text" name="teamchef_login">
                </label>
                <label style="<?= $label_stil; ?>">
                    Passwort<br>
                    <input type="password" name="teamchef_passwort"> <!-- type="password" versteckt die Eingabe -->
                </label>
                <button type="submit">Team speichern</button>
            </form>
        </div>

        <!-- Formular 2: Teamchef anmelden -->
        <div>
            <h2>Teamchef anmelden</h2>
            <form action="#" method="post">
                <input type="hidden" name="form_typ" value="teamchef_login"> <!-- kennzeichnet Login-Formular -->
                <label style="<?= $label_stil; ?>">
                    Loginname<br>
                    <input type="text" name="login_name">
                </label>
                <label style="<?= $label_stil; ?>">
                    Passwort<br>
                    <input type="password" name="password">
                </label>
                <button type="submit">Anmelden</button>
            </form>
        </div>

        <!-- Formular 3: Rennveranstalter registrieren -->
        <div>
            <h2>Rennveranstalter registrieren</h2>
            <form action="#" method="post">
                <input type="hidden" name="form_typ" value="veranstalter_registrierung">
                <label style="<?= $label_stil; ?>">
                    Loginname<br>
                    <input type="text" name="veranstalter_name" required> <!-- required = Pflichtfeld im Browser -->
                </label>
                <label style="<?= $label_stil; ?>">
                    Passwort<br>
                    <input type="password" name="veranstalter_passwort" required>
                </label>
                <button type="submit">Registrieren</button>
            </form>
        </div>

        <!-- Formular 4: Rennveranstalter anmelden -->
        <div>
            <h2>Rennveranstalter anmelden</h2>
            <form action="#" method="post">
                <input type="hidden" name="form_typ" value="veranstalter_login">
                <label style="<?= $label_stil; ?>">
                    Loginname<br>
                    <input type="text" name="veranstalter_login">
                </label>
                <label style="<?= $label_stil; ?>">
                    Passwort<br>
                    <input type="password" name="veranstalter_passwort_login">
                </label>
                <button type="submit">Anmelden</button>
            </form>
        </div>
    </div>
</body>
</html>
