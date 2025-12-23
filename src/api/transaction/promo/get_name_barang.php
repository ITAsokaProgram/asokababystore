<?php
include '../../../../aa_kon_sett.php';
include 'method_promo.php';
header("Content-Type:application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sql = "SELECT DISTINCT m.descp,m.plu, p.price FROM `master` m 
LEFT JOIN  price p ON p.plu = m.plu WHERE m.VENDOR = ?";
$kode_supp = $_GET['supplier'] ?? null;
try {
    $fetch = readMasterBarang($sql, $conn, $kode_supp);
    if ($fetch) {
        http_response_code(200);
        echo json_encode(['message' => 'Berhasil fetch data', 'data_barang' => $fetch], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode(['message' => "Data tidak ditemukan"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => "Server Error Atau Mengalami Kendala", "error" => $e->getMessage()]);
}


$conn->close();
