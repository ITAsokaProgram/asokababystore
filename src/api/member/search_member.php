<?php
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../redis.php";
header("Content-Type: application/json");

$keyword = strtolower($_GET['keyword'] ?? '');
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;

if (strlen($keyword) < 3) {
    echo json_encode(["success" => false, "message" => "Minimal 3 karakter"]);
    exit;
}

$raw = $redis->get('member_poin');
if (!$raw) {
    echo json_encode(["success" => false, "message" => "Data tidak ditemukan"]);
    exit;
}

$data = json_decode($raw, true)['data'] ?? [];

$filtered = array_filter($data, function ($item) use ($keyword) {
    return (
        isset($item['nama_cust']) && stripos($item['nama_cust'], $keyword) !== false
    ) || (
        isset($item['kd_cust']) && stripos($item['kd_cust'], $keyword) !== false
    );
});

$total = count($filtered);
$start = ($page - 1) * $limit;
$paged = array_slice(array_values($filtered), $start, $limit);

echo json_encode([
    "success" => true,
    "data" => $paged,
    "total" => $total,
    "page" => $page,
    "limit" => $limit
]);
