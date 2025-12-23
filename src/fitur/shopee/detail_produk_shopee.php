<?php
session_start();
include '../../../aa_kon_sett.php';
require_once __DIR__ . '/lib/ShopeeApiService.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$shopeeService = new ShopeeApiService();

if (!$shopeeService->isConnected()) {
    header('Location: produk_shopee.php');
    exit();
}

$item_id = isset($_GET['item_id']) ? (int) $_GET['item_id'] : 0;

if ($item_id <= 0) {
    header('Location: produk_shopee.php');
    exit();
}

require_once __DIR__ . '/../../component/menu_handler.php';
$menuHandler = new MenuHandler('shopee_dashboard');
if (!$menuHandler->initialize()) {
    exit();
}

$product_response = $shopeeService->call("/api/v2/product/get_item_base_info", 'GET', ['item_id_list' => $item_id]);

$product = null;
if (isset($product_response['response']['item_list'][0])) {
    $product = $product_response['response']['item_list'][0];

    if (!empty($product['has_model'])) {
        $model_response = $shopeeService->call("/api/v2/product/get_model_list", 'GET', ['item_id' => $item_id]);
        if (isset($model_response['response']['model'])) {
            $product['models'] = $model_response['response']['model'];

            $total_stock = 0;
            foreach ($product['models'] as $model) {
                $stock = $model['stock_info_v2']['summary_info']['total_available_stock']
                    ?? $model['stock_info'][0]['seller_stock']
                    ?? 0;
                $total_stock += $stock;
            }
            $product['calculated_total_stock'] = $total_stock;
        }
    }
}

if (!$product) {
    header('Location: produk_shopee.php');
    exit();
}

$sku_stock_map = [];
$kd_store = '3190';
$all_skus = [];

if (isset($product['has_model']) && $product['has_model'] === true && !empty($product['models'])) {
    foreach ($product['models'] as $model) {
        if (!empty($model['model_sku'])) {
            $all_skus[] = $model['model_sku'];
        }
    }
} else {
    if (!empty($product['item_sku'])) {
        $all_skus[] = $product['item_sku'];
    }
}

if (!empty($all_skus) && isset($conn) && $conn instanceof mysqli) {
    $unique_skus = array_unique($all_skus);
    $placeholders = implode(',', array_fill(0, count($unique_skus), '?'));
    $types = str_repeat('s', count($unique_skus));
    $sql = "SELECT item_n, qty FROM s_barang WHERE kd_store = ? AND item_n IN ($placeholders)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s" . $types, $kd_store, ...$unique_skus);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $sku_stock_map[$row['item_n']] = (int) $row['qty'];
        }
        $stmt->close();
    }
}

function formatPrice($price)
{
    return number_format($price, 0, ',', '.');
}

function getPriceRange($models)
{
    if (empty($models))
        return null;
    $prices = array_column(array_column($models, 'price_info'), 0);
    $original_prices = array_column($prices, 'original_price');
    if (empty($original_prices))
        return null;

    $minPrice = min($original_prices);
    $maxPrice = max($original_prices);

    return ($minPrice == $maxPrice)
        ? formatPrice($minPrice)
        : formatPrice($minPrice) . ' - ' . formatPrice($maxPrice);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['item_name']); ?> - Detail Produk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="../../style/shopee/shopee.css">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
</head>

<body class="bg-gray-50 overflow-auto">

    <?php include '../../component/navigation_report.php' ?>
    <?php include '../../component/sidebar_report.php' ?>

    <main id="main-content" class="flex-1 p-6 ml-64">
        <section class="min-h-[85vh] px-2 md:px-6">
            <div class="w-full max-w-7xl mx-auto">

                <!-- Back Button & Header -->
                <div class="mb-6">
                    <a href="produk_shopee.php"
                        class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300 font-semibold text-sm px-4 py-2 rounded-lg shadow-sm transition mb-4">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali ke Daftar Produk</span>
                    </a>

                    <div class="header-card p-6 rounded-2xl">
                        <div class="flex items-center gap-4">
                            <div class="icon-wrapper">
                                <img src="../../../public/images/logo/shopee.png" alt="Shopee Logo" class="h-10 w-10">
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800 mb-1">Detail Produk</h1>
                                <p class="text-sm text-gray-600">Informasi lengkap produk Shopee</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Detail Card -->
                <div class="section-card rounded-2xl overflow-hidden">
                    <div class="p-6 md:p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">

                            <!-- Product Images -->
                            <div class="sticky top-6">
                                <div
                                    class="bg-gray-100 rounded-xl overflow-hidden mb-4 aspect-square flex items-center justify-center">
                                    <img id="main-image"
                                        src="<?php echo htmlspecialchars($product['image']['image_url_list'][0] ?? 'https://placehold.co/600x600'); ?>"
                                        alt="<?php echo htmlspecialchars($product['item_name']); ?>"
                                        class="w-full h-full object-contain">
                                </div>

                                <?php if (!empty($product['image']['image_url_list']) && count($product['image']['image_url_list']) > 1): ?>
                                    <div class="relative">
                                        <button id="scroll-left"
                                            class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-2 z-10 bg-white shadow-lg rounded-full w-8 h-8 flex items-center justify-center hover:bg-gray-100 transition opacity-0 disabled:opacity-0">
                                            <i class="fas fa-chevron-left text-gray-700 text-sm"></i>
                                        </button>

                                        <div id="thumbnail-container"
                                            class="flex gap-2 overflow-x-auto scroll-smooth hide-scrollbar">
                                            <?php foreach ($product['image']['image_url_list'] as $index => $image_url): ?>
                                                <div class="flex-shrink-0 w-16 h-16 cursor-pointer border-2 <?php echo $index === 0 ? 'border-orange-500 border-4' : 'border-gray-200'; ?> rounded-lg overflow-hidden hover:border-orange-500 transition bg-gray-50 thumbnail-item"
                                                    onclick="document.getElementById('main-image').src='<?php echo htmlspecialchars($image_url); ?>'; 
                                                      document.querySelectorAll('.thumbnail-item').forEach(el => el.classList.remove('border-orange-500', '!border-4')); 
                                                      this.classList.add('border-orange-500', '!border-4');">
                                                    <img src="<?php echo htmlspecialchars($image_url); ?>"
                                                        alt="Thumbnail <?php echo $index + 1; ?>"
                                                        class="w-full h-full object-cover">
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <button id="scroll-right"
                                            class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-2 z-10 bg-white shadow-lg rounded-full w-8 h-8 flex items-center justify-center hover:bg-gray-100 transition">
                                            <i class="fas fa-chevron-right text-gray-700 text-sm"></i>
                                        </button>
                                    </div>

                                    <style>
                                        .hide-scrollbar::-webkit-scrollbar {
                                            display: none;
                                        }

                                        .hide-scrollbar {
                                            -ms-overflow-style: none;
                                            scrollbar-width: none;
                                        }

                                        #scroll-left.opacity-100,
                                        #scroll-right.opacity-100 {
                                            opacity: 1 !important;
                                        }
                                    </style>

                                    <script>
                                        document.addEventListener('DOMContentLoaded', function () {
                                            const container = document.getElementById('thumbnail-container');
                                            const scrollLeft = document.getElementById('scroll-left');
                                            const scrollRight = document.getElementById('scroll-right');

                                            if (!container || !scrollLeft || !scrollRight) return;

                                            function updateButtons() {
                                                const isAtStart = container.scrollLeft <= 0;
                                                const isAtEnd = container.scrollLeft >= container.scrollWidth - container.clientWidth - 1;

                                                if (isAtStart) {
                                                    scrollLeft.classList.remove('opacity-100');
                                                    scrollLeft.disabled = true;
                                                } else {
                                                    scrollLeft.classList.add('opacity-100');
                                                    scrollLeft.disabled = false;
                                                }

                                                if (isAtEnd) {
                                                    scrollRight.classList.remove('opacity-100');
                                                    scrollRight.disabled = true;
                                                } else {
                                                    scrollRight.classList.add('opacity-100');
                                                    scrollRight.disabled = false;
                                                }
                                            }

                                            scrollLeft.addEventListener('click', () => {
                                                container.scrollBy({ left: -200, behavior: 'smooth' });
                                            });

                                            scrollRight.addEventListener('click', () => {
                                                container.scrollBy({ left: 200, behavior: 'smooth' });
                                            });

                                            container.addEventListener('scroll', updateButtons);
                                            updateButtons();
                                        });
                                    </script>
                                <?php endif; ?>
                            </div>

                            <!-- Product Info -->
                            <div>
                                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                                    <?php echo htmlspecialchars($product['item_name']); ?></h2>

                                <div class="mb-6">
                                    <div class="text-3xl font-bold text-orange-600 mb-2">
                                        Rp <?php echo (isset($product['has_model']) && $product['has_model'] === true && !empty($product['models']))
                                            ? getPriceRange($product['models'])
                                            : formatPrice($product['price_info'][0]['original_price'] ?? 0); ?>
                                    </div>
                                </div>

                                <div class="space-y-3 mb-6">
                                    <div class="flex items-center gap-3">
                                        <span class="stats-badge badge-stock">
                                            <i class="fas fa-boxes"></i>
                                            <span>Stok Shopee:
                                                <strong><?php echo htmlspecialchars($product['calculated_total_stock'] ?? $product['stock_info_v2']['summary_info']['total_available_stock'] ?? $product['stock_info'][0]['seller_stock'] ?? 0); ?></strong></span>
                                        </span>

                                        <?php if (!(isset($product['has_model']) && $product['has_model'] === true && !empty($product['models']))): ?>
                                            <?php
                                            $item_sku = $product['item_sku'] ?? null;
                                            $db_stock = $sku_stock_map[$item_sku] ?? '';
                                            ?>
                                            <span class="stats-badge"
                                                style="background-color: #f3e8ff; color: #581c87; border-color: #e9d5ff;">
                                                <i class="fas fa-database fa-fw"></i>
                                                <span>Stok ADMB: <strong><?php echo $db_stock; ?></strong></span>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="flex gap-2 flex-wrap">
                                        <span class="badge-id">ID:
                                            <?php echo htmlspecialchars($product['item_id']); ?></span>
                                        <?php if (!empty($product['item_sku'])): ?>
                                            <span class="badge-id">SKU:
                                                <?php echo htmlspecialchars($product['item_sku']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if (!empty($product['description'])): ?>
                                    <div class="bg-gray-50 p-4 rounded-xl">
                                        <h3 class="font-bold text-gray-900 mb-2">Deskripsi Produk</h3>
                                        <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-line">
                                            <?php echo htmlspecialchars($product['description']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Variants Section -->
                        <?php if (isset($product['has_model']) && $product['has_model'] === true && !empty($product['models'])): ?>
                            <div class="border-t pt-8">
                                <div class="flex items-center gap-2 mb-6">
                                    <i class="fas fa-layer-group text-indigo-600 text-xl"></i>
                                    <h3 class="text-2xl font-bold text-gray-900">Variasi Produk</h3>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <?php foreach ($product['models'] as $model): ?>
                                        <div class="variant-card p-5">
                                            <div class="mb-4">
                                                <p class="font-bold text-gray-900 text-lg mb-3">
                                                    <?php echo htmlspecialchars($model['model_name'] ?? 'Variation'); ?></p>
                                                <div class="flex gap-3 flex-wrap">
                                                    <span class="text-xs"
                                                        style="background: #dcfce7; color: #15803d; padding: 6px 10px; border-radius: 6px; font-weight: 600;">
                                                        💰 Rp <span id="price-display-<?php echo $model['model_id']; ?>"
                                                            class="variant-price"><?php echo formatPrice($model['price_info'][0]['original_price'] ?? 0); ?></span>
                                                    </span>
                                                    <span class="text-xs"
                                                        style="background: #e0e7ff; color: #3730a3; padding: 6px 10px; border-radius: 6px; font-weight: 600;">
                                                        SKU:
                                                        <strong><?php echo htmlspecialchars($model['model_sku'] ?? ''); ?></strong>
                                                    </span>
                                                    <span class="text-xs"
                                                        style="background: #dbeafe; color: #1e40af; padding: 6px 10px; border-radius: 6px; font-weight: 600;">
                                                        📦 Stok Shopee: <strong
                                                            id="stock-display-<?php echo $model['model_id']; ?>"
                                                            class="variant-stock"><?php echo htmlspecialchars($model['stock_info_v2']['summary_info']['total_available_stock'] ?? $model['stock_info'][0]['seller_stock'] ?? 0); ?></strong>
                                                    </span>
                                                    <?php
                                                    $model_sku = $model['model_sku'] ?? null;
                                                    $db_stock = $sku_stock_map[trim($model_sku)] ?? '';
                                                    ?>
                                                    <span class="text-xs"
                                                        style="background: #f3e8ff; color: #581c87; padding: 6px 10px; border-radius: 6px; font-weight: 600;">
                                                        <i class="fas fa-database fa-fw"></i> Stok ADMB:
                                                        <strong><?php echo $db_stock; ?></strong>
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="space-y-3">
                                                <form class="update-stock-form form-group"
                                                    data-model-id="<?php echo $model['model_id']; ?>">
                                                    <input type="hidden" name="item_id"
                                                        value="<?php echo htmlspecialchars($product['item_id']); ?>">
                                                    <input type="hidden" name="model_id"
                                                        value="<?php echo htmlspecialchars($model['model_id']); ?>">
                                                    <label class="text-sm font-semibold text-gray-700 whitespace-nowrap w-28">
                                                        <i class="fas fa-box mr-1"></i> Stok Baru:
                                                    </label>
                                                    <input type="number" name="new_stock" placeholder="0"
                                                        class="input-field flex-1" required>
                                                    <button type="submit"
                                                        class="btn-action btn-stock rounded-xl whitespace-nowrap">Update</button>
                                                </form>

                                                <form class="update-price-form form-group"
                                                    data-model-id="<?php echo $model['model_id']; ?>">
                                                    <input type="hidden" name="item_id"
                                                        value="<?php echo htmlspecialchars($product['item_id']); ?>">
                                                    <input type="hidden" name="model_id"
                                                        value="<?php echo htmlspecialchars($model['model_id']); ?>">
                                                    <label class="text-sm font-semibold text-gray-700 whitespace-nowrap w-28">
                                                        <i class="fas fa-tag mr-1"></i> Harga Baru:
                                                    </label>
                                                    <input type="number" name="new_price" placeholder="0"
                                                        class="input-field flex-1" required>
                                                    <button type="submit"
                                                        class="btn-action btn-price rounded-xl whitespace-nowrap">Update</button>
                                                </form>

                                                <form class="sync-stock-form"
                                                    data-item-id="<?php echo htmlspecialchars($product['item_id']); ?>"
                                                    data-model-id="<?php echo htmlspecialchars($model['model_id']); ?>">
                                                    <input type="hidden" name="item_id"
                                                        value="<?php echo htmlspecialchars($product['item_id']); ?>">
                                                    <input type="hidden" name="model_id"
                                                        value="<?php echo htmlspecialchars($model['model_id']); ?>">
                                                    <input type="hidden" name="sku"
                                                        value="<?php echo htmlspecialchars($model['model_sku']); ?>">
                                                    <button type="submit" class="btn-action btn-sync rounded-xl w-full">
                                                        <i class="fas fa-sync-alt mr-1"></i> Sinkronisasi Stok
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Single Product Update Forms -->
                            <div class="border-t pt-8">
                                <h3 class="text-xl font-bold text-gray-900 mb-6">Update Produk</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <form class="update-stock-form form-group"
                                        data-item-id="<?php echo $product['item_id']; ?>">
                                        <input type="hidden" name="item_id"
                                            value="<?php echo htmlspecialchars($product['item_id']); ?>">
                                        <label class="text-sm font-semibold text-gray-700 whitespace-nowrap w-28">
                                            <i class="fas fa-box mr-1"></i> Stok Baru:
                                        </label>
                                        <input type="number" name="new_stock" placeholder="0" class="input-field flex-1"
                                            required>
                                        <button type="submit"
                                            class="btn-action btn-stock rounded-xl whitespace-nowrap">Update</button>
                                    </form>

                                    <form class="update-price-form form-group"
                                        data-item-id="<?php echo $product['item_id']; ?>">
                                        <input type="hidden" name="item_id"
                                            value="<?php echo htmlspecialchars($product['item_id']); ?>">
                                        <label class="text-sm font-semibold text-gray-700 whitespace-nowrap w-28">
                                            <i class="fas fa-tag mr-1"></i> Harga Baru:
                                        </label>
                                        <input type="number" name="new_price" placeholder="0" class="input-field flex-1"
                                            required>
                                        <button type="submit"
                                            class="btn-action btn-price rounded-xl whitespace-nowrap">Update</button>
                                    </form>

                                    <form class="sync-stock-form md:col-span-2"
                                        data-item-id="<?php echo htmlspecialchars($product['item_id']); ?>">
                                        <input type="hidden" name="item_id"
                                            value="<?php echo htmlspecialchars($product['item_id']); ?>">
                                        <input type="hidden" name="model_id" value="0">
                                        <input type="hidden" name="sku"
                                            value="<?php echo htmlspecialchars($product['item_sku']); ?>">
                                        <button type="submit" class="btn-action btn-sync rounded-xl w-full">
                                            <i class="fas fa-sync-alt mr-1"></i> Sinkronisasi Stok
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </section>
    </main>

    <script src="../../js/shopee/produk_handler.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>