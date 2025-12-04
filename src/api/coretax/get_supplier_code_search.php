<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
try {
    if (!$conn) {
        throw new Exception("Koneksi Database Gagal");
    }
    $term = isset($_GET['term']) ? trim($_GET['term']) : '';
    if (empty($term)) {
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }
    $query = "SELECT kode_supp, nama_supp 
              FROM supplier 
              WHERE kode_supp LIKE ? 
              GROUP BY kode_supp 
              ORDER BY kode_supp ASC 
              LIMIT 10";
    $stmt = $conn->prepare($query);
    $searchTerm = "%" . $term . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'code' => $row['kode_supp'],
            'name' => $row['nama_supp'] ?? ''
        ];
    }
    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>