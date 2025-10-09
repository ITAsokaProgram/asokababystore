<?php
session_start();
require_once __DIR__ . '/../../fitur/shopee/lib/ShopeeApiService.php';

header('Content-Type: application/json');

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
$new_stock = (int)($_POST['new_stock'] ?? 0);
$model_id = (int)($_POST['model_id'] ?? 0);

if (!$item_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Item ID is required']);
    exit();
}

$response = $shopeeService->updateStock($item_id, $new_stock, $model_id);

if (isset($response['error']) && $response['error']) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => "Error: [{$response['error']}] {$response['message']}"
    ]);
} else {
    echo json_encode([
        'success' => true,
        'new_stock' => $new_stock,
        'message' => "Stock updated successfully for Item ID {$item_id}."
    ]);
}