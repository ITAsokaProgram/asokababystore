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
    $limit = (int) ($_GET['limit'] ?? 10);
    $page = (int) ($_GET['page'] ?? 1);
    $offset = ($page - 1) * $limit;
    $filter = $_GET['filter'] ?? 'semua';
    $kd_cust_param = $_GET['kd_cust'] ?? '';

    if (empty($kd_cust_param)) {
        throw new Exception("Parameter 'kd_cust' tidak boleh kosong.");
    }

    $params_count = [$kd_cust_param];
    $types_count = "s";
    $params_data = [$kd_cust_param];
    $types_data = "s";

    $date_where_clause = "";
    $valid_filters = ['3bulan' => 3, '6bulan' => 6, '9bulan' => 9, '12bulan' => 12];
    if ($filter !== 'semua') {
        $interval = '3 months';
        if ($filter === 'kemarin')
            $interval = '1 day';
        elseif ($filter === '1minggu')
            $interval = '1 week';
        elseif ($filter === '1bulan')
            $interval = '1 month';
        elseif ($filter === '3bulan')
            $interval = '3 months';
        elseif ($filter === '6bulan')
            $interval = '6 months';
        elseif ($filter === '9bulan')
            $interval = '9 months';
        elseif ($filter === '12bulan')
            $interval = '12 months';

        $cutoff_date = date('Y-m-d 00:00:00', strtotime("-$interval"));
        $date_where_clause = " AND t.tgl_trans >= ? ";
        $params_count[] = $cutoff_date;
        $types_count .= "s";
        $params_data[] = $cutoff_date;
        $types_data .= "s";
    }


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