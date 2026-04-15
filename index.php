<?php
/*
Autor: Entwurf mit Codex, bitte im Projekt mit echtem Autor kennzeichnen.
Zweck: Startseite fuer Team-Neuregistrierung und Teamchef-Login.
*/

require_once __DIR__ . '/inc/team_functions.inc.php';

$message = $_GET["message"] ?? "";
$type = $_GET["type"] ?? "success";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $connection = dbConnect();

    if (isset($_POST["register_team"])) {
        $teamName = trim($_POST["team_name"] ?? "");
        $firstName = trim($_POST["first_name"] ?? "");
        $lastName = trim($_POST["last_name"] ?? "");
        $loginName = trim($_POST["login_name"] ?? "");
        $password = $_POST["password"] ?? "";

        if ($teamName === "" || $firstName === "" || $lastName === "" || $loginName === "" || $password === "") {
            $message = "Bitte alle Felder fuer die Team-Registrierung ausfuellen.";
            $type = "error";
        } else {
            [$success, $message] = createTeam($connection, $teamName, $firstName, $lastName, $loginName, $password);
            $type = $success ? "success" : "error";
        }
    }

    if (isset($_POST["login_teamchef"])) {
        $loginName = trim($_POST["teamchef_login_name"] ?? "");
        $password = $_POST["teamchef_password"] ?? "";

        if ($loginName === "" || $password === "") {
            $message = "Bitte Loginname und Kennwort eingeben.";
            $type = "error";
        } else {
            [$success, $message] = loginTeamchef($connection, $loginName, $password);
            if ($success) {
                $connection->close();
                header("Location: team_dashboard.php");
                exit;
            }
            $type = "error";
        }
    }

    $connection->close();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>RennradSV</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="page">
        <div class="hero">
            <h1>RennradSV</h1>
            <p>Auf dieser Startseite kannst du ein neues Team mit Teamchef anlegen oder dich als Teamchef anmelden.</p>
        </div>

        <?php if ($message !== ""): ?>
            <div class="message <?php echo h($type); ?>">
                <?php echo h($message); ?>
            </div>
        <?php endif; ?>

        <div class="grid">
            <section class="card">
                <h2>Neues Team anlegen</h2>
                <form method="post" action="index.php">
                    <label for="team_name">Teamname</label>
                    <input type="text" id="team_name" name="team_name" required>

                    <label for="first_name">Vorname Teamchef</label>
                    <input type="text" id="first_name" name="first_name" required>

                    <label for="last_name">Nachname Teamchef</label>
                    <input type="text" id="last_name" name="last_name" required>

                    <label for="login_name">Eindeutiger Loginname</label>
                    <input type="text" id="login_name" name="login_name" required>

                    <label for="password">Kennwort</label>
                    <input type="password" id="password" name="password" required>

                    <button type="submit" name="register_team">Team speichern</button>
                </form>
            </section>

            <section class="card">
                <h2>Teamchef anmelden</h2>
                <form method="post" action="index.php">
                    <label for="teamchef_login_name">Loginname</label>
                    <input type="text" id="teamchef_login_name" name="teamchef_login_name" required>

                    <label for="teamchef_password">Kennwort</label>
                    <input type="password" id="teamchef_password" name="teamchef_password" required>

                    <button type="submit" name="login_teamchef">Anmelden</button>
                </form>
            </section>
        </div>
    </div>
</body>
</html>
