<?php
/*
Autor: Entwurf mit Codex, bitte im Projekt mit echtem Autor kennzeichnen.
Zweck: Gemeinsame Helfer fuer Teamchef-, Fahrer-, Training-, Rennen- und Ergebnislogik.
*/

require_once __DIR__ . '/team_functions.inc.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirectWithMessage(string $url, string $message, string $type = "success"): void
{
    header("Location: " . $url . "?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit;
}

function getMessageData(): array
{
    return [
        "message" => $_GET["message"] ?? "",
        "type" => $_GET["type"] ?? "success",
    ];
}

function requireOrganizerLogin(): void
{
    if (empty($_SESSION["organizer_id"])) {
        redirectWithMessage("index.php", "Bitte zuerst als Rennveranstalter anmelden.", "error");
    }
}

function logoutAll(): void
{
    $_SESSION = [];
    session_destroy();
}

function createOrganizer(mysqli $connection, string $organizerName, string $password): array
{
    $statement = $connection->prepare("SELECT id FROM organizers WHERE organizer_name = ?");
    $statement->bind_param("s", $organizerName);
    $statement->execute();
    $statement->store_result();
    if ($statement->num_rows > 0) {
        $statement->close();
        return [false, "Der Name des Rennveranstalters ist bereits vergeben."];
    }
    $statement->close();

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $statement = $connection->prepare(
        "INSERT INTO organizers (organizer_name, password_hash) VALUES (?, ?)"
    );
    $statement->bind_param("ss", $organizerName, $passwordHash);
    $statement->execute();
    $statement->close();

    return [true, "Rennveranstalter wurde registriert."];
}

function loginOrganizer(mysqli $connection, string $organizerName, string $password): array
{
    $statement = $connection->prepare(
        "SELECT id, organizer_name, password_hash FROM organizers WHERE organizer_name = ?"
    );
    $statement->bind_param("s", $organizerName);
    $statement->execute();
    $result = $statement->get_result();
    $organizer = $result->fetch_assoc();
    $statement->close();

    if (!$organizer || !password_verify($password, $organizer["password_hash"])) {
        return [false, "Name oder Kennwort des Rennveranstalters sind falsch."];
    }

    $_SESSION["organizer_id"] = (int) $organizer["id"];
    $_SESSION["organizer_name"] = $organizer["organizer_name"];

    return [true, "Anmeldung als Rennveranstalter erfolgreich."];
}

function getTeamRiders(mysqli $connection, int $teamId): array
{
    $statement = $connection->prepare(
        "SELECT r.id, r.employee_id, r.rider_name, r.address_text, r.phone,
                b.brand, b.tire_size, b.tire_width
         FROM riders r
         INNER JOIN bikes b ON b.id = r.bike_id
         WHERE r.team_id = ?
         ORDER BY r.employee_id"
    );
    $statement->bind_param("i", $teamId);
    $statement->execute();
    $result = $statement->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $statement->close();

    return $rows;
}

function getRiderById(mysqli $connection, int $teamId, int $riderId): ?array
{
    $statement = $connection->prepare(
        "SELECT r.id, r.team_id, r.employee_id, r.rider_name, r.address_text, r.phone, r.bike_id,
                b.brand, b.tire_size, b.tire_width
         FROM riders r
         INNER JOIN bikes b ON b.id = r.bike_id
         WHERE r.team_id = ? AND r.id = ?"
    );
    $statement->bind_param("ii", $teamId, $riderId);
    $statement->execute();
    $result = $statement->get_result();
    $row = $result->fetch_assoc();
    $statement->close();

    return $row ?: null;
}

function saveRider(
    mysqli $connection,
    int $teamId,
    string $employeeId,
    string $riderName,
    string $addressText,
    string $phone,
    string $brand,
    string $tireWidth,
    ?int $riderId = null
): array {
    if ($employeeId === "" || $riderName === "" || $addressText === "" || $phone === "" || $brand === "" || $tireWidth === "") {
        return [false, "Bitte alle Fahrerfelder ausfuellen."];
    }

    if ($riderId === null) {
        $statement = $connection->prepare(
            "SELECT id FROM riders WHERE team_id = ? AND employee_id = ?"
        );
        $statement->bind_param("is", $teamId, $employeeId);
        $statement->execute();
        $statement->store_result();
        if ($statement->num_rows > 0) {
            $statement->close();
            return [false, "Diese Mitarbeiter-ID existiert im Team bereits."];
        }
        $statement->close();
    } else {
        $existing = getRiderById($connection, $teamId, $riderId);
        if (!$existing) {
            return [false, "Fahrer wurde nicht gefunden."];
        }
        $employeeId = $existing["employee_id"];
    }

    $connection->begin_transaction();

    try {
        if ($riderId === null) {
            $statement = $connection->prepare(
                "INSERT INTO bikes (team_id, brand, tire_size, tire_width) VALUES (?, ?, 28, ?)"
            );
            $statement->bind_param("isd", $teamId, $brand, $tireWidth);
            $statement->execute();
            $bikeId = $connection->insert_id;
            $statement->close();

            $statement = $connection->prepare(
                "INSERT INTO riders (team_id, employee_id, rider_name, address_text, phone, bike_id)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $statement->bind_param("issssi", $teamId, $employeeId, $riderName, $addressText, $phone, $bikeId);
            $statement->execute();
            $statement->close();
        } else {
            $existing = getRiderById($connection, $teamId, $riderId);

            $statement = $connection->prepare(
                "UPDATE bikes SET brand = ?, tire_width = ? WHERE id = ?"
            );
            $statement->bind_param("sdi", $brand, $tireWidth, $existing["bike_id"]);
            $statement->execute();
            $statement->close();

            $statement = $connection->prepare(
                "UPDATE riders
                 SET rider_name = ?, address_text = ?, phone = ?
                 WHERE id = ? AND team_id = ?"
            );
            $statement->bind_param("sssii", $riderName, $addressText, $phone, $riderId, $teamId);
            $statement->execute();
            $statement->close();
        }

        $connection->commit();
        return [true, "Fahrerdaten wurden gespeichert."];
    } catch (Throwable $exception) {
        $connection->rollback();
        return [false, "Speichern fehlgeschlagen: " . $exception->getMessage()];
    }
}

function deleteRider(mysqli $connection, int $teamId, int $riderId): array
{
    $rider = getRiderById($connection, $teamId, $riderId);
    if (!$rider) {
        return [false, "Fahrer wurde nicht gefunden."];
    }

    $statement = $connection->prepare("DELETE FROM riders WHERE id = ? AND team_id = ?");
    $statement->bind_param("ii", $riderId, $teamId);
    $statement->execute();
    $statement->close();

    return [true, "Fahrer wurde geloescht."];
}

function getTrainingGoals(mysqli $connection): array
{
    $result = $connection->query("SELECT id, goal_name FROM training_goals ORDER BY id");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function createTraining(
    mysqli $connection,
    int $teamId,
    int $riderId,
    string $trainingDate,
    string $kilometers,
    int $goalId
): array {
    $rider = getRiderById($connection, $teamId, $riderId);
    if (!$rider) {
        return [false, "Fahrer wurde nicht gefunden."];
    }

    if ($trainingDate === "" || $kilometers === "") {
        return [false, "Bitte Datum und Kilometer angeben."];
    }

    $statement = $connection->prepare(
        "INSERT INTO trainings (rider_id, bike_id, training_date, kilometers, goal_id)
         VALUES (?, ?, ?, ?, ?)"
    );
    $statement->bind_param("iisdi", $riderId, $rider["bike_id"], $trainingDate, $kilometers, $goalId);

    try {
        $statement->execute();
        $statement->close();
        return [true, "Training wurde gespeichert."];
    } catch (Throwable $exception) {
        $statement->close();
        return [false, "Training konnte nicht gespeichert werden: " . $exception->getMessage()];
    }
}

function getTeamTrainings(mysqli $connection, int $teamId): array
{
    $statement = $connection->prepare(
        "SELECT t.training_date, t.kilometers, g.goal_name, r.employee_id, r.rider_name,
                b.brand, b.tire_width
         FROM trainings t
         INNER JOIN riders r ON r.id = t.rider_id
         INNER JOIN training_goals g ON g.id = t.goal_id
         INNER JOIN bikes b ON b.id = t.bike_id
         WHERE r.team_id = ?
         ORDER BY t.training_date DESC, r.employee_id"
    );
    $statement->bind_param("i", $teamId);
    $statement->execute();
    $result = $statement->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $statement->close();

    return $rows;
}

function createRace(
    mysqli $connection,
    int $organizerId,
    string $raceDate,
    string $startLocation,
    string $kilometers,
    string $elevationMeters,
    string $maxGradientPercent
): array {
    if ($raceDate === "" || $startLocation === "" || $kilometers === "" || $elevationMeters === "" || $maxGradientPercent === "") {
        return [false, "Bitte alle Renndaten ausfuellen."];
    }

    $statement = $connection->prepare(
        "INSERT INTO races (organizer_id, race_date, start_location, race_kilometers, elevation_meters, max_gradient_percent)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $statement->bind_param("issdid", $organizerId, $raceDate, $startLocation, $kilometers, $elevationMeters, $maxGradientPercent);
    $statement->execute();
    $raceId = $connection->insert_id;
    $statement->close();

    return [true, "Rennen wurde angelegt. Vergebene ID: " . $raceId];
}

function getFutureRaces(mysqli $connection): array
{
    $result = $connection->query(
        "SELECT r.id, r.race_date, r.start_location, r.race_kilometers, r.elevation_meters, r.max_gradient_percent,
                o.organizer_name
         FROM races r
         INNER JOIN organizers o ON o.id = r.organizer_id
         WHERE r.race_date >= CURDATE()
         ORDER BY r.race_date, r.id"
    );

    return $result->fetch_all(MYSQLI_ASSOC);
}

function getOrganizerRaces(mysqli $connection, int $organizerId): array
{
    $statement = $connection->prepare(
        "SELECT id, race_date, start_location, race_kilometers, elevation_meters, max_gradient_percent
         FROM races
         WHERE organizer_id = ?
         ORDER BY race_date DESC, id DESC"
    );
    $statement->bind_param("i", $organizerId);
    $statement->execute();
    $result = $statement->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $statement->close();

    return $rows;
}

function getRaceById(mysqli $connection, int $raceId): ?array
{
    $statement = $connection->prepare(
        "SELECT r.id, r.organizer_id, r.race_date, r.start_location, r.race_kilometers, r.elevation_meters, r.max_gradient_percent,
                o.organizer_name
         FROM races r
         INNER JOIN organizers o ON o.id = r.organizer_id
         WHERE r.id = ?"
    );
    $statement->bind_param("i", $raceId);
    $statement->execute();
    $result = $statement->get_result();
    $race = $result->fetch_assoc();
    $statement->close();

    return $race ?: null;
}

function getMaxStartNumber(mysqli $connection, int $raceId): int
{
    $statement = $connection->prepare(
        "SELECT COALESCE(MAX(start_number), 0) AS max_number FROM race_registrations WHERE race_id = ?"
    );
    $statement->bind_param("i", $raceId);
    $statement->execute();
    $result = $statement->get_result();
    $row = $result->fetch_assoc();
    $statement->close();

    return (int) $row["max_number"];
}

function registerRidersToRace(mysqli $connection, int $teamId, int $raceId, array $riderIds): array
{
    $race = getRaceById($connection, $raceId);
    if (!$race) {
        return [false, "Rennen wurde nicht gefunden."];
    }

    $riderIds = array_values(array_unique(array_filter(array_map("intval", $riderIds))));
    if ($riderIds === []) {
        return [false, "Bitte mindestens einen Fahrer auswaehlen."];
    }

    $startNumber = getMaxStartNumber($connection, $raceId) + 1;
    $connection->begin_transaction();

    try {
        foreach ($riderIds as $riderId) {
            $rider = getRiderById($connection, $teamId, $riderId);
            if (!$rider) {
                continue;
            }

            $statement = $connection->prepare(
                "SELECT id FROM race_registrations WHERE race_id = ? AND rider_id = ?"
            );
            $statement->bind_param("ii", $raceId, $riderId);
            $statement->execute();
            $statement->store_result();
            $exists = $statement->num_rows > 0;
            $statement->close();

            if ($exists) {
                continue;
            }

            $statement = $connection->prepare(
                "INSERT INTO race_registrations (race_id, rider_id, start_number) VALUES (?, ?, ?)"
            );
            $statement->bind_param("iii", $raceId, $riderId, $startNumber);
            $statement->execute();
            $statement->close();
            $startNumber++;
        }

        $connection->commit();
        return [true, "Fahrer wurden fuer das Rennen gemeldet."];
    } catch (Throwable $exception) {
        $connection->rollback();
        return [false, "Meldung fehlgeschlagen: " . $exception->getMessage()];
    }
}

function copyRegistrationsForTeam(
    mysqli $connection,
    int $teamId,
    int $sourceRaceId,
    int $targetRaceId
): array {
    if ($sourceRaceId === $targetRaceId) {
        return [false, "Quell- und Zielrennen muessen verschieden sein."];
    }

    $statement = $connection->prepare(
        "SELECT rr.rider_id
         FROM race_registrations rr
         INNER JOIN riders r ON r.id = rr.rider_id
         WHERE rr.race_id = ? AND r.team_id = ?
         ORDER BY rr.start_number"
    );
    $statement->bind_param("ii", $sourceRaceId, $teamId);
    $statement->execute();
    $result = $statement->get_result();
    $riderIds = [];
    while ($row = $result->fetch_assoc()) {
        $riderIds[] = (int) $row["rider_id"];
    }
    $statement->close();

    if ($riderIds === []) {
        return [false, "Im Quellrennen gibt es fuer dein Team keine Meldungen."];
    }

    return registerRidersToRace($connection, $teamId, $targetRaceId, $riderIds);
}

function getTeamRaceOverview(mysqli $connection, int $teamId): array
{
    $statement = $connection->prepare(
        "SELECT ra.id AS race_id, ra.race_date, ra.start_location, rr.start_number,
                r.employee_id, r.rider_name, res.placing, res.medal, res.ride_time
         FROM race_registrations rr
         INNER JOIN riders r ON r.id = rr.rider_id
         INNER JOIN races ra ON ra.id = rr.race_id
         LEFT JOIN race_results res ON res.registration_id = rr.id
         WHERE r.team_id = ?
         ORDER BY ra.race_date DESC, rr.start_number"
    );
    $statement->bind_param("i", $teamId);
    $statement->execute();
    $result = $statement->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $statement->close();

    return $rows;
}

function getRaceRegistrationsForResults(mysqli $connection, int $raceId): array
{
    $statement = $connection->prepare(
        "SELECT rr.id AS registration_id, rr.start_number, r.employee_id, r.rider_name, t.team_name
         FROM race_registrations rr
         INNER JOIN riders r ON r.id = rr.rider_id
         INNER JOIN teams t ON t.id = r.team_id
         WHERE rr.race_id = ?
         ORDER BY rr.start_number"
    );
    $statement->bind_param("i", $raceId);
    $statement->execute();
    $result = $statement->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $statement->close();

    return $rows;
}

function raceHasResults(mysqli $connection, int $raceId): bool
{
    $statement = $connection->prepare(
        "SELECT res.id
         FROM race_results res
         INNER JOIN race_registrations rr ON rr.id = res.registration_id
         WHERE rr.race_id = ?
         LIMIT 1"
    );
    $statement->bind_param("i", $raceId);
    $statement->execute();
    $result = $statement->get_result();
    $row = $result->fetch_assoc();
    $statement->close();

    return (bool) $row;
}

function saveRaceResults(mysqli $connection, int $raceId, array $placings, array $medals, array $times): array
{
    if (raceHasResults($connection, $raceId)) {
        return [false, "Die Ergebnisse wurden bereits einmal erfasst und koennen nicht mehr geaendert werden."];
    }

    $registrations = getRaceRegistrationsForResults($connection, $raceId);
    if ($registrations === []) {
        return [false, "Es gibt keine gemeldeten Fahrer fuer dieses Rennen."];
    }

    $connection->begin_transaction();

    try {
        foreach ($registrations as $registration) {
            $registrationId = (int) $registration["registration_id"];
            $placing = trim($placings[$registrationId] ?? "");
            $medal = trim($medals[$registrationId] ?? "");
            $time = trim($times[$registrationId] ?? "");

            if ($placing === "" || $medal === "" || $time === "") {
                throw new RuntimeException("Bitte fuer alle Fahrer Platzierung, Medaille und Fahrtzeit eintragen.");
            }

            $statement = $connection->prepare(
                "INSERT INTO race_results (registration_id, placing, medal, ride_time)
                 VALUES (?, ?, ?, ?)"
            );
            $statement->bind_param("iiss", $registrationId, $placing, $medal, $time);
            $statement->execute();
            $statement->close();
        }

        $connection->commit();
        return [true, "Die Rennergebnisse wurden gespeichert."];
    } catch (Throwable $exception) {
        $connection->rollback();
        return [false, "Ergebnisse konnten nicht gespeichert werden: " . $exception->getMessage()];
    }
}
