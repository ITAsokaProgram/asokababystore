<?php
require_once __DIR__ . ("/../../../../aa_kon_sett.php");
require_once __DIR__ . ("/../../../auth/middleware_login.php");

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: POST");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode yang diizinkan hanya POST']);
    exit;
}

try {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token tidak ditemukan']);
        exit;
    }
    
    $token = $matches[1];
    $verif = verify_token($token);
    if (!$verif || !isset($verif->id)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token tidak valid']);
        exit;
    }
    $userId = $verif->id;

    $user_stmt = $conn->prepare("SELECT nama_lengkap, no_hp, email FROM user_asoka WHERE id_user = ?");
    $user_stmt->bind_param("i", $userId);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result()->fetch_assoc();
    if (!$user_result) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User tidak ditemukan.']);
        exit;
    }
    $user_stmt->close();

    $data = json_decode(file_get_contents('php://input'), true);
    $subject = trim($data['subject'] ?? '');
    $message = trim($data['message'] ?? '');

    if (empty($subject) || empty($message)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Subjek dan pesan tidak boleh kosong.']);
        exit;
    }

    $sql = "INSERT INTO contact_us (id_user, no_hp, nama_lengkap, email, subject, message, status, dikirim) VALUES (?, ?, ?, ?, ?, ?, 'open', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $userId, $user_result['no_hp'], $user_result['nama_lengkap'], $user_result['email'], $subject, $message);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $stmt->close();
    
    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Pesan Anda telah berhasil dikirim.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan pada server.', 'error_detail' => $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}