<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';

try {
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    $id = $input['id'] ?? 0;

    if (!$id)
        throw new Exception("ID Invalid");

    $stmt = $conn->prepare("DELETE FROM buku_besar WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data dihapus']);
    } else {
        throw new Exception("Gagal hapus data");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>