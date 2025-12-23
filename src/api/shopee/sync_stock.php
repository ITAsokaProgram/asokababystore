<?php
session_start();

ini_set('display_errors', 0); 
require_once __DIR__ . '/../../utils/Logger.php';
$logger = new AppLogger('shopee_sync.log'); 

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

function getFriendlyErrorMessage($errorCode, $originalMessage) {
    $friendlyMessage = 'Terjadi kesalahan saat berkomunikasi dengan Shopee. Silakan coba lagi nanti.';
    return $friendlyMessage . "\n\nPesan Teknis: [{$errorCode}] {$originalMessage}";
}

try {
    $logger->info("Memulai request sync stock...");

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

    $item_id = (int)($_POST['item_id'] ?? 0);
    $model_id = (int)($_POST['model_id'] ?? 0);
    $sku = trim($_POST['sku'] ?? '');
    $kd_store = '3190';
    
    $logger->info("Payload diterima", ['item_id' => $item_id, 'model_id' => $model_id, 'sku' => $sku]);

    if (!$item_id || empty($sku) || $sku === 'N/A') {
        http_response_code(400);
        $message = 'ID Produk dan SKU wajib diisi untuk sinkronisasi.';
        if ($sku === 'N/A' || empty($sku)) $message = 'Produk/variasi ini tidak memiliki SKU (N/A), tidak bisa disinkronkan.';
        
        $logger->warning("Validasi gagal: " . $message, ['item_id' => $item_id, 'sku' => $sku]);
        echo json_encode(['success' => false, 'message' => $message]);
        exit();
    }

    $new_stock = 0;

    if (!isset($conn) || !$conn instanceof mysqli) {
         $logger->critical("Objek koneksi database (\$conn) tidak ada atau bukan instance mysqli. Cek file 'aa_kon_sett.php'.");
         throw new Exception("Koneksi database tidak terinisialisasi.");
    }
    $logger->debug("Pengecekan koneksi database (\$conn) berhasil.");

    try {
        $stmt = $conn->prepare("SELECT qty FROM s_barang WHERE kd_store = ? AND item_n = ?");
        if ($stmt === false) {
            $logger->error("Database prepare failed: " . $conn->error);
            throw new Exception("Database prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("ss", $kd_store, $sku);  
        $stmt->execute();
        $result = $stmt->get_result();
        
        $logger->debug("Query database berhasil untuk SKU: {$sku}");

        if ($result->num_rows === 0) {
            http_response_code(404);
            $message = "SKU '{$sku}' tidak ditemukan di database (tabel s_barang) dengan kd_store '{$kd_store}'.";
            $logger->warning($message);
            echo json_encode(['success' => false, 'message' => $message]);
            $stmt->close();
            exit();
        }

        $row = $result->fetch_assoc();
        $new_stock = (int)$row['qty'];  
        $stmt->close();
        
        $logger->info("Stok ditemukan di database: {$new_stock} untuk SKU: {$sku}");

    } catch (Exception $e) {
        $logger->error("Error internal query database: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error Database Internal: ' . $e->getMessage()]);
        exit();
    }


    $response = $shopeeService->updateStock($item_id, $new_stock, $model_id);
    $logger->debug("Respon dari Shopee API updateStock:", $response);

    if (isset($response['error']) && $response['error']) {
        http_response_code(400);
        $friendlyMessage = getFriendlyErrorMessage($response['error'], $response['message']);
        $logger->warning("Shopee API Error: " . $friendlyMessage);
        echo json_encode([
            'success' => false,
            'message' => $friendlyMessage
        ]);
    } else {
        $logger->success("Sinkronisasi stok berhasil. SKU: {$sku}, Stok Baru: {$new_stock}");
        echo json_encode([
            'success' => true,
            'new_stock' => $new_stock,  
            'message' => "Stok untuk SKU {$sku} berhasil disinkronkan ke {$new_stock} (sesuai database)."
        ]);
    }

} catch (Throwable $t) {
    $logger->critical("FATAL ERROR (Throwable): " . $t->getMessage(), [
        'file' => $t->getFile(),
        'line' => $t->getLine(),
        'trace' => $t->getTraceAsString()
    ]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Terjadi kesalahan internal server. Administrator telah diberitahu.\n\nPesan Teknis: " . $t->getMessage()
    ]);
}
?>