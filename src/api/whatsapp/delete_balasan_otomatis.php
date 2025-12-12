<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');

try {
    // Ambil Input JSON
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;

    // --- Validasi ---
    if (empty($id)) {
        throw new Exception("ID data tidak valid.");
    }

    // --- Mulai Transaksi Database ---
    $conn->begin_transaction();

    // 1. Hapus Pesan (Child Table) terlebih dahulu
    // (Langkah ini opsional jika di database sudah di-set ON DELETE CASCADE, tapi lebih aman ditulis manual)
    $sql_pesan = "DELETE FROM wa_balasan_otomatis_pesan WHERE kamus_id = ?";
    $stmt_pesan = $conn->prepare($sql_pesan);
    $stmt_pesan->bind_param("i", $id);

    if (!$stmt_pesan->execute()) {
        throw new Exception("Gagal menghapus detail pesan: " . $stmt_pesan->error);
    }
    $stmt_pesan->close();

    // 2. Hapus Kamus/Keyword (Parent Table)
    $sql_kamus = "DELETE FROM wa_balasan_otomatis_kamus WHERE id = ?";
    $stmt_kamus = $conn->prepare($sql_kamus);
    $stmt_kamus->bind_param("i", $id);

    if (!$stmt_kamus->execute()) {
        throw new Exception("Gagal menghapus keyword: " . $stmt_kamus->error);
    }

    // Cek apakah ada data yang terhapus
    if ($stmt_kamus->affected_rows === 0) {
        // Bisa jadi ID tidak ditemukan, tapi kita anggap sukses saja (idempotent) 
        // atau throw error jika ingin strict. Di sini kita anggap sukses.
    }
    $stmt_kamus->close();

    // --- Commit Transaksi ---
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Data berhasil dihapus.']);

} catch (Exception $e) {
    // Rollback jika ada error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>