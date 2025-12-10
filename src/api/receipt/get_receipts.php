<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');
$response = [
    'tabel_data' => [],
    'summary' => [
        'total_selisih' => 0,
        'list_selisih' => [],
        'total_belum_ada' => 0,
        'list_belum_ada' => []
    ],
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
    $kode_store = $_GET['kode_store'] ?? '';
    $page = (int) ($_GET['page'] ?? 1);
    if ($page < 1)
        $page = 1;
    $limit = 100;
    $offset = ($page - 1) * $limit;
    $startDateSubquery = "SELECT MIN(tgl_receipt) FROM c_receipt cr WHERE cr.kode_store = rh.kd_store";
    $where_clauses = ["rh.tgl_tiba BETWEEN ? AND ?"];
    $params = [$tgl_mulai, $tgl_selesai];
    $types = "ss";
    $where_clauses[] = "rh.tgl_tiba >= COALESCE(($startDateSubquery), '2099-12-31')";
    if (!empty($kode_store)) {
        $where_clauses[] = "rh.kd_store = ?";
        $params[] = $kode_store;
        $types .= "s";
    }
    if (!empty($search_faktur)) {
        $where_clauses[] = "(rh.no_faktur LIKE ? OR rh.no_ord LIKE ?)";
        $searchTerm = "%" . $search_faktur . "%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }
    $where_sql = implode(" AND ", $where_clauses);
    $sql_summary = "SELECT 
                        rh.tgl_tiba, 
                        rh.no_faktur,
                        (IFNULL(rh.gtot, 0) - IFNULL(cr.total_penerimaan, 0)) as nilai_selisih,
                        CASE 
                            WHEN cr.no_faktur IS NULL THEN 'MISSING'
                            WHEN ABS(IFNULL(rh.gtot, 0) - IFNULL(cr.total_penerimaan, 0)) > 100 THEN 'DIFF'
                            ELSE 'OK'
                        END as status_cek
                    FROM receipt_head rh
                    LEFT JOIN c_receipt cr ON rh.no_faktur = cr.no_faktur AND rh.kode_supp = cr.kode_supp
                    WHERE $where_sql";
    $stmt_sum = $conn->prepare($sql_summary);
    $stmt_sum->bind_param($types, ...$params);
    $stmt_sum->execute();
    $res_sum = $stmt_sum->get_result();
    while ($row = $res_sum->fetch_assoc()) {
        if ($row['status_cek'] == 'MISSING') {
            $response['summary']['total_belum_ada']++;
            if (count($response['summary']['list_belum_ada']) < 50) {
                $response['summary']['list_belum_ada'][] = [
                    'tgl_tiba' => $row['tgl_tiba'],
                    'no_faktur' => $row['no_faktur']
                ];
            }
        } elseif ($row['status_cek'] == 'DIFF') {
            $response['summary']['total_selisih']++;
            if (count($response['summary']['list_selisih']) < 50) {
                $response['summary']['list_selisih'][] = [
                    'tgl_tiba' => $row['tgl_tiba'],
                    'no_faktur' => $row['no_faktur']
                ];
            }
        }
    }
    $sql_count = "SELECT COUNT(*) as total 
                  FROM c_receipt cr 
                  LEFT JOIN receipt_head rh ON cr.no_faktur = rh.no_faktur AND cr.kode_supp = rh.kode_supp
                  WHERE $where_sql";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param($types, ...$params);
    $stmt_count->execute();
    $total_rows = $stmt_count->get_result()->fetch_assoc()['total'] ?? 0;
    $response['pagination']['current_page'] = $page;
    $response['pagination']['total_rows'] = (int) $total_rows;
    $response['pagination']['total_pages'] = ceil($total_rows / $limit);
    $response['pagination']['limit'] = $limit;
    $response['pagination']['offset'] = $offset;
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    $sql_data = "SELECT 
                    rh.tgl_tiba, 
                    cr.no_faktur, 
                    cr.kode_store, 
                    cr.kode_supp, 
                    cr.keterangan,
                    ks.Nm_Alias,
                    IFNULL(cr.total_penerimaan, 0) as total_check
                 FROM c_receipt cr
                 LEFT JOIN receipt_head rh ON cr.no_faktur = rh.no_faktur AND cr.kode_supp = rh.kode_supp
                 LEFT JOIN kode_store ks ON cr.kode_store = ks.kd_store 
                 WHERE $where_sql 
                 ORDER BY rh.tgl_tiba DESC, cr.kode_store ASC 
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