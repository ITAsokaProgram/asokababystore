<?php
require_once __DIR__ . "/../../../../config.php";
require_once __DIR__ . "/../../../../redis.php";
$logger = null;
if (php_sapi_name() === 'cli') {
    require_once __DIR__ . "/../../../../src/utils/Logger.php";
    $logger = new AppLogger('cron_paginated_members.log');
    $logger->info("Mulai cron job get_paginated_members.php.");
}
if (php_sapi_name() !== 'cli') {
    header("Content-Type:application/json");
}
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
if (php_sapi_name() === 'cli') {
    require_once __DIR__ . "/../../../../src/utils/Logger.php";
    $logger = new AppLogger('cron_member_age.log');
    $logger->info("Mulai cron job get_member_by_age.php.");
    
    if (isset($argv[1])) {
        parse_str($argv[1], $_GET);
    }
} 
else {
    header("Content-Type:application/json");
    
    require_once __DIR__ . "/../../../auth/middleware_login.php";
    
    $userInfo = authenticate_request();
}
$search = $_GET['search'] ?? '';
$sortBy = $_GET['sort_by'] ?? 'belanja';
$page = (int) ($_GET['page'] ?? 1);
$limit = (int) ($_GET['limit'] ?? 10);
$status = $_GET['status'] ?? 'all';
$filter_type = $_GET['filter_type'] ?? null;
$filter_preset = $_GET['filter'] ?? null;
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$key_start = ($filter_type === 'preset') ? '' : $start_date;
$key_end = ($filter_type === 'preset') ? '' : $end_date;
$cacheKey = "report:paginated_members:" .
    "status=$status" .
    "&search=$search" .
    "&sort=$sortBy" .
    "&page=$page" .
    "&limit=$limit" .
    "&type=$filter_type" .
    "&preset=$filter_preset" .
    "&start=$key_start" .
    "&end=$key_end";
try {
    $cachedData = $redis->get($cacheKey);
    if ($cachedData && php_sapi_name() !== 'cli') {
        http_response_code(200);
        echo $cachedData;
        $conn->close();
        exit;
    }
} catch (Exception $e) {
    if ($logger) {
        $logger->error("Redis cache get failed: " . $e->getMessage());
    }
}
if (php_sapi_name() === 'cli') {
    echo date('Y-m-d H:i:s') . " - CLI Mode: Force Refresh. Mengabaikan cache lama, mengambil data baru dari DB...\n";
}
$offset = ($page - 1) * $limit;
$params = [];
$types = "";
$date_sql = "";
$status_sql = "";
$searchSql = "";
if ($filter_type === 'custom' && $start_date && $end_date) {
    if ($start_date === $end_date) {
        $date_sql = " AND t.tgl_trans >= ? AND t.tgl_trans < ? ";
        $params[] = $start_date . " 00:00:00";
        $next_day = date('Y-m-d', strtotime($start_date . ' +1 day'));
        $params[] = $next_day . " 00:00:00";
        $types .= "ss";
    } else {
        $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
        $params[] = $start_date . " 00:00:00";
        $params[] = $end_date . " 23:59:59";
        $types .= "ss";
    }
} elseif ($filter_type === 'preset' && $filter_preset) {
    $end = date('Y-m-d');
    $start = '';
    switch ($filter_preset) {
        case 'kemarin':
            $start = date('Y-m-d', strtotime('-1 day'));
            $end = $start;
            $date_sql = " AND t.tgl_trans >= ? AND t.tgl_trans < ? ";
            $params[] = $start . " 00:00:00";
            $next_day = date('Y-m-d', strtotime($start . ' +1 day'));
            $params[] = $next_day . " 00:00:00";
            $types .= "ss";
            break;
        case '1minggu':
            $start = date('Y-m-d', strtotime('-7 days'));
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start . " 00:00:00";
            $params[] = $end . " 23:59:59";
            $types .= "ss";
            break;
        case '1bulan':
            $start = date('Y-m-d', strtotime('-1 month'));
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start . " 00:00:00";
            $params[] = $end . " 23:59:59";
            $types .= "ss";
            break;
        case '3bulan':
            $start = date('Y-m-d', strtotime('-3 months'));
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start . " 00:00:00";
            $params[] = $end . " 23:59:59";
            $types .= "ss";
            break;
        case '6bulan':
            $start = date('Y-m-d', strtotime('-6 months'));
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start . " 00:00:00";
            $params[] = $end . " 23:59:59";
            $types .= "ss";
            break;
        case '9bulan':
            $start = date('Y-m-d', strtotime('-9 months'));
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start . " 00:00:00";
            $params[] = $end . " 23:59:59";
            $types .= "ss";
            break;
        case '12bulan':
            $start = date('Y-m-d', strtotime('-12 months'));
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start . " 00:00:00";
            $params[] = $end . " 23:59:59";
            $types .= "ss";
            break;
        case 'semua':
            $date_sql = "";
            break;
        default:
            $start = date('Y-m-d', strtotime('-3 months'));
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start . " 00:00:00";
            $params[] = $end . " 23:59:59";
            $types .= "ss";
            break;
    }
} else {
    if ($start_date && $end_date) {
        if ($start_date === $end_date) {
            $date_sql = " AND t.tgl_trans >= ? AND t.tgl_trans < ? ";
            $params[] = $start_date . " 00:00:00";
            $next_day = date('Y-m-d', strtotime($start_date . ' +1 day'));
            $params[] = $next_day . " 00:00:00";
            $types .= "ss";
        } else {
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start_date . " 00:00:00";
            $params[] = $end_date . " 23:59:59";
            $types .= "ss";
        }
    } else {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $date_sql = " AND t.tgl_trans >= ? AND t.tgl_trans < ? ";
        $params[] = $yesterday . " 00:00:00";
        $next_day = date('Y-m-d', strtotime($yesterday . ' +1 day'));
        $params[] = $next_day . " 00:00:00";
        $types .= "ss";
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
    $searchSql = " AND (MATCH(c.nama_cust) AGAINST(? IN BOOLEAN MODE) OR t.kd_cust LIKE ?) ";
    $searchValueFTS = $search . "*";
    $searchValueLike = "%" . $search . "%";
    $params[] = $searchValueFTS;
    $params[] = $searchValueLike;
    $types .= "ss";
}
$orderBySql = " ORDER BY total_penjualan DESC ";
if ($sortBy === 'qty') {
    $orderBySql = " ORDER BY total_qty DESC ";
} elseif ($sortBy === 'nama') {
    $orderBySql = " ORDER BY c.nama_cust ASC ";
}
$dataSql = "SELECT 
                SQL_CALC_FOUND_ROWS -- OPTIMISASI: Tambahkan ini
                t.kd_cust,
                t.kd_store,
                c.nama_cust,
                ks.nm_alias AS cabang,
                SUM(t.qty) AS total_qty,
                SUM(t.qty * t.harga) AS total_penjualan
            FROM trans_b t
            LEFT JOIN customers c ON t.kd_cust = c.kd_cust
            LEFT JOIN kode_store ks ON ks.kd_store = t.kd_store
            WHERE 
                t.kd_cust IS NOT NULL
                AND t.kd_cust NOT IN ('', '898989', '89898989', '999999999')
                $date_sql
                $status_sql
                $searchSql
            GROUP BY t.kd_cust, t.kd_store, c.nama_cust, ks.nm_alias
            $orderBySql
            LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';
$stmtData = $conn->prepare($dataSql);
if (!$stmtData) {
    if ($logger) {
        $logger->error("Statement error (data): " . $conn->error);
    }
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
    }
    echo json_encode(["success" => false, "message" => "Statement error (data): " . $conn->error]);
    exit;
}
$stmtData->bind_param($types, ...$params);
$stmtData->execute();
$resultData = $stmtData->get_result();
$data = $resultData->fetch_all(MYSQLI_ASSOC);
$stmtData->close();
$total_records_result = $conn->query("SELECT FOUND_ROWS() AS total");
$total_records = $total_records_result->fetch_assoc()['total'] ?? 0;
$total_pages = ceil($total_records / $limit);
if (empty($data)) {
    if (php_sapi_name() !== 'cli') {
        http_response_code(200);
    }
    $response = [
        "success" => false,
        "message" => "Data tidak ditemukan"
    ];
    $jsonData = json_encode($response);
    try {
        $redis->setex($cacheKey, 3600, $jsonData);
    } catch (Exception $e) {
        if ($logger) {
            $logger->error("Redis cache set (no data) failed: " . $e->getMessage());
        }
    }
    if (php_sapi_name() !== 'cli') {
        echo $jsonData;
    } else {
        echo "Cache generated (no data) for $cacheKey.\n";
    }
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
$jsonData = json_encode($response);
try {
    $redis->setex($cacheKey, 72000, $jsonData);
} catch (Exception $e) {
    if ($logger) {
        $logger->error("Redis cache set (with data) failed: " . $e->getMessage());
    }
}
if (php_sapi_name() !== 'cli') {
    http_response_code(200);
    echo $jsonData;
} else {
    echo "Cache generated (with data) for $cacheKey. Expires in 20 hour.\n";
}
if ($logger) {
    $logger->info("Selesai cron job get_paginated_members.php. Cache generated for $cacheKey.");
}
$conn->close();
?>