<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../helpers/finance_log_helper.php';
try {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token tidak ditemukan']);
        exit;
    }
    $token = $matches[1];
    $verif = verify_token($token);
    if (!$verif) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token tidak valid']);
        exit;
    }
    $kd_user = $verif->id ?? $verif->kode ?? 0;
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    $id = isset($input['id']) ? (int) $input['id'] : 0;
    if ($id === 0) {
        throw new Exception("ID tidak valid");
    }
    $queryCheck = "SELECT * FROM ff_faktur_pajak WHERE id = ?";
    $stmtCheck = $conn->prepare($queryCheck);
    $stmtCheck->bind_param("i", $id);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();
    if ($resCheck->num_rows === 0) {
        throw new Exception("Data faktur pajak tidak ditemukan");
    }
    $oldData = $resCheck->fetch_assoc();
    $nsfp = $oldData['nsfp'];
    $no_invoice = $oldData['no_invoice'];
    if (!empty($nsfp)) {
        $qPembelian = "SELECT id FROM ff_pembelian WHERE nsfp = ? LIMIT 1";
        $stmtPembelian = $conn->prepare($qPembelian);
        $stmtPembelian->bind_param("s", $nsfp);
        $stmtPembelian->execute();
        if ($stmtPembelian->get_result()->num_rows > 0) {
            throw new Exception("Gagal Hapus: NSFP ($nsfp) ini sudah ada di Data Pembelian.");
        }
    }
    $queryDel = "DELETE FROM ff_faktur_pajak WHERE id = ?";
    $stmtDel = $conn->prepare($queryDel);
    $stmtDel->bind_param("i", $id);
    if ($stmtDel->execute()) {
        write_finance_log(
            $conn,
            $kd_user,
            'ff_faktur_pajak',
            $no_invoice,
            'DELETE',
            $oldData,
            null
        );
        echo json_encode(['success' => true, 'message' => "Data Faktur (Invoice: $no_invoice) berhasil dihapus."]);
    } else {
        throw new Exception("Gagal menghapus data dari database.");
    }
} catch (Exception $e) {
    http_response_code(200);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>