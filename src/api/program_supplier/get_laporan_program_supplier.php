<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
try {
    if (!$conn)
        throw new Exception("Koneksi Database Gagal");
    // Hapus pengambilan parameter tanggal (filter_type, bulan, tahun, tgl_mulai, tgl_selesai)
    $kd_store = $_GET['kd_store'] ?? 'all';
    $search_query = $_GET['search_query'] ?? '';
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = 50;
    $offset = ($page - 1) * $limit;
    // WHERE default tanpa filter tanggal
    $where_conditions = "ps.visibilitas = 'On'";
    $bind_types = "";
    $bind_params = [];
    // Hapus logika WHERE MONTH/YEAR atau BETWEEN date
    if ($kd_store != 'all') {
        $where_conditions .= " AND ps.kode_cabang = ?";
        $bind_types .= 's';
        $bind_params[] = $kd_store;
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
    echo json_encode([
        'success' => true,
        'tabel_data' => $data,
        'stores' => $stores,
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