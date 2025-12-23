<?php
session_start();
ini_set('display_errors', 0);
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
    $password = $input['password'] ?? null;



    if (empty($kode_user) || empty($password)) {
        throw new Exception('User dan Kode Otorisasi wajib diisi.');
    }


    $sql = "INSERT INTO otorisasi_user (kode_user, password) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE password = VALUES(password)";

    $stmt = $conn->prepare($sql);
    if (!$stmt)
        throw new Exception("Database Error: " . $conn->error);

    $stmt->bind_param("is", $kode_user, $password);

    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan otorisasi: " . $stmt->error);
    }

    $stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Kode Otorisasi (Permanen) berhasil disimpan'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>