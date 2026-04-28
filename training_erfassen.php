<div>
    <h2>Neues Training erfassen</h2>
    <form action="teamchef_dashboard.php" method="post">
        <input type="hidden" name="aktion" value="training_speichern">

        <label style="<?= $label_stil; ?>">
            Fahrer<br>
            <select name="training_fahrer">
                <option value="">Bitte wählen</option>
                <?php foreach ($fahrer_array as $fahrer): ?>
                    <option value="<?= htmlspecialchars($fahrer['MitarbeiterID']); ?>" <?= $training_form['mitarbeiter_id'] === $fahrer['MitarbeiterID'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($fahrer['MitarbeiterID'] . ' - ' . $fahrer['Name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label style="<?= $label_stil; ?>">
            Datum<br>
            <input type="date" name="training_datum" value="<?= htmlspecialchars($training_form['datum']); ?>">
        </label>

        <label style="<?= $label_stil; ?>">
            Kilometer<br>
            <input type="number" step="0.1" name="training_kilometer" value="<?= htmlspecialchars($training_form['kilometer']); ?>">
        </label>

        <label style="<?= $label_stil; ?>">
            Trainingsziel<br>
            <select name="training_ziel">
                <?php foreach ($trainingsziele as $ziel): ?>
                    <option value="<?= htmlspecialchars($ziel); ?>" <?= $training_form['trainingsziel'] === $ziel ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($ziel); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <button type="submit">Training speichern</button>
    </form>
</div>
