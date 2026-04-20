<?php
require_once __DIR__ . '/inc/app_functions.inc.php';

$connection = dbConnect();
requireOrganizerLogin();

$raceId = (int) ($_GET["race_id"] ?? $_POST["race_id"] ?? 0);
$race = getRaceById($connection, $raceId);

if (!$race || (int) $race["organizer_id"] !== (int) $_SESSION["organizer_id"]) {
    redirectWithMessage("organizer_dashboard.php", "Rennen wurde nicht gefunden.", "error");
}

$messageData = getMessageData();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    [$success, $message] = saveRaceResults(
        $connection,
        $raceId,
        $_POST["placing"] ?? [],
        $_POST["medal"] ?? [],
        $_POST["ride_time"] ?? []
    );

    redirectWithMessage("race_results.php&race_id=" . $raceId, $message, $success ? "success" : "error");
}

$registrations = getRaceRegistrationsForResults($connection, $raceId);
$hasResults = raceHasResults($connection, $raceId);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Rennergebnisse</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #999; padding: 6px; text-align: left; }
        .error { color: #a40000; }
        .success { color: #006400; }
    </style>
</head>
<body>
    <h1>Ergebnisse fuer Rennen <?php echo (int) $race["id"]; ?></h1>
    <p><a href="organizer_dashboard.php">Zurueck</a></p>
    <p><?php echo h($race["race_date"] . " - " . $race["start_location"]); ?></p>

    <?php if ($messageData["message"] !== ""): ?>
        <p class="<?php echo h($messageData["type"]); ?>"><?php echo h($messageData["message"]); ?></p>
    <?php endif; ?>

    <?php if ($hasResults): ?>
        <p>Die Ergebnisse wurden bereits einmal erfasst und koennen nicht mehr geaendert werden.</p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="race_id" value="<?php echo $raceId; ?>">
        <table>
            <tr>
                <th>Startnummer</th>
                <th>Team</th>
                <th>Fahrer</th>
                <th>Platzierung</th>
                <th>Medaille</th>
                <th>Fahrtzeit</th>
            </tr>
            <?php foreach ($registrations as $registration): ?>
                <tr>
                    <td><?php echo (int) $registration["start_number"]; ?></td>
                    <td><?php echo h($registration["team_name"]); ?></td>
                    <td><?php echo h($registration["employee_id"] . " - " . $registration["rider_name"]); ?></td>
                    <td><input type="number" name="placing[<?php echo (int) $registration["registration_id"]; ?>]" <?php echo $hasResults ? "disabled" : "required"; ?>></td>
                    <td><input type="text" name="medal[<?php echo (int) $registration["registration_id"]; ?>]" <?php echo $hasResults ? "disabled" : "required"; ?>></td>
                    <td><input type="text" name="ride_time[<?php echo (int) $registration["registration_id"]; ?>]" placeholder="z. B. 03:21:11" <?php echo $hasResults ? "disabled" : "required"; ?>></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php if (!$hasResults): ?>
            <button type="submit">Ergebnisse speichern</button>
        <?php endif; ?>
    </form>
</body>
</html>
