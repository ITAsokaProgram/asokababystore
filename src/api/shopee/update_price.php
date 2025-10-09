<?php
session_start();
require_once __DIR__ . '/../../fitur/shopee/lib/ShopeeApiService.php';

header('Content-Type: application/json');

function getFriendlyErrorMessage($errorCode, $originalMessage) {
    $friendlyMessage = '';
    switch ($errorCode) {
        case 'product.error_update_price_fail':
            $friendlyMessage = "Gagal memperbarui harga. Shopee kemungkinan memiliki batasan pada perubahan harga yang drastis (misalnya, tidak boleh turun lebih dari 80%).\n\n💡 **Saran:** Coba turunkan harga secara bertahap atau periksa kembali nominal harga yang Anda masukkan.";
            break;
        default:
            $friendlyMessage = "Terjadi kesalahan saat berkomunikasi dengan Shopee. Silakan coba lagi nanti.";
            break;
    }
    return $friendlyMessage . "\n\nPesan Teknis: [{$errorCode}] {$originalMessage}";
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit();
}

$shopeeService = new ShopeeApiService();
if (!$shopeeService->isConnected()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated with Shopee']);
    exit();
}

$item_id = (int)($_POST['item_id'] ?? 0);
$new_price = (float)($_POST['new_price'] ?? 0);
$model_id = (int)($_POST['model_id'] ?? 0);

if (!$item_id || $new_price <= 0) {
    http_response_code(400);
    $message = 'ID Produk dan Harga (harus lebih dari 0) wajib diisi.';
    if (!$item_id) $message = 'ID Produk tidak valid.';
    if ($new_price <= 0) $message = 'Harga baru harus lebih besar dari 0.';
    
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

$response = $shopeeService->updatePrice($item_id, $new_price, $model_id);

if (isset($response['error']) && $response['error']) {
    http_response_code(400);
    $friendlyMessage = getFriendlyErrorMessage($response['error'], $response['message']);
    echo json_encode([
        'success' => false,
        'message' => $friendlyMessage
    ]);
} else {
    echo json_encode([
        'success' => true,
        'new_price' => $new_price,
        'message' => "Harga untuk Item ID {$item_id} berhasil diperbarui."
    ]);
}