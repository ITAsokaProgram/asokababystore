<?php

require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");
$token = $_COOKIE['admin-token'] ?? null;
$guest = !$token;
if ($guest) {
    try {
        $sql = "SELECT po.id, po.nama_produk, po.deskripsi, po.kategori, po.image_url, po.tanggal_upload,  sb.qty, po.barcode, sb.kd_store, sb.harga_jual, ks.nm_alias as cabang FROM product_online po
  LEFT JOIN s_barang sb ON sb.item_n = po.barcode
  JOIN kode_store ks ON po.kd_store = ks.kd_store
  WHERE po.kd_store = sb.kd_store
  ORDER BY tanggal_upload DESC LIMIT 10";
        $stmt = $conn->prepare($sql);
        $conn->query("SET SESSION MAX_EXECUTION_TIME=30000");
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        if (!$products) {
            http_response_code(204);
            echo json_encode(['success' => false, 'error' => 'No products found']);
            exit;
        }
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $products]);
    } catch (mysqli_sql_exception $e) {
        http_response_code(504);
        echo json_encode(['success' => false, 'error' => 'Request timeout ']);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Internal Server Error']);
        exit;
    }
} else {
    $verify = verify_token($token);
    try {
        $sql = "SELECT po.id, po.nama_produk, po.deskripsi, po.kategori, po.image_url, po.tanggal_upload,  sb.qty, po.barcode, sb.kd_store, sb.harga_jual, ks.nm_alias as cabang FROM product_online po
  LEFT JOIN s_barang sb ON sb.item_n = po.barcode
  JOIN kode_store ks ON po.kd_store = ks.kd_store
  WHERE po.kd_store = sb.kd_store
  ORDER BY tanggal_upload DESC";
        $stmt = $conn->prepare($sql);
        $conn->query("SET SESSION MAX_EXECUTION_TIME=30000");
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        if (!$products) {
            http_response_code(204);
            echo json_encode(['success' => false, 'error' => 'No products found']);
            exit;
        }
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $products]);
    } catch (mysqli_sql_exception $e) {
        http_response_code(504);
        echo json_encode(['success' => false, 'error' => 'Request timeout ']);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Internal Server Error']);
        exit;
    }
}
