<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
try {
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    if (!$input) {
        throw new Exception("Data tidak valid");
    }
    $id = isset($input['id']) ? (int) $input['id'] : null;
    $no_lpb = $input['no_lpb'] ?? '';
    $kode_supplier = $input['kode_supplier'] ?? '';
    $nama_supplier = $input['nama_supplier'] ?? '';
    $tgl_nota = $input['tgl_nota'] ?? date('Y-m-d');
    $no_faktur = $input['no_faktur'] ?? '';
    $dpp = (float) ($input['dpp'] ?? 0);
    $ppn = (float) ($input['ppn'] ?? 0);
    $total_terima_fp = (float) ($input['total_terima_fp'] ?? 0);
    if (empty($no_faktur) || empty($nama_supplier)) {
        throw new Exception("No Faktur dan Nama Supplier wajib diisi.");
    }
    if ($id) {
        $stmt = $conn->prepare("UPDATE ff_pembelian SET nama_supplier=?, kode_supplier=?, tgl_nota=?, no_faktur=?, dpp=?, ppn=?, total_terima_fp=?, edit_pada=NOW() WHERE id=?");
        if (!$stmt)
            throw new Exception("Prepare Update Error: " . $conn->error);
        $stmt->bind_param("ssssdddi", $nama_supplier, $kode_supplier, $tgl_nota, $no_faktur, $dpp, $ppn, $total_terima_fp, $id);
        if (!$stmt->execute()) {
            throw new Exception("Gagal mengupdate data: " . $stmt->error);
        }
        $message = "Data berhasil diperbarui.";
    } else {
        $check = $conn->prepare("SELECT id FROM ff_pembelian WHERE no_faktur = ?");
        $check->bind_param("s", $no_faktur);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            throw new Exception("No Faktur '$no_faktur' sudah ada di database.");
        }
        $check->close();
        $stmt = $conn->prepare("INSERT INTO ff_pembelian (nama_supplier, kode_supplier, tgl_nota, no_faktur, dpp, ppn, total_terima_fp) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt)
            throw new Exception("Prepare Insert Error: " . $conn->error);
        $stmt->bind_param("ssssddd", $nama_supplier, $kode_supplier, $tgl_nota, $no_faktur, $dpp, $ppn, $total_terima_fp);
        if (!$stmt->execute()) {
            throw new Exception("Gagal menyimpan data: " . $stmt->error);
        }
        $message = "Data berhasil disimpan.";
    }
    echo json_encode(['success' => true, 'message' => $message]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>