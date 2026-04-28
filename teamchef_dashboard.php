<?php
session_start();
require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/funktionen.php';

require_once __DIR__ . '/fahrer_funktionen.php';
require_once __DIR__ . '/training_funktionen.php';
require_once __DIR__ . '/auswertung_klasse.php';

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
$rennen_moeglich = tabelleExistiert($connection, 'RENNEN') && tabelleExistiert($connection, 'TEILNAHME');
$zukuenftige_rennen = array();
$rennen_mit_teilnahmen = array();
$auswertung_filter = ['trainingsziel' => '', 'von' => '', 'bis' => ''];
$auswertung_ergebnisse = array();
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

    if ($aktion === 'teilnahme_kopieren' && $rennen_moeglich) {
        $anzahl = kopiereTeilnahmen($connection, $teamname, $_POST['quell_renn_id'] ?? '', $_POST['ziel_renn_id'] ?? '');
        if ($anzahl > 0) {
            $meldung = "$anzahl Anmeldung(en) erfolgreich kopiert.";
        } else {
            $fehler = "Kopieren fehlgeschlagen. Möglicherweise sind die Fahrer bereits für das Zielrennen gemeldet.";
        }
    }

    if ($aktion === 'rennen_anmelden' && $rennen_moeglich) {
        $fahrer_ids = $_POST['fahrer_id'] ?? array();
        $fahrer_ids = array_filter(array_unique($fahrer_ids));
        $ergebnis = meldeFahrerZuRennen($connection, $_POST['rennen_id'] ?? '', $fahrer_ids);
        if ($ergebnis > 0) {
            $meldung = "$ergebnis Fahrer wurde(n) erfolgreich angemeldet.";
        } else {
            $fehler = "Keine Fahrer konnten angemeldet werden. Möglicherweise sind sie bereits für dieses Rennen gemeldet.";
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

    if ($aktion === 'auswertung_filtern' && $training_moeglich) {
        $auswertung_filter = [
            'trainingsziel' => trim($_POST['auswertung_ziel'] ?? ''),
            'von'           => trim($_POST['auswertung_von'] ?? ''),
            'bis'           => trim($_POST['auswertung_bis'] ?? ''),
        ];
        foreach ($fahrer_array as $fahrer) {
            $obj = new FahrerAuswertung(
                $fahrer['MitarbeiterID'],
                $fahrer['Name'],
                $auswertung_filter['trainingsziel'],
                $auswertung_filter['von'],
                $auswertung_filter['bis']
            );
            $obj->berechne($connection);
            $auswertung_ergebnisse[] = $obj;
        }
    }

    if ($training_moeglich) {
        $trainings_liste = holeTrainingsliste($connection);
    }

    if ($rennen_moeglich) {
        $zukuenftige_rennen = holeZukuenftigeRennen($connection);
        $rennen_mit_teilnahmen = holeRennenMitTeilnahmen($connection, $teamname);
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
        <p>Angemeldet als <?= htmlspecialchars($teamchef_login); ?> für Team <?= htmlspecialchars($teamname); ?></p>
        <p><a href="abmelden.php">Abmelden</a></p>

        <?php if ($meldung !== ""): ?>
            <p><?= htmlspecialchars($meldung); ?></p>
        <?php endif; ?>

        <?php if ($fehler !== ""): ?>
            <p><?= htmlspecialchars($fehler); ?></p>
        <?php endif; ?>

        <?php require __DIR__ . '/fahrer_anlegen.php'; ?>
        <?php require __DIR__ . '/fahrer_vorhanden.php'; ?>

        <?php if ($training_moeglich): ?>
            <?php require __DIR__ . '/training_erfassen.php'; ?>
            <?php require __DIR__ . '/training_vorhanden.php'; ?>
            <?php require __DIR__ . '/auswertung_bereich.php'; ?>
        <?php endif; ?>

        <?php if ($rennen_moeglich): ?>
            <?php require __DIR__ . '/rennen_anmelden.php'; ?>
        <?php endif; ?>
    </div>
</body>
</html>
