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

// Verifikasi Token Bearer
$verif = authenticate_request();



$is_export = $_GET['export'] ?? false;
$is_export = ($is_export === 'true' || $is_export === true);

$response = [
    'summary' => [
        'total_qty' => 0,
        'total_netto' => 0,
        'total_ppn' => 0,
        'total_total' => 0,
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
    // --- START LOGIKA GET KODE CABANG BERDASARKAN USER ---
    $sqlUserCabang = "SELECT kd_store FROM user_account WHERE kode = ?";
    $stmtUserCabang = $conn->prepare($sqlUserCabang);
    $stmtUserCabang->bind_param("s", $verif->kode);
    $stmtUserCabang->execute();
    $resultUserCabang = $stmtUserCabang->get_result();

    $allowed_stores_list = [];
    if ($resultUserCabang->num_rows > 0) {
        $userCabang = $resultUserCabang->fetch_assoc();
        if ($userCabang['kd_store'] == "Pusat") {
            $sql_st = "SELECT Kd_Store as store, Nm_Alias as nama_cabang FROM kode_store WHERE display = 'on' ORDER BY Nm_Alias ASC";
            $stmt_st = $conn->prepare($sql_st);
            $stmt_st->execute();
        } else {
            $kdStoreArray = explode(',', $userCabang['kd_store']);
            $allowed_stores_list = $kdStoreArray;
            $kdStoreImplode = "'" . implode("','", $kdStoreArray) . "'";
            $sql_st = "SELECT Kd_Store as store, Nm_Alias as nama_cabang FROM kode_store WHERE Kd_Store IN ($kdStoreImplode) AND display = 'on' ORDER BY Nm_Alias ASC";
            $stmt_st = $conn->prepare($sql_st);
            $stmt_st->execute();
        }
        $res_st = $stmt_st->get_result();
        while ($row_st = $res_st->fetch_assoc()) {
            $response['stores'][] = [
                'kd_store' => $row_st['store'],
                'nm_alias' => $row_st['nama_cabang']
            ];
        }
        $stmt_st->close();
    }
    $stmtUserCabang->close();
    // --- END LOGIKA GET KODE CABANG ---

    $tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));
    $tgl_mulai = $_GET['tgl_mulai'] ?? $tanggal_kemarin;
    $tgl_selesai = $_GET['tgl_selesai'] ?? $tanggal_kemarin;
    $kd_store = $_GET['kd_store'] ?? 'all';
    $search_query = $_GET['search_query'] ?? '';

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

    // Build Where Condition
    $where_conditions = "DATE(a.tgl_tiba) BETWEEN ? AND ?";
    $bind_types = "ss";
    $bind_params = [$tgl_mulai, $tgl_selesai];

    // Filter berdasarkan input kd_store dan batasan user_account
    if ($kd_store != 'all' && $kd_store != 'SEMUA CABANG') {
        $where_conditions .= " AND a.kd_store = ?";
        $bind_types .= "s";
        $bind_params[] = $kd_store;
    } elseif (!empty($allowed_stores_list)) {
        // Jika user bukan pusat dan memilih 'all', batasi ke cabang miliknya saja
        $storePlaceholder = implode(',', array_fill(0, count($allowed_stores_list), '?'));
        $where_conditions .= " AND a.kd_store IN ($storePlaceholder)";
        $bind_types .= str_repeat('s', count($allowed_stores_list));
        $bind_params = array_merge($bind_params, $allowed_stores_list);
    }

    if (!empty($search_query)) {
        $where_conditions .= " AND (a.no_faktur LIKE ? OR a.plu LIKE ? OR a.barcode LIKE ?)";
        $search_term = "%" . $search_query . "%";
        $bind_types .= "sss";
        array_push($bind_params, $search_term, $search_term, $search_term);
    }

    // Query Data Utama
    $sql_calc_found_rows = !$is_export ? "SQL_CALC_FOUND_ROWS" : "";
    $limit_offset_sql = !$is_export ? "LIMIT ? OFFSET ?" : "";

    $sql_data = "
        SELECT
            $sql_calc_found_rows
            DATE(a.tgl_tiba) AS tgl_tiba, 
            a.no_faktur,
            a.no_lpb,
            a.jam,
            a.plu,
            a.descp AS deskripsi,
            a.satuan AS sat,
            a.conv1,
            a.conv2,
            a.qty_rec AS qty,
            (a.netto * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END)) AS netto,
            (IFNULL(a.ppn,0) * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END)) AS ppn,
            (
                (a.netto * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END)) +
                (IFNULL(a.ppn,0) * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END))
            ) AS total
        FROM
            receipt a
        LEFT JOIN
            (SELECT kode_supp, nama_supp FROM supplier GROUP BY kode_supp) b ON a.kode_supp = b.kode_supp
        WHERE
            $where_conditions
        GROUP BY
            DATE(a.tgl_tiba), a.no_faktur, a.no_lpb, a.plu, a.barcode, a.descp, a.satuan, a.conv1, a.conv2, a.no_ord, a.kode_supp, b.nama_supp, a.qty_rec, a.timbang, a.ppn_bm, a.netto, a.ppn, a.jam
        ORDER BY
            DATE(a.tgl_tiba) DESC, a.no_faktur, a.plu 
        $limit_offset_sql
    ";

    $stmt_data = $conn->prepare($sql_data);

    // Copy params for data query
    $final_bind_types = $bind_types;
    $final_bind_params = $bind_params;
    if (!$is_export) {
        $final_bind_types .= "ii";
        $final_bind_params[] = $limit;
        $final_bind_params[] = $offset;
    }

    $stmt_data->bind_param($final_bind_types, ...$final_bind_params);
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
        $sql_count_result = "SELECT FOUND_ROWS() AS total_rows";
        $result_count = $conn->query($sql_count_result);
        $total_rows = $result_count->fetch_assoc()['total_rows'] ?? 0;
        $response['pagination']['total_rows'] = (int) $total_rows;
        $response['pagination']['total_pages'] = ceil($total_rows / $limit);
    }

    // Summary Query
    $sql_summary = "
        SELECT
            SUM(a.qty_rec) AS total_qty,
            SUM(a.netto * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END)) AS total_netto,
            SUM(IFNULL(a.ppn,0) * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END)) AS total_ppn,
            SUM(
                (a.netto * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END)) +
                (IFNULL(a.ppn,0) * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END))
            ) AS total_total
        FROM
            receipt a
        WHERE
            $where_conditions
    ";
    $stmt_summary = $conn->prepare($sql_summary);
    $stmt_summary->bind_param($bind_types, ...$bind_params);
    $stmt_summary->execute();
    $result_summary = $stmt_summary->get_result();
    $summary_data = $result_summary->fetch_assoc();
    $stmt_summary->close();

    if ($summary_data) {
        $response['summary'] = [
            'total_qty' => $summary_data['total_qty'] ?? 0,
            'total_netto' => $summary_data['total_netto'] ?? 0,
            'total_ppn' => $summary_data['total_ppn'] ?? 0,
            'total_total' => $summary_data['total_total'] ?? 0,
        ];
    }

    // Date Subtotals Query
    $sql_date_summary = "
        SELECT
            DATE(a.tgl_tiba) AS tanggal, 
            SUM(a.qty_rec) AS total_qty,
            SUM(a.netto * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END)) AS total_netto,
            SUM(IFNULL(a.ppn,0) * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END)) AS total_ppn,
            SUM(
                (a.netto * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END)) +
                (IFNULL(a.ppn,0) * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END))
            ) AS total_total
        FROM
            receipt a
        WHERE
            $where_conditions
        GROUP BY
            DATE(a.tgl_tiba) 
        ORDER BY
            tanggal DESC
    ";
    $stmt_date_summary = $conn->prepare($sql_date_summary);
    $stmt_date_summary->bind_param($bind_types, ...$bind_params);
    $stmt_date_summary->execute();
    $result_date_summary = $stmt_date_summary->get_result();
    while ($date_row = $result_date_summary->fetch_assoc()) {
        $response['date_subtotals'][$date_row['tanggal']] = [
            'total_qty' => $date_row['total_qty'] ?? 0,
            'total_netto' => $date_row['total_netto'] ?? 0,
            'total_ppn' => $date_row['total_ppn'] ?? 0,
            'total_total' => $date_row['total_total'] ?? 0,
        ];
    }
    $stmt_date_summary->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}

$json_output = json_encode($response);
if ($json_output === false) {
    http_response_code(500);
    echo json_encode(['error' => "Gagal encode JSON: " . json_last_error_msg()]);
} else {
    echo $json_output;
}
?>