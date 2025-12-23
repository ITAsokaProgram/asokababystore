<?php

require_once __DIR__ . "./../../../aa_kon_sett.php";
require_once __DIR__ . "./../../auth/middleware_login.php";
header("Content-Type:application/json");
$header = getAllHeaders();
$authHeader = $header['Authorization'];
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $macthes)) {
    $token = $macthes[1];
}
if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak user tidak terdaftar']);
    exit;
}
$verif = verify_token($token);

$sql = "SELECT Kd_Store AS store, Nm_Alias AS nama_cabang, kode_area AS group_cabang FROM kode_store WHERE display = 'on' ORDER BY kode_Area";

$stmt = $conn->prepare($sql);
$stmt->execute();
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server bermasalah"]);
}
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    http_response_code(200);
    $data = json_encode(["data" => $result->fetch_all(MYSQLI_ASSOC)]);
    echo $data;
} else {
    http_response_code(204);
    echo json_encode(["status" => "true", "message" => "Data kosong"]);
}
$stmt->close();
$conn->close();
