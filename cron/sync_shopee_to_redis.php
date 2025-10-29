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
$expiry_seconds = 3600; 
try {
    $logger->info("🚀 Memulai request sync SEMUA produk ke REDIS (via CRON)...");
    $shopeeService = new ShopeeApiService($logger); 
    if (!$shopeeService->isConnected()) {
        $logger->warning("CRON Gagal: Belum terautentikasi dengan Shopee (Kredensial di DB kosong?).");
        exit();
    }
    $logger->info("🔍 Memulai pengambilan data SEMUA produk dari Shopee...");
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
            $logger->info("🏁 Tidak ada item lagi pada offset: {$offset}. Selesai mengambil list.");
            break;
        }
        $detailed_items_batch = $shopeeService->getDetailedProductInfo($product_list_response);
        if (empty($detailed_items_batch)) {
            $logger->info("🏁 getDetailedProductInfo mengembalikan kosong untuk offset: {$offset}.");
            break; 
        }
        $all_detailed_products = array_merge($all_detailed_products, $detailed_items_batch);
        $total_items_found += count($detailed_items_batch);
        $has_next_page = $product_list_response['response']['has_next_page'] ?? false;
        $offset = $product_list_response['response']['next_offset'] ?? 0;
        $logger->info("Batch diproses: " . count($detailed_items_batch) . " produk. Total ditemukan: {$total_items_found}. Next offset: {$offset}. Has next: " . ($has_next_page ? 'Ya' : 'Tidak'));
        if (!$has_next_page) {
            break;
        }
        usleep(250000); 
    }
    $logger->info("✅ Pengambilan data Shopee selesai. Total produk/variasi ditemukan: " . count($all_detailed_products));
    if (empty($all_detailed_products)) {
        $logger->warning("Tidak ada produk yang ditemukan di akun Shopee.");
    }
    $total_products = count($all_detailed_products);
    $logger->info("💾 Meng-encode $total_products produk ke JSON...");
    $json_data = json_encode($all_detailed_products);
    if (json_last_error() !== JSON_ERROR_NONE) {
         $logger->error("❌ Gagal encode JSON: " . json_last_error_msg());
         throw new Exception("Gagal memproses data produk untuk cache.");
    }
    $logger->info("💾 Menyimpan $total_products produk ke Redis key: $redisKey dengan TTL: $expiry_seconds detik...");
    $success = $redis->setex($redisKey, $expiry_seconds, $json_data); 
    if (!$success) {
         throw new Exception("Perintah REDIS setex gagal mengembalikan true.");
    }
    $logger->info("🎉 Sinkronisasi CRON ke REDIS selesai.");
} catch (Throwable $t) {
    $logger->critical("🔥 FATAL ERROR CRON (Throwable): " . $t->getMessage(), [
        'file' => $t->getFile(),
        'line' => $t->getLine()
    ]);
}
?>