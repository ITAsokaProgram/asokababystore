<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';

try {
    if (!isset($_GET['buku_besar_id']))
        throw new Exception("ID tidak valid");

    $id = (int) $_GET['buku_besar_id'];

    $query = "SELECT * FROM buku_besar_angsuran WHERE buku_besar_id = ? ORDER BY tanggal_bayar DESC, id DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $row['nominal_bayar'] = (float) $row['nominal_bayar'];
        $data[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>