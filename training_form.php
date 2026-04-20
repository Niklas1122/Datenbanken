<?php
require_once __DIR__ . '/inc/app_functions.inc.php';

$connection = dbConnect();
requireTeamchefLogin();

$teamId = (int) $_SESSION["team_id"];
$riderId = (int) ($_GET["rider_id"] ?? 0);
$rider = getRiderById($connection, $teamId, $riderId);
$goals = getTrainingGoals($connection);
$messageData = getMessageData();

if (!$rider) {
    redirectWithMessage("team_dashboard.php", "Fahrer wurde nicht gefunden.", "error");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    [$success, $message] = createTraining(
        $connection,
        $teamId,
        $riderId,
        $_POST["training_date"] ?? "",
        $_POST["kilometers"] ?? "",
        (int) ($_POST["goal_id"] ?? 0)
    );

    if ($success) {
        redirectWithMessage("team_dashboard.php", $message);
    }

    $messageData = ["message" => $message, "type" => "error"];
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Training erfassen</title>
</head>
<body>
    <h1>Training fuer <?php echo h($rider["employee_id"] . " - " . $rider["rider_name"]); ?></h1>
    <p><a href="team_dashboard.php">Zurueck</a></p>

    <?php if ($messageData["message"] !== ""): ?>
        <p class="<?php echo h($messageData["type"]); ?>"><?php echo h($messageData["message"]); ?></p>
    <?php endif; ?>

    <p>Verwendetes Fahrrad: <?php echo h($rider["brand"] . ", 28 Zoll, " . $rider["tire_width"] . " mm"); ?></p>

    <form method="post">
        <label>Datum:
            <input type="date" name="training_date" required>
        </label><br>
        <label>Kilometer:
            <input type="number" step="0.01" name="kilometers" required>
        </label><br>
        <label>Trainingsziel:
            <select name="goal_id" required>
                <?php foreach ($goals as $goal): ?>
                    <option value="<?php echo (int) $goal["id"]; ?>"><?php echo h($goal["goal_name"]); ?></option>
                <?php endforeach; ?>
            </select>
        </label><br>
        <button type="submit">Speichern</button>
    </form>
</body>
</html>
