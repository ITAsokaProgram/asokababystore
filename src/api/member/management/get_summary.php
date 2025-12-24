<?php
ini_set('memory_limit', '512M');
ini_set('display_errors', 0);
error_reporting(E_ALL);
require_once __DIR__ . "/../../../../config.php";
require_once __DIR__ . "/../../../../redis.php";
require_once __DIR__ . "/../../../auth/middleware_login.php";
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Nama key tetap
$redisKey = $redis->get("member_poin");
if (!$redisKey) {
    echo json_encode(['status' => false, 'message' => 'Key tidak ditemukan']);
    exit;
}

$data = json_decode($redisKey, true);
$countTotalMember = count($data['data'] ?? []);
$countActiveMember = count(array_filter($data['data'], function ($item) {
    return isset($item['status_aktif']) && strcasecmp($item['status_aktif'], 'Aktif') === 0;
}));
$countNonActiveMember = count(array_filter($data['data'], function ($item) {
    return isset($item['status_aktif']) && strcasecmp($item['status_aktif'], 'Non-Aktif') === 0;
}));

echo json_encode([
    'status' => true,
    'data' => [
        'total_member' => $countTotalMember,
        'active_member' => $countActiveMember,
        'non_active_member' => $countNonActiveMember
    ]
]);
