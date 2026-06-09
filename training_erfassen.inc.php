<?php // Niklas ?>
<div>
    <h2>Neues Training erfassen</h2>
    <!-- Formular sendet per POST an teamchef_dashboard.php -->
    <form action="teamchef_dashboard.php" method="post">
        <input type="hidden" name="aktion" value="training_speichern"> <!-- teilt dem Dashboard mit, welche Aktion ausgeführt werden soll -->

        <!-- Dropdown mit allen Fahrern des Teams -->
        <label style="<?= $label_stil; ?>">
            Fahrer<br>
            <select name="training_fahrer">
                <option value="">Bitte wählen</option>
                <?php foreach ($fahrer_array as $fahrer): ?> <!-- schleife über alle Fahrer des Teams -->
                    <option value="<?= htmlspecialchars($fahrer['MitarbeiterID']); ?>"
                        <?= $training_form['mitarbeiter_id'] === $fahrer['MitarbeiterID'] ? 'selected' : ''; ?>> <!-- zuvor gewählten Fahrer wieder vorauswählen -->
                        <?= htmlspecialchars($fahrer['MitarbeiterID'] . ' - ' . $fahrer['Name']); ?> <!-- htmlspecialchars verhindert XSS -->
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label style="<?= $label_stil; ?>">
            Datum<br>
            <!-- value befüllt das Feld erneut, falls das Formular nach einem Fehler neu angezeigt wird -->
            <input type="date" name="training_datum" value="<?= htmlspecialchars($training_form['datum']); ?>">
        </label>

        <label style="<?= $label_stil; ?>">
            Kilometer<br>
            <!-- step="0.1" erlaubt Dezimalzahlen (z. B. 12.5 km) -->
            <input type="number" step="0.1" name="training_kilometer" value="<?= htmlspecialchars($training_form['kilometer']); ?>">
        </label>

        <!-- Dropdown mit Trainingszielen aus der Datenbank -->
        <label style="<?= $label_stil; ?>">
            Trainingsziel<br>
            <select name="training_ziel">
                <?php foreach ($trainingsziele as $ziel): ?>
                    <option value="<?= htmlspecialchars($ziel); ?>"
                        <?= $training_form['trainingsziel'] === $ziel ? 'selected' : ''; ?>> <!-- zuvor gewähltes Ziel wieder markieren -->
                        <?= htmlspecialchars($ziel); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <button type="submit">Training speichern</button>
    </form>
</div>
