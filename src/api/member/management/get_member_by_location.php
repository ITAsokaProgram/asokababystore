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
try {
    $filter = $_GET['filter'] ?? '3bulan';
    $status = $_GET['status'] ?? 'active';
    $level = $_GET['level'] ?? 'city';
    $selected_city = $_GET['city'] ?? null;
    $selected_district = $_GET['district'] ?? null;
    $valid_filters = ['3bulan' => 3, '6bulan' => 6, '9bulan' => 9, '12bulan' => 12];
    $params = [];
    $types = "";
    $where_clause = "";
    $cutoff_date = null;
    if ($filter !== 'semua') {
        $months = $valid_filters[$filter] ?? 3;
        $current_date_start_day = date('Y-m-d 00:00:00');
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-$months months", strtotime($current_date_start_day)));
        if ($status === 'active') {
            $where_clause = " WHERE Last_Trans >= ?";
        } else {
            $where_clause = " WHERE (Last_Trans < ? OR Last_Trans IS NULL)";
        }
        $params[] = $cutoff_date;
        $types .= "s";
    } else {
        if ($status === 'active') {
            $where_clause = " WHERE Last_Trans IS NOT NULL";
        } else {
            $where_clause = " WHERE Last_Trans IS NULL";
        }
    }
    $date_where_clause = "";
    $params_for_products = [];
    $types_for_products = "";
    if ($filter !== 'semua' && $cutoff_date) {
        $date_where_clause = " AND t.tgl_trans >= ?";
        $params_for_products[] = $cutoff_date;
        $types_for_products .= "s";
    }
    $location_field = "";
    $city_field_logic = "IF(Kota IS NULL OR Kota = '', 'Customer belum input', Kota)";
    $district_field_logic = "IF(Kec IS NULL OR Kec = '', 'Customer belum input', Kec)";
    $subdistrict_field_logic = "IF(Kel IS NULL OR Kel = '', 'Customer belum input', Kel)";
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
        AND UPPER(t.descp) NOT LIKE '%KERTAS KADO%'
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
                COUNT(*) AS count
            FROM customers
            $where_clause 
            GROUP BY location_name
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
                        FROM customers
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
    ";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $logger->error("Database prepare failed: " . $conn->error, ['sql' => $sql]);
        throw new Exception("Database prepare failed: " . $conn->error);
    }
    $all_params = array_merge($params, $params, $params_for_products);
    $all_types = $types . $types . $types_for_products;
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
            'top_product_descp' => $row['top_product_descp'],
            'top_product_qty' => $row['top_product_qty'] ? (int) $row['top_product_qty'] : null
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
    $logger->critical("🔥 FATAL ERROR: " . $t->getMessage(), [
        'file' => $t->getFile(),
        'line' => $t->getLine()
    ]);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Terjadi kesalahan: " . $t->getMessage()
    ]);
}
?>