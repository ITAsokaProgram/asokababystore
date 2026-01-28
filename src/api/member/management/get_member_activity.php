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
$userInfo = authenticate_request();
$user_kode = $userInfo->kode ?? 'UNKNOWN';
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
    $filter_type = $_GET['filter_type'] ?? 'preset';
    $sql = "";
    $stmt = null;
    if ($filter_type === 'custom' && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
        $start_date = $_GET['start_date'] . ' 00:00:00';
        $end_date = $_GET['end_date'] . ' 23:59:59';
        $sql = "SELECT 
                    COUNT(*) AS total_member,
                    COALESCE(SUM(CASE WHEN Last_Trans BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) AS active_member,
                    COALESCE(SUM(CASE WHEN Last_Trans < ? OR Last_Trans IS NULL THEN 1 ELSE 0 END), 0) AS inactive_member
                FROM customers";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Database prepare failed (custom): " . $conn->error);
        }
        $stmt->bind_param("sss", $start_date, $end_date, $start_date);
    } else {
        $filter = $_GET['filter'] ?? '3bulan';
        $valid_filters = [
            'kemarin' => '-1 day', '1minggu' => '-1 week', '1bulan' => '-1 month',
            '3bulan' => '-3 months', '6bulan' => '-6 months', '9bulan' => '-9 months',
            '12bulan' => '-12 months'
        ];
        if ($filter === 'semua') {
            $sql = "SELECT 
                        COUNT(*) AS total_member,
                        COALESCE(SUM(CASE WHEN Last_Trans IS NOT NULL THEN 1 ELSE 0 END), 0) AS active_member,
                        COALESCE(SUM(CASE WHEN Last_Trans IS NULL THEN 1 ELSE 0 END), 0) AS inactive_member
                    FROM customers";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Database prepare failed (semua): " . $conn->error);
            }
        } else {
            $time_modifier = $valid_filters[$filter] ?? '-3 months';
            $current_date_start_day = date('Y-m-d 00:00:00');
            if ($filter === 'kemarin') {
                $start_date = date('Y-m-d 00:00:00', strtotime('-1 day'));
                $end_date = date('Y-m-d 23:59:59', strtotime('-1 day'));
                $sql = "SELECT 
                            COUNT(*) AS total_member,
                            COALESCE(SUM(CASE WHEN Last_Trans BETWEEN ? AND ? THEN 1 ELSE 0 END), 0) AS active_member,
                            COALESCE(SUM(CASE WHEN Last_Trans < ? OR Last_Trans > ? OR Last_Trans IS NULL THEN 1 ELSE 0 END), 0) AS inactive_member
                        FROM customers";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    throw new Exception("Database prepare failed (kemarin): " . $conn->error);
                }
                $stmt->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
            } else {
                $cutoff_date = date('Y-m-d H:i:s', strtotime($time_modifier, strtotime($current_date_start_day)));
                $sql = "SELECT 
                            COUNT(*) AS total_member,
                            COALESCE(SUM(CASE WHEN Last_Trans >= ? THEN 1 ELSE 0 END), 0) AS active_member,
                            COALESCE(SUM(CASE WHEN Last_Trans < ? OR Last_Trans IS NULL THEN 1 ELSE 0 END), 0) AS inactive_member
                        FROM customers";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    throw new Exception("Database prepare failed (filtered): " . $conn->error);
                }
                $stmt->bind_param("ss", $cutoff_date, $cutoff_date);
            }
        }
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