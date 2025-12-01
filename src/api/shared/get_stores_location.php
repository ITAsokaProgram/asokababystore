<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';

try {
    // Query disesuaikan dengan struktur tabel yang Anda berikan
    // Kita filter agar hanya mengambil data yang latitude-nya ada isinya
    $query = "SELECT Kd_Store, Nm_Store, latitude, longitude, map_link, kota, alm_toko, telp 
              FROM kode_store 
              WHERE latitude IS NOT NULL AND latitude != ''
              ORDER BY kota ASC, Nm_Store ASC";

    $result = $conn->query($query);

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>