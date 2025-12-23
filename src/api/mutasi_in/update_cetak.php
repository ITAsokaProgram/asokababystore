<?php
header('Content-Type: application/json');
include '../../../aa_kon_sett.php';

$no_faktur = $_POST['no_faktur'] ?? '';
$kode_dari = $_POST['kode_dari'] ?? '';

if (empty($no_faktur)) {
    echo json_encode(['success' => false, 'message' => 'No Faktur tidak valid']);
    exit;
}

try {
    // Cek Receipt dulu (Server side validation)
    $checkSql = "SELECT receipt FROM mutasi_in WHERE no_faktur = ? AND kode_dari = ? LIMIT 1";
    $stmtCheck = $conn->prepare($checkSql);
    $stmtCheck->bind_param("ss", $no_faktur, $kode_dari);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result()->fetch_assoc();

    if (!$resCheck) {
        throw new Exception("Data tidak ditemukan");
    }

    if ($resCheck['receipt'] !== 'True') {
        throw new Exception("Barang belum diterima, tidak bisa update status cetak.");
    }

    // Update Status Cetak
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