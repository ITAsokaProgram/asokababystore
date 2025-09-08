<?php

include "../../../../aa_kon_sett.php";
include "method_promo.php";
header("Content-Type:application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$kodePromo = $_GET['kode_promo'];
$kodeSupp = $_GET['kode_supplier'];

$sql = "SELECT DISTINCT id_promo,plu,descp,kode_promo,harga_jual,tgl_mulai,tgl_selesai,keterangan,diskon,potongan_harga,status,status_digunakan,nama_supplier,kd_store FROM master_promo WHERE kode_supp = '$kodeSupp' AND kode_promo = '$kodePromo'";
$result = fetchPromo($sql, $conn);
if ($result) {
    http_response_code(200);
    echo json_encode(['message' => 'Berhasil fetch data', 'data_promo' => $result]);
} else {
    http_response_code(404);
    echo json_encode(['message' => 'Data tidak ditemukan']);
}
$conn->close();