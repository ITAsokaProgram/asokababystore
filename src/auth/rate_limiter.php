<?php
// Middleware Rate Limit dengan APCu
$ip = $_SERVER['REMOTE_ADDR'];
$key = 'rate_limit_' . $ip;
$limitTime = 5; // detik antar request

if (function_exists('apcu_fetch')) {
    $last = apcu_fetch($key);
    if ($last && (time() - $last) < $limitTime) {
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => 'Terlalu sering mengakses, coba beberapa detik lagi.'
        ]);
        exit;
    }
    if (function_exists('apcu_store')) {
        apcu_store($key, time(), $limitTime + 1);
    }
}