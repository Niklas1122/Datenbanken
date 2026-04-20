<?php
/*
Autor: Entwurf mit Codex, bitte im Projekt mit echtem Autor kennzeichnen.
Zweck: Gekapselte Funktionen fuer Team-Registrierung, Login und Teamdatenpflege.
*/

require_once __DIR__ . '/db.inc.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function teamExists(mysqli $connection, string $teamName): bool
{
    $statement = $connection->prepare("SELECT id FROM teams WHERE team_name = ?");
    $statement->bind_param("s", $teamName);
    $statement->execute();
    $statement->store_result();
    $exists = $statement->num_rows > 0;
    $statement->close();

    return $exists;
}

function createTeam(
    mysqli $connection,
    string $teamName,
    string $firstName,
    string $lastName,
    string $loginName,
    string $password
): array {
    if (teamExists($connection, $teamName)) {
        return [false, "Der Teamname existiert bereits."];
    }

    $statement = $connection->prepare("SELECT id FROM teamchefs WHERE login_name = ?");
    $statement->bind_param("s", $loginName);
    $statement->execute();
    $statement->store_result();
    if ($statement->num_rows > 0) {
        $statement->close();
        return [false, "Der Loginname wird bereits verwendet."];
    }
    $statement->close();

    $connection->begin_transaction();

    try {
        $statement = $connection->prepare("INSERT INTO teams (team_name) VALUES (?)");
        $statement->bind_param("s", $teamName);
        $statement->execute();
        $teamId = $connection->insert_id;
        $statement->close();

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $statement = $connection->prepare(
            "INSERT INTO teamchefs (team_id, first_name, last_name, login_name, password_hash)
             VALUES (?, ?, ?, ?, ?)"
        );
        $statement->bind_param("issss", $teamId, $firstName, $lastName, $loginName, $passwordHash);
        $statement->execute();
        $statement->close();

        $connection->commit();
        return [true, "Team wurde erfolgreich angelegt."];
    } catch (Throwable $exception) {
        $connection->rollback();
        return [false, "Speichern fehlgeschlagen: " . $exception->getMessage()];
    }
}

function loginTeamchef(mysqli $connection, string $loginName, string $password): array
{
    $statement = $connection->prepare(
        "SELECT tc.id, tc.team_id, tc.first_name, tc.last_name, tc.login_name, tc.password_hash, t.team_name
         FROM teamchefs tc
         INNER JOIN teams t ON t.id = tc.team_id
         WHERE tc.login_name = ?"
    );
    $statement->bind_param("s", $loginName);
    $statement->execute();
    $result = $statement->get_result();
    $teamchef = $result->fetch_assoc();
    $statement->close();

    if (!$teamchef || !password_verify($password, $teamchef["password_hash"])) {
        return [false, "Loginname oder Kennwort sind falsch."];
    }

    $_SESSION["teamchef_id"] = (int) $teamchef["id"];
    $_SESSION["team_id"] = (int) $teamchef["team_id"];
    $_SESSION["team_name"] = $teamchef["team_name"];
    $_SESSION["teamchef_name"] = $teamchef["first_name"] . " " . $teamchef["last_name"];

    return [true, "Anmeldung erfolgreich."];
}

function requireTeamchefLogin(): void
{
    if (empty($_SESSION["teamchef_id"])) {
        header("Location: index.php?message=Bitte+zuerst+anmelden.&type=error");
        exit;
    }
}

function getCurrentTeamData(mysqli $connection, int $teamchefId): ?array
{
    $statement = $connection->prepare(
        "SELECT tc.id, tc.first_name, tc.last_name, tc.login_name, t.id AS team_id, t.team_name
         FROM teamchefs tc
         INNER JOIN teams t ON t.id = tc.team_id
         WHERE tc.id = ?"
    );
    $statement->bind_param("i", $teamchefId);
    $statement->execute();
    $result = $statement->get_result();
    $data = $result->fetch_assoc();
    $statement->close();

    return $data ?: null;
}

function updateTeamData(
    mysqli $connection,
    int $teamchefId,
    int $teamId,
    string $teamName,
    string $firstName,
    string $lastName,
    string $loginName,
    string $newPassword
): array {
    $statement = $connection->prepare("SELECT id FROM teams WHERE team_name = ? AND id <> ?");
    $statement->bind_param("si", $teamName, $teamId);
    $statement->execute();
    $statement->store_result();
    if ($statement->num_rows > 0) {
        $statement->close();
        return [false, "Dieser Teamname ist bereits vergeben."];
    }
    $statement->close();

    $statement = $connection->prepare("SELECT id FROM teamchefs WHERE login_name = ? AND id <> ?");
    $statement->bind_param("si", $loginName, $teamchefId);
    $statement->execute();
    $statement->store_result();
    if ($statement->num_rows > 0) {
        $statement->close();
        return [false, "Dieser Loginname ist bereits vergeben."];
    }
    $statement->close();

    $connection->begin_transaction();

    try {
        $statement = $connection->prepare("UPDATE teams SET team_name = ? WHERE id = ?");
        $statement->bind_param("si", $teamName, $teamId);
        $statement->execute();
        $statement->close();

        if ($newPassword !== "") {
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $statement = $connection->prepare(
                "UPDATE teamchefs
                 SET first_name = ?, last_name = ?, login_name = ?, password_hash = ?
                 WHERE id = ?"
            );
            $statement->bind_param("ssssi", $firstName, $lastName, $loginName, $passwordHash, $teamchefId);
        } else {
            $statement = $connection->prepare(
                "UPDATE teamchefs
                 SET first_name = ?, last_name = ?, login_name = ?
                 WHERE id = ?"
            );
            $statement->bind_param("sssi", $firstName, $lastName, $loginName, $teamchefId);
        }
        $statement->execute();
        $statement->close();

        $connection->commit();

        $_SESSION["team_name"] = $teamName;
        $_SESSION["teamchef_name"] = $firstName . " " . $lastName;

        return [true, "Teamdaten wurden gespeichert."];
    } catch (Throwable $exception) {
        $connection->rollback();
        return [false, "Aendern fehlgeschlagen: " . $exception->getMessage()];
    }
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

