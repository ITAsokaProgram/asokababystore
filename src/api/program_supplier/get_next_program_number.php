<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
try {
    $verif = authenticate_request();
    $kd_user = $verif->kode ?? 'system';
    $kode_cabang = $_GET['kode_cabang'] ?? '';
    if (empty($kode_cabang)) {
        echo json_encode(['success' => true, 'nomor_program' => '']);
        exit;
    }
    $clean_user = preg_replace('/[^a-zA-Z0-9]/', '', $kd_user);
    $prefix = $kode_cabang . "-PS-" . $clean_user . "-";
    $sqlGetMax = "SELECT nomor_program FROM program_supplier 
                  WHERE nomor_program LIKE ? 
                  ORDER BY LENGTH(nomor_program) DESC, nomor_program DESC 
                  LIMIT 1";
    $stmtMax = $conn->prepare($sqlGetMax);
    $likeParam = $prefix . "%";
    $stmtMax->bind_param("s", $likeParam);
    $stmtMax->execute();
    $resMax = $stmtMax->get_result();
    $next_seq = 1;
    if ($rowMax = $resMax->fetch_assoc()) {
        $last_code = $rowMax['nomor_program'];
        $last_seq = (int) str_replace($prefix, "", $last_code);
        $next_seq = $last_seq + 1;
    }
    $preview_nomor = $prefix . $next_seq;
    echo json_encode(['success' => true, 'nomor_program' => $preview_nomor]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>