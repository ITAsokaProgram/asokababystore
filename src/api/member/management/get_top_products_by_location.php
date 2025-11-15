<?php
session_start();
ini_set('display_errors', 0);
require_once __DIR__ . '/../../../utils/Logger.php';
require_once __DIR__ . '/../../../auth/middleware_login.php';
$logger = new AppLogger('top_product_by_location.log');
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
    $limit = (int) ($_GET['limit'] ?? 10);
    $page = (int) ($_GET['page'] ?? 1);
    $offset = ($page - 1) * $limit;
    $filter = $_GET['filter'] ?? 'semua';
    $city = $_GET['city'] ?? null;
    $district = $_GET['district'] ?? null;
    $subdistrict = $_GET['subdistrict'] ?? null;
    $status = $_GET['status'] ?? null;
    if (empty($city)) {
        throw new Exception("Parameter 'city' tidak boleh kosong.");
    }
    $filter_map = [
        'kemarin' => '1 day',
        '1minggu' => '1 week',
        '1bulan' => '1 month',
        '3bulan' => '3 months',
        '6bulan' => '6 months',
        '9bulan' => '9 months',
        '12bulan' => '12 months'
    ];
    $interval = $filter_map[$filter] ?? '3 months';
    if ($status === 'inactive') {
        $cutoff_active_ts = strtotime("-3 months");
        $cutoff_filter_ts = strtotime("-$interval");
        if ($filter !== 'semua' && $cutoff_filter_ts >= $cutoff_active_ts) {
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
    $params_count = [];
    $types_count = "";
    $params_data = [];
    $types_data = "";
    $location_where_clause = " WHERE IF(c.Kota IS NULL OR c.Kota = '', 'Customer belum input', c.Kota) = ? ";
    $params_count[] = $city;
    $types_count .= "s";
    $params_data[] = $city;
    $types_data .= "s";
    if (!empty($district)) {
        $location_where_clause .= " AND IF(c.Kec IS NULL OR c.Kec = '', 'Customer belum input', c.Kec) = ? ";
        $params_count[] = $district;
        $types_count .= "s";
        $params_data[] = $district;
        $types_data .= "s";
    }
    if (!empty($subdistrict)) {
        $location_where_clause .= " AND IF(c.Kel IS NULL OR c.Kel = '', 'Customer belum input', c.Kel) = ? ";
        $params_count[] = $subdistrict;
        $types_count .= "s";
        $params_data[] = $subdistrict;
        $types_data .= "s";
    }
    if ($status) {
        $cutoff_active = date('Y-m-d 00:00:00', strtotime("-3 months"));
        if ($status === 'active') {
            $location_where_clause .= " AND (c.Last_Trans >= ?)";
            $params_count[] = $cutoff_active;
            $types_count .= "s";
            $params_data[] = $cutoff_active;
            $types_data .= "s";
        } elseif ($status === 'inactive') {
            $location_where_clause .= " AND (c.Last_Trans < ? OR c.Last_Trans IS NULL)";
            $params_count[] = $cutoff_active;
            $types_count .= "s";
            $params_data[] = $cutoff_active;
            $types_data .= "s";
        }
    }
    $date_where_clause = "";
    if ($filter !== 'semua') {
        $cutoff_date = date('Y-m-d 00:00:00', strtotime("-$interval"));
        $date_where_clause = " AND t.tgl_trans >= ? ";
        $params_count[] = $cutoff_date;
        $types_count .= "s";
        $params_data[] = $cutoff_date;
        $types_data .= "s";
    }
    $customer_subquery = "
        SELECT c.kd_cust
        FROM customers c
        $location_where_clause
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
    $stmt_count = $conn->prepare($count_sql);
    if ($stmt_count === false) {
        throw new Exception("Database prepare failed (count): " . $conn->error);
    }
    if (!empty($params_count)) {
        $stmt_count->bind_param($types_count, ...$params_count);
    }
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
    $stmt = $conn->prepare($sql);
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