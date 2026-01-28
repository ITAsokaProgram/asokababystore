<?php

require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Authorization");

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Request ditolak method tidak terdaftar']);
    exit;
}

$verif = authenticate_request();

$sql = "SELECT Kd_Store AS store, Nm_Store AS nama_cabang FROM kode_store";

$stmt = $conn->prepare($sql);
$stmt->execute();
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["status" => false, "message" => "Server bermasalah"]);
}
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    http_response_code(200);
    $data = json_encode(["status" => true, "message" => "Data ditemukan", "data" => $result->fetch_all(MYSQLI_ASSOC)]);
    echo $data;
} else {
    http_response_code(200);
    echo json_encode(["status" => true, "message" => "Data kosong", 'data' => []]);
}
$stmt->close();
$conn->close();
