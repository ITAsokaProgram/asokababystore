<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';

try {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        throw new Exception('Unauthorized');
    }

    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    $doc = $input['nomor_dokumen'] ?? '';

    if (empty($doc))
        throw new Exception("Nomor dokumen tidak valid");

    $stmt = $conn->prepare("DELETE FROM program_supplier WHERE nomor_dokumen = ?");
    $stmt->bind_param("s", $doc);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data berhasil dihapus']);
    } else {
        throw new Exception("Gagal menghapus data");
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>