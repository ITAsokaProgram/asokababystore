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


$response = [
    'stores' => [],
    'tabel_data' => [],
    'date_subtotals' => [],
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

    $tanggal_hari_ini = date('Y-m-d');
    $tanggal_bulan_lalu = date('Y-m-d', strtotime('-1 month'));

    $tgl_mulai = $_GET['tgl_mulai'] ?? $tanggal_bulan_lalu;
    $tgl_selesai = $_GET['tgl_selesai'] ?? $tanggal_hari_ini;
    $kd_store = $_GET['kd_store'] ?? 'all';

    $page = (int) ($_GET['page'] ?? 1);
    if ($page < 1)
        $page = 1;
    $limit = 10;

    $response['pagination']['limit'] = $limit;
    $response['pagination']['current_page'] = $page;

    $offset = ($page - 1) * $limit;
    $response['pagination']['offset'] = $offset;


    $sql_stores = "SELECT kd_store, nm_alias FROM kode_store ORDER BY kd_store ASC";
    $result_stores = $conn->query($sql_stores);
    if ($result_stores) {
        while ($row = $result_stores->fetch_assoc()) {
            $response['stores'][] = $row;
        }
    }


    $where_conditions = "DATE(tgl_awal) BETWEEN ? AND ?";
    $bind_params_data = ['ss', $tgl_mulai, $tgl_selesai];
    $bind_params_summary = ['ss', $tgl_mulai, $tgl_selesai];

    if ($kd_store != 'all') {
        $where_conditions .= " AND kd_store = ?";
        $bind_params_data[0] .= 's';
        $bind_params_data[] = $kd_store;
        $bind_params_summary[0] .= 's';
        $bind_params_summary[] = $kd_store;
    }


    $sql_calc_found_rows = "SQL_CALC_FOUND_ROWS";
    $limit_offset_sql = "LIMIT ? OFFSET ?";
    $bind_params_data[0] .= 'ii';
    $bind_params_data[] = $limit;
    $bind_params_data[] = $offset;

    $sql_data = "
        SELECT 
            $sql_calc_found_rows
            kd_voucher,
            nilai,
            pakai,
            sisa,
            tgl_awal,
            tgl_akhir,
            kd_cust,
            flag,
            tgl_beli,
            last_sold,
            pemilik,
            kd_store
        FROM voucher
        WHERE 
            $where_conditions
        ORDER BY 
            tgl_awal DESC, kd_voucher ASC  
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


    $sql_count_result = "SELECT FOUND_ROWS() AS total_rows";
    $result_count = $conn->query($sql_count_result);
    $total_rows = $result_count->fetch_assoc()['total_rows'] ?? 0;
    $response['pagination']['total_rows'] = (int) $total_rows;
    $response['pagination']['total_pages'] = ceil($total_rows / $limit);




    $sql_date_summary = "
        SELECT 
            DATE(tgl_awal) AS tanggal,
            SUM(nilai) AS total_nilai,
            SUM(pakai) AS total_pakai,
            SUM(sisa) AS total_sisa
        FROM 
            voucher
        WHERE 
            $where_conditions
        GROUP BY 
            DATE(tgl_awal)
        ORDER BY 
            tanggal
    ";

    $stmt_date_summary = $conn->prepare($sql_date_summary);
    if ($stmt_date_summary === false) {
        throw new Exception("Prepare failed (sql_date_summary): " . $conn->error);
    }

    $stmt_date_summary->bind_param(...$bind_params_summary);
    $stmt_date_summary->execute();
    $result_date_summary = $stmt_date_summary->get_result();

    while ($date_row = $result_date_summary->fetch_assoc()) {
        $tgl_key = $date_row['tanggal'] ?? '0000-00-00';
        if (!$tgl_key)
            $tgl_key = 'unknown';

        $response['date_subtotals'][$tgl_key] = [
            'total_nilai' => $date_row['total_nilai'] ?? 0,
            'total_pakai' => $date_row['total_pakai'] ?? 0,
            'total_sisa' => $date_row['total_sisa'] ?? 0,
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