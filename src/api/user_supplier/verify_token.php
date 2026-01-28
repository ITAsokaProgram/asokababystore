<?php
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../../aa_kon_sett.php';

header("Content-Type: application/json");

$decoded = authenticate_request();

echo json_encode([
    'status' => 'success',
    'message' => 'Token Valid',
    'data' => [
        'nama' => $decoded->nama ?? null,
        'email' => $decoded->email ?? null,
        'kode' => $decoded->kode ?? null
    ]
]);
?>