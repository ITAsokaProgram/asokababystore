<?php

include '../../../../aa_kon_sett.php';
include 'method_promo.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header(("Content-Type:application/json"));
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$sql = "SELECT 
    mp.id_promo,
    mp.kode_promo,
    mp.descp,
    mp.nama_supplier,
    mp.kode_supp,
    mp.tgl_mulai,
    mp.tgl_selesai,
    mp.status,
    mp.keterangan,
    mp.status_digunakan,
    GROUP_CONCAT(ms.Nm_Alias SEPARATOR ', ') AS nama_store
FROM 
    master_promo mp
JOIN 
    kode_store ms ON FIND_IN_SET(ms.Kd_Store, mp.kd_store)
GROUP BY 
    mp.id_promo";

try {
    $fetch = fetchPromo($sql, $conn);
    if ($fetch) {
        http_response_code(200);
        echo json_encode(['message' => 'Berhasil fetch data', 'promo' => $fetch]);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Data tidak ditemukan']);

    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Server error atau mengalami gangguan', 'error' => $e->getMessage()]);
}

$conn->close();
?>