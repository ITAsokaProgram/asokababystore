<?php

require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");
$headers = getallheaders();
if(!isset($headers['Authorization'])){
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak, token tidak ditemukan']);
    exit;
}
$authHeader = $headers['Authorization'];
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1]; // ini yang aman dan baku
}

$verif = verify_token($token);
$filter = $_GET['kategori'] ?? "";
$sql = "SELECT 
    inv.nama_kasir AS kasir,
    inv.kode_kasir AS kode,
    inv.type AS kategori,
    COUNT(inv.type) AS jml_gagal,
    s.Nm_Alias AS cabang
FROM invtrans inv
LEFT JOIN kode_store s ON s.Kd_Store = inv.kode_toko 
WHERE inv.type LIKE ?
GROUP BY inv.kode_kasir ORDER BY jml_gagal DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s",$filter);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server Error']);
}
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    http_response_code(200);
    $data = json_encode(['data' => $result->fetch_all(MYSQLI_ASSOC)]);
    echo $data;
} else {
    http_response_code(204);
    echo json_encode(['status' => 'success', 'message' => 'No data']);
}
$stmt->close();
$conn->close();
