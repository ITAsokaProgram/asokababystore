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
    if (!(is_object($decoded) && isset($decoded->kode))) {
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
    $filter = $_GET['filter'] ?? 'semua';
    $kd_cust_param = $_GET['kd_cust'] ?? '';
    $plu_param = $_GET['plu'] ?? '';
    if (empty($kd_cust_param) || empty($plu_param)) {
        throw new Exception("Parameter 'kd_cust' dan 'plu' tidak boleh kosong.");
    }
    $params_sub = [$kd_cust_param, $plu_param];
    $types_sub = "ss";
    $params_main = [];
    $types_main = "";
    $date_where_clause_sub = "";
    $date_where_clause_main = "";
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
        $date_where_clause_sub = " AND t2.tgl_trans >= ? ";
        $params_sub[] = $cutoff_date;
        $types_sub .= "s";
        $date_where_clause_main = " AND t.tgl_trans >= ? ";
        $params_main[] = $cutoff_date;
        $types_main .= "s";
    }
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
    $stmt->bind_param($types_sub . $types_main, ...$params_sub, ...$params_main);
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