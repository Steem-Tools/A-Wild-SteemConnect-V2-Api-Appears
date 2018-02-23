<?php
session_start();

function redirect() {
    if (isset($_GET['state'])) {
        header("Location: " . $_GET['state']);
        die();
    } else {
        header("Location: http://localhost:8080/SteemApps");
        die();
    }
}

//vote%2Ccomment%2Ccomment_delete%2Ccomment_options%2Ccustom_json%2Cclaim_reward_balance%2Coffline
//https://v2.steemconnect.com/oauth2/authorize?client_id=cadawg.app&redirect_uri=http://localhost:8080/SteemApps/callback.php&scope=vote%2Ccomment%2Ccomment_delete%2Ccomment_options%2Ccustom_json%2Cclaim_reward_balance%2Coffline

if (isset($_GET['access_token']) and isset($_GET['expires_in'])) {
    $_SESSION['code'] = $_GET['access_token'];
    if ((integer) $_GET['expires_in'] == 604800) {
        $_SESSION['expires'] = time() + 604800;
    } else {
        session_unset();
        session_regenerate_id(true);
        redirect();
    }
    $usr_name = require 'getLogin.php';
    if ($usr_name != false) {
        $_SESSION['user'] = $usr_name;
        redirect();
    } else {
        session_unset();
        session_regenerate_id(true);
        redirect();
    }
}
