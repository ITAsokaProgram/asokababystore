<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';

try {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        throw new Exception('Token tidak ditemukan');
    }
    if (!verify_token($matches[1]))
        throw new Exception('Token tidak valid');

    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    $id = isset($input['id']) ? (int) $input['id'] : 0;

    if ($id === 0)
        throw new Exception("ID tidak valid");

    $query = "DELETE FROM serah_terima_nota WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => "Data berhasil dihapus."]);
    } else {
        throw new Exception("Gagal menghapus data.");
    }

} catch (Exception $e) {
    http_response_code(200);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>