<?php
// Pastikan header content-type benar
header('Content-Type: text/plain');

// Token yang kamu dapat dari dashboard (bagian “Webhook URL”)
$expected_token = 'GF4XGTi15bdsz066MhShzF3KYJG9VMC1ZTOILmNZzbjZQMrq';

// Ambil token dari header request
$incoming_token = $_SERVER['HTTP_X_CALLBACK_TOKEN'] ?? '';

if ($incoming_token !== $expected_token) {
    http_response_code(401);
    echo 'invalid token';
    exit;
}

// Ambil payload JSON dari Xendit
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Ambil data penting
$status = $data['status'] ?? '';
$ref_id = $data['reference_id'] ?? '';
$qr_id  = $data['qr_id'] ?? '';
$amount = $data['amount'] ?? 0;

// Simpan log ke file untuk debugging
file_put_contents(__DIR__ . '/xendit-webhook.log', date('Y-m-d H:i:s') . " | REF=$ref_id | STATUS=$status | QR=$qr_id | AMOUNT=$amount\n", FILE_APPEND);


// Update database kalau status SUCCEEDED
if ($status === 'SUCCEEDED') {
    // Contoh update ke MySQL
    // $conn = new mysqli("localhost", "user", "pass", "db");
    // $conn->query("UPDATE orders SET status='PAID' WHERE reference_id='$ref_id'");
}

http_response_code(200);
echo 'ok';
