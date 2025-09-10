<?php
require_once 'vendor/autoload.php'; // Pastikan pakai Composer Google Client

$client = new Google_Client();
$client->setClientId('1023579101839-o2u4u9n4dhe9bvfi3ik2r2og3s9mmeoj.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('https://asokababystore.com/get_refresh_token.php');
$client->addScope('https://mail.google.com/');
$client->setAccessType('offline'); // Penting untuk dapatkan refresh_token
$client->setPrompt('consent');     // Paksa consent untuk token baru

if (!isset($_GET['code'])) {
    // Step 1: Redirect ke Google
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
} else {
    // Step 2: Google mengembalikan authorization code
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (isset($token['error'])) {
        echo "Error: " . $token['error_description'];
        exit;
    }

    // Tampilkan token
    echo "<h2>✅ Access Token didapatkan</h2>";
    echo "<pre>";
    print_r($token);
    echo "</pre>";

    // Simpan refresh_token untuk digunakan di PHPMailer
    if (isset($token['refresh_token'])) {
        echo "<p><strong>Refresh Token:</strong> " . htmlspecialchars($token['refresh_token']) . "</p>";
    } else {
        echo "<p><strong style='color:red;'>Refresh token tidak diberikan.</strong> Coba ulangi login dan pastikan setPrompt('consent')</p>";
    }
}
?>
