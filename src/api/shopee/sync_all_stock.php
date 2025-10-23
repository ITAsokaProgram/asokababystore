<?php
session_start();

ini_set('display_errors', 0);
set_time_limit(1800); 
ini_set('memory_limit', '512M'); 

require_once __DIR__ . '/../../utils/Logger.php';
$logger = new AppLogger('shopee_sync_all.log');

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
    $logger->info("🚀 Memulai request sync SEMUA stock...");

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
    $products_to_sync = [];
    $offset = 0;
    $page_size = 50; 
    $total_items_found = 0;
    $has_next_page = true;
    $kd_store = '3190';

    while ($has_next_page) {
        $api_params = [
            'offset'      => $offset,
            'page_size'   => $page_size,
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
                    $current_stock = $model['stock_info_v2']['summary_info']['total_available_stock']
                                   ?? $model['stock_info'][0]['seller_stock']
                                   ?? 0;
                    $products_to_sync[] = [
                        'item_id' => (int)$item['item_id'],
                        'model_id' => (int)$model['model_id'],
                        'sku' => trim($model['model_sku'] ?? ''),
                        'name' => $item_name . ' - ' . ($model['model_name'] ?? 'N/A'),
                        'current_stock' => (int)$current_stock
                    ];
                }
            } else {
                $current_stock = $item['stock_info_v2']['summary_info']['total_available_stock']
                               ?? $item['stock_info'][0]['seller_stock']
                               ?? 0;
                $products_to_sync[] = [
                    'item_id' => (int)$item['item_id'],
                    'model_id' => 0,
                    'sku' => trim($item['item_sku'] ?? ''),
                    'name' => $item_name,
                    'current_stock' => (int)$current_stock
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
        usleep(250000); // jeda 250ms
    }

    $logger->info("✅ Pengambilan data Shopee selesai. Total produk/variasi ditemukan: " . count($products_to_sync));

    if (empty($products_to_sync)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Tidak ada produk yang ditemukan di akun Shopee Anda.']);
        exit();
    }

    $all_skus = [];
    foreach ($products_to_sync as $product) {
        if (!empty($product['sku']) && $product['sku'] !== 'N/A') {
            $all_skus[] = $product['sku'];
        }
    }
    
    $db_stock_map = [];
    $unique_skus = array_unique($all_skus);

    if (!empty($unique_skus)) {
        if (!isset($conn) || !$conn instanceof mysqli) {
            $logger->critical("Objek koneksi database (\$conn) tidak ada.");
            throw new Exception("Koneksi database tidak terinisialisasi.");
        }

        try {
            $sku_chunks = array_chunk($unique_skus, 1000); // Ambil 1000 SKU per query
            $logger->info("🗃️ Mengambil stok DB untuk " . count($unique_skus) . " SKU unik dalam " . count($sku_chunks) . " chunk.");

            foreach ($sku_chunks as $chunk) {
                $placeholders = implode(',', array_fill(0, count($chunk), '?'));
                $types = str_repeat('s', count($chunk));
                $sql = "SELECT item_n, qty FROM s_barang WHERE kd_store = ? AND item_n IN ($placeholders)";
                
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    throw new Exception("Database prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param("s" . $types, $kd_store, ...$chunk);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $db_stock_map[$row['item_n']] = (int)$row['qty'];
                }
                $stmt->close();
            }
            $logger->info("✅ Berhasil mengambil " . count($db_stock_map) . " data stok dari database.");

        } catch (Exception $e) {
            $logger->error("❌ Error query database: " . $e->getMessage());
            throw new Exception("Gagal mengambil data stok dari database: " . $e->getMessage());
        }
    }

    $logger->info("🔄 Memulai proses sinkronisasi...");
    $results = [
        'synced' => 0,
        'failed' => 0,
        'skipped' => 0,
        'failed_details' => [],
        'skipped_details' => []
    ];

    foreach ($products_to_sync as $product) {
        $sku = $product['sku'];
        $item_id = (int)$product['item_id'];
        $model_id = (int)$product['model_id'];
        $name = $product['name'];
        $current_shopee_stock = $product['current_stock'];

        if (empty($sku) || $sku === 'N/A') {
            $results['skipped']++;
            $reason = "SKU tidak valid (N/A)";
            $results['skipped_details'][] = "{$name} (Item: {$item_id}) - Alasan: " . $reason;
            $logger->warning("⚠️ DILEWATI: {$reason} - Produk: '{$name}' (ItemID: {$item_id})");
            continue;
        }

        if (!isset($db_stock_map[$sku])) {
            $results['skipped']++;
            $reason = "SKU tidak ditemukan di DB";
            $results['skipped_details'][] = "{$name} (SKU: {$sku}) - Alasan: " . $reason;
            $logger->warning("⚠️ DILEWATI: {$reason} - Produk: '{$name}' (SKU: {$sku}, ItemID: {$item_id})");
            continue;
        }

        $new_stock = $db_stock_map[$sku];
        
        if ($new_stock === $current_shopee_stock) {
            $results['skipped']++; 
            $reason = "Stok sudah sama ({$new_stock})";
            $results['skipped_details'][] = "{$name} (SKU: {$sku}) - Alasan: " . $reason;
            $logger->info("🔄 SKIP (SAMA): '{$name}' (SKU: {$sku}) - Stok di DB ({$new_stock}) sudah sama dengan Shopee.");
            continue; 
        }

        $response = $shopeeService->updateStock($item_id, $new_stock, $model_id);
        
        if (isset($response['error']) && $response['error']) {
            $results['failed']++;
            $error_msg = $response['message'] ?? 'Error tidak diketahui';
            $results['failed_details'][] = "{$name} (SKU: {$sku}) - Error: " . $error_msg;
            $logger->error("❌ GAGAL UPDATE: '{$name}' (SKU: {$sku}) - " . $error_msg, $response);
        } else {
            $results['synced']++;
            $logger->success("✅ UPDATE: '{$name}' (SKU: {$sku}) disinkronkan dari {$current_shopee_stock} ke {$new_stock}");
        }
        
        usleep(100000); // 100ms jeda
    }

    $logger->info("🎉 Sinkronisasi selesai.", $results);
    echo json_encode([
        'success' => true,
        'message' => 'Sinkronisasi massal selesai.',
        'total_items_found' => count($products_to_sync), 
        'synced' => $results['synced'],
        'failed' => $results['failed'],
        'skipped' => $results['skipped'],
        'failed_details' => $results['failed_details'],
        'skipped_details' => $results['skipped_details'] 
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