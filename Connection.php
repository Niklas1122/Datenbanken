<?php
/*
Autor: Entwurf mit Codex, bitte im Projekt mit echtem Autor kennzeichnen.
Zweck: Einfache Testdatei fuer die Datenbankverbindung.
*/

require_once __DIR__ . '/inc/db.inc.php';

$connection = dbConnect();
echo "Datenbankverbindung erfolgreich.";
$connection->close();
