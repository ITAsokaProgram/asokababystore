<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';

try {
    $verif = authenticate_request();

    $input = json_decode(file_get_contents('php://input'), true);
    $doc = $input['nomor_dokumen'] ?? '';
    $mode = $input['mode'] ?? '';

    if (empty($doc)) {
        throw new Exception("Nomor Dokumen tidak ditemukan.");
    }

    if ($mode === 'finance') {
        $nilai_tf = (float) ($input['nilai_transfer'] ?? 0);
        $tgl_tf = !empty($input['tanggal_transfer']) ? $input['tanggal_transfer'] : null;

        $stmt = $conn->prepare("UPDATE program_supplier SET nilai_transfer = ?, tanggal_transfer = ? WHERE nomor_dokumen = ?");
        $stmt->bind_param("dss", $nilai_tf, $tgl_tf, $doc);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Data Finance berhasil diupdate.']);
        } else {
            throw new Exception("Gagal update finance: " . $stmt->error);
        }

    } elseif ($mode === 'tax') {
        $tgl_fpk = !empty($input['tgl_fpk']) ? $input['tgl_fpk'] : null;
        $nsfp = $input['nsfp'] ?? '';
        $dpp = (float) ($input['dpp'] ?? 0);
        $ppn = (float) ($input['ppn'] ?? 0);
        $pph = (float) ($input['pph'] ?? 0);
        $bukpot = $input['nomor_bukpot'] ?? '';

        $stmt = $conn->prepare("UPDATE program_supplier SET tgl_fpk=?, nsfp=?, dpp=?, ppn=?, pph=?, nomor_bukpot=? WHERE nomor_dokumen=?");
        $stmt->bind_param("ssdddss", $tgl_fpk, $nsfp, $dpp, $ppn, $pph, $bukpot, $doc);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Data Pajak berhasil diupdate.']);
        } else {
            throw new Exception("Gagal update pajak: " . $stmt->error);
        }

    } else {
        throw new Exception("Mode update tidak valid.");
    }

} catch (Exception $e) {
    http_response_code(200); // Return 200 OK with error message in body
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>