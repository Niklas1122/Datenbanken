<?php
/*
Autor: Entwurf mit Codex, bitte im Projekt mit echtem Autor kennzeichnen.
Zweck: Einfache Seite fuer Teamchefs zum Anpassen der Teamdaten.
*/

require_once __DIR__ . '/inc/team_functions.inc.php';

requireTeamchefLogin();

$connection = dbConnect();
$teamData = getCurrentTeamData($connection, (int) $_SESSION["teamchef_id"]);
$connection->close();

if (!$teamData) {
    header("Location: logout.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Teamdaten pflegen</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="page">
        <div class="hero">
            <h1>Teamdaten pflegen</h1>
            <p>Angemeldet als <?php echo h($_SESSION["teamchef_name"] ?? ""); ?> fuer das Team <?php echo h($_SESSION["team_name"] ?? ""); ?>.</p>
            <div class="top-links">
                <a class="button-link" href="logout.php">Abmelden</a>
            </div>
        </div>

        <?php if (!empty($_GET["message"])): ?>
            <div class="message <?php echo h($_GET["type"] ?? "success"); ?>">
                <?php echo h($_GET["message"]); ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <h2>Team und Teamchef aendern</h2>
            <form method="post" action="team_update.php">
                <input type="hidden" name="team_id" value="<?php echo (int) $teamData["team_id"]; ?>">
                <input type="hidden" name="teamchef_id" value="<?php echo (int) $teamData["id"]; ?>">

                <label for="team_name">Teamname</label>
                <input type="text" id="team_name" name="team_name" value="<?php echo h($teamData["team_name"]); ?>" required>

                <label for="first_name">Vorname</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo h($teamData["first_name"]); ?>" required>

                <label for="last_name">Nachname</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo h($teamData["last_name"]); ?>" required>

                <label for="login_name">Loginname</label>
                <input type="text" id="login_name" name="login_name" value="<?php echo h($teamData["login_name"]); ?>" required>

                <label for="new_password">Neues Kennwort</label>
                <input type="password" id="new_password" name="new_password">

                <button type="submit" name="save_team_data">Speichern</button>
            </form>
        </section>
    </div>
</body>
</html>

