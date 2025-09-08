<?php

include "../../../aa_kon_sett.php";
include "../../auth/middleware_login.php";
header("Content-Type:application/json");
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

$kode = $data['kode'];
$sql = "SELECT 
    no_faktur,
    tanggal,
    ks.Nm_Store,
    belanja,
    r.rating AS rating
    FROM pembayaran_b AS p
    LEFT JOIN user_asoka AS ua ON ua.no_hp = p.kd_cust
    LEFT JOIN kode_store AS ks ON ks.Kd_Store = p.kd_store
    LEFT JOIN review AS r ON r.id_user = ua.id_user AND r.no_bon = p.no_faktur
    WHERE ua.no_hp = ?
    ORDER BY tanggal DESC , jam DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s",$kode);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
http_response_code(200);
echo json_encode(['status'=>'success','message'=>'Data berhasil fetch','data'=>$data]);
$stmt->close();
$conn->close();