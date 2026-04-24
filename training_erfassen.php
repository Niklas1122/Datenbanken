<div>
    <h2>Neues Training erfassen</h2>
    <form action="teamchef_dashboard.php" method="post">
        <input type="hidden" name="aktion" value="training_speichern">

        <label style="<?php echo $label_stil; ?>">
            Fahrer<br>
            <select name="training_fahrer">
                <option value="">Bitte wählen</option>
                <?php foreach ($fahrer_array as $fahrer): ?>
                    <option value="<?php echo htmlspecialchars($fahrer['MitarbeiterID']); ?>" <?php echo $training_form['mitarbeiter_id'] === $fahrer['MitarbeiterID'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($fahrer['MitarbeiterID'] . ' - ' . $fahrer['Name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label style="<?php echo $label_stil; ?>">
            Datum<br>
            <input type="date" name="training_datum" value="<?php echo htmlspecialchars($training_form['datum']); ?>">
        </label>

        <label style="<?php echo $label_stil; ?>">
            Kilometer<br>
            <input type="number" step="0.1" name="training_kilometer" value="<?php echo htmlspecialchars($training_form['kilometer']); ?>">
        </label>

        <label style="<?php echo $label_stil; ?>">
            Trainingsziel<br>
            <select name="training_ziel">
                <?php foreach ($trainingsziele as $ziel): ?>
                    <option value="<?php echo htmlspecialchars($ziel); ?>" <?php echo $training_form['trainingsziel'] === $ziel ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($ziel); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <button type="submit">Training speichern</button>
    </form>
</div>
