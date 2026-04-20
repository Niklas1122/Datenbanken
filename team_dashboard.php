<?php
require_once __DIR__ . '/inc/app_functions.inc.php';

$connection = dbConnect();
requireTeamchefLogin();

$teamchefId = (int) $_SESSION["teamchef_id"];
$teamId = (int) $_SESSION["team_id"];
$messageData = getMessageData();
$teamData = getCurrentTeamData($connection, $teamchefId);

$registrationRaceId = (int) ($_POST["race_id"] ?? $_GET["race_id"] ?? 0);
$registrationCount = max(0, (int) ($_POST["registration_count"] ?? 0));

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "update_team") {
        [$success, $message] = updateTeamData(
            $connection,
            $teamchefId,
            $teamId,
            trim($_POST["team_name"] ?? ""),
            trim($_POST["first_name"] ?? ""),
            trim($_POST["last_name"] ?? ""),
            trim($_POST["login_name"] ?? ""),
            $_POST["new_password"] ?? ""
        );
        if ($success) {
            header("Location: team_dashboard.php?message=" . urlencode($message) . "&type=success");
            exit;
        }
        $messageData = ["message" => $message, "type" => "error"];
        $teamData = getCurrentTeamData($connection, $teamchefId);
    }

    if ($action === "delete_rider") {
        [$success, $message] = deleteRider($connection, $teamId, (int) ($_POST["rider_id"] ?? 0));
        redirectWithMessage("team_dashboard.php", $message, $success ? "success" : "error");
    }

    if ($action === "prepare_registration") {
        $messageData = ["message" => "Bitte Fahrer fuer das Rennen auswaehlen und speichern.", "type" => "success"];
    }

    if ($action === "save_registration") {
        [$success, $message] = registerRidersToRace(
            $connection,
            $teamId,
            (int) ($_POST["race_id"] ?? 0),
            $_POST["rider_ids"] ?? []
        );
        redirectWithMessage("team_dashboard.php", $message, $success ? "success" : "error");
    }

    if ($action === "copy_registration") {
        [$success, $message] = copyRegistrationsForTeam(
            $connection,
            $teamId,
            (int) ($_POST["source_race_id"] ?? 0),
            (int) ($_POST["target_race_id"] ?? 0)
        );
        redirectWithMessage("team_dashboard.php", $message, $success ? "success" : "error");
    }
}

$teamData = $teamData ?: getCurrentTeamData($connection, $teamchefId);
$riders = getTeamRiders($connection, $teamId);
$futureRaces = getFutureRaces($connection);
$trainings = getTeamTrainings($connection, $teamId);
$raceOverview = getTeamRaceOverview($connection, $teamId);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Teambereich</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #999; padding: 6px; text-align: left; }
        fieldset { margin-bottom: 20px; }
        .error { color: #a40000; }
        .success { color: #006400; }
    </style>
</head>
<body>
    <h1>Teambereich</h1>
    <p>Angemeldet als <?php echo h($_SESSION["teamchef_name"] ?? ""); ?> bei Team <?php echo h($_SESSION["team_name"] ?? ""); ?>.</p>
    <p><a href="index.php">Startseite</a> | <a href="logout.php">Abmelden</a></p>

    <?php if ($messageData["message"] !== ""): ?>
        <p class="<?php echo h($messageData["type"]); ?>"><?php echo h($messageData["message"]); ?></p>
    <?php endif; ?>

    <fieldset>
        <legend>Teamdaten pflegen</legend>
        <form method="post">
            <input type="hidden" name="action" value="update_team">
            <label>Teamname: <input type="text" name="team_name" value="<?php echo h($teamData["team_name"] ?? ""); ?>" required></label><br>
            <label>Vorname Teamchef: <input type="text" name="first_name" value="<?php echo h($teamData["first_name"] ?? ""); ?>" required></label><br>
            <label>Name Teamchef: <input type="text" name="last_name" value="<?php echo h($teamData["last_name"] ?? ""); ?>" required></label><br>
            <label>Loginname: <input type="text" name="login_name" value="<?php echo h($teamData["login_name"] ?? ""); ?>" required></label><br>
            <label>Neues Kennwort (optional): <input type="password" name="new_password"></label><br>
            <button type="submit">Speichern</button>
        </form>
    </fieldset>

    <h2>Fahrer</h2>
    <p><a href="rider_form.php">Neuen Fahrer anlegen</a></p>
    <table>
        <tr>
            <th>Mitarbeiter-ID</th>
            <th>Name</th>
            <th>Adresse</th>
            <th>Telefon</th>
            <th>Rad</th>
            <th>Aktionen</th>
        </tr>
        <?php foreach ($riders as $rider): ?>
            <tr>
                <td><?php echo h($rider["employee_id"]); ?></td>
                <td><?php echo h($rider["rider_name"]); ?></td>
                <td><?php echo h($rider["address_text"]); ?></td>
                <td><?php echo h($rider["phone"]); ?></td>
                <td><?php echo h($rider["brand"]); ?>, 28 Zoll, <?php echo h((string) $rider["tire_width"]); ?> mm</td>
                <td>
                    <a href="rider_form.php?rider_id=<?php echo (int) $rider["id"]; ?>">Bearbeiten</a>
                    |
                    <a href="training_form.php?rider_id=<?php echo (int) $rider["id"]; ?>">Training erfassen</a>
                    |
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="action" value="delete_rider">
                        <input type="hidden" name="rider_id" value="<?php echo (int) $rider["id"]; ?>">
                        <button type="submit" onclick="return confirm('Fahrer wirklich loeschen?');">Loeschen</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <fieldset>
        <legend>Fahrer zu zukuenftigem Rennen anmelden</legend>
        <form method="post">
            <input type="hidden" name="action" value="prepare_registration">
            <label>Rennen:
                <select name="race_id" required>
                    <option value="">Bitte waehlen</option>
                    <?php foreach ($futureRaces as $race): ?>
                        <option value="<?php echo (int) $race["id"]; ?>" <?php echo $registrationRaceId === (int) $race["id"] ? "selected" : ""; ?>>
                            <?php echo h($race["id"] . " - " . $race["race_date"] . " - " . $race["start_location"]); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Wie viele Fahrer sollen gemeldet werden?
                <input type="number" min="1" name="registration_count" value="<?php echo $registrationCount > 0 ? (int) $registrationCount : 1; ?>" required>
            </label>
            <button type="submit">Tabelle erzeugen</button>
        </form>
    </fieldset>

    <?php if ($registrationRaceId > 0 && $registrationCount > 0): ?>
        <fieldset>
            <legend>Meldetabelle</legend>
            <form method="post">
                <input type="hidden" name="action" value="save_registration">
                <input type="hidden" name="race_id" value="<?php echo $registrationRaceId; ?>">
                <table>
                    <tr>
                        <th>Zeile</th>
                        <th>Fahrer (ID@@@Name)</th>
                    </tr>
                    <?php for ($index = 0; $index < $registrationCount + 5; $index++): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td>
                                <select name="rider_ids[]">
                                    <option value="">Leer</option>
                                    <?php foreach ($riders as $rider): ?>
                                        <option value="<?php echo (int) $rider["id"]; ?>">
                                            <?php echo h($rider["employee_id"] . "@@@" . $rider["rider_name"]); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    <?php endfor; ?>
                </table>
                <button type="submit">Meldungen speichern</button>
            </form>
        </fieldset>
    <?php endif; ?>

    <fieldset>
        <legend>Meldungen auf neues Rennen kopieren</legend>
        <form method="post">
            <input type="hidden" name="action" value="copy_registration">
            <label>Von Rennen:
                <select name="source_race_id" required>
                    <option value="">Bitte waehlen</option>
                    <?php foreach ($raceOverview as $row): ?>
                        <option value="<?php echo (int) $row["race_id"]; ?>">
                            <?php echo h($row["race_id"] . " - " . $row["race_date"] . " - " . $row["start_location"]); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Auf Zukunftsrennen:
                <select name="target_race_id" required>
                    <option value="">Bitte waehlen</option>
                    <?php foreach ($futureRaces as $race): ?>
                        <option value="<?php echo (int) $race["id"]; ?>">
                            <?php echo h($race["id"] . " - " . $race["race_date"] . " - " . $race["start_location"]); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit">Meldungen kopieren</button>
        </form>
    </fieldset>

    <h2>Trainingsauswertung</h2>
    <table>
        <tr>
            <th>Datum</th>
            <th>Fahrer</th>
            <th>Ziel</th>
            <th>Kilometer</th>
            <th>Fahrrad</th>
        </tr>
        <?php foreach ($trainings as $training): ?>
            <tr>
                <td><?php echo h($training["training_date"]); ?></td>
                <td><?php echo h($training["employee_id"] . " - " . $training["rider_name"]); ?></td>
                <td><?php echo h($training["goal_name"]); ?></td>
                <td><?php echo h((string) $training["kilometers"]); ?></td>
                <td><?php echo h($training["brand"] . ", 28 Zoll, " . $training["tire_width"] . " mm"); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>Rennergebnisse und Meldungen</h2>
    <table>
        <tr>
            <th>Rennen</th>
            <th>Startnummer</th>
            <th>Fahrer</th>
            <th>Platzierung</th>
            <th>Medaille</th>
            <th>Fahrtzeit</th>
        </tr>
        <?php foreach ($raceOverview as $row): ?>
            <tr>
                <td><?php echo h($row["race_id"] . " - " . $row["race_date"] . " - " . $row["start_location"]); ?></td>
                <td><?php echo h((string) $row["start_number"]); ?></td>
                <td><?php echo h($row["employee_id"] . " - " . $row["rider_name"]); ?></td>
                <td><?php echo h((string) ($row["placing"] ?? "")); ?></td>
                <td><?php echo h((string) ($row["medal"] ?? "")); ?></td>
                <td><?php echo h((string) ($row["ride_time"] ?? "")); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
