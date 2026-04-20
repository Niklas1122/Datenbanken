<?php
require_once __DIR__ . '/inc/app_functions.inc.php';

$connection = dbConnect();
requireOrganizerLogin();
$messageData = getMessageData();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    [$success, $message] = createRace(
        $connection,
        (int) $_SESSION["organizer_id"],
        $_POST["race_date"] ?? "",
        trim($_POST["start_location"] ?? ""),
        $_POST["race_kilometers"] ?? "",
        $_POST["elevation_meters"] ?? "",
        $_POST["max_gradient_percent"] ?? ""
    );
    redirectWithMessage("organizer_dashboard.php", $message, $success ? "success" : "error");
}

$races = getOrganizerRaces($connection, (int) $_SESSION["organizer_id"]);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Rennveranstalterbereich</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #999; padding: 6px; text-align: left; }
        .error { color: #a40000; }
        .success { color: #006400; }
    </style>
</head>
<body>
    <h1>Rennveranstalterbereich</h1>
    <p>Angemeldet als <?php echo h($_SESSION["organizer_name"] ?? ""); ?>.</p>
    <p><a href="index.php">Startseite</a> | <a href="logout.php">Abmelden</a></p>

    <?php if ($messageData["message"] !== ""): ?>
        <p class="<?php echo h($messageData["type"]); ?>"><?php echo h($messageData["message"]); ?></p>
    <?php endif; ?>

    <fieldset>
        <legend>Rennen anlegen</legend>
        <form method="post">
            <label>Datum: <input type="date" name="race_date" required></label><br>
            <label>Startort: <input type="text" name="start_location" required></label><br>
            <label>Kilometer: <input type="number" step="0.01" name="race_kilometers" required></label><br>
            <label>Hoehenmeter: <input type="number" name="elevation_meters" required></label><br>
            <label>Maximale Steigung in Prozent: <input type="number" step="0.01" name="max_gradient_percent" required></label><br>
            <button type="submit">Rennen speichern</button>
        </form>
    </fieldset>

    <h2>Meine Rennen</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Datum</th>
            <th>Startort</th>
            <th>Kilometer</th>
            <th>Hoehenmeter</th>
            <th>Max. Steigung</th>
            <th>Ergebnisse</th>
        </tr>
        <?php foreach ($races as $race): ?>
            <tr>
                <td><?php echo (int) $race["id"]; ?></td>
                <td><?php echo h($race["race_date"]); ?></td>
                <td><?php echo h($race["start_location"]); ?></td>
                <td><?php echo h((string) $race["race_kilometers"]); ?></td>
                <td><?php echo h((string) $race["elevation_meters"]); ?></td>
                <td><?php echo h((string) $race["max_gradient_percent"]); ?></td>
                <td><a href="race_results.php?race_id=<?php echo (int) $race["id"]; ?>">Ergebnisse erfassen</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
