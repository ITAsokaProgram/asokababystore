<?php

require_once __DIR__ . "/../../../redis.php";
require_once __DIR__ . "/../../../config.php";

$redisKey = "top_member_by_sales";

$cached = $redis->get($redisKey);


$now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
$next830am = new DateTime('tomorrow 08:59', new DateTimeZone('Asia/Jakarta'));
$ttl = $next830am->getTimestamp() - $now->getTimestamp();


$sql = "SELECT 
  t.kd_cust,
  t.no_bon AS no_trans,
  c.nama_cust,
  ks.nm_alias AS cabang,
  ks.kd_store AS kd_store,
  SUM(t.qty) AS total_qty,
  SUM(t.qty * t.harga) AS total_penjualan
FROM trans_b t
LEFT JOIN customers c ON t.kd_cust = c.kd_cust
LEFT JOIN kode_store ks ON ks.kd_store = t.kd_store
WHERE 
  t.kd_cust IS NOT NULL
  AND t.kd_cust NOT IN ('', '898989', '#898989', '#999999999')
  AND t.tgl_trans = CURDATE() - INTERVAL 1 DAY
GROUP BY t.kd_cust
ORDER BY total_penjualan DESC
LIMIT 50";

$sqlNon = "SELECT 
  t.no_bon AS no_trans,
  ks.nm_alias AS cabang,
  SUM(t.qty) AS total_qty,
  SUM(t.qty * t.harga) AS total_penjualan
FROM trans_b t
LEFT JOIN customers c ON t.kd_cust = c.kd_cust
LEFT JOIN kode_store ks ON ks.kd_store = t.kd_store
WHERE 
  (t.kd_cust IS NULL OR t.kd_cust IN ('', '898989', '999999999'))
  AND t.tgl_trans = CURDATE() - INTERVAL 1 DAY
GROUP BY t.no_bon
ORDER BY total_penjualan DESC
LIMIT 50";

$stmt = $conn->prepare($sql);
if (!$stmt) {
  http_response_code(500);
  echo json_encode([
    "success" => false,
    "message" => "Statement error: " . $conn->error
  ]);
  exit;
}
$stmt->execute();
$result = $stmt->get_result();
$top_member_by_sales = $result->fetch_all(MYSQLI_ASSOC);

$stmtNon = $conn->prepare($sqlNon);
if (!$stmtNon) {
  http_response_code(500);
  echo json_encode([
    "success" => false,
    "message" => "Statement error: " . $conn->error
  ]);
  exit;
}
$stmtNon->execute();
$resultNon = $stmtNon->get_result();
$top_member_by_sales_non = $resultNon->fetch_all(MYSQLI_ASSOC);

if (count($top_member_by_sales) === 0 && count($top_member_by_sales_non) === 0) {
  http_response_code(404);
  echo json_encode([
    "success" => false,
    "message" => "Data belum tersedia"
  ]);
  $stmt->close();
  $stmtNon->close();
  $conn->close();
  exit;
}

$response = [
  "success" => true,
  "message" => "Data berhasil diambil",
  "total" => count($top_member_by_sales),
  "data" => $top_member_by_sales,
  "data_non" => $top_member_by_sales_non
];

$redis->setex($redisKey, $ttl, json_encode($response));
echo date('Y-m-d H:i:s') . " - Redis updated: $redisKey\n";

$stmt->close();
$stmtNon->close();
$conn->close();