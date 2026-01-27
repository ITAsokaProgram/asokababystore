<?php
include '../config/JWT/JWT.php';
include '../config/JWT/Key.php';
include '../config/JWT/config.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
$jwt = null;
$headers = getallheaders();
if (isset($headers['Authorization'])) {
    $jwt = $headers['Authorization'];
    list($type, $jwt) = explode(' ', $jwt);
    if (strcasecmp($type, 'Bearer') !== 0) {
        echo json_encode(['status' => 'error', 'message' => 'Format Authorization salah.']);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Token hilang.']);
    exit;
}
try {
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET_KEY, 'HS256'));
    if (!isset($decoded->role) || $decoded->role !== 'supplier') {
        throw new Exception("Token tidak valid untuk akses supplier.");
    }
    $kode_user = $decoded->kode;
    include "../../aa_kon_sett.php";
    $stmt = $conn->prepare("SELECT expired_token FROM user_supplier WHERE kode = ?");
    $stmt->bind_param("i", $kode_user);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($expired_token);
        $stmt->fetch();
        $current_time = date('Y-m-d H:i:s');
        if ($expired_token < $current_time) {
            echo json_encode(['status' => 'error', 'message' => 'Token kadaluarsa.']);
        } else {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Token valid',
                'data' => $decoded
            ]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan.']);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>