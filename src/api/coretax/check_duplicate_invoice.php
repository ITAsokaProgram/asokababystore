<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
try {
    $no_invoice = $_GET['no_invoice'] ?? '';
    $kode_store = $_GET['kode_store'] ?? ''; 
    $exclude_id = $_GET['exclude_id'] ?? 0;
    if (empty($no_invoice) || empty($kode_store)) {
        echo json_encode(['exists' => false]);
        exit;
    }
    $query = "SELECT id, nama_supplier, tgl_nota 
              FROM ff_pembelian 
              WHERE no_invoice = ? AND kode_store = ? AND id != ? LIMIT 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $no_invoice, $kode_store, $exclude_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    if ($data) {
        echo json_encode([
            'exists' => true,
            'message' => "Invoice sudah digunakan",
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