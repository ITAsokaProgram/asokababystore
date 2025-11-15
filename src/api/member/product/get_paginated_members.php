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
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-1 day'));
$end_date = $_GET['end_date'] ?? date('Y-m-d', strtotime('-1 day'));
$search = $_GET['search'] ?? '';
$sortBy = $_GET['sort_by'] ?? 'belanja';
$page = (int) ($_GET['page'] ?? 1);
$limit = (int) ($_GET['limit'] ?? 10);
$offset = ($page - 1) * $limit;
if (!strtotime($start_date) || !strtotime($end_date)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tanggal tidak valid']);
    exit;
}
$params = [$start_date, $end_date];
$searchSql = "";
if (!empty($search)) {
    $searchSql = " AND (c.nama_cust LIKE ? OR t.kd_cust LIKE ?) ";
    $searchValue = "%" . $search . "%";
    $params[] = $searchValue;
    $params[] = $searchValue;
}
$orderBySql = " ORDER BY total_penjualan DESC ";
if ($sortBy === 'qty') {
    $orderBySql = " ORDER BY total_qty DESC ";
} elseif ($sortBy === 'nama') {
    $orderBySql = " ORDER BY c.nama_cust ASC ";
}
$countSql = "SELECT COUNT(*) AS total
             FROM (
                 SELECT 1
                 FROM trans_b t
                 LEFT JOIN customers c ON t.kd_cust = c.kd_cust
                 WHERE 
                     t.kd_cust IS NOT NULL
                     AND t.kd_cust NOT IN ('', '898989', '#898989', '#999999999')
                     AND t.tgl_trans BETWEEN ? AND ?
                     $searchSql
                 GROUP BY t.kd_cust, c.nama_cust /* <--- UBAH BARIS INI */
             ) AS subquery";
$stmtCount = $conn->prepare($countSql);
if (!$stmtCount) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Statement error (count): " . $conn->error]);
    exit;
}
$types = str_repeat('s', count($params));
$stmtCount->bind_param($types, ...$params);
$stmtCount->execute();
$resultCount = $stmtCount->get_result();
$total_records = $resultCount->fetch_assoc()['total'] ?? 0;
$total_pages = ceil($total_records / $limit);
$stmtCount->close();
$dataSql = "SELECT 
                t.kd_cust,
                c.nama_cust,
                SUM(t.qty) AS total_qty,
                SUM(t.qty * t.harga) AS total_penjualan
            FROM trans_b t
            LEFT JOIN customers c ON t.kd_cust = c.kd_cust
            /* HAPUS JOIN KE KODE_STORE
            LEFT JOIN kode_store ks ON ks.kd_store = t.kd_store 
            */
            WHERE 
                t.kd_cust IS NOT NULL
                AND t.kd_cust NOT IN ('', '898989', '#898989', '#999999999')
                AND t.tgl_trans BETWEEN ? AND ?
                $searchSql
            GROUP BY t.kd_cust, c.nama_cust /* <--- UBAH BARIS INI */
            $orderBySql
            LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types = $types . 'ii';
$stmtData = $conn->prepare($dataSql);
if (!$stmtData) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Statement error (data): " . $conn->error]);
    exit;
}
$stmtData->bind_param($types, ...$params);
$stmtData->execute();
$resultData = $stmtData->get_result();
$data = $resultData->fetch_all(MYSQLI_ASSOC);
$stmtData->close();
if ($total_records === 0) {
    http_response_code(200);
    echo json_encode([
        "success" => false,
        "message" => "Data tidak ditemukan"
    ]);
    $conn->close();
    exit;
}
$response = [
    "success" => true,
    "data" => $data,
    "pagination" => [
        "current_page" => $page,
        "items_per_page" => $limit,
        "total_records" => $total_records,
        "total_pages" => $total_pages,
        "offset" => $offset
    ]
];
http_response_code(200);
echo json_encode($response);
$conn->close();
?>