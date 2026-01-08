<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');
$response = [
    'tabel_data' => [],
    'summary' => [
        'total_selisih' => 0,
        'total_selisih_rupiah' => 0,
        'list_selisih' => [],
        'total_belum_ada' => 0,
        'list_belum_ada' => [],
        'total_tidak_ditemukan' => 0,
        'list_tidak_ditemukan' => []
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
    $where_clauses_rh = ["rh.tgl_tiba BETWEEN ? AND ?"];
    $params_rh = [$tgl_mulai, $tgl_selesai];
    $types_rh = "ss";
    $where_clauses_rh[] = "rh.tgl_tiba >= COALESCE(($startDateSubquery), '2099-12-31')";
    if (!empty($kode_store)) {
        $where_clauses_rh[] = "rh.kd_store = ?";
        $params_rh[] = $kode_store;
        $types_rh .= "s";
    }
    if (!empty($search_faktur)) {
        $where_clauses_rh[] = "(rh.no_faktur LIKE ? OR rh.no_ord LIKE ?)";
        $searchTerm = "%" . $search_faktur . "%";
        $params_rh[] = $searchTerm;
        $params_rh[] = $searchTerm;
        $types_rh .= "ss";
    }
    $where_sql_rh = implode(" AND ", $where_clauses_rh);
    $sql_summary = "SELECT 
                        rh.tgl_tiba, 
                        rh.no_faktur,
                        (IFNULL(rh.gtot, 0) + IFNULL(rh.gppn, 0) + IFNULL(rh.gppn_bm, 0)) as total_erp,
                        ((IFNULL(rh.gtot, 0) + IFNULL(rh.gppn, 0) + IFNULL(rh.gppn_bm, 0)) - IFNULL(cr.total_penerimaan, 0)) as nilai_selisih,
                        CASE 
                            WHEN cr.no_faktur IS NULL THEN 'MISSING'
                            WHEN ABS((IFNULL(rh.gtot, 0) + IFNULL(rh.gppn, 0) + IFNULL(rh.gppn_bm, 0)) - IFNULL(cr.total_penerimaan, 0)) > 100 THEN 'DIFF'
                            ELSE 'OK'
                        END as status_cek
                    FROM receipt_head rh
                    LEFT JOIN c_receipt cr ON rh.no_faktur = cr.no_faktur AND rh.kode_supp = cr.kode_supp
                    WHERE $where_sql_rh";
    $stmt_sum = $conn->prepare($sql_summary);
    $stmt_sum->bind_param($types_rh, ...$params_rh);
    $stmt_sum->execute();
    $res_sum = $stmt_sum->get_result();
    while ($row = $res_sum->fetch_assoc()) {
        if ($row['status_cek'] == 'MISSING') {
            $response['summary']['total_belum_ada'] += $row['total_erp'];
            if (count($response['summary']['list_belum_ada']) < 50) {
                $response['summary']['list_belum_ada'][] = [
                    'tgl_tiba' => $row['tgl_tiba'],
                    'no_faktur' => $row['no_faktur'],
                    'total' => $row['total_erp']
                ];
            }
        } elseif ($row['status_cek'] == 'DIFF') {
            $response['summary']['total_selisih']++;
            $response['summary']['total_selisih_rupiah'] += abs($row['nilai_selisih']);
            if (count($response['summary']['list_selisih']) < 50) {
                $response['summary']['list_selisih'][] = [
                    'tgl_tiba' => $row['tgl_tiba'],
                    'no_faktur' => $row['no_faktur'],
                    'total' => abs($row['nilai_selisih'])
                ];
            }
        }
    }
    $where_clauses_nf = ["cr.tgl_receipt BETWEEN ? AND ?"];
    $params_nf = [$tgl_mulai, $tgl_selesai];
    $types_nf = "ss";
    if (!empty($kode_store)) {
        $where_clauses_nf[] = "cr.kode_store = ?";
        $params_nf[] = $kode_store;
        $types_nf .= "s";
    }
    $where_clauses_nf[] = "rh.no_faktur IS NULL";
    $where_sql_nf = implode(" AND ", $where_clauses_nf);
    $sql_not_found = "SELECT 
                        cr.tgl_receipt as tgl_tiba,
                        cr.no_faktur,
                        cr.total_penerimaan
                      FROM c_receipt cr
                      LEFT JOIN receipt_head rh ON cr.no_faktur = rh.no_faktur AND cr.kode_supp = rh.kode_supp
                      WHERE $where_sql_nf";
    $stmt_nf = $conn->prepare($sql_not_found);
    $stmt_nf->bind_param($types_nf, ...$params_nf);
    $stmt_nf->execute();
    $res_nf = $stmt_nf->get_result();
    while ($row = $res_nf->fetch_assoc()) {
        $response['summary']['total_tidak_ditemukan']++;
        if (count($response['summary']['list_tidak_ditemukan']) < 50) {
            $response['summary']['list_tidak_ditemukan'][] = [
                'tgl_tiba' => $row['tgl_tiba'],
                'no_faktur' => $row['no_faktur'],
                'total' => $row['total_penerimaan']
            ];
        }
    }
    $where_clauses_cr = ["COALESCE(rh.tgl_tiba, cr.tgl_receipt) BETWEEN ? AND ?"];
    $params_cr = [$tgl_mulai, $tgl_selesai];
    $types_cr = "ss";
    if (!empty($kode_store)) {
        $where_clauses_cr[] = "cr.kode_store = ?";
        $params_cr[] = $kode_store;
        $types_cr .= "s";
    }
    if (!empty($search_faktur)) {
        $where_clauses_cr[] = "(cr.no_faktur LIKE ? OR rh.no_ord LIKE ?)";
        $searchTerm = "%" . $search_faktur . "%";
        $params_cr[] = $searchTerm;
        $params_cr[] = $searchTerm;
        $types_cr .= "ss";
    }
    $where_sql_cr = implode(" AND ", $where_clauses_cr);
    $sql_count = "SELECT COUNT(*) as total 
                  FROM c_receipt cr 
                  LEFT JOIN receipt_head rh ON cr.no_faktur = rh.no_faktur AND cr.kode_supp = rh.kode_supp
                  WHERE $where_sql_cr";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param($types_cr, ...$params_cr);
    $stmt_count->execute();
    $total_rows = $stmt_count->get_result()->fetch_assoc()['total'] ?? 0;
    $response['pagination']['current_page'] = $page;
    $response['pagination']['total_rows'] = (int) $total_rows;
    $response['pagination']['total_pages'] = ceil($total_rows / $limit);
    $response['pagination']['limit'] = $limit;
    $response['pagination']['offset'] = $offset;
    $params_cr[] = $limit;
    $params_cr[] = $offset;
    $types_cr .= "ii";
    $sql_data = "SELECT 
                    COALESCE(rh.tgl_tiba, cr.tgl_receipt) as tgl_tiba, 
                    cr.no_faktur, 
                    cr.kode_store, 
                    cr.kode_supp, 
                    cr.keterangan,
                    ks.Nm_Alias,
                    IFNULL(cr.total_penerimaan, 0) as total_check,
                    (IFNULL(rh.gtot, 0) + IFNULL(rh.gppn, 0)) as total_erp,
                    CASE 
                        WHEN rh.no_faktur IS NULL THEN 'NOT_FOUND_IN_ERP'
                        WHEN ABS((IFNULL(rh.gtot, 0) + IFNULL(rh.gppn, 0)) - IFNULL(cr.total_penerimaan, 0)) > 100 THEN 'DIFF'
                        ELSE 'MATCH'
                    END as status_data,
                    ((IFNULL(rh.gtot, 0) + IFNULL(rh.gppn, 0)) - IFNULL(cr.total_penerimaan, 0)) as nilai_selisih_row
                 FROM c_receipt cr
                 LEFT JOIN receipt_head rh ON cr.no_faktur = rh.no_faktur AND cr.kode_supp = rh.kode_supp
                 LEFT JOIN kode_store ks ON cr.kode_store = ks.kd_store 
                 WHERE $where_sql_cr 
                 ORDER BY cr.no_faktur ASC
                 LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql_data);
    $stmt->bind_param($types_cr, ...$params_cr);
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