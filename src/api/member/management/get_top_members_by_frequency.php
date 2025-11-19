<?php
require_once __DIR__ . "/../../../../config.php";
require_once __DIR__ . "/../../../../redis.php";
$logger = null;
if (php_sapi_name() === 'cli') {
    require_once __DIR__ . "/../../../../src/utils/Logger.php";
    $logger = new AppLogger('cron_top_freq.log');
    $logger->info("Mulai cron job get_top_members_by_frequency.php.");
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
$filter_type = $_GET['filter_type'] ?? 'preset';
$filter_preset = $_GET['filter'] ?? '3bulan';
$status = $_GET['status'] ?? 'active';
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$limit = (int) ($_GET['limit'] ?? 10);
$page = (int) ($_GET['page'] ?? 1);
$is_export = isset($_GET['export']) && $_GET['export'] === 'true';
$offset = ($page - 1) * $limit;
$cacheKey = "report:top_member_freq:" .
    "status=$status" .
    "&type=$filter_type" .
    "&preset=$filter_preset" .
    "&start=$start_date" .
    "&end=$end_date" .
    "&limit=$limit" .
    "&page=$page" .
    "&export=" . ($is_export ? '1' : '0');
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
    echo "Cache not found. Generating cache...\n";
}
/**
 * Helper untuk mendapatkan parameter filter tanggal.
 */
function getDateFilterParams($filter_type, $filter_preset, $start_date, $end_date, $table_alias = 't')
{
    $date_where_clause = "";
    $params = [];
    $types = "";
    if ($filter_type === 'custom' && $start_date && $end_date) {
        $end_date_with_time = $end_date . ' 23:59:59';
        $date_where_clause = " AND {$table_alias}.tgl_trans BETWEEN ? AND ?";
        $params[] = $start_date;
        $params[] = $end_date_with_time;
        $types = "ss";
    } else {
        $filter_map = [
            'kemarin' => '1 day',
            '1minggu' => '1 week',
            '1bulan' => '1 month',
            '3bulan' => '3 months',
            '6bulan' => '6 months',
            '9bulan' => '9 months',
            '12bulan' => '12 months'
        ];
        if ($filter_preset === 'semua') {
        } elseif ($filter_preset === 'kemarin') {
            $cutoff_date_filter = date('Y-m-d', strtotime("-1 day"));
            $date_where_clause = " AND DATE({$table_alias}.tgl_trans) = ?";
            $params[] = $cutoff_date_filter;
            $types = "s";
        } else {
            $interval = $filter_map[$filter_preset] ?? '3 months';
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
        $response = [
            'success' => true,
            'data' => [],
            'pagination' => [
                'total_records' => 0,
                'current_page' => 1,
                'limit' => $limit,
                'total_pages' => 0
            ]
        ];
        $jsonData = json_encode($response);
        try {
            $redis->set($cacheKey, $jsonData);
        } catch (Exception $e) {
        }
        if (php_sapi_name() !== 'cli')
            echo $jsonData;
        else
            echo "Empty inactive data cached.\n";
        $conn->close();
        exit();
    }
    $dateFilter = getDateFilterParams($filter_type, $filter_preset, $start_date, $end_date, 't');
    $params = [];
    $types = "";
    $where_clauses = [];
    if (!empty($dateFilter['sql_clause'])) {
        $where_clauses[] = preg_replace('/^\s*AND\s/i', '', $dateFilter['sql_clause']);
    }
    $params = array_merge($params, $dateFilter['params']);
    $types .= $dateFilter['types'];
    $cutoff_active = date('Y-m-d 00:00:00', strtotime("-3 months"));
    if ($status === 'active') {
        $where_clauses[] = "(c.Last_Trans >= ?)";
        $params[] = $cutoff_active;
        $types .= "s";
    } else {
        $where_clauses[] = "(c.Last_Trans < ? OR c.Last_Trans IS NULL)";
        $params[] = $cutoff_active;
        $types .= "s";
    }
    $where_clauses[] = "t.kd_cust IS NOT NULL";
    $where_clauses[] = "t.kd_cust NOT IN ('', '898989', '89898989', '999999999')";
    $where_clauses[] = "c.nama_cust IS NOT NULL AND c.nama_cust != ''";
    $sql_where = implode(" AND ", $where_clauses);
    $count_sql = "
        SELECT COUNT(*) AS total_records
        FROM (
            SELECT 1
            FROM trans_b t
            INNER JOIN customers c ON t.kd_cust = c.kd_cust
            WHERE $sql_where
            GROUP BY c.nama_cust, t.kd_cust
        ) AS unique_customers
    ";
    $stmt_count = $conn->prepare($count_sql);
    if ($stmt_count === false)
        throw new Exception("Database prepare failed (count): " . $conn->error);
    if (!empty($params)) {
        $stmt_count->bind_param($types, ...$params);
    }
    if (!$stmt_count->execute())
        throw new Exception("Gagal eksekusi query (count): " . $stmt_count->error);
    $count_result = $stmt_count->get_result();
    $row_count = $count_result->fetch_assoc();
    $total_records = $row_count ? (int) $row_count['total_records'] : 0;
    $total_pages = $total_records > 0 ? ceil($total_records / $limit) : 0;
    $stmt_count->close();
    $sql = "
        SELECT 
            c.nama_cust,
            t.kd_cust,
            COUNT(DISTINCT t.no_bon) AS total_transactions,
            COALESCE(points_summary.total_poin_customer, 0) AS total_poin_customer
        FROM 
            trans_b t
        INNER JOIN 
            customers c ON t.kd_cust = c.kd_cust
        LEFT JOIN (
            SELECT 
                kd_cust, 
                SUM(total_point) AS total_poin_customer
            FROM (
                SELECT kd_cust, COALESCE(jum_point, 0) AS total_point FROM point_trans WHERE kd_cust IS NOT NULL AND kd_cust != ''
                UNION ALL
                SELECT kd_cust, COALESCE(jum_point, 0) AS total_point FROM point_manual WHERE kd_cust IS NOT NULL AND kd_cust != ''
                UNION ALL
                SELECT kd_cust, COALESCE(point_1, 0) AS total_point FROM point_kasir WHERE kd_cust IS NOT NULL AND kd_cust != ''
            ) AS all_points
            GROUP BY kd_cust
        ) AS points_summary ON t.kd_cust = points_summary.kd_cust
        WHERE 
            $sql_where
        GROUP BY 
            c.nama_cust, t.kd_cust, points_summary.total_poin_customer
        ORDER BY 
            total_transactions DESC
    ";
    if (!$is_export) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
    }
    $stmt = $conn->prepare($sql);
    if ($stmt === false)
        throw new Exception("Database prepare failed (data): " . $conn->error);
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute())
        throw new Exception("Gagal eksekusi query (data): " . $stmt->error);
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'nama_cust' => $row['nama_cust'],
            'kd_cust' => $row['kd_cust'],
            'total_transactions' => (int) $row['total_transactions'],
            'total_poin_customer' => (int) $row['total_poin_customer']
        ];
    }
    $stmt->close();
    $response = [
        'success' => true,
        'data' => $data,
        'pagination' => [
            'total_records' => $total_records,
            'current_page' => $page,
            'limit' => $limit,
            'total_pages' => $total_pages
        ]
    ];
    $jsonData = json_encode($response);
    try {
        $redis->set($cacheKey, $jsonData);
        if ($is_export) {
            $redis->expire($cacheKey, 300);
        }
    } catch (Exception $e) {
        if ($logger) {
            $logger->error("Redis cache set failed: " . $e->getMessage());
        }
    }
    if (php_sapi_name() !== 'cli') {
        http_response_code(200);
        echo $jsonData;
    } else {
        echo "Cache generated for $cacheKey. No TTL set.\n";
    }
    if ($logger) {
        $logger->info("Selesai cron job get_top_members_by_frequency.php.");
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
        echo "Error: " . $t->getMessage() . "\n";
    }
}
$conn->close();
?>