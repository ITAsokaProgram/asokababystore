<?php
session_start();
ini_set('display_errors', 0);
require_once __DIR__ . '/../../../utils/Logger.php';
require_once __DIR__ . '/../../../auth/middleware_login.php';

$logger = new AppLogger('top_customer_city.log');

try {
    require_once __DIR__ . '/../../../../aa_kon_sett.php';
} catch (Throwable $t) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: Gagal memuat file koneksi.']);
    exit();
}

header('Content-Type: application/json');

// --- VALIDASI TOKEN (Boilerplate) ---
try {
    $authHeader = null;
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (isset($headers['Authorization']))
            $authHeader = $headers['Authorization'];
    }
    if ($authHeader === null && isset($_SERVER['HTTP_AUTHORIZATION']))
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];

    if ($authHeader === null || !preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => "Token tidak valid."]);
        exit;
    }
    verify_token($matches[1]); // Asumsi fungsi ini ada dan melempar exception jika gagal
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// --- HELPER FILTER TANGGAL ---
function getDateFilterParams($get_params, $table_alias = 't')
{
    $date_where_clause = "";
    $params = [];
    $types = "";

    $filter_type = $get_params['filter_type'] ?? 'preset';

    if ($filter_type === 'custom' && !empty($get_params['start_date']) && !empty($get_params['end_date'])) {
        $start_date = $get_params['start_date'];
        $end_date = $get_params['end_date'] . ' 23:59:59';
        $date_where_clause = " AND {$table_alias}.tgl_trans BETWEEN ? AND ?";
        $params[] = $start_date;
        $params[] = $end_date;
        $types = "ss";
    } else {
        $filter = $get_params['filter'] ?? '3bulan';
        $interval_map = [
            'kemarin' => '1 day',
            '1minggu' => '1 week',
            '1bulan' => '1 month',
            '3bulan' => '3 months',
            '6bulan' => '6 months',
            '12bulan' => '12 months'
        ];

        if ($filter !== 'semua') {
            if ($filter === 'kemarin') {
                $date = date('Y-m-d', strtotime("-1 day"));
                $date_where_clause = " AND DATE({$table_alias}.tgl_trans) = ?";
                $params[] = $date;
                $types = "s";
            } else {
                $interval = $interval_map[$filter] ?? '3 months';
                $date = date('Y-m-d 00:00:00', strtotime("-$interval"));
                $date_where_clause = " AND {$table_alias}.tgl_trans >= ?";
                $params[] = $date;
                $types = "s";
            }
        }
    }
    return ['sql' => $date_where_clause, 'params' => $params, 'types' => $types];
}

try {
    $limit = (int) ($_GET['limit'] ?? 10);
    $page = (int) ($_GET['page'] ?? 1);
    $offset = ($page - 1) * $limit;
    $kota_filter = $_GET['kota'] ?? '';

    // Filter Tanggal
    $dateFilter = getDateFilterParams($_GET, 't');

    // Base Condition
    $where_kota = " AND c.Kota IS NOT NULL AND c.Kota != '' ";
    $params = $dateFilter['params'];
    $types = $dateFilter['types'];

    // Filter Kota Spesifik
    if (!empty($kota_filter) && $kota_filter !== 'all') {
        $where_kota .= " AND c.Kota = ? ";
        $params[] = $kota_filter;
        $types .= "s";
    }

    // Exclude Dummy/Internal
    $exclude = " AND c.kd_cust NOT IN ('INTERNAL', 'DUMMY') AND UPPER(c.nama_cust) NOT LIKE '%DUMMY%' ";

    // QUERY UTAMA
    // Menghitung Frequency (Jumlah bon unik) dan Total Omset
    $sql = "
        SELECT 
            c.kd_cust,
            c.nama_cust,
            c.Kota,
            c.no_hp,
            COUNT(DISTINCT t.no_bon) as freq_belanja,
            SUM(t.qty * t.harga) as total_belanja
        FROM trans_b t
        JOIN customers c ON t.kd_cust = c.kd_cust
        WHERE 1=1 
        {$dateFilter['sql']} 
        {$where_kota}
        {$exclude}
        GROUP BY c.kd_cust, c.Kota
        ORDER BY freq_belanja DESC, total_belanja DESC
        LIMIT ? OFFSET ?
    ";

    // Query untuk Total Records (Pagination)
    $sql_count = "
        SELECT COUNT(DISTINCT c.kd_cust) as total
        FROM trans_b t
        JOIN customers c ON t.kd_cust = c.kd_cust
        WHERE 1=1 
        {$dateFilter['sql']} 
        {$where_kota}
        {$exclude}
    ";

    // Eksekusi Count
    $stmt_count = $conn->prepare($sql_count);
    if (!empty($types)) {
        $stmt_count->bind_param($types, ...$params);
    }
    $stmt_count->execute();
    $total_records = $stmt_count->get_result()->fetch_assoc()['total'];
    $stmt_count->close();

    // Eksekusi Data
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'kd_cust' => $row['kd_cust'],
            'nama_cust' => $row['nama_cust'],
            'kota' => $row['Kota'],
            'no_hp' => $row['no_hp'],
            'freq' => (int) $row['freq_belanja'],
            'omset' => (float) $row['total_belanja']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $data,
        'pagination' => [
            'total_records' => $total_records,
            'total_pages' => ceil($total_records / $limit),
            'current_page' => $page
        ]
    ]);

} catch (Throwable $t) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $t->getMessage()]);
}
?>