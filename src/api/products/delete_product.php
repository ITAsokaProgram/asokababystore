<?php

require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: DELETE");

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

// Handle both DELETE and POST methods
$method = $_SERVER['REQUEST_METHOD'];
$id = null;

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
}

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Product ID is required']);
    exit;
}

try {
    $conn->autocommit(false);
    $conn->begin_transaction();
    
    // Check if product exists
    $checkSql = "SELECT id FROM product_online WHERE id = ? LIMIT 1";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param('i', $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Product not found']);
        exit;
    }
    
    // Delete the product
    $deleteSql = "DELETE FROM product_online WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param('i', $id);
    $deleteStmt->execute();
    
    if ($deleteStmt->affected_rows > 0) {
        $conn->commit();
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
    } else {
        $conn->rollback();
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Failed to delete product']);
    }
    
} catch (Exception $e) {
    if (isset($conn)) $conn->rollback();
    error_log('Error deleting product: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal Server Error']);
}
