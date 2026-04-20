<?php
/*
Autor: Entwurf mit Codex, bitte im Projekt mit echtem Autor kennzeichnen.
Zweck: Zentrale Datenbankverbindung fuer die PHP-Seiten.
*/

function dbConnect(): mysqli
{
    $host = "127.0.0.1";
    $user = "gruppe20";
    $password = "s*_C4u}y~if!";
    $database = "gruppe20";

    $connection = new mysqli($host, $user, $password, $database);
    if ($connection->connect_error) {
        die("Datenbankverbindung fehlgeschlagen: " . $connection->connect_error);
    }

    $connection->set_charset("utf8mb4");
    return $connection;
}

