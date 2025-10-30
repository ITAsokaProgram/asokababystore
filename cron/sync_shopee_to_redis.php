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

    $lockAcquired = $redis->set($lockKey, 1, ['nx', 'ex' => 1800]); 
    if (!$lockAcquired) {
        $logger->warning("CRON Gagal: Gagal mendapatkan lock '{$lockKey}'. Sync lain (mungkin via web) sedang berjalan.");
        exit();
    }

    $shopeeService = new ShopeeApiService($logger); 
    if (!$shopeeService->isConnected()) {
        $logger->warning("CRON Gagal: Belum terautentikasi dengan Shopee (Kredensial di DB kosong?).");
        $redis->del($lockKey); 
        exit();
    }
    $all_detailed_products = []; 
    $offset = 0;
    $page_size = 50;
    $total_items_found = 0;
    $has_next_page = true;
    while ($has_next_page) {
        $api_params = [
            'offset'    => $offset,
            'page_size' => $page_size,
            'item_status' => 'NORMAL'
        ];
        $product_list_response = $shopeeService->getProductList($api_params);
        if (isset($product_list_response['error']) && $product_list_response['error']) {
            $logger->error("❌ Gagal mengambil daftar produk batch offset: {$offset}", $product_list_response);
            throw new Exception("Gagal mengambil daftar produk dari Shopee: " . $product_list_response['message']);
        }
        if (!isset($product_list_response['response']['item']) || empty($product_list_response['response']['item'])) {
            break;
        }
        $detailed_items_batch = $shopeeService->getDetailedProductInfo($product_list_response);
        if (empty($detailed_items_batch)) {
            break; 
        }
        $all_detailed_products = array_merge($all_detailed_products, $detailed_items_batch);
        $total_items_found += count($detailed_items_batch);
        $has_next_page = $product_list_response['response']['has_next_page'] ?? false;
        $offset = $product_list_response['response']['next_offset'] ?? 0;
        if (!$has_next_page) {
            break;
        }
        usleep(250000); 
    }
    if (empty($all_detailed_products)) {
        $logger->warning("Tidak ada produk yang ditemukan di akun Shopee.");
    }
    $total_products = count($all_detailed_products);
    $json_data = json_encode($all_detailed_products);
    if (json_last_error() !== JSON_ERROR_NONE) {
         $logger->error("❌ Gagal encode JSON: " . json_last_error_msg());
         throw new Exception("Gagal memproses data produk untuk cache.");
    }
    $success = $redis->setex($redisKey, $expiry_seconds, $json_data); 
    if (!$success) {
         throw new Exception("Perintah REDIS setex gagal mengembalikan true.");
    }
    
    $redis->del($lockKey); 

} catch (Throwable $t) {
    $redis->del($lockKey); 
    $logger->critical("🔥 FATAL ERROR CRON (Throwable) - Lock dilepaskan: " . $t->getMessage(), [
        'file' => $t->getFile(),
        'line' => $t->getLine()
    ]);
}
?>