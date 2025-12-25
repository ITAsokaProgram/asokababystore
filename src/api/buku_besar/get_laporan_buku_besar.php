<?php
session_start();
include '../../../aa_kon_sett.php';

// Error Handler
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR])) {
        if (!headers_sent()) {
            http_response_code(500);
            echo json_encode(['error' => 'Fatal Server Error: ' . $error['message']]);
        }
    }
});

header('Content-Type: application/json');

$response = [
    'stores' => [],
    'tabel_data' => [],
    'pagination' => ['current_page' => 1, 'total_pages' => 1, 'total_rows' => 0, 'offset' => 0, 'limit' => 100],
    'error' => null,
];

try {
    // 1. Get Stores
    $sql_stores = "SELECT kd_store, nm_alias FROM kode_store WHERE display = 'on' ORDER BY Nm_Alias ASC";
    $result_stores = $conn->query($sql_stores);
    if ($result_stores) {
        while ($row = $result_stores->fetch_assoc()) {
            $response['stores'][] = $row;
        }
    }

    // 2. Prepare Filters
    $filter_type = $_GET['filter_type'] ?? 'month';
    $bulan = $_GET['bulan'] ?? date('m');
    $tahun = $_GET['tahun'] ?? date('Y');
    $tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-d');
    $tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');
    $search_query = $_GET['search_query'] ?? '';
    $kd_store = $_GET['kd_store'] ?? 'all';

    $page = (int) ($_GET['page'] ?? 1);
    if ($page < 1)
        $page = 1;
    $limit = 100;
    $offset = ($page - 1) * $limit;

    $response['pagination']['limit'] = $limit;
    $response['pagination']['current_page'] = $page;
    $response['pagination']['offset'] = $offset;

    // WHERE Logic
    $where_conditions = "1=1";
    $bind_types = "";
    $bind_params = [];

    if ($filter_type === 'month') {
        $where_conditions .= " AND MONTH(bb.tgl_nota) = ? AND YEAR(bb.tgl_nota) = ?";
        $bind_types .= 'ss';
        $bind_params[] = $bulan;
        $bind_params[] = $tahun;
    } else {
        $where_conditions .= " AND DATE(bb.tgl_nota) BETWEEN ? AND ?";
        $bind_types .= 'ss';
        $bind_params[] = $tgl_mulai;
        $bind_params[] = $tgl_selesai;
    }

    if ($kd_store != 'all') {
        $where_conditions .= " AND bb.kode_store = ?";
        $bind_types .= 's';
        $bind_params[] = $kd_store;
    }

    if (!empty($search_query)) {
        $search_raw = trim($search_query);
        $search_numeric = str_replace(['.', ','], '', $search_raw);

        $where_conditions .= " AND (
            bb.nama_supplier LIKE ? 
            OR bb.kode_supplier LIKE ? 
            OR bb.no_faktur LIKE ? 
            OR bb.ket LIKE ? 
            OR CAST(bb.total_bayar AS CHAR) LIKE ?
            OR CAST(bb.potongan AS CHAR) LIKE ?
        )";
        $termRaw = '%' . $search_raw . '%';
        $termNumeric = '%' . $search_numeric . '%';

        $bind_types .= 'ssssss';
        $bind_params[] = $termRaw;
        $bind_params[] = $termRaw;
        $bind_params[] = $termRaw;
        $bind_params[] = $termRaw;
        $bind_params[] = $termNumeric;
        $bind_params[] = $termNumeric;
    }

    // 3. Count Total Rows
    $sql_count = "SELECT COUNT(id) as total FROM buku_besar bb WHERE $where_conditions";
    $stmt_count = $conn->prepare($sql_count);
    if (!empty($bind_params)) {
        $stmt_count->bind_param($bind_types, ...$bind_params);
    }
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $total_rows = $result_count->fetch_assoc()['total'] ?? 0;
    $stmt_count->close();

    $response['pagination']['total_rows'] = (int) $total_rows;
    $response['pagination']['total_pages'] = ceil($total_rows / $limit);

    // 4. Fetch Data
    $sql_data = "
        SELECT 
            bb.*,
            ks.Nm_Alias
        FROM buku_besar bb
        LEFT JOIN kode_store ks ON bb.kode_store = ks.Kd_Store
        WHERE $where_conditions
        ORDER BY bb.tgl_nota DESC, bb.id DESC
        LIMIT ? OFFSET ?
    ";

    // Append Limit params
    $bind_types .= 'ii';
    $bind_params[] = $limit;
    $bind_params[] = $offset;

    $stmt_data = $conn->prepare($sql_data);
    if (!empty($bind_params)) {
        $stmt_data->bind_param($bind_types, ...$bind_params);
    }
    $stmt_data->execute();
    $result_data = $stmt_data->get_result();

    while ($row = $result_data->fetch_assoc()) {
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