<?php
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
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
    $verif = authenticate_request();
    $kd_user = $verif->id ?? $verif->kode ?? 0;
    $printed_by = $kd_user;

    $tgl_awal = $_GET['tgl_terima_awal'] ?? date('Y-m-d');
    $tgl_akhir = $_GET['tgl_terima_akhir'] ?? date('Y-m-d');
    $search_supplier = $_GET['search_supplier'] ?? '';
    $where_conditions = "visibilitas = 'Aktif' AND status = 'Sudah Terima'";
    $where_conditions .= " AND tgl_diterima BETWEEN ? AND ?";
    $bind_types = 'ss';
    $bind_params = [$tgl_awal, $tgl_akhir];
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
        )";
        $bind_types .= 'ssssss';
        $termRaw = '%' . $search_raw . '%';
        $termNumeric = '%' . $search_numeric . '%';
        array_push(
            $bind_params,
            $termRaw,
            $termRaw,
            $termRaw,
            $termRaw,
            $termNumeric,
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
    $sql_data = "SELECT tgl_nota, tgl_diterima, kode_supplier, nama_supplier, no_faktur, no_faktur_format, nominal, penerima 
                 FROM serah_terima_nota 
                 WHERE $where_conditions 
                 ORDER BY tgl_diterima ASC, nama_supplier ASC";
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
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    $response['error'] = $e->getMessage();
}
echo json_encode($response);
?>