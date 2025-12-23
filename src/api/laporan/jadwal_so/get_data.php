<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../../../../aa_kon_sett.php';
require_once __DIR__ . "./../../../auth/middleware_login.php";

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR])) {
        if (!headers_sent()) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Fatal Server Error: ' . $error['message']
            ]);
        }
    }
});

header('Content-Type: application/json');

// --- BAGIAN AUTHENTICATION (DIAMBIL DARI LOGIKA get_kode.php) ---
$header = getAllHeaders();
$authHeader = $header['Authorization'] ?? '';
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1];
}

if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthenticated', 'message' => 'Request ditolak user tidak terdaftar']);
    exit;
}

$verif = verify_token($token);
// --------------------------------------------------------------

$table_name = "jadwal_so";
$is_export = filter_var($_GET['export'] ?? false, FILTER_VALIDATE_BOOLEAN);

$response = [
    'summary' => [
        'total_jadwal' => 0,
        'total_proses' => 0,
        'total_selesai' => 0,
        'total_tunggu' => 0,
    ],
    'stores' => [],
    'tabel_data' => [],
    'pagination' => null,
    'error' => null,
];

try {
    $tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-d');
    $tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');
    $kd_store = $_GET['kd_store'] ?? 'all';
    $status = $_GET['status'] ?? 'all';
    $sync_filter = $_GET['sync'] ?? 'all';
    $page = (int) ($_GET['page'] ?? 1);
    $limit = 100;
    $offset = ($page - 1) * $limit;

    if (!$is_export) {
        if ($page < 1)
            $page = 1;
        $response['pagination'] = [
            'current_page' => $page,
            'limit' => $limit,
            'offset' => $offset
        ];
    }

    // --- BAGIAN PENGAMBILAN KODE CABANG (LOGIKA DISESUAIKAN) ---
    $sqlUserCabang = "SELECT kd_store FROM user_account WHERE kode = ?";
    $stmtUserCabang = $conn->prepare($sqlUserCabang);
    $stmtUserCabang->bind_param("s", $verif->kode);
    $stmtUserCabang->execute();
    $resultUserCabang = $stmtUserCabang->get_result();

    if ($resultUserCabang->num_rows > 0) {
        $userCabang = $resultUserCabang->fetch_assoc();
        if ($userCabang['kd_store'] == "Pusat") {
            $sql_stores = "SELECT Kd_Store as store, Nm_Alias as nama_cabang FROM kode_store WHERE display = 'on' ORDER BY Nm_Alias ASC";
            $stmt_s = $conn->prepare($sql_stores);
        } else {
            $kdStoreArray = explode(',', $userCabang['kd_store']);
            $kdStoreImplode = "'" . implode("','", $kdStoreArray) . "'";
            $sql_stores = "SELECT Kd_Store as store, Nm_Alias as nama_cabang FROM kode_store WHERE Kd_Store IN ($kdStoreImplode) AND display = 'on' ORDER BY Nm_Alias ASC";
            $stmt_s = $conn->prepare($sql_stores);
        }
        $stmt_s->execute();
        $result_stores = $stmt_s->get_result();
        while ($row = $result_stores->fetch_assoc()) {
            $response['stores'][] = $row;
        }
        $stmt_s->close();
    }
    $stmtUserCabang->close();
    // ----------------------------------------------------------

    $where_clauses = ["DATE(Tgl_schedule) BETWEEN ? AND ?"];
    $bind_types = "ss";
    $bind_params = [$tgl_mulai, $tgl_selesai];

    if ($kd_store !== 'all' && $kd_store !== 'SEMUA CABANG' && $kd_store !== 'none') {
        $where_clauses[] = "Kd_Store = ?";
        $bind_types .= "s";
        $bind_params[] = $kd_store;
    }

    if ($status !== 'all') {
        $where_clauses[] = "status = ?";
        $bind_types .= "s";
        $bind_params[] = $status;
    }

    if ($sync_filter !== 'all') {
        $where_clauses[] = "sync = ?";
        $bind_types .= "s";
        $bind_params[] = $sync_filter;
    }

    $where_sql = implode(" AND ", $where_clauses);
    $sql_calc = (!$is_export) ? "SQL_CALC_FOUND_ROWS" : "";
    $sql_limit = (!$is_export) ? "LIMIT ? OFFSET ?" : "";

    $query = "
        SELECT 
            $sql_calc
            Kd_Store, 
            Nm_Store, 
            kode_supp, 
            nama_supp, 
            Tgl_schedule, 
            status, 
            CAST(sync AS CHAR) as sync
        FROM $table_name
        WHERE $where_sql
        ORDER BY Tgl_schedule DESC, Kd_Store ASC
        $sql_limit
    ";

    $stmt_data = $conn->prepare($query);
    $params_data = $bind_params;
    if (!$is_export) {
        $bind_types_data = $bind_types . "ii";
        $params_data[] = $limit;
        $params_data[] = $offset;
    } else {
        $bind_types_data = $bind_types;
    }

    $stmt_data->bind_param($bind_types_data, ...$params_data);
    $stmt_data->execute();
    $result_data = $stmt_data->get_result();
    while ($row = $result_data->fetch_assoc()) {
        $response['tabel_data'][] = $row;
    }
    $stmt_data->close();

    if (!$is_export) {
        $res_count = $conn->query("SELECT FOUND_ROWS() as total");
        $total_rows = $res_count->fetch_assoc()['total'];
        $response['pagination']['total_rows'] = (int) $total_rows;
        $response['pagination']['total_pages'] = ceil($total_rows / $limit);
    }

    $query_summary = "
        SELECT 
            COUNT(*) as total_jadwal,
            SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as total_selesai,
            SUM(CASE WHEN status = 'Proses' THEN 1 ELSE 0 END) as total_proses, 
            SUM(CASE WHEN status = 'Tunggu' OR status IS NULL THEN 1 ELSE 0 END) as total_tunggu
        FROM $table_name
        WHERE $where_sql
    ";
    $stmt_summary = $conn->prepare($query_summary);
    $stmt_summary->bind_param($bind_types, ...$bind_params);
    $stmt_summary->execute();
    $res_summary = $stmt_summary->get_result()->fetch_assoc();
    if ($res_summary) {
        $response['summary'] = $res_summary;
    }

    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>