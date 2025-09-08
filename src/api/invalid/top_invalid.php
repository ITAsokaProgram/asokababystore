<?php

require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");
$headers = getallheaders();

if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak, token tidak ditemukan']);
    exit;
}
$authHeader = $headers['Authorization'];

$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1]; // ini yang aman dan baku
}

$verif = verify_token($token);

$sqlTable = "SELECT 
    inv.nama_kasir AS kasir,
    inv.kode_kasir AS kode,
    inv.kode_toko AS kode_cabang,
    inv.type AS kategori,
    COUNT(inv.type) AS jml_gagal,
    inv.tgl_trans AS tanggal,
    s.Nm_Alias AS cabang
FROM invtrans inv
LEFT JOIN kode_store s ON s.Kd_Store = inv.kode_toko 
WHERE inv.type LIKE '%VOID%' AND inv.tgl_trans BETWEEN CURDATE() - INTERVAL 1 DAY AND CURDATE() AND NOT inv.nama_kasir LIKE 'PROMO%'
GROUP BY inv.kode_kasir, inv.tgl_trans ORDER BY jml_gagal DESC";


$sqlSummaryTotalVoid = "SELECT 
COUNT(inv.type) AS total_void
FROM invtrans inv
LEFT JOIN kode_store s ON s.kd_store = inv.kode_toko
WHERE inv.type LIKE '%VOID%' AND inv.tgl_trans BETWEEN CURDATE() - INTERVAL 1 DAY AND CURDATE() AND NOT inv.nama_kasir LIKE 'PROMO%'
GROUP BY inv.type";

$sqlSummaryTopCabang = "SELECT 
s.nm_alias,
COUNT(inv.type) AS total_void
FROM invtrans inv
LEFT JOIN kode_store s ON s.kd_store = inv.kode_toko
WHERE inv.type LIKE '%VOID%' AND inv.tgl_trans BETWEEN CURDATE() - INTERVAL 1 DAY AND CURDATE() AND NOT inv.nama_kasir LIKE 'PROMO%'
GROUP BY s.kd_store ORDER BY total_void DESC LIMIT 1";

$stmtTable = $conn->prepare($sqlTable);
$stmtTable->execute();
$resultTable = $stmtTable->get_result();
$dataTable = $resultTable->fetch_all(MYSQLI_ASSOC);

$stmtSummaryTotalVoid = $conn->prepare($sqlSummaryTotalVoid);
$stmtSummaryTotalVoid->execute();
$resultSummaryTotalVoid = $stmtSummaryTotalVoid->get_result();
$dataSummaryTotalVoid = $resultSummaryTotalVoid->fetch_all(MYSQLI_ASSOC);

$stmtSummaryTopCabang = $conn->prepare($sqlSummaryTopCabang);
$stmtSummaryTopCabang->execute();
$resultSummaryTopCabang = $stmtSummaryTopCabang->get_result();
$dataSummaryTopCabang = $resultSummaryTopCabang->fetch_all(MYSQLI_ASSOC);


if ($resultTable && $resultSummaryTotalVoid && $resultSummaryTopCabang) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Data berhasil diambil',
        'data' => $dataTable,
        'summaryTotalVoid' => $dataSummaryTotalVoid,
        'summaryTopCabang' => $dataSummaryTopCabang
    ]);
} else {
    http_response_code(204);
    echo json_encode([
        'status' => 'success',
        'message' => 'Tidak ada data invalid transaksi',
        'data' => [],
        'summaryTotalVoid' => [],
        'summaryTopCabang' => []
    ]);
}
$stmtTable->close();
$stmtSummaryTotalVoid->close();
$stmtSummaryTopCabang->close();
$conn->close();