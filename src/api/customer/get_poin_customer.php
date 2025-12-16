<?php
include "../../../aa_kon_sett.php";
include "../../auth/verify_tokens.php";

header("Content-Type: application/json");
header("Access-Allow-Methods: GET, POST");

$headers = getallheaders();
$inputJSON = file_get_contents("php://input");
$data = json_decode($inputJSON, true);

if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Authorization header missing']);
    exit;
}

$authHeader = $headers['Authorization'];
$token = str_replace('Bearer ', '', $authHeader);

$verify = verify_token($token);
if (!$verify) {
    http_response_code(401);
    echo json_encode(['status' => 'unauthorized', 'message' => 'Token tidak valid atau tidak memiliki email']);
    exit;
}

$kd_cust = $data['kode'] ?? $verify->no_hp;

$sql = "SELECT 
    (
        COALESCE((SELECT SUM(point_1) FROM point_kasir WHERE kd_cust = c.kd_cust), 0) + 
        COALESCE((SELECT SUM(jum_point) FROM point_manual WHERE kd_cust = c.kd_cust), 0) - 
        COALESCE((SELECT SUM(jum_point) FROM point_trans WHERE kd_cust = c.kd_cust), 0)
    ) AS total_poin_pk_pm
FROM customers c
WHERE c.kd_cust = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $kd_cust);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal Query Database']);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $finalData = [$row];

    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Data berhasil di fetch', 'data' => $finalData]);
} else {
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Data user tidak ditemukan', 'data' => [['total_poin_pk_pm' => 0]]]);
}
?>