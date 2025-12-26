<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';

try {
    if (!$conn)
        throw new Exception("Koneksi Database Gagal");

    // Params
    $filter_type = $_GET['filter_type'] ?? 'month';
    $bulan = $_GET['bulan'] ?? date('m');
    $tahun = $_GET['tahun'] ?? date('Y');
    $tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-d');
    $tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');
    $search_query = $_GET['search_query'] ?? '';
    $kd_store = $_GET['kd_store'] ?? 'all';
    $status_bayar = $_GET['status_bayar'] ?? 'all';

    // Build Query
    $where_conditions = "1=1";
    $bind_types = "";
    $bind_params = [];

    if ($status_bayar === 'paid') {
        $where_conditions .= " AND bb.tanggal_bayar IS NOT NULL";
    } elseif ($status_bayar === 'unpaid') {
        $where_conditions .= " AND bb.tanggal_bayar IS NULL";
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

    // Query Data (No Limit)
    $sql = "
        SELECT 
            bb.*,
            ks.Nm_Alias,
            ks_bayar.Nm_Alias as Nm_Alias_Bayar
        FROM buku_besar bb
        LEFT JOIN kode_store ks ON bb.kode_store = ks.Kd_Store
        LEFT JOIN kode_store ks_bayar ON bb.store_bayar = ks_bayar.Kd_Store
        WHERE $where_conditions
        ORDER BY bb.tgl_nota DESC, bb.id DESC
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