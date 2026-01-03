<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';

try {
    if (!$conn)
        throw new Exception("Koneksi Database Gagal");

    $tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));
    $filter_type = $_GET['filter_type'] ?? 'month';
    $bulan = $_GET['bulan'] ?? date('m');
    $tahun = $_GET['tahun'] ?? date('Y');
    $tgl_mulai = $_GET['tgl_mulai'] ?? $tanggal_kemarin;
    $tgl_selesai = $_GET['tgl_selesai'] ?? $tanggal_kemarin;
    $search_supplier = $_GET['search_supplier'] ?? '';
    $filter_status_kontra = $_GET['status_kontra'] ?? '';
    $filter_status_bayar = $_GET['status_bayar'] ?? '';
    $filter_status_pinjam = $_GET['status_pinjam'] ?? '';

    $where_conditions = "visibilitas = 'Aktif'";
    $bind_types = "";
    $bind_params = [];

    // Filter Period
    if ($filter_type === 'month') {
        $where_conditions .= " AND MONTH(tgl_nota) = ? AND YEAR(tgl_nota) = ?";
        $bind_types .= 'ss';
        $bind_params[] = $bulan;
        $bind_params[] = $tahun;
    } else {
        $where_conditions .= " AND DATE(tgl_nota) BETWEEN ? AND ?";
        $bind_types .= 'ss';
        $bind_params[] = $tgl_mulai;
        $bind_params[] = $tgl_selesai;
    }
    if (!empty($filter_status_kontra)) {
        $where_conditions .= " AND status_kontra = ?";
        $bind_types .= 's';
        $bind_params[] = $filter_status_kontra;
    }

    if (!empty($filter_status_bayar)) {
        $where_conditions .= " AND status_bayar = ?";
        $bind_types .= 's';
        $bind_params[] = $filter_status_bayar;
    }

    if (!empty($filter_status_pinjam)) {
        $where_conditions .= " AND status_pinjam = ?";
        $bind_types .= 's';
        $bind_params[] = $filter_status_pinjam;
    }

    // Search Logic
    if (!empty($search_supplier)) {
        $search_raw = trim($search_supplier);
        $search_numeric = str_replace('.', '', $search_raw);

        $where_conditions .= " AND (
            nama_supplier LIKE ? 
            OR no_nota LIKE ? 
            OR no_faktur LIKE ? 
            OR kode_supplier LIKE ? 
            OR CAST(nominal_awal AS CHAR) LIKE ?
        )";
        $bind_types .= 'sssss';
        $termRaw = '%' . $search_raw . '%';
        $termNumeric = '%' . $search_numeric . '%';

        $bind_params[] = $termRaw;
        $bind_params[] = $termRaw;
        $bind_params[] = $termRaw;
        $bind_params[] = $termRaw;
        $bind_params[] = $termNumeric;
    }

    // Query All Data (No Limit/Offset)
    $query = "SELECT * FROM serah_terima_nota 
              WHERE $where_conditions 
              ORDER BY tgl_nota DESC, dibuat_pada DESC";

    $stmt = $conn->prepare($query);
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