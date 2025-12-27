<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';

try {
    if (!$conn)
        throw new Exception("Koneksi Database Gagal");

    // 1. Ambil Parameter Filter
    $filter_type = $_GET['filter_type'] ?? 'month';
    $bulan = $_GET['bulan'] ?? date('m');
    $tahun = $_GET['tahun'] ?? date('Y');
    $tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-d');
    $tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');
    $search_query = $_GET['search_query'] ?? '';
    $kd_store = $_GET['kd_store'] ?? 'all';
    $status_bayar = $_GET['status_bayar'] ?? 'all';

    // 2. Build Query Conditions (WHERE)
    $where_conditions = "1=1";
    $bind_types = "";
    $bind_params = [];

    // Filter Periode
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

    // Filter Store
    if ($kd_store != 'all') {
        $where_conditions .= " AND bb.kode_store = ?";
        $bind_types .= 's';
        $bind_params[] = $kd_store;
    }

    // Filter Status Bayar
    if ($status_bayar === 'paid') {
        $where_conditions .= " AND bb.tanggal_bayar IS NOT NULL";
    } elseif ($status_bayar === 'unpaid') {
        $where_conditions .= " AND bb.tanggal_bayar IS NULL";
    }

    // Filter Pencarian
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

    // 3. Logic Grouping
    $group_logic = "CASE WHEN bb.group_id IS NOT NULL AND bb.group_id != '' THEN bb.group_id ELSE bb.id END";

    // 4. Query Utama - PERBAIKAN DI SINI (SEPARATOR '\n')
    $sql = "
        SELECT 
            MAX(bb.tgl_nota) as sort_date,
            MAX(bb.id) as id,

            GROUP_CONCAT(DISTINCT bb.tgl_nota ORDER BY bb.tgl_nota DESC SEPARATOR ', ') as tgl_nota,
            
            -- Perbaikan: Menggunakan '\\n' bukan CHAR(10)
            GROUP_CONCAT(DISTINCT bb.no_faktur ORDER BY bb.no_faktur DESC SEPARATOR '\n') as no_faktur,
            
            GROUP_CONCAT(DISTINCT ks.Nm_Alias ORDER BY ks.Nm_Alias ASC SEPARATOR ', ') as Nm_Alias,
            GROUP_CONCAT(DISTINCT bb.kode_store SEPARATOR ', ') as kode_store,

            SUM(bb.potongan) as potongan,
            SUM(bb.nilai_faktur) as nilai_faktur,

            MAX(bb.nama_supplier) as nama_supplier,
            MAX(bb.kode_supplier) as kode_supplier,
            MAX(bb.ket) as ket,
            
            MAX(bb.total_bayar) as total_bayar,
            MAX(bb.tanggal_bayar) as tanggal_bayar,
            MAX(bb.store_bayar) as store_bayar,
            
            MAX(ks_bayar.Nm_Alias) as Nm_Alias_Bayar

        FROM buku_besar bb
        LEFT JOIN kode_store ks ON bb.kode_store = ks.Kd_Store
        LEFT JOIN kode_store ks_bayar ON bb.store_bayar = ks_bayar.Kd_Store
        
        WHERE $where_conditions
        
        GROUP BY $group_logic
        
        ORDER BY sort_date DESC, id DESC
    ";

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

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>