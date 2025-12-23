<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../aa_kon_sett.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$date = date('Y-m-d H:i:s');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);
    $token = $input['token'] ?? die("Token tidak ditemukan");
    $newPassword = $input['newPassword'] ?? die("Password baru tidak ditemukan");

    // Validasi token
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
    }

    // Update password
    $email = $resetRequest['email'];
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateStmt = $conn->prepare("UPDATE user_asoka SET password = ? WHERE email = ?");
    $updateStmt->bind_param("ss", $hashedPassword, $email);
    $updateStmt->execute();

    // Tandai token sebagai digunakan
    $updateTokenStmt = $conn->prepare("UPDATE reset_token SET used = 1 WHERE token = ?");
    $updateTokenStmt->bind_param("s", $token);
    $updateTokenStmt->execute();

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Password berhasil diperbarui.'
    ]);
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Metode HTTP tidak diizinkan.'
    ]);
}

$conn->close();