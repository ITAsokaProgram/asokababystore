<?php

require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");

$headers = getallheaders();
$authHeader = $headers['Authorization'];
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
$kd_store = $verif->kd_store;
$sql = "SELECT nama, hak , kode , GROUP_CONCAT(menu_code) AS menu_code, user_account.kd_store AS kode_cabang
from user_account 
LEFT JOIN user_internal_access ON user_account.kode = user_internal_access.id_user 
GROUP BY user_account.kode";

$stmt = $conn->prepare($sql);
$stmt->execute();
if(!$stmt){
    http_response_code(500);
    echo json_encode(["status"=> "error","message"=> "Server Error"]);
}

$result = $stmt->get_result();
if($result->num_rows > 0){
    http_response_code(200);
    $data = json_encode(['data'=>$result->fetch_all(MYSQLI_ASSOC)]);
    echo $data;
} else {
    http_response_code(204);
    echo json_encode(['status'=> 'error','message'=> 'Data tidak ada']);
}

$stmt->close();
$conn->close();