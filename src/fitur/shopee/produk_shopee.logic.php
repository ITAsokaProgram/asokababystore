<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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

// ... (Bagian OAuth Callback tetap sama, tidak berubah) ...
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $shop_id = $_GET['shop_id'] ?? null;
    $main_account_id = $_GET['main_account_id'] ?? null;
    $is_main_account = false;
    $id_to_pass = 0;
    if ($shop_id) {
        $id_to_pass = (int) $shop_id;
        $is_main_account = false;
    } elseif ($main_account_id) {
        $id_to_pass = (int) $main_account_id;
        $is_main_account = true;
    }
    if ($id_to_pass > 0) {
        $response = $shopeeService->handleOAuthCallback($code, $id_to_pass, $is_main_account);
        if (is_array($response) && isset($response['error']) && $response['error'] === 'error_param' && isset($response['message']) && $response['message'] === 'Invalid timestamp.') {
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
$menuHandler = new MenuHandler('shopee_produk');
if (!$menuHandler->initialize()) {
    exit();
}

$detailed_products = [];
$auth_url = null;
$page_size = 20;

$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_type = isset($_GET['search_type']) ? trim($_GET['search_type']) : 'sku';
$filter_type = isset($_GET['filter']) ? trim($_GET['filter']) : 'all';
$current_offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

$total_count = 0;
$pagination_info = null;
$total_pages = 1;
$current_page = 1;

$kd_store = '3190';
$kd_store_ol = '9998';

if ($shopeeService->isConnected()) {
    if (!isset($conn) || !$conn instanceof mysqli) {
        die("Fatal Error: Koneksi database (\$conn) tidak tersedia. Cek aa_kon_sett.php");
    }

    $check_table = $conn->query("SHOW TABLES LIKE 's_shopee_produk'");
    if ($check_table->num_rows == 0) {
        die("Fatal Error: Tabel <b>s_shopee_produk</b> belum dibuat di database.");
    }

    $sql_core = " FROM s_shopee_produk sp ";
    $joins = "";
    $wheres = " WHERE 1=1 ";
    $params = [];
    $types = "";

    // ... (Bagian Filter Logic tetap sama) ...
    if ($filter_type === 'pusat') {
        $joins .= " JOIN s_stok_ol so ON sp.sku = so.item_n AND so.KD_STORE = ? ";
        $params[] = $kd_store_ol;
        $types .= "s";
        $wheres .= " AND sp.stok != so.Qty ";
    } elseif ($filter_type === 'cabang') {
        $joins .= " JOIN s_barang sb ON sp.sku = sb.item_n AND sb.kd_store = ? ";
        $joins .= " LEFT JOIN s_stok_ol so ON sp.sku = so.item_n AND so.KD_STORE = ? ";
        $params[] = $kd_store;
        $params[] = $kd_store_ol;
        $types .= "ss";
        $wheres .= " AND sp.stok != sb.qty AND so.item_n IS NULL ";
    } elseif ($filter_type === 'beda_harga') {
        $joins .= " JOIN s_stok_ol so ON sp.sku = so.item_n AND so.KD_STORE = ? ";
        $params[] = $kd_store_ol;
        $types .= "s";
        $wheres .= " AND ABS(sp.harga - so.price) > 1 ";
    } elseif ($filter_type === 'ada_pusat') {
        $joins .= " JOIN s_stok_ol so ON sp.sku = so.item_n AND so.KD_STORE = ? ";
        $params[] = $kd_store_ol;
        $types .= "s";
    } elseif ($filter_type === 'ada_cabang') {
        $joins .= " JOIN s_barang sb ON sp.sku = sb.item_n AND sb.kd_store = ? ";
        $joins .= " LEFT JOIN s_stok_ol so ON sp.sku = so.item_n AND so.KD_STORE = ? ";
        $params[] = $kd_store;
        $params[] = $kd_store_ol;
        $types .= "ss";
        $wheres .= " AND so.item_n IS NULL ";
    }

    if (!empty($search_keyword)) {
        $wheres .= " AND (
            sp.kode_produk LIKE ? OR 
            sp.nama_produk LIKE ? OR 
            sp.kode_variasi LIKE ? OR 
            sp.sku_induk LIKE ? OR 
            sp.sku LIKE ?
        ) ";
        $kw = "%" . $search_keyword . "%";
        $params[] = $kw;
        $params[] = $kw;
        $params[] = $kw;
        $params[] = $kw;
        $params[] = $kw;
        $types .= "sssss";
    }

    // Hitung total row (Catatan: ini menghitung baris DB, bukan total produk unik, tapi ok untuk pagination sederhana)
    $sql_count = "SELECT COUNT(*) as total " . $sql_core . $joins . $wheres;
    $stmt_cnt = $conn->prepare($sql_count);
    if (!$stmt_cnt)
        die("Error SQL Count: " . $conn->error);

    if (!empty($params)) {
        $stmt_cnt->bind_param($types, ...$params);
    }

    $stmt_cnt->execute();
    $res_cnt = $stmt_cnt->get_result()->fetch_assoc();
    $total_count = $res_cnt['total'] ?? 0;
    $stmt_cnt->close();

    // -----------------------------------------------------------
    // PERUBAHAN UTAMA DI SINI (ORDER BY)
    // Kita tambahkan kode_produk agar baris dengan produk sama selalu berurutan
    // -----------------------------------------------------------
    $sql_data = "SELECT sp.* " . $sql_core . $joins . $wheres . " ORDER BY sp.nama_produk ASC, sp.kode_produk ASC LIMIT ?, ?";
    $params[] = $current_offset;
    $params[] = $page_size;
    $types .= "ii";

    $stmt_data = $conn->prepare($sql_data);
    if (!$stmt_data)
        die("Error SQL Data: " . $conn->error);

    $stmt_data->bind_param($types, ...$params);
    $stmt_data->execute();
    $result_data = $stmt_data->get_result();

    $all_skus = [];

    // Array sementara untuk grouping berdasarkan kode_produk
    $grouped_data = [];

    while ($row = $result_data->fetch_assoc()) {
        $prod_id = $row['kode_produk'];
        $is_variant = ($row['kode_variasi'] != 0);

        // Jika produk ini belum ada di array sementara, buat parent-nya
        if (!isset($grouped_data[$prod_id])) {
            $grouped_data[$prod_id] = [
                'item_id' => (int) $row['kode_produk'],
                'item_name' => $row['nama_produk'],
                'item_sku' => $row['sku_induk'], // Gunakan SKU induk
                'has_model' => false, // Default false, nanti diupdate jika ketemu variasi
                'harga_beli' => $row['harga_beli'], // Default dari row pertama yg ketemu
                'keterangan' => $row['keterangan'],
                'image' => [
                    'image_url_list' => [$row['image_url']]
                ],
                'price_info' => [
                    [
                        'currency' => 'IDR',
                        'original_price' => (float) $row['harga']
                    ]
                ],
                'stock_info_v2' => [
                    'summary_info' => [
                        'total_available_stock' => 0 // Kita hitung manual nanti
                    ]
                ],
                'calculated_total_stock' => 0,
                'models' => [],
                'matching_models' => []
            ];
        }

        // Logic Penggabungan Data (Grouping)
        if ($is_variant) {
            // Ini adalah variasi (Model)
            $grouped_data[$prod_id]['has_model'] = true;

            $model = [
                'model_id' => (int) $row['kode_variasi'],
                'model_name' => $row['nama_variasi'],
                'model_sku' => $row['sku'],
                'harga_beli' => $row['harga_beli'],
                'keterangan' => $row['keterangan'],
                'price_info' => [['original_price' => (float) $row['harga']]],
                'stock_info_v2' => ['summary_info' => ['total_available_stock' => (int) $row['stok']]],
                'stock_info' => [['seller_stock' => (int) $row['stok']]]
            ];

            // Masukkan ke array models parent
            $grouped_data[$prod_id]['models'][] = $model;
            // Masukkan ke matching models (berguna jika nanti ada filter spesifik variasi)
            $grouped_data[$prod_id]['matching_models'][] = $model;

            // Tambahkan stok variasi ke stok total parent
            $grouped_data[$prod_id]['calculated_total_stock'] += (int) $row['stok'];

            // Collect SKU
            if (!empty($row['sku']))
                $all_skus[] = $row['sku'];

        } else {
            // Ini adalah produk single (tanpa variasi)
            // Update data parent dengan data row ini
            $grouped_data[$prod_id]['item_sku'] = $row['sku']; // SKU diri sendiri
            $grouped_data[$prod_id]['calculated_total_stock'] = (int) $row['stok'];
            $grouped_data[$prod_id]['stock_info_v2']['summary_info']['total_available_stock'] = (int) $row['stok'];

            // Jika single produk, kadang harga beli/keterangan di row ini yg paling update
            $grouped_data[$prod_id]['harga_beli'] = $row['harga_beli'];
            $grouped_data[$prod_id]['keterangan'] = $row['keterangan'];

            if (!empty($row['sku']))
                $all_skus[] = $row['sku'];
        }
    }

    // Convert associative array back to indexed array untuk view
    $detailed_products = array_values($grouped_data);

    $stmt_data->close();

    $total_pages = ($total_count > 0) ? ceil($total_count / $page_size) : 1;
    $current_page = floor($current_offset / $page_size) + 1;
    if ($current_page > $total_pages)
        $current_page = $total_pages;
    if ($current_page < 1)
        $current_page = 1;

    $pagination_info = [
        'total_count' => $total_count,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'page_size' => $page_size
    ];

    $sku_stock_map = [];
    $sku_barang_data_map = [];
    $sku_stok_ol_data_map = [];

    // ... (Sisa logic pengambilan data s_barang dan s_stok_ol tetap sama) ...
    if (!empty($all_skus)) {
        $unique_skus = array_unique($all_skus);
        $chunked_skus = array_chunk($unique_skus, 100);

        foreach ($chunked_skus as $chunk) {
            $placeholders = implode(',', array_fill(0, count($chunk), '?'));
            $types_b = str_repeat('s', count($chunk));

            $sql_barang = "SELECT item_n, plu, DESCP, VENDOR, Harga_Beli, Harga_Jual, qty FROM s_barang WHERE kd_store = ? AND item_n IN ($placeholders)";
            $stmt_b = $conn->prepare($sql_barang);
            if ($stmt_b) {
                $stmt_b->bind_param("s" . $types_b, $kd_store, ...$chunk);
                $stmt_b->execute();
                $res_b = $stmt_b->get_result();
                while ($row = $res_b->fetch_assoc()) {
                    $sku_trim = trim($row['item_n']);
                    $sku_barang_data_map[$sku_trim] = [
                        'plu' => $row['plu'],
                        'descp' => $row['DESCP'],
                        'vendor' => $row['VENDOR'],
                        'harga_beli' => $row['Harga_Beli'],
                        'harga_jual' => $row['Harga_Jual']
                    ];
                    $sku_stock_map[$sku_trim] = (int) $row['qty'];
                }
                $stmt_b->close();
            }

            $sql_ol = "SELECT item_n, plu, DESCP, VENDOR, hrg_beli, price, Qty FROM s_stok_ol WHERE kd_store = ? AND item_n IN ($placeholders)";
            $stmt_ol = $conn->prepare($sql_ol);
            if ($stmt_ol) {
                $stmt_ol->bind_param("s" . $types_b, $kd_store_ol, ...$chunk);
                $stmt_ol->execute();
                $res_ol = $stmt_ol->get_result();
                while ($row = $res_ol->fetch_assoc()) {
                    $sku_trim = trim($row['item_n']);
                    $sku_stok_ol_data_map[$sku_trim] = [
                        'plu' => $row['plu'],
                        'descp' => $row['DESCP'],
                        'vendor' => $row['VENDOR'],
                        'hrg_beli' => $row['hrg_beli'],
                        'price' => $row['price'],
                        'qty' => (int) $row['Qty']
                    ];
                }
                $stmt_ol->close();
            }
        }
    }

} else {
    $auth_url = $shopeeService->getAuthUrl($redirect_uri);
}

$search_result_count = $total_count;
?>