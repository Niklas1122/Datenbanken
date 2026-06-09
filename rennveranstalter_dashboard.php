<?php
// Jonas
session_start();
require_once __DIR__ . '/connection.inc.php';


//Prüfen ob Veranstalter eingeloggt ist, sonst weiterleiten
if (!isset($_SESSION['veranstalter_login'])) {
    header("Location: index.php");
    exit;
}

$veranstalter_login = $_SESSION['veranstalter_login'];
$meldung = '';
$fehler = '';


//Holt alle Rennen, die vom aktuell eingeloggten Veranstalter erstellt wurden
function holeRennenDesVeranstalters($connection, $login)
{
    $liste = array();
    try {
        $stmt = mysqli_prepare($connection, "SELECT RennID, Datum, Standort FROM RENNEN WHERE VeranstalterLogin = ? ORDER BY Datum DESC");
        mysqli_stmt_bind_param($stmt, 's', $login);
        mysqli_stmt_execute($stmt);
        $abfrage = mysqli_stmt_get_result($stmt);
    } catch (mysqli_sql_exception $e) {
        return $liste;
    }
    // Alle gefundenen Rennen in das Array übernehmen
    if ($abfrage) {
        while ($zeile = mysqli_fetch_assoc($abfrage)) {
            $liste[] = $zeile;
        }
    }
    return $liste;
}

function holeTeilnehmerFuerRennen($connection, $renn_id)
{
    $liste = array();
    try {
        $stmt = mysqli_prepare($connection, "SELECT t.MitarbeiterID, t.Startnr, t.Platzierung, t.Fahrzeit, f.Name, f.Teamname FROM TEILNAHME t JOIN FAHRER f ON f.MitarbeiterID = t.MitarbeiterID WHERE t.RennID = ? ORDER BY t.Startnr");
        mysqli_stmt_bind_param($stmt, 's', $renn_id);
        mysqli_stmt_execute($stmt);
        $abfrage = mysqli_stmt_get_result($stmt);
    } catch (mysqli_sql_exception $e) {
        return $liste;
    }
        // Alle Teilnehmer in ein Array speichern
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


    //Prüfen ob alle Felder ausgefüllt wurden, sonst Fehlermeldung anzeigen. Wenn alles ok, Rennen in DB speichern
    if ($datum === '' || $standort === '' || $kilometer === '' || $hoehenmeter === '' || $max_steigung === '') {
        $fehler = 'Bitte alle Felder ausfüllen.';
    } else {
        $stmt = mysqli_prepare($connection, "INSERT INTO RENNEN (Datum, Standort, Kilometer, Hoehenmeter, MaximaleSteigung, VeranstalterLogin) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssssss', $datum, $standort, $kilometer, $hoehenmeter, $max_steigung, $veranstalter_login);

        if (mysqli_stmt_execute($stmt)) {
            $meldung = 'Rennen wurde angelegt. ID: ' . mysqli_insert_id($connection);
        } else {
            $fehler = 'Rennen konnte nicht gespeichert werden.';
        }
    }
}

// Ergebnisse speichern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aktion'] ?? '') === 'ergebnisse_speichern') {
    $renn_id       = trim($_POST['renn_id'] ?? '');
    $platzierungen = $_POST['platzierung'] ?? array();
    $fahrzeiten    = $_POST['fahrzeit'] ?? array();
    $gespeichert   = 0;

    mysqli_begin_transaction($connection);
    try {
        $stmt = mysqli_prepare($connection, "UPDATE TEILNAHME SET Platzierung = ?, Fahrzeit = ? WHERE RennID = ? AND MitarbeiterID = ?");
       
           // Für jeden Teilnehmer Platzierung und Fahrzeit speichern
        foreach ($platzierungen as $mitarbeiter_id => $platzierung) {
            $id_sicher  = trim((string)$mitarbeiter_id);
            $platz_wert = trim($platzierung) !== '' ? trim($platzierung) : null;
            $zeit_wert  = trim($fahrzeiten[$mitarbeiter_id] ?? '') !== '' ? trim($fahrzeiten[$mitarbeiter_id]) : null;
            mysqli_stmt_bind_param($stmt, 'ssss', $platz_wert, $zeit_wert, $renn_id, $id_sicher);
            mysqli_stmt_execute($stmt);
            $gespeichert++;
        }
        mysqli_commit($connection);
    } catch (mysqli_sql_exception $e) {
        mysqli_rollback($connection);
        $gespeichert = 0;
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
