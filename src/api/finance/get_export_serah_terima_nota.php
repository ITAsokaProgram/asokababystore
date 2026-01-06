<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR])) {
        if (!headers_sent()) {
            http_response_code(500);
            echo json_encode(['error' => 'Fatal Server Error: ' . $error['message']]);
        }
    }
});
$response = ['data' => [], 'error' => null];
try {
    $printed_by = $_SESSION['inisial'] ?? $_SESSION['username'] ?? 'SYSTEM';
    if (empty($printed_by)) {
        $printed_by = 'USER';
    }
    $tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));
    $filter_type = $_GET['filter_type'] ?? 'month';
    $filter_cod = $_GET['cod'] ?? '';
    $bulan = $_GET['bulan'] ?? date('m');
    $tahun = $_GET['tahun'] ?? date('Y');
    $tgl_mulai = $_GET['tgl_mulai'] ?? $tanggal_kemarin;
    $tgl_selesai = $_GET['tgl_selesai'] ?? $tanggal_kemarin;
    $search_supplier = $_GET['search_supplier'] ?? '';

    if ($filter_type === 'month') {
        $where_conditions = "visibilitas = 'Aktif' AND MONTH(tgl_nota) = ? AND YEAR(tgl_nota) = ?";
        $bind_types = 'ss';
        $bind_params = [$bulan, $tahun];
    } else {
        $where_conditions = "visibilitas = 'Aktif' AND DATE(tgl_nota) BETWEEN ? AND ?";
        $bind_types = 'ss';
        $bind_params = [$tgl_mulai, $tgl_selesai];
    }
    if (!empty($_GET['status_kontra'])) {
        $where_conditions .= " AND status_kontra = ?";
        $bind_types .= 's';
        $bind_params[] = $_GET['status_kontra'];
    }
    if (!empty($_GET['status_bayar'])) {
        $where_conditions .= " AND status_bayar = ?";
        $bind_types .= 's';
        $bind_params[] = $_GET['status_bayar'];
    }
    if (!empty($_GET['status_pinjam'])) {
        $where_conditions .= " AND status_pinjam = ?";
        $bind_types .= 's';
        $bind_params[] = $_GET['status_pinjam'];
    }
    if (!empty($filter_cod)) {
        $where_conditions .= " AND cod = ?";
        $bind_types .= 's';
        $bind_params[] = $filter_cod;
    }
    if (!empty($search_supplier)) {
        $search_raw = trim($search_supplier);
        $search_numeric = str_replace('.', '', $search_raw);

        $where_conditions .= " AND (
            nama_supplier LIKE ? 
            OR no_faktur LIKE ? 
            OR no_faktur_format LIKE ? 
            OR kode_supplier LIKE ? 
            OR CAST(nominal AS CHAR) LIKE ?
            OR penerima LIKE ?
            OR nama_bank LIKE ?
            OR no_rek LIKE ?
            OR atas_nama_rek LIKE ?
            OR cabang_penerima LIKE ?
        )";

        $bind_types .= 'ssssssssss';
        $termRaw = '%' . $search_raw . '%';
        $termNumeric = '%' . $search_numeric . '%';

        array_push(
            $bind_params,
            $termRaw,
            $termRaw,
            $termRaw,
            $termRaw,
            $termNumeric,
            $termRaw,
            $termRaw,
            $termRaw,
            $termRaw,
            $termRaw
        );
    }
    $sql_update = "UPDATE serah_terima_nota SET 
                   sudah_dicetak = 'Sudah', 
                   dicetak_oleh = ? 
                   WHERE $where_conditions 
                   AND (dicetak_oleh IS NULL OR dicetak_oleh = '')";
    $stmt_update = $conn->prepare($sql_update);
    $update_params = array_merge([$printed_by], $bind_params);
    $update_types = 's' . $bind_types;
    $stmt_update->bind_param($update_types, ...$update_params);
    $stmt_update->execute();
    $stmt_update->close();
    $sql_data = "SELECT tgl_nota, kode_supplier, nama_supplier, no_faktur, no_faktur_format, nominal, dicetak_oleh 
                 FROM serah_terima_nota 
                 WHERE $where_conditions 
                 ORDER BY tgl_nota ASC, nama_supplier ASC";
    $stmt_data = $conn->prepare($sql_data);
    $stmt_data->bind_param($bind_types, ...$bind_params);
    $stmt_data->execute();
    $result_data = $stmt_data->get_result();
    while ($row = $result_data->fetch_assoc()) {
        $response['data'][] = $row;
    }
    $stmt_data->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}
echo json_encode($response);
?>