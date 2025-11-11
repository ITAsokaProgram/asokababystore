<?php
session_start();
ini_set('display_errors', 0);
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
    $sql = "
        SELECT 
            $location_field AS location_name,
            COUNT(*) AS count
        FROM customers
        $where_clause
        GROUP BY location_name
        ORDER BY count DESC
    ";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Database prepare failed: " . $conn->error);
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        throw new Exception("Gagal eksekusi query: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'location_name' => $row['location_name'],
            'count' => (int) $row['count']
        ];
    }
    $stmt->close();
    $conn->close();
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
} catch (Throwable $t) {
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