<?php
session_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
header('Content-Type: application/json');
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method Not Allowed');
    }
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        http_response_code(401);
        throw new Exception('Token tidak ditemukan');
    }
    $token = $matches[1];
    if (!verify_token($token)) {
        http_response_code(401);
        throw new Exception('Token tidak valid');
    }
    $input = json_decode(file_get_contents('php://input'), true);
    $kode_user = $input['kode_user'] ?? null;
    $tipe = $input['tipe'] ?? '';
    $password = $input['password'] ?? null;
    $is_new_type = $input['is_new_type'] ?? false;
    if (empty($kode_user) || empty($password) || empty($tipe)) {
        throw new Exception('Data tidak lengkap (User, Tipe, dan Password wajib diisi).');
    }
    $conn->begin_transaction();
    if ($is_new_type) {
        $label = ucwords(trim($tipe));
        $tipe_id = strtolower(str_replace(' ', '_', trim($tipe)));
        $sqlTipe = "INSERT IGNORE INTO otorisasi_tipe (tipe, label) VALUES (?, ?)";
        $stmtTipe = $conn->prepare($sqlTipe);
        if (!$stmtTipe)
            throw new Exception("Prepare Tipe Gagal: " . $conn->error);
        $stmtTipe->bind_param("ss", $tipe_id, $label);
        $stmtTipe->execute();
        $stmtTipe->close();
        $tipe = $tipe_id;
    }
    $sqlOto = "INSERT INTO otorisasi_user (kode_user, tipe, password) 
               VALUES (?, ?, ?) 
               ON DUPLICATE KEY UPDATE password = VALUES(password)";
    $stmtOto = $conn->prepare($sqlOto);
    if (!$stmtOto)
        throw new Exception("Prepare Otorisasi Gagal: " . $conn->error);
    $stmtOto->bind_param("iss", $kode_user, $tipe, $password);
    if (!$stmtOto->execute()) {
        throw new Exception("Gagal simpan otorisasi: " . $stmtOto->error);
    }
    $stmtOto->close();
    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Otorisasi berhasil dikonfigurasi'
    ]);
} catch (Exception $e) {
    if (isset($conn) && $conn->connect_errno == 0) {
        $conn->rollback();
    }
    if (http_response_code() === 200) {
        http_response_code(400);
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>