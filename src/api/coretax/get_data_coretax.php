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
    $tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));
    $tgl_mulai = $_GET['tgl_mulai'] ?? $tanggal_kemarin;
    $tgl_selesai = $_GET['tgl_selesai'] ?? $tanggal_kemarin;
    $kd_store = $_GET['kd_store'] ?? 'all';
    $search_supplier = $_GET['search_supplier'] ?? '';

    $page = (int) ($_GET['page'] ?? 1);
    if ($page < 1)
        $page = 1;
    $limit = 100;

    $response['pagination']['limit'] = $limit;
    $response['pagination']['current_page'] = $page;
    $offset = ($page - 1) * $limit;
    $response['pagination']['offset'] = $offset;

    // Load Stores
    $sql_stores = "SELECT kd_store, nm_alias FROM kode_store WHERE display = 'on' ORDER BY Nm_Alias ASC";
    $result_stores = $conn->query($sql_stores);
    if ($result_stores) {
        while ($row = $result_stores->fetch_assoc()) {
            $response['stores'][] = $row;
        }
    }

    $where_conditions = "fc.tgl_faktur_pajak BETWEEN ? AND ?";
    $bind_types = 'ss';
    $bind_params = [$tgl_mulai, $tgl_selesai];

    if ($kd_store != 'all') {
        $where_conditions .= " AND fc.kode_store = ?";
        $bind_types .= 's';
        $bind_params[] = $kd_store;
    }

    if (!empty($search_supplier)) {
        $where_conditions .= " AND (fc.nama_penjual LIKE ? OR fc.npwp_penjual LIKE ? OR fc.nsfp LIKE ?)";
        $bind_types .= 'sss';
        $searchTerm = '%' . $search_supplier . '%';
        $bind_params[] = $searchTerm;
        $bind_params[] = $searchTerm;
        $bind_params[] = $searchTerm;
    }

    // Hitung Total Rows
    $sql_count = "SELECT COUNT(*) as total FROM ff_coretax fc WHERE $where_conditions";
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

    // Main Query dengan JOIN untuk status indikasi
    $sql_data = "
        SELECT 
            fc.npwp_penjual,
            fc.nama_penjual,
            fc.nsfp,
            fc.tgl_faktur_pajak,
            fc.masa_pajak,
            fc.tahun,
            fc.harga_jual,
            fc.dpp_nilai_lain,
            fc.ppn,
            fc.kode_store,
            ks.Nm_Alias,
            -- Indikasi Data
            IF(p.id IS NOT NULL, 1, 0) as ada_pembelian,
            IF(f.id IS NOT NULL, 1, 0) as ada_fisik
        FROM ff_coretax fc
        LEFT JOIN kode_store ks ON fc.kode_store = ks.Kd_Store
        -- Cek Pembelian (hanya yang sudah dikonfirmasi/linked)
        LEFT JOIN ff_pembelian p ON fc.nsfp = p.nsfp AND p.ada_di_coretax = 1
        -- Cek Fisik
        LEFT JOIN ff_faktur_pajak f ON fc.nsfp = f.nsfp
        WHERE $where_conditions
        ORDER BY RIGHT(fc.nsfp, 8) DESC, fc.nsfp ASC
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