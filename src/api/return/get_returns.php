<?php
session_start();
include '../../../aa_kon_sett.php';
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
    $tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-d', strtotime('-1 day'));
    $tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');
    $search_faktur = $_GET['search'] ?? '';
    $kode_store = $_GET['kode_store'] ?? ''; // TAMBAHAN
    $page = (int) ($_GET['page'] ?? 1);

    if ($page < 1)
        $page = 1;
    $limit = 100;
    $offset = ($page - 1) * $limit;

    $response['pagination']['current_page'] = $page;
    $response['pagination']['limit'] = $limit;
    $response['pagination']['offset'] = $offset;

    // Filter Query
    $where_clauses = ["tgl_return BETWEEN ? AND ?"];
    $params = [$tgl_mulai, $tgl_selesai];
    $types = "ss";

    // TAMBAHAN: Filter Cabang
    if (!empty($kode_store)) {
        $where_clauses[] = "kode_store = ?";
        $params[] = $kode_store;
        $types .= "s";
    }

    if (!empty($search_faktur)) {
        $where_clauses[] = "no_faktur LIKE ?";
        $params[] = "%" . $search_faktur . "%";
        $types .= "s";
    }

    $where_sql = implode(" AND ", $where_clauses);

    // Hitung Total Rows
    $sql_count = "SELECT COUNT(*) as total FROM c_return WHERE $where_sql";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param($types, ...$params);
    $stmt_count->execute();
    $total_rows = $stmt_count->get_result()->fetch_assoc()['total'] ?? 0;

    $response['pagination']['total_rows'] = (int) $total_rows;
    $response['pagination']['total_pages'] = ceil($total_rows / $limit);

    // Ambil Data
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    // TAMBAHAN: JOIN dengan kode_store
    $sql_data = "SELECT * FROM c_return 
                 LEFT JOIN kode_store ks ON c_return.kode_store = ks.kd_store 
                 WHERE $where_sql 
                 ORDER BY tgl_return DESC, kode_supp ASC 
                 LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql_data);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response['tabel_data'][] = $row;
    }

    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>