<?php
session_start();
require_once __DIR__ . '/connection.php';

if (!isset($_SESSION['veranstalter_login'])) {
    header("Location: index.php");
    exit;
}

$veranstalter_login = $_SESSION['veranstalter_login'];
$meldung = '';
$fehler = '';

function holeRennenDesVeranstalters($connection, $login)
{
    $login_sicher = mysqli_real_escape_string($connection, $login);
    $liste = array();
    try {
        $abfrage = mysqli_query(
            $connection,
            "SELECT RennID, Datum, Standort FROM RENNEN
             WHERE VeranstalterLogin = '$login_sicher'
             ORDER BY Datum DESC"
        );
    } catch (mysqli_sql_exception $e) {
        return $liste;
    }
    if ($abfrage) {
        while ($zeile = mysqli_fetch_assoc($abfrage)) {
            $liste[] = $zeile;
        }
    }
    return $liste;
}

function holeTeilnehmerFuerRennen($connection, $renn_id)
{
    $renn_sicher = mysqli_real_escape_string($connection, $renn_id);
    $liste = array();
    try {
        $abfrage = mysqli_query(
            $connection,
            "SELECT t.MitarbeiterID, t.Startnr, t.Platzierung, t.Fahrzeit, f.Name, f.Teamname
             FROM TEILNAHME t
             JOIN FAHRER f ON f.MitarbeiterID = t.MitarbeiterID
             WHERE t.RennID = '$renn_sicher'
             ORDER BY t.Startnr"
        );
    } catch (mysqli_sql_exception $e) {
        return $liste;
    }
    if ($abfrage) {
        while ($zeile = mysqli_fetch_assoc($abfrage)) {
            $liste[] = $zeile;
        }
    }
    return $liste;
}

// Rennen anlegen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aktion'] ?? '') === 'rennen_anlegen') {
    $datum       = trim($_POST['datum'] ?? '');
    $standort    = trim($_POST['standort'] ?? '');
    $kilometer   = trim($_POST['kilometer'] ?? '');
    $hoehenmeter = trim($_POST['hoehenmeter'] ?? '');
    $max_steigung = trim($_POST['max_steigung'] ?? '');

    if ($datum === '' || $standort === '' || $kilometer === '' || $hoehenmeter === '' || $max_steigung === '') {
        $fehler = 'Bitte alle Felder ausfüllen.';
    } else {
        $datum        = mysqli_real_escape_string($connection, $datum);
        $standort     = mysqli_real_escape_string($connection, $standort);
        $kilometer    = mysqli_real_escape_string($connection, $kilometer);
        $hoehenmeter  = mysqli_real_escape_string($connection, $hoehenmeter);
        $max_steigung = mysqli_real_escape_string($connection, $max_steigung);
        $veranstalter = mysqli_real_escape_string($connection, $veranstalter_login);

        $sql = "INSERT INTO RENNEN (Datum, Standort, Kilometer, Hoehenmeter, MaximaleSteigung, VeranstalterLogin)
                VALUES ('$datum', '$standort', '$kilometer', '$hoehenmeter', '$max_steigung', '$veranstalter')";

        if (mysqli_query($connection, $sql)) {
            $meldung = 'Rennen wurde angelegt. ID: ' . mysqli_insert_id($connection);
        } else {
            $fehler = 'Rennen konnte nicht gespeichert werden.';
        }
    }
}

// Ergebnisse speichern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aktion'] ?? '') === 'ergebnisse_speichern') {
    $renn_id      = mysqli_real_escape_string($connection, trim($_POST['renn_id'] ?? ''));
    $platzierungen = $_POST['platzierung'] ?? array();
    $fahrzeiten    = $_POST['fahrzeit'] ?? array();
    $gespeichert   = 0;

    foreach ($platzierungen as $mitarbeiter_id => $platzierung) {
        $id_sicher    = mysqli_real_escape_string($connection, $mitarbeiter_id);
        $platz_sicher = mysqli_real_escape_string($connection, trim($platzierung));
        $zeit_sicher  = mysqli_real_escape_string($connection, trim($fahrzeiten[$mitarbeiter_id] ?? ''));

        $platz_wert = $platz_sicher !== '' ? "'$platz_sicher'" : 'NULL';
        $zeit_wert  = $zeit_sicher  !== '' ? "'$zeit_sicher'"  : 'NULL';

        try {
            if (mysqli_query(
                $connection,
                "UPDATE TEILNAHME SET Platzierung = $platz_wert, Fahrzeit = $zeit_wert
                 WHERE RennID = '$renn_id' AND MitarbeiterID = '$id_sicher'"
            )) {
                $gespeichert++;
            }
        } catch (mysqli_sql_exception $e) {
            continue;
        }
    }

    if ($gespeichert > 0) {
        $meldung = "Ergebnisse für $gespeichert Fahrer gespeichert.";
    } else {
        $fehler = "Ergebnisse konnten nicht gespeichert werden.";
    }
}

$rennen_liste = holeRennenDesVeranstalters($connection, $veranstalter_login);
$renn_id      = trim($_GET['renn_id'] ?? '');
$teilnehmer   = array();
$ergebnisse_gesperrt = false;

if ($renn_id !== '') {
    $teilnehmer = holeTeilnehmerFuerRennen($connection, $renn_id);
    foreach ($teilnehmer as $t) {
        if ($t['Platzierung'] !== null) {
            $ergebnisse_gesperrt = true;
            break;
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
        <h1>Rennveranstalter</h1>
        <p>Angemeldet als <?= htmlspecialchars($veranstalter_login); ?></p>
        <p><a href="abmelden.php">Abmelden</a></p>

        <?php if ($meldung !== ''): ?>
            <p><?= htmlspecialchars($meldung); ?></p>
        <?php endif; ?>

        <?php if ($fehler !== ''): ?>
            <p><?= htmlspecialchars($fehler); ?></p>
        <?php endif; ?>

        <h2>Rennen anlegen</h2>
        <form action="rennveranstalter_dashboard.php" method="post">
            <input type="hidden" name="aktion" value="rennen_anlegen">
            <label>Datum<br><input type="date" name="datum"></label><br><br>
            <label>Startort<br><input type="text" name="standort"></label><br><br>
            <label>Kilometer<br><input type="number" step="0.1" name="kilometer"></label><br><br>
            <label>Höhenmeter<br><input type="number" name="hoehenmeter"></label><br><br>
            <label>Maximale Steigung in Prozent<br><input type="number" step="0.1" name="max_steigung"></label><br><br>
            <button type="submit">Rennen speichern</button>
        </form>

        <h2>Ergebnisse erfassen</h2>
        <?php if (count($rennen_liste) === 0): ?>
            <p>Noch keine Rennen vorhanden.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Datum</th>
                        <th>Standort</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rennen_liste as $rennen): ?>
                        <tr>
                            <td><?= htmlspecialchars($rennen['RennID']); ?></td>
                            <td><?= htmlspecialchars($rennen['Datum']); ?></td>
                            <td><?= htmlspecialchars($rennen['Standort']); ?></td>
                            <td>
                                <a href="rennveranstalter_dashboard.php?renn_id=<?= urlencode($rennen['RennID']); ?>">
                                    Ergebnisse erfassen
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if ($renn_id !== '' && count($teilnehmer) > 0): ?>
            <h3>Ergebnisse für Rennen <?= htmlspecialchars($renn_id); ?></h3>

            <?php if ($ergebnisse_gesperrt): ?>
                <p>Ergebnisse wurden bereits erfasst und können nicht mehr geändert werden.</p>
                <table>
                    <thead>
                        <tr>
                            <th>Startnr</th>
                            <th>Name</th>
                            <th>Team</th>
                            <th>Platzierung</th>
                            <th>Fahrzeit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teilnehmer as $t): ?>
                            <tr>
                                <td><?= htmlspecialchars($t['Startnr']); ?></td>
                                <td><?= htmlspecialchars($t['Name']); ?></td>
                                <td><?= htmlspecialchars($t['Teamname']); ?></td>
                                <td><?= htmlspecialchars($t['Platzierung'] ?? '–'); ?></td>
                                <td><?= htmlspecialchars($t['Fahrzeit'] ?? '–'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <form action="rennveranstalter_dashboard.php" method="post">
                    <input type="hidden" name="aktion" value="ergebnisse_speichern">
                    <input type="hidden" name="renn_id" value="<?= htmlspecialchars($renn_id); ?>">
                    <table>
                        <thead>
                            <tr>
                                <th>Startnr</th>
                                <th>Name</th>
                                <th>Team</th>
                                <th>Platzierung</th>
                                <th>Fahrzeit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teilnehmer as $t): ?>
                                <tr>
                                    <td><?= htmlspecialchars($t['Startnr']); ?></td>
                                    <td><?= htmlspecialchars($t['Name']); ?></td>
                                    <td><?= htmlspecialchars($t['Teamname']); ?></td>
                                    <td>
                                        <input type="number" name="platzierung[<?= htmlspecialchars($t['MitarbeiterID']); ?>]" min="1">
                                    </td>
                                    <td>
                                        <input type="time" step="1" name="fahrzeit[<?= htmlspecialchars($t['MitarbeiterID']); ?>]">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <br>
                    <button type="submit">Ergebnisse speichern</button>
                </form>
            <?php endif; ?>
        <?php elseif ($renn_id !== ''): ?>
            <p>Keine Teilnehmer für dieses Rennen gefunden.</p>
        <?php endif; ?>
    </div>
</body>
</html>
