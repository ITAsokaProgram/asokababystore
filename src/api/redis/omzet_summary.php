<?php

require_once __DIR__ . "/../../../redis.php";
require_once __DIR__ . "/../../../config.php";
$redisKey = "omzet_summary";
// Hitung TTL ke jam 7 pagi besok
$now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
$next7am = new DateTime('tomorrow 08:59', new DateTimeZone('Asia/Jakarta'));
$ttl = $next7am->getTimestamp() - $now->getTimestamp();


$stmt = $conn->prepare("
    SELECT
  -- Omzet periode sekarang
  SUM(CASE 
    WHEN t.tgl_trans BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() - INTERVAL 1 DAY 
    THEN t.hrg_promo * t.qty 
    ELSE 0 
  END) AS omzet_periode_sekarang,
  -- Omzet periode sebelumnya
  SUM(CASE 
    WHEN t.tgl_trans BETWEEN CURDATE() - INTERVAL 60 DAY AND CURDATE() - INTERVAL 31 DAY 
    THEN t.hrg_promo * t.qty 
    ELSE 0 
  END) AS omzet_periode_sebelumnya,
  -- Growth persen
  ROUND(
    IFNULL((
      SUM(CASE WHEN t.tgl_trans BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() - INTERVAL 1 DAY THEN t.hrg_promo * t.qty ELSE 0 END)
      -
      SUM(CASE WHEN t.tgl_trans BETWEEN CURDATE() - INTERVAL 60 DAY AND CURDATE() - INTERVAL 31 DAY THEN t.hrg_promo * t.qty ELSE 0 END)
    ) / NULLIF(
      SUM(CASE WHEN t.tgl_trans BETWEEN CURDATE() - INTERVAL 60 DAY AND CURDATE() - INTERVAL 31 DAY THEN t.hrg_promo * t.qty ELSE 0 END)
    , 0) * 100, 0)
  , 2) AS growth_omzet_percent
FROM trans_b t
WHERE
  t.kd_cust IS NOT NULL
  AND t.kd_cust NOT IN ('', '898989', '#898989', '#999999999')
");

if (!$stmt) {
    echo date('Y-m-d H:i:s') . " - Statement error: " . "\n";
    exit;
}

$stmt->execute();
$result = $stmt->get_result();
$omzet_summary = $result->fetch_all(MYSQLI_ASSOC);

if (count($omzet_summary) === 0) {
    echo date('Y-m-d H:i:s') . " - No Data: Not Found \n";
    exit;
}

$response = [
    "success" => true,
    "message" => "Data berhasil diambil",
    "total" => count($omzet_summary),
    "data" => $omzet_summary
];

$redis->setex($redisKey, $ttl, json_encode($response));
echo date('Y-m-d H:i:s') . " - Redis updated: $redisKey\n";
$stmt->close();