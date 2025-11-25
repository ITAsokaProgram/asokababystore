<?php
include '../../../../aa_kon_sett.php';
include 'method_promo.php';
header("Content-Type:application/json");

$sql = "SELECT Kd_Store,Nm_Alias FROM kode_store ORDER BY Nm_Alias ASC";

try {
    $fetch = readCabang($sql, $conn);
    if ($fetch) {
        http_response_code(200);
        echo json_encode(['message' => 'Berhasil fetch data', 'data_cabang' => $fetch]);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Data tidak ditemukan']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Server error atau mengalami gangguan', 'error' => $e->getMessage()]);
}

$conn->close();