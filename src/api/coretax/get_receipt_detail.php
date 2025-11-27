<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
try {
    $no_lpb = $_GET['no_lpb'] ?? '';
    if (empty($no_lpb)) {
        throw new Exception("Parameter No LPB kosong");
    }
    $query = "
        SELECT 
            rh.no_lpb,
            rh.no_faktur,
            rh.tgl_pesan as tgl_nota,
            rh.kode_supp,
            rh.kd_store,  
            s.nama_supp,
            rh.gtot as dpp, 
            rh.gppn as ppn
        FROM receipt_head rh
        LEFT JOIN supplier s ON rh.kode_supp = s.kode_supp AND rh.kd_store = s.kd_store
        WHERE rh.no_lpb = ? 
        LIMIT 1
    ";
    $stmt = $conn->prepare($query);
    if (!$stmt)
        throw new Exception($conn->error);
    $stmt->bind_param("s", $no_lpb);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    if ($data) {
        if (!empty($data['tgl_nota'])) {
            $data['tgl_nota'] = date('Y-m-d', strtotime($data['tgl_nota']));
        }
        echo json_encode([
            'success' => true,
            'data' => [
                'no_lpb' => $data['no_lpb'],
                'no_faktur' => $data['no_faktur'],
                'tgl_nota' => $data['tgl_nota'],
                'kode_supplier' => $data['kode_supp'],
                'nama_supplier' => $data['nama_supp'],
                'kode_store' => $data['kd_store'],
                'dpp' => (float) $data['dpp'],
                'ppn' => (float) $data['ppn']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Data receipt tidak ditemukan, silahkan input manual']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>