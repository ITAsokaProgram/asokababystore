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
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => false, 'message' => 'Method not allowed']);
        exit;
    }

    $id = $_POST['idhistory_aset'] ?? null;
    if (!$id) throw new Exception('Missing idhistory_aset');
    
    // log deletion
    $userId = $verif->kode ?? ($verif->kode ?? null);
    $logStmt = $conn->prepare('INSERT INTO log_history_aset (idhistory_aset, id_user, log_activity, created_at) VALUES (?, ?, ?, NOW())');
    $act = json_encode([['action'=>'delete']], JSON_UNESCAPED_UNICODE);
    $logStmt->bind_param('iis', $id, $userId, $act);
    $logStmt->execute();
    $logStmt->close();
    
    $stmt = $conn->prepare('DELETE FROM history_aset WHERE idhistory_aset = ?');
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) throw new Exception('Failed to delete: '.$stmt->error);
    $stmt->close();


    echo json_encode(['status'=>true, 'message'=>'Deleted']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status'=>false, 'message'=>$e->getMessage()]);
}
