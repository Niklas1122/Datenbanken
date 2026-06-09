<?php // Niklas ?>
<div>
    <h2>Vorhandene Trainings</h2>
    <!-- Tabelle zeigt alle Trainings des gesamten Teams -->
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
            <?php if (count($trainings_liste) > 0): ?> <!-- prüfen ob überhaupt Trainings vorhanden sind -->
                <?php foreach ($trainings_liste as $training): ?> <!-- jede Zeile aus der DB-Abfrage ausgeben -->
                    <tr>
                        <td><?= htmlspecialchars($training['MitarbeiterID']); ?></td>
                        <td><?= htmlspecialchars($training['Datum']); ?></td>
                        <td><?= htmlspecialchars($training['Kilometer']); ?></td>
                        <td><?= htmlspecialchars($training['TrainingszielBezeichnung']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Ersatz-Zeile, die alle 4 Spalten überspannt, wenn keine Daten vorhanden sind -->
                <tr>
                    <td colspan="4">Noch keine Trainings vorhanden.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
