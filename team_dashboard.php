<?php
session_start();
require_once __DIR__ . '/Connection.php';

if (!isset($_SESSION['teamchef_login'])) {
    header("Location: index.php");
    exit;
}

$teamchef_login = $_SESSION['teamchef_login'];
$leerer_fahrer = array(
    'MitarbeiterID' => '',
    'Name' => '',
    'PLZ' => '',
    'Ort' => '',
    'Strasse' => '',
    'Hausnr' => '',
    'TelNr' => ''
);

$teamname = '';
$meldung = '';
$fehler = '';
$fahrer_liste = false;
$bearbeiten = isset($_GET['edit']) && $_GET['edit'] !== '';
$fahrer_form = $leerer_fahrer;
$label_stil = 'display:block; margin-bottom:10px;';

// Team des angemeldeten Teamchefs laden
$login_sicher = mysqli_real_escape_string($connection, $teamchef_login);
$team_abfrage = mysqli_query($connection, "SELECT Teamname FROM TEAM WHERE Loginname = '$login_sicher' LIMIT 1");

if ($team_abfrage && mysqli_num_rows($team_abfrage) > 0) {
    $teamname = mysqli_fetch_assoc($team_abfrage)['Teamname'];
} else {
    $fehler = "Kein Team zum angemeldeten Teamchef gefunden.";
}

if ($teamname !== "") {
    $team_sicher = mysqli_real_escape_string($connection, $teamname);
    $aktion = $_POST['aktion'] ?? '';
    $modus = $_POST['modus'] ?? 'neu';

    // Fahrer löschen
    if ($aktion === 'loeschen' && !empty($_POST['mitarbeiter_id'])) {
        $id_sicher = mysqli_real_escape_string($connection, trim($_POST['mitarbeiter_id']));
        if (mysqli_query($connection, "DELETE FROM FAHRER WHERE MitarbeiterID = '$id_sicher' AND Teamname = '$team_sicher'")) {
            $meldung = "Fahrer wurde gelöscht.";
        } else {
            $fehler = "Fahrer konnte nicht gelöscht werden.";
        }
    }

    // Fahrer anlegen oder ändern
    if ($aktion === 'speichern') {
        $fahrer_form = array(
            'MitarbeiterID' => trim($_POST['mitarbeiter_id']),
            'Name' => trim($_POST['fahrer_name']),
            'PLZ' => trim($_POST['plz']),
            'Ort' => trim($_POST['ort']),
            'Strasse' => trim($_POST['strasse']),
            'Hausnr' => trim($_POST['hausnr']),
            'TelNr' => trim($_POST['telnr'])
        );

        if ($fahrer_form['MitarbeiterID'] === '' || $fahrer_form['Name'] === '') {
            $fehler = "Mitarbeiter-ID und Name müssen ausgefüllt sein.";
            $bearbeiten = $modus === 'bearbeiten';
        } else {
            $id_sicher = mysqli_real_escape_string($connection, $fahrer_form['MitarbeiterID']);
            $name_sicher = mysqli_real_escape_string($connection, $fahrer_form['Name']);
            $plz_sicher = mysqli_real_escape_string($connection, $fahrer_form['PLZ']);
            $ort_sicher = mysqli_real_escape_string($connection, $fahrer_form['Ort']);
            $strasse_sicher = mysqli_real_escape_string($connection, $fahrer_form['Strasse']);
            $hausnr_sicher = mysqli_real_escape_string($connection, $fahrer_form['Hausnr']);
            $telnr_sicher = mysqli_real_escape_string($connection, $fahrer_form['TelNr']);

            if ($modus === 'bearbeiten') {
                $sql = "UPDATE FAHRER SET Name = '$name_sicher', PLZ = '$plz_sicher', Ort = '$ort_sicher', Strasse = '$strasse_sicher', Hausnr = '$hausnr_sicher', TelNr = '$telnr_sicher' WHERE MitarbeiterID = '$id_sicher' AND Teamname = '$team_sicher'";
                if (mysqli_query($connection, $sql)) {
                    $meldung = "Fahrer wurde geändert.";
                    $fahrer_form = $leerer_fahrer;
                    $bearbeiten = false;
                } else {
                    $fehler = "Änderung konnte nicht gespeichert werden.";
                    $bearbeiten = true;
                }
            } else {
                $check = mysqli_query($connection, "SELECT MitarbeiterID FROM FAHRER WHERE MitarbeiterID = '$id_sicher' AND Teamname = '$team_sicher'");
                if ($check && mysqli_num_rows($check) > 0) {
                    $fehler = "Diese Mitarbeiter-ID gibt es im Team bereits.";
                } else {
                    $sql = "INSERT INTO FAHRER (MitarbeiterID, Name, PLZ, Ort, Strasse, Hausnr, TelNr, Teamname) VALUES ('$id_sicher', '$name_sicher', '$plz_sicher', '$ort_sicher', '$strasse_sicher', '$hausnr_sicher', '$telnr_sicher', '$team_sicher')";
                    if (mysqli_query($connection, $sql)) {
                        $meldung = "Fahrer wurde angelegt.";
                        $fahrer_form = $leerer_fahrer;
                        $bearbeiten = false;
                    } else {
                        $fehler = "Neuer Fahrer konnte nicht gespeichert werden.";
                    }
                }
            }
        }
    }

    // Daten für das Bearbeiten ins Formular laden
    if ($bearbeiten && $fahrer_form['MitarbeiterID'] === '') {
        $id_sicher = mysqli_real_escape_string($connection, $_GET['edit']);
        $abfrage = mysqli_query($connection, "SELECT MitarbeiterID, Name, PLZ, Ort, Strasse, Hausnr, TelNr FROM FAHRER WHERE MitarbeiterID = '$id_sicher' AND Teamname = '$team_sicher' LIMIT 1");
        if ($abfrage && mysqli_num_rows($abfrage) > 0) {
            $fahrer_form = mysqli_fetch_assoc($abfrage);
        }
    }

    // Alle Fahrer des Teams laden
    $fahrer_liste = mysqli_query($connection, "SELECT MitarbeiterID, Name, PLZ, Ort, Strasse, Hausnr, TelNr FROM FAHRER WHERE Teamname = '$team_sicher' ORDER BY MitarbeiterID");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fahrerpflege</title>
</head>
<body>
    <div>
        <h1>Fahrerpflege</h1>
        <p>Angemeldet als <?php echo htmlspecialchars($teamchef_login); ?> für Team <?php echo htmlspecialchars($teamname); ?></p>
        <p><a href="logout.php">Abmelden</a></p>

        <?php if ($meldung !== ""): ?>
            <p><?php echo htmlspecialchars($meldung); ?></p>
        <?php endif; ?>

        <?php if ($fehler !== ""): ?>
            <p><?php echo htmlspecialchars($fehler); ?></p>
        <?php endif; ?>

        <div>
            <h2><?php echo $bearbeiten ? 'Fahrer ändern' : 'Fahrer anlegen'; ?></h2>
            <form action="team_dashboard.php" method="post">
                <input type="hidden" name="aktion" value="speichern">
                <input type="hidden" name="modus" value="<?php echo $bearbeiten ? 'bearbeiten' : 'neu'; ?>">

                <label style="<?php echo $label_stil; ?>">
                    Mitarbeiter-ID<br>
                    <input type="text" name="mitarbeiter_id" value="<?php echo htmlspecialchars($fahrer_form['MitarbeiterID']); ?>" <?php echo $bearbeiten ? 'readonly' : ''; ?>>
                </label>
                <label style="<?php echo $label_stil; ?>">
                    Name<br>
                    <input type="text" name="fahrer_name" value="<?php echo htmlspecialchars($fahrer_form['Name']); ?>">
                </label>
                <label style="<?php echo $label_stil; ?>">
                    PLZ<br>
                    <input type="text" name="plz" value="<?php echo htmlspecialchars($fahrer_form['PLZ']); ?>">
                </label>
                <label style="<?php echo $label_stil; ?>">
                    Ort<br>
                    <input type="text" name="ort" value="<?php echo htmlspecialchars($fahrer_form['Ort']); ?>">
                </label>
                <label style="<?php echo $label_stil; ?>">
                    Strasse<br>
                    <input type="text" name="strasse" value="<?php echo htmlspecialchars($fahrer_form['Strasse']); ?>">
                </label>
                <label style="<?php echo $label_stil; ?>">
                    Hausnummer<br>
                    <input type="text" name="hausnr" value="<?php echo htmlspecialchars($fahrer_form['Hausnr']); ?>">
                </label>
                <label style="<?php echo $label_stil; ?>">
                    Telefonnummer<br>
                    <input type="text" name="telnr" value="<?php echo htmlspecialchars($fahrer_form['TelNr']); ?>">
                </label>
                <button type="submit">Speichern</button>
            </form>
        </div>

        <div>
            <h2>Vorhandene Fahrer</h2>
            <table>
                <thead>
                    <tr>
                        <th>Mitarbeiter-ID</th>
                        <th>Name</th>
                        <th>Adresse</th>
                        <th>Telefon</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($fahrer_liste && mysqli_num_rows($fahrer_liste) > 0): ?>
                        <?php while ($fahrer = mysqli_fetch_assoc($fahrer_liste)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fahrer['MitarbeiterID']); ?></td>
                                <td><?php echo htmlspecialchars($fahrer['Name']); ?></td>
                                <td><?php echo htmlspecialchars(trim($fahrer['Strasse'] . ' ' . $fahrer['Hausnr']) . ', ' . trim($fahrer['PLZ'] . ' ' . $fahrer['Ort'])); ?></td>
                                <td><?php echo htmlspecialchars($fahrer['TelNr']); ?></td>
                                <td>
                                    <a href="team_dashboard.php?edit=<?php echo urlencode($fahrer['MitarbeiterID']); ?>">Bearbeiten</a>
                                    <form action="team_dashboard.php" method="post">
                                        <input type="hidden" name="aktion" value="loeschen">
                                        <input type="hidden" name="mitarbeiter_id" value="<?php echo htmlspecialchars($fahrer['MitarbeiterID']); ?>">
                                        <button type="submit">Löschen</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">Noch keine Fahrer vorhanden.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
