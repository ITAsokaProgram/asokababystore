<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
try {
    if (!$conn)
        throw new Exception("Koneksi Database Gagal");
    $filter_type = $_GET['filter_type'] ?? 'month';
    $filter_status = $_GET['filter_status'] ?? 'all';
    $bulan = $_GET['bulan'] ?? date('m');
    $tahun = $_GET['tahun'] ?? date('Y');
    $tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-d');
    $tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');
    $search_query = $_GET['search_query'] ?? '';
    $kd_store = $_GET['kd_store'] ?? 'all';
    $status_bayar = $_GET['status_bayar'] ?? 'all';
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = 50;
    $offset = ($page - 1) * $limit;
    $where_conditions = "1=1";
    $bind_types = "";
    $bind_params = [];
    if ($filter_status !== 'all') {
        $where_conditions .= " AND bb.status = ?";
        $bind_types .= 's';
        $bind_params[] = $filter_status;
    }

    if ($filter_type === 'month') {
        $where_conditions .= " AND MONTH(bb.tgl_nota) = ? AND YEAR(bb.tgl_nota) = ?";
        $bind_types .= 'ss';
        $bind_params[] = $bulan;
        $bind_params[] = $tahun;
    } else {
        $where_conditions .= " AND DATE(bb.tgl_nota) BETWEEN ? AND ?";
        $bind_types .= 'ss';
        $bind_params[] = $tgl_mulai;
        $bind_params[] = $tgl_selesai;
    }
    if ($kd_store != 'all') {
        $where_conditions .= " AND bb.kode_store = ?";
        $bind_types .= 's';
        $bind_params[] = $kd_store;
    }
    if ($status_bayar === 'paid') {
        $where_conditions .= " AND bb.tanggal_bayar IS NOT NULL";
    } elseif ($status_bayar === 'unpaid') {
        $where_conditions .= " AND bb.tanggal_bayar IS NULL";
    }
    if (!empty($search_query)) {
        $search_raw = trim($search_query);
        $search_numeric = str_replace(['.', ','], '', $search_raw);
        $where_conditions .= " AND (
            bb.nama_supplier LIKE ? OR bb.kode_supplier LIKE ? OR bb.no_faktur LIKE ? 
            OR bb.ket LIKE ? OR CAST(bb.total_bayar AS CHAR) LIKE ?
        )";
        $termRaw = '%' . $search_raw . '%';
        $termNumeric = '%' . $search_numeric . '%';
        $bind_types .= 'sssss';
        $bind_params[] = $termRaw;
        $bind_params[] = $termRaw;
        $bind_params[] = $termRaw;
        $bind_params[] = $termRaw;
        $bind_params[] = $termNumeric;
    }
    $group_logic = "CASE WHEN bb.group_id IS NOT NULL AND bb.group_id != '' THEN bb.group_id ELSE bb.id END";
    $sql_count = "SELECT COUNT(DISTINCT $group_logic) as total FROM buku_besar bb WHERE $where_conditions";
    $stmt_count = $conn->prepare($sql_count);
    if (!empty($bind_params)) {
        $stmt_count->bind_param($bind_types, ...$bind_params);
    }
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $total_rows = $result_count->fetch_assoc()['total'];
    $total_pages = ceil($total_rows / $limit);
    $sql = "
        SELECT 
            MAX(bb.id) as id,
            MAX(bb.group_id) as group_id,
            MAX(bb.tgl_nota) as sort_date,
            dibuat_pada,
            GROUP_CONCAT(DISTINCT bb.tgl_nota ORDER BY bb.tgl_nota DESC SEPARATOR '<br>') as tgl_nota,
            GROUP_CONCAT(bb.no_faktur ORDER BY bb.id ASC SEPARATOR '<br>') as no_faktur,
            GROUP_CONCAT(COALESCE(bb.ket_potongan, '-') ORDER BY bb.id ASC SEPARATOR '<br>') as ket_potongan,
            GROUP_CONCAT(bb.potongan ORDER BY bb.id ASC SEPARATOR '|') as list_potongan,
            GROUP_CONCAT(bb.nilai_faktur ORDER BY bb.id ASC SEPARATOR '|') as list_nilai_faktur,
            GROUP_CONCAT(COALESCE(ks.Nm_Alias, bb.kode_store) ORDER BY bb.id ASC SEPARATOR '<br>') as Nm_Alias,
            GROUP_CONCAT(bb.kode_store ORDER BY bb.id ASC SEPARATOR '<br>') as kode_store,
            GROUP_CONCAT(COALESCE(bb.status, '-') ORDER BY bb.id ASC SEPARATOR '|') as list_status,
            GROUP_CONCAT(COALESCE(bb.top, '-') ORDER BY bb.id ASC SEPARATOR '|') as list_top,
            MAX(bb.nama_supplier) as nama_supplier,
            MAX(bb.kode_supplier) as kode_supplier,
            MAX(bb.ket) as ket,
            MAX(bb.total_bayar) as total_bayar,
            MAX(bb.tanggal_bayar) as tanggal_bayar,
            MAX(bb.store_bayar) as Nm_Alias_Bayar,
            SUM(bb.nilai_faktur) as sum_nilai_faktur,
            SUM(bb.potongan) as sum_potongan,
            bb.total_bayar as total_bayar,
            (SELECT COUNT(*) FROM buku_besar_angsuran ba WHERE ba.buku_besar_id = MAX(bb.id)) as history_count
        FROM buku_besar bb
        LEFT JOIN kode_store ks ON bb.kode_store = ks.Kd_Store
        WHERE $where_conditions
        GROUP BY $group_logic
        ORDER BY dibuat_pada DESC, id DESC
        LIMIT ? OFFSET ?
    ";
    $bind_types .= 'ii';
    $bind_params[] = $limit;
    $bind_params[] = $offset;
    $stmt = $conn->prepare($sql);
    if (!empty($bind_params)) {
        $stmt->bind_param($bind_types, ...$bind_params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $sql_stores = "SELECT Kd_Store as kd_store, Nm_Alias as nm_alias FROM kode_store ORDER BY Nm_Alias ASC";
    $res_stores = $conn->query($sql_stores);
    $stores = [];
    while ($r = $res_stores->fetch_assoc())
        $stores[] = $r;
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