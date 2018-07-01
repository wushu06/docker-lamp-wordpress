<?php

$curl_post_data = array(
    'grant_type' => "authorization_code",
    'code' => $_GET["code"],
    'redirect_uri' => "http://localhost/checkfire/redirect.php"
);
$service_url = 'http://localhost/checkfire/oauth/token';
$curl = curl_init($service_url);
curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($curl, CURLOPT_USERPWD, "8JGRHfmeaRFi8NGGmc3qJyv8Soi1QJ:lRwuwJdpXs5tL5CloQ6QewgT5WGr9Y"); //Your credentials goes here
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$curl_response = curl_exec($curl);
$response = json_decode($curl_response,true);
curl_close($curl);
/*echo "<pre>";
var_dump($response);
echo "</pre><hr/>";
echo "<a href='localhost:3000?token=".$response['access_token']."'>Get Info</a>";*/
header("Location: http://localhost:3000?token={$response['access_token']}");
die();