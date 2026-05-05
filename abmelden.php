<?php
// Niklas
session_start();
session_unset();   // alle Session-Variablen löschen
session_destroy(); // Session komplett beenden

header("Location: index.php");
exit;
