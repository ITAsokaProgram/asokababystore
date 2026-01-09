<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
try {
    if (!$conn)
        throw new Exception("Koneksi Database Gagal");
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input)
        throw new Exception("Invalid input");
    $mode = $input['mode'] ?? '';
    $nomor_dokumen = $input['nomor_dokumen'] ?? '';
    if (empty($nomor_dokumen))
        throw new Exception("Nomor Dokumen tidak ditemukan");
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
        echo json_encode(['success' => true, 'message' => 'Data berhasil diperbarui']);
    } else {
        throw new Exception("Database Error: " . $stmt->error);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>