<?php

require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../redis.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Content-Type: application/json");

$redisKey = "transaction_dashboard";

if ($redisKey === false) {
  http_response_code(204);
  echo json_encode(['status' => false, 'message' => 'Data tidak ditemukan']);
  exit;
}

$data = json_decode($redis->get($redisKey), true);
if (!isset($data['data'])) {
  http_response_code(204);
  echo json_encode(['status' => false, 'message' => 'Data tidak ditemukan']);
  exit;
}
http_response_code(200);
echo json_encode(['status' => true, 'message' => 'Data berhasil diambil', 'data' => $data['data']]);
$conn->close();
