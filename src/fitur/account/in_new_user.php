<?php
include '../../../aa_kon_sett.php';
// CORS Headers for API Access
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once __DIR__ . '/../../component/menu_handler.php';

$menuHandler = new MenuHandler('insert_new_user');

if (!$menuHandler->initialize()) {
  exit();
}

$user_id = $menuHandler->getUserId();
$logger = $menuHandler->getLogger();
$token = $menuHandler->getToken();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Pengguna Baru</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

  <link rel="stylesheet" href="../../style/header.css">
  <link rel="stylesheet" href="../../style/sidebar.css">
  <link rel="stylesheet" href="../../style/animation-fade-in.css">
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="../../style/default-font.css">
  <link rel="stylesheet" href="../../output2.css">
  <link rel="icon" type="image/png" href="../../../public/images/logo1.png">

</head>

<body class="bg-gray-50 overflow-auto">
  <?php include '../../component/navigation_report.php' ?>;
  <?php include '../../component/sidebar_report.php' ?>;

  <main id="main-content" class="flex-1 p-6 ml-64">
    <section class="min-h-[85vh] flex items-center justify-center px-2 md:px-6">
      <div class="w-full max-w-5xl">
        <div
          class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-white/30 overflow-hidden animate-fade-in-up">

          <div class="bg-gradient-to-r from-blue-500 to-blue-700 p-5 rounded-t-2xl">
            <h3 class="text-center text-white text-xl font-semibold">Form Pengguna</h3>
          </div>

          <div class="p-6">
            <form id="registrationForm" class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-fade-in-up">
              <p id="error_message" class="text-red-500 col-span-2 hidden transition-all duration-300">Error message
                here</p>

              <div>
                <label for="kode" class="block text-sm font-medium text-gray-700 mb-1">Kode</label>
                <input type="text" id="kode" name="kode"
                  class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition hover:border-blue-400" />
              </div>

              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" id="name" name="name" placeholder="Masukkan Nama Anda" required
                  class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition hover:border-blue-400" />
              </div>

              <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Nama Pengguna</label>
                <input type="text" id="username" name="username" required
                  class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition hover:border-blue-400" />
              </div>

              <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
                <div class="relative">
                  <input type="password" id="password" name="pass"
                    class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition hover:border-blue-400 pr-10" />
                  <button type="button" id="togglePassword" tabindex="-1"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-500 focus:outline-none">
                    <i class="fa fa-eye"></i>
                  </button>
                </div>
              </div>

              <div>
                <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Kata
                  Sandi</label>
                <div class="relative">
                  <input type="password" id="confirmPassword" name="c_pass"
                    class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition hover:border-blue-400 pr-10" />
                  <button type="button" id="toggleConfirmPassword" tabindex="-1"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-500 focus:outline-none">
                    <i class="fa fa-eye"></i>
                  </button>
                </div>
                <p id="passwordError" class="text-red-500 text-sm hidden">Kata sandi tidak cocok.</p>
              </div>

              <div>
                <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Posisi</label>
                <select id="position" name="position" required
                  class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition hover:border-blue-400">
                  <option value="Manajer">Manajer</option>
                  <option value="IT">IT</option>
                  <option value="Admin">Admin</option>
                </select>
              </div>

              <div>
                <label for="area" class="block text-sm font-medium text-gray-700 mb-1">Cabang</label>
                <select id="cabang" name="cabang" required
                  class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition hover:border-blue-400">
                </select>
              </div>

              <div>
                <label for="profile" class="block text-sm font-medium text-gray-700 mb-1">Pilih Profil</label>
                <input type="file" id="profile" name="profile" accept="image/*"
                  class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition hover:border-blue-400" />
              </div>

              <div class="md:col-span-2">
                <div class="flex justify-between items-center mb-2">
                  <label class="text-sm font-medium text-gray-700">Akses Menu</label>
                  <label
                    class="flex items-center gap-2 text-sm font-semibold text-blue-600 cursor-pointer hover:text-blue-800">
                    <input type="checkbox" id="selectAllMenus" class="scale-110 focus:ring focus:ring-blue-300 rounded">
                    Select All / Unselect All
                  </label>
                </div>


                <div class="grid md:grid-cols-2 gap-4">

                  <div class="bg-white/70 border border-white/40 rounded-xl shadow p-4 mb-2">
                    <p class="text-lg font-bold text-gray-800 mb-2">Dashboard</p>
                    <label class="flex items-center gap-2 text-sm">
                      <input type="checkbox" name="menus[]" value="dashboard"
                        class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Dashboard
                    </label>
                  </div>

                  <div class="bg-white/70 border border-white/40 rounded-xl shadow p-4 mb-2">
                    <p class="text-lg font-bold text-gray-800 mb-2">Integrasi (Shopee/WA)</p>
                    <div class="ml-4 mt-2 space-y-1">
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="shopee_dashboard"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-orange-300" /> Dashboard Shopee
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="shopee_produk"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-orange-300" /> Produk Shopee
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="shopee_order"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-orange-300" /> Order Shopee
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="shopee_terima_barang"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-orange-300" /> Terima Barang Shopee
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="whatsapp_dashboard"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-green-300" /> Dashboard WhatsApp
                      </label>
                    </div>
                  </div>

                  <div class="bg-white/70 border border-white/40 rounded-xl shadow p-4 mb-2">
                    <p class="text-lg font-bold text-gray-800 mb-2">Pajak (Coretax)</p>
                    <div class="ml-4 mt-2 space-y-1">
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="pajak_input_pembelian"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-purple-300" /> Form Pembelian
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="pajak_laporan_pembelian"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-purple-300" /> Laporan Pembelian
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="pajak_input_faktur"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-purple-300" /> Form Faktur
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="pajak_laporan_faktur"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-purple-300" /> Laporan Faktur
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="pajak_import"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-purple-300" /> Import Masukan
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="pajak_data"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-purple-300" /> Laporan Masukan
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="pajak_faktur_masukan"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-purple-300" /> Laporan Penerimaan
                      </label>
                    </div>
                  </div>

                  <div class="bg-white/70 border border-white/40 rounded-xl shadow p-4 mb-2">
                    <p class="text-lg font-bold text-gray-800 mb-2">Laporan - Penjualan</p>
                    <div class="ml-4 mt-2 space-y-1">
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_penjualan_subdept"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Subdept
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_penjualan_salesratio"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Sales Ratio
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_penjualan_kategori"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Kategori
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_penjualan_mnonm"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> M / Non M
                      </label>
                    </div>
                  </div>

                  <div class="bg-white/70 border border-white/40 rounded-xl shadow p-4 mb-2">
                    <p class="text-lg font-bold text-gray-800 mb-2">Laporan - Pelanggan</p>
                    <div class="ml-4 mt-2 space-y-1">
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_pelanggan_aktifitas"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Aktivitas Belanja
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_pelanggan_layanan"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Layanan
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_pelanggan_review"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Review
                      </label>
                    </div>
                  </div>

                  <div class="bg-white/70 border border-white/40 rounded-xl shadow p-4 mb-2">
                    <p class="text-lg font-bold text-gray-800 mb-2">Laporan - Top Sales</p>
                    <div class="ml-4 mt-2 space-y-1">
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_topsales_rupiah"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Top Sales (Rp)
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_topsales_qty"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Top Sales (Qty)
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_topsales_supplier"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Top Sales (Supplier)
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_topsales_kasir"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Sales per Kasir
                      </label>
                    </div>
                  </div>

                  <div class="bg-white/70 border border-white/40 rounded-xl shadow p-4 mb-2">
                    <p class="text-lg font-bold text-gray-800 mb-2">Laporan - Penerimaan</p>
                    <div class="ml-4 mt-2 space-y-1">
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="receipt_index"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Data Receipt
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="receipt_create"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Buat Receipt
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_receipt_detail"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Detail Receipt
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_receipt_supplier"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Receipt by Supplier
                      </label>
                    </div>
                  </div>

                  <div class="bg-white/70 border border-white/40 rounded-xl shadow p-4 mb-2">
                    <p class="text-lg font-bold text-gray-800 mb-2">Laporan - Retur Keluar</p>
                    <div class="ml-4 mt-2 space-y-1">
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="return_index"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Data Return
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="return_create"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Buat Return
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_return_all"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Return All Item
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_return_badstock"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Return Bad Stock
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_return_exp"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Return Expired
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_return_hilang"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Return Hilang Pasangan
                      </label>
                    </div>
                  </div>

                  <div class="bg-white/70 border border-white/40 rounded-xl shadow p-4 mb-2">
                    <p class="text-lg font-bold text-gray-800 mb-2">Mutasi & Koreksi Stok</p>
                    <div class="ml-4 mt-2 space-y-1">
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="koreksi_index"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Data Koreksi
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="koreksi_create"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Buat Koreksi
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_mutasi_in"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Mutasi Invoice
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_koreksi_supplier"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Koreksi (Supplier)
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_koreksi_plu"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Koreksi (PLU)
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="koreksi_so"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Koreksi SO
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="koreksi_so_missed"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Belum Koreksi SO
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="izin"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Approval Koreksi
                      </label>
                    </div>
                  </div>

                  <div class="bg-white/70 border border-white/40 rounded-xl shadow p-4 mb-2">
                    <p class="text-lg font-bold text-gray-800 mb-2">Jadwal SO</p>
                    <div class="ml-4 mt-2 space-y-1">
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_jadwal_so"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Jadwal SO
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_jadwal_so_create"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Buat Jadwal SO
                      </label>
                    </div>
                  </div>

                  <div class="bg-white/70 border border-white/40 rounded-xl shadow p-4 mb-2">
                    <p class="text-lg font-bold text-gray-800 mb-2">Voucher</p>
                    <div class="ml-4 mt-2 space-y-1">
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="voucher_index"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Data Voucher
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="voucher_create"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Buat Voucher
                      </label>
                    </div>
                  </div>

                  <div class="bg-white/70 border border-white/40 rounded-xl shadow p-4 mb-2">
                    <p class="text-lg font-bold text-gray-800 mb-2">Transaksi</p>
                    <div class="ml-4 mt-2 space-y-1">
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="transaksi_promo"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Promo
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="reward_give"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Hadiah
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="transaksi_invalid"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Invalid
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="top_invalid"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Top Invalid
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="top_retur"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Top Retur
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="top_margin"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Top Margin
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="transaksi_margin"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Margin
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="transaksi_cabang"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Transaksi Cabang
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="detail_transaksi_cabang"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Detail Transaksi Cabang
                      </label>
                    </div>
                  </div>

                  <div class="bg-white/70 border border-white/40 rounded-xl shadow p-4 mb-2">
                    <p class="text-lg font-bold text-gray-800 mb-2">Manajemen User</p>
                    <label class="flex items-center gap-2 text-sm ml-4">
                      <input type="checkbox" name="menus[]" value="user_management"
                        class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Manajemen User
                    </label>
                    <label class="flex items-center gap-2 text-sm ml-4">
                      <input type="checkbox" name="menus[]" value="insert_new_user"
                        class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Tambah Anggota
                    </label>
                  </div>

                  <div class="bg-white/70 border border-white/40 rounded-xl shadow p-4 mb-2">
                    <p class="text-lg font-bold text-gray-800 mb-2">Member</p>
                    <div class="ml-4 mt-2 space-y-1">
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="member_poin"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Poin
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="upload_banner"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Banner
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="product_favorite"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Produk Favorit
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="product_member"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Produk Member
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="top_sales"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Top Sales
                      </label>
                    </div>
                  </div>

                  <div class="bg-white/70 border border-white/40 rounded-xl shadow p-4 mb-2">
                    <p class="text-lg font-bold text-gray-800 mb-2">Lainnya

                    </p>
                    <div class="ml-4 mt-2 space-y-1">

                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="history_aset"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Management Aset
                      </label>
                      <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menus[]" value="laporan_log_backup"
                          class="menu-item-checkbox scale-110 focus:ring focus:ring-blue-300" /> Log Backup
                      </label>
                    </div>
                  </div>

                </div>

              </div>

              <div class="md:col-span-2">
                <button type="submit" id="btnSubmit"
                  class="w-full bg-gradient-to-r from-green-500 to-blue-500 hover:from-green-600 hover:to-blue-600 text-white font-semibold py-3 px-4 rounded-xl shadow-md transition-all duration-200 hover:scale-105 flex items-center justify-center gap-2">
                  <span id="btnSubmitText">Simpan</span>
                  <span id="btnSubmitLoading" class="hidden"><i class="fa fa-spinner fa-spin"></i> Menyimpan...</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>

  </main>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../../js/middleware_auth.js"></script>
  <script src="../../js/account/internal/insert.js" type="module"></script>
  <script src="../../js/ui/navbar_toogle.js" type="module"></script>
  <script type="module">
    import {
      kodeCabang
    } from '../../js/kode_cabang/kd.js';
    import {
      areaCabang
    } from '../../js/kode_cabang/cabang_area.js';

    await areaCabang('cabang');

    fetch('../../api/user/get_next_code_user')
      .then(res => res.json())
      .then(data => {
        document.getElementById('kode').value = data.next_kode;
      })
      .catch(err => console.error('Gagal fetch next_kode:', err));

    document.getElementById('togglePassword').addEventListener('click', function () {
      const passInput = document.getElementById('password');
      const icon = this.querySelector('i');
      if (passInput.type === 'password') {
        passInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        passInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    });

    document.getElementById('toggleConfirmPassword').addEventListener('click', function () {
      const passInput = document.getElementById('confirmPassword');
      const icon = this.querySelector('i');
      if (passInput.type === 'password') {
        passInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        passInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    });

    document.getElementById('confirmPassword').addEventListener('input', function () {
      var pass = document.getElementById('password').value;
      var c_pass = document.getElementById('confirmPassword').value;
      var errorElement = document.getElementById('passwordError');
      if (pass !== c_pass) {
        errorElement.classList.remove('hidden');
        errorElement.classList.add('animate-fade-in-up');
      } else {
        errorElement.classList.add('hidden');
        errorElement.classList.remove('animate-fade-in-up');
      }
    });

    const selectAllCheckbox = document.getElementById('selectAllMenus');
    const menuCheckboxes = document.querySelectorAll('input[name="menus[]"]');

    selectAllCheckbox.addEventListener('change', function () {
      const isChecked = this.checked;
      menuCheckboxes.forEach(checkbox => {
        checkbox.checked = isChecked;
      });
    });

    menuCheckboxes.forEach(checkbox => {
      checkbox.addEventListener('change', function () {
        const allChecked = Array.from(menuCheckboxes).every(cb => cb.checked);

        selectAllCheckbox.checked = allChecked;
      });
    });
  </script>
</body>

</html>