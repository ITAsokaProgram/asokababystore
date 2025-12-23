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
    $today = date('Y-m-d');
    $first_day = date('Y-m-01');

    $tgl_mulai = $_GET['tgl_mulai'] ?? $first_day;
    $tgl_selesai = $_GET['tgl_selesai'] ?? $today;
    $search_raw = trim($_GET['search_supplier'] ?? '');
    $search_number = str_replace('.', '', $search_raw);
    $kd_store = $_GET['kd_store'] ?? 'all';
    $status_data = $_GET['status_data'] ?? 'all';
    $page = (int) ($_GET['page'] ?? 1);
    if ($page < 1)
        $page = 1;
    $limit = 100;
    $response['pagination']['limit'] = $limit;
    $response['pagination']['current_page'] = $page;
    $offset = ($page - 1) * $limit;
    $response['pagination']['offset'] = $offset;
    $sql_stores = "SELECT kd_store, nm_alias FROM kode_store WHERE display = 'on' ORDER BY Nm_Alias ASC";
    $result_stores = $conn->query($sql_stores);
    if ($result_stores) {
        while ($row = $result_stores->fetch_assoc()) {
            $response['stores'][] = $row;
        }
    }
    $where_conditions = "DATE(f.tgl_faktur) BETWEEN ? AND ?";
    $bind_types = 'ss';
    $bind_params = [$tgl_mulai, $tgl_selesai];
    if ($kd_store != 'all') {
        $where_conditions .= " AND f.kode_store = ?";
        $bind_types .= 's';
        $bind_params[] = $kd_store;
    }
    if (!empty($search_raw)) {
        $where_conditions .= " AND (
            f.nama_supplier LIKE ? 
            OR f.nsfp LIKE ? 
            OR f.no_invoice LIKE ?
            OR CAST(f.total AS CHAR) LIKE ?
            OR CAST(f.dpp AS CHAR) LIKE ?
            OR CAST(f.dpp_nilai_lain AS CHAR) LIKE ?
            OR CAST(f.ppn AS CHAR) LIKE ?
        )";
        $bind_types .= 'sssssss';
        $termRaw = '%' . $search_raw . '%';
        $termNum = '%' . $search_number . '%';
        $bind_params[] = $termRaw;
        $bind_params[] = $termRaw;
        $bind_params[] = $termRaw;
        $bind_params[] = $termNum;
        $bind_params[] = $termNum;
        $bind_params[] = $termNum;
        $bind_params[] = $termNum;
    }
    $status_condition = "";
    if ($status_data != 'all') {
        if ($status_data == 'linked_pembelian') {
            $status_condition = " AND p.id IS NOT NULL";
        } elseif ($status_data == 'linked_coretax') {
            $status_condition = " AND c.nsfp IS NOT NULL";
        } elseif ($status_data == 'linked_both') {
            $status_condition = " AND p.id IS NOT NULL AND c.nsfp IS NOT NULL";
        } elseif ($status_data == 'unlinked_pembelian') {
            $status_condition = " AND p.id IS NULL";
        }
    }
    $sql_count = "SELECT COUNT(DISTINCT f.id) as total 
                  FROM ff_faktur_pajak f
                  LEFT JOIN ff_pembelian p ON f.nsfp = p.nsfp AND p.ada_di_coretax = 1
                  LEFT JOIN ff_coretax c ON f.nsfp = c.nsfp
                  WHERE $where_conditions $status_condition";
    $stmt_count = $conn->prepare($sql_count);
    if ($stmt_count === false)
        throw new Exception("Prepare failed (count): " . $conn->error);
    $stmt_count->bind_param($bind_types, ...$bind_params);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $total_rows = $result_count->fetch_assoc()['total'] ?? 0;
    $stmt_count->close();
    $response['pagination']['total_rows'] = (int) $total_rows;
    $response['pagination']['total_pages'] = ceil($total_rows / $limit);
    $sql_data = "
        SELECT 
            f.id,
            f.nsfp, 
            f.no_invoice,
            f.tgl_faktur, 
            f.nama_supplier, 
            f.dpp, 
            f.dpp_nilai_lain, 
            f.ppn, 
            f.total,
            f.kode_store,
            ks.Nm_Alias,
            -- Indikator Status
            IF(p.id IS NOT NULL, 1, 0) as ada_pembelian,
            IF(c.nsfp IS NOT NULL, 1, 0) as ada_coretax
        FROM ff_faktur_pajak f
        LEFT JOIN kode_store ks ON f.kode_store = ks.Kd_Store
        -- Link Pembelian (Hanya yg sudah confirmed di coretax)
        LEFT JOIN ff_pembelian p ON f.nsfp = p.nsfp AND p.ada_di_coretax = 1
        -- Link Coretax
        LEFT JOIN ff_coretax c ON f.nsfp = c.nsfp
        WHERE $where_conditions $status_condition
        ORDER BY f.tgl_faktur DESC, f.nsfp ASC
        LIMIT ? OFFSET ?
    ";
    $bind_types .= 'ii';
    $bind_params[] = $limit;
    $bind_params[] = $offset;
    $stmt_data = $conn->prepare($sql_data);
    if ($stmt_data === false)
        throw new Exception("Prepare failed (data): " . $conn->error);
    $stmt_data->bind_param($bind_types, ...$bind_params);
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