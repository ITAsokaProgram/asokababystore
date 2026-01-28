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
$new_poin = isset($_POST['new_poin']) ? (int)$_POST['new_poin'] : -1;
$kode_karyawan = $decoded->kode ?? $_POST['kode_karyawan'] ?? 'N/A';
$nama_karyawan = $decoded->nama ?? $_POST['nama_karyawan'] ?? 'System';

if (empty($plu) || empty($kd_store) || empty($nama_hadiah) || $new_poin < 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Input tidak valid. PLU, Nama Hadiah, Kode Store, dan Poin Baru harus diisi.']);
    exit;
}


$conn->begin_transaction();

try {
    
    $sql_get_data = "SELECT poin FROM hadiah WHERE plu = ? AND nama_hadiah = ? AND FIND_IN_SET(?, kd_store) LIMIT 1";
    $stmt_get_data = $conn->prepare($sql_get_data);
    if (!$stmt_get_data) throw new Exception("Prepare statement gagal (ambil data): " . $conn->error);
    $stmt_get_data->bind_param("sss", $plu, $nama_hadiah, $kd_store);
    $stmt_get_data->execute();
    $result_data = $stmt_get_data->get_result();
    if ($result_data->num_rows === 0) {
        throw new Exception("Hadiah '$nama_hadiah' dengan PLU $plu di cabang $kd_store tidak ditemukan.");
    }
    $hadiah_data = $result_data->fetch_assoc();
    $old_poin = (int)$hadiah_data['poin'];
    $stmt_get_data->close();
    
    if ($old_poin === $new_poin) {
        throw new Exception("Tidak ada perubahan. Poin baru sama dengan poin lama ($old_poin).");
    }

    // ! upd ini indikasi update ya
    
    $no_hdh = 'UPD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
    
    
    $stmt_insert = $conn->prepare(
        "INSERT INTO hadiah_r (no_hdh, plu, nama_hadiah, qty_rec, old_poin, new_poin, kd_store, kode_karyawan, nama_karyawan, tanggal, jam, ket) 
         VALUES (?, ?, ?, 0, ?, ?, ?, ?, ?, CURDATE(), CURTIME(), 'UPDATE_POIN')"
    );
    if (!$stmt_insert) throw new Exception("Prepare statement gagal (insert): " . $conn->error);
    $stmt_insert->bind_param("sssiisss", $no_hdh, $plu, $nama_hadiah, $old_poin, $new_poin, $kd_store, $kode_karyawan, $nama_karyawan);
    $stmt_insert->execute();
    $stmt_insert->close();
    
    
    $sql_update = "UPDATE hadiah SET poin = ? WHERE plu = ? AND nama_hadiah = ? AND FIND_IN_SET(?, kd_store)";
    $stmt_update = $conn->prepare($sql_update);
    if (!$stmt_update) throw new Exception("Prepare statement gagal (update): " . $conn->error);
    $stmt_update->bind_param("isss", $new_poin, $plu, $nama_hadiah, $kd_store);
    $stmt_update->execute();
    $stmt_update->close();
    
    
    $conn->commit();
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => "Poin hadiah ($nama_hadiah) berhasil diubah dari $old_poin menjadi $new_poin."]);

} catch (Exception $e) {
    
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}