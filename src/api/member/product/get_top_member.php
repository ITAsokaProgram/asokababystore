<?php
require_once __DIR__ . "/../../../../config.php";
require_once __DIR__ . ("./../../../auth/middleware_login.php");
header("Content-Type:application/json");
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
$headers = getallheaders();
$token = $headers['Authorization'];
$token = str_replace('Bearer ', '', $token);
$user = verify_token($token);
if (!$user) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit;
}
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-1 day'));
$end_date = $_GET['end_date'] ?? date('Y-m-d', strtotime('-1 day'));
if (!strtotime($start_date) || !strtotime($end_date)) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Tanggal tidak valid']);
    exit;
}
$sql = "SELECT 
    t.kd_cust,
    t.no_bon AS no_trans,
    c.nama_cust,
    ks.nm_alias AS cabang,
    ks.kd_store AS kd_store,
    SUM(t.qty) AS total_qty,
    SUM(t.qty * t.harga) AS total_penjualan
FROM trans_b t
LEFT JOIN customers c ON t.kd_cust = c.kd_cust
LEFT JOIN kode_store ks ON ks.kd_store = t.kd_store
WHERE 
    t.kd_cust IS NOT NULL
    AND t.kd_cust NOT IN ('', '898989', '#898989', '#999999999')
    AND t.tgl_trans BETWEEN ? AND ? 
GROUP BY t.kd_cust, c.nama_cust
ORDER BY total_penjualan DESC
LIMIT 50";
$sqlNon = "SELECT 
    t.no_bon AS no_trans,
    ks.nm_alias AS cabang,  
    ks.kd_store AS kd_store,
    SUM(t.qty) AS total_qty,
    SUM(t.qty * t.harga) AS total_penjualan
FROM trans_b t
LEFT JOIN customers c ON t.kd_cust = c.kd_cust
LEFT JOIN kode_store ks ON ks.kd_store = t.kd_store
WHERE 
    (t.kd_cust IS NULL OR t.kd_cust IN ('', '898989', '999999999'))
    AND t.tgl_trans BETWEEN ? AND ? 
GROUP BY t.no_bon, ks.nm_alias, ks.kd_store
ORDER BY total_penjualan DESC
LIMIT 50";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Statement error (member): " . $conn->error]);
    exit;
}
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
$top_member_by_sales = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$stmtNon = $conn->prepare($sqlNon);
if (!$stmtNon) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Statement error (non-member): " . $conn->error]);
    exit;
}
$stmtNon->bind_param("ss", $start_date, $end_date);
$stmtNon->execute();
$resultNon = $stmtNon->get_result();
$top_member_by_sales_non = $resultNon->fetch_all(MYSQLI_ASSOC);
$stmtNon->close();
if (count($top_member_by_sales) === 0 && count($top_member_by_sales_non) === 0) {
    http_response_code(200);
    echo json_encode([
        "success" => false,
        "message" => "Data tidak ditemukan untuk rentang tanggal ini"
    ]);
    $conn->close();
    exit;
}
$response = [
    "success" => true,
    "message" => "Data berhasil diambil",
    "total" => count($top_member_by_sales),
    "data" => $top_member_by_sales,
    "data_non" => $top_member_by_sales_non
];
http_response_code(200);
echo json_encode($response);
$conn->close();
?>