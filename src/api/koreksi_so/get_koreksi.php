<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');
$response = [
    'summary' => [
        'total_qty' => 0,
        'total_netto' => 0,
        'total_ppn' => 0,
        'total_grand' => 0,
    ],
    'stores' => [],
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
    $default_mulai = date('Y-m-16', strtotime('last month'));
    $default_selesai = date('Y-m-15');
    $tgl_mulai = $_GET['tgl_mulai'] ?? $default_mulai;
    $tgl_selesai = $_GET['tgl_selesai'] ?? $default_selesai;
    $kd_store = $_GET['kd_store'] ?? 'all';
    $page = (int) ($_GET['page'] ?? 1);
    $limit = 100;
    if ($page < 1)
        $page = 1;
    $offset = ($page - 1) * $limit;
    $response['pagination']['current_page'] = $page;
    $response['pagination']['limit'] = $limit;
    $response['pagination']['offset'] = $offset;
    $sql_stores = "SELECT kd_store, nm_alias FROM kode_store WHERE display = 'on' ORDER BY nm_alias ASC";
    $result_stores = $conn->query($sql_stores);
    if ($result_stores) {
        while ($row = $result_stores->fetch_assoc()) {
            $response['stores'][] = $row;
        }
    }
    $where = "DATE(tgl_koreksi) BETWEEN ? AND ?";
    $params = ['ss', $tgl_mulai, $tgl_selesai];
    if ($kd_store != 'all') {
        $where .= " AND kd_store = ?";
        $params[0] .= 's';
        $params[] = $kd_store;
    }
    $sql_summary = "
        SELECT 
            SUM(sel_qty) as total_qty,
            SUM(avg_cost * sel_qty) as total_netto,
            SUM(ppn_kor * sel_qty) as total_ppn,
            SUM((avg_cost + ppn_kor) * sel_qty) as total_grand
        FROM koreksi_so 
        WHERE $where
    ";
    $stmt_sum = $conn->prepare($sql_summary);
    $stmt_sum->bind_param(...$params);
    $stmt_sum->execute();
    $res_sum = $stmt_sum->get_result()->fetch_assoc();
    if ($res_sum) {
        $response['summary']['total_qty'] = (float) ($res_sum['total_qty'] ?? 0);
        $response['summary']['total_netto'] = (float) ($res_sum['total_netto'] ?? 0);
        $response['summary']['total_ppn'] = (float) ($res_sum['total_ppn'] ?? 0);
        $response['summary']['total_grand'] = (float) ($res_sum['total_grand'] ?? 0);
    }
    $stmt_sum->close();
    $sql_count = "
        SELECT COUNT(*) as total_group FROM (
            SELECT tgl_koreksi, kode_supp
            FROM koreksi_so
            WHERE $where
            GROUP BY tgl_koreksi, kode_supp
        ) as grouped_table
    ";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param(...$params);
    $stmt_count->execute();
    $total_rows = $stmt_count->get_result()->fetch_assoc()['total_group'] ?? 0;
    $response['pagination']['total_rows'] = (int) $total_rows;
    $response['pagination']['total_pages'] = ceil($total_rows / $limit);
    $stmt_count->close();
    $sql_data = "
        SELECT 
            DATE(tgl_koreksi) as tgl_koreksi, 
            kode_supp,
            SUM(sel_qty) as grp_qty,
            SUM(avg_cost * sel_qty) as grp_netto, 
            SUM(ppn_kor * sel_qty) as grp_ppn,
            SUM((avg_cost + ppn_kor) * sel_qty) as grp_total
        FROM koreksi_so
        WHERE $where
        GROUP BY tgl_koreksi, kode_supp
        ORDER BY tgl_koreksi DESC, kode_supp ASC
        LIMIT ? OFFSET ?
    ";
    $params[0] .= 'ii';
    $params[] = $limit;
    $params[] = $offset;
    $stmt = $conn->prepare($sql_data);
    $stmt->bind_param(...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $response['tabel_data'][] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}
echo json_encode($response);
?>