<?php
require_once __DIR__ . "/../../../config.php"; 
header("Content-Type: application/json");
$keyword = $_GET['q'] ?? '';
if (strlen($keyword) < 1) {
    echo json_encode([]);
    exit;
}
$sql = "SELECT DISTINCT kode_supp, nama_supp 
        FROM supplier 
        WHERE (kode_supp LIKE ? OR nama_supp LIKE ?) 
        AND aktif = 'True'
        LIMIT 15";
$stmt = $conn->prepare($sql);
$param = "%$keyword%";
$stmt->bind_param("ss", $param, $param);
$stmt->execute();
$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => $row['kode_supp'],
        'text' => $row['kode_supp'] . " - " . $row['nama_supp']
    ];
}
echo json_encode($data);
?>