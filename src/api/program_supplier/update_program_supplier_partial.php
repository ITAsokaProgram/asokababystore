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
        throw new Exception('Token tidak ditemukan');
    }
    $token = $matches[1];
    $verif = verify_token($token);
    if (!$verif) {
        http_response_code(401);
        throw new Exception('Token tidak valid');
    }
    $kd_user = $verif->id ?? $verif->kode ?? 'system';
    if (!$conn) {
        throw new Exception("Koneksi Database Gagal");
    }
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception("Invalid input");
    }
    $mode = $input['mode'] ?? '';
    $nomor_dokumen = $input['nomor_dokumen'] ?? '';
    if (empty($nomor_dokumen)) {
        throw new Exception("Nomor Dokumen tidak ditemukan");
    }
    $qOld = $conn->prepare("SELECT * FROM program_supplier WHERE nomor_dokumen = ?");
    $qOld->bind_param("s", $nomor_dokumen);
    $qOld->execute();
    $oldData = $qOld->get_result()->fetch_assoc();
    $qOld->close();
    if (!$oldData) {
        throw new Exception("Data dengan Nomor Dokumen '$nomor_dokumen' tidak ditemukan.");
    }
    if ($mode === 'finance') {
        $nilai_transfer = isset($input['nilai_transfer']) ? (float) $input['nilai_transfer'] : 0;
        $tgl_transfer = !empty($input['tanggal_transfer']) ? $input['tanggal_transfer'] : null;
        $sql = "UPDATE program_supplier SET 
                nilai_transfer = ?, 
                tanggal_transfer = ? 
                WHERE nomor_dokumen = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dss", $nilai_transfer, $tgl_transfer, $nomor_dokumen);
    } elseif ($mode === 'tax') {
        $tgl_fpk = !empty($input['tgl_fpk']) ? $input['tgl_fpk'] : null;
        $nsfp_raw = $input['nsfp'] ?? '';
        $nsfp = preg_replace('/[^a-zA-Z0-9]/', '', $nsfp_raw);
        $dpp = isset($input['dpp']) ? (float) $input['dpp'] : 0;
        $ppn = isset($input['ppn']) ? (float) $input['ppn'] : 0;
        $pph = isset($input['pph']) ? (float) $input['pph'] : 0;
        $nomor_bukpot = $input['nomor_bukpot'] ?? '';
        $sql = "UPDATE program_supplier SET 
                tgl_fpk = ?, 
                nsfp = ?, 
                dpp = ?, 
                ppn = ?, 
                pph = ?, 
                nomor_bukpot = ? 
                WHERE nomor_dokumen = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdddss", $tgl_fpk, $nsfp, $dpp, $ppn, $pph, $nomor_bukpot, $nomor_dokumen);
    } else {
        throw new Exception("Mode update tidak valid");
    }
    if ($stmt->execute()) {
        $newData = array_merge($oldData, $input);
        write_finance_log($conn, $kd_user, 'program_supplier', $nomor_dokumen, 'UPDATE', $oldData, $newData);
        echo json_encode(['success' => true, 'message' => 'Data berhasil diperbarui']);
    } else {
        throw new Exception("Database Error: " . $stmt->error);
    }
} catch (Exception $e) {
    http_response_code(200);
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>