<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
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
    if (!$input) {
        throw new Exception("Data tidak valid");
    }
    $id = isset($input['id']) ? (int) $input['id'] : null;
    $nsfp = $input['nsfp'] ?? '';
    $no_invoice = $input['no_invoice'] ?? '';
    $nama_supplier = $input['nama_supplier'] ?? '';
    $kode_store = $input['kode_store'] ?? NULL;
    $tgl_faktur = !empty($input['tgl_faktur']) ? $input['tgl_faktur'] : NULL;
    $dpp = (float) ($input['dpp'] ?? 0);
    $dpp_nilai_lain = (float) ($input['dpp_nilai_lain'] ?? 0);
    $ppn = (float) ($input['ppn'] ?? 0);
    $total = (float) ($input['total'] ?? 0);
    if (empty($nsfp)) {
        throw new Exception("No Seri Faktur wajib diisi.");
    }
    if ($id) {
        $stmt = $conn->prepare("UPDATE ff_faktur_pajak SET nsfp=?, no_faktur=?, tgl_faktur=?, nama_supplier=?, dpp=?, dpp_nilai_lain=?, ppn=?, total=?, kd_user=?, kode_store=?, edit_pada=NOW() WHERE id=?");
        if (!$stmt)
            throw new Exception("Prepare Update Error: " . $conn->error);
        $stmt->bind_param("ssssddddisi", $nsfp, $no_invoice, $tgl_faktur, $nama_supplier, $dpp, $dpp_nilai_lain, $ppn, $total, $kd_user, $kode_store, $id);
        if (!$stmt->execute()) {
            throw new Exception("Gagal mengupdate data: " . $stmt->error);
        }
        $message = "Data berhasil diperbarui.";
    } else {
        $check = $conn->prepare("SELECT id FROM ff_faktur_pajak WHERE nsfp = ?");
        $check->bind_param("s", $nsfp);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            throw new Exception("No Seri Faktur '$nsfp' sudah ada di database.");
        }
        $check->close();
        $stmt = $conn->prepare("INSERT INTO ff_faktur_pajak (nsfp, no_faktur, tgl_faktur, nama_supplier, dpp, dpp_nilai_lain, ppn, total, kd_user, kode_store) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt)
            throw new Exception("Prepare Insert Error: " . $conn->error);
        $stmt->bind_param("ssssddddis", $nsfp, $no_invoice, $tgl_faktur, $nama_supplier, $dpp, $dpp_nilai_lain, $ppn, $total, $kd_user, $kode_store);
        if (!$stmt->execute()) {
            throw new Exception("Gagal menyimpan data: " . $stmt->error);
        }
        $message = "Data berhasil disimpan.";
    }
    echo json_encode(['success' => true, 'message' => $message]);
} catch (Exception $e) {
    http_response_code(200);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>