<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
include '../../../aa_kon_sett.php';
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        if (!headers_sent()) {
            http_response_code(500);
            echo json_encode(['error' => 'Fatal Server Error: ' . $error['message']]);
        }
    }
});
$response = [
    'summary' => [
        'total_logs' => 0,
        'total_insert' => 0,
        'total_update' => 0,
        'total_delete' => 0,
    ],
    'tabel_data' => [],
    'pagination' => [
        'current_page' => 1,
        'total_pages' => 1,
        'total_rows' => 0,
        'offset' => 0,
        'limit' => 50,
    ],
    'error' => null,
];
try {
    $tanggal_kemarin = date('Y-m-d', strtotime('-30 days'));
    $tgl_mulai = !empty($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : $tanggal_kemarin;
    $tgl_selesai = !empty($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : date('Y-m-d');
    $search_keyword = $_GET['search'] ?? '';
    $page = (int) ($_GET['page'] ?? 1);
    $limit = 50;
    if ($page < 1)
        $page = 1;
    $offset = ($page - 1) * $limit;
    $response['pagination']['current_page'] = $page;
    $response['pagination']['limit'] = $limit;
    $response['pagination']['offset'] = $offset;
    $where_clause = "DATE(tgl_log) BETWEEN ? AND ?";
    $types = "ss";
    $params = [$tgl_mulai, $tgl_selesai];
    if (!empty($search_keyword)) {
        $where_clause .= " AND (no_faktur LIKE ? OR user_id LIKE ?)";
        $search_param = "%" . $search_keyword . "%";
        $types .= "ss";
        $params[] = $search_param;
        $params[] = $search_param;
    }
    $sql_data = "SELECT id, tgl_log, user_id, action, no_faktur, old_data, new_data, ip_address 
                 FROM serah_terima_nota_logs 
                 WHERE $where_clause 
                 ORDER BY tgl_log DESC 
                 LIMIT ? OFFSET ?";
    $types_data = $types . "ii";
    $params_data = array_merge($params, [$limit, $offset]);
    $stmt = $conn->prepare($sql_data);
    $stmt->bind_param($types_data, ...$params_data);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['old_data'] = json_decode($row['old_data']);
        $row['new_data'] = json_decode($row['new_data']);
        $response['tabel_data'][] = $row;
    }
    $stmt->close();
    $sql_count = "SELECT COUNT(*) as total FROM serah_terima_nota_logs WHERE $where_clause";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param($types, ...$params);
    $stmt_count->execute();
    $total_rows = $stmt_count->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt_count->close();
    $response['pagination']['total_rows'] = $total_rows;
    $response['pagination']['total_pages'] = ceil($total_rows / $limit);
    $sql_summary = "SELECT 
                        COUNT(*) as total_logs,
                        SUM(CASE WHEN action = 'INSERT' THEN 1 ELSE 0 END) as count_insert,
                        SUM(CASE WHEN action LIKE 'UPDATE%' THEN 1 ELSE 0 END) as count_update,
                        SUM(CASE WHEN action = 'SOFT_DELETE' THEN 1 ELSE 0 END) as count_delete
                    FROM serah_terima_nota_logs 
                    WHERE $where_clause";
    $stmt_sum = $conn->prepare($sql_summary);
    $stmt_sum->bind_param($types, ...$params);
    $stmt_sum->execute();
    $sum_data = $stmt_sum->get_result()->fetch_assoc();
    if ($sum_data) {
        $response['summary']['total_logs'] = $sum_data['total_logs'];
        $response['summary']['total_insert'] = $sum_data['count_insert'];
        $response['summary']['total_update'] = $sum_data['count_update'];
        $response['summary']['total_delete'] = $sum_data['count_delete'];
    }
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}
echo json_encode($response);
?>