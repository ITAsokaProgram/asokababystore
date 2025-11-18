<?php
require_once __DIR__ . "/../../../../config.php";
require_once __DIR__ . "/../../../../redis.php";
$logger = null;
if (php_sapi_name() === 'cli') {
    require_once __DIR__ . "/../../../../src/utils/Logger.php";
    $logger = new AppLogger('cron_get_top_member.log');
    $logger->info("Mulai cron job get_top_member.php.");
}
if (php_sapi_name() !== 'cli') {
    header("Content-Type:application/json");
}
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
if (php_sapi_name() === 'cli' && isset($argv[1])) {
    parse_str($argv[1], $_GET);
}
if (php_sapi_name() !== 'cli') {
    require_once __DIR__ . ("./../../../auth/middleware_login.php");
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? null;
    if (!$token) {
        http_response_code(401);
        echo json_encode(['status' => false, 'message' => 'Unauthorized: Token not provided']);
        exit;
    }
    $token = str_replace('Bearer ', '', $token);
    $user = verify_token($token);
    if (!$user) {
        http_response_code(401);
        echo json_encode(['status' => false, 'message' => 'Unauthorized: Invalid token']);
        exit;
    }
}
$status = $_GET['status'] ?? 'all';
$filter_type = $_GET['filter_type'] ?? null;
$filter_preset = $_GET['filter'] ?? null;
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$cacheKey = "report:top_member_sales:" .
    "status=$status" .
    "&type=$filter_type" .
    "&preset=$filter_preset" .
    "&start=$start_date" .
    "&end=$end_date";
try {
    $cachedData = $redis->get($cacheKey);
    if ($cachedData) {
        if (php_sapi_name() !== 'cli') {
            http_response_code(200);
            echo $cachedData;
        } else {
            echo "Cache found for $cacheKey. Skipping DB query.\n";
        }
        $conn->close();
        exit;
    }
} catch (Exception $e) {
    if ($logger) {
        $logger->error("Redis cache get failed: " . $e->getMessage());
    }
}
if (php_sapi_name() === 'cli') {
    echo "Cache not found for default view. Generating cache...\n";
}
$params = [];
$types = "";
$date_sql = "";
$status_sql = "";
$start_date_non = null;
$end_date_non = null;
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
    $start_date_non = $start_date;
    $end_date_non = $end_date;
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
            $start = '2000-01-01';
            break;
        default:
            $start = date('Y-m-d', strtotime('-3 months'));
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start;
            $params[] = $end;
            $types .= "ss";
            break;
    }
    $start_date_non = $start;
    $end_date_non = $end;
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
        $start_date_non = $start_date;
        $end_date_non = $end_date;
    } else {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $date_sql = " AND DATE(t.tgl_trans) = ? ";
        $params[] = $yesterday;
        $types .= "s";
        $start_date_non = $yesterday;
        $end_date_non = $yesterday;
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
$sql = "SELECT 
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
    AND t.kd_cust NOT IN ('', '898989', '#898989', '#999999999')
    $date_sql 
    $status_sql 
GROUP BY t.kd_cust, t.kd_store, c.nama_cust, ks.nm_alias
ORDER BY total_penjualan DESC
LIMIT 50";
$paramsNon = [];
$typesNon = "";
$date_sql_non = "";
if ($filter_preset === 'semua') {
    $date_sql_non = " AND t.tgl_trans BETWEEN ? AND ? ";
    $paramsNon[] = $start_date_non;
    $paramsNon[] = $end_date_non;
    $typesNon .= "ss";
} elseif ($start_date_non === $end_date_non) {
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
    if ($logger) {
        $logger->error("Statement error (member): " . $conn->error);
    }
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
    }
    echo json_encode(["success" => false, "message" => "Statement error (member): " . $conn->error]);
    exit;
}
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$top_member_by_sales = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$stmtNon = $conn->prepare($sqlNon);
if (!$stmtNon) {
    if ($logger) {
        $logger->error("Statement error (non-member): " . $conn->error);
    }
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
    }
    echo json_encode(["success" => false, "message" => "Statement error (non-member): " . $conn->error]);
    exit;
}
if (!empty($paramsNon)) {
    $stmtNon->bind_param($typesNon, ...$paramsNon);
}
$stmtNon->execute();
$resultNon = $stmtNon->get_result();
$top_member_by_sales_non = $resultNon->fetch_all(MYSQLI_ASSOC);
$stmtNon->close();
$jsonData = "";
if (count($top_member_by_sales) === 0 && count($top_member_by_sales_non) === 0) {
    if (php_sapi_name() !== 'cli') {
        http_response_code(200);
    }
    $response = [
        "success" => false,
        "message" => "Data tidak ditemukan untuk rentang tanggal ini"
    ];
    $jsonData = json_encode($response);
    try {
        $redis->setex($cacheKey, 900, $jsonData);
    } catch (Exception $e) {
        if ($logger) {
            $logger->error("Redis cache setex (no data) failed: " . $e->getMessage());
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
    "message" => "Data berhasil diambil",
    "total" => count($top_member_by_sales),
    "data" => $top_member_by_sales,
    "data_non" => $top_member_by_sales_non
];
$jsonData = json_encode($response);
try {
    $ttl = 900;
    if (
        ($filter_preset === 'kemarin') ||
        ($filter_type === 'custom' && $end_date && $end_date < date('Y-m-d'))
    ) {
        $ttl = 3600;
    }
    if (php_sapi_name() === 'cli') {
        $ttl = 84600;
        echo "Setting CLI cache TTL to $ttl seconds.\n";
    }
    $redis->setex($cacheKey, $ttl, $jsonData);
} catch (Exception $e) {
    if ($logger) {
        $logger->error("Redis cache setex (with data) failed: " . $e->getMessage());
    }
}
if (php_sapi_name() !== 'cli') {
    http_response_code(200);
    echo $jsonData;
} else {
    echo "Cache generated (with data) for $cacheKey. TTL: $ttl seconds.\n";
}
if ($logger) {
    $logger->info("Selesai cron job get_top_member.php. Cache generated for $cacheKey.");
}
$conn->close();
?>