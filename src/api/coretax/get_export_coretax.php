<?php
session_start();
include '../../../aa_kon_sett.php';

// Error Handling Global
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
    'data' => [],
    'error' => null,
];

try {
    // 1. Parameter Input
    $tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));
    $tgl_mulai = $_GET['tgl_mulai'] ?? $tanggal_kemarin;
    $tgl_selesai = $_GET['tgl_selesai'] ?? $tanggal_kemarin;
    $kd_store = $_GET['kd_store'] ?? 'all';
    $status_data = $_GET['status_data'] ?? 'all';

    // Pembersihan input search
    $search_raw = trim($_GET['search_supplier'] ?? '');
    $search_number = str_replace(['.', ','], '', $search_raw);

    // 2. Build Query Conditions
    $where_conditions = "fc.tgl_faktur_pajak BETWEEN ? AND ?";
    $bind_types = 'ss';
    $bind_params = [$tgl_mulai, $tgl_selesai];

    // Filter Store
    if ($kd_store != 'all') {
        $where_conditions .= " AND fc.kode_store = ?";
        $bind_types .= 's';
        $bind_params[] = $kd_store;
    }

    // Filter Search
    if (!empty($search_raw)) {
        $where_conditions .= " AND (
            fc.nama_penjual LIKE ? 
            OR fc.npwp_penjual LIKE ? 
            OR fc.nsfp LIKE ?
            OR fc.perekam LIKE ?
            OR CAST(fc.harga_jual AS CHAR) LIKE ?
            OR CAST(fc.dpp_nilai_lain AS CHAR) LIKE ?
            OR CAST(fc.ppn AS CHAR) LIKE ?
        )";
        $bind_types .= 'sssssss';
        $searchTermRaw = '%' . $search_raw . '%';
        $searchTermNum = '%' . $search_number . '%';
        $bind_params[] = $searchTermRaw;
        $bind_params[] = $searchTermRaw;
        $bind_params[] = $searchTermRaw;
        $bind_params[] = $searchTermRaw;
        $bind_params[] = $searchTermNum;
        $bind_params[] = $searchTermNum;
        $bind_params[] = $searchTermNum;
    }

    // Filter Status
    $status_condition = "";
    if ($status_data != 'all') {
        if ($status_data == 'linked_both') {
            $status_condition = " AND p.id IS NOT NULL AND f.id IS NOT NULL";
        } elseif ($status_data == 'linked_pembelian') {
            $status_condition = " AND p.id IS NOT NULL";
        } elseif ($status_data == 'linked_fisik') {
            $status_condition = " AND f.id IS NOT NULL";
        } elseif ($status_data == 'unlinked_pembelian') {
            $status_condition = " AND p.id IS NULL";
        }
    }

    // 3. Main Query Data (TANPA LIMIT/OFFSET)
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
            IF(p.id IS NOT NULL, 1, 0) as ada_pembelian,
            IF(f.id IS NOT NULL, 1, 0) as ada_fisik
        FROM ff_coretax fc
        LEFT JOIN kode_store ks ON fc.kode_store = ks.Kd_Store
        LEFT JOIN ff_pembelian p ON fc.nsfp = p.nsfp AND p.ada_di_coretax = 1
        LEFT JOIN ff_faktur_pajak f ON fc.nsfp = f.nsfp
        WHERE $where_conditions $status_condition
        ORDER BY fc.tgl_faktur_pajak DESC, fc.nsfp ASC
    ";

    $stmt_data = $conn->prepare($sql_data);
    if ($stmt_data === false)
        throw new Exception("Prepare failed: " . $conn->error);

    $stmt_data->bind_param($bind_types, ...$bind_params);
    $stmt_data->execute();
    $result_data = $stmt_data->get_result();

    while ($row = $result_data->fetch_assoc()) {
        foreach ($row as $key => $value) {
            if (is_string($value)) {
                $row[$key] = iconv('UTF-8', 'UTF-8//IGNORE', $value);
            }
        }
        $response['data'][] = $row;
    }

    $stmt_data->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>