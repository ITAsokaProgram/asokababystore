<?php

include "../../../aa_kon_sett.php";
include "../../auth/middleware_login.php";
header("Content-Type:application/json");
$headers = getallheaders();
$data = json_decode(file_get_contents("php://input"), true);

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

$kode = $data['kode'];
$sql = "SELECT 
    p.no_faktur,
    p.tanggal,
    p.jam,
    ks.Nm_Store,
    p.belanja,
    p.nama_kasir, 
    r.rating AS rating,
    r.id AS review_id,
    r.komentar as komentar,
    rd.review_id AS detail_review_id,
    rd.status AS detail_status, 
    (SELECT COUNT(*) 
     FROM review_conversation rc 
     WHERE rc.review_id = r.id 
       AND rc.sudah_dibaca = 0 
       AND rc.pengirim_type = 'admin'
    ) as unread_count,
    (SELECT COUNT(*) > 0 FROM review_conversation rc WHERE rc.review_id = r.id AND rc.pengirim_type = 'admin') as conversation_started
    FROM pembayaran_b AS p
    LEFT JOIN user_asoka AS ua ON ua.no_hp = p.kd_cust
    LEFT JOIN kode_store AS ks ON ks.Kd_Store = p.kd_store
    LEFT JOIN review AS r ON r.id_user = ua.id_user AND r.no_bon = p.no_faktur
    LEFT JOIN review_detail AS rd on rd.review_id = r.id
    WHERE ua.no_hp = ?
    ORDER BY p.tanggal DESC , p.jam DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $kode);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
$formattedData = array_map(function ($item) {
    $item['conversation_started'] = (bool) $item['conversation_started'];
    return $item;
}, $data);
http_response_code(200);
echo json_encode(['status' => 'success', 'message' => 'Data berhasil fetch', 'data' => $formattedData]);
$stmt->close();
$conn->close();