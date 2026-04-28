<h2>Auswertungsbereich</h2>
<form method="post">
    <input type="hidden" name="aktion" value="auswertung_filtern">
    <label style="<?= $label_stil ?>">
        Trainingsziel:
        <select name="auswertung_ziel">
            <option value="">Alle Ziele</option>
            <?php foreach ($trainingsziele as $ziel): ?>
                <option value="<?= htmlspecialchars($ziel) ?>"
                    <?= $auswertung_filter['trainingsziel'] === $ziel ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ziel) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label style="<?= $label_stil ?>">
        Von: <input type="date" name="auswertung_von" value="<?= htmlspecialchars($auswertung_filter['von']) ?>">
    </label>
    <label style="<?= $label_stil ?>">
        Bis: <input type="date" name="auswertung_bis" value="<?= htmlspecialchars($auswertung_filter['bis']) ?>">
    </label>
    <button type="submit">Auswerten</button>
</form>

<?php if (!empty($auswertung_ergebnisse)): ?>
    <?php foreach ($auswertung_ergebnisse as $auswertung): ?>
        <h3><?= htmlspecialchars($auswertung->getName()) ?> (<?= htmlspecialchars($auswertung->getMitarbeiterID()) ?>)</h3>
        <?php $monate = $auswertung->getMonate(); ?>
        <?php if (empty($monate)): ?>
            <p>Keine Trainingsdaten für diesen Zeitraum.</p>
        <?php else: ?>
            <table border="1">
                <tr>
                    <th>Monat</th>
                    <th>Summe (km)</th>
                    <th>Durchschnitt (km)</th>
                    <th>Minimum (km)</th>
                    <th>Maximum (km)</th>
                    <th>Median (km)</th>
                    <th>Standardabweichung (km)</th>
                </tr>
                <?php foreach ($monate as $monat => $stats): ?>
                    <tr>
                        <td><?= htmlspecialchars($monat) ?></td>
                        <td><?= $stats['summe'] ?></td>
                        <td><?= $stats['durchschnitt'] ?></td>
                        <td><?= $stats['minimum'] ?></td>
                        <td><?= $stats['maximum'] ?></td>
                        <td><?= $stats['median'] ?></td>
                        <td><?= $stats['stdabw'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
