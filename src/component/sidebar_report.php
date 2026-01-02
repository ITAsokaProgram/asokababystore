<?php

require_once __DIR__ . '/../config/JWT/JWT.php';
require_once __DIR__ . '/../config/JWT/Key.php';
require_once __DIR__ . '/../config/JWT/config.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$isSuperAdmin = false;

if (isset($_COOKIE['admin_token'])) {
    try {
        $decoded = JWT::decode($_COOKIE['admin_token'], new Key(JWT_SECRET_KEY, 'HS256'));
        if ($decoded->role === 'Superadmin') {
            $isSuperAdmin = true;
        }
    } catch (Exception $e) {
        $isSuperAdmin = false;
    }
}

$uri = $_SERVER['REQUEST_URI'];
$isActive = function ($path) use ($uri) {
    return strpos($uri, $path) !== false;
};

$isVoucherOpen = $isActive('/src/fitur/voucher/');
$isJadwalSoOpen = $isActive('/src/fitur/laporan/jadwal_so/');
$isReceiptToolOpen = $isActive('/src/fitur/receipt/');
$isReturnToolOpen = $isActive('/src/fitur/return/');
$isKoreksiToolOpen = $isActive('/src/fitur/koreksi/');
$isUangBrankasOpen = $isActive('/src/fitur/uang_brangkas/');

$isToolsOpen = $isVoucherOpen || $isJadwalSoOpen || $isActive('/src/fitur/approval/izin') || $isReceiptToolOpen || $isReturnToolOpen || $isKoreksiToolOpen || $isUangBrankasOpen;

$isPenjualanOpen = $isActive('/src/fitur/laporan/in_laporan_sub_dept') ||
    $isActive('/src/fitur/laporan/in_sales_ratio') ||
    $isActive('/src/fitur/laporan/in_sales_category') ||
    $isActive('/src/fitur/laporan/in_transaction') ||
    $isActive('/src/fitur/top_sales/');

$isPelangganOpen = $isActive('/src/fitur/laporan/in_customer') || $isActive('/src/fitur/laporan/layanan') || $isActive('/src/fitur/laporan/in_review_cust');
$isReceiptOpen = $isActive('/src/fitur/penerimaan_receipt/');
$isReturnOpen = $isActive('/src/fitur/return_out/');
$isMutasiOpen = $isActive('/src/fitur/mutasi_in/');
$isTransaksiOpen = $isActive('/src/fitur/transaction/');
$isKoreksiOpen = $isActive('/src/fitur/koreksi_stok/') || $isActive('/src/fitur/koreksi_so/');

$isLaporanOpen = $isPenjualanOpen || $isPelangganOpen || $isReceiptOpen || $isReturnOpen || $isMutasiOpen || $isTransaksiOpen || $isKoreksiOpen || $isActive('/src/fitur/log_backup/') || $isActive('/src/fitur/stok/') || $isActive('/src/fitur/logs/password_reset.php');

$isPembelianOpen = $isActive('/src/fitur/coretax/input_pembelian') || $isActive('/src/fitur/coretax/laporan_pembelian');
$isPengeluaranOpen = $isActive('/src/fitur/coretax/data_coretax_keluaran.php') || $isActive('/src/fitur/coretax/import_faktur_keluaran.php'); // Tambahan Baru
$isMasukanOpen = $isActive('/src/fitur/coretax/data_coretax.php') || $isActive('/src/fitur/coretax/import_faktur.php');
$isFakturOpen = $isActive('/src/fitur/coretax/input_faktur') || $isActive('/src/fitur/coretax/laporan_faktur') || $isActive('/src/fitur/coretax/import_faktur');
$isLainnyaOpen = $isActive('/src/fitur/coretax/data_coretax') || $isActive('/src/fitur/coretax/faktur_masukan');

$isPajakOpen = $isActive('/src/fitur/coretax/');
$isWhatsappOpen = $isActive('/src/fitur/whatsapp_cs/') || $isActive('/src/fitur/whatsapp/');

$isBukuBesarOpen = $isActive('/src/fitur/buku_besar/input_buku_besar') || $isActive('/src/fitur/buku_besar/laporan_buku_besar');
$isSerahTerimaOpen = $isActive('/src/fitur/finance/laporan_serah_terima_nota.php') || $isActive('/src/fitur/finance/input_serah_terima_nota.php');
$isFinanceOpen = $isBukuBesarOpen || $isSerahTerimaOpen; // Update logika induk

?>

<div id="sidebar"
    class="bg-white text-gray-700 w-64 h-screen flex flex-col p-6 fixed left-0 top-0 transition-all duration-300 shadow-2xl border-r border-blue-200 z-40">
    <button id="closeSidebar"
        class="absolute top-2 right-4 text-gray-600 hover:text-gray-800 text-2xl font-bold transition-colors duration-200 hover:scale-110">&times;</button>
    <nav class="text-sm mt-20 space-y-2 flex-1 overflow-y-auto">

        <a href="/in_beranda" id="berandaLink" data-menu="dashboard" data-title="Beranda"
            class="group flex items-center py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-blue-100 hover:to-blue-200 hover:text-blue-700 hover:shadow-lg transition-all duration-300  border border-transparent hover:border-blue-300">
            <div class="w-8 flex justify-center">
                <i
                    class="fas fa-home text-xl text-blue-600 group-hover:text-blue-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
            </div>
            <span
                class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">Beranda</span>
        </a>
        <div id="whatsappMenuContainer" x-data="{ open: <?= $isWhatsappOpen ? 'true' : 'false' ?> }" class="relative"
            style="display: none;">
            <button @click="open = !open" id="whatsappToggle" data-title="WhatsApp"
                class="group flex items-center w-full py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-green-100 hover:to-green-200 hover:text-green-700 hover:shadow-lg transition-all duration-300 cursor-pointer focus:outline-none border border-transparent hover:border-green-300">
                <div class="w-8 flex justify-center">
                    <i
                        class="fa-brands fa-whatsapp text-xl text-green-600 group-hover:text-green-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
                </div>
                <span
                    class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">WhatsApp
                    CS</span>
                <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="open" @click.away="open = false"
                class="mt-3 ml-4 bg-gradient-to-br from-white to-green-50 rounded-xl shadow-xl border border-green-200 z-10 backdrop-blur-sm"
                style="display: none;">
                <ul class="py-2 space-y-1">
                    <li>
                        <a href="/src/fitur/whatsapp/dashboard_whatsapp" data-menu="whatsapp_dashboard"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-green-100 hover:text-green-700 transition-all duration-200 group rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i
                                    class="fa-solid fa-gauge-high mr-2 text-base text-green-400 group-hover:text-green-600 group-hover:scale-110 transition-all duration-200"></i>
                                Dashboard
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="/src/fitur/whatsapp/kelola_balasan_otomatis.php" data-menu="whatsapp_autoreply"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-green-100 hover:text-green-700 transition-all duration-200 group rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i
                                    class="fa-solid fa-robot mr-2 text-base text-green-400 group-hover:text-green-600 group-hover:scale-110 transition-all duration-200"></i>
                                Balasan Otomatis
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="/src/fitur/whatsapp/kelola_dynamic_flow.php" data-menu="whatsapp_dynamic_flow"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-green-100 hover:text-green-700 transition-all duration-200 group rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i
                                    class="fa-solid fa-diagram-project mr-2 text-base text-green-400 group-hover:text-green-600 group-hover:scale-110 transition-all duration-200"></i>
                                Alur Percakapan Otomatis
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div x-data="{ open: false }" class="relative " @reset-menu.window="open = false">
            <button @click="open = !open" id="shopeeLink" data-title="Shopee"
                class="group flex items-center w-full py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-orange-100 hover:to-red-200 hover:text-orange-700 hover:shadow-lg transition-all duration-300 cursor-pointer focus:outline-none border border-transparent hover:border-orange-300">
                <div class="w-8 flex justify-center">
                    <i
                        class="fas fa-shopping-bag text-xl text-orange-600 group-hover:text-orange-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
                </div>
                <span
                    class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">Shopee</span>
                <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>


            <div x-show="open" @click.away="open = false"
                class="mt-3 ml-4 bg-gradient-to-br from-white to-orange-50 rounded-xl shadow-xl border border-orange-200 z-10 backdrop-blur-sm"
                style="display: none;">
                <ul class="py-2 space-y-1">
                    <li>
                        <a href="/src/fitur/shopee/dashboard_shopee" data-menu="shopee_dashboard"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-orange-100 hover:text-orange-700 transition-all duration-200 group rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i
                                    class="fa-solid fa-tachometer-alt mr-2 text-base text-orange-400 group-hover:text-orange-600 group-hover:scale-110 transition-all duration-200"></i>
                                Dashboard
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="/src/fitur/shopee/produk_shopee" data-menu="shopee_produk"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-orange-100 hover:text-orange-700 transition-all duration-200 group rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i
                                    class="fa-solid fa-box mr-2 text-base text-orange-400 group-hover:text-orange-600 group-hover:scale-110 transition-all duration-200"></i>
                                Produk
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="/src/fitur/shopee/order_shopee" data-menu="shopee_order"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-orange-100 hover:text-orange-700 transition-all duration-200 group rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i
                                    class="fa-solid fa-receipt mr-2 text-base text-orange-400 group-hover:text-orange-600 group-hover:scale-110 transition-all duration-200"></i>
                                Order
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="/src/fitur/shopee/terima_barang" data-menu="shopee_order"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-orange-100 hover:text-orange-700 transition-all duration-200 group rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i
                                    class="fa-solid fa-inbox mr-2 text-base text-orange-400 group-hover:text-orange-600 group-hover:scale-110 transition-all duration-200"></i>
                                Terima Barang
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div x-data="{ 
                open: <?= $isLaporanOpen ? 'true' : 'false' ?>, 
                nestedOpenPenjualan: <?= $isPenjualanOpen ? 'true' : 'false' ?>, 
                nestedOpenPelanggan: <?= $isPelangganOpen ? 'true' : 'false' ?>, 
                nestedOpenReceipt: <?= $isReceiptOpen ? 'true' : 'false' ?>, 
                nestedOpenReturn: <?= $isReturnOpen ? 'true' : 'false' ?>, 
                nestedOpenMutasi: <?= $isMutasiOpen ? 'true' : 'false' ?>, 
                nestedOpenTransaksi: <?= $isTransaksiOpen ? 'true' : 'false' ?>, 
                nestedOpenKoreksi: <?= $isKoreksiOpen ? 'true' : 'false' ?>, 
                nestedOpenKoreksiSO: false 
            }" class="relative "
            @reset-menu.window="open = false; nestedOpenPenjualan = false; nestedOpenPelanggan = false; nestedOpenReceipt = false; nestedOpenReturn = false; nestedOpenMutasi = false; nestedOpenTransaksi = false; nestedOpenKoreksi = false; nestedOpenKoreksiSO = false">
            <button @click="open = !open" id="laporan" data-title="Laporan"
                class="group flex items-center w-full py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-purple-100 hover:to-purple-200 hover:text-purple-700 hover:shadow-lg transition-all duration-300 cursor-pointer focus:outline-none border border-transparent hover:border-purple-300">
                <div class="w-8 flex justify-center">
                    <i
                        class="fa fa-book text-xl text-purple-600 group-hover:text-purple-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
                </div>
                <span
                    class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">Laporan</span>
                <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="open" @click.away="open = false"
                class="mt-3 ml-4 bg-gradient-to-br from-white to-purple-50 rounded-xl shadow-xl border border-purple-200 z-10 backdrop-blur-sm"
                style="display: none;">
                <ul class="py-2">
                    <li>
                        <button @click="nestedOpenPenjualan = !nestedOpenPenjualan"
                            class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-green-100 hover:text-green-700 transition-all duration-200 flex items-center group cursor-pointer rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center">
                                <i
                                    class="fa-solid fa-chart-line mr-2 text-lg text-green-500 group-hover:text-green-600 transition-all duration-200 group-hover:scale-110"></i>
                                Penjualan
                            </span>
                            <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                                :class="{ 'rotate-180': nestedOpenPenjualan }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="nestedOpenPenjualan" @click.away="nestedOpenPenjualan = false" class="ml-4 mt-1"
                            style="display: none;">
                            <ul
                                class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-2 space-y-1 border border-green-200">
                                <li>
                                    <a href="/src/fitur/laporan/in_laporan_sub_dept"
                                        data-menu="laporan_penjualan_subdept"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-regular fa-building mr-2 text-base text-green-400 group-hover:text-green-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Sub Dept
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/laporan/in_sales_ratio" data-menu="laporan_penjualan_salesratio"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-percent mr-2 text-base text-green-400 group-hover:text-green-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Sales Ratio
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/laporan/in_sales_category"
                                        data-menu="laporan_penjualan_kategori"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-layer-group mr-2 text-base text-green-400 group-hover:text-green-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Kategori
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/laporan/in_transaction" data-menu="laporan_penjualan_mnonm"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-barcode mr-2 text-base text-green-400 group-hover:text-green-600 group-hover:scale-110 transition-all duration-200"></i>
                                            M / Non M
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/top_sales/by_rupiah" data-menu="laporan_topsales_rupiah"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-dollar-sign mr-2 text-base text-green-400 group-hover:text-green-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Top Sales (Rp)
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/top_sales/by_qty" data-menu="laporan_topsales_qty"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-boxes-stacked mr-2 text-base text-green-400 group-hover:text-green-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Top Sales (Qty)
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/top_sales/by_supplier" data-menu="laporan_topsales_supplier"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-truck-field mr-2 text-base text-green-400 group-hover:text-green-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Top Sales (Supplier)
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/top_sales/sales_per_kasir_bon"
                                        data-menu="laporan_topsales_kasir"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-cash-register mr-2 text-base text-green-400 group-hover:text-green-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Sales per Kasir
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <button @click="nestedOpenPelanggan = !nestedOpenPelanggan"
                            class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-yellow-100 hover:text-yellow-700 transition-all duration-200 flex items-center group cursor-pointer rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center">
                                <i
                                    class="fa-solid fa-users mr-2 text-lg text-yellow-500 group-hover:text-yellow-600 transition-all duration-200 group-hover:scale-110"></i>
                                Pelanggan
                            </span>
                            <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                                :class="{ 'rotate-180': nestedOpenPelanggan }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="nestedOpenPelanggan" @click.away="nestedOpenPelanggan = false" class="ml-4 mt-1"
                            style="display: none;">
                            <ul
                                class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-lg p-2 space-y-1 border border-yellow-200">
                                <li>
                                    <a href="/src/fitur/laporan/in_customer" data-menu="laporan_pelanggan_aktifitas"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-shopping-bag mr-2 text-base text-yellow-400 group-hover:text-yellow-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Aktivitas Belanja
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/laporan/layanan" data-menu="laporan_pelanggan_layanan"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-headset mr-2 text-base text-yellow-400 group-hover:text-yellow-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Layanan
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/laporan/in_review_cust" data-menu="laporan_pelanggan_review"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-star mr-2 text-base text-yellow-400 group-hover:text-yellow-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Review
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <button @click="nestedOpenReceipt = !nestedOpenReceipt"
                            class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-blue-100 hover:text-blue-700 transition-all duration-200 flex items-center group cursor-pointer rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center">
                                <i
                                    class="fa-solid fa-file-invoice-dollar mr-2 text-lg text-blue-500 group-hover:text-blue-600 transition-all duration-200 group-hover:scale-110"></i>
                                Penerimaan
                            </span>
                            <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                                :class="{ 'rotate-180': nestedOpenReceipt }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="nestedOpenReceipt" @click.away="nestedOpenReceipt = false" class="ml-4 mt-1"
                            style="display: none;">
                            <ul
                                class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-2 space-y-1 border border-blue-200">
                                <li>
                                    <a href="/src/fitur/penerimaan_receipt/detail" data-menu="laporan_receipt_detail"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-list-ol mr-2 text-base text-blue-400 group-hover:text-blue-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Detail Receipt
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/penerimaan_receipt/by_supplier"
                                        data-menu="laporan_receipt_supplier"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-truck mr-2 text-base text-blue-400 group-hover:text-blue-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Receipt by Supplier
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <button @click="nestedOpenReturn = !nestedOpenReturn"
                            class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-yellow-100 hover:text-yellow-700 transition-all duration-200 flex items-center group cursor-pointer rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center">
                                <i
                                    class="fa-solid fa-undo mr-2 text-lg text-yellow-500 group-hover:text-yellow-600 transition-all duration-200 group-hover:scale-110"></i>
                                Return Out
                            </span>
                            <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                                :class="{ 'rotate-180': nestedOpenReturn }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="nestedOpenReturn" @click.away="nestedOpenReturn = false" class="ml-4 mt-1"
                            style="display: none;">
                            <ul
                                class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-lg p-2 space-y-1 border border-yellow-200">
                                <li>
                                    <a href="/src/fitur/return_out/all_item" data-menu="laporan_return_all"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-boxes-alt mr-2 text-base text-yellow-400 group-hover:text-yellow-600 group-hover:scale-110 transition-all duration-200"></i>
                                            All Item
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/return_out/bad_stock" data-menu="laporan_return_badstock"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">

                                            <i
                                                class="fa-solid fa-box-open mr-2 text-base text-yellow-400 group-hover:text-yellow-600 group-hover:scale-110 transition-all duration-200"></i>

                                            Bad Stock
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/return_out/exp_produk" data-menu="laporan_return_exp"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-calendar-times mr-2 text-base text-yellow-400 group-hover:text-yellow-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Expired
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/return_out/hilang_pasangan" data-menu="laporan_return_hilang"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-unlink mr-2 text-base text-yellow-400 group-hover:text-yellow-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Hilang Pasangan
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li>
                        <button @click="nestedOpenMutasi = !nestedOpenMutasi"
                            class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-fuchsia-100 hover:text-fuchsia-700 transition-all duration-200 flex items-center group cursor-pointer rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center">
                                <i
                                    class="fa-solid fa-right-left mr-2 text-lg text-fuchsia-500 group-hover:text-fuchsia-600 transition-all duration-200 group-hover:scale-110"></i>
                                Mutasi
                            </span>
                            <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                                :class="{ 'rotate-180': nestedOpenMutasi }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="nestedOpenMutasi" @click.away="nestedOpenMutasi = false" class="ml-4 mt-1"
                            style="display: none;">
                            <ul
                                class="bg-gradient-to-br from-fuchsia-50 to-pink-50 rounded-lg p-2 space-y-1 border border-fuchsia-200">
                                <li>
                                    <a href="/src/fitur/mutasi_in/index.php" data-menu="laporan_mutasi_in"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-right-to-bracket mr-2 text-base text-fuchsia-400 group-hover:text-fuchsia-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Mutasi Invoice
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li>
                        <button @click="nestedOpenTransaksi = !nestedOpenTransaksi" id="transaction"
                            class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-orange-100 hover:text-orange-700 transition-all duration-200 flex items-center group cursor-pointer rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center">
                                <i
                                    class="fa-solid fa-money-bill-transfer mr-2 text-lg text-orange-500 group-hover:text-orange-600 transition-all duration-200 group-hover:scale-110"></i>
                                Transaksi
                            </span>
                            <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                                :class="{ 'rotate-180': nestedOpenTransaksi }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="nestedOpenTransaksi" @click.away="nestedOpenTransaksi = false" class="ml-4 mt-1"
                            style="display: none;">
                            <ul
                                class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-lg p-2 space-y-1 border border-orange-200">
                                <li>
                                    <a href="/src/fitur/transaction/view_promo" data-menu="transaksi_promo"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-orange-100 hover:text-orange-700 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-percent mr-2 text-base text-orange-400 group-hover:text-orange-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Promo
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/transaction/invalid_trans" data-menu="transaksi_invalid"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-orange-100 hover:text-orange-700 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-ban mr-2 text-base text-orange-400 group-hover:text-orange-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Invalid
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/transaction/margin" data-menu="transaksi_margin"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-orange-100 hover:text-orange-700 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-coins mr-2 text-base text-orange-400 group-hover:text-orange-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Margin
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/transaction/reward_give" data-menu="reward_give"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-orange-100 hover:text-orange-700 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-gift mr-2 text-base text-orange-400 group-hover:text-orange-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Hadiah
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <button @click="nestedOpenKoreksi = !nestedOpenKoreksi"
                            class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-red-100 hover:text-red-700 transition-all duration-200 flex items-center group cursor-pointer rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center">
                                <i
                                    class="fa-solid fa-edit mr-2 text-lg text-red-500 group-hover:text-red-600 transition-all duration-200 group-hover:scale-110"></i>
                                Koreksi Stok
                            </span>
                            <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                                :class="{ 'rotate-180': nestedOpenKoreksi }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="nestedOpenKoreksi" @click.away="nestedOpenKoreksi = false" class="ml-4 mt-1"
                            style="display: none;">
                            <ul
                                class="bg-gradient-to-br from-red-50 to-pink-50 rounded-lg p-2 space-y-1 border border-red-200">
                                <li>
                                    <a href="/src/fitur/koreksi_stok/by_supplier" data-menu="laporan_koreksi_supplier"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-truck-fast mr-2 text-base text-red-400 group-hover:text-red-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Koreksi (Supplier)
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/koreksi_stok/by_plu" data-menu="laporan_koreksi_plu"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-barcode mr-2 text-base text-red-400 group-hover:text-red-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Koreksi (PLU)
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/koreksi_so/index.php" data-menu="koreksi_so"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-list mr-2 text-base text-red-400 group-hover:text-red-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Koreksi SO
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/koreksi_so/report_missed.php" data-menu="koreksi_so_missed"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-circle-exclamation mr-2 text-base text-red-400 group-hover:text-red-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Belum Koreksi
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <a href="/src/fitur/stok/index.php" data-menu="laporan_stok"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-indigo-100 hover:text-indigo-700 transition-all duration-200 group rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center">
                                <i
                                    class="fa-solid fa-warehouse mr-2 text-lg text-indigo-500 group-hover:text-indigo-600 transition-all duration-200 group-hover:scale-110"></i>
                                Stok
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="/src/fitur/log_backup/index.php" data-menu="laporan_log_backup"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-blue-100 hover:text-blue-700 transition-all duration-200 group rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center">
                                <i
                                    class="fa-solid fa-database mr-2 text-lg text-blue-500 group-hover:text-blue-600 transition-all duration-200 group-hover:scale-110"></i>
                                Log Backup
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="/src/fitur/logs/password_reset.php" data-menu="laporan_log_password"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-red-100 hover:text-red-700 transition-all duration-200 group rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center">
                                <i
                                    class="fa-solid fa-key mr-2 text-lg text-red-500 group-hover:text-red-600 transition-all duration-200 group-hover:scale-110"></i>
                                Log Password Reset
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div x-data="{ open: false, nestedOpenMember: false, nestedOpenMember: false }" class="relative ">
            <button @click="open = !open" id="member" data-title="Member"
                class="group flex items-center w-full py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-teal-100 hover:to-teal-200 hover:text-teal-700 hover:shadow-lg transition-all duration-300 cursor-pointer focus:outline-none border border-transparent hover:border-teal-300">
                <div class="w-8 flex justify-center">
                    <i
                        class="fa-solid fa-id-card text-xl text-teal-600 group-hover:text-teal-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
                </div>
                <span
                    class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">Member</span>
                <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="open" @click.away="open = false"
                class="mt-3 ml-4 bg-gradient-to-br from-white to-teal-50 rounded-xl shadow-xl border border-teal-200 z-10 backdrop-blur-sm"
                style="display: none;">
                <ul class="py-2">
                    <li>
                        <a href="/src/fitur/member/manage" data-menu="member_poin"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-teal-100 hover:text-teal-700 transition-all duration-200 group rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i
                                    class="fa-solid fa-coins mr-2 text-base text-teal-400 group-hover:text-teal-600 group-hover:scale-110 transition-all duration-200"></i>
                                Dashboard
                            </span>
                        </a>
                    </li>
                </ul>
                <ul class="py-2">
                    <li>
                        <a href="/src/fitur/member/poin_member" data-menu="member_poin"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-teal-100 hover:text-teal-700 transition-all duration-200 group rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i
                                    class="fa-solid fa-coins mr-2 text-base text-teal-400 group-hover:text-teal-600 group-hover:scale-110 transition-all duration-200"></i>
                                Poin
                            </span>
                        </a>
                    </li>
                </ul>

                <ul class="py-2">
                    <li>
                        <a href="/src/fitur/member/management_member" data-menu="member_poin"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-teal-100 hover:text-teal-700 transition-all duration-200 group rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i
                                    class="fa-solid fa-coins mr-2 text-base text-teal-400 group-hover:text-teal-600 group-hover:scale-110 transition-all duration-200"></i>
                                Kelola
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <?php if ($isSuperAdmin): // HANYA TAMPIL JIKA SUPERADMIN ?>
            <div x-data="{ open: false, nestedOpenAccount: false }" class="relative ">
                <button @click="open = !open" id="account" data-title="Akun"
                    class="group flex items-center w-full py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-indigo-100 hover:to-indigo-200 hover:text-indigo-700 hover:shadow-lg transition-all duration-300 cursor-pointer focus:outline-none border border-transparent hover:border-indigo-300">
                    <div class="w-8 flex justify-center">
                        <i
                            class="fa-solid fa-user text-xl text-indigo-600 group-hover:text-indigo-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
                    </div>
                    <span
                        class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">Account</span>
                    <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                        :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div x-show="open" @click.away="open = false"
                    class="mt-3 ml-4 bg-gradient-to-br from-white to-indigo-50 rounded-xl shadow-xl border border-indigo-200 z-10 backdrop-blur-sm"
                    style="display: none;">
                    <ul class="py-2 space-y-1">
                        <li>
                            <a href="/src/fitur/account/in_new_user" data-menu="insert_new_user"
                                class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-indigo-100 hover:text-indigo-700 transition-all duration-200 group rounded-lg">
                                <span
                                    class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                    <i
                                        class="fa-solid fa-user-plus mr-2 text-base text-indigo-400 group-hover:text-indigo-600 group-hover:scale-110 transition-all duration-200"></i>
                                    Tambah Anggota
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="/src/fitur/account/manajemen_user" data-menu="user_management"
                                class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-all duration-200 group rounded-lg">
                                <span
                                    class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                    <i
                                        class="fa-solid fa-users-cog mr-2 text-base text-indigo-400 group-hover:text-indigo-600 group-hover:scale-110 transition-all duration-200"></i>
                                    Kelola Anggota
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <div class="relative ">
            <a href="/src/fitur/products/product" id="productLink" data-menu="products" data-title="Produk"
                class="group flex items-center w-full py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-cyan-100 hover:to-cyan-200 hover:text-cyan-700 hover:shadow-lg transition-all duration-300 cursor-pointer focus:outline-none border border-transparent hover:border-cyan-300">
                <div class="w-8 flex justify-center">
                    <i
                        class="fa-solid fa-box text-xl text-cyan-600 group-hover:text-cyan-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
                </div>
                <span
                    class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">Produk</span>
            </a>
        </div>

        <div class="relative ">
            <a href="/src/fitur/aset/history_aset" id="productLink" data-menu="history_aset" data-title="Aset"
                class="group flex items-center w-full py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-cyan-100 hover:to-cyan-200 hover:text-cyan-700 hover:shadow-lg transition-all duration-300 cursor-pointer focus:outline-none border border-transparent hover:border-cyan-300">
                <div class="w-8 flex justify-center">
                    <i
                        class="fa-solid fa-boxes-packing text-xl text-cyan-600 group-hover:text-cyan-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
                </div>
                <span
                    class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">Management
                    Aset</span>
            </a>
        </div>

        <div x-data="{ open: false }" class="relative ">
            <button @click="open = !open" id="upload-menu" data-menu="upload_banner" data-title="Upload"
                class="group flex items-center w-full py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-cyan-100 hover:to-cyan-200 hover:text-cyan-700 hover:shadow-lg transition-all duration-300 cursor-pointer focus:outline-none border border-transparent hover:border-cyan-300">
                <div class="w-8 flex justify-center">
                    <i
                        class="fa-solid fa-upload text-xl text-cyan-600 group-hover:text-cyan-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
                </div>
                <span
                    class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">Upload</span>
                <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" @click.away="open = false"
                class="mt-3 ml-4 bg-gradient-to-br from-white to-cyan-50 rounded-xl shadow-xl border border-cyan-200 z-10 backdrop-blur-sm"
                style="display: none;">
                <ul class="py-2">
                    <li>
                        <a href="/src/fitur/banner/view_banner" data-menu="upload_banner"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-cyan-100 hover:text-cyan-700 transition-all duration-200 group rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i
                                    class="fa-regular fa-image mr-2 text-base text-cyan-400 group-hover:text-cyan-600 group-hover:scale-110 transition-all duration-200"></i>
                                Banner
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div x-data="{ 
                open: <?= $isToolsOpen ? 'true' : 'false' ?>, 
                nestedOpenJadwalSO: <?= $isJadwalSoOpen ? 'true' : 'false' ?>, 
                nestedOpenVoucher: <?= $isVoucherOpen ? 'true' : 'false' ?>,
                nestedOpenReceipt: <?= $isReceiptToolOpen ? 'true' : 'false' ?>,
                nestedOpenReturn: <?= $isReturnToolOpen ? 'true' : 'false' ?>,
                nestedOpenKoreksi: <?= $isKoreksiToolOpen ? 'true' : 'false' ?>
            }" class="relative "
            @reset-menu.window="open = false; nestedOpenJadwalSO = false; nestedOpenVoucher = false; nestedOpenReceipt = false; nestedOpenReturn = false; nestedOpenKoreksi = false">
            <button @click="open = !open" id="tools" data-title="Alat"
                class="group flex items-center w-full py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-cyan-100 hover:to-cyan-200 hover:text-cyan-700 hover:shadow-lg transition-all duration-300 cursor-pointer focus:outline-none border border-transparent hover:border-cyan-300">
                <div class="w-8 flex justify-center">
                    <i
                        class="fa-solid fa-toolbox text-xl text-cyan-600 group-hover:text-cyan-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
                </div>
                <span
                    class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">Tools</span>
                <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="open" @click.away="open = false"
                class="mt-3 ml-4 bg-gradient-to-br from-white to-cyan-50 rounded-xl shadow-xl border border-cyan-200 z-10 backdrop-blur-sm"
                style="display: none;">
                <ul class="py-2 space-y-1">

                    <li>
                        <a href="/src/fitur/approval/izin" data-menu="izin"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-cyan-100 hover:text-cyan-700 transition-all duration-200 group rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i
                                    class="fa-solid fa-clipboard-check mr-2 text-base text-cyan-400 group-hover:text-cyan-600 group-hover:scale-110 transition-all duration-200"></i>
                                Approval Koreksi
                            </span>
                        </a>
                    </li>

                    <li>
                        <button @click="nestedOpenReceipt = !nestedOpenReceipt"
                            class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-cyan-100 hover:text-cyan-700 transition-all duration-200 flex items-center group cursor-pointer rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center">
                                <i
                                    class="fa-solid fa-receipt mr-2 text-lg text-cyan-500 group-hover:text-cyan-600 transition-all duration-200 group-hover:scale-110"></i>
                                Receipt
                            </span>
                            <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                                :class="{ 'rotate-180': nestedOpenReceipt }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="nestedOpenReceipt" @click.away="nestedOpenReceipt = false" class="ml-4 mt-1"
                            style="display: none;">
                            <ul
                                class="bg-gradient-to-br from-cyan-50 to-blue-50 rounded-lg p-2 space-y-1 border border-cyan-200">
                                <li>
                                    <a href="/src/fitur/receipt/index.php" data-menu="receipt_index"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-cyan-100 hover:text-cyan-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-list mr-2 text-base text-cyan-400 group-hover:text-cyan-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Check Receipt
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/receipt/create.php" data-menu="receipt_create"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-cyan-100 hover:text-cyan-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-plus mr-2 text-base text-cyan-400 group-hover:text-cyan-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Buat Receipt
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li>
                        <button @click="nestedOpenReturn = !nestedOpenReturn"
                            class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-cyan-100 hover:text-cyan-700 transition-all duration-200 flex items-center group cursor-pointer rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center">
                                <i
                                    class="fa-solid fa-rotate-left mr-2 text-lg text-cyan-500 group-hover:text-cyan-600 transition-all duration-200 group-hover:scale-110"></i>
                                Return
                            </span>
                            <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                                :class="{ 'rotate-180': nestedOpenReturn }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="nestedOpenReturn" @click.away="nestedOpenReturn = false" class="ml-4 mt-1"
                            style="display: none;">
                            <ul
                                class="bg-gradient-to-br from-cyan-50 to-orange-50 rounded-lg p-2 space-y-1 border border-cyan-200">
                                <li>
                                    <a href="/src/fitur/return/index.php" data-menu="return_index"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-cyan-100 hover:text-cyan-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-list mr-2 text-base text-cyan-400 group-hover:text-cyan-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Check Return
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/return/create.php" data-menu="return_create"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-cyan-100 hover:text-cyan-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-plus mr-2 text-base text-cyan-400 group-hover:text-cyan-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Buat Return
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li>
                        <button @click="nestedOpenKoreksi = !nestedOpenKoreksi"
                            class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-cyan-100 hover:text-cyan-700 transition-all duration-200 flex items-center group cursor-pointer rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center">
                                <i
                                    class="fa-solid fa-pen-to-square mr-2 text-lg text-cyan-500 group-hover:text-cyan-600 transition-all duration-200 group-hover:scale-110"></i>
                                Koreksi
                            </span>
                            <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                                :class="{ 'rotate-180': nestedOpenKoreksi }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="nestedOpenKoreksi" @click.away="nestedOpenKoreksi = false" class="ml-4 mt-1"
                            style="display: none;">
                            <ul
                                class="bg-gradient-to-br from-cyan-50 to-red-50 rounded-lg p-2 space-y-1 border border-cyan-200">
                                <li>
                                    <a href="/src/fitur/koreksi/index.php" data-menu="koreksi_index"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-cyan-100 hover:text-cyan-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-list mr-2 text-base text-cyan-400 group-hover:text-cyan-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Check Koreksi
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/koreksi/create.php" data-menu="koreksi_create"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-cyan-100 hover:text-cyan-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-plus mr-2 text-base text-cyan-400 group-hover:text-cyan-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Buat Koreksi
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li>
                        <button @click="nestedOpenJadwalSO = !nestedOpenJadwalSO"
                            class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-cyan-100 hover:text-cyan-700 transition-all duration-200 flex items-center group cursor-pointer rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center">
                                <i
                                    class="fa-solid fa-calendar-days mr-2 text-lg text-cyan-500 group-hover:text-cyan-600 transition-all duration-200 group-hover:scale-110"></i>
                                Jadwal SO
                            </span>
                            <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                                :class="{ 'rotate-180': nestedOpenJadwalSO }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="nestedOpenJadwalSO" @click.away="nestedOpenJadwalSO = false" class="ml-4 mt-1"
                            style="display: none;">
                            <ul
                                class="bg-gradient-to-br from-cyan-50 to-gray-50 rounded-lg p-2 space-y-1 border border-cyan-200">
                                <li>
                                    <a href="/src/fitur/laporan/jadwal_so/index.php" data-menu="laporan_jadwal_so"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-cyan-100 hover:text-cyan-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-list-check mr-2 text-base text-cyan-400 group-hover:text-cyan-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Jadwal SO
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/laporan/jadwal_so/create_jadwal_so.php"
                                        data-menu="laporan_jadwal_so_create"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-cyan-100 hover:text-cyan-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-plus mr-2 text-base text-cyan-400 group-hover:text-cyan-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Buat Jadwal
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li>
                        <button @click="nestedOpenVoucher = !nestedOpenVoucher"
                            class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-cyan-100 hover:text-cyan-700 transition-all duration-200 flex items-center group cursor-pointer rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center">
                                <i
                                    class="fa-solid fa-ticket mr-2 text-lg text-cyan-500 group-hover:text-cyan-600 transition-all duration-200 group-hover:scale-110"></i>
                                Voucher
                            </span>
                            <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                                :class="{ 'rotate-180': nestedOpenVoucher }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="nestedOpenVoucher" @click.away="nestedOpenVoucher = false" class="ml-4 mt-1"
                            style="display: none;">
                            <ul
                                class="bg-gradient-to-br from-cyan-50 to-pink-50 rounded-lg p-2 space-y-1 border border-cyan-200">
                                <li>
                                    <a href="/src/fitur/voucher/index.php" data-menu="voucher_index"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-cyan-100 hover:text-cyan-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-list mr-2 text-base text-cyan-400 group-hover:text-cyan-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Data Voucher
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/voucher/create_voucher.php" data-menu="voucher_create"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-cyan-100 hover:text-cyan-600 transition-all duration-200 group rounded-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-plus mr-2 text-base text-cyan-400 group-hover:text-cyan-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Buat Voucher
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li>
                        <a href="/src/fitur/uang_brangkas/index.php" data-menu="uang_brankas"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-cyan-100 hover:text-cyan-700 transition-all duration-200 group rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i
                                    class="fa-solid fa-vault mr-2 text-base text-cyan-400 group-hover:text-cyan-600 group-hover:scale-110 transition-all duration-200"></i>
                                Uang Brankas
                            </span>
                        </a>
                    </li>

                </ul>
            </div>
        </div>
        <div x-data="{ 
                open: <?= $isPajakOpen ? 'true' : 'false' ?>, 
                nestedOpenPembelian: <?= $isPembelianOpen ? 'true' : 'false' ?>, 
                nestedOpenPengeluaran: <?= $isPengeluaranOpen ? 'true' : 'false' ?>,
                nestedOpenMasukan: <?= $isMasukanOpen ? 'true' : 'false' ?>, 
                nestedOpenFaktur: <?= $isFakturOpen ? 'true' : 'false' ?>,
                nestedOpenLainnya: <?= $isLainnyaOpen ? 'true' : 'false' ?>
            }" class="relative"
            @reset-menu.window="open = false; nestedOpenPembelian = false; nestedOpenPengeluaran = false; nestedOpenMasukan = false; nestedOpenFaktur = false; nestedOpenLainnya = false">
            <button @click="open = !open" id="pajakLink" data-title="Pajak"
                class="group flex items-center w-full py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-slate-100 hover:to-slate-200 hover:text-slate-700 hover:shadow-lg transition-all duration-300 cursor-pointer focus:outline-none border border-transparent hover:border-slate-300">
                <div class="w-8 flex justify-center">
                    <i
                        class="fa-solid fa-building-columns text-xl text-slate-600 group-hover:text-slate-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
                </div>
                <span
                    class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">Pajak</span>
                <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="open" @click.away="open = false"
                class="mt-3 ml-4 bg-gradient-to-br from-white to-slate-50 rounded-xl shadow-xl border border-slate-200 z-10 backdrop-blur-sm"
                style="display: none;">
                <ul class="py-2 space-y-1">

                    <li>
                        <button @click="nestedOpenPembelian = !nestedOpenPembelian"
                            class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-slate-100 hover:text-slate-700 transition-all duration-200 flex items-center group cursor-pointer rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center text-sm">
                                <i
                                    class="fa-solid fa-cart-shopping mr-2 text-base text-slate-500 group-hover:text-slate-600 transition-all duration-200 group-hover:scale-110"></i>
                                Pembelian
                            </span>
                            <svg class="ml-auto w-3 h-3 transform transition-transform duration-200 group-hover:translate-x-1"
                                :class="{ 'rotate-180': nestedOpenPembelian }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="nestedOpenPembelian" class="ml-4 mt-1" style="display: none;">
                            <ul class="bg-slate-50 rounded-lg p-2 space-y-1 border border-slate-200">
                                <li>
                                    <a href="/src/fitur/coretax/input_pembelian.php" data-menu="pajak_input_pembelian"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-white hover:text-slate-800 transition-all duration-200 group rounded-md shadow-sm hover:shadow-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-plus mr-2 text-sm text-slate-400 group-hover:text-slate-600"></i>
                                            Form Pembelian
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/coretax/laporan_pembelian.php"
                                        data-menu="pajak_laporan_pembelian"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-white hover:text-slate-800 transition-all duration-200 group rounded-md shadow-sm hover:shadow-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-list mr-2 text-sm text-slate-400 group-hover:text-slate-600"></i>
                                            Laporan Pembelian
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li>
                        <button @click="nestedOpenPengeluaran = !nestedOpenPengeluaran"
                            class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-slate-100 hover:text-slate-700 transition-all duration-200 flex items-center group cursor-pointer rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center text-sm">
                                <i
                                    class="fa-solid fa-money-bill-trend-up mr-2 text-base text-slate-500 group-hover:text-slate-600 transition-all duration-200 group-hover:scale-110"></i>
                                Penjualan
                            </span>
                            <svg class="ml-auto w-3 h-3 transform transition-transform duration-200 group-hover:translate-x-1"
                                :class="{ 'rotate-180': nestedOpenPengeluaran }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="nestedOpenPengeluaran" class="ml-4 mt-1" style="display: none;">
                            <ul class="bg-slate-50 rounded-lg p-2 space-y-1 border border-slate-200">
                                <li>
                                    <a href="/src/fitur/coretax/data_coretax_keluaran.php" data-menu="pajak_keluaran"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-white hover:text-slate-800 transition-all duration-200 group rounded-md shadow-sm hover:shadow-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-table-list mr-2 text-sm text-slate-400 group-hover:text-slate-600"></i>
                                            Data Keluaran
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/coretax/import_faktur_keluaran.php"
                                        data-menu="pajak_keluaran_import"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-white hover:text-slate-800 transition-all duration-200 group rounded-md shadow-sm hover:shadow-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-file-import mr-2 text-sm text-slate-400 group-hover:text-slate-600"></i>
                                            Import Keluaran
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li>
                        <button @click="nestedOpenMasukan = !nestedOpenMasukan"
                            class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-slate-100 hover:text-slate-700 transition-all duration-200 flex items-center group cursor-pointer rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center text-sm">
                                <i
                                    class="fa-solid fa-inbox mr-2 text-base text-slate-500 group-hover:text-slate-600 transition-all duration-200 group-hover:scale-110"></i>
                                Masukan
                            </span>
                            <svg class="ml-auto w-3 h-3 transform transition-transform duration-200 group-hover:translate-x-1"
                                :class="{ 'rotate-180': nestedOpenMasukan }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="nestedOpenMasukan" class="ml-4 mt-1" style="display: none;">
                            <ul class="bg-slate-50 rounded-lg p-2 space-y-1 border border-slate-200">
                                <li>
                                    <a href="/src/fitur/coretax/data_coretax.php" data-menu="pajak_data"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-white hover:text-slate-800 transition-all duration-200 group rounded-md shadow-sm hover:shadow-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-database mr-2 text-sm text-slate-400 group-hover:text-slate-600"></i>
                                            Laporan Masukan
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/coretax/import_faktur.php" data-menu="pajak_import"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-white hover:text-slate-800 transition-all duration-200 group rounded-md shadow-sm hover:shadow-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-file-import mr-2 text-sm text-slate-400 group-hover:text-slate-600"></i>
                                            Import Masukan
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li>
                        <button @click="nestedOpenFaktur = !nestedOpenFaktur"
                            class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-slate-100 hover:text-slate-700 transition-all duration-200 flex items-center group cursor-pointer rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center text-sm">
                                <i
                                    class="fa-solid fa-pen-to-square mr-2 text-base text-slate-500 group-hover:text-slate-600 transition-all duration-200 group-hover:scale-110"></i>
                                Faktur Manual
                            </span>
                            <svg class="ml-auto w-3 h-3 transform transition-transform duration-200 group-hover:translate-x-1"
                                :class="{ 'rotate-180': nestedOpenFaktur }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="nestedOpenFaktur" class="ml-4 mt-1" style="display: none;">
                            <ul class="bg-slate-50 rounded-lg p-2 space-y-1 border border-slate-200">
                                <li>
                                    <a href="/src/fitur/coretax/input_faktur_pajak.php" data-menu="pajak_input_faktur"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-white hover:text-slate-800 transition-all duration-200 group rounded-md shadow-sm hover:shadow-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-keyboard mr-2 text-sm text-slate-400 group-hover:text-slate-600"></i>
                                            Input Manual
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/coretax/laporan_faktur_pajak.php"
                                        data-menu="pajak_laporan_faktur"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-white hover:text-slate-800 transition-all duration-200 group rounded-md shadow-sm hover:shadow-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-file-lines mr-2 text-sm text-slate-400 group-hover:text-slate-600"></i>
                                            Laporan Manual
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li>
                        <button @click="nestedOpenLainnya = !nestedOpenLainnya"
                            class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-slate-100 hover:text-slate-700 transition-all duration-200 flex items-center group cursor-pointer rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center text-sm">
                                <i
                                    class="fa-solid fa-folder-open mr-2 text-base text-slate-500 group-hover:text-slate-600 transition-all duration-200 group-hover:scale-110"></i>
                                Data Lainnya
                            </span>
                            <svg class="ml-auto w-3 h-3 transform transition-transform duration-200 group-hover:translate-x-1"
                                :class="{ 'rotate-180': nestedOpenLainnya }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="nestedOpenLainnya" class="ml-4 mt-1" style="display: none;">
                            <ul class="bg-slate-50 rounded-lg p-2 space-y-1 border border-slate-200">
                                <li>
                                    <a href="/src/fitur/coretax/faktur_masukan.php" data-menu="pajak_faktur_masukan"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-white hover:text-slate-800 transition-all duration-200 group rounded-md shadow-sm hover:shadow-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i
                                                class="fa-solid fa-receipt mr-2 text-sm text-slate-400 group-hover:text-slate-600"></i>
                                            Laporan Penerimaan
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                </ul>
            </div>
        </div>
        <div x-data="{ 
    open: <?= $isFinanceOpen ? 'true' : 'false' ?>, 
    nestedOpenBukuBesar: <?= $isBukuBesarOpen ? 'true' : 'false' ?>,
    nestedOpenSerahTerima: <?= $isSerahTerimaOpen ? 'true' : 'false' ?> 
}" class="relative" @reset-menu.window="open = false; nestedOpenBukuBesar = false; nestedOpenSerahTerima = false">

            <button @click="open = !open" id="financeLink" data-title="Finance"
                class="group flex items-center w-full py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-emerald-100 hover:to-emerald-200 hover:text-emerald-700 hover:shadow-lg transition-all duration-300 cursor-pointer focus:outline-none border border-transparent hover:border-emerald-300">
                <div class="w-8 flex justify-center">
                    <i
                        class="fa-solid fa-file-invoice-dollar text-xl text-emerald-600 group-hover:text-emerald-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
                </div>
                <span
                    class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">Finance</span>
                <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="open" @click.away="open = false"
                class="mt-3 ml-4 bg-gradient-to-br from-white to-emerald-50 rounded-xl shadow-xl border border-emerald-200 z-10 backdrop-blur-sm"
                style="display: none;">
                <ul class="py-2 space-y-1">

                    <li>
                        <button @click="nestedOpenBukuBesar = !nestedOpenBukuBesar"
                            class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-emerald-100 hover:text-emerald-700 transition-all duration-200 flex items-center group cursor-pointer rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center text-sm">
                                <i
                                    class="fa-solid fa-book-journal-whills mr-2 text-base text-emerald-500 group-hover:text-emerald-600 transition-all duration-200 group-hover:scale-110"></i>
                                Buku Besar
                            </span>
                            <svg class="ml-auto w-3 h-3 transform transition-transform duration-200 group-hover:translate-x-1"
                                :class="{ 'rotate-180': nestedOpenBukuBesar }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="nestedOpenBukuBesar" class="ml-4 mt-1" style="display: none;">
                            <ul class="bg-emerald-50 rounded-lg p-2 space-y-1 border border-emerald-200">
                                <li>
                                    <a href="/src/fitur/buku_besar/input_buku_besar.php" data-menu="finance_input_bb"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-white hover:text-emerald-800 transition-all duration-200 group rounded-md shadow-sm hover:shadow-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i class="fa-solid fa-pen-to-square mr-2 text-sm text-emerald-400"></i>
                                            Input Buku Besar
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/buku_besar/laporan_buku_besar.php"
                                        data-menu="finance_laporan_bb"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-white hover:text-emerald-800 transition-all duration-200 group rounded-md shadow-sm hover:shadow-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i class="fa-solid fa-file-lines mr-2 text-sm text-emerald-400"></i>
                                            Laporan Buku Besar
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li>
                        <button @click="nestedOpenSerahTerima = !nestedOpenSerahTerima"
                            class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-emerald-100 hover:text-emerald-700 transition-all duration-200 flex items-center group cursor-pointer rounded-lg">
                            <span
                                class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center text-sm">
                                <i
                                    class="fa-solid fa-handshake mr-2 text-base text-emerald-500 group-hover:text-emerald-600 transition-all duration-200 group-hover:scale-110"></i>
                                Serah Terima
                            </span>
                            <svg class="ml-auto w-3 h-3 transform transition-transform duration-200 group-hover:translate-x-1"
                                :class="{ 'rotate-180': nestedOpenSerahTerima }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="nestedOpenSerahTerima" class="ml-4 mt-1" style="display: none;">
                            <ul class="bg-emerald-50 rounded-lg p-2 space-y-1 border border-emerald-200">
                                <li>
                                    <a href="/src/fitur/finance/input_serah_terima_nota.php"
                                        data-menu="finance_input_st"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-white hover:text-emerald-800 transition-all duration-200 group rounded-md shadow-sm hover:shadow-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i class="fa-solid fa-plus mr-2 text-sm text-emerald-400"></i>
                                            Input Serah Terima
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/finance/laporan_serah_terima_nota.php"
                                        data-menu="finance_laporan_st"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-white hover:text-emerald-800 transition-all duration-200 group rounded-md shadow-sm hover:shadow-md">
                                        <span
                                            class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i class="fa-solid fa-file-lines mr-2 text-sm text-emerald-400"></i>
                                            Laporan Serah Terima
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                </ul>
            </div>
        </div>


    </nav>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // === ELEMENT REFERENCES ===
        const profileImg = document.getElementById("profile-img");
        const profileCard = document.getElementById("profile-card");
        const sidebarTexts = document.querySelectorAll(".sidebar-text");
        const mainContent = document.getElementById("main-content");
        const sidebar = document.getElementById("sidebar");
        const toggleButton = document.getElementById("toggle-hide");
        const toggleSidebarBtn = document.getElementById("toggle-sidebar"); // Tombol Hamburger Mobile
        const closeBtn = document.getElementById("closeSidebar"); // Tombol X Mobile

        // === STATE VARIABLES ===
        let isAnimating = false;
        let hoverTimeout = null;

        // === HELPER FUNCTIONS ===

        // Cek apakah mode mobile aktif
        const isMobile = () => window.innerWidth <= 768;

        // Reset semua floating menu (Hanya untuk Desktop)
        function closeAllFloatingMenus() {
            document.querySelectorAll('.floating-submenu').forEach(submenu => {
                submenu.style.display = '';
                submenu.style.position = '';
                submenu.classList.remove('floating-submenu');
            });
            if (hoverTimeout) {
                clearTimeout(hoverTimeout);
                hoverTimeout = null;
            }
        }

        function expandSidebar() {
            if (isAnimating) return;
            isAnimating = true;
            closeAllFloatingMenus();

            sidebar.classList.remove("w-16", "px-2", "collapsed");
            sidebar.classList.add("w-64", "px-5");

            setTimeout(() => {
                sidebarTexts.forEach((text) => text.classList.remove("hidden"));
            }, 100);

            if (mainContent) {
                mainContent.classList.remove("ml-16");
                mainContent.classList.add("ml-64");
            }

            if (toggleButton) {
                toggleButton.classList.add("left-64");
                toggleButton.classList.remove("left-20");
                const icon = toggleButton.querySelector("i");
                if (icon) {
                    icon.classList.remove("fa-angle-right");
                    icon.classList.add("fa-angle-left");
                }
            }

            setTimeout(() => { isAnimating = false; }, 300);
        }

        function collapseSidebar() {
            if (isMobile()) return; // Jangan pernah collapse style desktop di HP

            if (isAnimating) return;
            isAnimating = true;

            // Reset Alpine Data hanya di Desktop
            window.dispatchEvent(new CustomEvent('reset-menu'));
            closeAllFloatingMenus();

            sidebar.classList.remove("w-64", "px-5");
            sidebar.classList.add("w-16", "px-2", "collapsed");
            sidebarTexts.forEach((text) => text.classList.add("hidden"));

            if (mainContent) {
                mainContent.classList.remove("ml-64");
                mainContent.classList.add("ml-16");
            }

            if (toggleButton) {
                toggleButton.classList.add("left-20");
                toggleButton.classList.remove("left-64");
                const icon = toggleButton.querySelector("i");
                if (icon) {
                    icon.classList.remove("fa-angle-left");
                    icon.classList.add("fa-angle-right");
                }
            }

            setTimeout(() => { isAnimating = false; }, 300);
        }

        // === EVENT LISTENERS ===

        // 1. Tombol Toggle Desktop (Panah Kecil)
        if (toggleButton) {
            toggleButton.addEventListener("click", function (e) {
                e.preventDefault();
                // e.stopPropagation();
                if (sidebar.classList.contains("w-64")) {
                    collapseSidebar();
                } else {
                    expandSidebar();
                }
            });
        }

        // 2. Tombol Hamburger Mobile
        if (toggleSidebarBtn) {
            toggleSidebarBtn.addEventListener("click", function (e) {
                e.stopPropagation(); // Cegah event bubbling
                sidebar.classList.toggle("open");
                // Pastikan sidebar dalam mode 'expand' secara internal (teks muncul)
                expandSidebar();
            });
        }

        // 3. Tombol Close Mobile (X)
        if (closeBtn) {
            closeBtn.addEventListener("click", function () {
                sidebar.classList.remove("open");
            });
        }

        // 4. Profile Card
        if (profileImg) {
            profileImg.addEventListener("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                profileCard.classList.toggle("show");
            });
        }
        document.addEventListener("click", function (e) {
            if (profileCard && !profileCard.contains(e.target) && !profileImg.contains(e.target)) {
                profileCard.classList.remove("show");
            }
        });

        // === LOGIKA MENU ITEM (INTI PERBAIKAN) ===
        const menuButtons = document.querySelectorAll('#sidebar button[\\@click*="open"]');

        menuButtons.forEach((button) => {
            const parentContainer = button.closest('.relative');
            const submenu = button.nextElementSibling;

            // A. LOGIKA KLIK
            button.addEventListener("click", function (e) {
                if (isMobile()) {
                    // DI HP: Biarkan Alpine.js bekerja natural.
                    // Jangan panggil preventDefault() kecuali diperlukan.
                    // Pastikan tidak ada event luar yang menutupnya.
                    e.stopPropagation();
                    return;
                }

                // DI DESKTOP (COLLAPSED): Expand dulu baru buka menu
                if (sidebar.classList.contains("collapsed")) {
                    e.preventDefault();
                    e.stopPropagation();
                    expandSidebar();
                    setTimeout(() => {
                        if (parentContainer && typeof Alpine !== 'undefined') {
                            Alpine.$data(parentContainer).open = true;
                        }
                    }, 300);
                }
            });

            // B. LOGIKA HOVER (HANYA DESKTOP)
            button.addEventListener("mouseenter", function () {
                if (isMobile() || isAnimating || !sidebar.classList.contains("collapsed")) return;

                if (hoverTimeout) clearTimeout(hoverTimeout);

                hoverTimeout = setTimeout(() => {
                    if (!submenu) return;
                    if (!sidebar.classList.contains("collapsed")) return;

                    submenu.classList.add("floating-submenu");
                    const rect = button.getBoundingClientRect();
                    submenu.style.position = "fixed";
                    submenu.style.left = (rect.right + 10) + "px";
                    submenu.style.top = rect.top + "px";
                    submenu.style.display = "block";
                    submenu.style.zIndex = "9999";
                }, 150);
            });

            // C. LOGIKA MOUSE LEAVE (HANYA DESKTOP & HANYA JIKA FLOATING)
            const handleMouseLeave = function (e) {
                if (isMobile()) return; // PROTEKSI UTAMA: Jangan jalankan di HP

                // Jika user pindah ke submenu-nya sendiri, biarkan
                if (e.relatedTarget && submenu && submenu.contains(e.relatedTarget)) return;

                // Batalkan timer jika belum muncul
                if (hoverTimeout) {
                    clearTimeout(hoverTimeout);
                    hoverTimeout = null;
                }

                // Hanya tutup jika itu adalah 'floating submenu' (bukan accordion biasa)
                if (submenu && submenu.classList.contains('floating-submenu')) {
                    setTimeout(() => {
                        if (!submenu.matches(':hover')) {
                            submenu.style.display = "none";
                            submenu.classList.remove("floating-submenu");
                        }
                    }, 100);
                }
            };

            button.addEventListener("mouseleave", handleMouseLeave);
            if (submenu) {
                submenu.addEventListener("mouseleave", handleMouseLeave);
            }
        });

        // === INITIALIZATION ===
        function initSidebar() {
            if (isMobile()) {
                // Mode HP: Pastikan struktur kelas siap untuk accordion
                sidebar.classList.remove("w-16", "px-2", "collapsed");
                sidebar.classList.add("w-64", "px-5");
                sidebarTexts.forEach(text => text.classList.remove("hidden"));

                // Pastikan sidebar tertutup saat load
                sidebar.classList.remove("open");
            } else {
                // Mode Desktop: Default Collapsed
                expandSidebar();
            }
        }

        initSidebar();

        // Handle Resize Window
        window.addEventListener('resize', function () {
            // Reset logika jika berpindah dari desktop ke mobile atau sebaliknya
            closeAllFloatingMenus();
            if (isMobile()) {
                sidebar.classList.remove("collapsed");
                sidebar.classList.add("w-64");
                sidebarTexts.forEach(text => text.classList.remove("hidden"));
            }
        });

        // Active Menu Highlighting
        const currentPath = window.location.pathname;
        const allLinks = document.querySelectorAll('#sidebar nav a');

        allLinks.forEach(link => {
            const linkHref = link.getAttribute('href');

            // Cek apakah URL cocok
            if (linkHref && currentPath.includes(linkHref) && linkHref !== '#') {

                // 1. Highlight Menu Anak Terakhir (Misal: Sub Dept)
                link.classList.add('btn', 'active');

                // 2. Loop ke atas untuk highlight SEMUA parent (Penjualan DAN Laporan)
                let currentElement = link;

                // Terus cari ke atas selama masih ada parent div[x-show]
                while (currentElement) {
                    const parentDiv = currentElement.closest('div[x-show]');

                    if (parentDiv) {
                        // Ambil tombol trigger menu (element sebelum div x-show)
                        const subMenuButton = parentDiv.previousElementSibling;

                        if (subMenuButton && subMenuButton.tagName === 'BUTTON') {
                            // Tambahkan class pink
                            subMenuButton.classList.add('submenu-active');

                            // Tambahkan indikator visual open state (opsional, untuk memastikan panah bawah/atas benar)
                            if (typeof Alpine !== 'undefined') {
                                // Opsional: Memastikan Alpine data state terbuka (biasanya sudah dihandle PHP)
                            }
                        }

                        // Geser currentElement ke atas parentDiv agar loop selanjutnya mencari kakek-nya
                        currentElement = parentDiv.parentElement;
                    } else {
                        // Stop jika sudah tidak ada parent dropdown lagi
                        break;
                    }
                }
            }
        });
    });
</script>

<style>
    /* Sidebar Transitions */
    .sidebar {
        transition: width 0.3s ease-in-out, padding 0.3s ease-in-out, transform 0.3s ease-in-out;
    }

    /* Scrollbar Customization */
    #sidebar nav::-webkit-scrollbar {
        width: 6px;
    }

    #sidebar nav::-webkit-scrollbar-track {
        background-color: #f1f5f9;
    }

    #sidebar nav::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 10px;
    }

    /* Floating Submenu (Desktop Only) */
    .floating-submenu {
        position: fixed !important;
        display: block !important;
        min-width: 200px;
        background-color: white;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        padding: 0.5rem;
        z-index: 9999 !important;
        animation: fadeInMenu 0.15s ease-out;
    }

    @keyframes fadeInMenu {
        from {
            opacity: 0;
            transform: translateX(-5px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Sembunyikan item saat collapsed (Desktop) */
    #sidebar.collapsed [x-show] {
        display: none !important;
    }

    #sidebar.collapsed .sidebar-text {
        display: none !important;
    }

    #sidebar.collapsed svg.ml-auto {
        display: none !important;
    }

    #sidebar.collapsed .group {
        justify-content: center;
        padding-left: 0;
        padding-right: 0;
    }

    #sidebar.collapsed .w-8 {
        width: 100%;
        display: flex;
        justify-content: center;
    }

    /* === MOBILE FIXES (Viewport HP) === */
    @media (max-width: 768px) {
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 50;
            /* Default state di HP: Tersembunyi ke kiri & Lebar Penuh */
            width: 16rem !important;
            /* w-64 equivalent */
            transform: translateX(-100%);
            box-shadow: none;
        }

        /* Kelas untuk membuka sidebar di HP */
        #sidebar.open {
            transform: translateX(0);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }

        /* Paksa teks selalu muncul di HP (override collapsed class logic) */
        .sidebar-text {
            display: block !important;
        }

        #sidebar svg.ml-auto {
            display: block !important;
        }

        /* Reset alignment icon di HP */
        #sidebar .group {
            justify-content: flex-start !important;
            padding-left: 1rem !important;
        }

        #sidebar .w-8 {
            width: 2rem !important;
            justify-content: center !important;
        }

        /* Matikan Floating Submenu di HP (PENTING) */
        .floating-submenu {
            position: static !important;
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
            display: none;
            /* Biarkan Alpine yang handle display block/none */
        }

    }
</style>