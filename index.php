<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>RennradSV</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="page">
        <h1>RennradSV</h1>
        <div class="form-box">
            <h2>Neues Team anlegen</h2>
            <form action="#" method="post">
                <label>
                    Teamname
                    <input type="text" name="team_name">
                </label>
                <label>
                    Vorname Teamchef
                    <input type="text" name="teamchef_vorname">
                </label>
                <label>
                    Nachname Teamchef
                    <input type="text" name="teamchef_name">
                </label>
                <label>
                    Loginname
                    <input type="text" name="teamchef_login">
                </label>
                <label>
                    Kennwort
                    <input type="password" name="teamchef_passwort">
                </label>
                <button type="submit">Team speichern</button>
            </form>
        </div>

        <div class="form-box">
            <h2>Teamchef anmelden</h2>
            <form action="#" method="post">
                <label>
                    Loginname
                    <input type="text" name="login_name">
                </label>
                <label>
                    Kennwort
                    <input type="password" name="password">
                </label>
                <button type="submit">Anmelden</button>
            </form>
        </div>

        <div class="form-box">
            <h2>Rennveranstalter registrieren</h2>
            <form action="#" method="post">
                <label>
                    Eindeutiger Name
                    <input type="text" name="veranstalter_name">
                </label>
                <label>
                    Kennwort
                    <input type="password" name="veranstalter_passwort">
                </label>
                <button type="submit">Registrieren</button>
            </form>
        </div>

        <div class="form-box">
            <h2>Rennveranstalter anmelden</h2>
            <form action="#" method="post">
                <label>
                    Eindeutiger Name
                    <input type="text" name="veranstalter_login">
                </label>
                <label>
                    Kennwort
                    <input type="password" name="veranstalter_passwort_login">
                </label>
                <button type="submit" class="pink-button">Anmelden</button>
            </form>
        </div>
    </div>
</body>
</html>
