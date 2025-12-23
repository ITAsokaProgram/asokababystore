<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');
$term = $_GET['term'] ?? '';
if (strlen($term) < 1) {
    echo json_encode([]);
    exit;
}
try {
    $term = "%" . $conn->real_escape_string($term) . "%";
    $sql = "SELECT DISTINCT kode_supp, nama_supp 
            FROM supplier 
            WHERE kode_supp LIKE ? OR nama_supp LIKE ? 
            LIMIT 20";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("SQL Prepare Failed: " . $conn->error);
    }
    $stmt->bind_param("ss", $term, $term);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => $row['kode_supp'],
            'text' => $row['kode_supp'] . " - " . $row['nama_supp'],
            'nama' => $row['nama_supp']
        ];
    }
    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>