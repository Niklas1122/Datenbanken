<?php // Bedi ?>
<div>
    <h2>Vorhandene Fahrer</h2>
    <!-- Tabelle mit allen vorhandenen fahrern -->
    <table>
        <thead>
            <tr>
                <th>Mitarbeiter-ID</th>
                <th>Name</th>
                <th>Adresse</th>
                <th>Telefon</th>
                <th>Aktion</th>
            </tr>
        </thead>
        <tbody>
            <!-- Prüft ob schon Fahrer gespeichert sind -->
            <?php if (count($fahrer_array) > 0): ?>
                <!-- Gibt jeden Fahrer als tabellenzeile aus -->
                <?php foreach ($fahrer_array as $fahrer): ?>
                    <tr>
                        <td><?= htmlspecialchars($fahrer['MitarbeiterID']); ?></td>
                        <td><?= htmlspecialchars($fahrer['Name']); ?></td>
                        <td><?= htmlspecialchars(trim($fahrer['Strasse'] . ' ' . $fahrer['Hausnr']) . ', ' . trim($fahrer['PLZ'] . ' ' . $fahrer['Ort'])); ?></td>
                        <td><?= htmlspecialchars($fahrer['TelNr']); ?></td>
                        <td>
                            <!-- Link zum bearbeiten des ausgewählten fahrers -->
                            <a href="teamchef_dashboard.php?edit=<?= urlencode($fahrer['MitarbeiterID']); ?>">Bearbeiten</a>
                            <!-- Formular zum Löschen eines Fahrers -->
                            <form action="teamchef_dashboard.php" method="post">
                                <input type="hidden" name="aktion" value="loeschen">
                                <input type="hidden" name="mitarbeiter_id" value="<?= htmlspecialchars($fahrer['MitarbeiterID']); ?>">
                                <button type="submit">Löschen</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Falls keine fahrer vorhanden sind, wird ein hinweis gezeigt -->
                <tr>
                    <td colspan="5">Noch keine Fahrer vorhanden.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
