<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $kata_kunci = $input['kata_kunci'] ?? '';

    if (empty($kata_kunci)) {
        throw new Exception("Kata kunci tidak valid.");
    }

    $sql = "DELETE FROM wa_balasan_otomatis WHERE kata_kunci = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $kata_kunci);

    if (!$stmt->execute()) {
        throw new Exception("Gagal menghapus: " . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Data tidak ditemukan atau sudah dihapus.");
    }

    echo json_encode(['success' => true, 'message' => 'Data berhasil dihapus.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>