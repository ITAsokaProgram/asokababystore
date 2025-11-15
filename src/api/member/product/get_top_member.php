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
$status = $_GET['status'] ?? 'all';
$params = [];
$types = "";
$date_sql = "";
$status_sql = "";
if (isset($_GET['date'])) {
    if (!strtotime($_GET['date'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Format tanggal tidak valid']);
        exit;
    }
    $date_sql = " AND DATE(t.tgl_trans) = ? ";
    $params[] = $_GET['date'];
    $types .= "s";
    $start_date_non = $_GET['date'];
    $end_date_non = $_GET['date'];
} elseif (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    if (!strtotime($_GET['start_date']) || !strtotime($_GET['end_date'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Format start_date/end_date tidak valid']);
        exit;
    }
    if ($_GET['start_date'] === $_GET['end_date']) {
        $date_sql = " AND DATE(t.tgl_trans) = ? ";
        $params[] = $_GET['start_date'];
        $types .= "s";
    } else {
        $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
        $params[] = $_GET['start_date'];
        $params[] = $_GET['end_date'];
        $types .= "ss";
    }
    $start_date_non = $_GET['start_date'];
    $end_date_non = $_GET['end_date'];
} elseif (isset($_GET['filter']) && $_GET['filter'] === 'kemarin') {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $date_sql = " AND DATE(t.tgl_trans) = ? ";
    $params[] = $yesterday;
    $types .= "s";
    $start_date_non = $yesterday;
    $end_date_non = $yesterday;
} else {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $date_sql = " AND DATE(t.tgl_trans) = ? ";
    $params[] = $yesterday;
    $types .= "s";
    $start_date_non = $yesterday;
    $end_date_non = $yesterday;
}
$cutoff_active = date('Y-m-d 00:00:00', strtotime("-3 months"));
if ($status === 'active') {
    $status_sql = " AND (c.Last_Trans >= ?) ";
    $params[] = $cutoff_active;
    $types .= "s";
} elseif ($status === 'inactive') {
    $status_sql = " AND (c.Last_Trans < ? OR c.Last_Trans IS NULL) ";
    $params[] = $cutoff_active;
    $types .= "s";
}
$sql = "SELECT 
    t.kd_cust,
    c.nama_cust,
    ks.nm_alias AS cabang,
    SUM(t.qty) AS total_qty,
    SUM(t.qty * t.harga) AS total_penjualan
FROM trans_b t
LEFT JOIN customers c ON t.kd_cust = c.kd_cust
LEFT JOIN kode_store ks ON ks.kd_store = t.kd_store
WHERE 
    t.kd_cust IS NOT NULL
    AND t.kd_cust NOT IN ('', '898989', '#898989', '#999999999')
    $date_sql 
    $status_sql 
GROUP BY t.kd_cust, c.nama_cust
ORDER BY total_penjualan DESC
LIMIT 50";
$paramsNon = [];
$typesNon = "";
$date_sql_non = "";
if ($start_date_non === $end_date_non) {
    $date_sql_non = " AND DATE(t.tgl_trans) = ? ";
    $paramsNon[] = $start_date_non;
    $typesNon .= "s";
} else {
    $date_sql_non = " AND t.tgl_trans BETWEEN ? AND ? ";
    $paramsNon[] = $start_date_non;
    $paramsNon[] = $end_date_non;
    $typesNon .= "ss";
}
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
    $date_sql_non 
GROUP BY t.no_bon, ks.nm_alias, ks.kd_store
ORDER BY total_penjualan DESC
LIMIT 50";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Statement error (member): " . $conn->error]);
    exit;
}
$stmt->bind_param($types, ...$params);
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
$stmtNon->bind_param($typesNon, ...$paramsNon);
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