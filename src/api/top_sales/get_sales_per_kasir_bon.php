<?php
session_start();
include '../../../aa_kon_sett.php';
require_once __DIR__ . "./../../auth/middleware_login.php";

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

// --- BAGIAN AUTH & GET KODE CABANG (DISESUAIKAN) ---
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
$stores_list = [];

$sqlUserCabang = "SELECT kd_store FROM user_account WHERE kode = ?";
$stmtUserCabang = $conn->prepare($sqlUserCabang);
$stmtUserCabang->bind_param("s", $verif->kode);
$stmtUserCabang->execute();
$resultUserCabang = $stmtUserCabang->get_result();

if ($resultUserCabang->num_rows > 0) {
    $userCabang = $resultUserCabang->fetch_assoc();
    if ($userCabang['kd_store'] == "Pusat") {
        $sql_stores = "SELECT Kd_Store as kd_store, Nm_Alias as nm_alias FROM kode_store WHERE display = 'on' ORDER BY Nm_Alias ASC";
        $stmt_s = $conn->prepare($sql_stores);
    } else {
        $kdStoreArray = explode(',', $userCabang['kd_store']);
        $kdStoreImplode = "'" . implode("','", $kdStoreArray) . "'";
        $sql_stores = "SELECT Kd_Store as kd_store, Nm_Alias as nm_alias FROM kode_store WHERE Kd_Store IN ($kdStoreImplode) AND display = 'on' ORDER BY Nm_Alias ASC";
        $stmt_s = $conn->prepare($sql_stores);
    }
    $stmt_s->execute();
    $res_s = $stmt_s->get_result();
    while ($row_s = $res_s->fetch_assoc()) {
        $stores_list[] = $row_s;
    }
    $stmt_s->close();
}
// --- END BAGIAN GET KODE CABANG ---

$is_export = $_GET['export'] ?? false;
$is_export = ($is_export === 'true' || $is_export === true);

$response = [
    'summary' => [
        'total_qty' => 0,
        'total_total_diskon' => 0,
        'total_total' => 0,
        'total_net_sales' => 0,
        'total_grs_margin' => 0,
        'total_hpp' => 0,
    ],
    'stores' => $stores_list, // Menggunakan hasil filter di atas
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

    $join_sql = " LEFT JOIN supplier s ON a.kode_supp = s.kode_supp AND a.kd_store = s.kd_store ";
    $where_conditions = "a.tgl_trans BETWEEN ? AND ?";
    $bind_params_data = ['ss', $tgl_mulai, $tgl_selesai];
    $bind_params_summary = ['ss', $tgl_mulai, $tgl_selesai];

    if ($kd_store != 'all' && $kd_store != 'SEMUA CABANG') {
        $where_conditions .= " AND a.kd_store = ?";
        $bind_params_data[0] .= 's';
        $bind_params_data[] = $kd_store;
        $bind_params_summary[0] .= 's';
        $bind_params_summary[] = $kd_store;
    }

    $search_keyword = $_GET['search'] ?? '';
    if (!empty($search_keyword)) {
        $where_conditions .= " AND (a.plu LIKE ? OR a.descp LIKE ? OR a.no_bon LIKE ? OR a.kode_supp LIKE ? OR s.nama_supp LIKE ?)";
        $search_param = "%" . $search_keyword . "%";
        $search_types = 'sssss';
        $bind_params_data[0] .= $search_types;
        for ($i = 0; $i < 5; $i++)
            $bind_params_data[] = $search_param;
        $bind_params_summary[0] .= $search_types;
        for ($i = 0; $i < 5; $i++)
            $bind_params_summary[] = $search_param;
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
            DATE(a.tgl_trans) AS tanggal,
            a.kode_kasir,
            a.jam_trs,
            a.nama_kasir,
            a.no_bon,
            FLOOR(a.plu / 10) AS plu,
            a.descp AS nama_barang,
            a.kode_supp,
            s.nama_supp,
            (CASE MOD(a.plu, 10)
                WHEN 0 THEN a.qty * a.conv2
                WHEN 1 THEN a.qty * (a.conv2 / a.conv1)
                ELSE a.qty
            END) AS qty,
            a.harga,
            ((a.harga - a.hrg_promo) * a.qty) + 
            (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100) * a.qty) +
            ((a.hrg_promo - (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100))) * (IFNULL(a.diskon2, 0) / 100) * a.qty) +
            (a.diskon3 * a.qty) AS total_diskon,
            (IFNULL((a.harga * a.qty) - ((a.harga - a.hrg_promo) * a.qty) - (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100) * a.qty) - ((a.hrg_promo - (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100))) * (IFNULL(a.diskon2, 0) / 100) * a.qty), 0) - (a.diskon3 * a.qty)) AS total
        FROM
            trans_b a
        $join_sql
        WHERE
            $where_conditions
        ORDER BY
            tanggal ASC, a.no_bon ASC, a.kode_kasir ASC
        $limit_offset_sql    
    ";

    $stmt_data = $conn->prepare($sql_data);
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
        $sql_count_total = "SELECT COUNT(*) AS total_rows FROM trans_b a $join_sql WHERE $where_conditions";
        $stmt_count = $conn->prepare($sql_count_total);
        $stmt_count->bind_param(...$bind_params_summary);
        $stmt_count->execute();
        $total_rows = $stmt_count->get_result()->fetch_assoc()['total_rows'] ?? 0;
        $stmt_count->close();
        $response['pagination']['total_rows'] = (int) $total_rows;
        $response['pagination']['total_pages'] = ceil($total_rows / $limit);
    }

    $sql_summary = "
        SELECT
            SUM((IFNULL((a.harga * a.qty) - ((a.harga - a.hrg_promo) * a.qty) - (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100) * a.qty) - ((a.hrg_promo - (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100))) * (IFNULL(a.diskon2, 0) / 100) * a.qty), 0) - (a.diskon3 * a.qty))) AS total_net_sales,
            SUM((a.avg_cost * a.qty)) AS total_hpp,
            SUM((( (a.hrg_promo * a.qty) - (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100) * a.qty) - ((a.hrg_promo - (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100))) * (IFNULL(a.diskon2, 0) / 100) * a.qty) ) - (IFNULL(a.avg_cost, 0) * IFNULL(a.qty, 0)) - (IFNULL(a.ppn * a.qty, 0)) - (a.diskon3 * a.qty))) AS total_grs_margin,
            SUM((CASE MOD(a.plu, 10) WHEN 0 THEN a.qty * a.conv2 WHEN 1 THEN a.qty * (a.conv2 / a.conv1) ELSE a.qty END)) AS total_qty,
            SUM((((a.harga - a.hrg_promo) * a.qty) + (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100) * a.qty) + ((a.hrg_promo - (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100))) * (IFNULL(a.diskon2, 0) / 100) * a.qty) + (a.diskon3 * a.qty))) AS total_total_diskon
        FROM trans_b a $join_sql WHERE $where_conditions
    ";
    $stmt_summary = $conn->prepare($sql_summary);
    $stmt_summary->bind_param(...$bind_params_summary);
    $stmt_summary->execute();
    $summary_data = $stmt_summary->get_result()->fetch_assoc();
    $stmt_summary->close();

    if ($summary_data) {
        $response['summary']['total_net_sales'] = $summary_data['total_net_sales'] ?? 0;
        $response['summary']['total_grs_margin'] = $summary_data['total_grs_margin'] ?? 0;
        $response['summary']['total_hpp'] = $summary_data['total_hpp'] ?? 0;
        $response['summary']['total_qty'] = $summary_data['total_qty'] ?? 0;
        $response['summary']['total_total_diskon'] = $summary_data['total_total_diskon'] ?? 0;
        $response['summary']['total_total'] = $summary_data['total_net_sales'] ?? 0;
    }

    $sql_date_summary = "
        SELECT
            DATE(a.tgl_trans) AS tanggal,
            SUM((CASE MOD(a.plu, 10) WHEN 0 THEN a.qty * a.conv2 WHEN 1 THEN a.qty * (a.conv2 / a.conv1) ELSE a.qty END)) AS total_qty,
            SUM((((a.harga - a.hrg_promo) * a.qty) + (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100) * a.qty) + ((a.hrg_promo - (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100))) * (IFNULL(a.diskon2, 0) / 100) * a.qty) + (a.diskon3 * a.qty))) AS total_total_diskon,
            SUM((IFNULL((a.harga * a.qty) - ((a.harga - a.hrg_promo) * a.qty) - (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100) * a.qty) - ((a.hrg_promo - (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100))) * (IFNULL(a.diskon2, 0) / 100) * a.qty), 0) - (a.diskon3 * a.qty))) AS total_total
        FROM trans_b a $join_sql WHERE $where_conditions GROUP BY DATE(a.tgl_trans) ORDER BY tanggal
    ";
    $stmt_date_summary = $conn->prepare($sql_date_summary);
    $stmt_date_summary->bind_param(...$bind_params_summary);
    $stmt_date_summary->execute();
    $result_date_summary = $stmt_date_summary->get_result();
    while ($date_row = $result_date_summary->fetch_assoc()) {
        $response['date_subtotals'][$date_row['tanggal']] = [
            'total_qty' => $date_row['total_qty'] ?? 0,
            'total_total_diskon' => $date_row['total_total_diskon'] ?? 0,
            'total_total' => $date_row['total_total'] ?? 0,
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