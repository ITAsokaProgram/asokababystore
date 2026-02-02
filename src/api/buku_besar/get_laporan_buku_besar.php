<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
try {
    if (!$conn) {
        throw new Exception("Koneksi Database Gagal");
    }
    $filter_type = $_GET['filter_type'] ?? 'month';
    $filter_status = $_GET['filter_status'] ?? 'all';
    $bulan = $_GET['bulan'] ?? date('m');
    $tahun = $_GET['tahun'] ?? date('Y');
    $tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-d');
    $tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');
    $search_query = $_GET['search_query'] ?? '';
    $kd_store = $_GET['kd_store'] ?? 'all';
    $status_bayar = $_GET['status_bayar'] ?? 'all';
    $top_mulai = $_GET['top_mulai'] ?? '';
    $top_selesai = $_GET['top_selesai'] ?? '';
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
    if (!empty($top_mulai) && !empty($top_selesai)) {
        $where_conditions .= " AND bb.top BETWEEN ? AND ?";
        $bind_types .= 'ss';
        $bind_params[] = $top_mulai;
        $bind_params[] = $top_selesai;
    }
    if (!empty($search_query)) {
        $search_raw = trim($search_query);
        $search_numeric = str_replace(['.', ','], '', $search_raw);
        $where_conditions .= " AND (
            bb.nama_supplier LIKE ? OR 
            bb.kode_supplier LIKE ? OR 
            bb.no_faktur LIKE ? OR 
            bb.ket LIKE ? OR 
            CAST(bb.total_bayar AS CHAR) LIKE ?
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
            MAX(bb.dibuat_pada) as dibuat_pada,
            GROUP_CONCAT(DISTINCT bb.tgl_nota ORDER BY bb.tgl_nota DESC SEPARATOR '<br>') as tgl_nota,
            GROUP_CONCAT(bb.no_faktur ORDER BY bb.id ASC SEPARATOR '<br>') as no_faktur,
            GROUP_CONCAT(COALESCE(ks.Nm_Alias, bb.kode_store) ORDER BY bb.id ASC SEPARATOR '<br>') as Nm_Alias,
            GROUP_CONCAT(bb.kode_store ORDER BY bb.id ASC SEPARATOR '<br>') as kode_store,
            GROUP_CONCAT(COALESCE(bb.status, '-') ORDER BY bb.id ASC SEPARATOR '|') as list_status,
            GROUP_CONCAT(COALESCE(bb.top, '-') ORDER BY bb.id ASC SEPARATOR '|') as list_top,
            GROUP_CONCAT(bb.nilai_faktur ORDER BY bb.id ASC SEPARATOR '|') as list_nilai_faktur,
            MAX(bb.nama_supplier) as nama_supplier,
            MAX(bb.kode_supplier) as kode_supplier,
            MAX(bb.ket) as ket,
            COALESCE(
                (SELECT SUM(ba.nominal_bayar) FROM buku_besar_angsuran ba WHERE ba.buku_besar_id = MAX(bb.id)),
                MAX(bb.total_bayar)
            ) as total_bayar,
            MAX(bb.tanggal_bayar) as tanggal_bayar,
            MAX(bb.store_bayar) as Nm_Alias_Bayar,
            SUM(bb.nilai_faktur) as sum_nilai_faktur,
            SUM(bb.potongan) as sum_potongan,
            SUM(bb.nilai_tambahan) as sum_tambahan, -- TAMBAHAN (Pastikan kolom ini ada di table parent untuk legacy/sum)
            (SELECT COUNT(*) FROM buku_besar_angsuran ba WHERE ba.buku_besar_id = MAX(bb.id)) as history_count
        FROM buku_besar bb
        LEFT JOIN kode_store ks ON bb.kode_store = ks.Kd_Store
        WHERE $where_conditions
        GROUP BY $group_logic
        ORDER BY dibuat_pada DESC, id DESC
        LIMIT ? OFFSET ?
    ";
    $stmt = $conn->prepare($sql);
    $params_exec = $bind_params;
    $params_exec[] = $limit;
    $params_exec[] = $offset;
    $types_exec = $bind_types . 'ii';
    $stmt->bind_param($types_exec, ...$params_exec);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    $all_group_ids = [];
    $all_single_ids = [];
    while ($row = $result->fetch_assoc()) {
        $row['details_potongan_list'] = []; 
        $row['details_tambahan_list'] = []; 
        if (!empty($row['group_id'])) {
            $all_group_ids[] = "'" . $conn->real_escape_string($row['group_id']) . "'";
        } else {
            $all_single_ids[] = $row['id'];
        }
        $data[] = $row;
    }
    $where_parts = [];
    if (!empty($all_group_ids)) {
        $grp_str = implode(",", $all_group_ids);
        $where_parts[] = "bb.group_id IN ($grp_str)";
    }
    if (!empty($all_single_ids)) {
        $id_str = implode(",", $all_single_ids);
        $where_parts[] = "bb.id IN ($id_str)";
    }
    $potongan_map = [];
    if (!empty($where_parts)) {
        $sql_pot = "
            SELECT bbp.nominal, bbp.keterangan, bb.group_id, bb.id as parent_id
            FROM buku_besar_potongan bbp
            JOIN buku_besar bb ON bbp.buku_besar_id = bb.id
            WHERE " . implode(" OR ", $where_parts);
        $res_pot = $conn->query($sql_pot);
        if ($res_pot) {
            while ($p = $res_pot->fetch_assoc()) {
                $key = !empty($p['group_id']) ? $p['group_id'] : $p['parent_id'];
                $potongan_map[$key][] = ['nominal' => (float)$p['nominal'], 'keterangan' => $p['keterangan']];
            }
        }
    }
    $tambahan_map = [];
    if (!empty($where_parts)) {
        $sql_tam = "
            SELECT bbt.nominal, bbt.keterangan, bb.group_id, bb.id as parent_id
            FROM buku_besar_tambahan bbt
            JOIN buku_besar bb ON bbt.buku_besar_id = bb.id
            WHERE " . implode(" OR ", $where_parts);
        $res_tam = $conn->query($sql_tam);
        if ($res_tam) {
            while ($t = $res_tam->fetch_assoc()) {
                $key = !empty($t['group_id']) ? $t['group_id'] : $t['parent_id'];
                $tambahan_map[$key][] = ['nominal' => (float)$t['nominal'], 'keterangan' => $t['keterangan']];
            }
        }
    }
    foreach ($data as &$row) {
        $key = !empty($row['group_id']) ? $row['group_id'] : $row['id'];
        if (isset($potongan_map[$key])) {
            $row['details_potongan_list'] = $potongan_map[$key];
        } else {
            if ($row['sum_potongan'] > 0) {
                 $row['details_potongan_list'][] = ['nominal' => $row['sum_potongan'], 'keterangan' => 'Potongan (Legacy)'];
            }
        }
        if (isset($tambahan_map[$key])) {
            $row['details_tambahan_list'] = $tambahan_map[$key];
        } else {
            if (isset($row['sum_tambahan']) && $row['sum_tambahan'] > 0) {
                 $row['details_tambahan_list'][] = ['nominal' => $row['sum_tambahan'], 'keterangan' => 'Tambahan (Legacy)'];
            }
        }
    }
    unset($row);
    $sql_stores = "SELECT Kd_Store as kd_store, Nm_Alias as nm_alias FROM kode_store ORDER BY Nm_Alias ASC";
    $res_stores = $conn->query($sql_stores);
    $stores = [];
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
    if (isset($conn)) $conn->close();
}
?>