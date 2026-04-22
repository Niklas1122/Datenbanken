<?php
session_start();

$meldung = "";
$label_stil = 'display:block; margin-bottom:10px;';
include 'Reg_Team.php';
include 'Anm_TeamChef.php';
include 'Reg_RV.php';
include 'Anm_RV.php';

if (isset($_SESSION['teamchef_login'])) {
    header("Location: team_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>RennradSV</title>
</head>
<body>
    <div>
        <h1>RennradSV</h1>
        <?php if ($meldung !== ""): ?>
            <p><?php echo $meldung; ?></p>
        <?php endif; ?>
        <div>
            <h2>Neues Team anlegen</h2>
            <form action="#" method="post">
                <input type="hidden" name="form_typ" value="team_registrierung">
                <label style="<?php echo $label_stil; ?>">
                    Teamname<br>
                    <input type="text" name="team_name">
                </label>
                <label style="<?php echo $label_stil; ?>">
                    Vorname Teamchef<br>
                    <input type="text" name="teamchef_vorname">
                </label>
                <label style="<?php echo $label_stil; ?>">
                    Nachname Teamchef<br>
                    <input type="text" name="teamchef_name">
                </label>
                <label style="<?php echo $label_stil; ?>">
                    Loginname<br>
                    <input type="text" name="teamchef_login">
                </label>
                <label style="<?php echo $label_stil; ?>">
                    Passwort<br>
                    <input type="password" name="teamchef_passwort">
                </label>
                <button type="submit">Team speichern</button>
            </form>
        </div>

        <div>
            <h2>Teamchef anmelden</h2>
            <form action="#" method="post">
                <input type="hidden" name="form_typ" value="teamchef_login">
                <label style="<?php echo $label_stil; ?>">
                    Loginname<br>
                    <input type="text" name="login_name">
                </label>
                <label style="<?php echo $label_stil; ?>">
                    Passwort<br>
                    <input type="password" name="password">
                </label>
                <button type="submit">Anmelden</button>
            </form>
        </div>

        <div>
            <h2>Rennveranstalter registrieren</h2>
            <form action="#" method="post">
                <input type="hidden" name="form_typ" value="veranstalter_registrierung">
                <label style="<?php echo $label_stil; ?>">
                    Loginname<br>
                    <input type="text" name="veranstalter_name" required>
                </label>
                <label style="<?php echo $label_stil; ?>">
                    Passwort<br>
                    <input type="password" name="veranstalter_passwort" required>
                </label>
                <button type="submit">Registrieren</button>
            </form>
        </div>

        <div>
            <h2>Rennveranstalter anmelden</h2>
            <form action="#" method="post">
                <input type="hidden" name="form_typ" value="veranstalter_login">
                <label style="<?php echo $label_stil; ?>">
                    Loginname<br>
                    <input type="text" name="veranstalter_login">
                </label>
                <label style="<?php echo $label_stil; ?>">
                    Passwort<br>
                    <input type="password" name="veranstalter_passwort_login">
                </label>
                <button type="submit">Anmelden</button>
            </form>
        </div>
    </div>
</body>
</html>
