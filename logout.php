<?php
require_once __DIR__ . '/inc/app_functions.inc.php';

logoutAll();
session_start();
redirectWithMessage("index.php", "Abmeldung erfolgreich.");
