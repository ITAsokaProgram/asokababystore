<?php
include '../config/JWT/JWT.php';
include '../config/JWT/Key.php';
include '../config/JWT/config.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
header("Content-Type: application/json");
$headers = getallheaders(); 
$allowedUser = ['manager', 'IT' , 'admin']; 
if (isset($headers['Authorization'])) {
    // Ambil token dari header Authorization
    $jwt = $headers['Authorization'];
    list($type, $jwt) = explode(' ', $jwt); // Memisahkan Bearer dan tokennya
    if (strcasecmp($type, 'Bearer') !== 0) {
        echo json_encode(['status' => 'error', 'message' => 'Format Authorization tidak sesuai.']);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Token tidak ditemukan.']);
    exit;
}


$secretKey = JWT_SECRET_KEY; // Gunakan kunci yang sama seperti waktu membuat token

try {
    // Decode token
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

    // Ambil data dari token
    $kode_user = $decoded->kode;
    $nama_user = $decoded->nama;  
    $username_user = $decoded->username;
    $role_user = $decoded->role; 


    echo json_encode([
        'status' => 'success',
        'message' => 'Token valid!',
        'data' => [
            'kode' => $kode_user,
            'nama' => $nama_user,
            'username' => $username_user,
            'role' => $role_user
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Token tidak valid!',
        'error' => $e->getMessage()
    ]);
}