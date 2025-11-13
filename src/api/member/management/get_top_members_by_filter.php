<?php
session_start();
ini_set('display_errors', 0);
require_once __DIR__ . '/../../../utils/Logger.php';
require_once __DIR__ . '/../../../auth/middleware_login.php';
$logger = new AppLogger('top_members_filter.log');
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
    $customer_subquery_sql = "SELECT kd_cust FROM customers";
    $customer_params = [];
    $customer_types = "";
    $customer_where_clause = "";
    if ($filter !== 'semua') {
        $interval = $filter_map[$filter] ?? '3 months';
        $cutoff_date = date('Y-m-d 00:00:00', strtotime("-$interval"));
        if ($status === 'active') {
            $customer_where_clause = " WHERE Last_Trans >= ?";
        } else {
            $customer_where_clause = " WHERE (Last_Trans < ? OR Last_Trans IS NULL)";
        }
        $customer_params[] = $cutoff_date;
        $customer_types .= "s";
    } else {
        if ($status === 'active') {
            $customer_where_clause = " WHERE Last_Trans IS NOT NULL";
        } else {
            $customer_where_clause = " WHERE Last_Trans IS NULL";
        }
    }
    $customer_subquery_sql .= $customer_where_clause;
    $trans_date_where_clause = "";
    $trans_params = [];
    $trans_types = "";
    if ($filter !== 'semua') {
        $cutoff_date_trans = $cutoff_date;
        $trans_date_where_clause = " AND t.tgl_trans >= ? ";
        $trans_params[] = $cutoff_date_trans;
        $trans_types .= "s";
    }
    $sql = "
        SELECT
            c.nama_cust,
            t.kd_cust,
            SUM(t.qty * t.harga) AS total_spent
        FROM
            trans_b t
        INNER JOIN
            ($customer_subquery_sql) AS c_filtered ON t.kd_cust = c_filtered.kd_cust
        INNER JOIN
            customers c ON t.kd_cust = c.kd_cust
        WHERE
            1=1 $trans_date_where_clause
            AND t.kd_cust IS NOT NULL
            AND t.kd_cust NOT IN ('', '898989', '#898989', '#999999999')
        GROUP BY
            c.nama_cust, t.kd_cust
        ORDER BY
            total_spent DESC
        LIMIT ?
    ";
    $all_params = array_merge($customer_params, $trans_params);
    $all_params[] = $limit;
    $all_types = $customer_types . $trans_types . "i";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Database prepare failed (data): " . $conn->error);
    }
    $stmt->bind_param($all_types, ...$all_params);
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
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
} catch (Throwable $t) {
    $logger->critical("🔥 FATAL ERROR: " . $t->getMessage(), [
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