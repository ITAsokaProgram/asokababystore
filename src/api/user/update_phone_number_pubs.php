<?php

include "../../../aa_kon_sett.php";
include "../../auth/middleware_login.php";
header("Content-Type:application/json");
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan']);
    exit;
}
function checkPhoneBeforeSend($conn, $sql, $param)
{
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $param);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function updateNumber($conn, $sql, $email, $no)
{

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        return json_encode([
            "status" => "error",
            "message" => "Terjadi Kesalahan Pada Statement"
        ]);
    }
    $stmt->bind_param("ss", $no, $email);
    if ($stmt->execute()) {
        http_response_code(200);
        return json_encode([
            "status" => "success",
            "message" => "Berhasil Mengirim Ke Server"
        ]);
    } else {
        http_response_code(500);
        return json_encode([
            "status" => "error",
            "message" => "Terjadi Kesalahan Pada PARAMETER"
        ]);
    }
}
$cookie = $_COOKIE['customer_token'];
$generate = verify_token($cookie);
if (!$generate || !isset($generate->email)) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}


$inputJSON = file_get_contents("php://input");
$data = json_decode($inputJSON, true);
$noHp = $data['no_hp'];
$email = $generate->email;

if (!preg_match('/^08\d{7,11}$/', $noHp)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Format No HP tidak valid']);
    exit;
}

$sqlCheck = "SELECT no_hp FROM user_asoka WHERE no_hp = ?";
$checking = checkPhoneBeforeSend($conn, $sqlCheck, $noHp);
if ($checking) {
    http_response_code(409);
    echo json_encode(['status' => 'error', 'message' => 'No Hp Sudah Terdaftar']);
    exit;
} 

$sqlUpdate = "UPDATE user_asoka SET no_hp = ? WHERE email = ?";
$update = updateNumber($conn, $sqlUpdate, $email, $noHp);
echo $update;