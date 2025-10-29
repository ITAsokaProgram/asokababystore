<?php
require_once __DIR__ . '/../../../../src/utils/Logger.php';
class ShopeeApiService {
    private $partner_id;
    private $partner_key;
    private $host;
    private $access_token;
    private $shop_id;
    private $logger; 
    public function __construct($logger = null) {
        if ($logger) {
            $this->logger = $logger;
        } else {
            $this->logger = new AppLogger('shopee_api-service');
        }
        $env = parse_ini_file(__DIR__ . '/../../../../.env');
        $this->partner_id = (int)$env['SHOPEE_PARTNER_ID'];
        $this->partner_key = trim($env['SHOPEE_PARTNER_KEY']);
        $this->host = trim($env['SHOPEE_HOST']);
        if (isset($_SESSION['access_token']) && isset($_SESSION['shop_id'])) {
            $this->access_token = $_SESSION['access_token'];
            $this->shop_id = (int)$_SESSION['shop_id'];
        } else {
            try {
                include __DIR__ . '/../../../../aa_kon_sett.php'; 
                if (isset($conn) && $conn) {
                    $stmt = $conn->prepare("SELECT access_token, shop_id FROM shopee_auth WHERE id = 1");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($row = $result->fetch_assoc()) {
                        if (!empty($row['access_token']) && !empty($row['shop_id'])) {
                            $this->logger->info("Memuat kredensial Shopee dari Database.");
                            $this->access_token = $row['access_token'];
                            $this->shop_id = (int)$row['shop_id'];
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
    public function isConnected() {
        return !empty($this->access_token) && !empty($this->shop_id);
    }
    public function disconnect() {
        unset($_SESSION['access_token'], $_SESSION['shop_id']);
        try {
            include __DIR__ . '/../../../../aa_kon_sett.php';
            if (isset($conn) && $conn) {
                $stmt = $conn->prepare("UPDATE shopee_auth SET access_token = NULL, shop_id = NULL WHERE id = 1");
                $stmt->execute();
                $stmt->close();
                $this->logger->info("Kredensial Shopee dihapus dari Database.");
            }
        } catch (Throwable $t) {
            $this->logger->error("Gagal menghapus kredensial Shopee dari DB: " . $t->getMessage());
        }
    }
    public function getAuthUrl($redirect_uri) {
        $path = "/api/v2/shop/auth_partner";
        $timestamp = time();
        $base_string = sprintf("%s%s%s", $this->partner_id, $path, $timestamp);
        $sign = hash_hmac('sha256', $base_string, $this->partner_key);
        $url = sprintf("%s%s?partner_id=%s&redirect=%s&timestamp=%s&sign=%s", 
            $this->host, $path, $this->partner_id, urlencode($redirect_uri), $timestamp, $sign);
        return $url;
    }
    public function handleOAuthCallback($code, $id, $is_main_account = false) {
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
                    $_SESSION['shop_id'] = (int)$response_data['shop_id_list'][0];
                    $shop_id_to_save = (int)$response_data['shop_id_list'][0]; 
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
                        $this->logger->info("Kredensial Shopee disimpan ke Database. Shop ID: $shop_id_to_save");
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
    public function call($path, $method = 'GET', $body = null) {
        if (!$this->isConnected()) {
            $this->logger->warning("API call attempted but not connected. Path: $path");
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
    private function executeCurl($url, $method = 'GET', $payload = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
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
            $this->logger->error("cURL Error: " . $curl_error);
            return ['error' => 'curl_error', 'message' => $curl_error];
        }
        $this->logger->info("cURL Raw Response: " . $response_str);
        $response_data = json_decode($response_str, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error("JSON Decode Error for response: " . $response_str);
        }
        if (isset($response_data['error']) && !empty($response_data['error'])) {
             $this->logger->warning("Shopee API Error. Path: $url, Error: " . $response_data['error'] . ", Message: " . ($response_data['message'] ?? 'No message'));
        }
        return $response_data;
    }
    public function getProductList($params) {
        return $this->call("/api/v2/product/get_item_list", 'GET', $params);
    }
    public function searchProductList($params) {
        return $this->call("/api/v2/product/search_item", 'GET', $params);
    }
    public function getDetailedProductInfo($product_list_response) {
        if (!isset($product_list_response['response']['item']) || empty($product_list_response['response']['item'])) {
            return [];
        }
        $item_ids = array_column($product_list_response['response']['item'], 'item_id');
        $detail_response = $this->call("/api/v2/product/get_item_base_info", 'GET', ['item_id_list' => implode(',', $item_ids)]);
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
        return $merged_products;
    }
    public function updateStock($item_id, $new_stock, $model_id = 0) {
        $body = [
            "item_id" => (int)$item_id,
            "stock_list" => [
                [
                    "model_id" => (int)$model_id,
                    "seller_stock" => [
                        [
                            "location_id" => "IDZ", 
                            "stock" => (int)$new_stock
                        ]
                    ]
                ]
            ]
        ];
        return $this->call("/api/v2/product/update_stock", 'POST', $body);
    }
    public function updatePrice($item_id, $new_price, $model_id = 0) {
        $body = [
            "item_id" => $item_id,
            "price_list" => [["model_id" => $model_id, "original_price" => $new_price]]
        ];
        return $this->call("/api/v2/product/update_price", 'POST', $body);
    }
    public function getOrderList($params) {
        return $this->call("/api/v2/order/get_order_list", 'GET', $params);
    }
    public function getOrderDetail($params) {
        return $this->call("/api/v2/order/get_order_detail", 'GET', $params);
    }
}