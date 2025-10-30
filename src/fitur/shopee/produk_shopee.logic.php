<?php
session_start();
include '../../../aa_kon_sett.php';
require_once __DIR__ . '/lib/ShopeeApiService.php';
require_once __DIR__ . '/lib/helpers.php'; 
require_once __DIR__ . '/../../utils/Logger.php'; 

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$shopeeService = new ShopeeApiService();
$redirect_uri = "https://" . $_SERVER['HTTP_HOST'] . preg_replace('/produk_shopee\.logic\.php$/', 'produk_shopee.php', $_SERVER['PHP_SELF']);

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $shop_id = $_GET['shop_id'] ?? null;
    $main_account_id = $_GET['main_account_id'] ?? null;
    
    $is_main_account = false;
    $id_to_pass = 0;

    if ($shop_id) {
        $id_to_pass = (int)$shop_id;
        $is_main_account = false;
    } elseif ($main_account_id) {
        $id_to_pass = (int)$main_account_id;
        $is_main_account = true;
    }

    if ($id_to_pass > 0) {
        $response = $shopeeService->handleOAuthCallback($code, $id_to_pass, $is_main_account);

        if (is_array($response) && isset($response['error']) && $response['error'] === 'error_param' &&
            isset($response['message']) && $response['message'] === 'Invalid timestamp.') {
            header('Location: ' . strtok($redirect_uri, '?'));
            exit();
        }

        header('Location: ' . strtok($redirect_uri, '?'));
        exit();
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'disconnect') {
    $shopeeService->disconnect();
    header('Location: ' . strtok($redirect_uri, '?'));
    exit();
}

require_once __DIR__ . '/../../component/menu_handler.php';
$menuHandler = new MenuHandler('shopee_dashboard');
if (!$menuHandler->initialize()) {
    exit();
}

$detailed_products = [];
$product_list_response = null;
$auth_url = null;
$page_size = 20;
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_type = isset($_GET['search_type']) ? trim($_GET['search_type']) : 'sku';
$filter_type = isset($_GET['filter']) ? trim($_GET['filter']) : 'all';
$current_offset_raw = $_GET['offset'] ?? 0;
$pagination_info = null;
$total_count = 0;
$has_next_page = false;
$next_offset = 0;
$all_products_from_redis = [];
$redis_error = null;
$redisKey = 'shopee_all_products';

if (!empty($search_keyword)) {
    $current_offset = $current_offset_raw;
    $has_prev_page = ($current_offset_raw != 0);
    $prev_offset = 0;
} else {
    $current_offset = (int)$current_offset_raw;
    $has_prev_page = $current_offset > 0;
    $prev_offset = max(0, $current_offset - $page_size);
}

if ($shopeeService->isConnected()) {
    
    try {
        require_once __DIR__ . '/../../../redis.php';
        if (!isset($redis) || !$redis->ping()) {
            throw new Exception("Koneksi Redis Gagal.");
        }
        
        $cached_products = $redis->get($redisKey);
        
        if ($cached_products) {
            // CACHE HIT: Data ditemukan
            $all_products_from_redis = json_decode($cached_products, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $all_products_from_redis = [];
                // Set error, tapi juga coba sync
                $redis_error = "Gagal decode JSON dari Redis: " . json_last_error_msg() . ". Mencoba sinkronisasi ulang...";
                $cached_products = false; // Paksa masuk ke blok sync di bawah
            }
        } 
        
        // CACHE MISS: Data tidak ditemukan (expired atau gagal decode)
        if (!$cached_products) {
            
            $lockKey = 'shopee_sync_in_progress';
            $lockAcquired = $redis->set($lockKey, 1, ['nx', 'ex' => 1800]); // Lock 30 menit

            if ($lockAcquired) {
                // 1. Lock didapat. User ini yang bertugas sync.
                $logger = new AppLogger('shopee_page_sync.log');
                $logger->info("🚀 Cache miss. Lock '{$lockKey}' didapat. Memulai sinkronisasi dari produk_shopee.php...");
                
                try {
                    // LOGIKA INTI SINKRONISASI (diambil dari cron/api)
                    $all_detailed_products_sync = []; 
                    $offset_sync = 0;
                    $page_size_sync = 50;
                    $total_items_found_sync = 0;
                    $has_next_page_sync = true;

                    while ($has_next_page_sync) {
                        $api_params = [
                            'offset'    => $offset_sync,
                            'page_size' => $page_size_sync,
                            'item_status' => 'NORMAL'
                        ];
                        $product_list_response_sync = $shopeeService->getProductList($api_params);

                        if (isset($product_list_response_sync['error']) && $product_list_response_sync['error']) {
                            $logger->error("❌ Gagal mengambil daftar produk batch offset: {$offset_sync}", $product_list_response_sync);
                            throw new Exception("Gagal mengambil daftar produk dari Shopee: " . $product_list_response_sync['message']);
                        }

                        if (!isset($product_list_response_sync['response']['item']) || empty($product_list_response_sync['response']['item'])) {
                            $logger->info("🏁 Tidak ada item lagi pada offset: {$offset_sync}. Selesai mengambil list.");
                            break;
                        }

                        $detailed_items_batch = $shopeeService->getDetailedProductInfo($product_list_response_sync);
                        
                        if (empty($detailed_items_batch)) {
                            $logger->info("🏁 getDetailedProductInfo mengembalikan kosong untuk offset: {$offset_sync}.");
                            break; 
                        }

                        $all_detailed_products_sync = array_merge($all_detailed_products_sync, $detailed_items_batch);

                        $total_items_found_sync += count($detailed_items_batch);
                        $has_next_page_sync = $product_list_response_sync['response']['has_next_page'] ?? false;
                        $offset_sync = $product_list_response_sync['response']['next_offset'] ?? 0;

                        $logger->info("Batch diproses: " . count($detailed_items_batch) . " produk. Total: {$total_items_found_sync}. Next: {$offset_sync}.");

                        if (!$has_next_page_sync) {
                            break;
                        }
                        usleep(250000); 
                    }

                    $logger->info("✅ Pengambilan data Shopee selesai. Total produk: " . count($all_detailed_products_sync));

                    if (!empty($all_detailed_products_sync)) {
                        $total_products_sync = count($all_detailed_products_sync);
                        $expiry_seconds = 3600; // 1 Jam
                        
                        $logger->info("💾 Meng-encode $total_products_sync produk ke JSON...");
                        $json_data = json_encode($all_detailed_products_sync);
                        
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $logger->error("❌ Gagal encode JSON: " . json_last_error_msg());
                            throw new Exception("Gagal memproses data produk untuk cache.");
                        }

                        $logger->info("💾 Menyimpan $total_products_sync produk ke Redis key: $redisKey dengan TTL: $expiry_seconds detik...");
                        $success = $redis->setex($redisKey, $expiry_seconds, $json_data); 

                        if (!$success) {
                            throw new Exception("Perintah REDIS setex gagal mengembalikan true.");
                        }

                        // Data berhasil di-sync, langsung gunakan
                        $all_products_from_redis = $all_detailed_products_sync;
                        $logger->info("🎉 Sinkronisasi on-page-load selesai.");

                    } else {
                        $logger->warning("Tidak ada produk yang ditemukan di akun Shopee saat sync on-page-load.");
                        $redis_error = "Sinkronisasi selesai, namun tidak ada produk yang ditemukan di akun Shopee.";
                    }

                } catch (Throwable $t_sync) {
                    $logger->critical("🔥 FATAL ERROR saat sync on-page-load: " . $t_sync->getMessage());
                    $redis_error = "Gagal sinkronisasi data produk: " . $t_sync->getMessage();
                } finally {
                    // Selalu lepaskan lock setelah selesai (baik sukses atau gagal)
                    $redis->del($lockKey);
                    $logger->info("Lock '{$lockKey}' dilepaskan.");
                }
                
            } else {
                // 2. Lock GAGAL didapat. User lain sedang sync.
                $redis_error = "Data produk sedang disinkronkan oleh pengguna lain. Halaman ini mungkin menampilkan data yang kedaluwarsa atau kosong. Silakan muat ulang dalam 1-2 menit.";
                $logger = new AppLogger('shopee_page_sync.log');
                $logger->info("Cache miss, tapi lock '{$lockKey}' gagal didapat. Menunggu...");

                // Coba tunggu 5 detik dan cek lagi, siapa tahu sync-nya cepat selesai
                sleep(5); 
                $cached_products_retry = $redis->get($redisKey);
                if ($cached_products_retry) {
                     $all_products_from_redis = json_decode($cached_products_retry, true);
                     if (json_last_error() === JSON_ERROR_NONE) {
                         $redis_error = null; // Sukses! data sudah ada
                         $logger->info("Data cache ditemukan setelah menunggu lock.");
                     }
                } else {
                     $logger->warning("Data cache masih belum ada setelah menunggu 5 detik.");
                }
            }
        }

    } catch (Throwable $t) {
        $redis_error = "Error Redis: " . $t->getMessage();
    }

    if (!empty($all_products_from_redis)) {
        $filtered_products = $all_products_from_redis;

        if (!empty($search_keyword)) {
            $filtered_products = array_filter($filtered_products, function($item) use ($search_keyword, $search_type) {
                if ($search_type === 'name') {
                    if (isset($item['item_name']) && stripos($item['item_name'], $search_keyword) !== false) {
                        return true;
                    }
                } else { 
                    if (isset($item['item_sku']) && stripos($item['item_sku'], $search_keyword) !== false) {
                        return true;
                    }
                    if (isset($item['has_model']) && $item['has_model'] === true && !empty($item['models'])) {
                        foreach ($item['models'] as $model) {
                            if (isset($model['model_sku']) && stripos($model['model_sku'], $search_keyword) !== false) {
                                return true;
                            }
                        }
                    }
                }
                return false;
            });
        }
        
        $total_count = count($filtered_products);
        
        $detailed_products = array_slice(array_values($filtered_products), $current_offset, $page_size);
        
        $next_offset_calc = $current_offset + $page_size;
        if ($next_offset_calc >= $total_count) {
            $has_next_page = false;
            $next_offset = 0;
        } else {
            $has_next_page = true;
            $next_offset = $next_offset_calc;
        }

        $pagination_info = [
            'total_count'   => $total_count,
            'has_next_page' => $has_next_page,
            'next_offset'   => $next_offset
        ];

    } else {
        $detailed_products = [];
        $total_count = 0;
        $pagination_info = null;
        $filtered_products = [];
    }
    
    $all_skus = [];
    $sku_stock_map = [];
    $sku_barang_data_map = [];
    $sku_stok_ol_data_map = [];
    $kd_store = '3190';
    $kd_store_ol = '9998';

    $search_result_count = count($filtered_products);

    foreach ($filtered_products as $product) {
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
    }

    if (!empty($all_skus) && isset($conn) && $conn instanceof mysqli) {
        $unique_skus = array_unique($all_skus);
        $placeholders = implode(',', array_fill(0, count($unique_skus), '?'));
        $types = str_repeat('s', count($unique_skus));
        
        $sql_barang = "SELECT item_n, plu, DESCP, VENDOR, Harga_Beli, Harga_Jual, qty 
                        FROM s_barang 
                        WHERE kd_store = ? AND item_n IN ($placeholders)";
        
        $stmt_barang = $conn->prepare($sql_barang);
        if ($stmt_barang) {
            $stmt_barang->bind_param("s" . $types, $kd_store, ...$unique_skus);
            $stmt_barang->execute();
            $result_barang = $stmt_barang->get_result();
            while ($row = $result_barang->fetch_assoc()) {
                $trimmed_sku = trim($row['item_n']);
                $sku_barang_data_map[$trimmed_sku] = [
                    'plu' => $row['plu'],
                    'descp' => $row['DESCP'],
                    'vendor' => $row['VENDOR'],
                    'harga_beli' => $row['Harga_Beli'],
                    'harga_jual' => $row['Harga_Jual']
                ];
                $sku_stock_map[$trimmed_sku] = (int)$row['qty'];
            }
            $stmt_barang->close();
        }

        $sql_stok_ol = "SELECT item_n, plu, DESCP, VENDOR, hrg_beli, price, Qty 
                        FROM s_stok_ol 
                        WHERE kd_store = ? AND item_n IN ($placeholders)";
        
        $stmt_stok_ol = $conn->prepare($sql_stok_ol);
        if ($stmt_stok_ol) {
            $stmt_stok_ol->bind_param("s" . $types, $kd_store_ol, ...$unique_skus);
            $stmt_stok_ol->execute();
            $result_stok_ol = $stmt_stok_ol->get_result();
            while ($row = $result_stok_ol->fetch_assoc()) {
                $sku_stok_ol_data_map[trim($row['item_n'])] = [
                    'plu' => $row['plu'],
                    'descp' => $row['DESCP'],
                    'vendor' => $row['VENDOR'],
                    'hrg_beli' => $row['hrg_beli'],
                    'price' => $row['price'],
                    'qty' => (int)$row['Qty']
                ];
            }
            $stmt_stok_ol->close();
        }
    }
    
    if ($filter_type == 'all') {
        $total_count = $search_result_count; 
        
        $detailed_products = array_slice(array_values($filtered_products), $current_offset, $page_size);
        
        $next_offset_calc = $current_offset + $page_size;
        if ($next_offset_calc >= $total_count) {
            $has_next_page = false;
            $next_offset = 0;
        } else {
            $has_next_page = true;
            $next_offset = $next_offset_calc;
        }

        $pagination_info = [
            'total_count'   => $total_count,
            'has_next_page' => $has_next_page,
            'next_offset'   => $next_offset
        ];
    } 
    else {
        $final_filtered_products = [];
        foreach ($filtered_products as $item) {
            $show_product = false;
            
            if (isset($item['has_model']) && $item['has_model'] === true && !empty($item['models'])) {
                
                $item['matching_models'] = []; 
                
                foreach ($item['models'] as $model) {
                    $shopee_stock = (int)($model['stock_info_v2']['summary_info']['total_available_stock'] 
                                        ?? $model['stock_info'][0]['seller_stock'] 
                                        ?? 0);
                    $sku = trim($model['model_sku'] ?? '');
                    
                    if (empty($sku)) continue;

                    $model_matches_filter = false; 

                    if ($filter_type == 'pusat' && isset($sku_stok_ol_data_map[$sku])) {
                        $db_stock_ol = (int)($sku_stok_ol_data_map[$sku]['qty'] ?? 0);
                        if ($shopee_stock != $db_stock_ol) {
                            $model_matches_filter = true;
                        }
                    } elseif ($filter_type == 'cabang' && isset($sku_stock_map[$sku]) && !isset($sku_stok_ol_data_map[$sku])) {
                        $db_stock_barang = (int)($sku_stock_map[$sku] ?? 0);
                        if ($shopee_stock != $db_stock_barang) {
                            $model_matches_filter = true;
                        }
                    } elseif ($filter_type == 'beda_harga' && isset($sku_stok_ol_data_map[$sku])) {
                        $shopee_price = (float)($model['price_info'][0]['original_price'] ?? 0);
                        $stok_ol_price = (float)($sku_stok_ol_data_map[$sku]['price'] ?? 0);
                        
                        if (abs($shopee_price - $stok_ol_price) > 0.001) { 
                            $model_matches_filter = true;
                        }
                    } elseif ($filter_type == 'ada_pusat' && isset($sku_stok_ol_data_map[$sku])) { 
                        $model_matches_filter = true;
                    } elseif ($filter_type == 'ada_cabang' && isset($sku_stock_map[$sku]) && !isset($sku_stok_ol_data_map[$sku])) { 
                        $model_matches_filter = true;
                    }

                    if ($model_matches_filter) {
                        $item['matching_models'][] = $model;
                    }
                }

                if (!empty($item['matching_models'])) {
                    $show_product = true; 
                }

            } 
            else { 
                $shopee_stock = (int)($item['stock_info_v2']['summary_info']['total_available_stock'] 
                                    ?? $item['stock_info'][0]['seller_stock'] 
                                    ?? 0);
                $sku = trim($item['item_sku'] ?? '');

                if (!empty($sku)) {
                    if ($filter_type == 'pusat' && isset($sku_stok_ol_data_map[$sku])) {
                        $db_stock_ol = (int)($sku_stok_ol_data_map[$sku]['qty'] ?? 0);
                        if ($shopee_stock != $db_stock_ol) {
                            $show_product = true;
                        }
                    } elseif ($filter_type == 'cabang' && isset($sku_stock_map[$sku]) && !isset($sku_stok_ol_data_map[$sku])) {
                        $db_stock_barang = (int)($sku_stock_map[$sku] ?? 0);
                        if ($shopee_stock != $db_stock_barang) {
                            $show_product = true;
                        }
                    } elseif ($filter_type == 'beda_harga' && isset($sku_stok_ol_data_map[$sku])) {
                        $shopee_price = (float)($item['price_info'][0]['original_price'] ?? 0);
                        $stok_ol_price = (float)($sku_stok_ol_data_map[$sku]['price'] ?? 0);
                        
                        if (abs($shopee_price - $stok_ol_price) > 0.001) { 
                            $show_product = true;
                        }
                    } elseif ($filter_type == 'ada_pusat' && isset($sku_stok_ol_data_map[$sku])) { 
                        $show_product = true;
                    } elseif ($filter_type == 'ada_cabang' && isset($sku_stock_map[$sku]) && !isset($sku_stok_ol_data_map[$sku])) { 
                        $show_product = true;
                    }
                }
            }

            if ($show_product) {
                $final_filtered_products[] = $item;
            }
        }
        
        $total_count = count($final_filtered_products);
        $pagination_threshold = 200; 

        if ($total_count > $pagination_threshold) {
            $detailed_products = array_slice(array_values($final_filtered_products), $current_offset, $page_size);
            
            $next_offset_calc = $current_offset + $page_size;
            if ($next_offset_calc >= $total_count) {
                $has_next_page = false;
                $next_offset = 0;
            } else {
                $has_next_page = true;
                $next_offset = $next_offset_calc;
            }

            $pagination_info = [
                'total_count'   => $total_count,
                'has_next_page' => $has_next_page,
                'next_offset'   => $next_offset
            ];
        } else {
            $detailed_products = array_values($final_filtered_products);
            $pagination_info = null; 
        }
    }
} else {
    $auth_url = $shopeeService->getAuthUrl($redirect_uri);
}
?>