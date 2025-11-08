<?php
session_start();
ini_set('display_errors', 0);
set_time_limit(1800);
ini_set('memory_limit', '512M');
require_once __DIR__ . '/../../utils/Logger.php';
$logger = new AppLogger('shopee_FORCE_sync_redis.log');
try {
    include '../../../aa_kon_sett.php';
    require_once __DIR__ . '/../../fitur/shopee/lib/ShopeeApiService.php';
    require_once __DIR__ . '/../../../redis.php';
} catch (Throwable $t) {
    $logger->critical("Gagal memuat file dependensi: " . $t->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: Gagal memuat file. Cek log.']);
    exit();
}
header('Content-Type: application/json');

$redisKey = 'shopee_all_products';
$lockKey = 'shopee_sync_in_progress';
$expiry_seconds = 86400;
$startTime = microtime(true);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $logger->warning("Method Not Allowed: " . $_SERVER['REQUEST_METHOD']);
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        exit();
    }

    if (!isset($redis) || !$redis->ping()) {
        throw new Exception("Koneksi Redis Gagal.");
    }

    $shopeeService = new ShopeeApiService($logger);



    $total_products = $shopeeService->fetchAndCacheAllProducts(
        $redis,
        $redisKey,
        $lockKey,
        $expiry_seconds,
        true
    );

    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);

    $logger->info("[SUCCESS] Panggilan API FORCE_sync selesai. Total: {$total_products}. Durasi: {$duration} detik.");

    echo json_encode([
        'success' => true,
        'message' => 'Sinkronisasi paksa produk ke cache Redis selesai.',
        'total_items_saved_to_redis' => $total_products,
        'duration_seconds' => $duration
    ]);

} catch (Throwable $t) {
    $errorMessage = $t->getMessage();
    $statusCode = 500;

    if (strpos($errorMessage, 'Not authenticated') !== false || strpos($errorMessage, 'Token Shopee tidak valid') !== false) {
        $statusCode = 401;
        $errorMessage = "Token Shopee tidak valid. Harap autentikasi ulang.";
    } elseif (strpos($errorMessage, 'Koneksi Redis Gagal') !== false) {
        $statusCode = 500;
        $errorMessage = "Internal Server Error: Koneksi Redis Gagal.";
    }

    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);

    $logger->critical("🔥 FATAL ERROR API (Throwable) - Durasi berjalan: {$duration} detik. Error: " . $t->getMessage(), [
        'file' => $t->getFile(),
        'line' => $t->getLine()
    ]);

    if (!headers_sent()) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => false,
            'message' => $errorMessage
        ]);
    }
}
?>