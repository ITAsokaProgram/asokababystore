<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Content-Type: application/json");
header("Access-Allow-Methods: GET, POST");
$inputJSON = file_get_contents("php://input");
$data = json_decode($inputJSON, true);
$verify = authenticate_request();
$token_identifier = null;
if (isset($verify->no_hp)) {
    $token_identifier = $verify->no_hp;
} elseif (isset($verify->user_name)) {
    $token_identifier = $verify->user_name;
} elseif (isset($verify->data) && isset($verify->data->no_hp)) {
    $token_identifier = $verify->data->no_hp;
} elseif (isset($verify->kode)) {
    $token_identifier = $verify->kode; 
}
$kd_cust = $data['kode'] ?? $token_identifier;
if (empty($kd_cust)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Identitas customer tidak ditemukan']);
    exit;
}
$sql = "SELECT 
    (
        COALESCE((SELECT SUM(point_1) FROM point_kasir WHERE kd_cust = c.kd_cust), 0) + 
        COALESCE((SELECT SUM(jum_point) FROM point_manual WHERE kd_cust = c.kd_cust), 0) - 
        COALESCE((SELECT SUM(jum_point) FROM point_trans WHERE kd_cust = c.kd_cust), 0)
    ) AS total_poin_pk_pm
FROM customers c
WHERE c.kd_cust = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal Query Database: ' . $conn->error]);
    exit;
}
$stmt->bind_param('s', $kd_cust);
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
$stmt->close();
$conn->close();
?>