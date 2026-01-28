<?php
session_start();
ini_set('display_errors', 0);
require_once __DIR__ . '/../../../utils/Logger.php';
require_once __DIR__ . '/../../../auth/middleware_login.php';
$logger = new AppLogger('transaction_detail.log');
try {
    require_once __DIR__ . '/../../../../aa_kon_sett.php';
} catch (Throwable $t) {
    $logger->critical("Gagal memuat file koneksi: " . $t->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: Gagal memuat file.']);
    exit();
}
header('Content-Type: application/json');
$verif = authenticate_request();

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
        // Tambahkan waktu ke end_date untuk mencakup seluruh hari
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
            // Tidak ada klausa where tanggal
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
    $kd_cust_param = $_GET['kd_cust'] ?? '';
    $plu_param = $_GET['plu'] ?? '';

    if (empty($kd_cust_param) || empty($plu_param)) {
        throw new Exception("Parameter 'kd_cust' dan 'plu' tidak boleh kosong.");
    }

    // Dapatkan filter untuk subquery (alias 't2')
    $dateFilterSub = getDateFilterParams($_GET, 't2');
    $date_where_clause_sub = $dateFilterSub['sql_clause'];
    $params_sub = array_merge([$kd_cust_param, $plu_param], $dateFilterSub['params']);
    $types_sub = "ss" . $dateFilterSub['types'];

    // Dapatkan filter untuk main query (alias 't')
    $dateFilterMain = getDateFilterParams($_GET, 't');
    $date_where_clause_main = $dateFilterMain['sql_clause'];
    $params_main = $dateFilterMain['params'];
    $types_main = $dateFilterMain['types'];

    $sql = "
        SELECT
            DATE(t.tgl_trans) as tgl_trans_date,
            t.tgl_trans,
            t.jam_trs,
            t.no_bon,
            t.descp,
            t.qty,
            t.harga,
            t.diskon
        FROM
            trans_b t
        WHERE
            t.no_bon IN (
                SELECT DISTINCT t2.no_bon
                FROM trans_b t2
                WHERE t2.kd_cust = ?
                    AND t2.plu = ?
                    $date_where_clause_sub
            )
            $date_where_clause_main
        ORDER BY
            t.tgl_trans DESC, t.jam_trs DESC, t.no_bon, t.descp
    ";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Database prepare failed: " . $conn->error);
    }

    // Gabungkan parameter dari subquery dan main query
    $all_params = array_merge($params_sub, $params_main);
    $all_types = $types_sub . $types_main;

    $stmt->bind_param($all_types, ...$all_params);

    if (!$stmt->execute()) {
        throw new Exception("Gagal eksekusi query: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'tgl_trans_date' => $row['tgl_trans_date'],
            'tgl_jam_trans' => $row['tgl_trans'] . ' ' . $row['jam_trs'],
            'jam_trs' => $row['jam_trs'],
            'no_bon' => $row['no_bon'],
            'descp' => $row['descp'],
            'qty' => (int) $row['qty'],
            'harga' => (float) $row['harga'],
            'diskon' => (float) $row['diskon']
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