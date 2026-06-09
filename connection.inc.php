<?php
// Niklas
$host = "localhost";   // Datenbankserver läuft auf demselben Rechner
$user = "gruppe20";    // MySQL-Benutzername
$password = "s*_C4u}y~if!"; // Passwort des Datenbankbenutzers
$db = "gruppe20";      // Name der Datenbank

$connection = mysqli_connect($host, $user, $password); // Verbindung zum MySQL-Server aufbauen
mysqli_select_db($connection, $db);                    // Datenbank "gruppe20" auswählen
mysqli_set_charset($connection, 'utf8mb4');            // Zeichenkodierung auf UTF-8 setzen (Umlaute etc.)
