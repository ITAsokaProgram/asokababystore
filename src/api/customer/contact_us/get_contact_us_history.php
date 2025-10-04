<?php
require_once __DIR__ . ("/../../../../aa_kon_sett.php");
require_once __DIR__ . ("/../../../auth/middleware_login.php");
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1];
}
if (!$token) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Token tidak ditemukan atau format salah']);
    exit;
}
try {
    $verif = verify_token($token);
    if (!$verif || !isset($verif->id)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token tidak valid atau user tidak dikenali']);
        exit;
    }
    $userId = $verif->id;
    $sql = "SELECT 
                cu.id,
                cu.subject,
                cu.status,
                cu.dikirim,
                (SELECT COUNT(*) 
                 FROM contact_us_conversation cuc 
                 WHERE cuc.contact_us_id = cu.id 
                   AND cuc.sudah_dibaca = 0 
                   AND cuc.pengirim_type = 'admin'
                ) as unread_count
            FROM contact_us cu
            WHERE cu.id_user = ?
            ORDER BY cu.dikirim DESC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("SQL prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $history = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    http_response_code(200);
    echo json_encode(['status' => 'success', 'data' => $history]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan internal pada server.', 'error_detail' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}