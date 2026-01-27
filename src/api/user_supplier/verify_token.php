<?php
require_once __DIR__ . '/../../auth/middleware_login.php'; 
require_once __DIR__ . '/../../../aa_kon_sett.php';
header("Content-Type: application/json");
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $token = $matches[1];
    $decoded = verify_token($token);
    if (is_array($decoded) && isset($decoded['status']) && $decoded['status'] === 'error') {
        http_response_code(401);
        echo json_encode($decoded); 
    } else {
        echo json_encode([
            'status' => 'success',
            'message' => 'Token Valid',
            'data' => [
                'nama' => $decoded->nama,
                'email' => $decoded->email,
                'kode' => $decoded->kode
            ]
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Token tidak ditemukan']);
}
?>