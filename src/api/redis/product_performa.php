<?php

require_once __DIR__ . "/../../../redis.php";
require_once __DIR__ . "/../../../config.php";
$redisKey = "product_performa";
// Hitung TTL ke jam 7 pagi besok
$now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
$next7am = new DateTime('tomorrow 08:59', new DateTimeZone('Asia/Jakarta'));
$ttl = $next7am->getTimestamp() - $now->getTimestamp();
$stmt = $conn->prepare("
    SELECT
t.plu,
t.descp AS barang,
SUM(CASE
WHEN t.tgl_trans BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() - INTERVAL 1 DAY
THEN t.qty ELSE 0 END) AS qty_periode_sekarang,
SUM(CASE
WHEN t.tgl_trans BETWEEN CURDATE() - INTERVAL 60 DAY AND CURDATE() - INTERVAL 31 DAY
THEN t.qty ELSE 0 END) AS qty_periode_sebelumnya
FROM trans_b t
WHERE
t.kd_cust IS NOT NULL
AND t.kd_cust NOT IN ('', '898989', '#898989', '#999999999')
GROUP BY t.plu, t.descp
HAVING qty_periode_sekarang > 0 OR qty_periode_sebelumnya > 0
ORDER BY qty_periode_sekarang DESC
LIMIT 10
");
if (!$stmt) {
    echo date('Y-m-d H:i:s') . " - Statement error: " . "\n";
    exit;
}

$stmt->execute();
$result = $stmt->get_result();
$product_performa = $result->fetch_all(MYSQLI_ASSOC);

if (count($product_performa) === 0) {
    echo date('Y-m-d H:i:s') . " - No Data: Not Found \n";
    exit;
}

$response = [
    "success" => true,
    "message" => "Data berhasil diambil",
    "total" => count($product_performa),
    "data" => $product_performa
];

$redis->setex($redisKey, $ttl, json_encode($response));
echo date('Y-m-d H:i:s') . " - Redis updated: $redisKey\n";
$stmt->close();
$conn->close();