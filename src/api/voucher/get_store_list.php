<?php
// src/api/voucher/get_store_list.php
session_start();
ini_set('display_errors', 0);

// Sesuaikan path ke aa_kon_sett.php
require_once __DIR__ . '/../../../../aa_kon_sett.php';

header('Content-Type: application/json');

try {
    // Query mengambil data store
    // Pastikan nama kolom sesuai dengan database Anda (Kd_Store, Nm_Store, Nm_Alias)
    // Tambahkan WHERE display = 'on' jika Anda ingin memfilter toko yang aktif saja
    $sql = "SELECT Kd_Store, Nm_Store, Nm_Alias FROM kode_store WHERE display = 'on' ORDER BY Nm_Alias ASC";
    $result = $conn->query($sql);

    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Konversi encoding jika perlu, untuk menghindari karakter aneh
            $data[] = [
                'Kd_Store' => $row['Kd_Store'],
                'Nm_Store' => mb_convert_encoding($row['Nm_Store'], 'UTF-8', 'UTF-8'),
                'Nm_Alias' => $row['Nm_Alias']
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>