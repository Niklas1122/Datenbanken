<?php
/*
Autor: Entwurf mit Codex, bitte im Projekt mit echtem Autor kennzeichnen.
Zweck: Beendet die Session des Teamchefs.
*/

session_start();
$_SESSION = [];
session_destroy();

header("Location: index.php?message=Abmeldung+erfolgreich.&type=success");
exit;

