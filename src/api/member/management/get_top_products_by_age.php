<?php
session_start();
ini_set('display_errors', 0);
require_once __DIR__ . '/../../../utils/Logger.php';
require_once __DIR__ . '/../../../auth/middleware_login.php';
$logger = new AppLogger('top_product_by_age.log');
try {
    require_once __DIR__ . '/../../../../aa_kon_sett.php';
} catch (Throwable $t) {
    $logger->critical("Gagal memuat file koneksi: " . $t->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: Gagal memuat file.']);
    exit();
}
header('Content-Type: application/json');
$user_kode = 'UNKNOWN';
try {
    $authHeader = null;
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        }
    }
    if ($authHeader === null && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    }
    if ($authHeader === null && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    if ($authHeader === null || !preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => "Token tidak ditemukan atau format salah."]);
        exit;
    }
    $token = $matches[1];
    $decoded = verify_token($token);
    $isTokenValidAdmin = is_object($decoded) && isset($decoded->kode);
    if (!$isTokenValidAdmin) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Token tidak valid.']);
        exit;
    }
    $user_kode = $decoded->kode;
} catch (Exception $e) {
    http_response_code(500);
    $logger->error("Token validation error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Token validation error: ' . $e->getMessage()]);
    exit;
}
if (!isset($conn) || !$conn instanceof mysqli) {
    $logger->critical("Objek koneksi database (\$conn) tidak ada.");
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Koneksi database tidak terinisialisasi.']);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $logger->warning("Method Not Allowed: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit();
}

// --- FUNGSI HELPER ---
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
            // Tidak ada klausa where tanggal
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
// --- AKHIR FUNGSI HELPER ---

try {
    $limit = (int) ($_GET['limit'] ?? 10);
    $page = (int) ($_GET['page'] ?? 1);
    $offset = ($page - 1) * $limit;
    // HAPUS $filter = $_GET['filter'] ?? 'semua';
    $age_group_param = $_GET['age_group'] ?? '';
    $status = $_GET['status'] ?? null;

    if (empty($age_group_param)) {
        throw new Exception("Parameter 'age_group' tidak boleh kosong.");
    }

    // HAPUS $filter_map
    // HAPUS $interval

    // Logika exit cepat untuk inactive
    $filter_type = $_GET['filter_type'] ?? 'preset';
    $filter = $_GET['filter'] ?? '3bulan';
    if ($status === 'inactive' && $filter_type === 'preset') {
        $isFilter3MonthsOrLess = in_array($filter, ['kemarin', '1minggu', '1bulan', '3bulan']);
        if ($isFilter3MonthsOrLess) {
            echo json_encode([
                'success' => true,
                'data' => [],
                'pagination' => [
                    'total_records' => 0,
                    'current_page' => $page,
                    'limit' => $limit,
                    'total_pages' => 0
                ]
            ]);
            $conn->close();
            exit();
        }
    }
    // HAPUS BLOK if ($status === 'inactive') LAMA

    $params_count = [$age_group_param];
    $types_count = "s";
    $params_data = [$age_group_param];
    $types_data = "s";

    $status_where_clause = "";
    if ($status) {
        $cutoff_active = date('Y-m-d 00:00:00', strtotime("-3 months"));
        if ($status === 'active') {
            $status_where_clause = " AND (c.Last_Trans >= ?)";
            $params_count[] = $cutoff_active;
            $types_count .= "s";
            $params_data[] = $cutoff_active;
            $types_data .= "s";
        } elseif ($status === 'inactive') {
            $status_where_clause = " AND (c.Last_Trans < ? OR c.Last_Trans IS NULL)";
            $params_count[] = $cutoff_active;
            $types_count .= "s";
            $params_data[] = $cutoff_active;
            $types_data .= "s";
        }
    }

    // HAPUS $date_where_clause = "";
    // HAPUS BLOK if ($filter !== 'semua')

    // --- GUNAKAN FUNGSI HELPER ---
    $dateFilter = getDateFilterParams($_GET, 't');
    $date_where_clause = $dateFilter['sql_clause'];

    $params_count = array_merge($params_count, $dateFilter['params']);
    $types_count .= $dateFilter['types'];
    $params_data = array_merge($params_data, $dateFilter['params']);
    $types_data .= $dateFilter['types'];
    // ----------------------------

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

    $customer_subquery = "
        SELECT c.kd_cust
        FROM customers c
        WHERE ($age_case_sql) = ?
        $status_where_clause
    ";

    $count_sql = "
        SELECT COUNT(DISTINCT t.plu) AS total_records
        FROM
            trans_b t
        INNER JOIN
            ($customer_subquery) AS c_filtered ON t.kd_cust = c_filtered.kd_cust
        WHERE
            1=1 $date_where_clause
    ";

    $count_sql_prepared = str_replace('?', '?', $count_sql);
    $stmt_count = $conn->prepare($count_sql_prepared);
    if ($stmt_count === false) {
        throw new Exception("Database prepare failed (count): " . $conn->error);
    }

    $stmt_count->bind_param($types_count, ...$params_count);
    if (!$stmt_count->execute()) {
        throw new Exception("Gagal eksekusi query (count): " . $stmt_count->error);
    }
    $count_result = $stmt_count->get_result();
    $total_records = (int) $count_result->fetch_assoc()['total_records'];
    $total_pages = $total_records > 0 ? ceil($total_records / $limit) : 0;
    $stmt_count->close();

    $sql = "
        SELECT
            t.plu,
            t.descp,
            SUM(t.qty) AS total_qty
        FROM
            trans_b t
        INNER JOIN
            ($customer_subquery) AS c_filtered ON t.kd_cust = c_filtered.kd_cust
        WHERE
            1=1 $date_where_clause
        GROUP BY
            t.plu, t.descp
        ORDER BY
            total_qty DESC
        LIMIT ? OFFSET ?
    ";

    $params_data[] = $limit;
    $params_data[] = $offset;
    $types_data .= "ii";

    $sql_prepared = str_replace('?', '?', $sql);
    $stmt = $conn->prepare($sql_prepared);
    if ($stmt === false) {
        throw new Exception("Database prepare failed (data): " . $conn->error);
    }

    $stmt->bind_param($types_data, ...$params_data);

    if (!$stmt->execute()) {
        throw new Exception("Gagal eksekusi query (data): " . $stmt->error);
    }

    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'plu' => $row['plu'],
            'descp' => $row['descp'],
            'total_qty' => (int) $row['total_qty']
        ];
    }
    $stmt->close();
    $conn->close();

    echo json_encode([
        'success' => true,
        'data' => $data,
        'pagination' => [
            'total_records' => $total_records,
            'current_page' => $page,
            'limit' => $limit,
            'total_pages' => $total_pages
        ]
    ]);

} catch (Throwable $t) {
    $logger->critical("🔥 FATAL ERROR: " . $t->getMessage());
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Terjadi kesalahan: " . $t->getMessage()
    ]);
}
?>