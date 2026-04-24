<?php

function holeTeamnameZumLogin($connection, $teamchef_login)
{
    $login_sicher = mysqli_real_escape_string($connection, $teamchef_login);
    $abfrage = mysqli_query($connection, "SELECT Teamname FROM TEAM WHERE Loginname = '$login_sicher' LIMIT 1");

    if ($abfrage && mysqli_num_rows($abfrage) > 0) {
        return mysqli_fetch_assoc($abfrage)['Teamname'];
    }

    return '';
}
