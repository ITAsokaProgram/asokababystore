<?php
require 'middleware_login.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$token = $_GET['token'] ?? die("Token tidak ditemukan.");
// Verifikasi token
$result = verify_token($token);
// Jika array, ubah ke object
if (is_array($result)) {
    $result = (object) $result;
}

if (isset($result->status) && $result->status === 'error') {
    echo json_encode([
        'status' => $result->status,
        'message' => $result->message
    ]);
} else {
    echo json_encode([
        'status' => 'success',
        'data' => $result
    ]);
}
exit;
