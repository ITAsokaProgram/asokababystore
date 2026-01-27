<?php
session_start();
include '../../../aa_kon_sett.php';

header('Content-Type: application/json');

// --- Inisialisasi Response Struct ---
$response = [
    'tabel_data' => [],
    'pagination' => [
        'current_page' => 1,
        'total_pages' => 1,
        'total_rows' => 0,
        'offset' => 0,
        'limit' => 20,
    ],
    'error' => null,
];

try {
    // --- 1. Ambil Parameter ---
    $search = $_GET['search'] ?? '';
    $page = (int) ($_GET['page'] ?? 1);
    if ($page < 1) $page = 1;
    
    $limit = 20;
    $offset = ($page - 1) * $limit;

    // --- 2. Build Query Where Clause ---
    $where_clauses = ["1=1"];
    $params = [];
    $types = "";

    if (!empty($search)) {
        $where_clauses[] = "(nama LIKE ? OR email LIKE ? OR wilayah LIKE ? OR no_telpon LIKE ?)";
        $searchTerm = "%" . $search . "%";
        $params[] = $searchTerm; $params[] = $searchTerm; $params[] = $searchTerm; $params[] = $searchTerm;
        $types .= "ssss";
    }

    $where_sql = implode(" AND ", $where_clauses);

    // --- 3. Hitung Total Data ---
    $sql_count = "SELECT COUNT(*) as total FROM user_supplier WHERE $where_sql";
    $stmt_count = $conn->prepare($sql_count);
    if (!empty($params)) {
        $stmt_count->bind_param($types, ...$params);
    }
    $stmt_count->execute();
    $total_rows = $stmt_count->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt_count->close();

    // Set Pagination
    $response['pagination']['current_page'] = $page;
    $response['pagination']['total_rows'] = (int) $total_rows;
    $response['pagination']['total_pages'] = ceil($total_rows / $limit);
    $response['pagination']['limit'] = $limit;
    $response['pagination']['offset'] = $offset;

    // --- 4. Ambil Data Utama (SORT BY created_at DESC) ---
    // Pastikan kolom created_at ada di database. Jika tidak ada, ganti jadi: ORDER BY kode DESC
    $sql_data = "SELECT kode, nama, email, no_telpon, wilayah 
                 FROM user_supplier 
                 WHERE $where_sql 
                 ORDER BY created_at DESC 
                 LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql_data);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response['tabel_data'][] = $row;
    }
    
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>