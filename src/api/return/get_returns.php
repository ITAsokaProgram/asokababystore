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
    $union_source = "
        SELECT no_faktur, kode_supp, kd_store, tgl_return as tgl_erp, 
               ((hrg_beli * qty) + IFNULL(ppn, 0) + IFNULL(ppn_bm, 0)) as nilai_row 
        FROM retur
        UNION ALL
        SELECT no_faktur, kode_supp, kd_store, tgl_expproduk as tgl_erp, 
               ((hrg_beli * qty) + IFNULL(ppn, 0) + IFNULL(ppn_bm, 0)) as nilai_row 
        FROM exp_produk
        UNION ALL
        SELECT no_faktur, kode_supp, kd_store, tgl_badstock as tgl_erp, 
               ((hrg_beli * qty) + IFNULL(ppn, 0) + IFNULL(ppn_bm, 0)) as nilai_row 
        FROM bad_stock
        UNION ALL
        SELECT no_faktur, kode_supp, kd_store, tgl_input as tgl_erp, 
               ((hrg_beli * qty) + IFNULL(ppn, 0) + IFNULL(ppn_bm, 0)) as nilai_row 
        FROM hilang_pasangan
    ";
    $params_base = [];
    $types_base = "";
    $filter_sql_strict = "1=1";
    $filter_sql_loose = "1=1";
    if (!empty($kode_store)) {
        $filter_sql_strict .= " AND erp.kd_store = ?";
        $filter_sql_loose .= " AND kd_store = ?";
        $params_base[] = $kode_store;
        $types_base .= "s";
    }
    if (!empty($search_faktur)) {
        $filter_sql_strict .= " AND erp.no_faktur LIKE ?";
        $filter_sql_loose .= " AND no_faktur LIKE ?";
        $params_base[] = "%" . $search_faktur . "%";
        $types_base .= "s";
    }
    $where_erp_strict = "$filter_sql_strict AND erp.tgl_erp BETWEEN ? AND ?";
    $params_strict = array_merge($params_base, [$tgl_mulai, $tgl_selesai]);
    $types_strict = $types_base . "ss";
    $sql_missing = "
        SELECT 
            erp.tgl_erp, erp.no_faktur, SUM(erp.nilai_row) as total_erp
        FROM ($union_source) as erp
        LEFT JOIN c_return cr ON erp.no_faktur = cr.no_faktur 
             AND erp.kode_supp = cr.kode_supp 
             AND erp.kd_store = cr.kode_store
        WHERE $where_erp_strict
        AND cr.no_faktur IS NULL
        GROUP BY erp.no_faktur, erp.kode_supp, erp.kd_store, erp.tgl_erp
    ";
    $stmt_miss = $conn->prepare($sql_missing);
    if ($types_strict)
        $stmt_miss->bind_param($types_strict, ...$params_strict);
    $stmt_miss->execute();
    $res_miss = $stmt_miss->get_result();
    while ($row = $res_miss->fetch_assoc()) {
        $response['summary']['total_belum_ada'] += $row['total_erp'];
        if (count($response['summary']['list_belum_ada']) < 50) {
            $response['summary']['list_belum_ada'][] = [
                'tgl_return' => $row['tgl_erp'],
                'no_faktur' => $row['no_faktur'],
                'total' => $row['total_erp']
            ];
        }
    }
    $where_erp_loose = $filter_sql_loose;
    $params_loose = $params_base;
    $types_loose = $types_base;
    $where_check = "cr.tgl_return BETWEEN ? AND ?";
    $params_check = [$tgl_mulai, $tgl_selesai];
    $types_check = "ss";
    if (!empty($kode_store)) {
        $where_check .= " AND cr.kode_store = ?";
        $params_check[] = $kode_store;
        $types_check .= "s";
    }
    if (!empty($search_faktur)) {
        $where_check .= " AND cr.no_faktur LIKE ?";
        $params_check[] = "%" . $search_faktur . "%";
        $types_check .= "s";
    }
    $sql_erp_grouped_loose = "
        SELECT no_faktur, kode_supp, kd_store, SUM(nilai_row) as total_erp_val 
        FROM ($union_source) as u 
        WHERE $where_erp_loose
        GROUP BY no_faktur, kode_supp, kd_store
    ";
    $sql_summary_check = "
        SELECT 
            cr.tgl_return, cr.no_faktur, cr.total_return,
            IFNULL(erp.total_erp_val, 0) as total_erp_val,
            CASE 
                WHEN erp.no_faktur IS NULL THEN 'NOT_FOUND_IN_ERP'
                WHEN ABS(IFNULL(erp.total_erp_val, 0) - IFNULL(cr.total_return, 0)) > 100 THEN 'DIFF'
                ELSE 'MATCH'
            END as status_data
        FROM c_return cr
        LEFT JOIN ($sql_erp_grouped_loose) erp 
            ON cr.no_faktur = erp.no_faktur 
            AND cr.kode_supp = erp.kode_supp 
            AND cr.kode_store = erp.kd_store
        WHERE $where_check
    ";
    $params_summary = array_merge($params_loose, $params_check);
    $types_summary = $types_loose . $types_check;
    $stmt_sum = $conn->prepare($sql_summary_check);
    if ($types_summary)
        $stmt_sum->bind_param($types_summary, ...$params_summary);
    $stmt_sum->execute();
    $res_sum = $stmt_sum->get_result();
    $total_rows_check = 0;
    while ($row = $res_sum->fetch_assoc()) {
        $total_rows_check++;
        if ($row['status_data'] == 'NOT_FOUND_IN_ERP') {
            $response['summary']['total_tidak_ditemukan']++;
            if (count($response['summary']['list_tidak_ditemukan']) < 50) {
                $response['summary']['list_tidak_ditemukan'][] = [
                    'tgl_return' => $row['tgl_return'],
                    'no_faktur' => $row['no_faktur'],
                    'total' => $row['total_return']
                ];
            }
        } elseif ($row['status_data'] == 'DIFF') {
            $selisih = abs($row['total_erp_val'] - $row['total_return']);
            $response['summary']['total_selisih']++;
            $response['summary']['total_selisih_rupiah'] += $selisih;
            if (count($response['summary']['list_selisih']) < 50) {
                $response['summary']['list_selisih'][] = [
                    'tgl_return' => $row['tgl_return'],
                    'no_faktur' => $row['no_faktur'],
                    'total' => $selisih
                ];
            }
        }
    }
    $response['pagination']['total_rows'] = $total_rows_check;
    $response['pagination']['total_pages'] = ceil($total_rows_check / $limit);
    $response['pagination']['limit'] = $limit;
    $response['pagination']['offset'] = $offset;
    $response['pagination']['current_page'] = $page;
    $params_data = array_merge($params_summary, [$limit, $offset]);
    $types_data = $types_summary . "ii";
    $sql_data = "
        SELECT 
            cr.tgl_return,
            cr.no_faktur,
            cr.kode_store,
            cr.kode_supp,
            cr.keterangan,
            ks.Nm_Alias,
            IFNULL(cr.total_return, 0) as total_check,
            IFNULL(erp.total_erp_val, 0) as total_erp,
            CASE 
                WHEN erp.no_faktur IS NULL THEN 'NOT_FOUND_IN_ERP'
                WHEN ABS(IFNULL(erp.total_erp_val, 0) - IFNULL(cr.total_return, 0)) > 100 THEN 'DIFF'
                ELSE 'MATCH'
            END as status_data,
            (IFNULL(erp.total_erp_val, 0) - IFNULL(cr.total_return, 0)) as nilai_selisih_row
        FROM c_return cr
        LEFT JOIN ($sql_erp_grouped_loose) erp 
            ON cr.no_faktur = erp.no_faktur 
            AND cr.kode_supp = erp.kode_supp 
            AND cr.kode_store = erp.kd_store
        LEFT JOIN kode_store ks ON cr.kode_store = ks.kd_store
        WHERE $where_check
        ORDER BY cr.tgl_return DESC, cr.kode_store ASC
        LIMIT ? OFFSET ?
    ";
    $stmt_data = $conn->prepare($sql_data);
    if ($types_data)
        $stmt_data->bind_param($types_data, ...$params_data);
    $stmt_data->execute();
    $res_data = $stmt_data->get_result();
    while ($row = $res_data->fetch_assoc()) {
        $response['tabel_data'][] = $row;
    }
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}
echo json_encode($response);
?>