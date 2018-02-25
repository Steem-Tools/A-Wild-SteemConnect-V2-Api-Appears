<?php
session_start();
if (!isset($_SESSION['code'])) {
    if(isset($_GET['state'])) {header("Location: " . $_GET['state']);} else {header("Location: http://localhost:8080/SteemApps");}
} else {
    $authstr = "authorization: " . $_SESSION['code'];
    $headers = array($authstr,"Content-Type: application/json");
    $check = curl_init();
    curl_setopt_array($check, array(
        CURLOPT_URL => "https://v2.steemconnect.com/api/oauth2/token/revoke",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 1,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "{}",
        CURLOPT_HTTPHEADER => array(
            $authstr,
            "cache-control: no-cache",
            "content-type: application/json",
        ),
    ));
    $result = curl_exec($check);
    curl_close($check);
    session_unset();
    session_destroy();

    if(isset($_GET['state'])) {header("Location: " . $_GET['state']);} else {header("Location: http://localhost:8080/SteemApps");}
}
