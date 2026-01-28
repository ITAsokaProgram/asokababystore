<?php

require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");
$verif = authenticate_request();

$sqlTable = "SELECT 
    inv.nama_kasir AS kasir,
    inv.no_bon as no_trans,
    inv.keterangan as ket,
    inv.descp as barang,
    inv.plu,
    inv.ket_cek,
    inv.kode_kasir AS kode,
    inv.kode_toko AS kode_cabang,
    inv.type AS kategori,
    COUNT(inv.type) AS jml_retur,
    inv.tgl_trans AS tanggal,
    s.Nm_Alias AS cabang
FROM invtrans inv
LEFT JOIN kode_store s ON s.Kd_Store = inv.kode_toko 
WHERE inv.type LIKE '%RETUR%' AND inv.tgl_trans BETWEEN CURDATE() - INTERVAL 1 DAY AND CURDATE() AND NOT inv.nama_kasir LIKE 'PROMO%'
GROUP BY inv.kode_kasir, inv.tgl_trans ORDER BY jml_retur DESC";


$sqlSummaryTotalReturDay = "SELECT 
    COUNT(inv.type) AS jml_retur
FROM invtrans inv
LEFT JOIN kode_store s ON s.Kd_Store = inv.kode_toko 
WHERE inv.type LIKE '%RETUR%' AND inv.tgl_trans BETWEEN CURDATE() - INTERVAL 1 DAY AND CURDATE() AND NOT inv.nama_kasir LIKE 'PROMO%'
GROUP BY inv.type;";

$sqlSummaryTotalReturMonth = "SELECT 
    COUNT(inv.type) AS jml_retur
FROM invtrans inv
LEFT JOIN kode_store s ON s.Kd_Store = inv.kode_toko 
WHERE inv.type LIKE '%RETUR%' AND inv.tgl_trans BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())AND NOT inv.nama_kasir LIKE 'PROMO%'
GROUP BY inv.type";

$stmtTable = $conn->prepare($sqlTable);
$stmtTable->execute();
$resultTable = $stmtTable->get_result();
$dataTable = $resultTable->fetch_all(MYSQLI_ASSOC);

$stmtSummaryTotalReturDay = $conn->prepare($sqlSummaryTotalReturDay);
$stmtSummaryTotalReturDay->execute();
$resultSummaryTotalReturDay = $stmtSummaryTotalReturDay->get_result();
$dataSummaryTotalReturDay = $resultSummaryTotalReturDay->fetch_all(MYSQLI_ASSOC);

$stmtSummaryTotalReturMonth = $conn->prepare($sqlSummaryTotalReturMonth);
$stmtSummaryTotalReturMonth->execute();
$resultSummaryTotalReturMonth = $stmtSummaryTotalReturMonth->get_result();
$dataSummaryTotalReturMonth = $resultSummaryTotalReturMonth->fetch_all(MYSQLI_ASSOC);


if ($resultTable && $resultSummaryTotalReturDay && $resultSummaryTotalReturMonth) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Data berhasil diambil',
        'data' => $dataTable,
        'summaryTotalReturDay' => $dataSummaryTotalReturDay,
        'summaryTotalReturMonth' => $dataSummaryTotalReturMonth
    ]);
} else {
    http_response_code(204);
    echo json_encode([
        'status' => 'success',
        'message' => 'Tidak ada data invalid transaksi',
        'data' => [],
        'summaryTotalReturDay' => [],
        'summaryTotalReturMonth' => []
    ]);
}
$stmtTable->close();
$stmtSummaryTotalReturDay->close();
$stmtSummaryTotalReturMonth->close();
$conn->close();