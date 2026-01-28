<?php
session_start();
ini_set('display_errors', 0);
require_once __DIR__ . '/../../../utils/Logger.php';
require_once __DIR__ . '/../../../auth/middleware_login.php';

$logger = new AppLogger('top_product_by_customer.log');
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
    $logger->warning("Method Not Allowed: " . $_SERVER['REQUEST_METHOD']);
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
    $limit = (int) ($_GET['limit'] ?? 10);
    $page = (int) ($_GET['page'] ?? 1);
    $offset = ($page - 1) * $limit;
    $kd_cust_param = $_GET['kd_cust'] ?? '';

    if (empty($kd_cust_param)) {
        throw new Exception("Parameter 'kd_cust' tidak boleh kosong.");
    }

    // Gunakan helper filter tanggal (alias tabel 't')
    $dateFilter = getDateFilterParams($_GET, 't');
    $date_where_clause = $dateFilter['sql_clause'];

    // Siapkan parameter untuk query COUNT
    $params_count = [$kd_cust_param];
    $types_count = "s";
    $params_count = array_merge($params_count, $dateFilter['params']);
    $types_count .= $dateFilter['types'];

    // Siapkan parameter untuk query DATA
    $params_data = [$kd_cust_param];
    $types_data = "s";
    $params_data = array_merge($params_data, $dateFilter['params']);
    $types_data .= $dateFilter['types'];


    $exclude_clause = "
        AND UPPER(t.descp) NOT LIKE '%MEMBER BY PHONE%'
        AND UPPER(t.descp) NOT LIKE '%TAS ASOKA BIRU%'
    ";

    $count_sql = "
        SELECT COUNT(DISTINCT t.plu) AS total_records
        FROM
            trans_b t
        WHERE
            t.kd_cust = ? $date_where_clause $exclude_clause
    ";

    $stmt_count = $conn->prepare($count_sql);
    if ($stmt_count === false) {
        throw new Exception("Database prepare failed (count): " . $conn->error);
    }

    $stmt_count->bind_param($types_count, ...$params_count);

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
        WHERE
            t.kd_cust = ? $date_where_clause $exclude_clause
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