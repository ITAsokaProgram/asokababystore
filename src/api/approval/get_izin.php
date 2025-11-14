<?php
session_start();
include '../../../aa_kon_sett.php';
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
$is_export = $_GET['export'] ?? false;
$is_export = ($is_export === 'true' || $is_export === true);
$response = [
    'summary' => ['total_item' => 0, 'total_selisih' => 0, 'total_nilai' => 0],
    'stores' => [],
    'tabel_data' => [],
    'pagination' => ['current_page' => 1, 'total_pages' => 1, 'total_rows' => 0, 'offset' => 0, 'limit' => 10],
    'error' => null,
];
try {
    $tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));
    $tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-d', strtotime('-1 month'));
    $tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');
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
    $sql_stores = "SELECT Kd_Store as kd_store, Nm_Alias as nm_alias FROM kode_store ORDER BY Kd_Store ASC";
    $result_stores = $conn->query($sql_stores);
    if ($result_stores) {
        while ($row = $result_stores->fetch_assoc()) {
            $response['stores'][] = $row;
        }
    }
    $where_conditions = "DATE(a.tgl_koreksi) BETWEEN ? AND ?";
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
            a.tgl_koreksi,
            a.no_faktur,
            a.kd_store,
            b.Nm_Alias as nm_alias,
            a.plu,
            a.deskripsi,
            a.satuan,
            a.stock,
            a.qty_kor,
            a.sel_qty,
            a.avg_cost,
            a.Keterangan,
            a.izin_koreksi,
            a.kode_supp,
            a.status_k
        FROM koreksi_izin a
        LEFT JOIN kode_store b ON a.kd_store = b.Kd_Store
        WHERE 
            $where_conditions
        ORDER BY 
            a.kd_store ASC, a.tgl_koreksi DESC, a.no_faktur ASC
        $limit_offset_sql
    ";
    $stmt_data = $conn->prepare($sql_data);
    if ($stmt_data === false)
        throw new Exception("Prepare failed: " . $conn->error);
    $stmt_data->bind_param(...$bind_params_data);
    $stmt_data->execute();
    $result_data = $stmt_data->get_result();
    while ($row = $result_data->fetch_assoc()) {
        foreach ($row as $key => $value) {
            if (is_string($value))
                $row[$key] = iconv('UTF-8', 'UTF-8//IGNORE', $value);
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
    $sql_summary = "SELECT COUNT(*) AS total_item, SUM(a.sel_qty) AS total_selisih, SUM(a.sel_qty * a.avg_cost) AS total_nilai FROM koreksi_izin a WHERE $where_conditions";
    $stmt_summary = $conn->prepare($sql_summary);
    $stmt_summary->bind_param(...$bind_params_summary);
    $stmt_summary->execute();
    $result_summary = $stmt_summary->get_result();
    $summary_data = $result_summary->fetch_assoc();
    if ($summary_data) {
        $response['summary'] = $summary_data;
    }
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}
echo json_encode($response);
?>