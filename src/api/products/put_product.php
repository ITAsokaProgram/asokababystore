<?php

require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: POST, PUT");

$token = $_COOKIE['customer_token'] ?? null;
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

// Accept both POST and PUT for updates
$method = $_SERVER['REQUEST_METHOD'];
$input = [];
if ($method === 'PUT') {
    parse_str(file_get_contents('php://input'), $input);
} else {
    // POST
    $input = $_POST;
}

$id = $input['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Product ID is required']);
    exit;
}

// Fields allowed to update
$barcode = $input['barcode'] ?? null;
$plu = $input['plu'] ?? null;
$nama_produk = $input['nama_produk'] ?? null;
$deskripsi = $input['deskripsi'] ?? null;
$kategori = $input['kategori'] ?? null;
$kd_store = $input['kd_store'] ?? ($input['cabang'] ?? null);

try {
    // Ensure product exists
    $checkSql = "SELECT id FROM product_online WHERE id = ? LIMIT 1";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param('i', $id);
    $checkStmt->execute();
    $checkRes = $checkStmt->get_result();
    if ($checkRes->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Product not found']);
        exit;
    }

    // Build update statement dynamically for provided fields
    $fields = [];
    $types = '';
    $values = [];

    if ($barcode !== null) { $fields[] = 'barcode = ?'; $types .= 's'; $values[] = $barcode; }
    if ($plu !== null) { $fields[] = 'plu = ?'; $types .= 's'; $values[] = $plu; }
    if ($nama_produk !== null) { $fields[] = 'nama_produk = ?'; $types .= 's'; $values[] = $nama_produk; }
    if ($deskripsi !== null) { $fields[] = 'deskripsi = ?'; $types .= 's'; $values[] = $deskripsi; }
    if ($kategori !== null) { $fields[] = 'kategori = ?'; $types .= 's'; $values[] = $kategori; }
    if ($kd_store !== null) { $fields[] = 'kd_store = ?'; $types .= 's'; $values[] = $kd_store; }

    if (count($fields) === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No fields to update']);
        exit;
    }

    $sql = 'UPDATE product_online SET ' . implode(', ', $fields) . ' WHERE id = ?';
    $types .= 'i'; // id type
    $values[] = $id;

    $stmt = $conn->prepare($sql);
    // bind_param requires variables
    $bind_names[] = $types;
    for ($i=0; $i<count($values); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $values[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);

    $stmt->execute();
    if ($stmt->affected_rows >= 0) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Failed to update product']);
    }
    $stmt->close();
} catch (Exception $e) {
    error_log('Error updating product: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal Server Error']);
}
$conn->close();
