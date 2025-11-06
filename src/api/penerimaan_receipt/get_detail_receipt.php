<?php
session_start();
include '../../../aa_kon_sett.php';


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
    'pagination' => [
        'current_page' => 1,
        'total_pages' => 1,
        'total_rows' => 0,
        'offset' => 0,
        'limit' => 10,
    ],
    'error' => null,
];

try {

    $tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));
    $tgl_mulai = $_GET['tgl_mulai'] ?? $tanggal_kemarin;
    $tgl_selesai = $_GET['tgl_selesai'] ?? $tanggal_kemarin;
    $kd_store = $_GET['kd_store'] ?? 'all';

    $page = 1;
    $limit = 10;

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


    $sql_stores = "SELECT kd_store, nm_alias FROM kode_store ORDER BY kd_store ASC";
    $result_stores = $conn->query($sql_stores);
    if ($result_stores) {
        while ($row = $result_stores->fetch_assoc()) {
            $response['stores'][] = $row;
        }
    }


    $where_conditions = "a.tgl_tiba BETWEEN ? AND ?";
    $bind_params_data = ['ss', $tgl_mulai, $tgl_selesai];
    $bind_params_summary = ['ss', $tgl_mulai, $tgl_selesai];

    if ($kd_store != 'all') {
        $where_conditions .= " AND a.kd_store = ?";
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
            a.tgl_tiba, -- DITAMBAHKAN UNTUK GROUPING TANGGAL
            a.no_faktur,
            a.plu,
            a.descp AS deskripsi,
            a.satuan AS sat,
            a.conv1,
            a.conv2,
            a.qty_rec AS qty,
            (a.netto * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END)) AS netto,
            (IFNULL(a.ppn,0) * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END)) AS ppn,
            -- (IFNULL(a.ppn_bm,0) * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END)) AS ppnbm, -- Dihapus
            (
                (a.netto * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END)) +
                (IFNULL(a.ppn,0) * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END))
                -- (IFNULL(a.ppn_bm,0) * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END)) -- Dihapus
            ) AS total
        FROM
            receipt a
        LEFT JOIN
            (SELECT kode_supp, nama_supp FROM supplier GROUP BY kode_supp) b ON a.kode_supp = b.kode_supp
        WHERE
            $where_conditions
        GROUP BY
            a.tgl_tiba, a.no_faktur, a.no_lpb, a.plu, a.descp, a.satuan, a.conv1, a.conv2, a.no_ord, a.kode_supp, b.nama_supp, a.qty_rec, a.timbang, a.ppn_bm, a.netto, a.ppn
        ORDER BY
            a.tgl_tiba, a.no_faktur, a.plu -- DIUBAH: Urutkan berdasarkan tgl_tiba dulu
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
        $sql_count_result = "SELECT FOUND_ROWS() AS total_rows";
        $result_count = $conn->query($sql_count_result);
        $total_rows = $result_count->fetch_assoc()['total_rows'] ?? 0;
        $response['pagination']['total_rows'] = (int) $total_rows;
        $response['pagination']['total_pages'] = ceil($total_rows / $limit);

        if ($page > $response['pagination']['total_pages'] && $total_rows > 0) {
            $page = $response['pagination']['total_pages'];
            $response['pagination']['current_page'] = $page;
            $offset = ($page - 1) * $limit;
            $response['pagination']['offset'] = $offset;
        }
    }


    $sql_summary = "
        SELECT
            SUM(a.qty_rec) AS total_qty,
            SUM(a.netto * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END)) AS total_netto,
            SUM(IFNULL(a.ppn,0) * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END)) AS total_ppn,
            -- SUM(IFNULL(a.ppn_bm,0) * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END)) AS total_ppnbm, -- Dihapus
            SUM(
                (a.netto * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END)) +
                (IFNULL(a.ppn,0) * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END))
                -- (IFNULL(a.ppn_bm,0) * (CASE WHEN a.timbang='true' THEN a.qty_rec/1000 ELSE a.qty_rec END)) -- Dihapus
            ) AS total_total
        FROM
            receipt a
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
        $response['summary']['total_qty'] = $summary_data['total_qty'] ?? 0;
        $response['summary']['total_netto'] = $summary_data['total_netto'] ?? 0;
        $response['summary']['total_ppn'] = $summary_data['total_ppn'] ?? 0;
        $response['summary']['total_total'] = $summary_data['total_total'] ?? 0;
    }

    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}


$json_output = json_encode($response);
if ($json_output === false) {
    $json_error_code = json_last_error();
    $json_error_msg = json_last_error_msg();
    http_response_code(500);
    echo json_encode([
        'error' => "Gagal melakukan encode JSON. Pesan: " . $json_error_msg,
        'json_error_code' => $json_error_code
    ]);
} else {
    echo $json_output;
}
?>