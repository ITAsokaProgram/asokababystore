<?php
require_once __DIR__ . '/middleware_login.php';

header("Content-Type: application/json");

$decoded = authenticate_request();

echo json_encode([
    'status' => 'success',
    'message' => 'Token valid!',
    'data' => [
        'kode' => $decoded->kode ?? null,
        'nama' => $decoded->nama ?? null,
        'username' => $decoded->username ?? null,
        'role' => $decoded->role ?? null
    ]
]);
?>