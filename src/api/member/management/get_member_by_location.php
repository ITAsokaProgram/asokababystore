<?php
session_start();
ini_set('display_errors', 0);
ob_start();
require_once __DIR__ . '/../../../utils/Logger.php';
require_once __DIR__ . '/../../../auth/middleware_login.php';
$logger = new AppLogger('member_location_activity.log');
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
    $status = $_GET['status'] ?? 'active';
    $level = $_GET['level'] ?? 'city';
    $selected_city = $_GET['city'] ?? null;
    $selected_district = $_GET['district'] ?? null;
    $limit_param = $_GET['limit'] ?? 'default';

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
    $filter_type = $_GET['filter_type'] ?? 'preset';
    $filter = $_GET['filter'] ?? '3bulan';
    $isFilter3MonthsOrLess = false;
    if ($filter_type === 'preset') {
        $isFilter3MonthsOrLess = in_array($filter, ['kemarin', '1minggu', '1bulan', '3bulan']);
    }

    // --- GUNAKAN FUNGSI HELPER ---
    $dateFilter = getDateFilterParams($_GET, 't');
    $date_where_clause = $dateFilter['sql_clause'];
    $params_for_products = $dateFilter['params'];
    $types_for_products = $dateFilter['types'];
    // ----------------------------

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
        $logger->error("Database prepare failed (active): " . $conn->error);
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
    $conn->close();
    ob_end_clean();

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch (Throwable $t) {
    ob_end_clean();
    $logger->critical("🔥 FATAL ERROR: " . $t->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Terjadi kesalahan: " . $t->getMessage()
    ]);
}
?>