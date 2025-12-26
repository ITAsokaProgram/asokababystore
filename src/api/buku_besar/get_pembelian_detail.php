<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
try {
    $raw_term = $_GET['no_faktur'] ?? '';
    $term = trim(urldecode($raw_term));
    if (empty($term)) {
        throw new Exception("Parameter pencarian kosong");
    }
    $query = "
        SELECT 
            id,
            no_faktur,
            no_invoice,
            tgl_nota,
            kode_supplier,
            nama_supplier,
            kode_store,
            total_terima_fp as total_nilai
        FROM ff_pembelian
        WHERE no_faktur = ? OR no_invoice = ?
        LIMIT 1
    ";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Database Error: " . $conn->error);
    }
    $stmt->bind_param("ss", $term, $term);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    if ($data) {
        if (!empty($data['tgl_nota'])) {
            $data['tgl_nota'] = date('Y-m-d', strtotime($data['tgl_nota']));
        }
        echo json_encode([
            'success' => true,
            'found' => true,
            'data' => [
                'id_pembelian' => $data['id'],
                'no_faktur' => !empty($data['no_invoice']) ? $data['no_invoice'] : $data['no_faktur'],
                'no_invoice_asli' => $data['no_invoice'],
                'tgl_nota' => $data['tgl_nota'],
                'kode_supplier' => $data['kode_supplier'],
                'nama_supplier' => $data['nama_supplier'],
                'kode_store' => $data['kode_store'],
                'total_bayar' => (float) $data['total_nilai']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'found' => false,
            'message' => "Data tidak ditemukan berdasarkan No Invoice maupun No Faktur."
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>