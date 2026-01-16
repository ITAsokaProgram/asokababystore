<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../helpers/finance_log_helper.php';
try {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        http_response_code(401);
        throw new Exception("Token tidak ditemukan");
    }
    $verif = verify_token($matches[1]);
    if (!$verif) {
        http_response_code(401);
        throw new Exception("Token tidak valid");
    }
    $kd_user = $verif->id ?? $verif->kode ?? 0;
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    $id = isset($input['id']) ? (int) $input['id'] : 0;
    if (!$id)
        throw new Exception("ID Invalid");
    $qCheck = "SELECT * FROM buku_besar WHERE id = ?";
    $stmtCheck = $conn->prepare($qCheck);
    $stmtCheck->bind_param("i", $id);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();
    if ($resCheck->num_rows === 0) {
        throw new Exception("Data tidak ditemukan");
    }
    $oldData = $resCheck->fetch_assoc();
    $no_faktur = $oldData['no_faktur'] ?? '-';
    $stmtCheck->close();
    $stmt = $conn->prepare("DELETE FROM buku_besar WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        write_finance_log(
            $conn,
            $kd_user,
            'buku_besar',
            $no_faktur,
            'DELETE',
            $oldData,
            null
        );
        echo json_encode(['success' => true, 'message' => 'Data dihapus']);
    } else {
        throw new Exception("Gagal hapus data");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>