<?php

require_once __DIR__ . "/../../../redis.php";
require_once __DIR__ . "/../../../config.php";

$redisKey = 'top_sales_product_by_member';
$now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
$next7am = new DateTime('tomorrow 08:59', new DateTimeZone('Asia/Jakarta'));
$ttl = $next7am->getTimestamp() - $now->getTimestamp();


$sql = "SELECT
  t.plu,
  t.descp AS barang,
  
  SUM(CASE
    WHEN t.tgl_trans = CURDATE() - INTERVAL 1 DAY THEN t.qty
    ELSE 0
  END) AS qty_periode_sekarang,
  
  SUM(CASE
    WHEN t.tgl_trans = CURDATE() - INTERVAL 2 DAY THEN t.qty
    ELSE 0
  END) AS qty_periode_sebelumnya,
  
  ROUND(
    IFNULL((
      SUM(CASE WHEN t.tgl_trans = CURDATE() - INTERVAL 1 DAY THEN t.qty ELSE 0 END)
      - SUM(CASE WHEN t.tgl_trans = CURDATE() - INTERVAL 2 DAY THEN t.qty ELSE 0 END)
    ) / NULLIF(SUM(CASE WHEN t.tgl_trans = CURDATE() - INTERVAL 2 DAY THEN t.qty ELSE 0 END), 0) * 100, 0)
  , 2) AS growth_percent

FROM trans_b t
WHERE
  t.kd_cust IS NOT NULL
  AND t.kd_cust NOT IN ('', '898989', '89898989', '999999999') AND t.descp NOT LIKE '%kertas%' AND t.descp NOT LIKE '%tas%' AND t.descp NOT LIKE '%askp%'
GROUP BY t.plu, t.descp
HAVING qty_periode_sekarang > 0 OR qty_periode_sebelumnya > 0
ORDER BY qty_periode_sekarang DESC
LIMIT 100";

$stmt = $conn->prepare($sql);
if (!$stmt) {
  echo date('Y-m-d H:i:s') . " - Statement error: " . "\n";
  exit;
}
$stmt->execute();
$result = $stmt->get_result();
$top_sales_product_by_member = $result->fetch_all(MYSQLI_ASSOC);

if (count($top_sales_product_by_member) === 0) {
  echo date('Y-m-d H:i:s') . " - No Data: Not Found \n";
  exit;
}
$response = [
  "success" => true,
  "message" => "Data berhasil diambil",
  "total" => count($top_sales_product_by_member),
  "data" => $top_sales_product_by_member
];
$redis->setex($redisKey, $ttl, json_encode($response));
echo date('Y-m-d H:i:s') . " - Redis updated: $redisKey\n";
$stmt->close();
