<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';

try {
    $no_faktur = $_GET['no_faktur'] ?? '';
    $exclude_id = $_GET['exclude_id'] ?? 0; // Untuk mode edit

    if (empty($no_faktur)) {
        echo json_encode(['exists' => false]);
        exit;
    }

    // Cek di tabel ff_pembelian
    $query = "SELECT id, nama_supplier, tgl_nota FROM ff_pembelian WHERE no_faktur = ? AND id != ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $no_faktur, $exclude_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        echo json_encode([
            'exists' => true,
            'message' => "No Faktur sudah digunakan",
            'data' => $data
        ]);
    } else {
        echo json_encode(['exists' => false]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['exists' => false, 'error' => $e->getMessage()]);
}
?>