<?php
if(isset($_SESSION['expires'])) {if($_SESSION['expires'] < time()) {session_unset(); session_regenerate_id(true);}} else {session_regenerate_id(true);}
if (!isset($_SESSION['code'])) {
    return false;
} else {
    $authstr = "authorization: " . $_SESSION['code'];
    $headers = array($authstr,"Content-Type: application/json");
    $check = curl_init();
    curl_setopt_array($check, array(
        CURLOPT_URL => "https://v2.steemconnect.com/api/me",
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
    $_result = json_decode($result);
    if(isset($_result->user)) {
        return true;
    } else {
        return false;
    }
}
