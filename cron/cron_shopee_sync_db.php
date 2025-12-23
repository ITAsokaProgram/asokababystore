<?php
require_once __DIR__ . '/../aa_kon_sett.php';
require_once __DIR__ . '/../src/utils/Logger.php';
require_once __DIR__ . '/../src/fitur/shopee/lib/ShopeeApiService.php';
require_once __DIR__ . '/../redis.php';
ini_set('display_errors', 1);
set_time_limit(0);
ini_set('memory_limit', '1024M');
$logger = new AppLogger('shopee_cron_sync.log');
$logger->info("=== MEMULAI CRON SYNC SHOPEE KE DB ===");
try {
    if (!isset($conn) || !$conn) {
        throw new Exception("Koneksi Database Gagal");
    }
    $shopeeService = new ShopeeApiService($logger);
    if (!$shopeeService->isConnected()) {
        throw new Exception("Token Shopee tidak valid atau kadaluwarsa. Mohon login ulang via panel admin.");
    }
    $total = $shopeeService->syncAllProductsToDatabase($conn);
    $logger->info("=== CRON SELESAI. Total Data: $total ===");
    echo "Sukses sinkronisasi $total data produk/variasi ke Database.\n";
} catch (Throwable $e) {
    $logger->critical("CRON GAGAL: " . $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}