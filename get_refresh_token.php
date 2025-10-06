<?php
require_once 'vendor/autoload.php'; 

$env = parse_ini_file(__DIR__ . '/.env'); 
if ($env === false) {
    die("Error: Tidak dapat memuat file .env. Pastikan file tersebut ada di root direktori dan dapat dibaca.");
}

$client = new Google_Client();

$client->setClientId($env['GOOGLE_CLIENT_ID']);
$client->setClientSecret($env['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($env['GET_REFRESH_TOKEN_REDIRECT_URI']); 

$client->addScope('https://mail.google.com/');
$client->setAccessType('offline'); 
$client->setPrompt('consent');    

if (!isset($_GET['code'])) {
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
} else {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (isset($token['error'])) {
        echo "Error: " . $token['error_description'];
        exit;
    }

    echo "<h2>✅ Access Token didapatkan</h2>";
    echo "<pre>";
    print_r($token);
    echo "</pre>";

    if (isset($token['refresh_token'])) {
        echo "<p><strong>Refresh Token:</strong> " . htmlspecialchars($token['refresh_token']) . "</p>";
    } else {
        echo "<p><strong style='color:red;'>Refresh token tidak diberikan.</strong> Coba ulangi login dan pastikan setPrompt('consent')</p>";
    }
}
?>