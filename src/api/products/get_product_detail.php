<?php

require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");

$token = $_COOKIE['admin_token'] ?? null;
if (!$token) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$verify = verify_token($token);
if (!$verify) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid token']);
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Product ID is required']);
    exit;
}

try {
    $sql = "SELECT id, barcode, plu, nama_produk, deskripsi, kategori, image_url, tanggal_upload, kd_store as cabang FROM product_online WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Product not found']);
        exit;
    }
    
    $product = $result->fetch_assoc();
    http_response_code(200);
    echo json_encode(['success' => true, 'data' => $product]);
    
} catch (Exception $e) {
    error_log('Error fetching product detail: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal Server Error']);
}
