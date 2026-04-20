<!DOCTYPE html>
<html>
<head>
    <title>Surface</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #222;
        }

        .page {
            max-width: 420px;
            margin: 60px 0 60px auto;
            padding: 32px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        }

        h1 {
            margin-top: 0;
        }

        .login-box label {
            display: block;
            margin-bottom: 14px;
        }
        .login-box input {
            width: 100%;
            margin-top: 6px;
            padding: 10px;
            box-sizing: border-box;
        }

        .login-box button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background-color: #1f6feb;
            color: #fff;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="page">
        <h1>RennradSV</h1>
        <p>Hier findest du alle Informationen zu unserem Rennradverein.</p>
        <div class="login-box">
            <label>
                Anmeldename:
                <input type="text" name="username">
            </label>
            <label>
                Passwort:
                <input type="password" name="password">
            </label>
            <button>Login</button>
        </div>
    </div>
</body>
</html>
