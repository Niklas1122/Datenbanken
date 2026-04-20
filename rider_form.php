<?php
require_once __DIR__ . '/inc/app_functions.inc.php';

$connection = dbConnect();
requireTeamchefLogin();

$teamId = (int) $_SESSION["team_id"];
$riderId = isset($_GET["rider_id"]) ? (int) $_GET["rider_id"] : 0;
$rider = $riderId > 0 ? getRiderById($connection, $teamId, $riderId) : null;
$messageData = getMessageData();

if ($riderId > 0 && !$rider) {
    redirectWithMessage("team_dashboard.php", "Fahrer wurde nicht gefunden.", "error");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    [$success, $message] = saveRider(
        $connection,
        $teamId,
        trim($_POST["employee_id"] ?? ""),
        trim($_POST["rider_name"] ?? ""),
        trim($_POST["address_text"] ?? ""),
        trim($_POST["phone"] ?? ""),
        trim($_POST["brand"] ?? ""),
        trim($_POST["tire_width"] ?? ""),
        $riderId > 0 ? $riderId : null
    );

    if ($success) {
        redirectWithMessage("team_dashboard.php", $message);
    }

    $messageData = ["message" => $message, "type" => "error"];
    $rider = $riderId > 0 ? getRiderById($connection, $teamId, $riderId) : null;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Fahrer speichern</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .error { color: #a40000; }
    </style>
</head>
<body>
    <h1><?php echo $riderId > 0 ? "Fahrer aendern" : "Fahrer anlegen"; ?></h1>
    <p><a href="team_dashboard.php">Zurueck</a></p>

    <?php if ($messageData["message"] !== ""): ?>
        <p class="<?php echo h($messageData["type"]); ?>"><?php echo h($messageData["message"]); ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Mitarbeiter-ID:
            <input type="text" name="employee_id" value="<?php echo h($rider["employee_id"] ?? ""); ?>" <?php echo $riderId > 0 ? "readonly" : ""; ?> required>
        </label><br>
        <label>Name:
            <input type="text" name="rider_name" value="<?php echo h($rider["rider_name"] ?? ""); ?>" required>
        </label><br>
        <label>Adresse:
            <input type="text" name="address_text" value="<?php echo h($rider["address_text"] ?? ""); ?>" required>
        </label><br>
        <label>Telefon:
            <input type="text" name="phone" value="<?php echo h($rider["phone"] ?? ""); ?>" required>
        </label><br>
        <label>Radmarke:
            <input type="text" name="brand" value="<?php echo h($rider["brand"] ?? ""); ?>" required>
        </label><br>
        <label>Reifengroesse:
            <input type="text" value="28 Zoll" readonly>
        </label><br>
        <label>Reifenbreite:
            <input type="number" step="0.01" name="tire_width" value="<?php echo h((string) ($rider["tire_width"] ?? "")); ?>" required>
        </label><br>
        <button type="submit">Speichern</button>
    </form>
</body>
</html>
