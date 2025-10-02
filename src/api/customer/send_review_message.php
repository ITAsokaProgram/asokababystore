<?php
require_once __DIR__ . ("/../../../aa_kon_sett.php");
require_once __DIR__ . ("/../../auth/middleware_login.php");

header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST");

set_exception_handler(function($exception) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan internal pada server.',
        'error_detail' => $exception->getMessage()
    ]);
    exit;
});

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
    
    if (!$verif) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token tidak valid']);
        exit;
    }

    $admin_id = $verif->id ?? $verif->kode ?? null;

    $data = json_decode(file_get_contents('php://input'), true);
    $review_id = $data['review_id'] ?? null;
    $pesan = trim($data['pesan'] ?? '');

    if (!$review_id || empty($pesan)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Review ID dan pesan wajib diisi']);
        exit;
    }

    $sql = "INSERT INTO review_conversation (review_id, pengirim_type, pengirim_id, pesan) 
            VALUES (?, 'admin', ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Prepare Gagal: " . $conn->error);

    $stmt->bind_param("iss", $review_id, $admin_id, $pesan);
    
    if (!$stmt->execute()) throw new Exception("Execute Gagal: " . $stmt->error);

    http_response_code(201);
    echo json_encode([
        'success' => true, 
        'message' => 'Pesan berhasil dikirim',
        'data' => [
            'review_id' => $review_id,
            'pesan' => $pesan
        ]
    ]);

} catch (Exception $e) {
    throw $e;
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}