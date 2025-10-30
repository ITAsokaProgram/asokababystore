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
$expiry_seconds = 3600; 
try {
    $startTime = microtime(true); 
    $lockAcquired = $redis->set($lockKey, 1, ['nx', 'ex' => 1800]);
    if (!$lockAcquired) {
        $logger->warning("[CRON-FAIL] Gagal mendapatkan lock '{$lockKey}'. Sync lain (mungkin via web) sedang berjalan.");
        exit();
    }
    $logger->info("[CRON-START] Lock '{$lockKey}' didapat. Memulai sinkronisasi Shopee ke Redis...");
    $shopeeService = new ShopeeApiService($logger);
    if (!$shopeeService->isConnected()) {
        $logger->warning("[CRON-FAIL] Gagal: Belum terautentikasi dengan Shopee (Kredensial di DB kosong?).");
        $redis->del($lockKey);
        exit();
    }
    $all_detailed_products = [];
    $offset = 0;
    $page_size = 50;
    $total_items_found = 0;
    $has_next_page = true;
    $page_counter = 1; 
    while ($has_next_page) {
        $logger->info("[CRON-PROGRESS] Mengambil batch #{$page_counter}. Offset: {$offset}, Page Size: {$page_size}.");
        $api_params = [
            'offset'    => $offset,
            'page_size' => $page_size,
            'item_status' => 'NORMAL'
        ];
        $product_list_response = $shopeeService->getProductList($api_params);
        if (isset($product_list_response['error']) && $product_list_response['error']) {
            $logger->error("❌ [CRON-FAIL] Gagal mengambil daftar produk batch offset: {$offset}", $product_list_response);
            throw new Exception("Gagal mengambil daftar produk dari Shopee: " . $product_list_response['message']);
        }
        if (!isset($product_list_response['response']['item']) || empty($product_list_response['response']['item'])) {
            $logger->info("[CRON-PROGRESS] Tidak ada item lagi di batch (Offset: {$offset}). Berhenti mengambil.");
            break;
        }
        $detailed_items_batch = $shopeeService->getDetailedProductInfo($product_list_response);
        if (empty($detailed_items_batch)) {
            $logger->info("[CRON-PROGRESS] Batch (Offset: {$offset}) tidak mengembalikan item detail. Menghentikan loop.");
            break;
        }
        $batch_count = count($detailed_items_batch);
        $all_detailed_products = array_merge($all_detailed_products, $detailed_items_batch);
        $total_items_found += $batch_count;
        $logger->info("[CRON-PROGRESS] Batch #{$page_counter} (Offset: {$offset}) sukses. Ditemukan {$batch_count} item. Total sementara: {$total_items_found}.");
        $has_next_page = $product_list_response['response']['has_next_page'] ?? false;
        $offset = $product_list_response['response']['next_offset'] ?? 0;
        $page_counter++;
        if (!$has_next_page) {
            $logger->info("[CRON-PROGRESS] Shopee API melaporkan tidak ada halaman berikutnya (has_next_page: false).");
            break;
        }
        usleep(250000); 
    }
    if (empty($all_detailed_products)) {
        $logger->warning("[CRON-WARN] Tidak ada produk yang ditemukan di akun Shopee setelah iterasi selesai.");
    }
    $total_products = count($all_detailed_products);
    $logger->info("[CRON-SAVE] Selesai mengambil semua produk dari Shopee. Total item: {$total_products}. Mulai menyimpan ke Redis key '{$redisKey}'...");
    $json_data = json_encode($all_detailed_products);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $logger->error("❌ [CRON-FAIL] Gagal encode JSON: " . json_last_error_msg());
        throw new Exception("Gagal memproses data produk untuk cache.");
    }
    $success = $redis->setex($redisKey, $expiry_seconds, $json_data);
    if (!$success) {
        $logger->error("❌ [CRON-FAIL] Gagal menyimpan ke Redis. Perintah SETEX mengembalikan false. Key: {$redisKey}");
        throw new Exception("Perintah REDIS setex gagal mengembalikan true.");
    }
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    $logger->info("✅ [CRON-SUCCESS] Berhasil menyimpan {$total_products} item ke Redis key '{$redisKey}'. Expire dalam {$expiry_seconds} detik. Durasi total: {$duration} detik.");
    $redis->del($lockKey); 
} catch (Throwable $t) {
    $redis->del($lockKey); 
    $errorMessage = $t->getMessage();
    if (strpos($errorMessage, 'Invalid access_token') !== false || strpos($errorMessage, 'invalid_acceess_token') !== false) {
        if (isset($shopeeService)) {
            $shopeeService->disconnect();
            $logger->warning("[CRON-DISCONNECT] Token invalid terdeteksi. Otomatis disconnect.");
        }
    }
    $logMessage = "🔥 FATAL ERROR CRON (Throwable) - Lock dilepaskan: " . $errorMessage;
    if (isset($startTime)) {
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        $logMessage .= " Durasi berjalan: {$duration} detik.";
    }
    $logger->critical($logMessage, [
        'file' => $t->getFile(),
        'line' => $t->getLine()
    ]);
}
?>