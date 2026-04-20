<?php
require_once __DIR__ . '/inc/app_functions.inc.php';

$connection = dbConnect();
$messageData = getMessageData();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "register_team") {
        [$success, $message] = createTeam(
            $connection,
            trim($_POST["team_name"] ?? ""),
            trim($_POST["first_name"] ?? ""),
            trim($_POST["last_name"] ?? ""),
            trim($_POST["login_name"] ?? ""),
            $_POST["password"] ?? ""
        );
        redirectWithMessage("index.php", $message, $success ? "success" : "error");
    }

    if ($action === "login_teamchef") {
        [$success, $message] = loginTeamchef(
            $connection,
            trim($_POST["login_name"] ?? ""),
            $_POST["password"] ?? ""
        );

        if ($success) {
            header("Location: team_dashboard.php");
            exit;
        }

        redirectWithMessage("index.php", $message, "error");
    }

    if ($action === "register_organizer") {
        [$success, $message] = createOrganizer(
            $connection,
            trim($_POST["organizer_name"] ?? ""),
            $_POST["password"] ?? ""
        );
        redirectWithMessage("index.php", $message, $success ? "success" : "error");
    }

    if ($action === "login_organizer") {
        [$success, $message] = loginOrganizer(
            $connection,
            trim($_POST["organizer_name"] ?? ""),
            $_POST["password"] ?? ""
        );

        if ($success) {
            header("Location: organizer_dashboard.php");
            exit;
        }

        redirectWithMessage("index.php", $message, "error");
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>RennradSV</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        fieldset { margin-bottom: 20px; padding: 15px; }
        label { display: block; margin: 8px 0; }
        .error { color: #a40000; }
        .success { color: #006400; }
        .pink-button { background: hotpink; color: #fff; border: 0; padding: 8px 12px; }
    </style>
</head>
<body>
    <h1>RennradSV</h1>
    <p>Startseite fuer Teams, Teamchefs und Rennveranstalter.</p>

    <?php if ($messageData["message"] !== ""): ?>
        <p class="<?php echo h($messageData["type"]); ?>">
            <?php echo h($messageData["message"]); ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($_SESSION["teamchef_id"])): ?>
        <p>Angemeldet als Teamchef von <?php echo h($_SESSION["team_name"] ?? ""); ?>.
            <a href="team_dashboard.php">Zum Teambereich</a></p>
    <?php endif; ?>

    <?php if (!empty($_SESSION["organizer_id"])): ?>
        <p>Angemeldet als Rennveranstalter <?php echo h($_SESSION["organizer_name"] ?? ""); ?>.
            <a href="organizer_dashboard.php">Zum Veranstalterbereich</a></p>
    <?php endif; ?>

    <fieldset>
        <legend>Neues Team anlegen</legend>
        <form method="post">
            <input type="hidden" name="action" value="register_team">
            <label>Teamname: <input type="text" name="team_name" required></label>
            <label>Vorname Teamchef: <input type="text" name="first_name" required></label>
            <label>Name Teamchef: <input type="text" name="last_name" required></label>
            <label>Loginname: <input type="text" name="login_name" required></label>
            <label>Kennwort: <input type="password" name="password" required></label>
            <button type="submit">Team speichern</button>
        </form>
    </fieldset>

    <fieldset>
        <legend>Teamchef anmelden</legend>
        <form method="post">
            <input type="hidden" name="action" value="login_teamchef">
            <label>Loginname: <input type="text" name="login_name" required></label>
            <label>Kennwort: <input type="password" name="password" required></label>
            <button type="submit">Anmelden</button>
        </form>
    </fieldset>

    <fieldset>
        <legend>Rennveranstalter registrieren</legend>
        <form method="post">
            <input type="hidden" name="action" value="register_organizer">
            <label>Eindeutiger Name: <input type="text" name="organizer_name" required></label>
            <label>Kennwort: <input type="password" name="password" required></label>
            <button type="submit">Registrieren</button>
        </form>
    </fieldset>

    <fieldset>
        <legend>Rennveranstalter anmelden</legend>
        <form method="post">
            <input type="hidden" name="action" value="login_organizer">
            <label>Name: <input type="text" name="organizer_name" required></label>
            <label>Kennwort: <input type="password" name="password" required></label>
            <button type="submit" class="pink-button">Anmelden</button>
        </form>
    </fieldset>
</body>
</html>
