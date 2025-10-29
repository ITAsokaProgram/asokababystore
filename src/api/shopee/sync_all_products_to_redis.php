<?php
session_start();

ini_set('display_errors', 0);
set_time_limit(1800); // 30 menit
ini_set('memory_limit', '512M');

// 1. Load dependencies (Logger, ShopeeService, Redis)
require_once __DIR__ . '/../../utils/Logger.php';
$logger = new AppLogger('shopee_sync_redis.log'); // Log file terpisah

try {
    include '../../../aa_kon_sett.php'; // Untuk $conn, meskipun tidak dipakai
    require_once __DIR__ . '/../../fitur/shopee/lib/ShopeeApiService.php';
    require_once __DIR__ . '/../../../redis.php'; // Memuat koneksi Redis
} catch (Throwable $t) {
    $logger->critical("Gagal memuat file dependensi: " . $t->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: Gagal memuat file. Cek log.']);
    exit();
}

header('Content-Type: application/json');

// 2. Cek koneksi Redis
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


$redisKey = 'shopee_all_products'; // Key untuk menyimpan semua produk

try {
    $logger->info("🚀 Memulai request sync SEMUA produk ke REDIS...");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $logger->warning("Method Not Allowed: " . $_SERVER['REQUEST_METHOD']);
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        exit();
    }

    $shopeeService = new ShopeeApiService($logger); // Pass logger
    if (!$shopeeService->isConnected()) {
        $logger->warning("Request gagal: Belum terautentikasi dengan Shopee.");
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Not authenticated with Shopee']);
        exit();
    }

    $logger->info("🔍 Memulai pengambilan data SEMUA produk dari Shopee...");
    
    $all_detailed_products = []; // Kita akan menyimpan objek lengkap
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

        // getDetailedProductInfo sudah mengambil data model, ini yang kita inginkan
        $detailed_items_batch = $shopeeService->getDetailedProductInfo($product_list_response);
        
        if (empty($detailed_items_batch)) {
            $logger->info("🏁 getDetailedProductInfo mengembalikan kosong untuk offset: {$offset}.");
            break; 
        }

        // Tambahkan batch ini ke array utama
        $all_detailed_products = array_merge($all_detailed_products, $detailed_items_batch);

        $total_items_found += count($detailed_items_batch);
        $has_next_page = $product_list_response['response']['has_next_page'] ?? false;
        $offset = $product_list_response['response']['next_offset'] ?? 0;

        $logger->info("Batch diproses: " . count($detailed_items_batch) . " produk. Total ditemukan: {$total_items_found}. Next offset: {$offset}. Has next: " . ($has_next_page ? 'Ya' : 'Tidak'));

        if (!$has_next_page) {
            break;
        }
        usleep(250000); // 250ms sleep
    }

    $logger->info("✅ Pengambilan data Shopee selesai. Total produk/variasi ditemukan: " . count($all_detailed_products));

    if (empty($all_detailed_products)) {
        $logger->warning("Tidak ada produk yang ditemukan di akun Shopee.");
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Tidak ada produk yang ditemukan di akun Shopee Anda.']);
        exit();
    }

    // 3. Simpan ke Redis
    $logger->info("💾 Menyimpan " . count($all_detailed_products) . " produk ke Redis key: $redisKey");
    
    // Simpan tanpa TTL (Time To Live), akan di-overwrite saat sync berikutnya
    $redis->set($redisKey, json_encode($all_detailed_products)); 

    $logger->info("🎉 Sinkronisasi ke REDIS selesai.");
    
    echo json_encode([
        'success' => true,
        'message' => 'Sinkronisasi produk ke cache Redis selesai.',
        'total_items_saved_to_redis' => count($all_detailed_products)
    ]);

} catch (Throwable $t) {
    $logger->critical("🔥 FATAL ERROR (Throwable): " . $t->getMessage(), [
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