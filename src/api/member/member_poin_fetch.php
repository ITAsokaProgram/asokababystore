<?php
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../redis.php";
header("Content-Type: application/json");

// Ambil parameter
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($limit <= 0 || $page <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Parameter tidak valid"]);
    exit;
}

$redisKey = "member_poin";

// Ambil cache dari Redis
$cached = $redis->get($redisKey);
if ($cached === false) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Data belum tersedia di Redis"]);
    exit;
}

$decoded = json_decode($cached, true);
if (!isset($decoded['data'])) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Format data Redis tidak valid"]);
    exit;
}

// Slice data sesuai page dan limit
$data = $decoded['data'];
$total = count($data);
$totalPages = ceil($total / $limit);
$offset = ($page - 1) * $limit;
$paginatedData = array_slice($data, $offset, $limit);

// Kirim response
echo json_encode([
    "success" => true,
    "message" => "Data berhasil diambil dari Redis",
    "page" => $page,
    "limit" => $limit,
    "total" => $total,
    "total_pages" => $totalPages,
    "data" => $paginatedData
]);
