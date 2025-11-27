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
    $search_supplier = $_GET['search_supplier'] ?? '';

    $page = (int) ($_GET['page'] ?? 1);
    if ($page < 1)
        $page = 1;
    $limit = 10;

    $response['pagination']['limit'] = $limit;
    $response['pagination']['current_page'] = $page;
    $offset = ($page - 1) * $limit;
    $response['pagination']['offset'] = $offset;

    // Conditions untuk tabel pembelian
    $where_conditions = "DATE(p.tgl_nota) BETWEEN ? AND ?";
    $bind_types = 'ss';
    $bind_params = [$tgl_mulai, $tgl_selesai];

    if (!empty($search_supplier)) {
        $where_conditions .= " AND (p.nama_supplier LIKE ? OR p.kode_supplier LIKE ?)";
        $bind_types .= 'ss';
        $searchTerm = '%' . $search_supplier . '%';
        $bind_params[] = $searchTerm;
        $bind_params[] = $searchTerm;
    }

    // 1. Hitung Total Rows
    $sql_count = "SELECT COUNT(*) as total FROM ff_pembelian p WHERE $where_conditions";
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

    // 2. Ambil Data dengan LEFT JOIN ke Core Tax
    // Logika: Mencocokkan DPP (pembelian) dengan harga_jual (coretax) DAN PPN
    // Menggunakan GROUP BY p.id agar baris pembelian tidak duplikat jika ada multiple match di coretax
    $sql_data = "
        SELECT 
            p.id,
            p.nama_supplier, 
            p.kode_supplier, 
            p.tgl_nota, 
            p.no_faktur, 
            p.dpp, 
            p.ppn, 
            p.total_terima_fp,
            p.ada_di_coretax,
            p.nsfp,
            -- Ambil satu kandidat NSFP dari coretax jika cocok
            MAX(c.nomor_faktur_pajak) as candidate_nsfp,
            COUNT(c.nomor_faktur_pajak) as match_count
        FROM ff_pembelian p
        LEFT JOIN ff_coretax c ON p.dpp = c.harga_jual AND p.ppn = c.ppn
        WHERE $where_conditions
        GROUP BY p.id
        ORDER BY p.tgl_nota DESC, p.no_faktur ASC
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