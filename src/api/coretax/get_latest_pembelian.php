<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
try {
    if (!$conn) {
        throw new Exception("Koneksi Database Gagal");
    }
    $query = "SELECT 
                fp.id, 
                fp.nama_supplier, 
                fp.kode_supplier, 
                fp.kode_store, 
                fp.tgl_nota, 
                fp.no_faktur, 
                fp.dpp, 
                fp.dpp_nilai_lain, 
                fp.ppn, 
                fp.total_terima_fp,
                fp.edit_pada,
                fp.status, 
                fp.nsfp,
                ks.nm_alias 
              FROM ff_pembelian as fp
              INNER JOIN kode_store as ks on fp.kode_store = ks.kd_store
            --   WHERE DATE(fp.dibuat_pada) = CURDATE()
              ORDER BY COALESCE(fp.edit_pada, fp.dibuat_pada) DESC, fp.id DESC 
            --   LIMIT 50
              
              ";
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception("Query SQL Error: " . $conn->error);
    }
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $row['id'] = (int) $row['id'];
        $row['dpp'] = (float) $row['dpp'];
        $row['dpp_nilai_lain'] = (float) ($row['dpp_nilai_lain'] ?? 0);
        $row['ppn'] = (float) $row['ppn'];
        $row['total_terima_fp'] = (float) $row['total_terima_fp'];
        $data[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>