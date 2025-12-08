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

    $tgl_receipt = $input['tgl_receipt'] ?? '';
    $kode_supp = trim($input['kode_supp'] ?? '');
    $nama_supplier = trim($input['nama_supplier'] ?? '');
    $no_faktur = trim($input['no_faktur'] ?? '');
    // [BARU] Ambil no_invoice
    $no_invoice = trim($input['no_invoice'] ?? '');
    $total_penerimaan = (float) ($input['total_penerimaan'] ?? 0);
    $keterangan = $input['keterangan'] ?? '';

    if (empty($tgl_receipt) || empty($kode_supp) || empty($no_faktur)) {
        throw new Exception("Tanggal, Kode Supplier, dan No Faktur wajib diisi.");
    }

    // Cek duplikat (tetap berdasarkan faktur dan supplier)
    $cek_sql = "SELECT count(*) as cnt FROM c_receipt WHERE kode_supp = ? AND no_faktur = ?";
    $stmt_cek = $conn->prepare($cek_sql);
    $stmt_cek->bind_param("ss", $kode_supp, $no_faktur);
    $stmt_cek->execute();
    $res_cek = $stmt_cek->get_result()->fetch_assoc();

    if ($res_cek['cnt'] > 0) {
        throw new Exception("Data duplikat! Kombinasi Kode Supplier '$kode_supp' dan No Faktur '$no_faktur' sudah ada.");
    }

    // [UPDATE] Query Insert tambah no_invoice
    $sql_insert = "INSERT INTO c_receipt 
                   (tgl_receipt, kode_supp, nama_supplier, no_faktur, no_invoice, total_penerimaan, keterangan) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql_insert);
    if (!$stmt)
        throw new Exception("Prepare failed: " . $conn->error);

    // [UPDATE] Bind param tambah 's' untuk no_invoice
    $stmt->bind_param(
        "sssssds",
        $tgl_receipt,
        $kode_supp,
        $nama_supplier,
        $no_faktur,
        $no_invoice, // [BARU]
        $total_penerimaan,
        $keterangan
    );

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Data Receipt berhasil disimpan.'
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