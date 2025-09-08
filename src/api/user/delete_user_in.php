<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Methods: DELETE");
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Cek Authorization header
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? null;

if (!$authHeader || !preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Token tidak ditemukan atau format salah']);
    exit;
}

$token = $matches[1];
$verif = verify_token($token);
if (!$verif) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Token tidak valid']);
    exit;
}

// Pastikan metode DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan']);
    exit;
}

// Ambil data JSON
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['kode']) || !is_numeric($data['kode'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Kode harus berupa angka']);
    exit;
}

$kode = intval($data['kode']);

// Hapus akses user di user_internal_access
$accessStmt = $conn->prepare("DELETE FROM user_internal_access WHERE id_user = ?");
$accessStmt->bind_param("i", $kode);
$accessStmt->execute();
$accessStmt->close();

// Hapus user_account
$stmt = $conn->prepare("DELETE FROM user_account WHERE kode = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal prepare delete user']);
    exit;
}
$stmt->bind_param("i", $kode);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan']);
    $stmt->close();
    exit;
}
$stmt->close();



$conn->close();
http_response_code(200);
echo json_encode(['status' => 'success', 'message' => 'User berhasil dihapus']);
exit;
