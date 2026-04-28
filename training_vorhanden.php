<div>
    <h2>Vorhandene Trainings</h2>
    <table>
        <thead>
            <tr>
                <th>Mitarbeiter-ID</th>
                <th>Datum</th>
                <th>Kilometer</th>
                <th>Trainingsziel</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($trainings_liste) > 0): ?>
                <?php foreach ($trainings_liste as $training): ?>
                    <tr>
                        <td><?= htmlspecialchars($training['MitarbeiterID']); ?></td>
                        <td><?= htmlspecialchars($training['Datum']); ?></td>
                        <td><?= htmlspecialchars($training['Kilometer']); ?></td>
                        <td><?= htmlspecialchars($training['TrainingszielBezeichnung']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Noch keine Trainings vorhanden.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
