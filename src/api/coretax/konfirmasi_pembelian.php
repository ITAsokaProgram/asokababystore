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
    $in_coretax = false;
    $in_fisik = false;
    $stmt_check_c = $conn->prepare("SELECT 1 FROM ff_coretax WHERE nsfp = ? LIMIT 1");
    $stmt_check_c->bind_param("s", $nsfp);
    $stmt_check_c->execute();
    if ($stmt_check_c->get_result()->num_rows > 0)
        $in_coretax = true;
    $stmt_check_c->close();
    $stmt_check_f = $conn->prepare("SELECT 1 FROM ff_faktur_pajak WHERE nsfp = ? LIMIT 1");
    $stmt_check_f->bind_param("s", $nsfp);
    $stmt_check_f->execute();
    if ($stmt_check_f->get_result()->num_rows > 0)
        $in_fisik = true;
    $stmt_check_f->close();
    $tipe_nsfp = '';
    if ($in_coretax && $in_fisik) {
        $tipe_nsfp = 'coretax,fisik';
    } else if ($in_coretax) {
        $tipe_nsfp = 'coretax';
    } else if ($in_fisik) {
        $tipe_nsfp = 'fisik';
    } else {
        throw new Exception("NSFP tidak ditemukan di data Coretax maupun Fisik.");
    }
    $sql = "UPDATE ff_pembelian SET ada_di_coretax = 1, nsfp = ?, tipe_nsfp = ?, kd_user = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ssii", $nsfp, $tipe_nsfp, $kd_user, $id);
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Data berhasil dikonfirmasi.',
            'tipe' => $tipe_nsfp
        ]);
    } else {
        if ($stmt->errno === 1062) {
            http_response_code(409);
            throw new Exception("NSFP '$nsfp' sudah digunakan pada data pembelian lain.");
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