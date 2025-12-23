<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
try {
    $nsfp = $_GET['nsfp'] ?? '';
    if (empty($nsfp)) {
        throw new Exception("NSFP kosong");
    }
    $query = "SELECT 
                nama_penjual, 
                tgl_faktur_pajak, 
                harga_jual as dpp, 
                dpp_nilai_lain, 
                ppn, 
                kode_store 
              FROM ff_coretax 
              WHERE nsfp = ? 
              LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nsfp);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    if ($data) {
        echo json_encode([
            'success' => true,
            'found' => true,
            'data' => [
                'nama_supplier' => $data['nama_penjual'],
                'tgl_faktur' => $data['tgl_faktur_pajak'],
                'dpp' => (float) $data['dpp'],
                'dpp_nilai_lain' => (float) $data['dpp_nilai_lain'],
                'ppn' => (float) $data['ppn'],
                'kode_store' => $data['kode_store']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'found' => false,
            'message' => 'Data tidak ditemukan di Coretax'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>