<?php
require_once __DIR__ . '/../../../../src/utils/Logger.php';
class ShopeeApiService
{
    private $partner_id;
    private $partner_key;
    private $host;
    private $access_token;
    private $shop_id;
    private $logger;
    public function __construct($logger = null)
    {
        if ($logger) {
            $this->logger = $logger;
        } else {
            $this->logger = new AppLogger('shopee_api-service.log');
        }
        $env = parse_ini_file(__DIR__ . '/../../../../.env');
        $this->partner_id = (int) $env['SHOPEE_PARTNER_ID'];
        $this->partner_key = trim($env['SHOPEE_PARTNER_KEY']);
        $this->host = trim($env['SHOPEE_HOST']);
        if (session_status() == PHP_SESSION_ACTIVE && isset($_SESSION['access_token']) && isset($_SESSION['shop_id'])) {
            $this->access_token = $_SESSION['access_token'];
            $this->shop_id = (int) $_SESSION['shop_id'];
        } else {
            try {
                include __DIR__ . '/../../../../aa_kon_sett.php';
                if (isset($conn) && $conn) {
                    $stmt = $conn->prepare("SELECT access_token, shop_id FROM shopee_auth WHERE id = 1");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($row = $result->fetch_assoc()) {
                        if (!empty($row['access_token']) && !empty($row['shop_id'])) {
                            $this->access_token = $row['access_token'];
                            $this->shop_id = (int) $row['shop_id'];
                            if (session_status() == PHP_SESSION_ACTIVE) {
                                $_SESSION['access_token'] = $this->access_token;
                                $_SESSION['shop_id'] = $this->shop_id;
                            }
                        }
                    }
                    $stmt->close();
                }
            } catch (Throwable $t) {
                $this->logger->error("Gagal memuat kredensial Shopee dari DB: " . $t->getMessage());
            }
        }
    }
    public function isConnected()
    {
        return !empty($this->access_token) && !empty($this->shop_id);
    }
    public function disconnect()
    {
        unset($_SESSION['access_token'], $_SESSION['shop_id']);
        try {
            include __DIR__ . '/../../../../aa_kon_sett.php';
            if (isset($conn) && $conn) {
                $stmt = $conn->prepare("UPDATE shopee_auth SET access_token = NULL, shop_id = NULL WHERE id = 1");
                $stmt->execute();
                $stmt->close();
            }
        } catch (Throwable $t) {
            $this->logger->error("Gagal menghapus kredensial Shopee dari DB: " . $t->getMessage());
        }
    }
    public function getAuthUrl($redirect_uri)
    {
        $path = "/api/v2/shop/auth_partner";
        $timestamp = time();
        $base_string = sprintf("%s%s%s", $this->partner_id, $path, $timestamp);
        $sign = hash_hmac('sha256', $base_string, $this->partner_key);
        $url = sprintf(
            "%s%s?partner_id=%s&redirect=%s&timestamp=%s&sign=%s",
            $this->host,
            $path,
            $this->partner_id,
            urlencode($redirect_uri),
            $timestamp,
            $sign
        );
        return $url;
    }
    public function handleOAuthCallback($code, $id, $is_main_account = false)
    {
        $path = "/api/v2/auth/token/get";
        $timestamp = time();
        $base_string = sprintf("%s%s%s", $this->partner_id, $path, $timestamp);
        $sign = hash_hmac('sha256', $base_string, $this->partner_key);
        $body_payload = [
            "code" => $code,
            "partner_id" => $this->partner_id
        ];
        if ($is_main_account) {
            $body_payload['main_account_id'] = $id;
        } else {
            $body_payload['shop_id'] = $id;
        }
        $body = json_encode($body_payload);
        $url = sprintf("%s%s?partner_id=%s&timestamp=%s&sign=%s", $this->host, $path, $this->partner_id, $timestamp, $sign);
        $response_data = $this->executeCurl($url, 'POST', $body);
        if (isset($response_data['access_token'])) {
            $_SESSION['access_token'] = $response_data['access_token'];
            $shop_id_to_save = 0;
            if ($is_main_account) {
                if (isset($response_data['shop_id_list']) && !empty($response_data['shop_id_list'])) {
                    $_SESSION['shop_id'] = (int) $response_data['shop_id_list'][0];
                    $shop_id_to_save = (int) $response_data['shop_id_list'][0];
                }
            } else {
                $_SESSION['shop_id'] = $id;
                $shop_id_to_save = $id;
            }
            if ($shop_id_to_save > 0) {
                try {
                    include __DIR__ . '/../../../../aa_kon_sett.php';
                    if (isset($conn) && $conn) {
                        $stmt = $conn->prepare("UPDATE shopee_auth SET access_token = ?, shop_id = ? WHERE id = 1");
                        $stmt->bind_param("si", $response_data['access_token'], $shop_id_to_save);
                        $stmt->execute();
                        $stmt->close();
                    }
                } catch (Throwable $t) {
                    $this->logger->error("Gagal menyimpan kredensial Shopee ke DB: " . $t->getMessage());
                }
            }
        } else {
            $this->logger->error("OAuth Callback failed. Response: " . json_encode($response_data));
        }
        return $response_data;
    }
    public function call($path, $method = 'GET', $body = null)
    {
        if (!$this->isConnected()) {
            return ['error' => 'auth_error', 'message' => 'User not authenticated.'];
        }
        $timestamp = time();
        $base_string = sprintf("%s%s%s%s%s", $this->partner_id, $path, $timestamp, $this->access_token, $this->shop_id);
        $sign = hash_hmac('sha256', $base_string, $this->partner_key);
        $common_params = http_build_query([
            'partner_id' => $this->partner_id,
            'timestamp' => $timestamp,
            'access_token' => $this->access_token,
            'shop_id' => $this->shop_id,
            'sign' => $sign,
        ]);
        $url = $this->host . $path . '?' . $common_params;
        if ($method === 'GET' && !empty($body)) {
            $url .= '&' . http_build_query($body);
            return $this->executeCurl($url);
        }
        return $this->executeCurl($url, $method, json_encode($body));
    }
    private function executeCurl($url, $method = 'GET', $payload = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($payload) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            }
        }
        $response_str = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);
        if ($curl_error) {
            $this->logger->error("❌ cURL Error: " . $curl_error);
            return ['error' => 'curl_error', 'message' => $curl_error];
        }
        $response_data = json_decode($response_str, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error("❌ JSON Decode Error. Response: " . $response_str);
        }
        if (isset($response_data['error']) && !empty($response_data['error'])) {
            $this->logger->warning("⚠️ Shopee API Error. Path: $url, Error: " . $response_data['error'] . ", Message: " . ($response_data['message'] ?? 'No message'));
        }
        return $response_data;
    }
    public function getProductList($params)
    {
        return $this->call("/api/v2/product/get_item_list", 'GET', $params);
    }
    public function searchProductList($params)
    {
        return $this->call("/api/v2/product/search_item", 'GET', $params);
    }
    public function getDetailedProductInfo($product_list_response)
    {
        if (!isset($product_list_response['response']['item']) || empty($product_list_response['response']['item'])) {
            return [];
        }
        $item_ids = array_column($product_list_response['response']['item'], 'item_id');
        $detail_response = $this->call("/api/v2/product/get_item_base_info", 'GET', ['item_id_list' => implode(',', $item_ids)]);
        if (isset($detail_response['error']) && $detail_response['error']) {
            $error_message = $detail_response['message'] ?? 'Unknown error';
            $this->logger->error("❌ Gagal get_item_base_info: " . $error_message);
            throw new Exception("Gagal mengambil detail (get_item_base_info): " . $error_message);
        }
        $detailed_info_map = [];
        if (isset($detail_response['response']['item_list'])) {
            foreach ($detail_response['response']['item_list'] as $item) {
                $detailed_info_map[$item['item_id']] = $item;
            }
        }
        $merged_products = [];
        foreach ($product_list_response['response']['item'] as $original_item) {
            $item_id = $original_item['item_id'];
            $merged_products[] = isset($detailed_info_map[$item_id]) ? array_merge($detailed_info_map[$item_id], $original_item) : $original_item;
        }
        foreach ($merged_products as $index => &$item) {
            if (!empty($item['has_model'])) {
                $model_response = $this->call("/api/v2/product/get_model_list", 'GET', ['item_id' => $item['item_id']]);
                if (isset($model_response['error']) && $model_response['error']) {
                    $error_message = $model_response['message'] ?? 'Unknown error';
                    $this->logger->warning("⚠️ Gagal get_model_list untuk item_id " . $item['item_id'] . ": " . $error_message . ". Melanjutkan tanpa data model.");
                    continue;
                }
                if (isset($model_response['response']['model']) && !empty($model_response['response']['model'])) {
                    $item['models'] = $model_response['response']['model'];
                    $total_stock = 0;
                    foreach ($item['models'] as $model) {
                        $stock = $model['stock_info_v2']['summary_info']['total_available_stock']
                            ?? $model['stock_info'][0]['seller_stock']
                            ?? 0;
                        $total_stock += $stock;
                    }
                    $item['calculated_total_stock'] = $total_stock;
                }
            }
        }
        usleep(200000);
        return $merged_products;
    }
    public function updateStock($item_id, $new_stock, $model_id = 0)
    {
        $body = [
            "item_id" => (int) $item_id,
            "stock_list" => [
                [
                    "model_id" => (int) $model_id,
                    "seller_stock" => [
                        [
                            "location_id" => "IDZ",
                            "stock" => (int) $new_stock
                        ]
                    ]
                ]
            ]
        ];
        return $this->call("/api/v2/product/update_stock", 'POST', $body);
    }
    public function updatePrice($item_id, $new_price, $model_id = 0)
    {
        $body = [
            "item_id" => $item_id,
            "price_list" => [["model_id" => $model_id, "original_price" => $new_price]]
        ];
        return $this->call("/api/v2/product/update_price", 'POST', $body);
    }
    public function getOrderList($params)
    {
        return $this->call("/api/v2/order/get_order_list", 'GET', $params);
    }
    public function getOrderDetail($params)
    {
        return $this->call("/api/v2/order/get_order_detail", 'GET', $params);
    }
    public function syncAllProductsToDatabase($conn, $force = false)
    {
        $lockFile = sys_get_temp_dir() . '/shopee_db_sync.lock';
        if (!$force) {
            if (file_exists($lockFile)) {
                $last_update = filemtime($lockFile);
                if ((time() - $last_update) < 1800) {
                    $this->logger->warning("[SyncDB] Skip: Proses lain sedang berjalan (Lock file exists).");
                    throw new Exception("Sinkronisasi sedang berjalan. Harap tunggu atau gunakan 'Force Sync'.");
                } else {
                    @unlink($lockFile);
                }
            }
        } else {
            if (file_exists($lockFile)) {
                @unlink($lockFile);
                $this->logger->info("[SyncDB] Force Sync: Lock file dihapus.");
            }
        }
        touch($lockFile);
        $this->logger->info("[SyncDB] Memulai sinkronisasi Shopee ke Database MySQL...");
        try {
            if (!$this->isConnected()) {
                throw new Exception("Not authenticated with Shopee");
            }
            $offset = 0;
            $page_size = 50;
            $total_processed = 0;
            $has_next_page = true;
            $sql = "INSERT INTO s_shopee_produk 
                    (kode_produk, nama_produk, kode_variasi, nama_variasi, sku_induk, sku, barcode, harga, stok, image_url) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    nama_produk = VALUES(nama_produk),
                    nama_variasi = VALUES(nama_variasi),
                    sku_induk = VALUES(sku_induk),
                    sku = VALUES(sku),
                    barcode = VALUES(barcode),
                    harga = VALUES(harga),
                    stok = VALUES(stok),
                    image_url = VALUES(image_url),
                    updated_at = NOW()";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Gagal prepare statement: " . $conn->error);
            }
            while ($has_next_page) {
                $api_params = [
                    'offset' => $offset,
                    'page_size' => $page_size,
                    'item_status' => 'NORMAL'
                ];
                $list_response = $this->getProductList($api_params);
                if (isset($list_response['error']) && $list_response['error']) {
                    throw new Exception("Shopee API Error: " . ($list_response['message'] ?? 'Unknown'));
                }
                if (empty($list_response['response']['item'])) {
                    break;
                }
                $detailed_items = $this->getDetailedProductInfo($list_response);
                if (empty($detailed_items)) {
                    break;
                }
                $conn->begin_transaction();
                foreach ($detailed_items as $item) {
                    $kode_produk = $item['item_id'];
                    $nama_produk = $item['item_name'];
                    $sku_induk = $item['item_sku'] ?? '';
                    $image_url = $item['image']['image_url_list'][0] ?? '';
                    if (isset($item['has_model']) && $item['has_model'] === true && !empty($item['models'])) {
                        foreach ($item['models'] as $model) {
                            $kode_variasi = $model['model_id'];
                            $nama_variasi = $model['model_name'];
                            $sku = $model['model_sku'] ?? '';
                            $barcode = (empty($sku)) ? $sku_induk : $sku;
                            $harga = $model['price_info'][0]['original_price'] ?? 0;
                            $stok = $model['stock_info_v2']['summary_info']['total_available_stock']
                                ?? $model['stock_info'][0]['seller_stock'] ?? 0;
                            $stmt->bind_param(
                                "isissssdis",
                                $kode_produk,
                                $nama_produk,
                                $kode_variasi,
                                $nama_variasi,
                                $sku_induk,
                                $sku,
                                $barcode,
                                $harga,
                                $stok,
                                $image_url
                            );
                            $stmt->execute();
                            $total_processed++;
                        }
                    } else {
                        $kode_variasi = 0;
                        $nama_variasi = null;
                        $sku = $item['item_sku'] ?? '';
                        $barcode = (empty($sku)) ? $sku_induk : $sku;
                        $harga = $item['price_info'][0]['original_price'] ?? 0;
                        $stok = $item['stock_info_v2']['summary_info']['total_available_stock']
                            ?? $item['stock_info'][0]['seller_stock'] ?? 0;
                        $stmt->bind_param(
                            "isissssdis",
                            $kode_produk,
                            $nama_produk,
                            $kode_variasi,
                            $nama_variasi,
                            $sku_induk,
                            $sku,
                            $barcode,
                            $harga,
                            $stok,
                            $image_url
                        );
                        $stmt->execute();
                        $total_processed++;
                    }
                }
                $conn->commit();
                $has_next_page = $list_response['response']['has_next_page'];
                $offset = $list_response['response']['next_offset'];
                usleep(500000);
            }
            $stmt->close();
            $this->logger->info("[SyncDB] Menjalankan update harga beli berdasarkan Barcode...");
            $sql_update_receipt = "
                UPDATE s_shopee_produk sp
                    INNER JOIN (
                        SELECT r1.barcode, 
                            (r1.netto + r1.ppn) AS total_beli_receipt
                        FROM receipt r1
                        INNER JOIN (
                            SELECT barcode, MAX(tgl_tiba) AS max_tgl
                            FROM receipt 
                            WHERE kd_store = '3190'
                            GROUP BY barcode
                        ) r2 ON r1.barcode = r2.barcode AND r1.tgl_tiba = r2.max_tgl
                        WHERE r1.kd_store = '3190'
                        GROUP BY r1.barcode 
                    ) src ON sp.barcode = src.barcode
                    LEFT JOIN s_stok_ol so ON sp.barcode = so.item_n AND so.KD_STORE = '9998'
                    SET 
                        sp.hb_old = CASE 
                            WHEN sp.harga_beli = src.total_beli_receipt THEN sp.hb_old
                            WHEN sp.harga_beli IS NULL OR sp.harga_beli = 0 THEN src.total_beli_receipt
                            ELSE sp.harga_beli 
                        END,
                        sp.harga_beli = src.total_beli_receipt,
                        sp.keterangan = 'Dari Receipt (Last Data)'
                    WHERE so.item_n IS NULL;
            ";
            $conn->query($sql_update_receipt);
            $sql_update_stok_ol = "
                UPDATE s_shopee_produk sp
                INNER JOIN s_stok_ol so ON sp.barcode = so.item_n
                SET 
                    sp.hb_old = sp.harga_beli,
                    sp.harga_beli = so.hrg_beli,
                    sp.keterangan = 'Dari Stok OL'
                WHERE 
                    so.KD_STORE = '9998'
            ";
            $conn->query($sql_update_stok_ol);
            $this->logger->info("[SyncDB] Update harga beli selesai.");
            @unlink($lockFile);
            $this->logger->info("[SyncDB] Selesai. Total item/variasi tersimpan: $total_processed");
            return $total_processed;
        } catch (Throwable $t) {
            if (isset($conn))
                $conn->rollback();
            $this->logger->error("[SyncDB] Error: " . $t->getMessage());
            throw $t;
        }
    }
}
?>