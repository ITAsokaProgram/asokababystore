<?php
require_once __DIR__ . '/produk_shopee.logic.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Shopee</title>
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

                <div class="header-card p-6 rounded-2xl mb-6">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-4">
                            <div class="icon-wrapper">
                                <img src="../../../public/images/logo/shopee.png" alt="Shopee Logo" class="h-10 w-10">
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800 mb-1">Produk</h1>
                                <p class="text-sm text-gray-600">Kelola produk</p>
                            </div>
                        </div>
                        <?php if ($shopeeService->isConnected()): ?>
                            <a href="?action=disconnect"
                                class="inline-flex items-center gap-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-3 px-6 rounded-xl transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <i class="fas fa-unlink"></i>
                                <span>Disconnect</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($shopeeService->isConnected()): ?>

                    <div class="search-filter-section">
                        <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-start justify-between">

                            <div class="flex flex-col w-full lg:w-auto lg:max-w-xl gap-2">
                                <div class="search-box flex-grow relative">
                                    <input type="text" id="product-search" placeholder="Cari Kode, Nama, SKU, atau Variasi..."
                                        class="w-full px-4 py-2 rounded-xl border-2 border-gray-300 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"
                                        autocomplete="off" aria-label="Cari produk"
                                        value="<?php echo htmlspecialchars($search_keyword); ?>">
                                    <?php if (!empty($search_keyword)): ?>
                                        <button id="clear-search"
                                            class="absolute hidden right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                                            <i class="fas fa-times hidden"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="flex flex-col gap-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <?php
                                    $filters = [
                                        'all' => ['label' => 'Semua', 'icon' => 'fa-th-large'],
                                        'pusat' => ['label' => 'Beda Stok (Pusat)', 'icon' => 'fa-cloud'],
                                        'cabang' => ['label' => 'Beda Stok (Cabang)', 'icon' => 'fa-store'],
                                        'beda_harga' => ['label' => 'Beda Harga (Pusat)', 'icon' => 'fa-dollar-sign'],
                                        'beda_hb_old_new' => ['label' => 'Beda HB (Lama vs Baru)', 'icon' => 'fa-history'],
                                        'ada_pusat' => ['label' => 'Ada di Pusat', 'icon' => 'fa-cloud-download-alt'],
                                        'ada_cabang' => ['label' => 'Ada di Cabang', 'icon' => 'fa-store-alt']
                                    ];

                                    foreach ($filters as $key => $filter):
                                        $is_active = ($filter_type == $key);
                                        $active_class = $is_active
                                            ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-200 scale-105'
                                            : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 hover:border-indigo-300 hover:shadow-md';
                                    ?>
                                        <button type="button"
                                            class="filter-btn px-4 py-2.5 rounded-xl font-semibold text-sm border-2 transition-all duration-200 flex items-center gap-2 <?php echo $active_class; ?>"
                                            data-filter="<?php echo $key; ?>" <?php if ($is_active)
                                                                                    echo 'disabled'; ?>>
                                            <i class="fas <?php echo $filter['icon']; ?> text-xs"></i>
                                            <span><?php echo $filter['label']; ?></span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>

                                <div class="flex gap-2 flex-wrap">
                                    <button id="sync-all-stock-btn"
                                        class="px-5 btn-sync py-2.5 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2 whitespace-nowrap"
                                        data-total-count="<?php echo $total_count; ?>">
                                        <i class="fas fa-sync-alt text-sm"></i>
                                        <span>Sync Semua Stok</span>
                                    </button>
                                    <button id="sync-products-to-redis-btn"
                                        class="px-5 btn-sync py-2.5 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2 whitespace-nowrap"
                                        title="Ambil semua produk dari Shopee dan simpan di cache (DB)">
                                        <i class="fas fa-bolt text-sm"></i>
                                        <span>Sync Produk ke Database</span>
                                    </button>
                                    <button id="force-sync-products-to-redis-btn"
                                        class="px-5 btn-sync py-2.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2 whitespace-nowrap"
                                        title="PAKSA sinkronisasi semua produk dari Shopee ke cache.">
                                        <i class="fas fa-exclamation-triangle text-sm"></i>
                                        <span>Paksa Sync Produk ke Database</span>
                                    </button>
                                    <button id="update-harga-beli-btn"
                                        class="px-5 btn-sync py-2.5 bg-gradient-to-r from-cyan-500 to-cyan-600 hover:from-cyan-600 hover:to-cyan-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2 whitespace-nowrap"
                                        title="Update Harga Beli dari Receipt & Stok OL">
                                        <i class="fas fa-file-invoice-dollar text-sm"></i>
                                        <span>Update Harga Beli</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-card rounded-2xl overflow-hidden">
                        <div class="section-header p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-xl font-bold text-gray-800 mb-1">Daftar Produk</h2>
                                    <p class="text-sm text-gray-600">Update harga dan stok produk Anda dengan cepat</p>
                                </div>
                                <div class="stats-badge"
                                    style="background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%); color: #6b21a8; border: 1px solid #c4b5fd;">
                                    <i class="fas fa-boxes"></i>
                                    <span id="product-count-display"><?php echo $total_count; ?> Produk</span>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($detailed_products)): ?>
                            <div class="divide-y divide-gray-100 flex flex-col gap-4">
                                <?php
                                $filtered_product_count = 0;
                                foreach ($detailed_products as $item):
                                    $filtered_product_count++;

                                    $product_card_style = '';
                                    if (!(isset($item['has_model']) && $item['has_model'] === true && !empty($item['models']))) {
                                        $item_sku_trimmed = trim($item['item_sku'] ?? '');
                                        $stok_ol_data = $sku_stok_ol_data_map[$item_sku_trimmed] ?? null;
                                        if ($stok_ol_data) {
                                            $product_card_style = 'style="background-color: #ffdae8;"';
                                        }
                                    }
                                ?>
                                    <div class="product-card p-6" <?php echo $product_card_style; ?>>
                                        <div class="flex gap-6 mb-5">
                                            <div class="product-image flex-shrink-0">
                                                <a href="detail_produk_shopee.php?item_id=<?php echo $item['item_id']; ?>"
                                                    class="product-image flex-shrink-0 cursor-pointer hover:opacity-80 transition">
                                                    <img src="<?php echo htmlspecialchars($item['image']['image_url_list'][0] ?? 'https://placehold.co/100x100'); ?>"
                                                        alt="<?php echo htmlspecialchars($item['item_name']); ?>"
                                                        class="w-24 h-24 object-cover rounded-xl bg-gray-100 border-2 border-gray-200">
                                                </a>
                                            </div>

                                            <div class="flex-grow min-w-0">
                                                <a href="detail_produk_shopee.php?item_id=<?php echo $item['item_id']; ?>"
                                                    class="hover:text-orange-600 transition">
                                                    <h3 class="font-bold text-gray-900 mb-3 text-lg line-clamp-2 leading-snug">
                                                        <?php echo htmlspecialchars($item['item_name']); ?>
                                                    </h3>
                                                </a>
                                                <div class="flex flex-wrap gap-2 mb-3">
                                                    <span class="stats-badge badge-price">
                                                        <i class="fas fa-tag"></i>
                                                        <span> <span id="price-display-<?php echo $item['item_id']; ?>">
                                                                <?php echo (isset($item['has_model']) && $item['has_model'] === true && !empty($item['models'])) ? getPriceRange($item['models']) : number_format($item['price_info'][0]['original_price'] ?? 0, 0, ',', '.'); ?>
                                                            </span></span>
                                                    </span>

                                                    <?php
                                                    // ============================================
                                                    // TAMPILAN HARGA BELI (Single Product)
                                                    // ============================================
                                                    $hb_val = (float) ($item['harga_beli'] ?? 0);
                                                    $hb_old_val = (float) ($item['hb_old'] ?? 0);
                                                    $ket_val = $item['keterangan'] ?? '';
                                                    $has_hb_data = ($hb_val > 0);
                                                    $is_diff_hb = ($hb_old_val > 0 && abs($hb_old_val - $hb_val) > 1);
                                                    ?>

                                                    <?php if ($is_diff_hb): ?>
                                                        <span class="stats-badge badge-hb-old">
                                                            <i class="fas fa-history"></i>
                                                            <div class="badge-content-wrapper">
                                                                <span class="badge-label">HB Lama</span>
                                                                <span class="badge-value line-through">Rp <?php echo number_format($hb_old_val, 0, ',', '.'); ?></span>
                                                            </div>
                                                        </span>
                                                    <?php endif; ?>

                                                    <span class="stats-badge <?php echo $has_hb_data ? 'badge-hb-active' : 'badge-hb-empty'; ?>">
                                                        <i class="fas fa-file-invoice-dollar"></i>
                                                        <div class="badge-content-wrapper">
                                                            <span class="badge-label">Harga Beli</span>
                                                            <span class="badge-value">
                                                                <?php echo $has_hb_data ? 'Rp ' . number_format($hb_val, 0, ',', '.') : '-'; ?>
                                                                <?php if (!empty($ket_val)): ?>
                                                                    <span class="badge-sub-value">(<?php echo htmlspecialchars($ket_val); ?>)</span>
                                                                <?php endif; ?>
                                                            </span>
                                                        </div>
                                                    </span>

                                                    <span class="stats-badge badge-stock">
                                                        <i class="fas fa-boxes"></i>
                                                        <span>Stok Shopee: <strong
                                                                id="stock-display-<?php echo $item['item_id']; ?>"><?php echo htmlspecialchars($item['calculated_total_stock'] ?? $item['stock_info_v2']['summary_info']['total_available_stock'] ?? $item['stock_info'][0]['seller_stock'] ?? ''); ?></strong></span>
                                                    </span>
                                                    <?php if (!(isset($item['has_model']) && $item['has_model'] === true && !empty($item['models']))): ?>
                                                        <?php $item_sku_trimmed = trim($item['item_sku'] ?? ''); ?>

                                                        <?php if (isset($sku_stock_map[$item_sku_trimmed])): ?>
                                                            <span class="stats-badge"
                                                                style="background-color: #f3e8ff; color: #581c87; border-color: #e9d5ff;">
                                                                <i class="fas fa-database fa-fw"></i>
                                                                <span>Stok ADMB:
                                                                    <strong><?php echo $sku_stock_map[$item_sku_trimmed]; ?></strong></span>
                                                            </span>
                                                        <?php endif; ?>

                                                        <?php if (isset($sku_stok_ol_data_map[$item_sku_trimmed])): ?>
                                                            <span class="stats-badge"
                                                                style="background-color: #e0f2fe; color: #0369a1; border-color: #bae6fd;">
                                                                <i class="fas fa-cloud fa-fw"></i>
                                                                <span>Stok Online:
                                                                    <strong><?php echo $sku_stok_ol_data_map[$item_sku_trimmed]['qty'] ?? ''; ?></strong></span>
                                                            </span>
                                                            <span class="stats-badge"
                                                                style="background-color: #fef3c7; color: #92400e; border-color: #fde68a;">
                                                                <i class="fas fa-money-bill-wave fa-fw"></i>
                                                                <span>Harga Online: <strong>
                                                                        <?php echo number_format($sku_stok_ol_data_map[$item_sku_trimmed]['price'] ?? 0, 0, ',', '.'); ?></strong></span>
                                                            </span>
                                                        <?php endif; ?>


                                                    <?php endif; ?>
                                                </div>

                                                <div class="flex gap-2 flex-wrap">
                                                    <span class="badge-id">ID:
                                                        <?php echo htmlspecialchars($item['item_id']); ?></span>
                                                    <?php if (!(isset($item['has_model']) && $item['has_model'] === true && !empty($item['models']))): ?>
                                                        <span class="badge-id">SKU:
                                                            <?php echo htmlspecialchars($item['item_sku'] ?? ''); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="update-form-wrapper">
                                            <?php if (isset($item['has_model']) && $item['has_model'] === true && !empty($item['models'])): ?>
                                                <div class="flex items-center gap-2 mb-4">
                                                    <i class="fas fa-layer-group text-indigo-600"></i>
                                                    <p class="text-sm font-bold text-gray-700 uppercase tracking-wide">Variasi Produk
                                                    </p>
                                                </div>
                                                <div class="space-y-3">
                                                    <?php
                                                    $models_to_display = ($filter_type == 'all')
                                                        ? ($item['models'] ?? [])
                                                        : ($item['matching_models'] ?? []);

                                                    foreach ($models_to_display as $model):
                                                        $model_sku_trimmed = trim($model['model_sku'] ?? '');
                                                        $barang_data = $sku_barang_data_map[$model_sku_trimmed] ?? null;
                                                        $stok_ol_data = $sku_stok_ol_data_map[$model_sku_trimmed] ?? null;

                                                        $variant_card_style = '';
                                                        if ($stok_ol_data) {
                                                            $variant_card_style = 'style="background-color: #ffdae8;"';
                                                        }
                                                    ?>
                                                        <div class="variant-card p-4" <?php echo $variant_card_style; ?>>
                                                            <div class="flex justify-between items-start mb-4 gap-4">
                                                                <div>
                                                                    <p class="font-bold text-gray-900 mb-2">
                                                                        <?php echo htmlspecialchars($model['model_name'] ?? 'Variation'); ?>
                                                                    </p>
                                                                    <div class="flex gap-3 flex-wrap">
                                                                        <span class="text-xs"
                                                                            style="background: #dcfce7; color: #15803d; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                                                            💰  <span id="price-display-<?php echo $model['model_id']; ?>"
                                                                                class="variant-price"><?php echo number_format($model['price_info'][0]['original_price'] ?? 0, 0, ',', '.'); ?></span>
                                                                        </span>
                                                                        <?php
                                                                        // ============================================
                                                                        // TAMPILAN HARGA BELI (Variasi)
                                                                        // ============================================
                                                                        $hb_val_mod = (float)($model['harga_beli'] ?? 0);
                                                                        $hb_old_val_mod = (float)($model['hb_old'] ?? 0);
                                                                        $ket_val_mod = $model['keterangan'] ?? '';
                                                                        $has_hb_data_mod = ($hb_val_mod > 0);
                                                                        $is_diff_hb_mod = ($hb_old_val_mod > 0 && abs($hb_old_val_mod - $hb_val_mod) > 1);
                                                                        ?>

                                                                        <?php if ($is_diff_hb_mod): ?>
                                                                                <span class="stats-badge badge-hb-old">
                                                                                    <i class="fas fa-history"></i>
                                                                                    <div class="badge-content-wrapper">
                                                                                        <span class="badge-label">HB Lama</span>
                                                                                        <span class="badge-value line-through">Rp <?php echo number_format($hb_old_val_mod, 0, ',', '.'); ?></span>
                                                                                    </div>
                                                                                </span>
                                                                            <?php endif; ?>

                                                                            <span class="stats-badge <?php echo $has_hb_data_mod ? 'badge-hb-active' : 'badge-hb-empty'; ?>">
                                                                                <i class="fas fa-file-invoice-dollar"></i>
                                                                                <div class="badge-content-wrapper">
                                                                                    <span class="badge-label">Harga Beli</span>
                                                                                    <span class="badge-value">
                                                                                        <?php echo $has_hb_data_mod ? 'Rp ' . number_format($hb_val_mod, 0, ',', '.') : '-'; ?>
                                                                                        <?php if (!empty($ket_val_mod)): ?>
                                                                                            <span class="badge-sub-value">(<?php echo htmlspecialchars($ket_val_mod); ?>)</span>
                                                                                        <?php endif; ?>
                                                                                    </span>
                                                                                </div>
                                                                            </span>

                                                                        <span class="text-xs"
                                                                            style="background: #e0e7ff; color: #3730a3; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                                                            SKU:
                                                                            <strong><?php echo htmlspecialchars($model['model_sku'] ?? ''); ?></strong>
                                                                        </span>
                                                                        <span class="text-xs"
                                                                            style="background: #dbeafe; color: #1e40af; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                                                            📦 Stok Shopee: <strong
                                                                                id="stock-display-<?php echo $model['model_id']; ?>"
                                                                                class="variant-stock"><?php echo htmlspecialchars($model['stock_info_v2']['summary_info']['total_available_stock'] ?? $model['stock_info'][0]['seller_stock'] ?? ''); ?></strong>
                                                                        </span>
                                                                        <?php if (isset($sku_stock_map[$model_sku_trimmed])): ?>
                                                                            <span class="text-xs"
                                                                                style="background: #f3e8ff; color: #581c87; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                                                                <i class="fas fa-database fa-fw"></i> Stok ADMB:
                                                                                <strong><?php echo $sku_stock_map[$model_sku_trimmed]; ?></strong>
                                                                            </span>
                                                                        <?php endif; ?>
                                                                        <?php if (isset($sku_stok_ol_data_map[$model_sku_trimmed])): ?>
                                                                            <span class="text-xs"
                                                                                style="background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                                                                <i class="fas fa-cloud fa-fw"></i> Stok Online:
                                                                                <strong><?php echo $sku_stok_ol_data_map[$model_sku_trimmed]['qty'] ?? ''; ?></strong>
                                                                            </span>
                                                                            <span class="text-xs"
                                                                                style="background: #fef3c7; color: #92400e; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                                                                <i class="fas fa-money-bill-wave fa-fw"></i> Harga Online:
                                                                                <strong>
                                                                                    <?php echo number_format($sku_stok_ol_data_map[$model_sku_trimmed]['price'] ?? 0, 0, ',', '.'); ?></strong>
                                                                            </span>
                                                                        <?php endif; ?>

                                                                    </div>
                                                                </div>

                                                                <div
                                                                    class="flex md:items-center justify-between md:flex-row flex-col flex-wrap gap-4">
                                                                    <form class="update-stock-form form-group"
                                                                        data-model-id="<?php echo $model['model_id']; ?>">
                                                                        <input type="hidden" name="item_id"
                                                                            value="<?php echo htmlspecialchars($item['item_id']); ?>">
                                                                        <input type="hidden" name="model_id"
                                                                            value="<?php echo htmlspecialchars($model['model_id']); ?>">
                                                                        <label
                                                                            class="text-sm font-semibold text-gray-700 whitespace-nowrap w-28">
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
                                                                            value="<?php echo htmlspecialchars($item['item_id']); ?>">
                                                                        <input type="hidden" name="model_id"
                                                                            value="<?php echo htmlspecialchars($model['model_id']); ?>">
                                                                        <label
                                                                            class="text-sm font-semibold text-gray-700 whitespace-nowrap w-28">
                                                                            <i class="fas fa-tag mr-1"></i> Harga Baru:
                                                                        </label>
                                                                        <input type="number" name="new_price" placeholder="0"
                                                                            class="input-field flex-1" required>
                                                                        <button type="submit"
                                                                            class="btn-action btn-price rounded-xl whitespace-nowrap">Update</button>
                                                                    </form>

                                                                    <?php if ($is_diff_hb_mod): ?>
                                                                        <button type="button"
                                                                            class="btn-action btn-calc-margin rounded-xl whitespace-nowrap bg-indigo-100 text-indigo-700 hover:bg-indigo-200 border border-indigo-200 px-3 py-2 transition-colors"
                                                                            data-sku="<?php echo htmlspecialchars($model['model_sku'] ?? ''); ?>"
                                                                            data-item-id="<?php echo htmlspecialchars($item['item_id']); ?>"
                                                                            data-model-id="<?php echo htmlspecialchars($model['model_id'] ?? 0); ?>"
                                                                            data-hb="<?php echo $hb_val_mod; ?>"
                                                                            data-hb-old="<?php echo $hb_old_val_mod; ?>"
                                                                            data-price="<?php echo (float)($model['price_info'][0]['original_price'] ?? 0); ?>">
                                                                            <i class="fas fa-calculator mr-1"></i> Cek Harga
                                                                        </button>
                                                                    <?php endif; ?>

                                                                    <form class="sync-stock-form"
                                                                        data-item-id="<?php echo htmlspecialchars($item['item_id']); ?>"
                                                                        data-model-id="<?php echo htmlspecialchars($model['model_id']); ?>">
                                                                        <input type="hidden" name="item_id"
                                                                            value="<?php echo htmlspecialchars($item['item_id']); ?>">
                                                                        <input type="hidden" name="model_id"
                                                                            value="<?php echo htmlspecialchars($model['model_id']); ?>">
                                                                        <input type="hidden" name="sku"
                                                                            value="<?php echo htmlspecialchars($model['model_sku']); ?>">
                                                                        <button type="submit"
                                                                            class="btn-action btn-sync rounded-xl whitespace-nowrap"
                                                                            title="Samakan stok Shopee dengan stok database">Sinkronisasi
                                                                            Stok</button>
                                                                    </form>
                                                                    <?php
                                                                    $btn_text = '';
                                                                    $btn_class = '';
                                                                    $btn_icon = 'fa-plus';
                                                                    $btn_disabled = false;
                                                                    $data_attrs = '';
                                                                    $show_button = false;
                                                                    if ($barang_data) {
                                                                        if ($stok_ol_data) {
                                                                            $show_button = false;
                                                                        } else {
                                                                            $show_button = true;
                                                                            $btn_text = 'Masukkan ke Stok Online';
                                                                            $btn_class = 'btn-manage-ol-add';
                                                                            $btn_icon = 'fa-plus';
                                                                            $data_attrs = 'data-mode="add" data-sku="' . htmlspecialchars($model_sku_trimmed) . '" data-plu="' . htmlspecialchars($barang_data['plu']) . '" data-descp="' . htmlspecialchars($barang_data['descp']) . '" data-vendor="' . htmlspecialchars($barang_data['vendor']) . '" data-hrg_beli="' . htmlspecialchars($barang_data['harga_beli']) . '" data-price="' . htmlspecialchars($barang_data['harga_jual']) . '"';
                                                                        }
                                                                    } else {
                                                                        $show_button = true;
                                                                        $btn_text = 'SKU tdk ada di s_barang';
                                                                        $btn_class = 'btn-manage-ol-disabled';
                                                                        $btn_icon = 'fa-times';
                                                                        $btn_disabled = true;
                                                                    }
                                                                    ?>
                                                                    <?php if ($show_button): ?>
                                                                        <button type="button"
                                                                            class="btn-action btn-manage-stok-ol <?php echo $btn_class; ?> rounded-xl whitespace-nowrap"
                                                                            <?php echo $data_attrs; ?> <?php if ($btn_disabled)
                                                                                                            echo 'disabled'; ?>>
                                                                            <i
                                                                                class="fas <?php echo $btn_icon; ?> fa-fw"></i><span><?php echo $btn_text; ?></span>
                                                                        </button>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <?php
                                                $item_sku_trimmed = trim($item['item_sku'] ?? '');
                                                $barang_data = $sku_barang_data_map[$item_sku_trimmed] ?? null;
                                                $stok_ol_data = $sku_stok_ol_data_map[$item_sku_trimmed] ?? null;
                                                ?>
                                                <div
                                                    class="flex md:items-center justify-between md:flex-row flex-col flex-wrap gap-4">
                                                    <form class="update-stock-form form-group"
                                                        data-item-id="<?php echo $item['item_id']; ?>">
                                                        <input type="hidden" name="item_id"
                                                            value="<?php echo htmlspecialchars($item['item_id']); ?>">
                                                        <label class="text-sm font-semibold text-gray-700 whitespace-nowrap w-28"><i
                                                                class="fas fa-box mr-1"></i> Stok Baru:</label>
                                                        <input type="number" name="new_stock" placeholder="0"
                                                            class="input-field flex-1" required>
                                                        <button type="submit"
                                                            class="btn-action btn-stock rounded-xl whitespace-nowrap">Update</button>
                                                    </form>
                                                    <form class="update-price-form form-group"
                                                        data-item-id="<?php echo $item['item_id']; ?>">
                                                        <input type="hidden" name="item_id"
                                                            value="<?php echo htmlspecialchars($item['item_id']); ?>">
                                                        <label class="text-sm font-semibold text-gray-700 whitespace-nowrap w-28"><i
                                                                class="fas fa-tag mr-1"></i> Harga Baru:</label>
                                                        <input type="number" name="new_price" placeholder="0"
                                                            class="input-field flex-1" required>
                                                        <button type="submit"
                                                            class="btn-action btn-price rounded-xl whitespace-nowrap">Update</button>
                                                    </form>

                                                    <?php if ($is_diff_hb): ?>
                                                        <button type="button"
                                                            class="btn-action btn-calc-margin rounded-xl whitespace-nowrap bg-indigo-100 text-indigo-700 hover:bg-indigo-200 border border-indigo-200 px-3 py-2 transition-colors"
                                                            data-sku="<?php echo htmlspecialchars($item['item_sku'] ?? ''); ?>"
                                                            data-item-id="<?php echo htmlspecialchars($item['item_id']); ?>"
                                                            data-model-id="0"
                                                            data-hb="<?php echo $hb_val; ?>"
                                                            data-hb-old="<?php echo $hb_old_val; ?>"
                                                            data-price="<?php echo (float)($item['price_info'][0]['original_price'] ?? 0); ?>">
                                                            <i class="fas fa-calculator mr-1"></i> Cek Harga
                                                        </button>
                                                    <?php endif; ?>

                                                    <form class="sync-stock-form"
                                                        data-item-id="<?php echo htmlspecialchars($item['item_id']); ?>">
                                                        <input type="hidden" name="item_id"
                                                            value="<?php echo htmlspecialchars($item['item_id']); ?>">
                                                        <input type="hidden" name="model_id" value="0"> <input type="hidden"
                                                            name="sku" value="<?php echo htmlspecialchars($item['item_sku']); ?>">
                                                        <button type="submit"
                                                            class="btn-action btn-sync rounded-xl whitespace-nowrap"
                                                            title="Samakan stok Shopee dengan stok database">Sinkronisasi
                                                            Stok</button>
                                                    </form>
                                                    <?php
                                                    $btn_text = '';
                                                    $btn_class = '';
                                                    $btn_icon = 'fa-plus';
                                                    $btn_disabled = false;
                                                    $data_attrs = '';
                                                    $show_button = false;
                                                    if ($barang_data) {
                                                        if ($stok_ol_data) {
                                                            $show_button = false;
                                                        } else {
                                                            $show_button = true;
                                                            $btn_text = 'Masukkan ke Stok Online';
                                                            $btn_class = 'btn-manage-ol-add';
                                                            $btn_icon = 'fa-plus';
                                                            $data_attrs = 'data-mode="add" data-sku="' . htmlspecialchars($item_sku_trimmed) . '" data-plu="' . htmlspecialchars($barang_data['plu']) . '" data-descp="' . htmlspecialchars($barang_data['descp']) . '" data-vendor="' . htmlspecialchars($barang_data['vendor']) . '" data-hrg_beli="' . htmlspecialchars($barang_data['harga_beli']) . '" data-price="' . htmlspecialchars($barang_data['harga_jual']) . '"';
                                                        }
                                                    } else {
                                                        $show_button = true;
                                                        $btn_text = 'SKU tdk ada di s_barang';
                                                        $btn_class = 'btn-manage-ol-disabled';
                                                        $btn_icon = 'fa-times';
                                                        $btn_disabled = true;
                                                    }
                                                    ?>
                                                    <?php if ($show_button): ?>
                                                        <button type="button"
                                                            class="btn-action btn-manage-stok-ol <?php echo $btn_class; ?> rounded-xl whitespace-nowrap"
                                                            <?php echo $data_attrs; ?> <?php if ($btn_disabled)
                                                                                            echo 'disabled'; ?>>
                                                            <i
                                                                class="fas <?php echo $btn_icon; ?> fa-fw"></i><span><?php echo $btn_text; ?></span>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if ($filter_type != 'all'): ?>
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const countDisplay = document.getElementById('product-count-display');
                                            if (countDisplay) {
                                                const filteredPageCount = <?php echo $total_count; ?>;
                                                countDisplay.innerHTML = `<i class="fas fa-filter"></i> ${filteredPageCount} Produk Ditemukan`;
                                                const countBadge = countDisplay.closest('.stats-badge');
                                                if (countBadge) {
                                                    countBadge.style.background = 'linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%)';
                                                    countBadge.style.color = '#0369a1';
                                                    countBadge.style.borderColor = '#7dd3fc';
                                                }
                                            }
                                        });
                                    </script>
                                <?php endif; ?>
                            </div>

                            <?php if ($filtered_product_count == 0): ?>
                                <div class="p-8 text-center">
                                    <i class="fas fa-filter text-6xl text-gray-300 mb-4"></i>
                                    <p class="text-gray-500 text-lg font-medium">Tidak Ada Produk Ditemukan</p>
                                    <p class="text-gray-400 text-sm mt-2">Tidak ada produk yang cocok dengan filter
                                        "<?php echo htmlspecialchars($filter_type); ?>" dan pencarian Anda.</p>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['shopee_flash_message'])): ?>
                                <script>
                                    document.addEventListener('DOMContentLoaded', () => {
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Perhatian',
                                            text: '<?php echo addslashes($_SESSION['shopee_flash_message']); ?>',
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 4000,
                                            timerProgressBar: true,
                                            didOpen: (toast) => {
                                                toast.addEventListener('mouseenter', Swal.stopTimer);
                                                toast.addEventListener('mouseleave', Swal.resumeTimer);
                                            }
                                        });
                                    });
                                </script>
                                <?php unset($_SESSION['shopee_flash_message']);
                            endif; ?>

                        <?php else: ?>
                            <div class="p-8 text-center">
                                <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500 text-lg font-medium">Tidak ada produk ditemukan</p>
                                <p class="text-gray-400 text-sm mt-2">Produk Anda akan muncul di sini. Jika cache kosong,
                                    coba
                                    'Sync Produk ke Cache'.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($pagination_info && $total_pages > 1): ?>
                        <div class="pagination-controls p-6 border-t border-gray-100 bg-white rounded-b-2xl mt-6">
                            <div class="flex flex-wrap items-center justify-center gap-2">
                                <?php
                                $adjacents = 2; // Jumlah angka di kiri/kanan halaman aktif
                                $start_loop = max(2, $current_page - $adjacents);
                                $end_loop = min($total_pages - 1, $current_page + $adjacents);

                                // Helper function untuk generate URL
                                function get_page_url($page_num, $page_size)
                                {
                                    global $search_keyword, $search_type, $filter_type;
                                    $offset_val = ($page_num - 1) * $page_size;
                                    $url = "?offset=" . $offset_val;
                                    if (!empty($search_keyword)) {
                                        $url .= "&search=" . urlencode($search_keyword) . "&search_type=" . $search_type;
                                    }
                                    if ($filter_type != 'all') {
                                        $url .= "&filter=" . $filter_type;
                                    }
                                    return $url;
                                }
                                ?>

                                <?php
                                $active_class = ($current_page == 1)
                                    ? 'bg-indigo-600 text-white border-indigo-600 shadow-md transform scale-105'
                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-indigo-300';
                                ?>
                                <a href="<?php echo get_page_url(1, $page_size); ?>"
                                    class="w-10 h-10 flex items-center justify-center rounded-xl border-2 font-semibold text-sm transition-all duration-200 <?php echo $active_class; ?>">
                                    1
                                </a>

                                <?php if ($start_loop > 2): ?>
                                    <span class="w-10 h-10 flex items-center justify-center text-gray-400 font-bold">...</span>
                                <?php endif; ?>

                                <?php for ($i = $start_loop; $i <= $end_loop; $i++): ?>
                                    <?php
                                    $active_class = ($current_page == $i)
                                        ? 'bg-indigo-600 text-white border-indigo-600 shadow-md transform scale-105'
                                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-indigo-300';
                                    ?>
                                    <a href="<?php echo get_page_url($i, $page_size); ?>"
                                        class="w-10 h-10 flex items-center justify-center rounded-xl border-2 font-semibold text-sm transition-all duration-200 <?php echo $active_class; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($end_loop < $total_pages - 1): ?>
                                    <span class="w-10 h-10 flex items-center justify-center text-gray-400 font-bold">...</span>
                                <?php endif; ?>

                                <?php if ($total_pages > 1): ?>
                                    <?php
                                    $active_class = ($current_page == $total_pages)
                                        ? 'bg-indigo-600 text-white border-indigo-600 shadow-md transform scale-105'
                                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-indigo-300';
                                    ?>
                                    <a href="<?php echo get_page_url($total_pages, $page_size); ?>"
                                        class="w-10 h-10 flex items-center justify-center rounded-xl border-2 font-semibold text-sm transition-all duration-200 <?php echo $active_class; ?>">
                                        <?php echo $total_pages; ?>
                                    </a>
                                <?php endif; ?>

                            </div>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="connect-card p-16 rounded-2xl">
                        <div class="max-w-md mx-auto text-center py-4">
                            <div class="icon-wrapper w-24 h-24 mx-auto mb-8 flex items-center justify-center">
                                <img src="../../../public/images/logo/shopee.png" alt="Shopee" class="h-12 w-12">
                            </div>
                            <h2 class="text-3xl font-bold text-gray-800 mb-4">Hubungkan Toko Shopee</h2>
                            <p class="text-gray-600 mb-8 text-lg leading-relaxed">Kelola produk dan stok toko Shopee
                                Anda
                                dengan mudah dari satu dashboard yang terintegrasi</p>

                            <?php if (isset($auth_url)): ?>
                                <a href="<?php echo htmlspecialchars($auth_url); ?>"
                                    class="inline-flex items-center justify-center gap-3 w-full bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold py-4 px-8 rounded-xl text-lg transition shadow-xl hover:shadow-2xl transform hover:-translate-y-1">
                                    <i class="fas fa-link text-xl"></i>
                                    <span>Hubungkan Sekarang</span>
                                </a>
                            <?php else: ?>
                                <div class="bg-red-50 border-2 border-red-200 text-red-700 px-6 py-4 rounded-xl">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <span class="font-semibold">Gagal membuat URL autentikasi</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </section>
    </main>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/shopee/produk_handler.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>

</html>