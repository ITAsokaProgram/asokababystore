<?php

require_once __DIR__ . "/../../../redis.php";
require_once __DIR__ . "/../../../config.php";
$redisKey = "trend_pembelian_member";
// Hitung TTL ke jam 7 pagi besok
$now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
$next7am = new DateTime('tomorrow 08:59', new DateTimeZone('Asia/Jakarta'));
$ttl = $next7am->getTimestamp() - $now->getTimestamp();


$stmt = $conn->prepare("
    SELECT
MONTH(tgl_trans) AS bulan,
SUM(qty) AS total_qty
FROM trans_b
WHERE
kd_cust IS NOT NULL
AND kd_cust NOT IN ('', '898989', '89898989', '999999999')
AND YEAR(tgl_trans) = YEAR(CURDATE())
GROUP BY MONTH(tgl_trans)
ORDER BY bulan
");

if (!$stmt) {
    echo date('Y-m-d H:i:s') . " - Statement error: " . "\n";
    exit;
}

$stmt->execute();
$result = $stmt->get_result();
$trend_pembelian_member = $result->fetch_all(MYSQLI_ASSOC);

if (count($trend_pembelian_member) === 0) {
    echo date('Y-m-d H:i:s') . " - No Data: Not Found \n";
    exit;
}

$response = [
    "success" => true,
    "message" => "Data berhasil diambil",
    "total" => count($trend_pembelian_member),
    "data" => $trend_pembelian_member
];

$redis->setex($redisKey, $ttl, json_encode($response));
echo date('Y-m-d H:i:s') . " - Redis updated: $redisKey\n";
$stmt->close();