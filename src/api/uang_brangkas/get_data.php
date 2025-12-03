<?php
session_start();
include '../../../aa_kon_sett.php';

// Error Handling Standard
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
        'limit' => 100,
    ],
    'error' => null,
];

try {
    // 1. Ambil Parameter Filter
    $tanggal_hari_ini = date('Y-m-d');
    $tgl_mulai = $_GET['tgl_mulai'] ?? $tanggal_hari_ini;
    $tgl_selesai = $_GET['tgl_selesai'] ?? $tanggal_hari_ini;

    // Pagination
    $page = (int) ($_GET['page'] ?? 1);
    if ($page < 1)
        $page = 1;
    $limit = 100;
    $offset = ($page - 1) * $limit;

    $response['pagination']['limit'] = $limit;
    $response['pagination']['current_page'] = $page;
    $response['pagination']['offset'] = $offset;

    // 2. Query Utama
    // Menggunakan SQL_CALC_FOUND_ROWS untuk efisiensi pagination sederhana
    $sql_data = "
        SELECT 
            SQL_CALC_FOUND_ROWS
            tanggal,
            jam,
            user_hitung,
            user_cek,
            kode_otorisasi_input,
            qty_100rb, qty_50rb, qty_20rb, qty_10rb, qty_5rb, qty_2rb, qty_1rb,
            qty_1000_koin, qty_500_koin, qty_200_koin, qty_100_koin,
            total_nominal,
            keterangan
        FROM uang_brangkas
        WHERE 
            tanggal BETWEEN ? AND ?
        ORDER BY 
            tanggal DESC, jam DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql_data);
    if (!$stmt)
        throw new Exception("Prepare failed: " . $conn->error);

    $stmt->bind_param("ssii", $tgl_mulai, $tgl_selesai, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response['tabel_data'][] = $row;
    }
    $stmt->close();

    // 3. Hitung Total Rows untuk Pagination
    $result_count = $conn->query("SELECT FOUND_ROWS() AS total_rows");
    $total_rows = $result_count->fetch_assoc()['total_rows'] ?? 0;

    $response['pagination']['total_rows'] = (int) $total_rows;
    $response['pagination']['total_pages'] = ceil($total_rows / $limit);

    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>