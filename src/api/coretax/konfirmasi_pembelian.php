<?php
session_start();
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}
try {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token tidak ditemukan']);
        exit;
    }
    $token = $matches[1];
    $verif = verify_token($token);
    if (!$verif) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token tidak valid']);
        exit;
    }
    $kd_user = $verif->id ?? $verif->kode ?? 0;
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
    $nsfp = $input['nsfp'] ?? null;
    if (!$id || !$nsfp) {
        throw new Exception("ID Pembelian atau NSFP tidak valid.");
    }
    $sql = "UPDATE ff_pembelian SET ada_di_coretax = 1, nsfp = ?, kd_user = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("sii", $nsfp, $kd_user, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data berhasil dikonfirmasi ke Coretax']);
    } else {
        if ($stmt->errno === 1062) {
            http_response_code(409);
            throw new Exception("NSFP '$nsfp' sudah digunakan pada data pembelian lain. Mohon periksa kembali.");
        } else {
            throw new Exception("Database Error: " . $stmt->error);
        }
    }
    $stmt->close();
} catch (Exception $e) {
    $code = ($e->getMessage() == "Token tidak ditemukan" || $e->getMessage() == "Token tidak valid") ? 401 : 500;
    http_response_code($code);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>