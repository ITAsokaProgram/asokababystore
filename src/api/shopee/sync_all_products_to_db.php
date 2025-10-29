<?php
session_start();

ini_set('display_errors', 0);
set_time_limit(1800); // 30 menit
ini_set('memory_limit', '512M'); 

require_once __DIR__ . '/../../utils/Logger.php';
$logger = new AppLogger('shopee_sync_products.log'); // Log file terpisah

try {
    include '../../../aa_kon_sett.php';
    require_once __DIR__ . '/../../fitur/shopee/lib/ShopeeApiService.php';
} catch (Throwable $t) {
    $logger->critical("Gagal memuat file dependensi: " . $t->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: Gagal memuat file. Cek log.']);
    exit();
}

header('Content-Type: application/json');

try {
    $logger->info("🚀 Memulai request sync SEMUA produk ke tabel s_shopee...");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $logger->warning("Method Not Allowed: " . $_SERVER['REQUEST_METHOD']);
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        exit();
    }

    $shopeeService = new ShopeeApiService();
    if (!$shopeeService->isConnected()) {
        $logger->warning("Request gagal: Belum terautentikasi dengan Shopee.");
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Not authenticated with Shopee']);
        exit();
    }

    $logger->info("🔍 Memulai pengambilan data SEMUA produk dari Shopee...");
    $products_to_insert = [];
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

        $detailed_items = $shopeeService->getDetailedProductInfo($product_list_response);
        if (empty($detailed_items)) {
            $logger->info("🏁 getDetailedProductInfo mengembalikan kosong untuk offset: {$offset}.");
            break;
        }

        foreach ($detailed_items as $item) {
            $item_name = $item['item_name'] ?? 'N/A';
            
            if (isset($item['has_model']) && $item['has_model'] === true && !empty($item['models'])) {
                foreach ($item['models'] as $model) {
                    $price = $model['price_info'][0]['original_price'] ?? 0.0;
                    $products_to_insert[] = [
                        'sku' => trim($model['model_sku'] ?? ''),
                        'name' => $item_name . ' - ' . ($model['model_name'] ?? 'N/A'),
                        'price' => (float)$price
                    ];
                }
            } else {
                $price = $item['price_info'][0]['original_price'] ?? 0.0;
                $products_to_insert[] = [
                    'sku' => trim($item['item_sku'] ?? ''),
                    'name' => $item_name,
                    'price' => (float)$price
                ];
            }
        }

        $total_items_found += count($detailed_items);
        $has_next_page = $product_list_response['response']['has_next_page'] ?? false;
        $offset = $product_list_response['response']['next_offset'] ?? 0;

        $logger->info("Batch diproses: " . count($detailed_items) . " produk. Total ditemukan: {$total_items_found}. Next offset: {$offset}. Has next: " . ($has_next_page ? 'Ya' : 'Tidak'));

        if (!$has_next_page) {
            break;
        }
        usleep(250000); 
    }

    $logger->info("✅ Pengambilan data Shopee selesai. Total produk/variasi ditemukan: " . count($products_to_insert));

    if (empty($products_to_insert)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Tidak ada produk yang ditemukan di akun Shopee Anda.']);
        exit();
    }

    // --- Validasi dan Insert ke Database ---

    if (!isset($conn) || !$conn instanceof mysqli) {
        $logger->critical("Objek koneksi database (\$conn) tidak ada.");
        throw new Exception("Koneksi database tidak terinisialisasi.");
    }

    $logger->info("🗃️ Mengambil data ITEM_N yang sudah ada dari s_shopee...");
    $existing_item_n = [];
    
    try {
        $stmt_check = $conn->prepare("SELECT ITEM_N FROM s_shopee");
        if ($stmt_check === false) {
             throw new Exception("Database prepare (s_shopee SELECT) failed: " . $conn->error);
        }
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        while ($row = $result_check->fetch_assoc()) {
            $existing_item_n[trim($row['ITEM_N'])] = true;
        }
        $stmt_check->close();
        $logger->info("Ditemukan " . count($existing_item_n) . " item yang sudah ada di s_shopee.");
    
    } catch (Exception $e) {
         $logger->error("❌ Error query database (SELECT s_shopee): " . $e->getMessage());
         throw new Exception("Gagal mengambil data dari s_shopee: " . $e->getMessage());
    }


    $logger->info("🔄 Memulai proses insert ke s_shopee...");
    $inserted_count = 0;
    $skipped_count = 0;
    $harga_beli = null; // Sesuai permintaan, Harga_Beli di-set NULL

    try {
        $conn->begin_transaction();
        
        $stmt_insert = $conn->prepare("INSERT INTO s_shopee (ITEM_N, DESCP, Harga_Beli, Harga_Jual) VALUES (?, ?, ?, ?)");
        if ($stmt_insert === false) {
            throw new Exception("Database prepare (s_shopee INSERT) failed: " . $conn->error);
        }

        foreach ($products_to_insert as $product) {
            $sku = $product['sku'];
            $name = $product['name'];
            $price = $product['price'];

            // 1. Skip jika SKU tidak valid
            if (empty($sku) || $sku === 'N/A') {
                $skipped_count++;
                continue;
            }

            // 2. Skip jika SKU sudah ada di database
            if (isset($existing_item_n[$sku])) {
                $skipped_count++;
                continue;
            }

            $stmt_insert->bind_param("ssdd", $sku, $name, $harga_beli, $price);
            $stmt_insert->execute();
            
            $inserted_count++;
            $existing_item_n[$sku] = true; // Tandai sbg sudah ada (untuk deduplikasi data dari Shopee)
        }

        $stmt_insert->close();
        $conn->commit();

    } catch (Exception $e) {
        $conn->rollback();
        $logger->error("❌ GAGAL INSERT: " . $e->getMessage());
        throw new Exception("Gagal memasukkan data ke database: " . $e->getMessage());
    }

    $logger->info("🎉 Sinkronisasi ke DB selesai. Inserted: {$inserted_count}, Skipped: {$skipped_count}");
    
    echo json_encode([
        'success' => true,
        'message' => 'Sinkronisasi produk ke database selesai.',
        'total_items_found' => count($products_to_insert), 
        'inserted' => $inserted_count,
        'skipped' => $skipped_count
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