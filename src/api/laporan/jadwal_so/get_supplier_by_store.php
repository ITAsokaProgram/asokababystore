<?php
require_once __DIR__ . '/../../../../aa_kon_sett.php';
require_once __DIR__ . '/../../../auth/middleware_login.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}
try {
    $input = json_decode(file_get_contents('php://input'), true);
    $store_ids = $input['store_ids'] ?? [];
    if (empty($store_ids)) {
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }
    $ids = array_map(function($id) use ($conn) {
        return "'" . $conn->real_escape_string($id) . "'";
    }, $store_ids);
    $ids_string = implode(',', $ids);
    $sql = "SELECT DISTINCT kode_supp, nama_supp 
            FROM supplier 
            WHERE kd_store IN ($ids_string) 
            AND kode_supp IS NOT NULL 
            AND nama_supp IS NOT NULL
            ORDER BY nama_supp ASC";
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>