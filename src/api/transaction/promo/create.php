<?php
include '../../../../aa_kon_sett.php';
header("Content-Type:application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$data = json_decode(file_get_contents("php://input"), true);

// Ambil data form utama
$kode_promo = $data['kode_promo'];
$nama_supplier = $data['nama_supplier'];
$kd_store = $data['kd_store'];
$tgl_awal = $data['start'];
$tgl_akhir = $data['end'];
$keterangan = $data['ket'];
$status = $data['status'];
$statusD = $data['status_digunakan'];
$barangList = $data['barang'];

// Validasi data dasar
if (!$kode_promo || !$nama_supplier || !$kd_store || !$tgl_awal || !$tgl_akhir || empty($barangList)) {
    http_response_code(400);
    echo json_encode(['message' => 'Data tidak lengkap!']);
    exit;
}

try {
    $conn->begin_transaction();

    $sql = "INSERT INTO master_promo
    (kode_promo, plu, descp, kode_supp, nama_supplier, harga_jual, diskon, potongan_harga, kd_store, tgl_mulai, tgl_selesai, keterangan, status, status_digunakan) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Prepare failed: " . $conn->error
        ]);
        exit;  // hentikan eksekusi setelah kirim error
    }

    foreach ($barangList as $barang) {
        $descp = isset($barang['namaBarang']) ? $barang['namaBarang'] : '';
        $harga_jual = is_numeric($barang['hargaJual']) ? floatval($barang['hargaJual']) : 0;
        $diskon = is_numeric($barang['diskon']) ? floatval($barang['diskon']) : 0;
        $potongan = is_numeric($barang['potongan']) ? floatval($barang['potongan']) : 0;
        $barcode = isset($barang['barcode']) ? $barang['barcode'] : 0;
        $kd_supp = isset($barang['kode_supp']) ? $barang['kode_supp'] : 0;

        $stmt->bind_param(
            "sssssdddssssss",
            $kode_promo,
            $barcode,
            $descp,
            $kd_supp,
            $nama_supplier,
            $harga_jual,
            $diskon,
            $potongan,
            $kd_store,
            $tgl_awal,
            $tgl_akhir,
            $keterangan,
            $status,
            $statusD
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
    }

    $conn->commit();

    http_response_code(201);
    echo json_encode(['success' => true, 'menssage' => 'Berhasil menambahkan promo']);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error saat menyimpan data',
        'error' => $e->getMessage()
    ]);
}
$conn->close();
