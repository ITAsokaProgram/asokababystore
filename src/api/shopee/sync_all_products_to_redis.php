<?php
session_start();
ini_set('display_errors', 0);
set_time_limit(1800); 
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
    $startTime = microtime(true);
    $lockAcquired = $redis->set($lockKey, 1, ['nx', 'ex' => 1800]); 
    if (!$lockAcquired) {
        $logger->warning("Gagal mendapatkan lock '{$lockKey}'. Sync lain mungkin sedang berjalan.");
        http_response_code(429); 
        echo json_encode(['success' => false, 'message' => 'Sinkronisasi sudah sedang berjalan. Silakan coba lagi dalam beberapa menit.']);
        exit();
    }
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
    $logger->info("[START] Mulai sinkronisasi Shopee ke Redis...");
    $all_detailed_products = []; 
    $offset = 0;
    $page_size = 50;
    $total_items_found = 0;
    $has_next_page = true;
    $page_counter = 1; 
    while ($has_next_page) {
        $logger->info("[PROGRESS] Mengambil batch #{$page_counter}. Offset: {$offset}, Page Size: {$page_size}.");
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
            $logger->info("[PROGRESS] Tidak ada item lagi di batch (Offset: {$offset}). Berhenti mengambil.");
            break;
        }
        $detailed_items_batch = $shopeeService->getDetailedProductInfo($product_list_response);
        if (empty($detailed_items_batch)) {
            $logger->info("[PROGRESS] Batch (Offset: {$offset}) tidak mengembalikan item detail. Menghentikan loop.");
            break; 
        }
        $batch_count = count($detailed_items_batch); 
        $all_detailed_products = array_merge($all_detailed_products, $detailed_items_batch);
        $total_items_found += $batch_count; 
        $logger->info("[PROGRESS] Batch #{$page_counter} (Offset: {$offset}) sukses. Ditemukan {$batch_count} item. Total sementara: {$total_items_found}.");
        $has_next_page = $product_list_response['response']['has_next_page'] ?? false;
        $offset = $product_list_response['response']['next_offset'] ?? 0;
        $page_counter++;
        if (!$has_next_page) {
            $logger->info("[PROGRESS] Shopee API melaporkan tidak ada halaman berikutnya (has_next_page: false).");
            break;
        }
        usleep(250000); 
    }
    $total_products = count($all_detailed_products);
    if (empty($all_detailed_products)) {
        $logger->warning("Tidak ada produk yang ditemukan di akun Shopee setelah iterasi selesai.");
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Tidak ada produk yang ditemukan di akun Shopee Anda.']);
        exit();
    }
    $logger->info("[SAVE] Selesai mengambil semua produk dari Shopee. Total item: {$total_products}. Mulai menyimpan ke Redis key '{$redisKey}'...");
    $expiry_seconds = 7500; 
    $json_data = json_encode($all_detailed_products);
    if (json_last_error() !== JSON_ERROR_NONE) {
         $logger->error("❌ Gagal encode JSON sebelum menyimpan ke Redis: " . json_last_error_msg());
         throw new Exception("Gagal memproses data produk untuk cache.");
    }
    $success = $redis->setex($redisKey, $expiry_seconds, $json_data); 
    if (!$success) {
        $logger->error("❌ Gagal menyimpan ke Redis. Perintah SETEX mengembalikan false. Key: {$redisKey}");
        throw new Exception("Perintah REDIS setex gagal mengembalikan true. Key mungkin tidak tersimpan.");
    }
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    $logger->info("[SUCCESS] Berhasil menyimpan {$total_products} item ke Redis key '{$redisKey}'. Durasi total: {$duration} detik.");
    $redis->del($lockKey); 
    echo json_encode([
        'success' => true,
        'message' => 'Sinkronisasi produk ke cache Redis selesai.',
        'total_items_saved_to_redis' => $total_products,
        'duration_seconds' => $duration 
    ]);
} catch (Throwable $t) {
    $redis->del($lockKey);
    $errorMessage = $t->getMessage();
    $statusCode = 500;

    if (strpos($errorMessage, 'Invalid access_token') !== false || strpos($errorMessage, 'invalid_acceess_token') !== false) {
        if (isset($shopeeService)) {
            $shopeeService->disconnect();
            $logger->warning("API: Token invalid terdeteksi. Otomatis disconnect.");
        }
        $statusCode = 401;
        $errorMessage = "Token Shopee tidak valid (invalid access_token). Halaman akan dimuat ulang.";
    }
    
    $endTime = microtime(true);
    if (!isset($startTime)) {
        $startTime = $endTime; 
    }
    $duration = round($endTime - $startTime, 2);
    
    $logger->critical("🔥 FATAL ERROR API (Throwable) - Lock dilepaskan. Durasi berjalan: {$duration} detik. Error: " . $t->getMessage(), [
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