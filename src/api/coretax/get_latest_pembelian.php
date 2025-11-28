<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';

try {
    if (!$conn) {
        throw new Exception("Koneksi Database Gagal");
    }

    // Select kolom baru juga
    $query = "SELECT 
                id, 
                nama_supplier, 
                kode_supplier, 
                kode_store, 
                tgl_nota, 
                no_faktur, 
                dpp, 
                dpp_nilai_lain, 
                ppn, 
                total_terima_fp,
                edit_pada,
                ks.nm_alias 
              FROM ff_pembelian as fp
              inner join kode_store as ks on fp.kode_store = ks.kd_store
              ORDER BY COALESCE(edit_pada, dibuat_pada) DESC, id DESC 
              LIMIT 50";

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