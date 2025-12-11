<?php
session_start();
include '../../../aa_kon_sett.php'; // Sesuaikan path koneksi database
header('Content-Type: application/json');

$response = ['data' => [], 'error' => null];

try {
    $search = $_GET['search_keyword'] ?? '';

    $sql = "SELECT * FROM wa_balasan_otomatis";
    
    if (!empty($search)) {
        $sql .= " WHERE kata_kunci LIKE ?";
    }
    
    $sql .= " ORDER BY dibuat_pada DESC";

    $stmt = $conn->prepare($sql);

    if (!empty($search)) {
        $paramSearch = "%" . $search . "%";
        $stmt->bind_param("s", $paramSearch);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response['data'][] = $row;
    }

} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>