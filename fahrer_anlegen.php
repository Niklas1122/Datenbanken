<div>
    <h2><?= $bearbeiten ? 'Fahrer ändern' : 'Fahrer anlegen'; ?></h2>
    <form action="teamchef_dashboard.php" method="post">
        <input type="hidden" name="aktion" value="speichern">
        <input type="hidden" name="modus" value="<?= $bearbeiten ? 'bearbeiten' : 'neu'; ?>">

        <label style="<?= $label_stil; ?>">
            Mitarbeiter-ID<br>
            <input type="text" name="mitarbeiter_id" value="<?= htmlspecialchars($fahrer_form['MitarbeiterID']); ?>" <?= $bearbeiten ? 'readonly' : ''; ?>>
        </label>
        <label style="<?= $label_stil; ?>">
            Name<br>
            <input type="text" name="fahrer_name" value="<?= htmlspecialchars($fahrer_form['Name']); ?>">
        </label>
        <label style="<?= $label_stil; ?>">
            PLZ<br>
            <input type="text" name="plz" value="<?= htmlspecialchars($fahrer_form['PLZ']); ?>">
        </label>
        <label style="<?= $label_stil; ?>">
            Ort<br>
            <input type="text" name="ort" value="<?= htmlspecialchars($fahrer_form['Ort']); ?>">
        </label>
        <label style="<?= $label_stil; ?>">
            Strasse<br>
            <input type="text" name="strasse" value="<?= htmlspecialchars($fahrer_form['Strasse']); ?>">
        </label>
        <label style="<?= $label_stil; ?>">
            Hausnummer<br>
            <input type="text" name="hausnr" value="<?= htmlspecialchars($fahrer_form['Hausnr']); ?>">
        </label>
        <label style="<?= $label_stil; ?>">
            Telefonnummer<br>
            <input type="text" name="telnr" value="<?= htmlspecialchars($fahrer_form['TelNr']); ?>">
        </label>
        <button type="submit">Speichern</button>
    </form>
</div>
