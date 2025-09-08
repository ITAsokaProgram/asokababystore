<?php

require_once __DIR__ . '/../../../aa_kon_sett.php';

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");

$token = $_COOKIE['token'] ?? null;
$guest = !$token;
$id = $_GET['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Product ID is required']);
    exit;
}

try {
    if($guest){
        $sql = "SELECT po.nama_produk,po.id,po.deskripsi,po.kategori,po.image_url,sb.qty,sb.kd_store,sb.harga_jual,ks.nm_store, ks.telp AS store_phone FROM product_online po
        LEFT JOIN s_barang sb ON sb.item_n = po.barcode
    LEFT JOIN kode_store ks ON ks.kd_store = sb.kd_store
    WHERE po.id = ? AND ks.nm_store NOT IN ('ASOKA TES PROGRAM', 'asoka training program') AND ks.kota NOT IN ('BANGKA','BELITUNG') AND ks.nm_alias NOT IN('MAYA')
    ORDER BY sb.harga_jual ASC";
        $stmt = $conn->prepare($sql);
        $conn->query("SET SESSION MAX_EXECUTION_TIME=30000");

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Product not found']);
            exit;
        }

        $product = $result->fetch_all(MYSQLI_ASSOC);
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $product]);
        } else{
            $sql = "SELECT po.nama_produk,po.id,po.deskripsi,po.kategori,po.image_url,sb.qty,sb.kd_store,sb.harga_jual,ks.nm_store,ks.telp AS store_phone FROM product_online po
        LEFT JOIN s_barang sb ON sb.item_n = po.barcode
    LEFT JOIN kode_store ks ON ks.kd_store = sb.kd_store
    WHERE po.id = ? AND ks.nm_store NOT IN ('ASOKA TES PROGRAM', 'asoka training program') AND ks.kota NOT IN ('BANGKA','BELITUNG') AND ks.nm_alias NOT IN('MAYA')
    ORDER BY sb.harga_jual ASC";
        $stmt = $conn->prepare($sql);
        $conn->query("SET SESSION MAX_EXECUTION_TIME=30000");

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Product not found']);
            exit;
        }

        $product = $result->fetch_all(MYSQLI_ASSOC);
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $product]);
        }
        
} catch (mysqli_sql_exception $e) {
    http_response_code(504);
    echo json_encode(['success' => false, 'error' => 'Request timeout ']);
    exit;
} catch (Exception $e) {
    error_log('Error fetching product detail: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal Server Error']);
}
