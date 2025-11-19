<?php
require_once __DIR__ . "/../../../../config.php";
require_once __DIR__ . "/../../../../redis.php";
$logger = null;
if (php_sapi_name() === 'cli') {
    require_once __DIR__ . "/../../../../src/utils/Logger.php";
    $logger = new AppLogger('cron_member_age.log');
    $logger->info("Mulai cron job get_member_by_age.php.");
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
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$cacheKey = "report:member_age:" .
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
            echo date('Y-m-d H:i:s') . " - Cache found for $cacheKey. Skipping DB query.\n";
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
    echo date('Y-m-d H:i:s') . " - Cache not found. Generating cache...\n";
}
$params = [];
$types = "";
$date_where_clause = "";
$table_alias = 't';
if ($filter_type === 'custom' && $start_date && $end_date) {
    if ($start_date === $end_date) {
        $date_where_clause = " AND {$table_alias}.tgl_trans >= ? AND {$table_alias}.tgl_trans < ? ";
        $params[] = $start_date . " 00:00:00";
        $next_day = date('Y-m-d', strtotime($start_date . ' +1 day'));
        $params[] = $next_day . " 00:00:00";
        $types .= "ss";
    } else {
        $date_where_clause = " AND {$table_alias}.tgl_trans BETWEEN ? AND ? ";
        $params[] = $start_date . " 00:00:00";
        $params[] = $end_date . " 23:59:59";
        $types .= "ss";
    }
} elseif ($filter_type === 'preset') {
    $end = date('Y-m-d');
    switch ($filter_preset) {
        case 'kemarin':
            $start = date('Y-m-d', strtotime('-1 day'));
            $date_where_clause = " AND {$table_alias}.tgl_trans >= ? AND {$table_alias}.tgl_trans < ? ";
            $params[] = $start . " 00:00:00";
            $next_day = date('Y-m-d', strtotime($start . ' +1 day'));
            $params[] = $next_day . " 00:00:00";
            $types .= "ss";
            break;
        case '1minggu':
            $start = date('Y-m-d', strtotime('-7 days'));
            $date_where_clause = " AND {$table_alias}.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start . " 00:00:00";
            $params[] = $end . " 23:59:59";
            $types .= "ss";
            break;
        case '1bulan':
            $start = date('Y-m-d', strtotime('-1 month'));
            $date_where_clause = " AND {$table_alias}.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start . " 00:00:00";
            $params[] = $end . " 23:59:59";
            $types .= "ss";
            break;
        case '3bulan':
            $start = date('Y-m-d', strtotime('-3 months'));
            $date_where_clause = " AND {$table_alias}.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start . " 00:00:00";
            $params[] = $end . " 23:59:59";
            $types .= "ss";
            break;
        case '6bulan':
            $start = date('Y-m-d', strtotime('-6 months'));
            $date_where_clause = " AND {$table_alias}.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start . " 00:00:00";
            $params[] = $end . " 23:59:59";
            $types .= "ss";
            break;
        case '9bulan':
            $start = date('Y-m-d', strtotime('-9 months'));
            $date_where_clause = " AND {$table_alias}.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start . " 00:00:00";
            $params[] = $end . " 23:59:59";
            $types .= "ss";
            break;
        case '12bulan':
            $start = date('Y-m-d', strtotime('-12 months'));
            $date_where_clause = " AND {$table_alias}.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start . " 00:00:00";
            $params[] = $end . " 23:59:59";
            $types .= "ss";
            break;
        case 'semua':
            $date_where_clause = "";
            break;
        default:
            $start = date('Y-m-d', strtotime('-3 months'));
            $date_where_clause = " AND {$table_alias}.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start . " 00:00:00";
            $params[] = $end . " 23:59:59";
            $types .= "ss";
            break;
    }
}
$cutoff_date_status = date('Y-m-d 00:00:00', strtotime("-3 months"));
$status_params = [];
$status_types = "";
$where_clause = "";
if ($status === 'active') {
    $where_clause = " WHERE c.Last_Trans >= ?";
    $status_params[] = $cutoff_date_status;
    $status_types .= "s";
} else {
    $where_clause = " WHERE (c.Last_Trans < ? OR c.Last_Trans IS NULL)";
    $status_params[] = $cutoff_date_status;
    $status_types .= "s";
}
$isFilter3MonthsOrLess = false;
if ($filter_type === 'preset') {
    $isFilter3MonthsOrLess = in_array($filter_preset, ['kemarin', '1minggu', '1bulan', '3bulan']);
}
$age_case_sql = "
    CASE
        WHEN c.tgl_lahir IS NULL OR YEAR(c.tgl_lahir) > 2020 THEN '-'
        WHEN TIMESTAMPDIFF(YEAR, c.tgl_lahir, CURDATE()) <= 17 THEN '<= 17'
        WHEN TIMESTAMPDIFF(YEAR, c.tgl_lahir, CURDATE()) BETWEEN 18 AND 20 THEN '18-20'
        WHEN TIMESTAMPDIFF(YEAR, c.tgl_lahir, CURDATE()) BETWEEN 21 AND 25 THEN '21-25'
        WHEN TIMESTAMPDIFF(YEAR, c.tgl_lahir, CURDATE()) BETWEEN 26 AND 30 THEN '26-30'
        WHEN TIMESTAMPDIFF(YEAR, c.tgl_lahir, CURDATE()) BETWEEN 31 AND 35 THEN '31-35'
        WHEN TIMESTAMPDIFF(YEAR, c.tgl_lahir, CURDATE()) BETWEEN 36 AND 45 THEN '36-45'
        WHEN TIMESTAMPDIFF(YEAR, c.tgl_lahir, CURDATE()) BETWEEN 46 AND 59 THEN '46-59'
        WHEN TIMESTAMPDIFF(YEAR, c.tgl_lahir, CURDATE()) >= 60 THEN '>= 60'
        ELSE '-'
    END
";
$exclude_clause = "
    AND UPPER(t.descp) NOT LIKE '%MEMBER BY PHONE%'
    AND UPPER(t.descp) NOT LIKE '%TAS ASOKA BIRU%'
";
$sql = "
    SELECT
        agc.age_group,
        agc.count,
        tp.top_product_descp,
        tp.top_product_qty
    FROM
    (
        SELECT 
            ($age_case_sql) AS age_group,
            COUNT(DISTINCT c.kd_cust) AS count
        FROM customers c
        INNER JOIN trans_b t ON c.kd_cust = t.kd_cust
        $where_clause
        $date_where_clause
        GROUP BY age_group
        HAVING age_group != '-'
    ) AS agc
    LEFT JOIN
    (
        SELECT
            age_group,
            descp AS top_product_descp,
            total_qty AS top_product_qty
        FROM
        (
            SELECT
                @rn := IF(@current_group = age_group, @rn + 1, 1) AS rn,
                @current_group := age_group AS age_group,
                descp,
                total_qty
            FROM
            (
                SELECT
                    cag.age_group,
                    t.descp,
                    SUM(t.qty) AS total_qty
                FROM trans_b t
                INNER JOIN (
                    SELECT 
                        kd_cust,
                        ($age_case_sql) AS age_group
                    FROM customers c
                    $where_clause
                ) AS cag ON t.kd_cust = cag.kd_cust
                CROSS JOIN (SELECT @rn := 0, @current_group := '') AS init_vars
                WHERE 1=1 
                    $date_where_clause
                    $exclude_clause
                GROUP BY cag.age_group, t.descp
                ORDER BY cag.age_group, total_qty DESC
            ) AS ProductSums
        ) AS RankedProducts
        WHERE rn = 1
    ) AS tp ON agc.age_group = tp.age_group
    ORDER BY 
        CASE agc.age_group
            WHEN '<= 17' THEN 1
            WHEN '18-20' THEN 2
            WHEN '21-25' THEN 3
            WHEN '26-30' THEN 4
            WHEN '31-35' THEN 5
            WHEN '36-45' THEN 6
            WHEN '46-59' THEN 7
            WHEN '>= 60' THEN 8
            WHEN '-' THEN 9
            ELSE 10
        END
";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    if ($logger) {
        $logger->error("Statement error (member age): " . $conn->error);
    }
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
    }
    echo json_encode(["success" => false, "message" => "Statement error: " . $conn->error]);
    exit;
}
$final_params = [];
$final_types = "";
foreach ($status_params as $p) {
    $final_params[] = $p;
}
$final_types .= $status_types;
foreach ($params as $p) {
    $final_params[] = $p;
}
$final_types .= $types;
foreach ($status_params as $p) {
    $final_params[] = $p;
}
$final_types .= $status_types;
foreach ($params as $p) {
    $final_params[] = $p;
}
$final_types .= $types;
if (!empty($final_params)) {
    $stmt->bind_param($final_types, ...$final_params);
}
$stmt->execute();
$result = $stmt->get_result();
$raw_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$processed_data = [];
foreach ($raw_data as $row) {
    $processed_data[] = [
        'age_group' => $row['age_group'],
        'count' => (int) $row['count'],
        'top_product_descp' => ($status === 'inactive' && $isFilter3MonthsOrLess) ? null : $row['top_product_descp'],
        'top_product_qty' => ($status === 'inactive' && $isFilter3MonthsOrLess) ? null : ($row['top_product_qty'] ? (int) $row['top_product_qty'] : null)
    ];
}
$response = [
    'success' => true,
    'data' => $processed_data
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
    http_response_code(200);
    echo $jsonData;
} else {
    echo date('Y-m-d H:i:s') . " - Cache generated for $cacheKey. No TTL set.\n";
}
if ($logger) {
    $logger->info("Selesai cron job get_member_by_age.php. Cache generated.");
}
$conn->close();
?>