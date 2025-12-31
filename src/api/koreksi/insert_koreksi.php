<?php
session_start();
ini_set('display_errors', 0);
require_once __DIR__ . '/../../../aa_kon_sett.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method Not Allowed');
    }

    $input = json_decode(file_get_contents('php://input'), true);

    $kode_store = trim($input['kode_store'] ?? null);
    $tgl_koreksi = $input['tgl_koreksi'] ?? '';

    $kode_supp = trim($input['kode_supp'] ?? '');
    $nama_supplier = trim($input['nama_supplier'] ?? '');
    $no_faktur = trim($input['no_faktur'] ?? '');
    $total_koreksi = (float) ($input['total_koreksi'] ?? 0);
    $keterangan = $input['keterangan'] ?? '';


    if (empty($kode_store) || empty($tgl_koreksi) || empty($no_faktur)) {
        throw new Exception("Cabang, Tanggal, dan No Faktur wajib diisi.");
    }


    $cek_sql = "SELECT count(*) as cnt FROM c_koreksi WHERE tgl_koreksi = ? AND no_faktur = ?";
    $stmt_cek = $conn->prepare($cek_sql);


    $stmt_cek->bind_param("ss", $tgl_koreksi, $no_faktur);
    $stmt_cek->execute();
    $res_cek = $stmt_cek->get_result()->fetch_assoc();

    if ($res_cek['cnt'] > 0) {
        throw new Exception("Data duplikat! Kombinasi Tanggal '$tgl_koreksi' dan No Faktur '$no_faktur' sudah ada.");
    }


    $sql_insert = "INSERT INTO c_koreksi 
                   (tgl_koreksi, kode_store, kode_supp, nama_supplier, no_faktur, total_koreksi, keterangan) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql_insert);
    if (!$stmt)
        throw new Exception("Prepare failed: " . $conn->error);

    $stmt->bind_param(
        "sssssds",
        $tgl_koreksi,
        $kode_store,
        $kode_supp,
        $nama_supplier,
        $no_faktur,
        $total_koreksi,
        $keterangan
    );

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Data Koreksi berhasil disimpan.'
        ]);
    } else {
        throw new Exception("Gagal menyimpan: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(200);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>