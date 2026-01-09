<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';

try {
    if (!$conn)
        throw new Exception("Koneksi Database Gagal");

    // Params (Sama dengan get data tapi tanpa pagination limit)
    $filter_type = $_GET['filter_type'] ?? 'month';
    $bulan = $_GET['bulan'] ?? date('m');
    $tahun = $_GET['tahun'] ?? date('Y');
    $tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-d');
    $tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');
    $kd_store = $_GET['kd_store'] ?? 'all';
    $search_query = $_GET['search_query'] ?? '';

    $where_conditions = "1=1";
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

    if (!empty($search_query)) {
        $search_raw = trim($search_query);
        $term = '%' . $search_raw . '%';
        $where_conditions .= " AND (ps.nama_supplier LIKE ? OR ps.nomor_dokumen LIKE ? OR ps.pic LIKE ? OR ps.nama_program LIKE ?)";
        $bind_types .= 'ssss';
        $bind_params[] = $term;
        $bind_params[] = $term;
        $bind_params[] = $term;
        $bind_params[] = $term;
    }

    // Query tanpa LIMIT
    $sql = "SELECT ps.* FROM program_supplier ps 
            WHERE $where_conditions 
            ORDER BY ps.top_date DESC, ps.dibuat_pada DESC";

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
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>