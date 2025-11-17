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
$search = $_GET['search'] ?? '';
$sortBy = $_GET['sort_by'] ?? 'belanja';
$page = (int) ($_GET['page'] ?? 1);
$limit = (int) ($_GET['limit'] ?? 10);
$status = $_GET['status'] ?? 'all';
$offset = ($page - 1) * $limit;
$params = [];
$types = "";
$date_sql = "";
$status_sql = "";
$searchSql = "";
$filter_type = $_GET['filter_type'] ?? null;
$filter_preset = $_GET['filter'] ?? null;
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
if ($filter_type === 'custom' && $start_date && $end_date) {
    if ($start_date === $end_date) {
        $date_sql = " AND DATE(t.tgl_trans) = ? ";
        $params[] = $start_date;
        $types .= "s";
    } else {
        $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
        $params[] = $start_date;
        $params[] = $end_date;
        $types .= "ss";
    }
} elseif ($filter_type === 'preset' && $filter_preset) {
    $end = date('Y-m-d');
    $start = '';
    switch ($filter_preset) {
        case 'kemarin':
            $start = date('Y-m-d', strtotime('-1 day'));
            $end = $start;
            $date_sql = " AND DATE(t.tgl_trans) = ? ";
            $params[] = $start;
            $types .= "s";
            break;
        case '1minggu':
            $start = date('Y-m-d', strtotime('-7 days'));
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start;
            $params[] = $end;
            $types .= "ss";
            break;
        case '1bulan':
            $start = date('Y-m-d', strtotime('-1 month'));
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start;
            $params[] = $end;
            $types .= "ss";
            break;
        case '3bulan':
            $start = date('Y-m-d', strtotime('-3 months'));
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start;
            $params[] = $end;
            $types .= "ss";
            break;
        case '6bulan':
            $start = date('Y-m-d', strtotime('-6 months'));
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start;
            $params[] = $end;
            $types .= "ss";
            break;
        case '9bulan':
            $start = date('Y-m-d', strtotime('-9 months'));
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start;
            $params[] = $end;
            $types .= "ss";
            break;
        case '12bulan':
            $start = date('Y-m-d', strtotime('-12 months'));
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start;
            $params[] = $end;
            $types .= "ss";
            break;
        case 'semua':
            $date_sql = "";
            break;
        default:
            $start = date('Y-m-d', strtotime('-3 months'));
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start;
            $params[] = $end;
            $types .= "ss";
            break;
    }
} else {
    if ($start_date && $end_date) {
        if ($start_date === $end_date) {
            $date_sql = " AND DATE(t.tgl_trans) = ? ";
            $params[] = $start_date;
            $types .= "s";
        } else {
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start_date;
            $params[] = $end_date;
            $types .= "ss";
        }
    } else {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $date_sql = " AND DATE(t.tgl_trans) = ? ";
        $params[] = $yesterday;
        $types .= "s";
    }
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
if (!empty($search)) {
    $searchSql = " AND (c.nama_cust LIKE ? OR t.kd_cust LIKE ?) ";
    $searchValue = "%" . $search . "%";
    $params[] = $searchValue;
    $params[] = $searchValue;
    $types .= "ss";
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
                     $date_sql
                     $status_sql
                     $searchSql
                 GROUP BY t.kd_cust, c.nama_cust
             ) AS subquery";
$stmtCount = $conn->prepare($countSql);
if (!$stmtCount) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Statement error (count): " . $conn->error]);
    exit;
}
$countParams = $params;
$countTypes = $types;
if (!empty($countParams)) {
    $stmtCount->bind_param($countTypes, ...$countParams);
}
$stmtCount->execute();
$resultCount = $stmtCount->get_result();
$total_records = $resultCount->fetch_assoc()['total'] ?? 0;
$total_pages = ceil($total_records / $limit);
$stmtCount->close();
$dataSql = "SELECT 
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
                $searchSql
            GROUP BY t.kd_cust, c.nama_cust
            $orderBySql
            LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';
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
if (empty($data)) {
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
        "total_records" => (int) $total_records,
        "total_pages" => $total_pages,
        "offset" => $offset
    ]
];
http_response_code(200);
echo json_encode($response);
$conn->close();
?>