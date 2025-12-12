<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');
try {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
    if (empty($id)) {
        throw new Exception("ID data tidak valid.");
    }
    $conn->begin_transaction();
    $sql_pesan = "DELETE FROM wa_balasan_otomatis_pesan WHERE kamus_id = ?";
    $stmt_pesan = $conn->prepare($sql_pesan);
    $stmt_pesan->bind_param("i", $id);
    if (!$stmt_pesan->execute()) {
        throw new Exception("Gagal menghapus detail pesan: " . $stmt_pesan->error);
    }
    $stmt_pesan->close();
    $sql_kamus = "DELETE FROM wa_balasan_otomatis_kamus WHERE id = ?";
    $stmt_kamus = $conn->prepare($sql_kamus);
    $stmt_kamus->bind_param("i", $id);
    if (!$stmt_kamus->execute()) {
        throw new Exception("Gagal menghapus keyword: " . $stmt_kamus->error);
    }
    if ($stmt_kamus->affected_rows === 0) {
    }
    $stmt_kamus->close();
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Data berhasil dihapus.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>