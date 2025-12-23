<?php
include '../../../aa_kon_sett.php';
session_start();
session_regenerate_id(true); 
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$period = $_GET['period'] ?? 'day'; 

// Query berdasarkan periode
if ($period == 'day') {
    $sql = "SELECT DATE(tgl_trans) AS tanggal, COUNT(DISTINCT no_bon) AS total_transaksi 
            FROM trans_b 
            WHERE tgl_trans BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()
            GROUP BY DATE(tgl_trans)
            ORDER BY tanggal ASC";
} elseif ($period == 'month') {
    $sql = "SELECT MONTH(tgl_trans) AS bulan, YEAR(tgl_trans) AS tahun, COUNT(DISTINCT no_bon) AS total_transaksi
            FROM trans_b 
            WHERE tgl_trans BETWEEN CURDATE() - INTERVAL 12 MONTH AND CURDATE()
            GROUP BY YEAR(tgl_trans), MONTH(tgl_trans)
            ORDER BY tahun ASC, bulan ASC";
} else {
    $sql = "SELECT YEAR(tgl_trans) AS tahun, COUNT(DISTINCT no_bon) AS total_transaksi
            FROM trans_b 
            WHERE tgl_trans BETWEEN CURDATE() - INTERVAL 5 YEAR AND CURDATE()
            GROUP BY YEAR(tgl_trans)
            ORDER BY tahun ASC";
}

// Eksekusi query
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

// Ambil data dan kirimkan dalam format JSON
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(['data' => $data]);
$conn->close();
?>
