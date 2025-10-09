<?php
session_start();

$env = parse_ini_file('.env');

$partner_id = $env['SHOPEE_PARTNER_ID'];
$partner_key = trim($env['SHOPEE_PARTNER_KEY']);
$host = "https://openplatform.sandbox.test-stable.shopee.sg";
$redirect_uri = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

function callShopeeAPI($path, $method = 'GET', $body = null) {
    global $host, $partner_id, $partner_key;

    if (!isset($_SESSION['access_token']) || !isset($_SESSION['shop_id'])) {
        return ['error' => 'auth_error', 'message' => 'User not authenticated.'];
    }

    $access_token = $_SESSION['access_token'];
    $shop_id = $_SESSION['shop_id'];
    $timestamp = time();

    $common_params = [
        'partner_id' => (int)$partner_id,
        'timestamp' => $timestamp,
        'access_token' => $access_token,
        'shop_id' => (int)$shop_id,
    ];

    $base_string = sprintf("%s%s%s%s%s", $partner_id, $path, $timestamp, $access_token, $shop_id);
    $sign = hash_hmac('sha256', $base_string, $partner_key);
    $common_params['sign'] = $sign;

    $api_url = $host . $path . '?' . http_build_query($common_params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    } else { // GET Request
        $get_params = is_array($body) ? $body : [];
        $full_get_url = $api_url . '&' . http_build_query($get_params);
        curl_setopt($ch, CURLOPT_URL, $full_get_url);
    }
    
    $response_str = curl_exec($ch);
    curl_close($ch);

    return json_decode($response_str, true);
}

function getPriceRange($models) {
    if (empty($models)) {
        return null;
    }
    
    $prices = [];
    foreach ($models as $model) {
        if (isset($model['price_info'][0]['original_price'])) {
            $prices[] = $model['price_info'][0]['original_price'];
        }
    }
    
    if (empty($prices)) {
        return null;
    }
    
    $minPrice = min($prices);
    $maxPrice = max($prices);
    
    if ($minPrice == $maxPrice) {
        return number_format($minPrice, 0, ',', '.');
    } else {
        return number_format($minPrice, 0, ',', '.') . ' - ' . number_format($maxPrice, 0, ',', '.');
    }
}

if (isset($_GET['code']) && isset($_GET['shop_id'])) {
    $code = $_GET['code'];
    $shop_id = (int)$_GET['shop_id'];
    
    $path = "/api/v2/auth/token/get";
    $timestamp = time();
    $base_string = sprintf("%s%s%s", $partner_id, $path, $timestamp);
    $sign = hash_hmac('sha256', $base_string, $partner_key);

    $body = json_encode([ "code" => $code, "shop_id" => $shop_id, "partner_id" => (int)$partner_id ]);
    $token_url = sprintf("%s%s?partner_id=%s&timestamp=%s&sign=%s", $host, $path, $partner_id, $timestamp, $sign);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response_str = curl_exec($ch);
    curl_close($ch);
    $response_data = json_decode($response_str, true);

    if (isset($response_data['access_token'])) {
        $_SESSION['access_token'] = $response_data['access_token'];
        $_SESSION['shop_id'] = $shop_id;
    }
    
    header('Location: ' . $redirect_uri);
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'disconnect') {
    session_destroy();
    header('Location: ' . $redirect_uri);
    exit();
}

if (isset($_SESSION['access_token']) && isset($_SESSION['shop_id'])) {

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id']) && isset($_POST['new_stock'])) {
        $item_id = (int)$_POST['item_id'];
        $new_stock = (int)$_POST['new_stock'];
        $model_id = isset($_POST['model_id']) ? (int)$_POST['model_id'] : 0;
        $location_id = "IDZ"; 

        $path = "/api/v2/product/update_stock";
        $body = [
            "item_id" => $item_id,
            "stock_list" => [
                [
                    "model_id" => $model_id,
                    "seller_stock" => [
                        [
                            "location_id" => $location_id,
                            "stock" => $new_stock
                        ]
                    ]
                ]
            ]
        ];
        
        $update_response = callShopeeAPI($path, 'POST', $body);
        
        header('Content-Type: application/json');

        if (isset($update_response['error']) && $update_response['error']) {
            $error_code = $update_response['error'] ?? 'UNKNOWN';
            $error_message = $update_response['message'] ?? 'No additional message.';
            echo json_encode([
                'success' => false,
                'message' => "Error: [" . $error_code . "] " . $error_message
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'new_stock' => $new_stock,
                'message' => "Successfully updated stock for Item ID {$item_id} (Model ID: {$model_id}) to {$new_stock}."
            ]);
        }

        exit();
    }

    // Handler untuk UPDATE PRICE
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id']) && isset($_POST['new_price'])) {
        $item_id = (int)$_POST['item_id'];
        $new_price = (float)$_POST['new_price'];
        $model_id = isset($_POST['model_id']) ? (int)$_POST['model_id'] : 0;

        $path = "/api/v2/product/update_price";
        $body = [
            "item_id" => $item_id,
            "price_list" => [
                [
                    "model_id" => $model_id,
                    "original_price" => $new_price
                ]
            ]
        ];
        
        $update_response = callShopeeAPI($path, 'POST', $body);
        
        header('Content-Type: application/json');

        if (isset($update_response['error']) && $update_response['error']) {
            $error_code = $update_response['error'] ?? 'UNKNOWN';
            $error_message = $update_response['message'] ?? 'No additional message.';
            echo json_encode([
                'success' => false,
                'message' => "Error: [" . $error_code . "] " . $error_message
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'new_price' => $new_price,
                'message' => "Successfully updated price for Item ID {$item_id} (Model ID: {$model_id}) to Rp " . number_format($new_price, 0, ',', '.')
            ]);
        }

        exit();
    }

    // --- FETCH PRODUCT LIST (BASIC INFO) ---
    $path = "/api/v2/product/get_item_list";
    $params = [ 'offset' => 0, 'page_size' => 20, 'item_status' => 'NORMAL' ];
    $product_list_response = callShopeeAPI($path, 'GET', $params);

    // --- FETCH DETAILED PRODUCT INFO AND MERGE ---
    $detailed_products = [];
    if (isset($product_list_response['response']['item']) && !empty($product_list_response['response']['item'])) {
        $item_ids = array_column($product_list_response['response']['item'], 'item_id');

        $detail_path = "/api/v2/product/get_item_base_info";
        $detail_params = [ 'item_id_list' => implode(',', $item_ids) ];
        $detail_response = callShopeeAPI($detail_path, 'GET', $detail_params);

        $detailed_info_map = [];
        if (isset($detail_response['response']['item_list'])) {
            foreach ($detail_response['response']['item_list'] as $detail_item) {
                $detailed_info_map[$detail_item['item_id']] = $detail_item;
            }
        }

        foreach ($product_list_response['response']['item'] as $original_item) {
            $item_id = $original_item['item_id'];
            if (isset($detailed_info_map[$item_id])) {
                $detailed_products[] = array_merge($original_item, $detailed_info_map[$item_id]);
            } else {
                $detailed_products[] = $original_item;
            }
        }
    }
    
    // --- FETCH MODEL INFO FOR VARIANT PRODUCTS ---
    foreach ($detailed_products as $index => $item) {
        if (isset($item['has_model']) && $item['has_model'] === true) {
            $model_path = "/api/v2/product/get_model_list";
            $model_params = ['item_id' => $item['item_id']];
            $model_response = callShopeeAPI($model_path, 'GET', $model_params);

            if (isset($model_response['response']['model']) && !empty($model_response['response']['model'])) {
                $total_stock = 0;
                foreach ($model_response['response']['model'] as $model) {
                    $stock = $model['stock_info_v2']['summary_info']['total_available_stock'] ?? $model['stock_info'][0]['seller_stock'] ?? 0;
                    $total_stock += $stock;
                }
                $detailed_products[$index]['calculated_total_stock'] = $total_stock;
                $detailed_products[$index]['models'] = $model_response['response']['model']; 
            }
        }
    }
} else {
    $path = "/api/v2/shop/auth_partner";
    $timestamp = time();
    $base_string = sprintf("%s%s%s", $partner_id, $path, $timestamp);
    $sign = hash_hmac('sha256', $base_string, $partner_key);
    $auth_url = sprintf("%s%s?partner_id=%s&redirect=%s&timestamp=%s&sign=%s", 
        $host, $path, $partner_id, urlencode($redirect_uri), $timestamp, $sign);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopee Integration App</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center font-sans">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-4xl mx-4 my-8">
        <div class="flex items-center justify-center mb-6">
            <img src="public/images/logo/shopee.png" alt="Shopee Logo" class="h-10 w-10">
            <h1 class="text-2xl font-bold text-gray-800 ml-4">Integration Dashboard</h1>
        </div>
        
        <?php if (isset($_SESSION['access_token']) && isset($_SESSION['shop_id'])): ?>
             <div class="text-center">
                <a href="?action=disconnect" class="mt-8 inline-block w-full text-center bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-6 rounded-lg transition">Disconnect</a>
            </div>
            <div class="mt-10 pt-6 border-t">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Product List</h2>

                <?php if (isset($_SESSION['update_message'])): ?>
                    <div class="p-4 mb-4 text-sm rounded-lg <?php echo $_SESSION['update_status'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>" role="alert">
                        <?php echo $_SESSION['update_message']; ?>
                    </div>
                    <?php 
                        unset($_SESSION['update_message']);
                        unset($_SESSION['update_status']);
                    ?>
                <?php endif; ?>

                <?php if (!empty($detailed_products)): ?>
                    <div class="space-y-4">
                        <?php foreach ($detailed_products as $item): ?>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="flex items-center gap-4 w-full">
                                    <img src="<?php echo htmlspecialchars($item['image']['image_url_list'][0] ?? 'https://placehold.co/80x80/e2e8f0/e2e8f0'); ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>" class="w-20 h-20 object-cover rounded-md bg-gray-200 flex-shrink-0">
                                    <div class="text-left flex-grow">
                                        <p class="font-bold text-gray-800"><?php echo htmlspecialchars($item['item_name']); ?></p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            ID: <?php echo htmlspecialchars($item['item_id']); ?> | SKU: <strong><?php echo htmlspecialchars($item['item_sku'] ?? 'N/A'); ?></strong>
                                        </p>
                                        <p class="text-sm text-gray-700 font-semibold mt-1">
                                            Price: Rp 
                                            <span id="price-display-<?php echo $item['item_id']; ?>">
                                                <?php 
                                                // Jika produk punya model/variasi, tampilkan range harga
                                                if (isset($item['has_model']) && $item['has_model'] === true && !empty($item['models'])) {
                                                    echo getPriceRange($item['models']);
                                                } else {
                                                    // Jika tidak punya variasi, tampilkan harga normal
                                                    echo number_format($item['price_info'][0]['original_price'] ?? 0, 0, ',', '.');
                                                }
                                                ?>
                                            </span>
                                        </p>
                                        <p class="text-sm text-gray-600 mt-1 font-bold">
                                            Total Stock: <strong id="stock-display-<?php echo $item['item_id']; ?>"><?php echo htmlspecialchars($item['calculated_total_stock'] ?? $item['stock_info_v2']['summary_info']['total_available_stock'] ?? $item['stock_info'][0]['seller_stock'] ?? 'N/A'); ?></strong>
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <?php if (isset($item['has_model']) && $item['has_model'] === true && !empty($item['models'])): ?>
                                        <!-- VARIASI PRODUK -->
                                        <h4 class="font-semibold text-sm mb-2 text-gray-600">Update per Variation:</h4>
                                        <div class="space-y-3">
                                            <?php foreach ($item['models'] as $model): ?>
                                                <div class="bg-white p-3 rounded-md border border-gray-200">
                                                    <div class="flex-1 text-left mb-2">
                                                        <span class="text-sm font-medium"><?php echo htmlspecialchars($model['model_name'] ?? 'Variation'); ?></span>
                                                        <p class="text-xs text-gray-600">
                                                            Harga: Rp <span id="price-display-<?php echo $model['model_id']; ?>"><?php echo number_format($model['price_info'][0]['original_price'] ?? 0, 0, ',', '.'); ?></span>
                                                            | Stok: <strong id="stock-display-<?php echo $model['model_id']; ?>"><?php echo htmlspecialchars($model['stock_info_v2']['summary_info']['total_available_stock'] ?? $model['stock_info'][0]['seller_stock'] ?? 'N/A'); ?></strong>
                                                        </p>
                                                    </div>

                                                    <!-- Form Update Stock -->
                                                    <form class="update-stock-form flex items-center gap-2 mb-2" data-model-id="<?php echo $model['model_id']; ?>">
                                                        <label class="text-xs font-medium w-20">Stock:</label>
                                                        <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['item_id']); ?>">
                                                        <input type="hidden" name="model_id" value="<?php echo htmlspecialchars($model['model_id']); ?>">
                                                        <input type="number" name="new_stock" placeholder="New" class="w-24 px-2 py-1 border border-gray-300 rounded-md shadow-sm text-sm" required>
                                                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1 px-3 rounded-lg transition text-sm">Update</button>
                                                    </form>

                                                    <!-- Form Update Price -->
                                                    <form class="update-price-form flex items-center gap-2" data-model-id="<?php echo $model['model_id']; ?>">
                                                        <label class="text-xs font-medium w-20">Price:</label>
                                                        <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['item_id']); ?>">
                                                        <input type="hidden" name="model_id" value="<?php echo htmlspecialchars($model['model_id']); ?>">
                                                        <input type="number" name="new_price" placeholder="New" class="w-24 px-2 py-1 border border-gray-300 rounded-md shadow-sm text-sm" required>
                                                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-3 rounded-lg transition text-sm">Update</button>
                                                    </form>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <!-- PRODUK TANPA VARIASI -->
                                        <div class="space-y-2">
                                            <!-- Form Update Stock -->
                                            <form class="update-stock-form flex items-center gap-2" data-item-id="<?php echo $item['item_id']; ?>">
                                                <label class="text-sm font-medium w-28">Update Stock:</label>
                                                <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['item_id']); ?>">
                                                <input type="number" name="new_stock" placeholder="New Stock" class="w-32 px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                                                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition">Update</button>
                                            </form>

                                            <!-- Form Update Price -->
                                            <form class="update-price-form flex items-center gap-2" data-item-id="<?php echo $item['item_id']; ?>">
                                                <label class="text-sm font-medium w-28">Update Price:</label>
                                                <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['item_id']); ?>">
                                                <input type="number" name="new_price" placeholder="New Price" class="w-32 px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition">Update</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php elseif (isset($product_list_response['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">API Error!</strong>
                        <span class="block sm:inline">Could not fetch products. Message: <?php echo htmlspecialchars($product_list_response['message']); ?></span>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-500">No products found or unable to fetch product list.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="text-center">
                <p class="text-gray-600 mb-8">
                    Efficiently manage your products and sales. Connect your Shopee store to get started.
                </p>  
                <?php if(isset($auth_url)): ?>
                    <a href="<?php echo htmlspecialchars($auth_url); ?>" class="inline-block w-full text-center bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-lg transition duration-300 ease-in-out text-lg">
                        🔗 Connect Shopee Store
                    </a>
                <?php else: ?>
                    <p class="text-red-500">Could not generate authentication URL. Please check your configuration.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handler untuk Update Stock
            const stockForms = document.querySelectorAll('.update-stock-form');
            stockForms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    
                    const modelId = form.dataset.modelId;
                    const itemId = form.dataset.itemId;
                    const uniqueId = modelId || itemId;
                    const stockDisplayElement = document.getElementById('stock-display-' + uniqueId);
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalButtonText = submitButton.innerHTML;
                    
                    submitButton.innerHTML = '...';
                    submitButton.disabled = true;
                    
                    const formData = new FormData(form);
                    
                    fetch('<?php echo htmlspecialchars($redirect_uri); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            stockDisplayElement.innerText = data.new_stock;
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi error. Silakan cek console browser untuk detail.');
                    })
                    .finally(() => {
                        submitButton.innerHTML = originalButtonText;
                        submitButton.disabled = false;
                    });
                });
            });

            // Handler untuk Update Price
            const priceForms = document.querySelectorAll('.update-price-form');
            priceForms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    
                    const modelId = form.dataset.modelId;
                    const itemId = form.dataset.itemId;
                    const uniqueId = modelId || itemId;
                    const priceDisplayElement = document.getElementById('price-display-' + uniqueId);
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalButtonText = submitButton.innerHTML;
                    
                    submitButton.innerHTML = '...';
                    submitButton.disabled = true;
                    
                    const formData = new FormData(form);
                    
                    fetch('<?php echo htmlspecialchars($redirect_uri); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Format harga dengan pemisah ribuan
                            const formattedPrice = new Intl.NumberFormat('id-ID').format(data.new_price);
                            priceDisplayElement.innerText = formattedPrice;
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi error. Silakan cek console browser untuk detail.');
                    })
                    .finally(() => {
                        submitButton.innerHTML = originalButtonText;
                        submitButton.disabled = false;
                    });
                });
            });
        });
    </script>
</body>
</html>