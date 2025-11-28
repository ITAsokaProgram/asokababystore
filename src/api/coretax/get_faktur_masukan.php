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
        'limit' => 10,
    ],
    'error' => null,
];

try {
    $tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));
    $tgl_mulai = $_GET['tgl_mulai'] ?? $tanggal_kemarin;
    $tgl_selesai = $_GET['tgl_selesai'] ?? $tanggal_kemarin;
    $kd_store = $_GET['kd_store'] ?? 'all';

    // BERSIHKAN INPUT
    $search_raw = trim($_GET['search_supplier'] ?? '');
    // Hapus titik (untuk format 15.000.000) dan koma (jaga-jaga) agar jadi angka murni
    $search_number = str_replace(['.', ','], '', $search_raw);

    $filter_ppn = $_GET['filter_ppn'] ?? 'all';

    $page = (int) ($_GET['page'] ?? 1);
    if ($page < 1)
        $page = 1;
    $limit = 10;

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

    $where_conditions = "DATE(rh.tgl_tiba) BETWEEN ? AND ?";
    $bind_types = 'ss';
    $bind_params = [$tgl_mulai, $tgl_selesai];


    if ($kd_store != 'all') {
        $where_conditions .= " AND rh.kd_store = ?";
        $bind_types .= 's';
        $bind_params[] = $kd_store;
    }


    // --- LOGIKA PENCARIAN YANG DIPERBAIKI ---
    if (!empty($search_raw)) {
        // Kita gunakan CAST(... AS UNSIGNED) untuk memaksa angka database jadi bilangan bulat (hilangkan .00)
        // Agar 15.870.865 (input user) cocok dengan 15870865.00 (di database)

        $where_conditions .= " AND (
            rh.kode_supp LIKE ? 
            OR s.nama_supp LIKE ?
            OR rh.no_faktur LIKE ?
            OR rh.no_lpb LIKE ?
            OR CAST(rh.gtot AS UNSIGNED) LIKE ?
            OR CAST(rh.gppn AS UNSIGNED) LIKE ?
            OR CAST((rh.gtot - rh.gppn) AS UNSIGNED) LIKE ?
        )";

        $bind_types .= 'sssssss';
        $termRaw = '%' . $search_raw . '%';
        $termNum = '%' . $search_number . '%';

        // Urutan parameter
        $bind_params[] = $termRaw; // kode_supp
        $bind_params[] = $termRaw; // nama_supp
        $bind_params[] = $termRaw; // no_faktur
        $bind_params[] = $termRaw; // no_lpb
        $bind_params[] = $termNum; // gtot (Grand Total)
        $bind_params[] = $termNum; // gppn (PPN)
        $bind_params[] = $termNum; // dpp (Hasil pengurangan)
    }


    if ($filter_ppn === 'yes') {
        $where_conditions .= " AND rh.gppn > 0";
    } elseif ($filter_ppn === 'no') {
        $where_conditions .= " AND (rh.gppn = 0 OR rh.gppn IS NULL)";
    }


    $sql_count = "
        SELECT COUNT(*) as total
        FROM receipt_head rh
        LEFT JOIN supplier s ON rh.kode_supp = s.kode_supp AND rh.kd_store = s.kd_store
        WHERE $where_conditions
    ";

    $stmt_count = $conn->prepare($sql_count);
    if ($stmt_count === false) {
        throw new Exception("Prepare failed (count): " . $conn->error);
    }
    $stmt_count->bind_param($bind_types, ...$bind_params);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $total_rows = $result_count->fetch_assoc()['total'] ?? 0;
    $stmt_count->close();

    $response['pagination']['total_rows'] = (int) $total_rows;
    $response['pagination']['total_pages'] = ceil($total_rows / $limit);


    $sql_data = "
        SELECT 
            rh.tgl_tiba,
            rh.no_faktur,
            rh.no_lpb,
            rh.kd_store,
            ks.Nm_Alias, 
            rh.kode_supp,
            s.nama_supp,
            s.nama_inisial,
            rh.gtot,
            rh.gppn,
            rh.ppn
        FROM receipt_head rh
        LEFT JOIN supplier s ON rh.kode_supp = s.kode_supp AND rh.kd_store = s.kd_store
        LEFT JOIN kode_store ks ON rh.kd_store = ks.Kd_Store
        WHERE $where_conditions
        ORDER BY rh.tgl_tiba DESC, rh.no_faktur ASC
        LIMIT ? OFFSET ?
    ";


    $bind_types .= 'ii';
    $bind_params[] = $limit;
    $bind_params[] = $offset;

    $stmt_data = $conn->prepare($sql_data);
    if ($stmt_data === false) {
        throw new Exception("Prepare failed (data): " . $conn->error);
    }
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