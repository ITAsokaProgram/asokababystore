<?php

require_once __DIR__ . ("/../../../aa_kon_sett.php");
require_once __DIR__ . ("/../../auth/middleware_login.php");

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

$sql = "SELECT ua.id_user, ua.nama_lengkap AS nama, rating, 
komentar, review.kategori, review.no_bon AS bon,
k.nm_alias AS cabang, 
dibuat_tgl AS tanggal, 
ua.no_hp AS no_hp ,
t.nama_kasir,
rf.path_file AS enpoint_foto
FROM review 
LEFT JOIN user_asoka ua ON ua.id_user = review.id_user
LEFT JOIN kode_store k ON k.kd_store = SUBSTRING(review.no_bon, 1, 4)
LEFT JOIN trans_b t ON t.kode_kasir = SUBSTRING(review.no_bon, 6,6)
LEFT JOIN review_foto rf ON rf.review_id = review.id
GROUP BY review.no_bon ORDER BY tanggal DESC
";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server Error"]);
}   
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    http_response_code(200);
    $row = $result->fetch_all(MYSQLI_ASSOC);
    $data = array_map(function ($item) {
        return [
            'id_user' => $item['id_user'],
            'nama' => $item['nama'],
            'hp' => $item['no_hp'],
            'rating' => $item['rating'],
            'komentar' => $item['komentar'],
            'kategori' => $item['kategori'],
            'no_bon' => $item['bon'],
            'cabang' => $item['cabang'],
            'nama_kasir' => $item['nama_kasir'],
            'enpoint_foto' => $item['enpoint_foto'],
            'tanggal' => $item['tanggal']
        ];
    }, $row);
    echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_SLASHES);
} else {
    http_response_code(204);
    echo json_encode(['status' => 'error', 'message' => 'Data tidak ada']);
}
$stmt->close();
$conn->close();
