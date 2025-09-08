<?php
$verify_token = "asoka123hooktoken"; // Sama dengan yang kamu isi di Meta

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Verifikasi webhook dari Facebook
    $hub_verify_token = $_GET['hub_verify_token'];
    $hub_challenge = $_GET['hub_challenge'];
    $hub_mode = $_GET['hub_mode'];

    if ($hub_mode === 'subscribe' && $hub_verify_token === $verify_token) {
        echo $hub_challenge;
    } else {
        http_response_code(403);
        echo "Token salah.";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Menerima notifikasi dari WhatsApp
    $data = file_get_contents('php://input');
    file_put_contents('webhook_log.txt', $data . PHP_EOL, FILE_APPEND);
    http_response_code(200);
}
