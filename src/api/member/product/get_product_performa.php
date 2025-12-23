<?php

require_once __DIR__ . "/../../../../config.php";
require_once __DIR__ . "/../../../../redis.php";
header("Content-Type:application/json");
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
$redisKey = "product_performa";

$cached = $redis->get($redisKey);

if ($cached === false) {
    http_response_code(204);
    echo json_encode(["success" => false, "message" => "Data belum tersedia"]);
    exit;
}

$decoded = json_decode($cached, true);
if (!isset($decoded['data'])) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Format data tidak valid"]);
    exit;
}


echo json_encode($decoded);