<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>RennradSV</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            color: #222;
        }

        .page {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
        }

        h1,
        h2 {
            margin-top: 0;
        }

        .form-box {
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 16px;
            margin-bottom: 20px;
        }

        .form-box label {
            display: block;
            margin-bottom: 12px;
        }

        .form-box input,
        .form-box select,
        .form-box textarea {
            width: 100%;
            margin-top: 6px;
            padding: 8px;
            box-sizing: border-box;
        }

        .form-box button {
            padding: 10px 14px;
            border: 1px solid #999;
            background-color: #e6e6e6;
            cursor: pointer;
        }

        .pink-button {
            background-color: hotpink !important;
            color: #fff;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="page">
        <h1>RennradSV</h1>
        <p>Einfache Startseite mit einem einheitlichen Layout fuer alle Anmeldemasken.</p>

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
