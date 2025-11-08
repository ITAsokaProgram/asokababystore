<?php
ini_set('display_errors', 0);
set_time_limit(1800);
ini_set('memory_limit', '512M');
try {
    require_once __DIR__ . '/../aa_kon_sett.php';
    require_once __DIR__ . '/../src/utils/Logger.php';
    require_once __DIR__ . '/../src/fitur/shopee/lib/ShopeeApiService.php';
    require_once __DIR__ . '/../redis.php';
} catch (Throwable $t) {
    error_log("CRON FATAL: Gagal memuat dependensi: " . $t->getMessage());
    exit();
}
$logger = new AppLogger('cron_shopee_sync_redis.log');
try {
    if (!isset($redis) || !$redis->ping()) {
        throw new Exception("Koneksi Redis Gagal.");
    }
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Koneksi Database Gagal.");
    }
} catch (Throwable $t) {
    $logger->critical("Koneksi (Redis/DB) Gagal: " . $t->getMessage());
    exit();
}
$redisKey = 'shopee_all_products';
$lockKey = 'shopee_sync_in_progress';
$expiry_seconds = 86400;
$startTime = microtime(true);
try {
    $logger->info("[CRON-START] Memulai sinkronisasi Shopee ke Redis...");
    $shopeeService = new ShopeeApiService($logger);
    $total_products = $shopeeService->fetchAndCacheAllProducts(
        $redis,
        $redisKey,
        $lockKey,
        $expiry_seconds,
        false
    );
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    $logger->info("✅ [CRON-SUCCESS] Berhasil menyimpan {$total_products} item ke Redis key '{$redisKey}'. Durasi total: {$duration} detik.");
} catch (Throwable $t) {
    $errorMessage = $t->getMessage();
    if (strpos($errorMessage, 'Sinkronisasi sudah sedang berjalan') !== false) {
        $logger->warning("[CRON-FAIL] Gagal mendapatkan lock '{$lockKey}'. Sync lain sedang berjalan. CRON dihentikan.");
        exit();
    }
    if (strpos($errorMessage, 'Not authenticated') !== false) {
        $logger->warning("[CRON-FAIL] Gagal: Belum terautentikasi dengan Shopee (Kredensial di DB kosong?).");
        exit();
    }
    $logMessage = "🔥 FATAL ERROR CRON (Throwable): " . $errorMessage;
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    $logMessage .= " Durasi berjalan: {$duration} detik.";
    $logger->critical($logMessage, [
        'file' => $t->getFile(),
        'line' => $t->getLine()
    ]);
}
?>