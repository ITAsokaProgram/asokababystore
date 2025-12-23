<?php
session_start();
include '../../../aa_kon_sett.php';
require_once __DIR__ . '/../../fitur/shopee/lib/ShopeeApiService.php';
require_once __DIR__ . '/../../utils/Logger.php';
header('Content-Type: application/json');
$logger = new AppLogger('shopee_price_update.log');
function getFriendlyErrorMessage($errorCode, $originalMessage)
{
    $friendlyMessage = '';
    switch ($errorCode) {
        case 'product.error_update_price_fail':
            $friendlyMessage = "Gagal memperbarui harga. Shopee kemungkinan memiliki batasan pada perubahan harga yang drastis.";
            break;
        default:
            $friendlyMessage = "Terjadi kesalahan saat berkomunikasi dengan Shopee.";
            break;
    }
    return $friendlyMessage . "\n\nPesan Teknis: [{$errorCode}] {$originalMessage}";
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit();
}
$logger->info("Update Price Request: " . json_encode($_POST));
$shopeeService = new ShopeeApiService();
if (!$shopeeService->isConnected()) {
    $logger->error("Shopee not connected");
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated with Shopee']);
    exit();
}
$item_id = (int) ($_POST['item_id'] ?? 0);
$new_price = (float) ($_POST['new_price'] ?? 0);
$model_id = (int) ($_POST['model_id'] ?? 0);
$from_margin = (int) ($_POST['from_margin'] ?? 0);
if (!$item_id || $new_price <= 0) {
    $logger->warning("Invalid input: item_id=$item_id, price=$new_price");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Input tidak valid']);
    exit();
}
$response = $shopeeService->updatePrice($item_id, $new_price, $model_id);
if (isset($response['error']) && $response['error']) {
    $logger->error("Shopee API Error: " . $response['message']);
    http_response_code(400);
    $friendlyMessage = getFriendlyErrorMessage($response['error'], $response['message']);
    echo json_encode(['success' => false, 'message' => $friendlyMessage]);
} else {
    $db_updated = false;
    if (isset($conn) && $conn instanceof mysqli) {
        if ($from_margin === 1) {
            $logger->info("Update via Margin: Syncing hb_old = harga_beli for Item ID $item_id");

            $sql = "UPDATE s_shopee_produk 
            SET harga = ?, 
                hb_old = harga_beli, 
                updated_at = NOW() 
            WHERE kode_produk = ? AND kode_variasi = ?";
        } else {
            $sql = "UPDATE s_shopee_produk 
            SET harga = ?, 
                updated_at = NOW() 
            WHERE kode_produk = ? AND kode_variasi = ?";
        }
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("dii", $new_price, $item_id, $model_id);
            $stmt->execute();
            if ($stmt->affected_rows >= 0) {
                $db_updated = true;
                $logger->success("DB Updated successfully. Rows affected: " . $stmt->affected_rows);
            } else {
                $logger->error("DB Update failed: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $logger->critical("SQL Prepare failed: " . $conn->error);
        }
    }
    echo json_encode([
        'success' => true,
        'new_price' => $new_price,
        'message' => "Harga berhasil diperbarui" . ($db_updated ? " & Local DB Updated" : ""),
        'db_updated' => $db_updated
    ]);
}