<?php
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../redis.php";

// Nama key tetap
$redisKey = $redis->get("member_poin");
if (!$redisKey) {
    echo date('Y-m-d H:i:s') . " - GAGAL: Key tidak ditemukan \n";
    exit;
}

$data = json_decode($redisKey, true);
$now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
$next7am = new DateTime('tomorrow 07:00', new DateTimeZone('Asia/Jakarta'));
$ttl = $next7am->getTimestamp() - $now->getTimestamp();
// Validasi struktur
if (!isset($data['data']) || !is_array($data['data'])) {
    echo date('Y-m-d H:i:s') . " - GAGAL: Struktur data tidak valid atau kosong\n";
    exit(1);
}

// Filter member dengan status Non-Aktif
$nonAktif = array_filter($data['data'], function ($item) {
    return isset($item['status_aktif']) && strcasecmp($item['status_aktif'], 'Non-Aktif') === 0;
});

// Simpan hasil filter ke Redis
$redis->setex('member_non_active', $ttl, json_encode(array_values($nonAktif)));

echo date('Y-m-d H:i:s') . " - member_active (" . count($nonAktif) . " member non aktif)\n";
