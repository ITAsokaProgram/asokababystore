<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
try {
    if (!$conn)
        throw new Exception("Koneksi Database Gagal");
    $filter_type = $_GET['filter_type'] ?? 'month';
    $bulan = $_GET['bulan'] ?? date('m');
    $tahun = $_GET['tahun'] ?? date('Y');
    $tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-d');
    $tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');
    $kd_store = $_GET['kd_store'] ?? 'all';
    $filter_pic = $_GET['pic'] ?? 'all';
    $search_query = $_GET['search_query'] ?? '';
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = 50;
    $offset = ($page - 1) * $limit;
    $where_conditions = "ps.visibilitas = 'On'";
    $bind_types = "";
    $bind_params = [];
    if ($filter_type === 'month') {
        $where_conditions .= " AND MONTH(ps.top_date) = ? AND YEAR(ps.top_date) = ?";
        $bind_types .= 'ss';
        $bind_params[] = $bulan;
        $bind_params[] = $tahun;
    } else {
        $where_conditions .= " AND ps.top_date BETWEEN ? AND ?";
        $bind_types .= 'ss';
        $bind_params[] = $tgl_mulai;
        $bind_params[] = $tgl_selesai;
    }
    if ($kd_store != 'all') {
        $where_conditions .= " AND ps.kode_cabang = ?";
        $bind_types .= 's';
        $bind_params[] = $kd_store;
    }
    if ($filter_pic != 'all' && !empty($filter_pic)) {
        $where_conditions .= " AND ps.pic LIKE ?";
        $bind_types .= 's';
        $bind_params[] = '%' . trim($filter_pic) . '%';
    }
    if (!empty($search_query)) {
        $search_raw = trim($search_query);
        $term_text = '%' . $search_raw . '%';
        $search_clean_number = str_replace(['.', ','], '', $search_raw);
        $is_numeric_search = is_numeric($search_clean_number) && $search_clean_number != '';
        $term_numeric = '%' . $search_clean_number . '%';
        $search_parts = [];
        $str_cols = [
            'nama_supplier',
            'nomor_dokumen',
            'pic',
            'nama_program',
            'kode_cabang',
            'nama_cabang',
            'nsfp',
            'nomor_bukpot'
        ];
        foreach ($str_cols as $col) {
            $search_parts[] = "ps.$col LIKE ?";
            $bind_types .= 's';
            $bind_params[] = $term_text;
        }
        if ($is_numeric_search) {
            $num_cols = ['nilai_program', 'nilai_transfer', 'dpp', 'ppn', 'pph'];
            foreach ($num_cols as $col) {
                $search_parts[] = "ps.$col LIKE ?";
                $bind_types .= 's';
                $bind_params[] = $term_numeric;
            }
        }
        if (!empty($search_parts)) {
            $where_conditions .= " AND (" . implode(" OR ", $search_parts) . ")";
        }
    }
    $sql_count = "SELECT COUNT(*) as total FROM program_supplier ps WHERE $where_conditions";
    $stmt_count = $conn->prepare($sql_count);
    if (!empty($bind_params)) {
        $stmt_count->bind_param($bind_types, ...$bind_params);
    }
    $stmt_count->execute();
    $total_rows = $stmt_count->get_result()->fetch_assoc()['total'];
    $total_pages = ceil($total_rows / $limit);
    $sql = "SELECT ps.* FROM program_supplier ps 
            WHERE $where_conditions 
            ORDER BY ps.top_date DESC, ps.dibuat_pada DESC 
            LIMIT ? OFFSET ?";
    $bind_types_final = $bind_types . 'ii';
    $bind_params_final = array_merge($bind_params, [$limit, $offset]);
    $stmt = $conn->prepare($sql);
    if (!empty($bind_params_final)) {
        $stmt->bind_param($bind_types_final, ...$bind_params_final);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $stores = [];
    $res_stores = $conn->query("SELECT Kd_Store as kd_store, Nm_Alias as nm_alias FROM kode_store ORDER BY Nm_Alias ASC");
    while ($r = $res_stores->fetch_assoc()) {
        $stores[] = $r;
    }
    $pics = [];
    $res_pic = $conn->query("SELECT DISTINCT pic FROM program_supplier WHERE visibilitas = 'On' AND pic IS NOT NULL AND pic != ''");
    while ($r = $res_pic->fetch_assoc()) {
        $parts = explode(',', $r['pic']);
        foreach ($parts as $p) {
            $clean_name = trim($p);
            if (!empty($clean_name)) {
                $pics[] = $clean_name;
            }
        }
    }
    $pics = array_unique($pics);
    sort($pics);
    echo json_encode([
        'success' => true,
        'tabel_data' => $data,
        'stores' => $stores,
        'pic_list' => array_values($pics),
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_rows' => $total_rows,
            'limit' => $limit,
            'offset' => $offset
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>