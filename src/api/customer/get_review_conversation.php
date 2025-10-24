<?php
require_once __DIR__ . ("/../../../aa_kon_sett.php");
require_once __DIR__ . ("/../../auth/middleware_login.php");

header('Content-Type: application/json');
header("Access-Control-Allow-Methods: GET");


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


$update_stmt = null;
$stmt = null;

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

    $review_id = filter_input(INPUT_GET, 'review_id', FILTER_VALIDATE_INT);
    if (!$review_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Review ID tidak valid']);
        exit;
    }

    $user_type = isset($verif->id) ? 'customer' : 'admin';
    $type_to_mark_as_read = ($user_type === 'customer') ? 'admin' : 'customer';

    $conn->begin_transaction();
    $update_sql = "UPDATE review_conversation 
                       SET sudah_dibaca = 1 
                       WHERE review_id = ? AND pengirim_type = ? AND sudah_dibaca = 0";
    $update_stmt = $conn->prepare($update_sql);
    if (!$update_stmt) throw new Exception("Prepare Update Gagal: " . $conn->error);
    
    $update_stmt->bind_param("is", $review_id, $type_to_mark_as_read);
    if (!$update_stmt->execute()) throw new Exception("Execute Update Gagal: " . $update_stmt->error);
    

    
    $sql = "SELECT 
                rc.review_id, rc.pengirim_type, rc.pengirim_id,
                rc.pesan, rc.dibuat_tgl, rc.sudah_dibaca, rc.tipe_pesan, rc.media_url, 
                CASE 
                    WHEN rc.pengirim_type = 'admin' THEN a.nama
                    WHEN rc.pengirim_type = 'customer' THEN ua.nama_lengkap
                END AS nama_pengirim
            FROM review_conversation rc
            LEFT JOIN user_account a ON rc.pengirim_type = 'admin' AND rc.pengirim_id = a.kode
            LEFT JOIN user_asoka ua ON rc.pengirim_type = 'customer' AND rc.pengirim_id = ua.id_user
            WHERE rc.review_id = ?
            ORDER BY rc.dibuat_tgl ASC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Prepare Gagal: " . $conn->error);

    $stmt->bind_param("i", $review_id);
    if (!$stmt->execute()) throw new Exception("Execute Gagal: " . $stmt->error);

    $result = $stmt->get_result();
    $conversations = [];
    
    while ($row = $result->fetch_assoc()) {
        $conversations[] = [
            'review_id' => (int)$row['review_id'],
            'pengirim_type' => $row['pengirim_type'],
            'pengirim_id' => (int)$row['pengirim_id'],
            'nama_pengirim' => $row['nama_pengirim'],
            'pesan' => $row['pesan'],
            'tipe_pesan' => $row['tipe_pesan'],
            'media_url' => $row['media_url'],
            'dibuat_tgl' => $row['dibuat_tgl'],
            'sudah_dibaca' => (bool)$row['sudah_dibaca'],
            
        ];
    }

    $conn->commit(); 
    echo json_encode(['success' => true, 'data' => $conversations]);

} catch (Exception $e) {
    if (isset($conn) && $conn->in_transaction) {
        $conn->rollback();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan internal pada server.',
        'error_detail' => $e->getMessage()
    ]);
    exit; 
} finally {
    
    if (isset($stmt)) $stmt->close();
    if (isset($update_stmt)) $update_stmt->close();
    if (isset($conn)) $conn->close();
}