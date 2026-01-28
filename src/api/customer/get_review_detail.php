<?php
require_once __DIR__ . ("/../../../aa_kon_sett.php");
require_once __DIR__ . ("/../../auth/middleware_login.php");


ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

set_exception_handler(function($exception) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan internal pada server.',
        'error_detail' => $exception->getMessage()
    ]);
    exit;
});


if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); 
    echo json_encode(['success' => false, 'message' => 'Metode yang diizinkan hanya GET']);
    exit;
}

try {
    
    $verif = authenticate_request();
    
    $review_id = filter_input(INPUT_GET, 'review_id', FILTER_VALIDATE_INT);
    if (!$review_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Review ID tidak valid atau tidak disediakan']);
        exit;
    }

    
    $sql = "SELECT rd.*, r.komentar, r.rating, ua.nama_lengkap, ua.no_hp
            FROM review_detail rd
            JOIN review r ON r.id = rd.review_id
            JOIN user_asoka ua ON ua.id_user = r.id_user
            WHERE rd.review_id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Prepare Gagal: " . $conn->error);

    $stmt->bind_param("i", $review_id); 
    if (!$stmt->execute()) throw new Exception("Execute Gagal: " . $stmt->error);

    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => true, 'data' => $result->fetch_assoc()]);
    } else {
        http_response_code(404);
        
        $checkReviewSql = "SELECT id FROM review WHERE id = ?";
        $checkReviewStmt = $conn->prepare($checkReviewSql);
        $checkReviewStmt->bind_param("i", $review_id);
        $checkReviewStmt->execute();
        if ($checkReviewStmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Review ditemukan, namun data penanganan belum ada.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Review dengan ID tersebut tidak ditemukan.']);
        }
        $checkReviewStmt->close();
    }

} catch (Exception $e) {
    throw $e; 
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}