<?php

function leeresTrainingFormular($trainingsziele)
{
    return array(
        'mitarbeiter_id' => '',
        'datum' => '',
        'kilometer' => '',
        'trainingsziel' => count($trainingsziele) > 0 ? $trainingsziele[0] : 'Ausdauer'
    );
}

function liesTrainingFormularAusPost($trainingsziele)
{
    $formular = leeresTrainingFormular($trainingsziele);
    $formular['mitarbeiter_id'] = trim($_POST['training_fahrer'] ?? '');
    $formular['datum'] = trim($_POST['training_datum'] ?? '');
    $formular['kilometer'] = trim($_POST['training_kilometer'] ?? '');
    $formular['trainingsziel'] = trim($_POST['training_ziel'] ?? $formular['trainingsziel']);

    return $formular;
}

function speichereTrainingAusFormular($connection, $training_form, $trainingsziele)
{
    if (
        $training_form['mitarbeiter_id'] === '' ||
        $training_form['datum'] === '' ||
        $training_form['kilometer'] === '' ||
        $training_form['trainingsziel'] === ''
    ) {
        return array(
            'meldung' => '',
            'fehler' => 'Bitte alle Trainingsfelder ausfüllen.',
            'training_form' => $training_form
        );
    }

    $training_fehler = speichereTraining(
        $connection,
        $training_form['mitarbeiter_id'],
        $training_form['datum'],
        $training_form['kilometer'],
        $training_form['trainingsziel']
    );

    if ($training_fehler === '') {
        return array(
            'meldung' => 'Training wurde gespeichert.',
            'fehler' => '',
            'training_form' => leeresTrainingFormular($trainingsziele)
        );
    }

    return array(
        'meldung' => '',
        'fehler' => $training_fehler,
        'training_form' => $training_form
    );
}
