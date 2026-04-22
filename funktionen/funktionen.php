<?php

function tabelleExistiert($connection, $tabellenname)
{
    $tabellenname = mysqli_real_escape_string($connection, $tabellenname);
    $sql = "SHOW TABLES LIKE '$tabellenname'";
    $ergebnis = mysqli_query($connection, $sql);

    return $ergebnis && mysqli_num_rows($ergebnis) > 0;
}

function holeTrainingsziele($connection)
{
    $ziele = array();

    if (!tabelleExistiert($connection, 'TRAININGSZIEL')) {
        return array('Ausdauer', 'Sprintkraft', 'Steigungen');
    }

    try {
        $ergebnis = mysqli_query($connection, "SELECT Bezeichnung FROM TRAININGSZIEL ORDER BY Bezeichnung");
    } catch (mysqli_sql_exception $e) {
        return array('Ausdauer', 'Sprintkraft', 'Steigungen');
    }

    if ($ergebnis) {
        while ($zeile = mysqli_fetch_assoc($ergebnis)) {
            $ziele[] = $zeile['Bezeichnung'];
        }
    }

    return count($ziele) > 0 ? $ziele : array('Ausdauer', 'Sprintkraft', 'Steigungen');
}

function speichereTraining($connection, $mitarbeiter_id, $datum, $kilometer, $trainingsziel)
{
    if (!tabelleExistiert($connection, 'TRAINING')) {
        return "Die Tabelle TRAINING fehlt noch.";
    }

    $mitarbeiter_id = mysqli_real_escape_string($connection, trim($mitarbeiter_id));
    $datum = mysqli_real_escape_string($connection, trim($datum));
    $kilometer = mysqli_real_escape_string($connection, trim($kilometer));
    $trainingsziel = mysqli_real_escape_string($connection, trim($trainingsziel));

    try {
        $pruefung = mysqli_query(
            $connection,
            "SELECT MitarbeiterID FROM TRAINING WHERE MitarbeiterID = '$mitarbeiter_id' AND Datum = '$datum'"
        );
    } catch (mysqli_sql_exception $e) {
        return "Training konnte nicht gespeichert werden.";
    }

    if ($pruefung && mysqli_num_rows($pruefung) > 0) {
        return "Für diesen Fahrer gibt es an diesem Tag schon ein Training.";
    }

    $sql = "INSERT INTO TRAINING (Datum, Kilometer, MitarbeiterID, TrainingszielBezeichnung)
            VALUES ('$datum', '$kilometer', '$mitarbeiter_id', '$trainingsziel')";

    try {
        if (mysqli_query($connection, $sql)) {
            return "";
        }
    } catch (mysqli_sql_exception $e) {
        return "Training konnte nicht gespeichert werden.";
    }

    return "Training konnte nicht gespeichert werden.";
}

function holeTrainingsliste($connection)
{
    $liste = array();

    if (!tabelleExistiert($connection, 'TRAINING')) {
        return $liste;
    }

    $sql = "SELECT MitarbeiterID, Datum, Kilometer, TrainingszielBezeichnung FROM TRAINING ORDER BY Datum DESC";

    try {
        $ergebnis = mysqli_query($connection, $sql);
    } catch (mysqli_sql_exception $e) {
        return $liste;
    }

    if ($ergebnis) {
        while ($zeile = mysqli_fetch_assoc($ergebnis)) {
            $liste[] = $zeile;
        }
    }

    return $liste;
}
