<?php
session_start();
ini_set('display_errors', 0);
require_once __DIR__ . "/../../../../config.php";
require_once __DIR__ . "/../../../../redis.php";
require_once __DIR__ . '/../../../utils/Logger.php';
$logger = null;
if (php_sapi_name() === 'cli') {
    $logger = new AppLogger('cron_top_members_filter.log');
    $logger->info("Mulai cron job get_top_members_by_filter.php.");
} else {
    $logger = new AppLogger('top_members_filter.log');
    header('Content-Type: application/json');
}
try {
    require_once __DIR__ . '/../../../../aa_kon_sett.php';
} catch (Throwable $t) {
    if ($logger)
        $logger->critical("Gagal memuat file koneksi: " . $t->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: Gagal memuat file.']);
    exit();
}
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
if (!isset($conn) || !$conn instanceof mysqli) {
    if ($logger)
        $logger->critical("Objek koneksi database (\$conn) tidak ada.");
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Koneksi database tidak terinisialisasi.']);
    exit();
}
$status = $_GET['status'] ?? 'active';
$filter_type = $_GET['filter_type'] ?? 'preset';
$filter_preset = $_GET['filter'] ?? '3bulan';
$limit = (int) ($_GET['limit'] ?? 10);
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$cacheKey = "report:top_members_filter:" .
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
        $filter_display = htmlspecialchars($start_date) . " s/d " . htmlspecialchars($end_date);
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
        $display_map = [
            'kemarin' => 'Kemarin',
            '1minggu' => '1 Minggu Terakhir',
            '1bulan' => '1 Bulan Terakhir',
            '3bulan' => '3 Bulan Terakhir',
            '6bulan' => '6 Bulan Terakhir',
            '9bulan' => '9 Bulan Terakhir',
            '12bulan' => '1 Tahun Terakhir',
            'semua' => 'Semua Waktu'
        ];
        $filter_display = $display_map[$filter] ?? '3 Bulan Terakhir';
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
        'types' => $types,
        'display' => $filter_display
    ];
}
try {
    if ($status === 'inactive' && $filter_type === 'preset') {
        $isFilter3MonthsOrLess = in_array($filter_preset, ['kemarin', '1minggu', '1bulan', '3bulan']);
        if ($isFilter3MonthsOrLess) {
            $responseEmpty = json_encode(['success' => true, 'data' => []]);
            try {
                $redis->set($cacheKey, $responseEmpty);
            } catch (Exception $e) {
            }
            if (php_sapi_name() !== 'cli')
                echo $responseEmpty;
            $conn->close();
            exit();
        }
    }
    $params = [];
    $types = "";
    $where_clauses = [];
    $dateFilter = getDateFilterParams($_GET, 't');
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
    $sql_where = implode(" AND ", $where_clauses);
    $sql = "
        SELECT
            c.nama_cust,
            t.kd_cust,
            SUM(t.qty * t.harga) AS total_spent
        FROM
            trans_b t
        INNER JOIN
            customers c ON t.kd_cust = c.kd_cust
        WHERE
            $sql_where
        GROUP BY
            c.nama_cust, t.kd_cust
        ORDER BY
            total_spent DESC
        LIMIT ?
    ";
    $params[] = $limit;
    $types .= "i";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Database prepare failed (data): " . $conn->error);
    }
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        throw new Exception("Gagal eksekusi query (data): " . $stmt->error);
    }
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'nama_cust' => $row['nama_cust'],
            'kd_cust' => $row['kd_cust'],
            'total_spent' => (float) $row['total_spent']
        ];
    }
    $stmt->close();
    $conn->close();
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
    if ($logger && php_sapi_name() === 'cli') {
        $logger->info("Selesai cron job get_top_members_by_filter.php.");
    }
} catch (Throwable $t) {
    if ($logger) {
        $logger->critical("FATAL ERROR: " . $t->getMessage(), [
            'file' => $t->getFile(),
            'line' => $t->getLine(),
            'params' => $_GET
        ]);
    }
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
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
?>