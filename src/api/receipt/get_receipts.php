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
    $tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-d', strtotime('-1 month'));
    $tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');
    $search_faktur = $_GET['search'] ?? '';
    $kode_store = $_GET['kode_store'] ?? '';
    $page = (int) ($_GET['page'] ?? 1);
    if ($page < 1)
        $page = 1;
    $limit = 100;
    $offset = ($page - 1) * $limit;
    $response['pagination']['current_page'] = $page;
    $response['pagination']['limit'] = $limit;
    $response['pagination']['offset'] = $offset;
    $where_clauses = ["tgl_receipt BETWEEN ? AND ?"];
    $params = [$tgl_mulai, $tgl_selesai];
    $types = "ss";

    if (!empty($kode_store)) {
        $where_clauses[] = "kode_store = ?";
        $params[] = $kode_store;
        $types .= "s";
    }
    if (!empty($search_faktur)) {
        $where_clauses[] = "(no_faktur LIKE ? OR no_invoice LIKE ?)";
        $searchTerm = "%" . $search_faktur . "%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }
    $where_sql = implode(" AND ", $where_clauses);
    $sql_count = "SELECT COUNT(*) as total FROM c_receipt WHERE $where_sql";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param($types, ...$params);
    $stmt_count->execute();
    $total_rows = $stmt_count->get_result()->fetch_assoc()['total'] ?? 0;
    $response['pagination']['total_rows'] = (int) $total_rows;
    $response['pagination']['total_pages'] = ceil($total_rows / $limit);
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    $sql_data = "SELECT * FROM c_receipt
                 LEFT JOIN kode_store ks ON c_receipt.kode_store = ks.kd_store 
                 WHERE $where_sql 
                 ORDER BY tgl_receipt DESC, kode_supp ASC 
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