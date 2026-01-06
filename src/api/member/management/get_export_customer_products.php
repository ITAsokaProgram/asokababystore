<?php
session_start();
ini_set('display_errors', 0);
require_once __DIR__ . '/../../../utils/Logger.php';
require_once __DIR__ . '/../../../auth/middleware_login.php';

$logger = new AppLogger('export_customer_history.log');

try {
    require_once __DIR__ . '/../../../../aa_kon_sett.php';
} catch (Throwable $t) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: Gagal memuat file koneksi.']);
    exit();
}

header('Content-Type: application/json');

// --- 1. Auth Check (Sama seperti sebelumnya) ---
try {
    $authHeader = null;
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (isset($headers['Authorization']))
            $authHeader = $headers['Authorization'];
    }
    if ($authHeader === null && isset($_SERVER['HTTP_AUTHORIZATION']))
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    if ($authHeader === null && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']))
        $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];

    if ($authHeader === null || !preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        throw new Exception("Token tidak ditemukan.");
    }

    $decoded = verify_token($matches[1]);
    if (!(is_object($decoded) && isset($decoded->kode))) {
        throw new Exception("Token tidak valid.");
    }
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

// --- 2. Helper Filter Tanggal (Sama seperti sebelumnya) ---
function getDateFilterParams($get_params, $table_alias = 't')
{
    $date_where_clause = "";
    $params = [];
    $types = "";
    $filter_type = $get_params['filter_type'] ?? 'preset';

    if ($filter_type === 'custom' && !empty($get_params['start_date']) && !empty($get_params['end_date'])) {
        $end_date_with_time = $get_params['end_date'] . ' 23:59:59';
        $date_where_clause = " AND {$table_alias}.tgl_trans BETWEEN ? AND ?";
        $params[] = $get_params['start_date'];
        $params[] = $end_date_with_time;
        $types = "ss";
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

        if ($filter === 'kemarin') {
            $cutoff = date('Y-m-d', strtotime("-1 day"));
            $date_where_clause = " AND DATE({$table_alias}.tgl_trans) = ?";
            $params[] = $cutoff;
            $types = "s";
        } elseif ($filter !== 'semua') {
            $interval = $filter_map[$filter] ?? '3 months';
            $cutoff = date('Y-m-d 00:00:00', strtotime("-$interval"));
            $date_where_clause = " AND {$table_alias}.tgl_trans >= ?";
            $params[] = $cutoff;
            $types = "s";
        }
    }
    return ['sql' => $date_where_clause, 'params' => $params, 'types' => $types];
}

try {
    $kd_cust = $_GET['kd_cust'] ?? '';
    if (empty($kd_cust))
        throw new Exception("Kode Customer tidak ditemukan.");

    $dateFilter = getDateFilterParams($_GET, 't');

    // --- PERUBAHAN DI SINI: ORDER BY DATE(...) DESC, t.qty DESC ---
    // Kita grouping pakai DATE(tgl_trans), jadi sorting utama harus DATE(tgl_trans) juga
    // agar data hari yang sama mengumpul. Lalu secondary sort pakai QTY DESC.
    $sql = "SELECT 
                t.plu, 
                t.descp, 
                t.tgl_trans, 
                DATE(t.tgl_trans) as tgl_only,
                t.jam_trs, 
                t.no_bon, 
                t.qty, 
                t.harga, 
                (t.qty * t.harga) as subtotal
            FROM trans_b t
            WHERE t.kd_cust = ? 
            " . $dateFilter['sql'] . "
            AND UPPER(t.descp) NOT LIKE '%MEMBER BY PHONE%'
            AND UPPER(t.descp) NOT LIKE '%TAS ASOKA BIRU%'
            ORDER BY DATE(t.tgl_trans) DESC, t.qty DESC";

    $stmt = $conn->prepare($sql);
    $params = array_merge([$kd_cust], $dateFilter['params']);
    $types = "s" . $dateFilter['types'];

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $groupedByDate = [];

    while ($row = $result->fetch_assoc()) {
        $tgl = $row['tgl_only'];

        if (!isset($groupedByDate[$tgl])) {
            $groupedByDate[$tgl] = [
                'date' => $tgl,
                'total_daily_omset' => 0,
                'total_daily_qty' => 0,
                'items' => []
            ];
        }

        $qty = (int) $row['qty'];
        $subtotal = (float) $row['subtotal'];

        $groupedByDate[$tgl]['total_daily_omset'] += $subtotal;
        $groupedByDate[$tgl]['total_daily_qty'] += $qty;

        $groupedByDate[$tgl]['items'][] = [
            'jam' => $row['jam_trs'],
            'no_bon' => $row['no_bon'],
            'plu' => $row['plu'],
            'descp' => $row['descp'],
            'qty' => $qty,
            'harga' => (float) $row['harga'],
            'subtotal' => $subtotal
        ];
    }

    $finalData = array_values($groupedByDate);

    echo json_encode([
        'success' => true,
        'data' => $finalData
    ]);

} catch (Throwable $t) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $t->getMessage()]);
}
?>