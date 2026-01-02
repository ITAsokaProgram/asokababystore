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

$response = [
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
    $filter_type = $_GET['filter_type'] ?? 'month';
    $bulan = $_GET['bulan'] ?? date('m');
    $tahun = $_GET['tahun'] ?? date('Y');
    $tgl_mulai = $_GET['tgl_mulai'] ?? $tanggal_kemarin;
    $tgl_selesai = $_GET['tgl_selesai'] ?? $tanggal_kemarin;
    $search_supplier = $_GET['search_supplier'] ?? '';

    $page = (int) ($_GET['page'] ?? 1);
    if ($page < 1)
        $page = 1;
    $limit = 100;

    $response['pagination']['limit'] = $limit;
    $response['pagination']['current_page'] = $page;
    $offset = ($page - 1) * $limit;
    $response['pagination']['offset'] = $offset;

    // Filter Logic
    if ($filter_type === 'month') {
        $where_conditions = "MONTH(tgl_nota) = ? AND YEAR(tgl_nota) = ?";
        $bind_types = 'ss';
        $bind_params = [$bulan, $tahun];
    } else {
        $where_conditions = "DATE(tgl_nota) BETWEEN ? AND ?";
        $bind_types = 'ss';
        $bind_params = [$tgl_mulai, $tgl_selesai];
    }

    // Search Logic
    if (!empty($search_supplier)) {
        $search_raw = trim($search_supplier);
        $search_numeric = str_replace('.', '', $search_raw); // remove dots for currency search

        $where_conditions .= " AND (
            nama_supplier LIKE ? 
            OR no_nota LIKE ? 
            OR no_faktur LIKE ? 
            OR kode_supplier LIKE ? 
            OR CAST(nominal_awal AS CHAR) LIKE ?
        )";
        $bind_types .= 'sssss';
        $termRaw = '%' . $search_raw . '%';
        $termNumeric = '%' . $search_numeric . '%';

        $bind_params[] = $termRaw;
        $bind_params[] = $termRaw;
        $bind_params[] = $termRaw;
        $bind_params[] = $termRaw;
        $bind_params[] = $termNumeric;
    }

    // 1. Get Total Count
    $sql_count = "SELECT COUNT(id) as total FROM surat_terima_nota WHERE $where_conditions";
    $stmt_count = $conn->prepare($sql_count);
    if ($stmt_count === false)
        throw new Exception("Prepare failed (count): " . $conn->error);

    if (!empty($bind_params)) {
        $stmt_count->bind_param($bind_types, ...$bind_params);
    }

    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $total_rows = $result_count->fetch_assoc()['total'] ?? 0;
    $stmt_count->close();

    $response['pagination']['total_rows'] = (int) $total_rows;
    $response['pagination']['total_pages'] = ceil($total_rows / $limit);

    // 2. Get Data
    $sql_data = "SELECT * FROM surat_terima_nota 
                 WHERE $where_conditions 
                 ORDER BY tgl_nota DESC, dibuat_pada DESC 
                 LIMIT ? OFFSET ?";

    // Append limit/offset params
    $bind_types .= 'ii';
    $bind_params[] = $limit;
    $bind_params[] = $offset;

    $stmt_data = $conn->prepare($sql_data);
    if ($stmt_data === false)
        throw new Exception("Prepare failed (data): " . $conn->error);

    if (!empty($bind_params)) {
        $stmt_data->bind_param($bind_types, ...$bind_params);
    }

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
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>