<?php

require_once __DIR__ . ("./../../../../aa_kon_sett.php");
require_once __DIR__ . ("./../../../auth/middleware_login.php");
header("Content-Type:application/json");
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

$headers = getallheaders();
$token = $headers['Authorization'];
$token = str_replace('Bearer ', '', $token);
$user = verify_token($token);
if (!$user) {
  http_response_code(401);
  echo json_encode(['status' => false, 'message' => 'Unauthorized']);
  exit;
}
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-1 day'));
$end_date = $_GET['end_date'] ?? date('Y-m-d', strtotime('-1 day'));
if (!strtotime($start_date) || !strtotime($end_date)) {
  http_response_code(400);
  echo json_encode(['status' => false, 'message' => 'Tanggal tidak valid']);
  exit;
}
$sql = "SELECT 
 t.kd_cust,
 c.nama_cust AS nama_customer,
 t.plu,
 t.descp AS barang,
 SUM(t.qty) AS total_qty,
 SUM(t.qty * t.harga) AS total_hrg
FROM trans_b t
LEFT JOIN customers c ON t.kd_cust = c.kd_cust
WHERE 
  t.kd_cust IS NOT NULL
  AND t.kd_cust NOT IN ('', '898989', '#898989', '#999999999')
  AND t.tgl_trans BETWEEN '$start_date' AND '$end_date'
GROUP BY t.kd_cust, t.plu
ORDER BY total_qty DESC LIMIT 100";

$stmt = $conn->prepare($sql);


if (!$stmt) {
  http_response_code(500);
  echo json_encode(['status' => false, 'message' => 'Internal Server Error']);
  exit;
}

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
  http_response_code(200);
  $data = $result->fetch_all(MYSQLI_ASSOC);
  echo json_encode(['status' => true, 'data' => $data]);
} else {
  echo json_encode(['status' => false, 'message' => 'Data Kosong']);
}
$stmt->close();
$conn->close();

