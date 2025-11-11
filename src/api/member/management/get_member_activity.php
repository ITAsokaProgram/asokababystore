<?php
session_start();
ini_set('display_errors', 0);
require_once __DIR__ . '/../../../utils/Logger.php';
require_once __DIR__ . '/../../../auth/middleware_login.php';
$logger = new AppLogger('member_activity.log');
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
try {
    $filter = $_GET['filter'] ?? '3bulan';
    $valid_filters = ['3bulan' => 3, '6bulan' => 6, '9bulan' => 9, '12bulan' => 12];
    $sql = "";
    $stmt = null;
    if ($filter === 'semua') {
        $sql = "SELECT
                    COUNT(*) AS total_member,
                    COALESCE(SUM(CASE WHEN Last_Trans IS NOT NULL THEN 1 ELSE 0 END), 0) AS active_member,
                    COALESCE(SUM(CASE WHEN Last_Trans IS NULL THEN 1 ELSE 0 END), 0) AS inactive_member
                FROM customers
                ";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Database prepare failed (semua): " . $conn->error);
        }
    } else {
        $months = $valid_filters[$filter] ?? 3;
        $current_date_start_day = date('Y-m-d 00:00:00');
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-$months months", strtotime($current_date_start_day)));
        $sql = "SELECT
                    COUNT(*) AS total_member,
                    COALESCE(SUM(CASE WHEN Last_Trans >= ? THEN 1 ELSE 0 END), 0) AS active_member,
                    COALESCE(SUM(CASE WHEN Last_Trans < ? OR Last_Trans IS NULL THEN 1 ELSE 0 END), 0) AS inactive_member
                FROM customers
                ";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Database prepare failed (bulan): " . $conn->error);
        }
        $stmt->bind_param("ss", $cutoff_date, $cutoff_date);
    }
    if (!$stmt->execute()) {
        throw new Exception("Gagal eksekusi query: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    echo json_encode([
        'success' => true,
        'data' => [
            'total' => (int) $data['total_member'],
            'active' => (int) $data['active_member'],
            'inactive' => (int) $data['inactive_member']
        ]
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