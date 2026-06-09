<?php
// Niklas

// Gibt ein leeres Formular-Array zurück, mit dem ersten Trainingsziel als Vorauswahl
function leeresTrainingFormular($trainingsziele)
{
    return array(
        'mitarbeiter_id' => '',
        'datum'          => '',
        'kilometer'      => '',
        // erstes Ziel vorauswählen, falls die Liste nicht leer ist, sonst Fallback "Ausdauer"
        'trainingsziel'  => count($trainingsziele) > 0 ? $trainingsziele[0] : 'Ausdauer'
    );
}

// Liest die gesendeten POST-Felder aus und befüllt damit das Formular-Array
function liesTrainingFormularAusPost($trainingsziele)
{
    $formular = leeresTrainingFormular($trainingsziele); // leeres Array als Basis
    $formular['mitarbeiter_id'] = trim($_POST['training_fahrer']    ?? ''); // Fahrer-ID aus Dropdown
    $formular['datum']          = trim($_POST['training_datum']      ?? ''); // Datum aus Datumsfeld
    $formular['kilometer']      = trim($_POST['training_kilometer']  ?? ''); // Kilometeranzahl
    $formular['trainingsziel']  = trim($_POST['training_ziel']       ?? $formular['trainingsziel']); // Ziel, Fallback auf Vorauswahl

    return $formular;
}

// Validiert das Formular und speichert das Training in der Datenbank
function speichereTrainingAusFormular($connection, $training_form, $trainingsziele)
{
    // Prüfen ob alle Pflichtfelder ausgefüllt sind
    if (
        $training_form['mitarbeiter_id'] === '' ||
        $training_form['datum']          === '' ||
        $training_form['kilometer']      === '' ||
        $training_form['trainingsziel']  === ''
    ) {
        // Validierungsfehler zurückgeben, Formular mit eingegebenen Werten behalten
        return array(
            'meldung'       => '',
            'fehler'        => 'Bitte alle Trainingsfelder ausfüllen.',
            'training_form' => $training_form
        );
    }

    // speichereTraining() führt das eigentliche INSERT in die Datenbank durch
    $training_fehler = speichereTraining(
        $connection,
        $training_form['mitarbeiter_id'],
        $training_form['datum'],
        $training_form['kilometer'],
        $training_form['trainingsziel']
    );

    if ($training_fehler === '') {
        // Kein Fehler: Erfolgsmeldung + Formular zurücksetzen
        return array(
            'meldung'       => 'Training wurde gespeichert.',
            'fehler'        => '',
            'training_form' => leeresTrainingFormular($trainingsziele)
        );
    }

    // Datenbankfehler: Fehlermeldung + eingegebene Werte erhalten
    return array(
        'meldung'       => '',
        'fehler'        => $training_fehler,
        'training_form' => $training_form
    );
}
