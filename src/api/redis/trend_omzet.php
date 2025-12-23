<?php

require_once __DIR__ . "/../../../redis.php";
require_once __DIR__ . "/../../../config.php";
$redisKey = "trend_omzet";
// Hitung TTL ke jam 7 pagi besok
$now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
$next7am = new DateTime('tomorrow 08:59', new DateTimeZone('Asia/Jakarta'));
$ttl = $next7am->getTimestamp() - $now->getTimestamp();


$stmt = $conn->prepare("
    SELECT 
  DATE_FORMAT(tgl_trans, '%Y-%m') AS bulan,
  SUM(hrg_promo * qty) AS total_omzet
FROM trans_b
WHERE 
  YEAR(tgl_trans) = YEAR(CURDATE())
  AND MONTH(tgl_trans) <= MONTH(CURDATE())
  AND kd_cust IS NOT NULL
  AND kd_cust NOT IN ('', '898989', '89898989', '999999999')
GROUP BY DATE_FORMAT(tgl_trans, '%Y-%m')
ORDER BY bulan
");

if (!$stmt) {
  echo date('Y-m-d H:i:s') . " - Statement error: " . "\n";
  exit;
}

$stmt->execute();
$result = $stmt->get_result();
$trend_omzet = $result->fetch_all(MYSQLI_ASSOC);

if (count($trend_omzet) === 0) {
  echo date('Y-m-d H:i:s') . " - No Data: Not Found \n";
  exit;
}

$response = [
  "success" => true,
  "message" => "Data berhasil diambil",
  "total" => count($trend_omzet),
  "data" => $trend_omzet
];

$redis->setex($redisKey, $ttl, json_encode($response));
echo date('Y-m-d H:i:s') . " - Redis updated: $redisKey\n";
$stmt->close();