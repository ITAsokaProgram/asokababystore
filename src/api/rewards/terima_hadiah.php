<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";

header("Content-Type:application/json");
date_default_timezone_set('Asia/Jakarta');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Gunakan metode POST.']);
    exit;
}
$decoded = authenticate_request();


$plu = $_POST['plu'] ?? '';
$kd_store = $_POST['kd_store'] ?? '';
$nama_hadiah = $_POST['nama_hadiah'] ?? ''; 
$qty_rec = isset($_POST['qty_rec']) ? (int)$_POST['qty_rec'] : 0;
$kode_karyawan = $decoded->kode ?? $_POST['kode_karyawan'] ?? 'N/A';
$nama_karyawan = $decoded->nama ?? $_POST['nama_karyawan'] ?? 'System';

if (empty($plu) || empty($kd_store) || empty($nama_hadiah) || $qty_rec <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Input tidak valid. PLU, Nama Hadiah, Kode Store, dan Jumlah harus diisi.']);
    exit;
}


$conn->begin_transaction();

try {
    // ! rec ini indikasi receive ya
    $no_hdh = 'REC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

    
    $stmt_insert = $conn->prepare(
        "INSERT INTO hadiah_r (no_hdh, plu, nama_hadiah, qty_rec, old_poin, new_poin, kd_store, kode_karyawan, nama_karyawan, tanggal, jam, ket) 
         VALUES (?, ?, ?, ?, 0, 0, ?, ?, ?, CURDATE(), CURTIME(), 'RECEIVE')"
    );
    if (!$stmt_insert) throw new Exception("Prepare statement gagal (insert): " . $conn->error);
    $stmt_insert->bind_param("sssisss", $no_hdh, $plu, $nama_hadiah, $qty_rec, $kd_store, $kode_karyawan, $nama_karyawan);
    $stmt_insert->execute();
    if ($stmt_insert->affected_rows === 0) {
        throw new Exception("Gagal mencatat histori penerimaan.");
    }
    $stmt_insert->close();
    
    
    $stmt_update = $conn->prepare("UPDATE hadiah SET qty = qty + ? WHERE plu = ? AND nama_hadiah = ? AND FIND_IN_SET(?, kd_store)");
    if (!$stmt_update) throw new Exception("Prepare statement gagal (update): " . $conn->error);
    $stmt_update->bind_param("isss", $qty_rec, $plu, $nama_hadiah, $kd_store);
    $stmt_update->execute();
    if ($stmt_update->affected_rows === 0) {
        throw new Exception("Gagal memperbarui stok. Pastikan hadiah '$nama_hadiah' dengan PLU $plu ada di cabang $kd_store.");
    }
    $stmt_update->close();
    
    
    $conn->commit();
    http_response_code(201);
    echo json_encode(['success' => true, 'message' => "Stok hadiah ($nama_hadiah) berhasil ditambahkan sebanyak $qty_rec."]);

} catch (Exception $e) {
    
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}