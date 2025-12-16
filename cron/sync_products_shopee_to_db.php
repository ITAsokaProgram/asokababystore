<?php
ini_set('display_errors', 1);
set_time_limit(0);
ini_set('memory_limit', '1024M');
try {
    require_once __DIR__ . '/../aa_kon_sett.php';
    require_once __DIR__ . '/../src/utils/Logger.php';
    require_once __DIR__ . '/../src/fitur/shopee/lib/ShopeeApiService.php';
} catch (Throwable $t) {
    echo "CRON FATAL: Gagal memuat dependensi: " . $t->getMessage() . "\n";
    exit(1);
}
$logger = new AppLogger('cron_sync_products_shopee_to_db.log');
try {
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Koneksi Database Gagal.");
    }
} catch (Throwable $t) {
    $logger->critical("Koneksi DB Gagal: " . $t->getMessage());
    exit(1);
}
$startTime = microtime(true);
try {
    $logger->info("[CRON-START] Memulai sinkronisasi Shopee ke Database...");
    $shopeeService = new ShopeeApiService($logger);
    if (!$shopeeService->isConnected()) {
        $logger->warning("[CRON-FAIL] Token Shopee tidak valid atau belum login.");
        echo "Token Shopee tidak valid.\n";
        exit(1);
    }
    $total_products = $shopeeService->syncAllProductsToDatabase($conn);
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    $msg = "✅ [CRON-SUCCESS] Berhasil menyimpan {$total_products} item ke Database. Durasi: {$duration} detik.";
    $logger->info($msg);
    echo $msg . "\n";
} catch (Throwable $t) {
    $errorMessage = $t->getMessage();
    $logMessage = "🔥 FATAL ERROR CRON: " . $errorMessage;
    $logger->critical($logMessage, [
        'file' => $t->getFile(),
        'line' => $t->getLine()
    ]);
    echo $logMessage . "\n";
}
?>