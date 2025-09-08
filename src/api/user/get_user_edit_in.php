<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$headers = getallheaders();
$authHeader = $headers['Authorization'];
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1]; // ini yang aman dan baku
}
if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak user tak terdaftar']);
    exit;
}

$verif = verify_token($token);
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode tak diizinkan']);
    exit;
}
$kode = $_GET['kode'] ?? 0;

if (!$kode) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Kode tidak valid']);
    exit;
}
$sql = "SELECT ua.nama,ua.hak,ua.inisial, ua.kode , GROUP_CONCAT(ui.menu_code) AS menus, ua.kd_store AS kode_cabang
FROM user_account AS ua 
LEFT JOIN user_internal_access AS ui 
ON ua.kode = ui.id_user WHERE ua.kode = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $kode);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server Error"]);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $userData = $result->fetch_assoc();
    $response = [
        'status' => 'success',
        'data' => [
            'nama' => $userData['nama'],
            'hak' => $userData['hak'],
            'inisial' => $userData['inisial'],
            'kode' => $userData['kode'],
            'menu_code' => $userData['menus'] ? explode(',', $userData['menus']) : [],
            'kode_cabang' => $userData['kode_cabang']
        ]
    ];
    echo json_encode($response);
} else {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'User tak ditemukan']);
}
