<?php
include "../../../aa_kon_sett.php";
include "../../auth/verify_tokens.php";

header("Content-Type: application/json");
header("Access-Allow-Methods: GET");
// Ambil header Authorization
$headers = getallheaders();
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Authorization header missing']);
    exit;
}

$authHeader = $headers['Authorization'];
$token = str_replace('Bearer ', '', $authHeader);

// Verifikasi token
$verify = verify_token($token);
if (!$verify) {
    http_response_code(401);
    echo json_encode(['status' => 'unauthorized', 'message' => 'Token tidak valid atau tidak memiliki email']);
    exit;
}

$sql = "SELECT 
    COALESCE(tp.total_poin_pk, 0) + COALESCE(tpm.total_poin_pm, 0) - COALESCE(pt.total_poin_pt, 0)
AS total_poin_pk_pm
FROM customers c
-- Poin Kasir
LEFT JOIN (
    SELECT kd_cust, SUM(point_1) AS total_poin_pk
    FROM point_kasir
    GROUP BY kd_cust
) AS tp ON c.kd_cust = tp.kd_cust
-- Poin Manual
LEFT JOIN (
    SELECT kd_cust, SUM(jum_point) AS total_poin_pm
    FROM point_manual
    GROUP BY kd_cust
) AS tpm ON c.kd_cust = tpm.kd_cust
LEFT JOIN (
    SELECT kd_cust, SUM(jum_point) AS total_poin_pt
    FROM point_trans
    GROUP BY kd_cust
) AS pt ON c.kd_cust = pt.kd_cust
WHERE c.kd_cust = ?";
$kd_cust = $data['kode'] ?? $verify->no_hp;
$stmt = $conn->prepare($sql);
$stmt->bind_param('s',$kd_cust);

if(!$stmt){
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Gagal Query']);
    exit;
}
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
http_response_code(200);
echo json_encode(['status'=>'success','message'=>'Data berhasil di fetch','data'=>$data]);