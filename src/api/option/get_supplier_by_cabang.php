<?php
require_once __DIR__ . "/../../../config.php";
header("Content-Type: application/json");
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['kd_store']) || empty($input['kd_store'])) {
    echo json_encode(['success' => false, 'data' => []]);
    exit;
}
$stores = $input['kd_store'];
$placeholders = implode(',', array_fill(0, count($stores), '?'));
$types = str_repeat('s', count($stores));
$sql = "SELECT kode_supp, nama_supp 
        FROM supplier 
        WHERE kd_store IN ($placeholders) 
        GROUP BY kode_supp 
        ORDER BY nama_supp ASC";
try {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$stores);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'kode' => $row['kode_supp'],
            'nama' => $row['nama_supp'] . ' (' . $row['kode_supp'] . ')'
        ];
    }
    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>