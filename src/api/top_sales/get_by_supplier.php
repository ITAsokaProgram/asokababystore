<?php
session_start();
include '../../../aa_kon_sett.php';

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR])) {
        if (!headers_sent()) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Fatal Server Error. Pesan: ' . $error['message']
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
        'total_gross_sales' => 0,
        'total_ppn' => 0,
        'total_total_diskon' => 0,
        'total_net_sales' => 0,
        'total_grs_margin' => 0,
        'total_hpp' => 0,
    ],
    'stores' => [],
    'tabel_data' => [],
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

    $sql_stores = "SELECT kd_store, nm_alias FROM kode_store ORDER BY kd_store ASC";
    $result_stores = $conn->query($sql_stores);
    if ($result_stores) {
        while ($row = $result_stores->fetch_assoc()) {
            $response['stores'][] = $row;
        }
    }

    $where_conditions = "a.tgl_trans BETWEEN ? AND ?";
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
            a.kode_supp,
            IFNULL(b.nama_supp, '-') AS nama_supp,
            SUM(
                CASE MOD(a.plu, 10)
                    WHEN 0 THEN a.qty * a.conv2
                    WHEN 1 THEN a.qty * (a.conv2 / a.conv1)
                    ELSE a.qty
                END
            ) AS qty,
            SUM(a.harga * a.qty) AS gross_sales,
            SUM(IFNULL(a.ppn * a.qty, 0)) AS ppn,
            (SUM((a.harga - a.hrg_promo) * a.qty)) + 
            (SUM((a.hrg_promo * (IFNULL(a.diskon1, 0) / 100)) * a.qty)) +
            (SUM((a.hrg_promo - (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100))) * (IFNULL(a.diskon2, 0) / 100) * a.qty)) +
            (SUM(a.diskon3 * a.qty)) AS total_diskon,
            (IFNULL(SUM(a.harga * a.qty) - SUM((a.harga - a.hrg_promo) * a.qty) - SUM((a.hrg_promo * (IFNULL(a.diskon1, 0) / 100)) * a.qty) - SUM((a.hrg_promo - (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100))) * (IFNULL(a.diskon2, 0) / 100) * a.qty), 0) - SUM(a.diskon3 * a.qty)) AS net_sales,
            SUM(a.avg_cost * a.qty) AS hpp,
            (( (SUM(a.hrg_promo * a.qty) - SUM((a.hrg_promo * (IFNULL(a.diskon1, 0) / 100)) * a.qty) - SUM((a.hrg_promo - (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100))) * (IFNULL(a.diskon2, 0) / 100) * a.qty)) - SUM(IFNULL(a.avg_cost, 0) * IFNULL(a.qty, 0)) - SUM(IFNULL(a.ppn * a.qty, 0)) - SUM(a.diskon3 * a.qty) )) AS grs_margin,
            ( ( ( (SUM(a.hrg_promo * a.qty) - SUM((a.hrg_promo * (IFNULL(a.diskon1, 0) / 100)) * a.qty) - SUM((a.hrg_promo - (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100))) * (IFNULL(a.diskon2, 0) / 100) * a.qty)) - SUM(IFNULL(a.avg_cost, 0) * IFNULL(a.qty, 0)) - SUM(IFNULL(a.ppn * a.qty, 0)) - SUM(a.diskon3 * a.qty) ) / NULLIF( ( (SUM(a.hrg_promo * a.qty) - SUM((a.hrg_promo * (IFNULL(a.diskon1, 0) / 100)) * a.qty) - SUM((a.hrg_promo - (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100))) * (IFNULL(a.diskon2, 0) / 100) * a.qty)) - SUM(IFNULL(a.ppn * a.qty, 0)) - SUM(a.diskon3 * a.qty) ), 0) ) * 100 ) AS margin_percent
        FROM
            trans_b a
            LEFT JOIN (
                SELECT kd_store, kode_supp, MAX(nama_supp) as nama_supp 
                FROM supplier 
                GROUP BY kd_store, kode_supp
            ) b ON a.kode_supp = b.kode_supp AND a.kd_store = b.kd_store
        WHERE
            $where_conditions
        GROUP BY
            a.kode_supp,
            b.nama_supp
        ORDER BY
            gross_sales DESC
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
            SUM(tbl.qty) AS total_qty,
            SUM(tbl.gross_sales) AS total_gross_sales,
            SUM(tbl.ppn) AS total_ppn,
            SUM(tbl.total_diskon) AS total_total_diskon,
            SUM(tbl.net_sales) AS total_net_sales,
            SUM(tbl.grs_margin) AS total_grs_margin,
            SUM(tbl.hpp) AS total_hpp
        FROM (
            SELECT
                SUM(
                    CASE MOD(a.plu, 10)
                        WHEN 0 THEN a.qty * a.conv2
                        WHEN 1 THEN a.qty * (a.conv2 / a.conv1)
                        ELSE a.qty
                    END
                ) AS qty,
                SUM(a.harga * a.qty) AS gross_sales,
                SUM(IFNULL(a.ppn * a.qty, 0)) AS ppn,
                (SUM((a.harga - a.hrg_promo) * a.qty)) + 
                (SUM((a.hrg_promo * (IFNULL(a.diskon1, 0) / 100)) * a.qty)) +
                (SUM((a.hrg_promo - (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100))) * (IFNULL(a.diskon2, 0) / 100) * a.qty)) +
                (SUM(a.diskon3 * a.qty)) AS total_diskon,
                (IFNULL(SUM(a.harga * a.qty) - SUM((a.harga - a.hrg_promo) * a.qty) - SUM((a.hrg_promo * (IFNULL(a.diskon1, 0) / 100)) * a.qty) - SUM((a.hrg_promo - (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100))) * (IFNULL(a.diskon2, 0) / 100) * a.qty), 0) - SUM(a.diskon3 * a.qty)) AS net_sales,
                SUM(a.avg_cost * a.qty) AS hpp,
                (( (SUM(a.hrg_promo * a.qty) - SUM((a.hrg_promo * (IFNULL(a.diskon1, 0) / 100)) * a.qty) - SUM((a.hrg_promo - (a.hrg_promo * (IFNULL(a.diskon1, 0) / 100))) * (IFNULL(a.diskon2, 0) / 100) * a.qty)) - SUM(IFNULL(a.avg_cost, 0) * IFNULL(a.qty, 0)) - SUM(IFNULL(a.ppn * a.qty, 0)) - SUM(a.diskon3 * a.qty) )) AS grs_margin
            FROM
                trans_b a
                LEFT JOIN (
                    SELECT kd_store, kode_supp, MAX(nama_supp) as nama_supp 
                    FROM supplier 
                    GROUP BY kd_store, kode_supp
                ) b ON a.kode_supp = b.kode_supp AND a.kd_store = b.kd_store
            WHERE
                $where_conditions
            GROUP BY
                a.kode_supp, b.nama_supp
        ) AS tbl
    ";


    $stmt_summary = $conn->prepare($sql_summary);
    $stmt_summary->bind_param(...$bind_params_summary);
    $stmt_summary->execute();
    $result_summary = $stmt_summary->get_result();
    $summary_data = $result_summary->fetch_assoc();
    $stmt_summary->close();


    if ($summary_data) {
        $response['summary']['total_qty'] = $summary_data['total_qty'] ?? 0;
        $response['summary']['total_gross_sales'] = $summary_data['total_gross_sales'] ?? 0;
        $response['summary']['total_ppn'] = $summary_data['total_ppn'] ?? 0;
        $response['summary']['total_total_diskon'] = $summary_data['total_total_diskon'] ?? 0;
        $response['summary']['total_net_sales'] = $summary_data['total_net_sales'] ?? 0;
        $response['summary']['total_grs_margin'] = $summary_data['total_grs_margin'] ?? 0;
        $response['summary']['total_hpp'] = $summary_data['total_hpp'] ?? 0;
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