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
                id, 
                nsfp, 
                tgl_faktur, 
                nama_supplier, 
                dpp, 
                dpp_nilai_lain, 
                ppn, 
                total,
                edit_pada 
              FROM ff_faktur_pajak 
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