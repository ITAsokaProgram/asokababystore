<?php
session_start();

ini_set('display_errors', 0);
set_time_limit(1800); // 30 menit
ini_set('memory_limit', '512M');

require_once __DIR__ . '/../../utils/Logger.php';
$logger = new AppLogger('shopee_sync_redis.log'); 

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

try {
    if (!isset($redis) || !$redis->ping()) {
        throw new Exception("Koneksi Redis Gagal.");
    }
} catch (Throwable $t) {
    $logger->critical("Koneksi Redis Gagal: " . $t->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: Koneksi Redis Gagal.']);
    exit();
}


$redisKey = 'shopee_all_products'; 
$lockKey = 'shopee_sync_in_progress';

try {

    // --- TAMBAHKAN BLOK LOCK ---
    $lockAcquired = $redis->set($lockKey, 1, ['nx', 'ex' => 1800]); // Lock 30 menit
    if (!$lockAcquired) {
        $logger->warning("Gagal mendapatkan lock '{$lockKey}'. Sync lain mungkin sedang berjalan.");
        http_response_code(429); // Too Many Requests
        echo json_encode(['success' => false, 'message' => 'Sinkronisasi sudah sedang berjalan. Silakan coba lagi dalam beberapa menit.']);
        exit();
    }
    // --- SELESAI BLOK LOCK ---

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $logger->warning("Method Not Allowed: " . $_SERVER['REQUEST_METHOD']);
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        exit();
    }

    $shopeeService = new ShopeeApiService($logger); 
    if (!$shopeeService->isConnected()) {
        $logger->warning("Request gagal: Belum terautentikasi dengan Shopee.");
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Not authenticated with Shopee']);
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
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Tidak ada produk yang ditemukan di akun Shopee Anda.']);
        exit();
    }



    $total_products = count($all_detailed_products);
    $expiry_seconds = 3600; 
    
    $json_data = json_encode($all_detailed_products);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
         $logger->error("❌ Gagal encode JSON sebelum menyimpan ke Redis: " . json_last_error_msg());
         throw new Exception("Gagal memproses data produk untuk cache.");
    }

    
    $success = $redis->setex($redisKey, $expiry_seconds, $json_data); 

    if (!$success) {
         throw new Exception("Perintah REDIS setex gagal mengembalikan true. Key mungkin tidak tersimpan.");
    }

    
    $redis->del($lockKey); // <--- RELEASE LOCK
    
    echo json_encode([
        'success' => true,
        'message' => 'Sinkronisasi produk ke cache Redis selesai.',
        'total_items_saved_to_redis' => $total_products
    ]);

} catch (Throwable $t) {
    $redis->del($lockKey); // <--- RELEASE LOCK ON ERROR
    $logger->critical("🔥 FATAL ERROR (Throwable) - Lock dilepaskan: " . $t->getMessage(), [
        'file' => $t->getFile(),
        'line' => $t->getLine()
    ]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Terjadi kesalahan internal server: " . $t->getMessage()
    ]);
}
?>