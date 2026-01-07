<?php
require_once __DIR__ . '/../../../aa_kon_sett.php';
header('Content-Type: application/json');

$result = $conn->query("SELECT tipe, label FROM otorisasi_tipe ORDER BY label ASC");
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(['success' => true, 'data' => $data]);