<?php
// Niklas

session_start();      // bestehende Session des Benutzers laden
session_unset();      // alle Session-Variablen (z. B. teamchef_login) löschen
session_destroy();    // Session auf dem Server vollständig beenden

header("Location: index.php"); // Browser zur Startseite weiterleiten
exit;                           // weiteren PHP-Code nach dem Redirect stoppen
