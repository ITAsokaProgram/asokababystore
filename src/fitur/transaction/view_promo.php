<?php
require_once __DIR__ . '/../../component/menu_handler.php';

$menuHandler = new MenuHandler('transaksi_promo');

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
  <title>Promo</title>

  <!-- font awesome cdn link  -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

  <!-- Penjelasan: Link ke CSS eksternal dengan cache busting query string -->
  <link rel="stylesheet" href="../../style/header.css">
  <link rel="stylesheet" href="../../style/sidebar.css">
  <link rel="stylesheet" href="../../style/animation-fade-in.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <!-- Setting logo pada tab di website Anda / Favicon -->
  <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="../../style/default-font.css">
  <!-- <link rel="stylesheet" href="../../style/output.css"> -->
  <link rel="stylesheet" href="../../output2.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
  <!-- CSS Tippy -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tippy.js@6/dist/tippy.css" />

  <!-- Popper.js UMD (minified) -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2/dist/umd/popper.min.js"></script>

  <!-- Tippy.js UMD (minified) -->
  <script src="https://cdn.jsdelivr.net/npm/tippy.js@6/dist/tippy-bundle.umd.min.js"></script>

  <!-- Add SweetAlert2 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
  <!-- Add SweetAlert2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

  <style>
    .btn.active {
      background-color: transparent;
      /* background tidak diisi */
      color: #ec4899;
      /* warna teks bisa disesuaikan */
      outline: 2px solid #ec4899;
      outline-offset: 1px;
    }

    th.th-total-poin,
    th.th-tukar-poin,
    th.th-sisa-poin,
    th.th-transaksi {
      text-align: center !important;
    }
  </style>
</head>

<body class="bg-white text-black">
  <?php include '../../component/navigation_report.php'; ?>
  <?php include '../../component/sidebar_report.php'; ?>


  <main id="main-content" class="flex-1 p-6 transition-all duration-300 ml-64">
    <div class="w-full mx-auto px-4 py-8">
      
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6 fade-in">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="fas fa-tags text-blue-600 text-lg"></i>
          </div>
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">Manajemen Promo</h1>
            <p class="text-gray-500 text-sm">Kelola dan pantau semua promo yang aktif</p>
          </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
          <div class="relative w-full">
            <input
              type="text"
              id="searchInput"
              placeholder="Cari promo..."
              class="pl-10 pr-4 py-2.5 w-full rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200" />
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
          </div>
          <button
            onclick="tambahPromo()"
            class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all duration-200 flex items-center justify-center space-x-2 hover-lift shadow-sm w-full sm:w-auto flex-shrink-0">
            <i class="fas fa-plus text-sm"></i>
            <span class="font-medium">Tambah Promo</span>
          </button>
        </div>

      </div>
    </div>

      <!-- Table Container -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden fade-in">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Kode Promo</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Supplier</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Store</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Penggunaan</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Keterangan</th>
                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
              </tr>
            </thead>
            <tbody id="tableBody" class="bg-white divide-y divide-gray-200">

            </tbody>
          </table>
        </div>
      </div>

      <!-- Footer/Pagination -->
      <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-4 fade-in">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
          <div class="flex items-center space-x-2">
            <select id="pageSize" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
              <option value="10">10 per halaman</option>
              <option value="50">50 per halaman</option>
              <option value="100">100 per halaman</option>
              <option value="1000">1000 per halaman</option>
            </select>
          </div>

          <div class="text-sm text-gray-500" id="dataInfo">
            Menampilkan 1-2 dari 2 data
          </div>

          <div class="flex items-center space-x-1" id="pagination">
            <button class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
              <i class="fas fa-chevron-left"></i>
            </button>
            <button class="px-3 py-2 text-sm bg-blue-600 text-white rounded-lg">1</button>
            <button class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Tambah Promo -->
    <div id="modalTambahPromo"
      class="fixed inset-0 bg-black/60 flex justify-center items-center z-50 hidden backdrop-blur-sm transition-all duration-300">
      <div
        class="bg-white w-full max-w-5xl rounded-3xl shadow-2xl border border-gray-100 relative animate-fade-in-up overflow-hidden max-h-[95vh] flex flex-col"
        id="modalContent">

        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-600 to-emerald-600 px-8 py-6 relative">
          <!-- Close Button -->
          <button onclick="closeModal('modalTambahPromo','modalContent')"
            class="absolute top-4 right-4 text-white/80 hover:text-white text-xl bg-white/10 rounded-full p-2 backdrop-blur-sm transition-all duration-200 hover:bg-white/20">
            <i class="fas fa-times"></i>
          </button>

          <h2 class="text-2xl font-bold text-white flex items-center gap-3">
            <div class="bg-white/20 p-3 rounded-xl backdrop-blur-sm">
              <i class="fas fa-tag text-xl"></i>
            </div>
            Tambah Promo Baru
          </h2>
        </div>

        <!-- Form Content - Scrollable -->
        <div class="flex-1 overflow-y-auto">
          <form id="formTambahPromo" class="p-8 space-y-8">
            <!-- Basic Info Section -->
            <div class="bg-gray-50/50 rounded-2xl p-6 border border-gray-100">
              <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center gap-2">
                <i class="fas fa-info-circle text-indigo-500"></i>
                Informasi Dasar
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label for="kode_promo" class="block text-sm font-semibold text-gray-700">Kode Promo</label>
                  <input type="text" name="kode_promo" id="kode_promo" required
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 hover:border-gray-300"
                    placeholder="Masukkan kode promo" />
                </div>
                <div class="space-y-2">
                  <label for="supplier-view" class="block text-sm font-semibold text-gray-700">Supplier</label>
                  <input type="text" name="supplier" id="supplier-view" required
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 hover:border-gray-300"
                    placeholder="Pilih supplier" />
                </div>
              </div>
              <div class="mt-6 space-y-2">
                <label for="keterangan" class="block text-sm font-semibold text-gray-700">Keterangan</label>
                <textarea name="keterangan" id="keterangan" rows="3" required
                  class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 hover:border-gray-300 resize-none"
                  placeholder="Masukkan keterangan promo"></textarea>
              </div>
            </div>

            <!-- Date & Branch Section -->
            <div class="bg-gray-50/50 rounded-2xl p-6 border border-gray-100">
              <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center gap-2">
                <i class="fas fa-calendar-alt text-indigo-500"></i>
                Periode & Cabang
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Custom Multi-Select Checkbox Dropdown -->
                <div class="relative space-y-2" id="dropdownCabangWrapper">
                  <label for="dropdownCabangButton" class="block text-sm font-semibold text-gray-700">Cabang</label>
                  <button type="button" onclick="toggleCabangDropdown('dropdownCabangList')"
                    class="w-full text-left px-4 py-3 border border-gray-200 rounded-xl bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-200 text-sm hover:border-gray-300 flex items-center justify-between"
                    id="dropdownCabangButton">
                    <span>Pilih Cabang</span>
                    <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                  </button>
                  <div id="dropdownCabangList"
                    class="absolute z-10 w-full bg-white border border-gray-200 rounded-xl mt-1 max-h-60 overflow-y-auto shadow-xl hidden">
                    <!-- Store options will be populated dynamically -->
                  </div>
                </div>
                <div class="space-y-2">
                  <label for="start" class="block text-sm font-semibold text-gray-700">Tanggal Mulai</label>
                  <input type="date" name="tanggal_mulai" required id="start"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 hover:border-gray-300" />
                </div>
                <div class="space-y-2">
                  <label for="end" class="block text-sm font-semibold text-gray-700">Tanggal Selesai</label>
                  <input type="date" name="tanggal_selesai" required id="end"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 hover:border-gray-300" />
                </div>
              </div>
            </div>

            <!-- Hidden input untuk submit -->
            <input type="hidden" name="cabang_terpilih" id="cabangTerpilih">

            <!-- Products Section -->
            <div class="bg-gray-50/50 rounded-2xl p-6 border border-gray-100">
              <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center gap-2">
                <i class="fas fa-box text-indigo-500"></i>
                Daftar Barang
              </h3>

              <!-- Search and Controls -->
              <div class="bg-white rounded-xl p-4 mb-4 border border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                  <!-- Pencarian Barang -->
                  <div class="flex flex-col">
                    <label for="search-barang" class="text-xs font-medium text-gray-600 mb-1">
                      Pencarian Barang
                    </label>
                    <input
                      type="text"
                      id="search-barang"
                      placeholder="Cari barang..."
                      class="w-full h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm 
               placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 
               focus:ring-indigo-100 outline-none transition-all duration-200" />
                  </div>

                  <!-- All Diskon -->
                  <div class="flex flex-col">
                    <label for="allDiskon" class="text-xs font-medium text-gray-600 mb-1">
                      All Diskon (%)
                    </label>
                    <input
                      type="number"
                      id="allDiskon"
                      placeholder="0"
                      min="1" max="100"
                      class="w-full h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm 
               placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 
               focus:ring-indigo-100 outline-none transition-all duration-200" />
                  </div>

                  <!-- All Potongan -->
                  <div class="flex flex-col">
                    <label for="allPotongan" class="text-xs font-medium text-gray-600 mb-1">
                      All Potongan
                    </label>
                    <input
                      type="number"
                      id="allPotongan"
                      placeholder="0"
                      class="w-full h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm 
               placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 
               focus:ring-indigo-100 outline-none transition-all duration-200" />
                  </div>

                </div>
              </div>


              <!-- Table Container -->
              <div class="bg-white rounded-xl border border-gray-200 overflow-hidden relative">
                <div id="loadTable" class="absolute inset-0 bg-white/80 flex justify-center items-center z-10 hidden">
                  <div class="w-8 h-8 border-2 border-t-transparent border-indigo-500 border-solid rounded-full animate-spin"></div>
                </div>

                <div class="max-h-[300px] overflow-y-auto">
                  <table class="w-full">
                    <thead class="bg-gray-50 sticky top-0 z-5">
                      <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-[13%]">Barcode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-[45%]">Master Barang</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-[17%]">Harga Jual</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-[12%]">Diskon</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-[13%]">Potongan</th>
                      </tr>
                    </thead>
                    <tbody id="tbody-barang" class="divide-y divide-gray-100">
                      <!-- Content will be populated by JavaScript -->
                      <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-600">Loading...</td>
                        <td class="px-4 py-3 text-sm text-gray-600">Please wait...</td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-center">-</td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-center">-</td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-center">-</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </form>
        </div>

        <!-- Footer Actions -->
        <div class="bg-gray-50/50 border-t border-gray-100 px-8 py-6">
          <div class="flex flex-col sm:flex-row justify-end gap-3">
            <button type="button" onclick="closeModal('modalTambahPromo','modalContent')"
              class="px-6 py-3 bg-white text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50 transition-all duration-200 flex items-center justify-center gap-2 text-sm font-medium">
              <i class="fas fa-times text-sm"></i>
              Batal
            </button>
            <button type="submit" form="formTambahPromo"
              class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-emerald-600 text-white rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 flex items-center justify-center gap-2 shadow-lg hover:shadow-xl text-sm font-semibold">
              <i class="fas fa-save text-sm"></i>
              Simpan Promo
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal Edit Promo -->
    <div id="modalEditPromo"
      class="fixed inset-0 bg-black/60 flex justify-center items-center z-50 hidden backdrop-blur-sm transition-all duration-300">
      <div
        class="bg-white w-full max-w-5xl rounded-3xl shadow-2xl border border-gray-100 relative animate-fade-in-up overflow-hidden max-h-[95vh] flex flex-col"
        id="modalContentEdit">

        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-800 to-blue-600 px-8 py-6 relative">
          <!-- Close Button -->
          <button onclick="closeModal('modalEditPromo','modalContentEdit')"
            class="absolute top-4 right-4 text-white/80 hover:text-white text-xl bg-white/10 rounded-full p-2 backdrop-blur-sm transition-all duration-200 hover:bg-white/20">
            <i class="fas fa-times"></i>
          </button>

          <h2 class="text-2xl font-bold text-white flex items-center gap-3">
            <div class="bg-white/20 p-3 rounded-xl backdrop-blur-sm">
              <i class="fas fa-edit text-xl"></i>
            </div>
            Edit Promo
          </h2>
        </div>

        <!-- Form Content - Scrollable -->
        <div class="flex-1 overflow-y-auto">
          <form id="formEditPromo" class="p-8 space-y-8">
            <!-- Basic Info Section -->
            <div class="bg-gray-50/50 rounded-2xl p-6 border border-gray-100">
              <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center gap-2">
                <i class="fas fa-info-circle text-slate-600"></i>
                Informasi Dasar
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label for="kode_promo_edit" class="block text-sm font-semibold text-gray-700">Kode Promo</label>
                  <input type="text" name="kode_promo_edit" id="kode_promo_edit" required
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all duration-200 hover:border-gray-300"
                    placeholder="Masukkan kode promo" />
                </div>
                <div class="space-y-2">
                  <label for="supplier-view-edit" class="block text-sm font-semibold text-gray-700">Supplier</label>
                  <input type="text" name="supplier-edit" id="supplier-view-edit" required
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-gray-100 shadow-sm focus:outline-none transition-all duration-200 cursor-not-allowed text-gray-500"
                    placeholder="Pilih supplier" disabled />
                </div>
              </div>
              <div class="mt-6 space-y-2">
                <label for="keterangan-edit" class="block text-sm font-semibold text-gray-700">Keterangan</label>
                <textarea name="keterangan-edit" id="keterangan-edit" rows="3" required
                  class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all duration-200 hover:border-gray-300 resize-none"
                  placeholder="Masukkan keterangan promo"></textarea>
              </div>
            </div>

            <!-- Status Section -->
            <div class="bg-gray-50/50 rounded-2xl p-6 border border-gray-100">
              <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center gap-2">
                <i class="fas fa-toggle-on text-slate-600"></i>
                Status Promo
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label for="status_edit" class="block text-sm font-semibold text-gray-700">Status Edit</label>
                  <select name="status-edit" id="status_edit"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all duration-200 hover:border-gray-300">
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                  </select>
                </div>
                <div class="space-y-2">
                  <label for="status_edit_penggunaan" class="block text-sm font-semibold text-gray-700">Status Penggunaan</label>
                  <select name="status-edit-penggunaan" id="status_edit_penggunaan"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all duration-200 hover:border-gray-300">
                    <option value="tidak_dipakai">Tidak Dipakai</option>
                    <option value="sedang_dipakai">Sedang Dipakai</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Date & Branch Section -->
            <div class="bg-gray-50/50 rounded-2xl p-6 border border-gray-100">
              <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center gap-2">
                <i class="fas fa-calendar-alt text-slate-600"></i>
                Periode & Cabang
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Custom Multi-Select Checkbox Dropdown -->
                <div class="relative space-y-2" id="dropdownCabangWrapperEdit">
                  <label for="dropdownCabangButtonEdit" class="block text-sm font-semibold text-gray-700">Cabang</label>
                  <button type="button" onclick="toggleCabangDropdown('dropdownCabangListEdit')"
                    class="w-full text-left px-4 py-3 border border-gray-200 rounded-xl bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-500 transition-all duration-200 text-sm hover:border-gray-300 flex items-center justify-between"
                    id="dropdownCabangButtonEdit">
                    <span>Pilih Cabang</span>
                    <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                  </button>
                  <div id="dropdownCabangListEdit"
                    class="absolute z-10 w-full bg-white border border-gray-200 rounded-xl mt-1 max-h-60 overflow-y-auto shadow-xl hidden">
                    <!-- Store options will be populated dynamically -->
                  </div>
                </div>
                <div class="space-y-2">
                  <label for="start-edit" class="block text-sm font-semibold text-gray-700">Tanggal Mulai</label>
                  <input type="date" name="tanggal_mulai" required id="start-edit"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all duration-200 hover:border-gray-300" />
                </div>
                <div class="space-y-2">
                  <label for="end-edit" class="block text-sm font-semibold text-gray-700">Tanggal Selesai</label>
                  <input type="date" name="tanggal_selesai" required id="end-edit"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all duration-200 hover:border-gray-300" />
                </div>
              </div>
            </div>

            <!-- Hidden input untuk submit -->
            <input type="hidden" name="cabang_terpilih_edit" id="cabangTerpilihEdit">

            <!-- Products Section -->
            <div class="bg-gray-50/50 rounded-2xl p-6 border border-gray-100">
              <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center gap-2">
                <i class="fas fa-box text-slate-600"></i>
                Daftar Barang
              </h3>

              <!-- Search and Controls -->
              <div class="bg-white rounded-xl p-4 mb-4 border border-gray-200">
                <div class="flex flex-wrap items-center gap-4">
                  <div class="flex-1 min-w-[200px] space-y-1">
                    <label class="text-xs font-medium text-gray-600">Pencarian Barang</label>
                    <input
                      type="text"
                      id="search-barang-edit"
                      placeholder="Cari barang..."
                      class="w-full h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm placeholder:text-gray-400 focus:border-slate-500 focus:ring-2 focus:ring-slate-100 outline-none transition-all duration-200" />
                  </div>

                  <div class="space-y-1">
                    <label class="text-xs font-medium text-gray-600">All Diskon (%)</label>
                    <input
                      type="number"
                      id="allDiskonEdit"
                      placeholder="0"
                      min="1" max="100"
                      class="w-[100px] h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm placeholder:text-gray-400 focus:border-slate-500 focus:ring-2 focus:ring-slate-100 outline-none transition-all duration-200" />
                  </div>

                  <div class="space-y-1">
                    <label class="text-xs font-medium text-gray-600">All Potongan</label>
                    <input
                      type="number"
                      id="allPotonganEdit"
                      placeholder="0"
                      class="w-[120px] h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm placeholder:text-gray-400 focus:border-slate-500 focus:ring-2 focus:ring-slate-100 outline-none transition-all duration-200" />
                  </div>
                </div>
              </div>

              <!-- Table Container -->
              <div class="bg-white rounded-xl border border-gray-200 overflow-hidden relative">
                <div class="max-h-[300px] overflow-y-auto">
                  <table class="w-full">
                    <thead class="bg-gray-50 sticky top-0 z-5">
                      <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-[13%]">Barcode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-[45%]">Master Barang</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-[17%]">Harga Jual</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-[12%]">Diskon</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-[13%]">Potongan</th>
                      </tr>
                    </thead>
                    <tbody id="tbody-barang-edit" class="divide-y divide-gray-100">
                      <!-- Content will be populated by JavaScript -->
                      <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-600">Loading...</td>
                        <td class="px-4 py-3 text-sm text-gray-600">Please wait...</td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-center">-</td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-center">-</td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-center">-</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Notes -->
              <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                <div class="flex items-start gap-3">
                  <i class="fas fa-info-circle text-amber-600 text-sm mt-0.5"></i>
                  <div class="text-xs text-amber-800 leading-relaxed">
                    <strong>Catatan Penting:</strong> Hanya salah satu antara diskon atau potongan yang bisa diisi per barang.
                    Jika tidak bisa mengubah nilainya, harap hapus terlebih dahulu baik diskon maupun potongannya.
                    Hapus semua bisa dilakukan di all diskon atau all potongan, cukup klik dan tab.
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>

        <!-- Footer Actions -->
        <div class="bg-gray-50/50 border-t border-gray-100 px-8 py-6">
          <div class="flex flex-col sm:flex-row justify-end gap-3">
            <button type="button" onclick="closeModal('modalEditPromo','modalContentEdit')"
              class="px-6 py-3 bg-white text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50 transition-all duration-200 flex items-center justify-center gap-2 text-sm font-medium">
              <i class="fas fa-times text-sm"></i>
              Batal
            </button>
            <button type="submit" form="formEditPromo"
              class="px-6 py-3 bg-slate-700 text-white rounded-xl hover:bg-slate-800 transition-all duration-200 flex items-center justify-center gap-2 shadow-lg hover:shadow-xl text-sm font-semibold">
              <i class="fas fa-save text-sm"></i>
              Update Promo
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal Detail Promo -->
    <div id="modalDetailPromo"
      class="fixed inset-0 bg-black/60 flex justify-center items-center z-50 backdrop-blur-sm transition-all duration-300 hidden">
      <div id="modalContentDetail"
        class="bg-white w-full max-w-6xl rounded-3xl shadow-2xl border border-gray-100 relative overflow-hidden max-h-[90vh] flex flex-col scale-95 opacity-0 transition-all duration-300 ease-out">

        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-800 to-blue-600 px-8 py-6 relative">
          <!-- Close Button -->
          <button onclick="closeModal('modalDetailPromo','modalContentDetail')"
            class="absolute top-4 right-4 text-white/80 hover:text-white text-xl bg-white/10 rounded-full p-2 backdrop-blur-sm transition-all duration-200 hover:bg-white/20">
            <i class="fas fa-times"></i>
          </button>

          <h2 class="text-2xl font-bold text-white flex items-center gap-3">
            <div class="bg-white/20 p-3 rounded-xl backdrop-blur-sm">
              <i class="fas fa-box-open text-xl"></i>
            </div>
            Detail Barang Promo
          </h2>
        </div>

        <!-- Content Section -->
        <div class="flex-1 p-8 overflow-hidden">
          <div class="bg-gray-50/50 rounded-2xl p-6 border border-gray-100 h-full flex flex-col">
            <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center gap-2">
              <i class="fas fa-list-alt text-blue-600"></i>
              Daftar Barang dalam Promo
            </h3>

            <!-- Table Container -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden flex-1 flex flex-col">
              <div class="flex-1 overflow-auto">
                <table class="w-full text-sm">
                  <thead class="bg-gradient-to-r from-blue-600 to-blue-500 text-white sticky top-0 z-10">
                    <tr>
                      <th class="px-4 py-4 text-left font-semibold border-r border-blue-400/30">Kode Promo</th>
                      <th class="px-4 py-4 text-left font-semibold border-r border-blue-400/30">Barcode</th>
                      <th class="px-4 py-4 text-left font-semibold border-r border-blue-400/30">Nama Barang</th>
                      <th class="px-4 py-4 text-right font-semibold border-r border-blue-400/30">Harga Jual</th>
                      <th class="px-4 py-4 text-center font-semibold border-r border-blue-400/30">Diskon (%)</th>
                      <th class="px-4 py-4 text-center font-semibold">Potongan (Rp)</th>
                    </tr>
                  </thead>
                  <tbody id="tbody-detail-barang" class="divide-y divide-gray-100">
                    <!-- Sample data for demonstration -->
                    <tr class="hover:bg-gray-50/50 transition-colors">
                      <td class="px-4 py-3 text-gray-700 border-r border-gray-100">PROMO001</td>
                      <td class="px-4 py-3 text-gray-600 font-mono border-r border-gray-100">1234567890</td>
                      <td class="px-4 py-3 text-gray-700 border-r border-gray-100">Contoh Nama Barang</td>
                      <td class="px-4 py-3 text-gray-700 text-right border-r border-gray-100">Rp 25,000</td>
                      <td class="px-4 py-3 text-center border-r border-gray-100">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                          10%
                        </span>
                      </td>
                      <td class="px-4 py-3 text-center">
                        <span class="text-gray-500">-</span>
                      </td>
                    </tr>
                    <tr class="hover:bg-gray-50/50 transition-colors">
                      <td class="px-4 py-3 text-gray-700 border-r border-gray-100">PROMO001</td>
                      <td class="px-4 py-3 text-gray-600 font-mono border-r border-gray-100">0987654321</td>
                      <td class="px-4 py-3 text-gray-700 border-r border-gray-100">Contoh Barang Lainnya</td>
                      <td class="px-4 py-3 text-gray-700 text-right border-r border-gray-100">Rp 50,000</td>
                      <td class="px-4 py-3 text-center border-r border-gray-100">
                        <span class="text-gray-500">-</span>
                      </td>
                      <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                          Rp 5,000
                        </span>
                      </td>
                    </tr>
                    <!-- More rows will be populated by JavaScript -->
                  </tbody>
                </table>
              </div>

              <!-- Empty State (hidden by default, shown when no data) -->
              <div id="emptyState" class="flex-1 flex items-center justify-center py-12 hidden">
                <div class="text-center">
                  <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
                  <p class="text-gray-500 text-sm">Tidak ada data barang promo</p>
                </div>
              </div>

              <!-- Loading State -->
              <div id="loadingState" class="flex-1 flex items-center justify-center py-12 hidden">
                <div class="text-center">
                  <div class="w-8 h-8 border-2 border-t-transparent border-blue-500 border-solid rounded-full animate-spin mx-auto mb-4"></div>
                  <p class="text-gray-500 text-sm">Memuat data...</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- custom js file link -->
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- AlpineJS -->
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <!-- Custom JS -->
  <script src="../../js/middleware_auth.js"></script>
  <script src="../../js/promo.js"></script>
  <script>
    document.getElementById("toggle-sidebar").addEventListener("click", function() {
      document.getElementById("sidebar").classList.toggle("open");
    });
    document.addEventListener("DOMContentLoaded", function() {
      const sidebar = document.getElementById("sidebar");
      const closeBtn = document.getElementById("closeSidebar");

      closeBtn.addEventListener("click", function() {
        sidebar.classList.remove("open"); // Hilangkan class .open agar sidebar tertutup
      });
    });
    document.getElementById("toggle-hide").addEventListener("click", function() {
      var sidebarTexts = document.querySelectorAll(".sidebar-text");
      let mainContent = document.getElementById("main-content");
      let sidebar = document.getElementById("sidebar");
      var toggleButton = document.getElementById("toggle-hide");
      var icon = toggleButton.querySelector("i");

      if (sidebar.classList.contains("w-64")) {
        // Sidebar mengecil
        sidebar.classList.remove("w-64", "px-5");
        sidebar.classList.add("w-16", "px-2");
        sidebarTexts.forEach((text) => text.classList.add("hidden")); // Sembunyikan teks
        mainContent.classList.remove("ml-64");
        mainContent.classList.add("ml-16"); // Main ikut mundur
        toggleButton.classList.add("left-20"); // Geser tombol lebih dekat
        toggleButton.classList.remove("left-64");
        icon.classList.remove("fa-angle-left"); // Ubah ikon
        icon.classList.add("fa-angle-right");
      } else {
        // Sidebar membesar
        sidebar.classList.remove("w-16", "px-2");
        sidebar.classList.add("w-64", "px-5");
        sidebarTexts.forEach((text) => text.classList.remove("hidden")); // Tampilkan teks kembali
        mainContent.classList.remove("ml-16");
        mainContent.classList.add("ml-64");
        toggleButton.classList.add("left-64"); // Geser tombol ke posisi awal
        toggleButton.classList.remove("left-20");
        icon.classList.remove("fa-angle-right"); // Ubah ikon
        icon.classList.add("fa-angle-left");
      }
    });
    document.addEventListener("DOMContentLoaded", function() {
      const profileImg = document.getElementById("profile-img");
      const profileCard = document.getElementById("profile-card");

      profileImg.addEventListener("click", function(event) {
        event.preventDefault();
        profileCard.classList.toggle("show");
      });

      // Tutup profile-card jika klik di luar
      document.addEventListener("click", function(event) {
        if (!profileCard.contains(event.target) && !profileImg.contains(event.target)) {
          profileCard.classList.remove("show");
        }
      });
    });
  </script>


</body>

</html>