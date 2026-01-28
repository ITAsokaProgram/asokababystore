<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../helpers/finance_log_helper.php';
try {
    $verif = authenticate_request();

    $kd_user = $verif->id ?? $verif->kode ?? 0;
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    $id = isset($input['id']) ? (int) $input['id'] : 0;
    if ($id === 0) {
        throw new Exception("ID tidak valid");
    }
    $queryCheck = "SELECT * FROM ff_pembelian WHERE id = ?";
    $stmtCheck = $conn->prepare($queryCheck);
    $stmtCheck->bind_param("i", $id);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();
    if ($resCheck->num_rows === 0) {
        throw new Exception("Data pembelian tidak ditemukan");
    }
    $oldData = $resCheck->fetch_assoc();
    $nsfp = $oldData['nsfp'];
    $no_invoice = $oldData['no_invoice'];
    if (!empty($nsfp)) {
        $qCore = "SELECT nsfp FROM ff_coretax WHERE nsfp = ? LIMIT 1";
        $stmtCore = $conn->prepare($qCore);
        $stmtCore->bind_param("s", $nsfp);
        $stmtCore->execute();
        if ($stmtCore->get_result()->num_rows > 0) {
            throw new Exception("Gagal Hapus: Data NSFP ($nsfp) sudah terdaftar di Coretax.");
        }
        $qFisik = "SELECT nsfp FROM ff_faktur_pajak WHERE nsfp = ? LIMIT 1";
        $stmtFisik = $conn->prepare($qFisik);
        $stmtFisik->bind_param("s", $nsfp);
        $stmtFisik->execute();
        if ($stmtFisik->get_result()->num_rows > 0) {
            throw new Exception("Gagal Hapus: Data NSFP ($nsfp) sudah terdaftar di Faktur Fisik.");
        }
    }
    $queryDel = "DELETE FROM ff_pembelian WHERE id = ?";
    $stmtDel = $conn->prepare($queryDel);
    $stmtDel->bind_param("i", $id);
    if ($stmtDel->execute()) {
        write_finance_log(
            $conn,
            $kd_user,
            'ff_pembelian',
            $no_invoice,
            'DELETE',
            $oldData,
            null
        );
        echo json_encode(['success' => true, 'message' => "Data Invoice $no_invoice berhasil dihapus."]);
    } else {
        throw new Exception("Gagal menghapus data database.");
    }
} catch (Exception $e) {
    http_response_code(200);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>