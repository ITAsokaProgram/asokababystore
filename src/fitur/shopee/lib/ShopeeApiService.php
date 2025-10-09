<?php
class ShopeeApiService {
    private $partner_id;
    private $partner_key;
    private $host;
    private $access_token;
    private $shop_id;

    public function __construct() {
        $env = parse_ini_file(__DIR__ . '/../../../../.env');
        $this->partner_id = (int)$env['SHOPEE_PARTNER_ID'];
        $this->partner_key = trim($env['SHOPEE_PARTNER_KEY']);
        $this->host = "https://openplatform.sandbox.test-stable.shopee.sg";

        if (isset($_SESSION['access_token']) && isset($_SESSION['shop_id'])) {
            $this->access_token = $_SESSION['access_token'];
            $this->shop_id = (int)$_SESSION['shop_id'];
        }
    }

    public function isConnected() {
        return !empty($this->access_token) && !empty($this->shop_id);
    }

    public function disconnect() {
        unset($_SESSION['access_token'], $_SESSION['shop_id']);
    }

    public function getAuthUrl($redirect_uri) {
        $path = "/api/v2/shop/auth_partner";
        $timestamp = time();
        $base_string = sprintf("%s%s%s", $this->partner_id, $path, $timestamp);
        $sign = hash_hmac('sha256', $base_string, $this->partner_key);
        return sprintf("%s%s?partner_id=%s&redirect=%s&timestamp=%s&sign=%s", 
            $this->host, $path, $this->partner_id, urlencode($redirect_uri), $timestamp, $sign);
    }

    public function handleOAuthCallback($code, $shop_id) {
        $path = "/api/v2/auth/token/get";
        $timestamp = time();
        $base_string = sprintf("%s%s%s", $this->partner_id, $path, $timestamp);
        $sign = hash_hmac('sha256', $base_string, $this->partner_key);

        $body = json_encode(["code" => $code, "shop_id" => $shop_id, "partner_id" => $this->partner_id]);
        $url = sprintf("%s%s?partner_id=%s&timestamp=%s&sign=%s", $this->host, $path, $this->partner_id, $timestamp, $sign);

        $response_data = $this->executeCurl($url, 'POST', $body);

        if (isset($response_data['access_token'])) {
            $_SESSION['access_token'] = $response_data['access_token'];
            $_SESSION['shop_id'] = $shop_id;
        }
        return $response_data;
    }
    
    public function call($path, $method = 'GET', $body = null) {
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
        curl_close($ch);
        return json_decode($response_str, true);
    }
    
    // --- Specific API Methods ---
    public function getProductList($params) {
        return $this->call("/api/v2/product/get_item_list", 'GET', $params);
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
            $merged_products[] = isset($detailed_info_map[$item_id]) ? array_merge($original_item, $detailed_info_map[$item_id]) : $original_item;
        }

        // Fetch model info
        foreach ($merged_products as $index => &$item) {
            if (!empty($item['has_model'])) {
                $model_response = $this->call("/api/v2/product/get_model_list", 'GET', ['item_id' => $item['item_id']]);
                if (isset($model_response['response']['model']) && !empty($model_response['response']['model'])) {
                    $item['models'] = $model_response['response']['model'];

                    // --- Logika baru untuk menghitung total stok dengan benar ---
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
}