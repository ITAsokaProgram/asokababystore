<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';

try {
    $term = $_GET['term'] ?? '';

    $query = "SELECT DISTINCT nama_penjual 
              FROM ff_coretax 
              WHERE nama_penjual LIKE ? 
              ORDER BY nama_penjual ASC LIMIT 10";

    $stmt = $conn->prepare($query);
    $searchTerm = "%" . $term . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row['nama_penjual'];
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'data' => []]);
}
?>