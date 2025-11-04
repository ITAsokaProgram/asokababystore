<?php
session_start();
ini_set('display_errors', 0);
set_time_limit(1800);
ini_set('memory_limit', '512M');
require_once __DIR__ . '/../../utils/Logger.php';
$logger = new AppLogger('shopee_sync_all_stock.log');
try {
    include '../../../aa_kon_sett.php';
    require_once __DIR__ . '/../../fitur/shopee/lib/ShopeeApiService.php';
    require_once __DIR__ . '/../../../redis.php';
} catch (Throwable $t) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: Gagal memuat file dependensi. Cek log.']);
    exit();
}
header('Content-Type: application/json');
try {
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
    $products_to_sync = [];
    $kd_store = '3190';
    $kd_store_ol = '9998';
    $redisKey = 'shopee_all_products';
    $all_products_from_redis = [];
    try {
        if (!isset($redis) || !$redis->ping()) {
            throw new Exception("Koneksi Redis Gagal.");
        }
        $cached_products = $redis->get($redisKey);
        if (empty($cached_products)) {
            $logger->error("❌ GAGAL: Cache produk di Redis (key: '{$redisKey}') kosong atau tidak ditemukan.");
            throw new Exception("Cache produk di Redis kosong. Harap jalankan 'Sync Produk ke Cache' di halaman produk Shopee terlebih dahulu.");
        }
        $all_products_from_redis = json_decode($cached_products, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $logger->error("❌ GAGAL: Gagal decode JSON dari Redis. Error: " . json_last_error_msg());
            throw new Exception("Gagal membaca data cache produk dari Redis (JSON tidak valid).");
        }
        $logger->info("Berhasil mengambil " . count($all_products_from_redis) . " item dari cache Redis '{$redisKey}'.");
    } catch (Throwable $t) {
        $logger->critical("🔥 FATAL ERROR (Redis): " . $t->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => "Terjadi kesalahan saat mengambil data dari Redis: " . $t->getMessage()
        ]);
        exit();
    }
    foreach ($all_products_from_redis as $item) {
        $item_name = $item['item_name'] ?? 'N/A';
        $item_id = (int) ($item['item_id'] ?? 0);
        if (isset($item['has_model']) && $item['has_model'] === true && !empty($item['models'])) {
            foreach ($item['models'] as $model) {
                $current_stock = $model['stock_info_v2']['summary_info']['total_available_stock']
                    ?? $model['stock_info'][0]['seller_stock']
                    ?? 0;
                $model_id = (int) ($model['model_id'] ?? 0);
                $sku = trim($model['model_sku'] ?? '');
                $model_name = $model['model_name'] ?? 'N/A';
                $products_to_sync[] = [
                    'item_id' => $item_id,
                    'model_id' => $model_id,
                    'sku' => $sku,
                    'name' => $item_name . ' - ' . $model_name,
                    'current_stock' => (int) $current_stock
                ];
            }
        } else {
            $current_stock = $item['stock_info_v2']['summary_info']['total_available_stock']
                ?? $item['stock_info'][0]['seller_stock']
                ?? 0;
            $sku = trim($item['item_sku'] ?? '');
            $products_to_sync[] = [
                'item_id' => $item_id,
                'model_id' => 0,
                'sku' => $sku,
                'name' => $item_name,
                'current_stock' => (int) $current_stock
            ];
        }
    }
    if (empty($products_to_sync)) {
        $logger->warning("Tidak ada produk yang ditemukan di cache Redis (array products_to_sync kosong).");
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Tidak ada produk yang ditemukan di cache Redis.']);
        exit();
    }
    $all_skus = [];
    foreach ($products_to_sync as $product) {
        if (!empty($product['sku']) && $product['sku'] !== 'N/A') {
            $all_skus[] = $product['sku'];
        }
    }
    $db_stock_map = [];
    $excluded_skus_map = [];
    $unique_skus = array_unique($all_skus);
    $logger->info("Total unique SKUs dari Redis: " . count($unique_skus));
    if (!empty($unique_skus)) {
        if (!isset($conn) || !$conn instanceof mysqli) {
            $logger->critical("Objek koneksi database (\$conn) tidak ada.");
            throw new Exception("Koneksi database tidak terinisialisasi.");
        }
        try {
            $sku_chunks_ol = array_chunk($unique_skus, 1000);
            foreach ($sku_chunks_ol as $chunk) {
                $placeholders = implode(',', array_fill(0, count($chunk), '?'));
                $types = str_repeat('s', count($chunk));
                $sql_stok_ol = "SELECT item_n FROM s_stok_ol WHERE KD_STORE = ? AND item_n IN ($placeholders)";
                $stmt_stok_ol = $conn->prepare($sql_stok_ol);
                if ($stmt_stok_ol === false) {
                    throw new Exception("Database prepare (s_stok_ol) failed: " . $conn->error);
                }
                $stmt_stok_ol->bind_param("s" . $types, $kd_store_ol, ...$chunk);
                $stmt_stok_ol->execute();
                $result_stok_ol = $stmt_stok_ol->get_result();
                while ($row_ol = $result_stok_ol->fetch_assoc()) {
                    $excluded_skus_map[$row_ol['item_n']] = true;
                }
                $stmt_stok_ol->close();
            }
            $logger->info("MAP PENGECUALIAN: Ditemukan " . count($excluded_skus_map) . " item dari s_stok_ol (KD_STORE: {$kd_store_ol}). Item ini akan dilewati.");
            $skus_for_barang = array_diff($unique_skus, array_keys($excluded_skus_map));
            $logger->info("MAP BARANG: Mencari " . count($skus_for_barang) . " sisa item di s_barang.");
            if (!empty($skus_for_barang)) {
                $sku_chunks_barang = array_chunk($skus_for_barang, 1000);
                foreach ($sku_chunks_barang as $chunk_barang) {
                    $placeholders_barang = implode(',', array_fill(0, count($chunk_barang), '?'));
                    $types_barang = str_repeat('s', count($chunk_barang));
                    $sql_barang = "SELECT item_n, qty FROM s_barang WHERE kd_store = ? AND item_n IN ($placeholders_barang)";
                    $stmt_barang = $conn->prepare($sql_barang);
                    if ($stmt_barang === false) {
                        throw new Exception("Database prepare (s_barang) failed: " . $conn->error);
                    }
                    $stmt_barang->bind_param("s" . $types_barang, $kd_store, ...$chunk_barang);
                    $stmt_barang->execute();
                    $result_barang = $stmt_barang->get_result();
                    while ($row_barang = $result_barang->fetch_assoc()) {
                        $db_stock_map[$row_barang['item_n']] = (int) $row_barang['qty'];
                    }
                    $stmt_barang->close();
                }
            }
            $logger->info("MAP BARANG: Ditemukan " . count($db_stock_map) . " item dari s_barang (KD_STORE: {$kd_store}). Item ini akan disinkronkan.");
        } catch (Exception $e) {
            $logger->error("❌ Error query database: " . $e->getMessage());
            throw new Exception("Gagal mengambil data stok dari database: " . $e->getMessage());
        }
    }
    $results = [
        'synced' => 0,
        'failed' => 0,
        'skipped' => 0,
        'failed_details' => [],
        'skipped_details' => []
    ];
    foreach ($products_to_sync as $product) {
        $sku = $product['sku'];
        $item_id = (int) $product['item_id'];
        $model_id = (int) $product['model_id'];
        $name = $product['name'];
        $current_shopee_stock = $product['current_stock'];
        if (empty($sku) || $sku === 'N/A') {
            $results['skipped']++;
            $reason = "SKU tidak valid (N/A)";
            $results['skipped_details'][] = "{$name} (Item: {$item_id}) - Alasan: " . $reason;
            $logger->warning("⚠️ DILEWATI: {$reason} - Produk: '{$name}' (ItemID: {$item_id})");
            continue;
        }
        if (isset($excluded_skus_map[$sku])) {
            $results['skipped']++;
            $reason = "SKU ditemukan di s_stok_ol (dilewati)";
            $results['skipped_details'][] = "{$name} (SKU: {$sku}) - Alasan: " . $reason;
            $logger->info("DILEWATI: '{$name}' (SKU: {$sku}). Alasan: {$reason}");
            continue;
        }
        if (!isset($db_stock_map[$sku])) {
            $results['skipped']++;
            $reason = "SKU tidak ditemukan di s_barang (setelah filter s_stok_ol)";
            $results['skipped_details'][] = "{$name} (SKU: {$sku}) - Alasan: " . $reason;
            $logger->warning("⚠️ DILEWATI: {$reason} - Produk: '{$name}' (SKU: {$sku}, ItemID: {$item_id})");
            continue;
        }
        $new_stock = $db_stock_map[$sku];
        $stock_source = "s_barang";
        if ($new_stock === $current_shopee_stock) {
            $results['skipped']++;
            $reason = "Stok sudah sama ({$new_stock}) dari [{$stock_source}]";
            $results['skipped_details'][] = "{$name} (SKU: {$sku}) - Alasan: " . $reason;
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
            $logger->success("✅ UPDATE: '{$name}' (SKU: {$sku}) disinkronkan dari {$current_shopee_stock} ke {$new_stock} (Sumber: {$stock_source})");
        }
        usleep(100000);
    }
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