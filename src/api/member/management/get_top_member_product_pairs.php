<?php
session_start();
ini_set('display_errors', 0);
require_once __DIR__ . '/../../../utils/Logger.php';
require_once __DIR__ . '/../../../auth/middleware_login.php';
$logger = new AppLogger('top_member_product.log');
try {
    require_once __DIR__ . '/../../../../aa_kon_sett.php';
} catch (Throwable $t) {
    $logger->critical("Gagal memuat file koneksi: " . $t->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: Gagal memuat file.']);
    exit();
}
header('Content-Type: application/json');
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
    if (!is_object($decoded) || !isset($decoded->kode)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Token tidak valid.']);
        exit;
    }
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
    $limit = (int) ($_GET['limit'] ?? 10);
    $filter_map = [
        'kemarin' => '1 day',
        '1minggu' => '1 week',
        '1bulan' => '1 month',
        '3bulan' => '3 months',
        '6bulan' => '6 months',
        '9bulan' => '9 months',
        '12bulan' => '12 months'
    ];
    $params = [];
    $types = "";
    $where_clauses = [];
    if ($filter !== 'semua') {
        $interval_trans = $filter_map[$filter] ?? '3 months';
        $cutoff_trans = date('Y-m-d 00:00:00', strtotime("-$interval_trans"));
        $where_clauses[] = "t.tgl_trans >= ?";
        $params[] = $cutoff_trans;
        $types .= "s";
    }
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
    $where_clauses[] = "t.kd_cust NOT IN ('', '898989', '#898989', '#999999999')";
    $sql_where = implode(" AND ", $where_clauses);
    $sql = "
        SELECT
            c.nama_cust,
            t.kd_cust,
            t.descp,
            t.plu,
            SUM(t.qty) AS total_item_qty
        FROM
            trans_b t
        INNER JOIN
            customers c ON t.kd_cust = c.kd_cust
        WHERE
            $sql_where
        GROUP BY
            t.kd_cust, t.plu
        ORDER BY
            total_item_qty DESC
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
            'descp' => $row['descp'],
            'plu' => $row['plu'],
            'total_item_qty' => (int) $row['total_item_qty']
        ];
    }
    $stmt->close();
    $conn->close();
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
} catch (Throwable $t) {
    $logger->critical("FATAL ERROR: " . $t->getMessage(), [
        'file' => $t->getFile(),
        'line' => $t->getLine(),
        'params' => $_GET
    ]);
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