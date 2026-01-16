<?php
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../helpers/finance_log_helper.php';
require_once __DIR__ . '/../../../aa_kon_sett.php';

try {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        http_response_code(401);
        throw new Exception('Token tidak ditemukan');
    }
    $verif = verify_token($matches[1]);
    if (!$verif) {
        http_response_code(401);
        throw new Exception("Token tidak valid");
    }
    $kd_user = $verif->id ?? $verif->kode ?? 0;
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    $doc = $input['nomor_dokumen'] ?? '';
    if (empty($doc))
        throw new Exception("Nomor dokumen tidak valid");
    $qCheck = $conn->prepare("SELECT * FROM program_supplier WHERE nomor_dokumen = ?");
    $qCheck->bind_param("s", $doc);
    $qCheck->execute();
    $oldData = $qCheck->get_result()->fetch_assoc();
    $qCheck->close();
    if (!$oldData) {
        throw new Exception("Data tidak ditemukan");
    }
    $stmt = $conn->prepare("DELETE FROM program_supplier WHERE nomor_dokumen = ?");
    $stmt->bind_param("s", $doc);
    if ($stmt->execute()) {
        write_finance_log($conn, $kd_user, 'program_supplier', $doc, 'DELETE', $oldData, null);
        echo json_encode(['success' => true, 'message' => 'Data berhasil dihapus']);
    } else {
        throw new Exception("Gagal menghapus data");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>