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
/**
 * Helper untuk mendapatkan parameter filter tanggal.
 */
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
    $filter_type = $_GET['filter_type'] ?? 'preset';
    $filter = $_GET['filter'] ?? '3bulan';
    $status = $_GET['status'] ?? 'active';
    $limit = (int) ($_GET['limit'] ?? 10);
    $isFilter3MonthsOrLess = false;
    if ($filter_type === 'preset') {
        $isFilter3MonthsOrLess = in_array($filter, ['kemarin', '1minggu', '1bulan', '3bulan']);
    }
    if ($status === 'inactive' && $isFilter3MonthsOrLess) {
        echo json_encode(['success' => true, 'data' => []]);
        $conn->close();
        exit();
    }
    $dateFilter = getDateFilterParams($_GET, 't');
    $params = [];
    $types = "";
    $where_clauses = [];
    if (!empty($dateFilter['sql_clause'])) {
        $where_clauses[] = preg_replace('/^\s*AND\s/i', '', $dateFilter['sql_clause']);
    }
    $params = array_merge($params, $dateFilter['params']);
    $types .= $dateFilter['types'];
    $cutoff_date_status = date('Y-m-d 00:00:00', strtotime("-3 months"));
    if ($status === 'active') {
        $where_clauses[] = "(c.Last_Trans >= ?)";
        $params[] = $cutoff_date_status;
        $types .= "s";
    } else {
        $where_clauses[] = "(c.Last_Trans < ? OR c.Last_Trans IS NULL)";
        $params[] = $cutoff_date_status;
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
            t.kd_cust, t.plu, c.nama_cust, t.descp
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
    $logger->critical("FATAL ERROR: " . $t->getMessage());
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