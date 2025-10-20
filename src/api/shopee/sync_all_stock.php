<?php
session_start();

ini_set('display_errors', 0);
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
    $logger->info("Memulai request sync SEMUA stock...");

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

    // Ambil data JSON dari body request
    $json_payload = file_get_contents('php://input');
    $data = json_decode($json_payload, true);
    $products_to_sync = $data['products'] ?? [];
    
    $kd_store = '3190';
    $logger->info("Total produk/variasi diterima untuk sync: " . count($products_to_sync));

    if (empty($products_to_sync)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tidak ada produk yang dikirim untuk disinkronkan.']);
        exit();
    }

    // 1. Kumpulkan semua SKU yang valid
    $all_skus = [];
    foreach ($products_to_sync as $product) {
        if (!empty($product['sku']) && $product['sku'] !== 'N/A') {
            $all_skus[] = $product['sku'];
        }
    }
    
    $db_stock_map = [];
    $unique_skus = array_unique($all_skus);

    // 2. Ambil semua stok dari DB dalam satu query
    if (!empty($unique_skus)) {
        if (!isset($conn) || !$conn instanceof mysqli) {
            $logger->critical("Objek koneksi database (\$conn) tidak ada.");
            throw new Exception("Koneksi database tidak terinisialisasi.");
        }

        try {
            $placeholders = implode(',', array_fill(0, count($unique_skus), '?'));
            $types = str_repeat('s', count($unique_skus));
            
            $sql = "SELECT item_n, qty FROM s_barang WHERE kd_store = ? AND item_n IN ($placeholders)";
            
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Database prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("s" . $types, $kd_store, ...$unique_skus);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $db_stock_map[$row['item_n']] = (int)$row['qty'];
            }
            $stmt->close();
            $logger->info("Berhasil mengambil " . count($db_stock_map) . " stok dari database.");

        } catch (Exception $e) {
            $logger->error("Error query database: " . $e->getMessage());
            throw new Exception("Gagal mengambil data stok dari database: " . $e->getMessage());
        }
    }

    // 3. Loop dan update ke Shopee
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

        // Lewati jika SKU tidak valid atau tidak ditemukan di DB
        if (empty($sku) || $sku === 'N/A' || !isset($db_stock_map[$sku])) {
            $results['skipped']++;
            
            // Tentukan alasan dilewati
            $reason = "SKU tidak ditemukan di DB";
            if (empty($sku) || $sku === 'N/A') {
                $reason = "SKU tidak valid (N/A)";
            }
            
            // <-- TAMBAHKAN BARIS INI
            $results['skipped_details'][] = "SKU {$sku}: " . $reason; 
            
            $logger->warning("Dilewati: {$reason}", ['sku' => $sku, 'item_id' => $item_id]);
            continue;
        }

        $new_stock = $db_stock_map[$sku];
        
        // Panggil API Shopee
        $response = $shopeeService->updateStock($item_id, $new_stock, $model_id);
        
        if (isset($response['error']) && $response['error']) {
            $results['failed']++;
            $results['failed_details'][] = "SKU {$sku}: " . $response['message'];
            $logger->error("Gagal update SKU: {$sku}", $response);
        } else {
            $results['synced']++;
            $logger->success("Berhasil update SKU: {$sku} ke stok {$new_stock}");
        }
        
        // Beri jeda sedikit untuk menghindari rate limit
        usleep(100000); // 100ms
    }

    $logger->info("Sinkronisasi selesai.", $results);
    echo json_encode([
        'success' => true,
        'message' => 'Sinkronisasi massal selesai.',
        'synced' => $results['synced'],
        'failed' => $results['failed'],
        'skipped' => $results['skipped'],
        'failed_details' => $results['failed_details'],
        'skipped_details' => $results['skipped_details'] 
    ]);

} catch (Throwable $t) {
    $logger->critical("FATAL ERROR (Throwable): " . $t->getMessage(), [
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