<div>
    <h2><?php echo $bearbeiten ? 'Fahrer ändern' : 'Fahrer anlegen'; ?></h2>
    <form action="teamchef_dashboard.php" method="post">
        <input type="hidden" name="aktion" value="speichern">
        <input type="hidden" name="modus" value="<?php echo $bearbeiten ? 'bearbeiten' : 'neu'; ?>">

        <label style="<?php echo $label_stil; ?>">
            Mitarbeiter-ID<br>
            <input type="text" name="mitarbeiter_id" value="<?php echo htmlspecialchars($fahrer_form['MitarbeiterID']); ?>" <?php echo $bearbeiten ? 'readonly' : ''; ?>>
        </label>
        <label style="<?php echo $label_stil; ?>">
            Name<br>
            <input type="text" name="fahrer_name" value="<?php echo htmlspecialchars($fahrer_form['Name']); ?>">
        </label>
        <label style="<?php echo $label_stil; ?>">
            PLZ<br>
            <input type="text" name="plz" value="<?php echo htmlspecialchars($fahrer_form['PLZ']); ?>">
        </label>
        <label style="<?php echo $label_stil; ?>">
            Ort<br>
            <input type="text" name="ort" value="<?php echo htmlspecialchars($fahrer_form['Ort']); ?>">
        </label>
        <label style="<?php echo $label_stil; ?>">
            Strasse<br>
            <input type="text" name="strasse" value="<?php echo htmlspecialchars($fahrer_form['Strasse']); ?>">
        </label>
        <label style="<?php echo $label_stil; ?>">
            Hausnummer<br>
            <input type="text" name="hausnr" value="<?php echo htmlspecialchars($fahrer_form['Hausnr']); ?>">
        </label>
        <label style="<?php echo $label_stil; ?>">
            Telefonnummer<br>
            <input type="text" name="telnr" value="<?php echo htmlspecialchars($fahrer_form['TelNr']); ?>">
        </label>
        <button type="submit">Speichern</button>
    </form>
</div>
