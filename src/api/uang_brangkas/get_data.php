<?php
session_start();
include '../../../aa_kon_sett.php';

header('Content-Type: application/json');

$response = [
    'tabel_data' => [],
    'pagination' => ['current_page' => 1, 'total_pages' => 1, 'total_rows' => 0, 'offset' => 0, 'limit' => 100],
    'error' => null,
];

try {
    $tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-d');
    $tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $limit = 100;
    $offset = ($page - 1) * $limit;

    $response['pagination']['limit'] = $limit;
    $response['pagination']['current_page'] = $page;
    $response['pagination']['offset'] = $offset;

    $sql_data = "
        SELECT SQL_CALC_FOUND_ROWS *
        FROM uang_brangkas
        WHERE tanggal BETWEEN ? AND ?
        ORDER BY tanggal DESC, jam DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql_data);
    $stmt->bind_param("ssii", $tgl_mulai, $tgl_selesai, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response['tabel_data'][] = $row;
    }
    $stmt->close();

    $total_rows = $conn->query("SELECT FOUND_ROWS() AS total_rows")->fetch_assoc()['total_rows'] ?? 0;
    $response['pagination']['total_rows'] = (int) $total_rows;
    $response['pagination']['total_pages'] = ceil($total_rows / $limit);

} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>