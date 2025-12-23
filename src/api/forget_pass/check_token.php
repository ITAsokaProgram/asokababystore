<?php

include '../../../aa_kon_sett.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$token = $_GET['token'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!$token) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Token tidak ditemukan.'
        ]);
        exit;
    }
    // Validasi token
    $date = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("SELECT * FROM reset_token WHERE token = ? AND used = 0 AND kadaluarsa > ?");
    $stmt->bind_param("ss", $token, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $resetRequest = $result->fetch_assoc();

    if (!$resetRequest) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Sesi tidak valid atau sudah kadaluarsa.'
        ]);
        exit;
    } else {
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'Token valid.',
            'email' => $resetRequest['email'],
            'kadaluarsa' => $resetRequest['kadaluarsa']
        ]);
    }
    $stmt->close();
    $conn->close();
}
