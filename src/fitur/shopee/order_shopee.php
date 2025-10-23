<?php
date_default_timezone_set('UTC');
session_start();
include '../../../aa_kon_sett.php';
require_once __DIR__ . '/lib/ShopeeApiService.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$shopeeService = new ShopeeApiService();
$redirect_uri = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

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
        $shopeeService->handleOAuthCallback($code, $id_to_pass, $is_main_account);
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

$order_list_response = null;
$order_details_list = [];
$auth_url = null;
$error_message = null; 

if ($shopeeService->isConnected()) {
    $time_to = time();
    $time_from = $time_to - (14 * 24 * 60 * 60); 

    $order_list_params = [
        'time_range_field' => 'create_time',
        'time_from' => $time_from,
        'time_to' => $time_to,
        'page_size' => 20,
        'order_status' => 'READY_TO_SHIP',
        'response_optional_fields' => 'order_status',
        'request_order_status_pending' => true,
        'logistics_channel_id' => 91007 
    ];

    $order_list_response = $shopeeService->getOrderList($order_list_params);

    // ddd($order_list_response);
    // die();
    if (isset($order_list_response['error']) &&
        ($order_list_response['error'] === 'invalid_acceess_token' || $order_list_response['error'] === 'invalid_access_token')) {
        
        $shopeeService->disconnect();
        $_SESSION['shopee_flash_message'] = 'Sesi Shopee Anda telah habis (expired). Silakan hubungkan kembali.';
        header('Location: ' . strtok($redirect_uri, '?'));
        exit();
    }

    if (!empty($order_list_response['error'])) {
        $error_message = $order_list_response['message'];
    }

    if (empty($error_message) && isset($order_list_response['response']['order_list']) && !empty($order_list_response['response']['order_list'])) {
        $order_sn_list = array_column($order_list_response['response']['order_list'], 'order_sn');
        
        $order_detail_params = [
            'order_sn_list' => implode(',', $order_sn_list),
            'response_optional_fields' => 'item_list,shipping_carrier,total_amount,cod,create_time,order_status',
            'request_order_status_pending' => true
        ];

        $detail_response = $shopeeService->getOrderDetail($order_detail_params);
        
        if (!empty($detail_response['error'])) {
            $error_message = $detail_response['message']; 
        } elseif (isset($detail_response['response']['order_list'])) {
            $order_details_list = $detail_response['response']['order_list'];

            // === TAMBAHAN KODE: MULAI ===
            // Cek stok untuk menentukan "Ambil Di"
            if (!empty($order_details_list) && isset($conn)) { // $conn dari aa_kon_sett.php
                
                // Siapkan statement SQL di luar loop
                $stmt_stock = $conn->prepare("SELECT qty FROM s_barang WHERE kd_store = 3190 AND item_n = ?");
                
                if ($stmt_stock) {
                    // Loop menggunakan referensi (&) agar bisa memodifikasi array aslinya
                    foreach ($order_details_list as &$order) { 
                        if (isset($order['item_list']) && is_array($order['item_list'])) {
                            foreach ($order['item_list'] as &$item) { 
                                
                                // Tentukan SKU (logic disamakan dgn JS: model_sku > item_sku)
                                $sku = trim($item['model_sku'] ?: ($item['item_sku'] ?: ''));
                                $order_qty = (int)$item['model_quantity_purchased'];
                                
                                // Set nilai default
                                $item['ambil_di'] = ''; 

                                if (!empty($sku)) {
                                    $stmt_stock->bind_param('s', $sku);
                                    $stmt_stock->execute();
                                    $result_stock = $stmt_stock->get_result();
                                    
                                    if ($row_stock = $result_stock->fetch_assoc()) {
                                        $s_barang_qty = (int)$row_stock['qty'];
                                        
                                        // Terapkan logika
                                        if ($order_qty <= $s_barang_qty) {
                                            $item['ambil_di'] = 'ADMB';
                                        }
                                    }
                                    // Jika $row_stock tidak ada, 'ambil_di' tetap empty string
                                }
                            }
                            unset($item); // Hapus referensi item
                        }
                    }
                    unset($order); // Hapus referensi order
                    $stmt_stock->close();
                }
            }
        }

    } 

} else {
    $auth_url = $shopeeService->getAuthUrl($redirect_uri);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Shopee - Order</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="../../style/shopee/shopee.css">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <style>
        .badge-status-ready_to_ship {
            background-color: #fef3c7; 
            color: #b45309; 
            font-weight: 600;
        }
        .badge-cod {
            background-color: #dbeafe; 
            color: #1e40af; 
            font-weight: 600;
        }
        .badge-price {
            background-color: #dcfce7; 
            color: #15803d; 
            font-weight: 600;
        }
    </style>
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
                                <h1 class="text-2xl font-bold text-gray-800 mb-1">Daftar Pesanan</h1>
                                <p class="text-sm text-gray-600">Kelola pesanan yang masuk</p>
                            </div>
                        </div>
                        <?php if ($shopeeService->isConnected()): ?>
                            <a href="?action=disconnect" class="inline-flex items-center gap-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-3 px-6 rounded-xl transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <i class="fas fa-unlink"></i>
                                <span>Disconnect</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($shopeeService->isConnected()): ?>
                    <div class="section-card rounded-2xl overflow-hidden">
                        <div class="section-header p-6">
                                <div class="flex items-center justify-between flex-wrap gap-4"> 
                                    <div>
                                        <h2 class="text-xl font-bold text-gray-800 mb-1">Daftar Order</h2>
                                        <p class="text-sm text-gray-600">Pesanan yang siap dikirim (READY_TO_SHIP) - 14 hari terakhir</p>
                                    </div>
                                    
                                    <?php if (!empty($order_details_list)): ?>
                                        <div class="flex items-center gap-4">
                                            <div class="stats-badge" style="background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); color: #0369a1; border: 1px solid #7dd3fc;">
                                                <i class="fas fa-receipt"></i>
                                                <span><?php echo count($order_details_list); ?> Pesanan</span>
                                            </div>
                                            
                                            <button onclick="exportShopeeOrders()"
                                                    class="bg-gradient-to-r from-yellow-400 to-yellow-600 hover:from-yellow-500 hover:to-yellow-700 text-white px-4 py-2 rounded-xl font-bold shadow-md transition-all duration-200 flex items-center gap-2">
                                                <i class="fas fa-file-excel"></i> Export
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                        </div>

                        <?php if (!empty($error_message)): ?>
                            <div class="p-6">
                                <div class="bg-red-50 border-2 border-red-200 text-red-700 px-6 py-4 rounded-xl" role="alert">
                                    <div class="flex items-center gap-3">
                                        <i class="fas fa-exclamation-circle text-2xl"></i>
                                        <div>
                                            <strong class="font-bold text-lg">Error API!</strong>
                                            <p class="text-sm mt-1"><?php echo htmlspecialchars($error_message); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php elseif (!empty($order_details_list)): ?>
                            <div class="divide-y divide-gray-100">
                                <?php foreach ($order_details_list as $order): ?>
                                    <div class="order-card p-6 hover:bg-gray-50 transition">
                                        <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
                                            <div>
                                                <h3 class="font-bold text-gray-900 text-lg">
                                                    Nomor Pesanan: <?php echo htmlspecialchars($order['order_sn']); ?>
                                                </h3>
                                                <p class="text-sm text-gray-500">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    <?php echo date('d M Y, H:i', $order['create_time']); ?>
                                                </p>
                                            </div>
                                            <div class="flex flex-wrap gap-2">
                                                <span class="stats-badge badge-price">
                                                    <i class="fas fa-tag"></i>
                                                    <span>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></span>
                                                </span>
                                                <span class="stats-badge badge-status-<?php echo strtolower($order['order_status']); ?>">
                                                    <?php echo htmlspecialchars($order['order_status']); ?>
                                                </span>
                                                <?php if ($order['cod']): ?>
                                                    <span class="stats-badge badge-cod">
                                                        <i class="fas fa-hand-holding-usd"></i>
                                                        <span>COD</span>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <p class="text-sm text-gray-700 mb-4 font-medium">
                                            <i class="fas fa-truck mr-2 text-gray-500"></i>
                                            <?php echo htmlspecialchars($order['shipping_carrier']); ?>
                                        </p>

                                        <div class="space-y-4">
                                            <?php foreach ($order['item_list'] as $item): ?>
                                                <div class="flex items-center gap-4 p-4 bg-white rounded-lg border border-gray-200">
                                                    <div class="flex-shrink-0">
                                                        <img src="<?php echo htmlspecialchars($item['image_info']['image_url'] ?? 'https://placehold.co/80x80'); ?>" 
                                                             alt="<?php echo htmlspecialchars($item['item_name']); ?>"
                                                             class="w-16 h-16 object-cover rounded-lg bg-gray-100 border border-gray-200">
                                                    </div>
                                                    <div class="flex-grow min-w-0">
                                                        <p class="font-semibold text-gray-800 line-clamp-2"><?php echo htmlspecialchars($item['item_name']); ?></p>
                                                        <?php if (!empty($item['model_name'])): ?>
                                                            <p class="text-sm text-gray-500">Variasi: <?php echo htmlspecialchars($item['model_name']); ?></p>
                                                        <?php endif; ?>
                                                        <p class="text-sm text-gray-500">SKU: <?php echo htmlspecialchars(trim($item['model_sku']) ?: ($item['item_sku'] ?: 'N/A')); ?></p>
                                                    </div>
                                                    <div class="flex-shrink-0 text-right">
                                                        <p class="font-semibold text-gray-900">
                                                            <?php echo $item['model_quantity_purchased']; ?>x 
                                                            <span class="text-green-700">Rp <?php echo number_format($item['model_discounted_price'], 0, ',', '.'); ?></span>
                                                        </p>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php else: ?>
                            <div class="p-8 text-center">
                                <i class="fas fa-receipt text-6xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500 text-lg font-medium">Tidak ada pesanan ditemukan</p>
                                <p class="text-gray-400 text-sm mt-2">Pesanan (Ready to Ship) akan muncul di sini</p>
                            </div>
                        <?php endif; ?>
                        </div>

                <?php else: ?>
                    <div class="connect-card p-16 rounded-2xl">
                        <div class="max-w-md mx-auto text-center py-4">
                            <div class="icon-wrapper w-24 h-24 mx-auto mb-8 flex items-center justify-center">
                                <img src="../../../public/images/logo/shopee.png" alt="Shopee" class="h-12 w-12">
                            </div>
                            <h2 class="text-3xl font-bold text-gray-800 mb-4">Hubungkan Toko Shopee</h2>
                            <p class="text-gray-600 mb-8 text-lg leading-relaxed">Kelola pesanan Shopee Anda dengan mudah dari satu dashboard yang terintegrasi</p>
                            
                            <?php if(isset($auth_url)): ?>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const profileImg = document.getElementById("profile-img");
            const profileCard = document.getElementById("profile-card");

            if (profileImg && profileCard) {
                profileImg.addEventListener("click", function (event) {
                    event.preventDefault();
                    profileCard.classList.toggle("show");
                });

                document.addEventListener("click", function (event) {
                    if (!profileCard.contains(event.target) && !profileImg.contains(event.target)) {
                        profileCard.classList.remove("show");
                    }
                });
            }
        });
    </script>
    <script>
        const allOrdersData = <?php echo json_encode($order_details_list ?? []); ?>;

        const exportShopeeOrders = () => {
            if (allOrdersData.length === 0) {
                Swal.fire("Tidak Ada Data", "Tidak ada data pesanan untuk diekspor.", "info");
                return;
            }

            try {
                const wb = XLSX.utils.book_new();

                const title = [["Permintaan Barang ke Gudang"]];

                const headers = [
                    "Order SN", "Tanggal Order", "SKU", "Nama Barang", "Variasi",
                    "Qty", "Ambil Di", "Harga Satuan", "Total Pesanan"
                ];

                const dataRows = [];
                const merges = [
                    { s: { r: 0, c: 0 }, e: { r: 0, c: 8 } } 
                ];
                
                let currentRowIndex = 2; 

                allOrdersData.forEach(order => {
                    const orderSN = order.order_sn;
                    const orderDate = new Date(order.create_time * 1000).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' });
                    const totalPesanan = order.total_amount;
                    const numItems = order.item_list.length;

                    order.item_list.forEach((item, itemIndex) => {
                        const sku = (item.model_sku || item.item_sku || '').trim();
                        const namaBarang = item.item_name || '';
                        const variasi = item.model_name || '';
                        const qty = item.model_quantity_purchased;
                        const hargaSatuan = item.model_discounted_price;
                        const ambilDi = item.ambil_di || '';

                        dataRows.push([
                            itemIndex === 0 ? orderSN : "",
                            itemIndex === 0 ? orderDate : "",
                            sku,
                            namaBarang,
                            variasi,
                            qty,
                            ambilDi,
                            hargaSatuan,
                            itemIndex === 0 ? totalPesanan : "",
                            
                        ]);
                    });

                    if (numItems > 1) {
                        const startRow = currentRowIndex;
                        const endRow = currentRowIndex + numItems - 1;
                        merges.push({ s: { r: startRow, c: 0 }, e: { r: endRow, c: 0 } }); 
                        merges.push({ s: { r: startRow, c: 1 }, e: { r: endRow, c: 1 } }); 
                        merges.push({ s: { r: startRow, c: 8 }, e: { r: endRow, c: 8 } });
                    }
                    
                    currentRowIndex += numItems;
                });

                const ws = XLSX.utils.aoa_to_sheet(title); 
                XLSX.utils.sheet_add_aoa(ws, [headers], { origin: "A2" });
                XLSX.utils.sheet_add_aoa(ws, dataRows, { origin: "A3" });

                ws['!merges'] = merges; 

                if (ws['A1']) ws['A1'].s = { 
                    alignment: { horizontal: "center", vertical: "center" } 
                };

                const headerStyle = {
                    font: { bold: true },
                    fill: { fgColor: { rgb: "E0FFFF" } }, 
                    border: {
                        top: { style: "thin" }, bottom: { style: "thin" },
                        left: { style: "thin" }, right: { style: "thin" }
                    },
                    alignment: { vertical: "center" }
                };

                const dataStyle = {
                    border: {
                        top: { style: "thin" }, bottom: { style: "thin" },
                        left: { style: "thin" }, right: { style: "thin" }
                    },
                    alignment: { vertical: "top", wrapText: true }
                };
                
                for (let C = 0; C < headers.length; C++) {
                    const cellRef = XLSX.utils.encode_cell({ r: 1, c: C });
                    if (ws[cellRef]) ws[cellRef].s = headerStyle;
                }

                for (let R = 2; R < dataRows.length + 2; R++) {
                    for (let C = 0; C < headers.length; C++) {
                        const cellRef = XLSX.utils.encode_cell({ r: R, c: C });
                        if (!ws[cellRef]) ws[cellRef] = {};
                        
                        ws[cellRef].s = dataStyle; 

                        if (C === 5) { 
                            ws[cellRef].t = 'n';
                        }
                        if (C === 7 || C === 8) { 
                            if (ws[cellRef].v) { 
                                ws[cellRef].t = 'n';
                                ws[cellRef].s.numFmt = "#,##0";
                            }
                        }
                    }
                }

                ws['!cols'] = [
                    { wch: 20 }, 
                    { wch: 15 }, 
                    { wch: 20 }, 
                    { wch: 50 }, 
                    { wch: 15 }, 
                    { wch: 5 },  
                    { wch: 12 },
                    { wch: 12 }, 
                    { wch: 10 }  
                ];

                XLSX.utils.book_append_sheet(wb, ws, "Permintaan Gudang");
                const fileName = `Permintaan_Barang_ke_Gudang_${new Date().toISOString().split("T")[0]}.xlsx`;
                XLSX.writeFile(wb, fileName);

            } catch (error) {
                console.error("Error exporting data:", error);
                Swal.fire("Export Gagal", "Terjadi kesalahan: " + error.message, "error");
            }
        };
    </script>
</body>
</html>