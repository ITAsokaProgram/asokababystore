<?php
include '../../../../aa_kon_sett.php';
include 'method_promo.php';
header("Content-Type:application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
try {
    $data = json_decode(file_get_contents("php://input"), true);
    $kode_promo = $data['kode_promo'];
    $nama_supplier = $data['nama_supplier'];
    $kd_store = $data['kd_store'];
    $tgl_awal = $data['start'];
    $tgl_akhir = $data['end'];
    $keterangan = $data['ket'];
    $status = $data['status'];
    $statusD = $data['status_digunakan'];
    $barangList = $data['barang'];
    $sql = "UPDATE master_promo
            SET plu = ?, descp = ?, kode_promo = ?, harga_jual = ?, tgl_mulai = ?, tgl_selesai = ?, keterangan = ?, diskon = ?, potongan_harga = ?, status = ?, 
            status_digunakan = ?, nama_supplier = ?, kd_store = ? WHERE id_promo = ?;";
    foreach ($barangList as $item) {
        $plu = $item['barcode'];
        $descp = $item['namaBarang'];
        $hargaJual = is_numeric($item['hargaJual']) ? $item['hargaJual'] : 0;
        $diskon = is_numeric($item['diskon']) ? $item['diskon'] : 0;
        $potongan = is_numeric($item['potongan']) ? $item['potongan'] : 0;
        $namaSupplier = $item['kode_supp'];
        $idPromo = $item['idPromo'];
        $params = [$plu, $descp, $kode_promo, $hargaJual, $tgl_awal, $tgl_akhir, $keterangan, $diskon, $potongan, $status, $statusD, $namaSupplier, $kd_store, $idPromo];
        $fetch = updatePromoDetail($sql, $conn, $params);
    }
    if ($fetch) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Berhasil mengubah data']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Gagal fetch data, no response']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error please reconnect']);
}