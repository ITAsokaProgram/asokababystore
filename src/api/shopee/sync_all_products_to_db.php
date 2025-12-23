<?php
session_start();
ini_set('display_errors', 0);
set_time_limit(1800);
ini_set('memory_limit', '512M');
require_once __DIR__ . '/../../utils/Logger.php';
$logger = new AppLogger('shopee_sync_db.log');
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
$startTime = microtime(true);
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $logger->warning("Method Not Allowed: " . $_SERVER['REQUEST_METHOD']);
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        exit();
    }
    if (!isset($conn) || !$conn instanceof mysqli) {
        throw new Exception("Koneksi Database Gagal.");
    }
    $shopeeService = new ShopeeApiService($logger);
    $total_products = $shopeeService->syncAllProductsToDatabase($conn, false);
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    $logger->info("[SUCCESS] Sync DB selesai. Total: {$total_products}. Durasi: {$duration}s.");
    echo json_encode([
        'success' => true,
        'message' => 'Sinkronisasi produk ke Database selesai.',
        'total_items_saved' => $total_products,
        'duration_seconds' => $duration
    ]);
} catch (Throwable $t) {
    $errorMessage = $t->getMessage();
    $statusCode = 500;
    if (strpos($errorMessage, 'Not authenticated') !== false) {
        $statusCode = 401;
        $errorMessage = "Token Shopee tidak valid. Harap autentikasi ulang.";
    } elseif (strpos($errorMessage, 'Sinkronisasi sedang berjalan') !== false) {
        $statusCode = 429;
    }
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    $logger->critical("🔥 API ERROR: " . $t->getMessage());
    if (!headers_sent()) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => false,
            'message' => $errorMessage
        ]);
    }
}
?>