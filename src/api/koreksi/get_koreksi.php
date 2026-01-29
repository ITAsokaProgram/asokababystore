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
    if ($page < 1) $page = 1;
    $limit = 100;
    $offset = ($page - 1) * $limit;
    $sql_erp_agg = "
        SELECT 
            k.no_faktur, k.kd_store, k.kode_supp, DATE(k.tgl_koreksi) as tgl_koreksi,
            SUM((
                CASE 
                    WHEN k.keterangan = 'Minus System' THEN k.sel_qty 
                    WHEN k.keterangan = 'Stock Opname' THEN k.sel_qty 
                    ELSE 0 
                END * (k.harga_beli + IFNULL(k.ppn_kor, 0))
            )) as total_erp_calc
        FROM koreksi k
        WHERE k.tgl_koreksi BETWEEN ? AND ?
    ";
    $params_agg = [$tgl_mulai, $tgl_selesai];
    $types_agg = "ss";
    if (!empty($kode_store)) {
        $sql_erp_agg .= " AND k.kd_store = ?";
        $params_agg[] = $kode_store;
        $types_agg .= "s";
    }
    $sql_erp_agg .= " GROUP BY k.no_faktur, k.kd_store, k.kode_supp";
    $filter_search_param = "%" . $search_faktur . "%";
    $sql_check_erp = "
        SELECT * FROM (
            SELECT 
                erp.tgl_koreksi, erp.no_faktur, erp.total_erp_calc,
                IFNULL(ck.total_koreksi, 0) as total_scan,
                CASE 
                    WHEN ck.no_faktur IS NULL THEN 'MISSING'
                    WHEN ABS(erp.total_erp_calc - IFNULL(ck.total_koreksi, 0)) > 100 THEN 'DIFF'
                    ELSE 'OK'
                END as status_cek
            FROM ($sql_erp_agg) erp
            LEFT JOIN c_koreksi ck ON erp.no_faktur = ck.no_faktur 
                 AND erp.kd_store = ck.kode_store 
                 AND erp.kode_supp = ck.kode_supp
        ) as summary_data
        WHERE status_cek != 'OK'  -- HANYA AMBIL YANG BERMASALAH
    ";

    if (!empty($search_faktur)) {
        $sql_check_erp .= " AND no_faktur LIKE '$filter_search_param'";
    }

    $stmt_sum = $conn->prepare($sql_check_erp);
    $stmt_sum->bind_param($types_agg, ...$params_agg);
    $stmt_sum->execute();
    $res_sum = $stmt_sum->get_result();
    while ($row = $res_sum->fetch_assoc()) {
        if ($row['status_cek'] == 'MISSING') {
            $response['summary']['total_belum_ada'] += $row['total_erp_calc']; 
            
            if (count($response['summary']['list_belum_ada']) < 50) {
                $response['summary']['list_belum_ada'][] = [
                    'tgl_tiba' => $row['tgl_koreksi'],
                    'no_faktur' => $row['no_faktur'],
                    'total' => $row['total_erp_calc']
                ];
            }
        } elseif ($row['status_cek'] == 'DIFF') {
            $response['summary']['total_selisih']++;
            $selisih = abs($row['total_erp_calc'] - $row['total_scan']);
            $response['summary']['total_selisih_rupiah'] += $selisih;
            
            if (count($response['summary']['list_selisih']) < 50) {
                $response['summary']['list_selisih'][] = [
                    'tgl_tiba' => $row['tgl_koreksi'],
                    'no_faktur' => $row['no_faktur'],
                    'total' => $selisih
                ];
            }
        }
    }
    $where_nf = ["ck.tgl_koreksi BETWEEN ? AND ?"];
    $params_nf = [$tgl_mulai, $tgl_selesai];
    $types_nf = "ss";
    if (!empty($kode_store)) {
        $where_nf[] = "ck.kode_store = ?";
        $params_nf[] = $kode_store;
        $types_nf .= "s";
    }
    $where_sql_nf = implode(" AND ", $where_nf);
    $sql_nf = "
        SELECT ck.tgl_koreksi, ck.no_faktur, ck.total_koreksi
        FROM c_koreksi ck
        WHERE $where_sql_nf
        AND NOT EXISTS (
            SELECT 1 FROM koreksi k 
            WHERE k.no_faktur = ck.no_faktur AND k.kd_store = ck.kode_store AND k.kode_supp = ck.kode_supp
        )
    ";
    if (!empty($search_faktur)) {
        $sql_nf .= " AND ck.no_faktur LIKE ?";
        $params_nf[] = $filter_search_param;
        $types_nf .= "s";
    }
    $stmt_nf = $conn->prepare($sql_nf);
    $stmt_nf->bind_param($types_nf, ...$params_nf);
    $stmt_nf->execute();
    $res_nf = $stmt_nf->get_result();
    while ($row = $res_nf->fetch_assoc()) {
        $response['summary']['total_tidak_ditemukan']++;
        if (count($response['summary']['list_tidak_ditemukan']) < 50) {
            $response['summary']['list_tidak_ditemukan'][] = [
                'tgl_tiba' => $row['tgl_koreksi'],
                'no_faktur' => $row['no_faktur'],
                'total' => $row['total_koreksi']
            ];
        }
    }
    $where_main = ["ck.tgl_koreksi BETWEEN ? AND ?"];
    $params_main = [$tgl_mulai, $tgl_selesai];
    $types_main = "ss";
    if (!empty($kode_store)) {
        $where_main[] = "ck.kode_store = ?";
        $params_main[] = $kode_store;
        $types_main .= "s";
    }
    if (!empty($search_faktur)) {
        $where_main[] = "ck.no_faktur LIKE ?";
        $params_main[] = $filter_search_param;
        $types_main .= "s";
    }
    $where_sql_main = implode(" AND ", $where_main);
    $sql_count = "SELECT COUNT(*) as total FROM c_koreksi ck WHERE $where_sql_main";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param($types_main, ...$params_main);
    $stmt_count->execute();
    $total_rows = $stmt_count->get_result()->fetch_assoc()['total'] ?? 0;
    $response['pagination']['total_rows'] = (int) $total_rows;
    $response['pagination']['total_pages'] = ceil($total_rows / $limit);
    $response['pagination']['current_page'] = $page;
    $response['pagination']['limit'] = $limit;
    $response['pagination']['offset'] = $offset;
    $params_limit = $params_main;
    $params_limit[] = $limit;
    $params_limit[] = $offset;
    $types_limit = $types_main . "ii";
    $sql_ck = "
        SELECT ck.*, ks.Nm_Alias 
        FROM c_koreksi ck
        LEFT JOIN kode_store ks ON ck.kode_store = ks.kd_store
        WHERE $where_sql_main
        ORDER BY ck.no_faktur ASC
        LIMIT ? OFFSET ?
    ";
    $stmt_ck = $conn->prepare($sql_ck);
    $stmt_ck->bind_param($types_limit, ...$params_limit);
    $stmt_ck->execute();
    $res_ck = $stmt_ck->get_result();
    $temp_data = [];
    $faktur_list = [];
    while ($row = $res_ck->fetch_assoc()) {
        $row['total_koreksi'] = floatval($row['total_koreksi']);
        $row['total_erp'] = 0; 
        $row['status_data'] = 'NOT_FOUND_IN_ERP';
        $row['nilai_selisih_row'] = 0;
        $key = $row['kode_store'] . "_" . $row['no_faktur'] . "_" . $row['kode_supp'];
        $temp_data[] = $row;
        if (!in_array($row['no_faktur'], $faktur_list)) {
            $faktur_list[] = $row['no_faktur'];
        }
    }
    $erp_map = [];
    if (!empty($faktur_list) && !empty($kode_store)) {
        $placeholders = implode(',', array_fill(0, count($faktur_list), '?'));
        $sql_lookup = "
            SELECT 
                k.no_faktur, k.kd_store, k.kode_supp,
                SUM((
                    CASE 
                        WHEN k.keterangan = 'Minus System' THEN k.sel_qty 
                        WHEN k.keterangan = 'Stock Opname' THEN k.sel_qty 
                        ELSE 0 
                    END * (k.harga_beli + IFNULL(k.ppn_kor, 0))
                )) as total_erp_calc
            FROM koreksi k
            WHERE k.kd_store = ? 
            AND k.no_faktur IN ($placeholders)
            GROUP BY k.no_faktur, k.kd_store, k.kode_supp
        ";
        $stmt_lookup = $conn->prepare($sql_lookup);
        $types_lookup = "s" . str_repeat('s', count($faktur_list));
        $params_lookup = array_merge([$kode_store], $faktur_list);
        $stmt_lookup->bind_param($types_lookup, ...$params_lookup);
        $stmt_lookup->execute();
        $res_lookup = $stmt_lookup->get_result();
        while ($r = $res_lookup->fetch_assoc()) {
            $key = $r['kd_store'] . "_" . $r['no_faktur'] . "_" . $r['kode_supp'];
            $erp_map[$key] = floatval($r['total_erp_calc']);
        }
    }
    foreach ($temp_data as $row) {
        $key = $row['kode_store'] . "_" . $row['no_faktur'] . "_" . $row['kode_supp'];
        if (isset($erp_map[$key])) {
            $total_erp = $erp_map[$key];
            $total_scan = $row['total_koreksi'];
            $row['total_erp'] = $total_erp;
            if (abs($total_erp - $total_scan) > 100) {
                $row['status_data'] = 'DIFF';
                $row['nilai_selisih_row'] = $total_erp - $total_scan;
            } else {
                $row['status_data'] = 'MATCH';
                $row['nilai_selisih_row'] = 0;
            }
        } else {
        }
        $response['tabel_data'][] = $row;
    }
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}
echo json_encode($response);
?>