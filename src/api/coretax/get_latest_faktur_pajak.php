<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
try {
    if (!$conn) {
        throw new Exception("Koneksi Database Gagal: " . mysqli_connect_error());
    }
    $query = "SELECT 
                fp.id, 
                fp.nsfp, 
                fp.no_faktur,  
                fp.tgl_faktur, 
                fp.nama_supplier, 
                fp.dpp, 
                fp.dpp_nilai_lain, 
                fp.ppn, 
                fp.total,
                fp.kode_store,
                fp.edit_pada,
                ks.Nm_Alias as nm_alias 
              FROM ff_faktur_pajak fp
              LEFT JOIN kode_store ks ON fp.kode_store = ks.Kd_Store
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
        $row['dpp_nilai_lain'] = (float) $row['dpp_nilai_lain'];
        $row['ppn'] = (float) $row['ppn'];
        $row['total'] = (float) $row['total'];
        $data[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>