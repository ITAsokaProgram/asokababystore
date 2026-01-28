<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: DELETE");
ini_set('display_errors', 1);
error_reporting(E_ALL);
$verif = authenticate_request();

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan']);
    exit;
}
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['kode']) || !is_numeric($data['kode'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Kode harus berupa angka']);
    exit;
}
$kode = intval($data['kode']);

$sql = "UPDATE user_account SET aktif = 0, token = NULL, refresh_token = NULL WHERE kode = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal prepare update user']);
    exit;
}
$stmt->bind_param("i", $kode);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menonaktifkan user. Error: ' . $stmt->error
    ]);
    $stmt->close();
    exit;
}
if ($stmt->affected_rows === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan']);
    $stmt->close();
    exit;
}
$stmt->close();
$conn->close();
http_response_code(200);
echo json_encode(['status' => 'success', 'message' => 'User berhasil dinonaktifkan']);
exit;