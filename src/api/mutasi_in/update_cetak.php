<?php
header('Content-Type: application/json');
include '../../../aa_kon_sett.php';
require_once __DIR__ . '/../../helpers/otorisasi_helper.php';
$no_faktur = $_POST['no_faktur'] ?? '';
$kode_dari = $_POST['kode_dari'] ?? '';
$user_otorisasi = $_POST['user_otorisasi'] ?? '';
$pass_otorisasi = $_POST['pass_otorisasi'] ?? '';
if (empty($no_faktur) || empty($user_otorisasi) || empty($pass_otorisasi)) {
    echo json_encode(['success' => false, 'message' => 'Data otorisasi tidak lengkap']);
    exit;
}
try {
    if (!cekOtorisasi($conn, $user_otorisasi, $pass_otorisasi, 'print_mutasi_invoice')) {
        throw new Exception("Otorisasi Gagal! Kode salah atau Anda tidak memiliki akses cetak invoice.");
    }
    $checkSql = "SELECT receipt FROM mutasi_in WHERE no_faktur = ? AND kode_dari = ? LIMIT 1";
    $stmtCheck = $conn->prepare($checkSql);
    $stmtCheck->bind_param("ss", $no_faktur, $kode_dari);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result()->fetch_assoc();
    if (!$resCheck)
        throw new Exception("Data tidak ditemukan");
    if ($resCheck['receipt'] !== 'True')
        throw new Exception("Barang belum diterima.");
    $updateSql = "UPDATE mutasi_in SET cetak = 'True' WHERE no_faktur = ? AND kode_dari = ?";
    $stmtUpd = $conn->prepare($updateSql);
    $stmtUpd->bind_param("ss", $no_faktur, $kode_dari);
    if ($stmtUpd->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Gagal update database");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>