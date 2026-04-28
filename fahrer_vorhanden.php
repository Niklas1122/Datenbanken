<div>
    <h2>Vorhandene Fahrer</h2>
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
            <?php if (count($fahrer_array) > 0): ?>
                <?php foreach ($fahrer_array as $fahrer): ?>
                    <tr>
                        <td><?= htmlspecialchars($fahrer['MitarbeiterID']); ?></td>
                        <td><?= htmlspecialchars($fahrer['Name']); ?></td>
                        <td><?= htmlspecialchars(trim($fahrer['Strasse'] . ' ' . $fahrer['Hausnr']) . ', ' . trim($fahrer['PLZ'] . ' ' . $fahrer['Ort'])); ?></td>
                        <td><?= htmlspecialchars($fahrer['TelNr']); ?></td>
                        <td>
                            <a href="teamchef_dashboard.php?edit=<?= urlencode($fahrer['MitarbeiterID']); ?>">Bearbeiten</a>
                            <form action="teamchef_dashboard.php" method="post">
                                <input type="hidden" name="aktion" value="loeschen">
                                <input type="hidden" name="mitarbeiter_id" value="<?= htmlspecialchars($fahrer['MitarbeiterID']); ?>">
                                <button type="submit">Löschen</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Noch keine Fahrer vorhanden.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
