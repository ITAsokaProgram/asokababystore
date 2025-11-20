<?php
require_once __DIR__ . "/../../../../config.php";
require_once __DIR__ . "/../../../../redis.php";
$logger = null;
if (php_sapi_name() === 'cli') {
    require_once __DIR__ . "/../../../../src/utils/Logger.php";
    $logger = new AppLogger('cron_top_member_product_pairs.log');
    $logger->info("Mulai cron job get_top_member_product_pairs.php.");
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
        echo json_encode(['success' => false, 'message' => 'Unauthorized: Token not provided']);
        exit;
    }
    $token = str_replace('Bearer ', '', $token);
    $user = verify_token($token);
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized: Invalid token']);
        exit;
    }
}
$status = $_GET['status'] ?? 'active';
$filter_type = $_GET['filter_type'] ?? 'preset';
$filter_preset = $_GET['filter'] ?? '3bulan';
$limit = (int) ($_GET['limit'] ?? 10);
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$cacheKey = "report:top_member_pairs:" .
    "status=$status" .
    "&type=$filter_type" .
    "&preset=$filter_preset" .
    "&start=$start_date" .
    "&end=$end_date" .
    "&limit=$limit";
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
function getDateFilterParams($get_params, $table_alias = 't')
{
    $date_where_clause = "";
    $params = [];
    $types = "";
    $filter_display = "";
    $filter_type = $get_params['filter_type'] ?? 'preset';
    if ($filter_type === 'custom' && !empty($get_params['start_date']) && !empty($get_params['end_date'])) {
        $start_date = $get_params['start_date'];
        $end_date = $get_params['end_date'];
        $end_date_with_time = $end_date . ' 23:59:59';
        $date_where_clause = " AND {$table_alias}.tgl_trans BETWEEN ? AND ?";
        $params[] = $start_date;
        $params[] = $end_date_with_time;
        $types = "ss";
    } else {
        $filter = $get_params['filter'] ?? '3bulan';
        $filter_map = [
            'kemarin' => '1 day',
            '1minggu' => '1 week',
            '1bulan' => '1 month',
            '3bulan' => '3 months',
            '6bulan' => '6 months',
            '9bulan' => '9 months',
            '12bulan' => '12 months'
        ];
        if ($filter === 'semua') {
        } elseif ($filter === 'kemarin') {
            $cutoff_date_filter = date('Y-m-d', strtotime("-1 day"));
            $date_where_clause = " AND DATE({$table_alias}.tgl_trans) = ?";
            $params[] = $cutoff_date_filter;
            $types = "s";
        } else {
            $interval = $filter_map[$filter] ?? '3 months';
            $cutoff_date_filter = date('Y-m-d 00:00:00', strtotime("-$interval"));
            $date_where_clause = " AND {$table_alias}.tgl_trans >= ?";
            $params[] = $cutoff_date_filter;
            $types = "s";
        }
    }
    return [
        'sql_clause' => $date_where_clause,
        'params' => $params,
        'types' => $types
    ];
}
try {
    $isFilter3MonthsOrLess = false;
    if ($filter_type === 'preset') {
        $isFilter3MonthsOrLess = in_array($filter_preset, ['kemarin', '1minggu', '1bulan', '3bulan']);
    }
    if ($status === 'inactive' && $isFilter3MonthsOrLess) {
        $responseEmpty = json_encode(['success' => true, 'data' => []]);
        try {
            $redis->set($cacheKey, $responseEmpty);
        } catch (Exception $e) {
            if ($logger)
                $logger->error("Redis set empty failed: " . $e->getMessage());
        }
        if (php_sapi_name() !== 'cli') {
            echo $responseEmpty;
        } else {
            echo date('Y-m-d H:i:s') . " - Condition met (Inactive < 3mo), returning empty.\n";
        }
        $conn->close();
        exit();
    }
    $dateFilter = getDateFilterParams($_GET, 't');
    $params = [];
    $types = "";
    $where_clauses = [];
    if (!empty($dateFilter['sql_clause'])) {
        $where_clauses[] = preg_replace('/^\s*AND\s/i', '', $dateFilter['sql_clause']);
    }
    $params = array_merge($params, $dateFilter['params']);
    $types .= $dateFilter['types'];
    $cutoff_date_status = date('Y-m-d 00:00:00', strtotime("-3 months"));
    if ($status === 'active') {
        $where_clauses[] = "(c.Last_Trans >= ?)";
        $params[] = $cutoff_date_status;
        $types .= "s";
    } else {
        $where_clauses[] = "(c.Last_Trans < ? OR c.Last_Trans IS NULL)";
        $params[] = $cutoff_date_status;
        $types .= "s";
    }
    $where_clauses[] = "t.kd_cust IS NOT NULL";
    $where_clauses[] = "t.kd_cust NOT IN ('', '898989', '89898989', '999999999')";
    $sql_where = implode(" AND ", $where_clauses);
    $sql = "
        SELECT
            c.nama_cust,
            t.kd_cust,
            t.descp,
            t.plu,
            SUM(t.qty) AS total_item_qty
        FROM
            trans_b t
        INNER JOIN
            customers c ON t.kd_cust = c.kd_cust
        WHERE
            $sql_where
        GROUP BY
            t.kd_cust, t.plu, c.nama_cust, t.descp
        ORDER BY
            total_item_qty DESC
        LIMIT ?
    ";
    $params[] = $limit;
    $types .= "i";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database prepare failed: " . $conn->error);
    }
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'nama_cust' => $row['nama_cust'],
            'kd_cust' => $row['kd_cust'],
            'descp' => $row['descp'],
            'plu' => $row['plu'],
            'total_item_qty' => (int) $row['total_item_qty']
        ];
    }
    $stmt->close();
    $response = [
        'success' => true,
        'data' => $data
    ];
    $jsonData = json_encode($response);
    try {
        $redis->set($cacheKey, $jsonData);
    } catch (Exception $e) {
        if ($logger) {
            $logger->error("Redis cache set failed: " . $e->getMessage());
        }
    }
    if (php_sapi_name() !== 'cli') {
        echo $jsonData;
    } else {
        echo date('Y-m-d H:i:s') . " - Cache generated for $cacheKey. No TTL set.\n";
    }
    if ($logger) {
        $logger->info("Selesai cron job get_top_member_product_pairs.php. Cache generated.");
    }
} catch (Throwable $t) {
    if ($logger) {
        $logger->critical("FATAL ERROR: " . $t->getMessage());
    }
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => "Terjadi kesalahan: " . $t->getMessage()
        ]);
    } else {
        echo date('Y-m-d H:i:s') . " - Error: " . $t->getMessage() . "\n";
    }
}
$conn->close();
?>