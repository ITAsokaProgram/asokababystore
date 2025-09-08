<?php

require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");


if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Gunakan metode GET.']);
    exit;
}

$headers = getallheaders();
$authHeader = $headers['Authorization'];
if (!$authHeader) {
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak user tidak terdaftar']);
    exit;
}
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1]; // ini yang aman dan baku
}
if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak user tidak terdaftar']);
    exit;
}
$verif = verify_token($token);



$sql = "SELECT COUNT(*) as total FROM hadiah";
$stmt = $conn->prepare($sql);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $result = $result->fetch_assoc();
    http_response_code(200);
    echo json_encode(['success' => true, 'message'=>'Data ditemukan', 'total' => $result]);
} else {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Data tidak ditemukan', 'total' => 0]);
}
$stmt->close();
$conn->close();
