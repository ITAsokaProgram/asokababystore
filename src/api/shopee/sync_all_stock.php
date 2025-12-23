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
    if (!isset($conn) || !$conn instanceof mysqli) {
        throw new Exception("Koneksi Database Gagal.");
    }
    $products_to_sync = [];
    $kd_store = '3190';
    $kd_store_ol = '9998';
    try {
        $sql_local = "SELECT kode_produk, kode_variasi, sku, nama_produk, nama_variasi, stok FROM s_shopee_produk";
        $result_local = $conn->query($sql_local);
        if ($result_local->num_rows > 0) {
            while ($row = $result_local->fetch_assoc()) {
                $sku = trim($row['sku']);
                if (empty($sku))
                    continue;
                $full_name = $row['nama_produk'];
                if (!empty($row['nama_variasi'])) {
                    $full_name .= ' - ' . $row['nama_variasi'];
                }
                $products_to_sync[] = [
                    'item_id' => (int) $row['kode_produk'],
                    'model_id' => (int) $row['kode_variasi'],
                    'sku' => $sku,
                    'name' => $full_name,
                    'current_stock' => (int) $row['stok']
                ];
            }
        } else {
            $logger->error("❌ GAGAL: Tabel s_shopee_produk kosong.");
            throw new Exception("Data produk kosong. Harap jalankan 'Sync Produk ke Cache (DB)' terlebih dahulu.");
        }
        $logger->info("Berhasil mengambil " . count($products_to_sync) . " item dari database s_shopee_produk.");
    } catch (Throwable $t) {
        $logger->critical("🔥 FATAL ERROR (Fetch DB Local): " . $t->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => "Terjadi kesalahan saat mengambil data lokal: " . $t->getMessage()
        ]);
        exit();
    }
    $all_skus = [];
    foreach ($products_to_sync as $product) {
        $all_skus[] = $product['sku'];
    }
    $db_stock_map = [];
    $excluded_skus_map = [];
    $unique_skus = array_unique($all_skus);
    if (!empty($unique_skus)) {
        try {
            $sku_chunks = array_chunk($unique_skus, 500);
            foreach ($sku_chunks as $chunk) {
                $placeholders = implode(',', array_fill(0, count($chunk), '?'));
                $types = str_repeat('s', count($chunk));
                $sql_ol = "SELECT item_n FROM s_stok_ol WHERE KD_STORE = ? AND item_n IN ($placeholders)";
                $stmt_ol = $conn->prepare($sql_ol);
                if ($stmt_ol) {
                    $stmt_ol->bind_param("s" . $types, $kd_store_ol, ...$chunk);
                    $stmt_ol->execute();
                    $res_ol = $stmt_ol->get_result();
                    while ($r = $res_ol->fetch_assoc()) {
                        $excluded_skus_map[trim($r['item_n'])] = true;
                    }
                    $stmt_ol->close();
                }
                $sql_brg = "SELECT item_n, qty FROM s_barang WHERE kd_store = ? AND item_n IN ($placeholders)";
                $stmt_brg = $conn->prepare($sql_brg);
                if ($stmt_brg) {
                    $stmt_brg->bind_param("s" . $types, $kd_store, ...$chunk);
                    $stmt_brg->execute();
                    $res_brg = $stmt_brg->get_result();
                    while ($r = $res_brg->fetch_assoc()) {
                        $db_stock_map[trim($r['item_n'])] = (int) $r['qty'];
                    }
                    $stmt_brg->close();
                }
            }
        } catch (Exception $e) {
            $logger->error("❌ Error query master stok: " . $e->getMessage());
            throw new Exception("Gagal mengambil data stok master.");
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
        $item_id = $product['item_id'];
        $model_id = $product['model_id'];
        $name = $product['name'];
        $current_shopee_stock = $product['current_stock'];
        if (isset($excluded_skus_map[$sku])) {
            $results['skipped']++;
            $results['skipped_details'][] = "$name ($sku) - Ada di s_stok_ol (Skip)";
            continue;
        }
        if (!isset($db_stock_map[$sku])) {
            $results['skipped']++;
            $results['skipped_details'][] = "$name ($sku) - Tidak ada di s_barang";
            continue;
        }
        $new_stock = $db_stock_map[$sku];
        if ($new_stock === $current_shopee_stock) {
            $results['skipped']++;
            continue;
        }
        $response = $shopeeService->updateStock($item_id, $new_stock, $model_id);
        if (isset($response['error']) && $response['error']) {
            $results['failed']++;
            $results['failed_details'][] = "$name ($sku) - Error: " . ($response['message'] ?? 'Unknown');
            $logger->error("Gagal Update $name: " . ($response['message'] ?? ''));
        } else {
            $results['synced']++;
            $logger->success("Updated $name ($sku): $current_shopee_stock -> $new_stock");
        }
        usleep(100000);
    }
    $cache_refresh_result = ['message' => 'Tidak dijalankan'];
    if ($results['synced'] > 0) {
        try {
            $shopeeService->syncAllProductsToDatabase($conn);
            $cache_refresh_result['message'] = "Database lokal berhasil di-refresh.";
        } catch (Throwable $t) {
            $cache_refresh_result['message'] = "Gagal refresh database lokal: " . $t->getMessage();
        }
    }
    echo json_encode([
        'success' => true,
        'message' => 'Sinkronisasi massal selesai.',
        'total_items_found' => count($products_to_sync),
        'synced' => $results['synced'],
        'failed' => $results['failed'],
        'skipped' => $results['skipped'],
        'db_refresh' => $cache_refresh_result,
        'failed_details' => $results['failed_details'],
        'skipped_details' => $results['skipped_details']
    ]);
} catch (Throwable $t) {
    $logger->critical("FATAL ERROR: " . $t->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => "Error: " . $t->getMessage()]);
}
?>