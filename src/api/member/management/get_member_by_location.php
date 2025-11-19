<?php
require_once __DIR__ . "/../../../../config.php";
require_once __DIR__ . "/../../../../redis.php";

// 1. Setup Logger CLI
$logger = null;
if (php_sapi_name() === 'cli') {
    require_once __DIR__ . "/../../../../src/utils/Logger.php";
    $logger = new AppLogger('cron_member_location.log');
    $logger->info("Mulai cron job get_member_by_location.php.");
}

// 2. Header JSON (Non-CLI)
if (php_sapi_name() !== 'cli') {
    header("Content-Type:application/json");
}

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

// 3. Parse Params CLI
if (php_sapi_name() === 'cli' && isset($argv[1])) {
    parse_str($argv[1], $_GET);
}

// 4. Auth (Non-CLI)
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

// 5. Parameter
$status = $_GET['status'] ?? 'active';
$filter_type = $_GET['filter_type'] ?? 'preset';
$filter_preset = $_GET['filter'] ?? '3bulan';
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

$level = $_GET['level'] ?? 'city';
$selected_city = $_GET['city'] ?? null;
$selected_district = $_GET['district'] ?? null;
$limit_param = $_GET['limit'] ?? 'default';

// 6. Redis Cache Check
$cacheKey = "report:member_loc:" .
    "status=$status" .
    "&type=$filter_type" .
    "&preset=$filter_preset" .
    "&start=$start_date" .
    "&end=$end_date" .
    "&lvl=$level" .
    "&city=$selected_city" .
    "&dist=$selected_district" .
    "&limit=$limit_param";

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

// --- LOGIKA UTAMA ---

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
    $limit_clause = "";
    if ($limit_param === 'default') {
        $limit_clause = " LIMIT 20 ";
    }

    $params = [];
    $types = "";
    $where_clause = "";

    // Logika status
    $cutoff_date_status = date('Y-m-d 00:00:00', strtotime("-3 months"));
    if ($status === 'active') {
        $where_clause = " WHERE c.Last_Trans >= ?";
        $params[] = $cutoff_date_status;
        $types .= "s";
    } else {
        $where_clause = " WHERE (c.Last_Trans < ? OR c.Last_Trans IS NULL)";
        $params[] = $cutoff_date_status;
        $types .= "s";
    }

    // Logika $isFilter3MonthsOrLess
    $isFilter3MonthsOrLess = false;
    if ($filter_type === 'preset') {
        $isFilter3MonthsOrLess = in_array($filter_preset, ['kemarin', '1minggu', '1bulan', '3bulan']);
    }

    // Date Filter
    $dateFilter = getDateFilterParams($filter_type, $filter_preset, $start_date, $end_date, 't');
    $date_where_clause = $dateFilter['sql_clause'];
    $params_for_products = $dateFilter['params'];
    $types_for_products = $dateFilter['types'];

    $location_field = "";
    $city_field_logic = "IF(c.Kota IS NULL OR c.Kota = '', 'Customer belum input', c.Kota)";
    $district_field_logic = "IF(c.Kec IS NULL OR c.Kec = '', 'Customer belum input', c.Kec)";
    $subdistrict_field_logic = "IF(c.Kel IS NULL OR c.Kel = '', 'Customer belum input', c.Kel)";

    switch ($level) {
        case 'district':
            $location_field = $district_field_logic;
            $where_clause .= " AND $city_field_logic = ?";
            $params[] = $selected_city;
            $types .= "s";
            break;
        case 'subdistrict':
            $location_field = $subdistrict_field_logic;
            $where_clause .= " AND $city_field_logic = ? AND $district_field_logic = ?";
            $params[] = $selected_city;
            $params[] = $selected_district;
            $types .= "ss";
            break;
        case 'city':
        default:
            $location_field = $city_field_logic;
            break;
    }

    $exclude_clause = "
        AND UPPER(t.descp) NOT LIKE '%MEMBER BY PHONE%'
        AND UPPER(t.descp) NOT LIKE '%TAS ASOKA BIRU%'
    ";

    $sql = "
        SELECT 
            loc.location_name,
            loc.count,
            tp.top_product_descp,
            tp.top_product_qty
        FROM 
        (
            SELECT  
                $location_field AS location_name,
                COUNT(DISTINCT c.kd_cust) AS count
            FROM customers c
            INNER JOIN trans_b t ON c.kd_cust = t.kd_cust
            $where_clause 
            $date_where_clause
            GROUP BY location_name
            HAVING location_name NOT IN ('-', 'Customer belum input')
        ) AS loc
        LEFT JOIN
        (
            SELECT 
                location_name, 
                descp AS top_product_descp, 
                total_qty AS top_product_qty
            FROM 
            (
                SELECT 
                    @rn := IF(@current_group = location_name, @rn + 1, 1) AS rn,
                    @current_group := location_name AS location_name,
                    descp,
                    total_qty
                FROM 
                (
                    SELECT 
                        cloc.location_name,
                        t.descp,
                        SUM(t.qty) AS total_qty
                    FROM trans_b t
                    INNER JOIN (
                        SELECT  
                            kd_cust,
                            $location_field AS location_name
                        FROM customers c
                        $where_clause 
                    ) AS cloc ON t.kd_cust = cloc.kd_cust
                    CROSS JOIN (SELECT @rn := 0, @current_group := '') AS init_vars
                    WHERE 1=1 
                        $date_where_clause 
                        $exclude_clause
                    GROUP BY cloc.location_name, t.descp
                    ORDER BY cloc.location_name, total_qty DESC
                ) AS ProductSums
            ) AS RankedProducts
            WHERE rn = 1
        ) AS tp ON loc.location_name = tp.location_name
        ORDER BY loc.count DESC
        $limit_clause
    ";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Database prepare failed (active): " . $conn->error);
    }

    $all_params = array_merge($params, $params_for_products, $params, $params_for_products);
    $all_types = $types . $types_for_products . $types . $types_for_products;

    if (!empty($all_params)) {
        $bind_params = [$all_types];
        foreach ($all_params as $key => $value) {
            $bind_params[] = &$all_params[$key];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_params);
    }

    if (!$stmt->execute()) {
        throw new Exception("Gagal eksekusi query: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'location_name' => $row['location_name'],
            'count' => (int) $row['count'],
            'top_product_descp' => ($status === 'inactive' && $isFilter3MonthsOrLess) ? null : $row['top_product_descp'],
            'top_product_qty' => ($status === 'inactive' && $isFilter3MonthsOrLess) ? null : ($row['top_product_qty'] ? (int) $row['top_product_qty'] : null)
        ];
    }
    $stmt->close();

    $response = [
        'success' => true,
        'data' => $data
    ];

    $jsonData = json_encode($response);

    // 7. Simpan Redis
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
        echo "Cache generated for $cacheKey. No TTL set.\n";
    }

    if ($logger) {
        $logger->info("Selesai cron job get_member_by_location.php.");
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