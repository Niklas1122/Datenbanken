<div>
    <h2>Fahrer zu Rennen anmelden</h2>

    <?php if (count($zukuenftige_rennen) === 0): ?>
        <p>Keine zukünftigen Rennen vorhanden.</p>
    <?php else: ?>
        <label style="<?= $label_stil; ?>">
            Rennen<br>
            <select id="rennen_auswahl">
                <option value="">-- Rennen wählen --</option>
                <?php foreach ($zukuenftige_rennen as $rennen): ?>
                    <option value="<?= htmlspecialchars($rennen['RennID']); ?>">
                        <?= htmlspecialchars($rennen['Datum'] . ' – ' . $rennen['Standort']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label style="<?= $label_stil; ?>">
            Anzahl Fahrer<br>
            <input type="number" id="anzahl_fahrer" min="1" value="1">
        </label>

        <button type="button" onclick="zeigeAnmeldeTabelle()">Weiter</button>

        <form action="teamchef_dashboard.php" method="post" id="anmelde_form" style="display:none; margin-top:10px;">
            <input type="hidden" name="aktion" value="rennen_anmelden">
            <input type="hidden" name="rennen_id" id="rennen_id_hidden">
            <table>
                <thead>
                    <tr>
                        <th>Nr.</th>
                        <th>Fahrer</th>
                    </tr>
                </thead>
                <tbody id="fahrer_tabelle"></tbody>
            </table>
            <br>
            <button type="submit">Anmelden</button>
        </form>

        <script>
        var fahrerOptionen = <?php
            $optionen = '';
            foreach ($fahrer_array as $f) {
                $id = htmlspecialchars($f['MitarbeiterID'], ENT_QUOTES);
                $name = htmlspecialchars($f['Name'], ENT_QUOTES);
                $optionen .= '<option value="' . $id . '">' . $id . ' – ' . $name . '</option>';
            }
            echo json_encode($optionen);
        ?>;

        function zeigeAnmeldeTabelle() {
            var rennenId = document.getElementById('rennen_auswahl').value;
            var anzahl = parseInt(document.getElementById('anzahl_fahrer').value);

            if (!rennenId || !anzahl || anzahl < 1) return;

            document.getElementById('rennen_id_hidden').value = rennenId;

            var tbody = document.getElementById('fahrer_tabelle');
            tbody.innerHTML = '';

            for (var i = 0; i < anzahl; i++) {
                var tr = document.createElement('tr');
                tr.innerHTML = '<td>' + (i + 1) + '</td>'
                    + '<td><select name="fahrer_id[]" onchange="pruefeDoubletten()">'
                    + '<option value="">-- Fahrer wählen --</option>'
                    + fahrerOptionen
                    + '</select></td>';
                tbody.appendChild(tr);
            }

            document.getElementById('anmelde_form').style.display = '';
        }

        function pruefeDoubletten() {
            var selects = document.querySelectorAll('#fahrer_tabelle select');
            var gewaehlt = [];
            selects.forEach(function(sel) {
                gewaehlt.push(sel.value);
            });
            selects.forEach(function(sel) {
                var optionen = sel.querySelectorAll('option');
                optionen.forEach(function(opt) {
                    if (opt.value !== '' && opt.value !== sel.value && gewaehlt.indexOf(opt.value) !== -1) {
                        opt.disabled = true;
                    } else {
                        opt.disabled = false;
                    }
                });
            });
        }
        </script>
    <?php endif; ?>
</div>

<div>
    <h2>Anmeldungen kopieren</h2>

    <?php if (count($rennen_mit_teilnahmen) === 0): ?>
        <p>Keine Rennen mit vorhandenen Anmeldungen gefunden.</p>
    <?php elseif (count($zukuenftige_rennen) === 0): ?>
        <p>Keine zukünftigen Rennen zum Kopieren vorhanden.</p>
    <?php else: ?>
        <form action="teamchef_dashboard.php" method="post">
            <input type="hidden" name="aktion" value="teilnahme_kopieren">

            <label style="<?= $label_stil; ?>">
                Anmeldungen von Rennen<br>
                <select name="quell_renn_id">
                    <option value="">-- Rennen wählen --</option>
                    <?php foreach ($rennen_mit_teilnahmen as $rennen): ?>
                        <option value="<?= htmlspecialchars($rennen['RennID']); ?>">
                            <?= htmlspecialchars($rennen['Datum'] . ' – ' . $rennen['Standort']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label style="<?= $label_stil; ?>">
                Kopieren nach Rennen<br>
                <select name="ziel_renn_id">
                    <option value="">-- Rennen wählen --</option>
                    <?php foreach ($zukuenftige_rennen as $rennen): ?>
                        <option value="<?= htmlspecialchars($rennen['RennID']); ?>">
                            <?= htmlspecialchars($rennen['Datum'] . ' – ' . $rennen['Standort']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <button type="submit">Anmeldungen kopieren</button>
        </form>
    <?php endif; ?>
</div>
