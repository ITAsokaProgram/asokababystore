<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
try {
    $no_seri_fp = $_GET['no_seri_fp'] ?? '';
    $exclude_id = $_GET['exclude_id'] ?? 0;
    if (empty($no_seri_fp)) {
        echo json_encode(['exists' => false]);
        exit;
    }
    $query = "SELECT id, nama_supplier, tgl_faktur FROM ff_faktur_pajak WHERE no_seri_fp = ? AND id != ? LIMIT 1";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception($conn->error);
    }
    $stmt->bind_param("si", $no_seri_fp, $exclude_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    if ($data) {
        $tgl = date('d-m-Y', strtotime($data['tgl_faktur']));
        echo json_encode([
            'exists' => true,
            'message' => "No Seri Faktur ini sudah ada (Supplier: {$data['nama_supplier']}, Tgl: $tgl)",
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