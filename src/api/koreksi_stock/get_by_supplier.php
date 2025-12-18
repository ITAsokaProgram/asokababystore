<?php
session_start();
include '../../../aa_kon_sett.php';
require_once __DIR__ . "./../../auth/middleware_login.php"; // Tambahkan middleware

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR])) {
        if (!headers_sent()) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Fatal Server Error. Pesan: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']
            ]);
        }
    }
});

header('Content-Type: application/json');

// --- BAGIAN OTENTIKASI (DARI get_kode.php) ---
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
// ---------------------------------------------

$is_export = $_GET['export'] ?? false;
$is_export = ($is_export === 'true' || $is_export === true);

$response = [
    'summary' => [
        'total_qtykor' => 0,
        'total_rp_koreksi' => 0,
        'total_rp_selisih' => 0,
    ],
    'stores' => [],
    'tabel_data' => [],
    'date_subtotals' => [],
    'pagination' => [
        'current_page' => 1,
        'total_pages' => 1,
        'total_rows' => 0,
        'offset' => 0,
        'limit' => 100,
    ],
    'error' => null,
];

try {
    $tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));
    $tgl_mulai = $_GET['tgl_mulai'] ?? $tanggal_kemarin;
    $tgl_selesai = $_GET['tgl_selesai'] ?? $tanggal_kemarin;
    $kd_store = $_GET['kd_store'] ?? 'all';

    $page = 1;
    $limit = 100;

    if (!$is_export) {
        $page = (int) ($_GET['page'] ?? 1);
        if ($page < 1)
            $page = 1;
        $response['pagination']['limit'] = $limit;
        $response['pagination']['current_page'] = $page;
    } else {
        $response['pagination'] = null;
    }

    $offset = ($page - 1) * $limit;
    if (isset($response['pagination'])) {
        $response['pagination']['offset'] = $offset;
    }

    // --- BAGIAN GET KODE CABANG (FUNCTIONALITY DARI get_kode.php) ---
    $sqlUserCabang = "SELECT kd_store FROM user_account WHERE kode = ?";
    $stmtUserCabang = $conn->prepare($sqlUserCabang);
    $stmtUserCabang->bind_param("s", $verif->kode);
    $stmtUserCabang->execute();
    $resultUserCabang = $stmtUserCabang->get_result();

    if ($resultUserCabang->num_rows > 0) {
        $userCabang = $resultUserCabang->fetch_assoc();
        if ($userCabang['kd_store'] == "Pusat") {
            $sql_stores = "SELECT Kd_Store as kd_store, Nm_Alias as nm_alias FROM kode_store WHERE display = 'on' ORDER BY nm_alias ASC";
            $stmt_s = $conn->prepare($sql_stores);
        } else {
            $kdStoreArray = explode(',', $userCabang['kd_store']);
            $kdStoreImplode = "'" . implode("','", $kdStoreArray) . "'";
            $sql_stores = "SELECT Kd_Store as kd_store, Nm_Alias as nm_alias FROM kode_store WHERE Kd_Store IN ($kdStoreImplode) AND display = 'on' ORDER BY nm_alias ASC";
            $stmt_s = $conn->prepare($sql_stores);
        }
        $stmt_s->execute();
        $res_s = $stmt_s->get_result();
        while ($row = $res_s->fetch_assoc()) {
            $response['stores'][] = $row;
        }
        $stmt_s->close();
    }
    $stmtUserCabang->close();
    // --------------------------------------------------------------

    $where_conditions = "DATE(k.tgl_koreksi) BETWEEN ? AND ?";
    $bind_params_data = ['ss', $tgl_mulai, $tgl_selesai];
    $bind_params_summary = ['ss', $tgl_mulai, $tgl_selesai];

    if ($kd_store != 'all' && $kd_store != 'SEMUA CABANG' && $kd_store != 'none') {
        $where_conditions .= " AND k.kd_store = ?";
        $bind_params_data[0] .= 's';
        $bind_params_data[] = $kd_store;
        $bind_params_summary[0] .= 's';
        $bind_params_summary[] = $kd_store;
    }

    $sql_calc_found_rows = "";
    $limit_offset_sql = "";
    if (!$is_export) {
        $sql_calc_found_rows = "SQL_CALC_FOUND_ROWS";
        $limit_offset_sql = "LIMIT ? OFFSET ?";
        $bind_params_data[0] .= 'ii';
        $bind_params_data[] = $limit;
        $bind_params_data[] = $offset;
    }

    $sql_data = "
        SELECT
            $sql_calc_found_rows
            DATE(k.tgl_koreksi) AS tanggal,
            k.no_faktur,
            k.plu,
            k.deskripsi,
            k.isi1 AS conv1,
            k.isi2 AS conv2,
            k.avg_cost AS hpp,
            k.qty_kor AS qtykor,
            k.stock,
            k.sel_qty AS selqty,
            (k.qty_kor * k.avg_cost) AS t_rp,
            (k.sel_qty * k.avg_cost) AS t_selisih,
            keterangan as ket,
            k.kode_supp,
            COALESCE(s.nama_supp, 'SUPPLIER LAIN/NON-AKTIF') AS nama_supp
        FROM
            koreksi AS k
        LEFT JOIN
            supplier AS s ON k.kode_supp = s.kode_supp AND k.kd_store = s.kd_store
        WHERE
            $where_conditions
        GROUP BY 
            tanggal, k.no_faktur, k.plu, k.deskripsi, k.isi1, k.isi2, k.avg_cost, k.qty_kor, k.stock, k.sel_qty, k.type_kor, k.kode_supp, s.nama_supp
        ORDER BY
            tanggal, nama_supp, k.kode_supp, k.plu
        $limit_offset_sql
    ";

    $stmt_data = $conn->prepare($sql_data);
    if ($stmt_data === false) {
        throw new Exception("Prepare failed (sql_data): " . $conn->error);
    }
    $stmt_data->bind_param(...$bind_params_data);
    $stmt_data->execute();
    $result_data = $stmt_data->get_result();

    while ($row = $result_data->fetch_assoc()) {
        foreach ($row as $key => $value) {
            if (is_string($value)) {
                $row[$key] = iconv('UTF-8', 'UTF-8//IGNORE', $value);
            }
        }
        $response['tabel_data'][] = $row;
    }
    $stmt_data->close();

    if (!$is_export) {
        $sql_count_total = "
            SELECT COUNT(DISTINCT DATE(k.tgl_koreksi), k.kode_supp, k.plu) AS total_rows
            FROM koreksi AS k
            LEFT JOIN supplier AS s ON k.kode_supp = s.kode_supp AND k.kd_store = s.kd_store
            WHERE $where_conditions
        ";
        $stmt_count = $conn->prepare($sql_count_total);
        if ($stmt_count === false) {
            throw new Exception("Prepare failed (sql_count_total): " . $conn->error);
        }
        $stmt_count->bind_param(...$bind_params_summary);
        $stmt_count->execute();
        $total_rows = $stmt_count->get_result()->fetch_assoc()['total_rows'] ?? 0;
        $stmt_count->close();

        $response['pagination']['total_rows'] = (int) $total_rows;
        $response['pagination']['total_pages'] = ceil($total_rows / $limit);
    }

    $sql_summary = "
        SELECT
            SUM(k.qty_kor) AS total_qtykor,
            SUM(k.qty_kor * k.avg_cost) AS total_rp_koreksi,
            SUM(k.sel_qty * k.avg_cost) AS total_rp_selisih
        FROM
            koreksi AS k
        WHERE
            $where_conditions
    ";

    $stmt_summary = $conn->prepare($sql_summary);
    if ($stmt_summary === false) {
        throw new Exception("Prepare failed (sql_summary): " . $conn->error);
    }
    $stmt_summary->bind_param(...$bind_params_summary);
    $stmt_summary->execute();
    $result_summary = $stmt_summary->get_result();
    $summary_data = $result_summary->fetch_assoc();
    $stmt_summary->close();

    if ($summary_data) {
        $response['summary']['total_qtykor'] = $summary_data['total_qtykor'] ?? 0;
        $response['summary']['total_rp_koreksi'] = $summary_data['total_rp_koreksi'] ?? 0;
        $response['summary']['total_rp_selisih'] = $summary_data['total_rp_selisih'] ?? 0;
    }

    $sql_date_summary = "
        SELECT
            DATE(k.tgl_koreksi) AS tanggal,
            SUM(k.qty_kor) AS total_qtykor,
            SUM(k.qty_kor * k.avg_cost) AS total_rp_koreksi,
            SUM(k.sel_qty * k.avg_cost) AS total_rp_selisih
        FROM
            koreksi AS k
        WHERE
            $where_conditions
        GROUP BY
            DATE(k.tgl_koreksi)
        ORDER BY
            tanggal
    ";

    $stmt_date_summary = $conn->prepare($sql_date_summary);
    $stmt_date_summary->bind_param(...$bind_params_summary);
    $stmt_date_summary->execute();
    $result_date_summary = $stmt_date_summary->get_result();

    while ($date_row = $result_date_summary->fetch_assoc()) {
        $response['date_subtotals'][$date_row['tanggal']] = [
            'total_qtykor' => $date_row['total_qtykor'] ?? 0,
            'total_rp_koreksi' => $date_row['total_rp_koreksi'] ?? 0,
            'total_rp_selisih' => $date_row['total_rp_selisih'] ?? 0,
        ];
    }
    $stmt_date_summary->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>