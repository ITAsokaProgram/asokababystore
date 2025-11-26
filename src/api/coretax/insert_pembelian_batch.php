<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
try {
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    if (!isset($input['items']) || !is_array($input['items'])) {
        throw new Exception("Format data tidak valid");
    }
    $items = $input['items'];
    $inserted_count = 0;
    $conn->begin_transaction();
    $stmt = $conn->prepare("INSERT INTO ff_pembelian (nama_supplier, kode_supplier, tgl_nota, no_faktur, dpp, ppn, total_terima_fp) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt)
        throw new Exception("Database Error: Gagal menyiapkan query.");
    foreach ($items as $row) {
        if (empty($row['nama_supplier']))
            continue;
        $nama_supplier = $row['nama_supplier'];
        $kode_supplier = $row['kode_supplier'];
        $tgl_nota = $row['tgl_nota'];
        $no_faktur = $row['no_faktur'];
        $dpp = $row['dpp'];
        $ppn = $row['ppn'];
        $total = $row['total_terima_fp'];
        $stmt->bind_param(
            "ssssddd",
            $nama_supplier,
            $kode_supplier,
            $tgl_nota,
            $no_faktur,
            $dpp,
            $ppn,
            $total
        );
        if (!$stmt->execute()) {
            if ($stmt->errno == 1062) {
                throw new Exception("Gagal Simpan: Nomor Faktur '$no_faktur' sudah pernah diinput sebelumnya. Mohon cek kembali data Anda.");
            }
            throw new Exception("Gagal menyimpan baris faktur $no_faktur. Terjadi kesalahan sistem.");
        }
        $inserted_count++;
    }
    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => "Berhasil menyimpan $inserted_count data pembelian."
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>