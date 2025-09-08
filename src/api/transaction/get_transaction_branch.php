<?php
require_once __DIR__ . ("./../../../aa_kon_sett.php");
require_once __DIR__ . ("./../../auth/middleware_login.php");

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

$sql = "SELECT 
  ks.Nm_Alias AS cabang,
  l.kd_store,
  l.total_transaksi,

  COUNT(DISTINCT CASE 
    WHEN t.kd_cust IS NOT NULL 
      AND t.kd_cust NOT IN ('', '898989', '#898989', '#999999999') 
    THEN t.no_bon END) AS member,

  COUNT(DISTINCT CASE 
    WHEN t.kd_cust IS NULL 
      OR t.kd_cust IN ('', '898989', '#898989', '#999999999') 
    THEN t.no_bon END) AS non_member

FROM (
  SELECT kd_store, COUNT(DISTINCT no_bon) AS total_transaksi
  FROM trans_b
  WHERE tgl_trans = CURDATE() - INTERVAL 1 DAY
  GROUP BY kd_store
) AS l

LEFT JOIN trans_b t 
  ON t.kd_store = l.kd_store
  AND t.tgl_trans = CURDATE() - INTERVAL 1 DAY

LEFT JOIN kode_store ks 
  ON ks.kd_store = l.kd_store

GROUP BY l.kd_store, ks.Nm_Alias, l.total_transaksi
ORDER BY l.total_transaksi DESC";

$sqlAll = "SELECT 
  COUNT(DISTINCT no_bon) AS total_transaksi,
  COUNT(DISTINCT CASE 
    WHEN kd_cust IS NOT NULL 
      AND kd_cust NOT IN ('', '898989', '#898989', '#999999999') 
    THEN no_bon END) AS member,
  COUNT(DISTINCT CASE 
    WHEN kd_cust IS NULL 
      OR kd_cust IN ('', '898989', '#898989', '#999999999') 
    THEN no_bon END) AS non_member
FROM trans_b WHERE tgl_trans = CURDATE() - INTERVAL 1 DAY";

$stmt = $conn->prepare($sql);
if (!$stmt) {
  http_response_code(500);
  echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $conn->error]);
  exit;
}

$stmt->execute();
$result = $stmt->get_result();
$stmtAll = $conn->prepare($sqlAll);
$stmtAll->execute();
$resultAll = $stmtAll->get_result();
if ($result->num_rows > 0 ) {
  http_response_code(200);
  $row = $result->fetch_all(MYSQLI_ASSOC);
  $rowAll = $resultAll->fetch_all(MYSQLI_ASSOC);
  echo json_encode(['status' => 'success', 'message' => 'Data berhasil dimuat', 'data' => $row, 'data_all'=>$rowAll]);
} else {
  http_response_code(200);
  echo json_encode(['status' => 'success', 'message' => 'Data kosong', 'data' => []]);
}
$stmtAll->close();
$stmt->close();
$conn->close();
