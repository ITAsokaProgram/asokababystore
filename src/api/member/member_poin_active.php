<?php

require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../redis.php";
header("Content-Type: application/json");

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
$offset = ($page - 1) * $limit;

$raw = $redis->get('member_active');
if (!$raw) {
    echo json_encode([
        "success" => false,
        "message" => "Key Redis tidak ditemukan"
    ]);
    exit;
}

$data = json_decode($raw, true);
$total = count($data);
$paginated = array_slice($data, $offset, $limit);

echo json_encode([
    "success" => true,
    "message" => "Data berhasil diambil",
    "total" => $total,
    "page" => $page,
    "limit" => $limit,
    "data" => $paginated
]);