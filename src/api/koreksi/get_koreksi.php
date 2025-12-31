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

    // --- 1. MEMBUAT SUBQUERY AGREGASI ERP (Tabel koreksi) ---
    // Karena koreksi per item, kita harus SUM dulu by faktur
    // Asumsi Total = (qty_kor * harga_beli) + ppn_kor (sesuaikan jika rumus beda)
    $sql_erp_agg = "
        SELECT 
            k.no_faktur, 
            k.kd_store, 
            k.kode_supp,
            DATE(k.tgl_koreksi) as tgl_koreksi,
            SUM((k.qty_kor * k.harga_beli) + IFNULL(k.ppn_kor, 0)) as total_erp_calc
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

    // Grouping
    $sql_erp_agg .= " GROUP BY k.no_faktur, k.kd_store, k.kode_supp";

    // --- 2. SUMMARY STATS (Logic mirip Receipt) ---

    // A. FIND MISSING & DIFF (Perspective ERP vs Scan)
    // Kita jalankan query terpisah atau CTE. Karena MySQL 5.7 support CTE terbatas di beberapa hosting, 
    // kita gunakan pendekatan Left Join ke Subquery.

    // Siapkan WHERE dasar untuk filter
    $filter_search_param = "%" . $search_faktur . "%";

    // --- LOGIC SUMMARY: FIND MISSING (Ada di ERP, Belum di Scan) & DIFF ---
    // Kita ambil data dari Agregasi ERP, Left Join ke c_koreksi

    // Hack: Kita buat temporary table atau derived table untuk performa jika data besar,
    // tapi untuk sekarang kita inject subquery langsung.

    $sql_check_erp = "
        SELECT 
            erp.tgl_koreksi,
            erp.no_faktur,
            erp.total_erp_calc,
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
    ";

    // Tambahkan filter search jika ada (di level summary juga sebaiknya di filter)
    if (!empty($search_faktur)) {
        $sql_check_erp .= " WHERE erp.no_faktur LIKE '$filter_search_param'";
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

    // B. FIND NOT FOUND (Ada di Scan, Tidak ada di ERP)
    // Query c_koreksi LEFT JOIN koreksi
    $where_nf = ["ck.tgl_koreksi BETWEEN ? AND ?"];
    $params_nf = [$tgl_mulai, $tgl_selesai];
    $types_nf = "ss";

    if (!empty($kode_store)) {
        $where_nf[] = "ck.kode_store = ?";
        $params_nf[] = $kode_store;
        $types_nf .= "s";
    }

    // Check keberadaan di tabel koreksi (cukup cek 1 row exist)
    // Kita gunakan NOT EXISTS untuk performa lebih baik daripada LEFT JOIN ke tabel besar
    $where_sql_nf = implode(" AND ", $where_nf);
    $sql_nf = "
        SELECT ck.tgl_koreksi, ck.no_faktur, ck.total_koreksi
        FROM c_koreksi ck
        WHERE $where_sql_nf
        AND NOT EXISTS (
            SELECT 1 FROM koreksi k 
            WHERE k.no_faktur = ck.no_faktur 
            AND k.kd_store = ck.kode_store
            AND k.kode_supp = ck.kode_supp
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

    // --- 3. MAIN TABLE DATA (Based on c_koreksi joined with ERP Aggregation) ---
    // Kita tampilkan apa yang sudah di-scan (c_koreksi)

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

    // Hitung Total Rows untuk Pagination
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

    // Ambil Data Utama
    // Disini kita perlu join lagi dengan subquery agregasi ERP untuk status per baris
    // Karena kita tidak bisa bind param di dalam subquery join dengan mudah di mysqli raw,
    // Kita join ke tabel koreksi langsung dengan group by di luar (atau subquery statis).
    // Untuk keamanan dan kemudahan, kita akan fetch ERP value via subquery di SELECT clause 
    // (karena limit per page cuma 100, subquery per row masih acceptable untuk DB modern)

    // Namun agar lebih clean, kita gunakan derived table join pattern
    // Note: Kita butuh parameter tanggal lagi untuk subquery erp agar index terpakai

    // params untuk main query final
    $params_final = array_merge($params_main, $params_main); // dua kali karena dipakai di join conditions/select kalau kompleks, tapi mari sederhanakan

    // Query Clean:
    $params_main[] = $limit;
    $params_main[] = $offset;
    $types_main .= "ii";

    $sql_data = "
        SELECT 
            ck.*,
            ks.Nm_Alias,
            (
                SELECT SUM((k.qty_kor * k.harga_beli) + IFNULL(k.ppn_kor, 0))
                FROM koreksi k 
                WHERE k.no_faktur = ck.no_faktur 
                AND k.kd_store = ck.kode_store
                AND k.kode_supp = ck.kode_supp
            ) as total_erp,
            CASE 
                WHEN NOT EXISTS (SELECT 1 FROM koreksi k2 WHERE k2.no_faktur = ck.no_faktur AND k2.kd_store = ck.kode_store) THEN 'NOT_FOUND_IN_ERP'
                ELSE 'CHECK_DIFF' -- Akan divalidasi logic PHP
            END as status_base
        FROM c_koreksi ck
        LEFT JOIN kode_store ks ON ck.kode_store = ks.kd_store
        WHERE $where_sql_main
        ORDER BY ck.tgl_koreksi DESC, ck.kode_store ASC
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql_data);
    $stmt->bind_param($types_main, ...$params_main);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $total_erp = floatval($row['total_erp'] ?? 0);
        $total_scan = floatval($row['total_koreksi'] ?? 0);

        // Tentukan Status Final
        if ($row['status_base'] == 'NOT_FOUND_IN_ERP') {
            $row['status_data'] = 'NOT_FOUND_IN_ERP';
            $row['nilai_selisih_row'] = 0;
        } else {
            if (abs($total_erp - $total_scan) > 100) { // Toleransi 100 perak
                $row['status_data'] = 'DIFF';
                $row['nilai_selisih_row'] = $total_erp - $total_scan;
            } else {
                $row['status_data'] = 'MATCH';
                $row['nilai_selisih_row'] = 0;
            }
        }

        // Pastikan total_erp dikirim ke frontend
        $row['total_erp'] = $total_erp;
        $response['tabel_data'][] = $row;
    }

    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}
echo json_encode($response);
?>