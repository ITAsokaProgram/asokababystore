<?php
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
header('Content-Type: application/json');

$headers = getallheaders();
$auth_header = $headers['Authorization'] ?? '';
$token = $auth_header ? str_replace('Bearer ', '', $auth_header) : null;
if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit;
}
$verif = verify_token($token);
if (!$verif) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Invalid token']);
    exit;
}

try {
    $id = $_GET['idhistory_aset'] ?? null;
    if (!$id) throw new Exception('Missing idhistory_aset');

    $sql = "SELECT ua.nama, la.created_at AS tanggal, la.log_activity AS kegiatan
            FROM log_history_aset la
            JOIN history_aset ha ON ha.idhistory_aset = la.idhistory_aset
            JOIN user_account ua ON ua.kode = la.id_user
            WHERE la.idhistory_aset = ?
            ORDER BY tanggal DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception('DB prepare failed: ' . $conn->error);
    $idInt = (int)$id;
    $stmt->bind_param('i', $idInt);
    $stmt->execute();
    $res = $stmt->get_result();
    $items = [];
    while ($r = $res->fetch_assoc()) {
        $items[] = $r;
    }
    $stmt->close();

    echo json_encode(['status' => true, 'data' => ['items' => $items]]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
}
