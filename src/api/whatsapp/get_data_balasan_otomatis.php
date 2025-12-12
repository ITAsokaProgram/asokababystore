<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');
$response = [
    'data' => [],
    'pagination' => [
        'current_page' => 1,
        'total_pages' => 1,
        'total_rows' => 0,
        'limit' => 50
    ],
    'error' => null
];
try {
    $search = $_GET['search_keyword'] ?? '';
    $page = (int) ($_GET['page'] ?? 1);
    if ($page < 1)
        $page = 1;
    $limit = 50;
    $offset = ($page - 1) * $limit;
    $response['pagination']['current_page'] = $page;
    $response['pagination']['limit'] = $limit;
    $sql_count = "SELECT COUNT(*) as total FROM wa_balasan_otomatis_kamus";
    $types_count = "";
    $params_count = [];
    if (!empty($search)) {
        $sql_count .= " WHERE kata_kunci LIKE ?";
        $types_count .= "s";
        $params_count[] = "%" . $search . "%";
    }
    $stmt_count = $conn->prepare($sql_count);
    if (!empty($params_count)) {
        $stmt_count->bind_param($types_count, ...$params_count);
    }
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $row_count = $result_count->fetch_assoc();
    $total_rows = $row_count['total'] ?? 0;
    $stmt_count->close();
    $response['pagination']['total_rows'] = (int) $total_rows;
    $response['pagination']['total_pages'] = ceil($total_rows / $limit);
    $sql = "SELECT id, kata_kunci, status_aktif FROM wa_balasan_otomatis_kamus";
    $types = "";
    $params = [];
    if (!empty($search)) {
        $sql .= " WHERE kata_kunci LIKE ?";
        $types .= "s";
        $params[] = "%" . $search . "%";
    }
    $sql .= " ORDER BY dibuat_pada DESC LIMIT ? OFFSET ?";
    $types .= "ii";
    $params[] = $limit;
    $params[] = $offset;
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $kamus_data = [];
    $kamus_ids = [];
    while ($row = $result->fetch_assoc()) {
        $row['list_pesan'] = [];
        $kamus_data[$row['id']] = $row;
        $kamus_ids[] = $row['id'];
    }
    $stmt->close();
    if (!empty($kamus_ids)) {
        $in_placeholder = implode(',', array_fill(0, count($kamus_ids), '?'));
        $types_msg = str_repeat('i', count($kamus_ids));
        $sql_msg = "SELECT kamus_id, jenis_pesan, isi_pesan FROM wa_balasan_otomatis_pesan 
                    WHERE kamus_id IN ($in_placeholder) 
                    ORDER BY kamus_id, urutan ASC";
        $stmt_msg = $conn->prepare($sql_msg);
        $stmt_msg->bind_param($types_msg, ...$kamus_ids);
        $stmt_msg->execute();
        $res_msg = $stmt_msg->get_result();
        while ($msg = $res_msg->fetch_assoc()) {
            if (isset($kamus_data[$msg['kamus_id']])) {
                $content = $msg['isi_pesan'];
                if ($msg['jenis_pesan'] !== 'text') {
                    $decoded = json_decode($content, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $content = $decoded;
                    }
                }
                $kamus_data[$msg['kamus_id']]['list_pesan'][] = [
                    'type' => $msg['jenis_pesan'],
                    'content' => $content
                ];
            }
        }
        $stmt_msg->close();
    }
    $response['data'] = array_values($kamus_data);
} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}
echo json_encode($response);
?>