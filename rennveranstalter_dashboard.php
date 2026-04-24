<?php
session_start();
require_once __DIR__ . '/connection.php';

if (!isset($_SESSION['veranstalter_login'])) {
    header("Location: index.php");
    exit;
}

$meldung = '';
$fehler = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datum = trim($_POST['datum'] ?? '');
    $standort = trim($_POST['standort'] ?? '');
    $kilometer = trim($_POST['kilometer'] ?? '');
    $hoehenmeter = trim($_POST['hoehenmeter'] ?? '');
    $max_steigung = trim($_POST['max_steigung'] ?? '');

    if ($datum === '' || $standort === '' || $kilometer === '' || $hoehenmeter === '' || $max_steigung === '') {
        $fehler = 'Bitte alle Felder ausfüllen.';
    } else {
        $datum = mysqli_real_escape_string($connection, $datum);
        $standort = mysqli_real_escape_string($connection, $standort);
        $kilometer = mysqli_real_escape_string($connection, $kilometer);
        $hoehenmeter = mysqli_real_escape_string($connection, $hoehenmeter);
        $max_steigung = mysqli_real_escape_string($connection, $max_steigung);
        $veranstalter = mysqli_real_escape_string($connection, $_SESSION['veranstalter_login']);

        $sql = "INSERT INTO RENNEN (Datum, Standort, Kilometer, Hoehenmeter, MaximaleSteigung, VeranstalterLogin)
                VALUES ('$datum', '$standort', '$kilometer', '$hoehenmeter', '$max_steigung', '$veranstalter')";

        if (mysqli_query($connection, $sql)) {
            $meldung = 'Rennen wurde angelegt. ID: ' . mysqli_insert_id($connection);
        } else {
            $fehler = 'Rennen konnte nicht gespeichert werden.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rennveranstalter</title>
</head>
<body>
    <div>
        <h1>Rennen anlegen</h1>
        <p>Angemeldet als <?php echo htmlspecialchars($_SESSION['veranstalter_login']); ?></p>
        <p><a href="abmelden.php">Abmelden</a></p>

        <?php if ($meldung !== ''): ?>
            <p><?php echo htmlspecialchars($meldung); ?></p>
        <?php endif; ?>

        <?php if ($fehler !== ''): ?>
            <p><?php echo htmlspecialchars($fehler); ?></p>
        <?php endif; ?>

        <form action="rennveranstalter_dashboard.php" method="post">
            <label>
                Datum<br>
                <input type="date" name="datum">
            </label>
            <br><br>
            <label>
                Startort<br>
                <input type="text" name="standort">
            </label>
            <br><br>
            <label>
                Kilometer<br>
                <input type="number" step="0.1" name="kilometer">
            </label>
            <br><br>
            <label>
                Höhenmeter<br>
                <input type="number" name="hoehenmeter">
            </label>
            <br><br>
            <label>
                Maximale Steigung in Prozent<br>
                <input type="number" step="0.1" name="max_steigung">
            </label>
            <br><br>
            <button type="submit">Rennen speichern</button>
        </form>
    </div>
</body>
</html>
