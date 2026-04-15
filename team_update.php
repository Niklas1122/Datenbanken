<?php
/*
Autor: Entwurf mit Codex, bitte im Projekt mit echtem Autor kennzeichnen.
Zweck: Speichert geaenderte Teamdaten des angemeldeten Teamchefs.
*/

require_once __DIR__ . '/inc/team_functions.inc.php';

requireTeamchefLogin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: team_dashboard.php");
    exit;
}

$teamId = (int) ($_POST["team_id"] ?? 0);
$teamchefId = (int) ($_POST["teamchef_id"] ?? 0);
$teamName = trim($_POST["team_name"] ?? "");
$firstName = trim($_POST["first_name"] ?? "");
$lastName = trim($_POST["last_name"] ?? "");
$loginName = trim($_POST["login_name"] ?? "");
$newPassword = $_POST["new_password"] ?? "";

if (
    $teamId <= 0 ||
    $teamchefId <= 0 ||
    $teamName === "" ||
    $firstName === "" ||
    $lastName === "" ||
    $loginName === ""
) {
    header("Location: team_dashboard.php?message=Bitte+alle+Pflichtfelder+ausfuellen.&type=error");
    exit;
}

$connection = dbConnect();
[$success, $message] = updateTeamData(
    $connection,
    $teamchefId,
    $teamId,
    $teamName,
    $firstName,
    $lastName,
    $loginName,
    $newPassword
);
$connection->close();

$type = $success ? "success" : "error";
header("Location: team_dashboard.php?message=" . urlencode($message) . "&type=" . urlencode($type));
exit;

