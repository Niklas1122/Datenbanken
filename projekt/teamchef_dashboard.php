<?php
session_start();
require_once __DIR__ . '/../funktionen/Connection.php';
require_once __DIR__ . '/../funktionen/funktionen.php';
require_once __DIR__ . '/../funktionen/team_funktionen.php';
require_once __DIR__ . '/../funktionen/fahrer_funktionen.php';
require_once __DIR__ . '/../funktionen/training_funktionen.php';

if (!isset($_SESSION['teamchef_login'])) {
    header("Location: index.php");
    exit;
}

$teamchef_login = $_SESSION['teamchef_login'];
$leerer_fahrer = leeresFahrerFormular();
$teamname = '';
$meldung = '';
$fehler = '';
$fahrer_array = array();
$trainings_liste = array();
$bearbeiten = isset($_GET['edit']) && $_GET['edit'] !== '';
$fahrer_form = $leerer_fahrer;
$label_stil = 'display:block; margin-bottom:10px;';
$trainingsziele = holeTrainingsziele($connection);
$training_form = leeresTrainingFormular($trainingsziele);
$training_moeglich = tabelleExistiert($connection, 'TRAINING');
$teamname = holeTeamnameZumLogin($connection, $teamchef_login);

if ($teamname === '') {
    $fehler = "Kein Team zum angemeldeten Teamchef gefunden.";
}

if ($teamname !== "") {
    $aktion = $_POST['aktion'] ?? '';
    $modus = $_POST['modus'] ?? 'neu';

    if ($aktion === 'loeschen' && !empty($_POST['mitarbeiter_id'])) {
        if (loescheFahrer($connection, $teamname, $_POST['mitarbeiter_id'])) {
            $meldung = "Fahrer wurde gelöscht.";
        } else {
            $fehler = "Fahrer konnte nicht gelöscht werden.";
        }
    }

    if ($aktion === 'speichern') {
        $fahrer_form = liesFahrerFormularAusPost();
        $ergebnis = speichereFahrer($connection, $teamname, $fahrer_form, $modus);
        $meldung = $ergebnis['meldung'];
        $fehler = $ergebnis['fehler'];
        $bearbeiten = $ergebnis['bearbeiten'];

        if ($ergebnis['formular_zuruecksetzen']) {
            $fahrer_form = $leerer_fahrer;
        }
    }

    if ($aktion === 'training_speichern' && $training_moeglich) {
        $training_form = liesTrainingFormularAusPost($trainingsziele);
        $ergebnis = speichereTrainingAusFormular($connection, $training_form, $trainingsziele);
        $meldung = $ergebnis['meldung'];
        $fehler = $ergebnis['fehler'];
        $training_form = $ergebnis['training_form'];
    }

    if ($bearbeiten && $fahrer_form['MitarbeiterID'] === '') {
        $fahrer_form = holeFahrerZumBearbeiten($connection, $teamname, $_GET['edit']);
    }

    $fahrer_array = holeFahrerListe($connection, $teamname);

    if ($training_moeglich) {
        $trainings_liste = holeTrainingsliste($connection);
    }
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
        <p><a href="abmelden.php">Abmelden</a></p>

        <?php if ($meldung !== ""): ?>
            <p><?php echo htmlspecialchars($meldung); ?></p>
        <?php endif; ?>

        <?php if ($fehler !== ""): ?>
            <p><?php echo htmlspecialchars($fehler); ?></p>
        <?php endif; ?>

        <?php require __DIR__ . '/../views/fahrer_anlegen.php'; ?>
        <?php require __DIR__ . '/../views/fahrer_vorhanden.php'; ?>

        <?php if ($training_moeglich): ?>
            <?php require __DIR__ . '/../views/training_erfassen.php'; ?>
            <?php require __DIR__ . '/../views/training_vorhanden.php'; ?>
        <?php endif; ?>
    </div>
</body>
</html>
