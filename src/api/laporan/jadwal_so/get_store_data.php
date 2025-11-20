<?php
require_once __DIR__ . '/../../../../aa_kon_sett.php';
require_once __DIR__ . '/../../../utils/Logger.php';
require_once __DIR__ . '/../../../auth/middleware_login.php';
header('Content-Type: application/json');
try {
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Koneksi database gagal");
    }
    $sql = "SELECT Kd_Store, Nm_Store, Nm_Alias FROM kode_store WHERE display = 'on' ORDER BY Kd_Store ASC";
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Query Error: " . $conn->error . " (SQL: $sql)");
    }
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>