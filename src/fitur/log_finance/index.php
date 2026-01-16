<?php
session_start();
include '../../../aa_kon_sett.php';

$selected_date = $_GET['tanggal'] ?? date('Y-m-d');

require_once __DIR__ . '/../../component/menu_handler.php';
$menuHandler = new MenuHandler('laporan_log_finance');
if (!$menuHandler->initialize()) {
  exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log Aktivitas Finance</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
  <link rel="stylesheet" href="../../style/header.css">
  <link rel="stylesheet" href="../../style/sidebar.css">
  <link rel="stylesheet" href="../../style/animation-fade-in.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../style/default-font.css">
  <link rel="stylesheet" href="../../output2.css">
  <link rel="stylesheet" href="../../style/pink-theme.css">
  <link rel="icon" type="image/png" href="../../../public/images/logo1.png">

  <style>
    .json-container {
      font-family: 'Consolas', 'Monaco', monospace;
      font-size: 0.85rem;
      white-space: pre-wrap;
      word-break: break-all;
    }

    .diff-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    /* Style tambahan untuk baris yang bisa diklik */
    .clickable-row {
      cursor: pointer;
    }

    .clickable-row:hover {
      background-color: #fdf2f8;
      /* Pink-50ish */
    }

    @media (max-width: 768px) {
      .diff-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body class="bg-gray-50">
  <?php include '../../component/navigation_report.php' ?>
  <?php include '../../component/sidebar_report.php' ?>

  <main id="main-content" class="flex-1 p-6 ml-64">
    <section class="min-h-screen">
      <div class="max-w-7xl mx-auto">

        <div class="header-card p-6 rounded-2xl mb-6">
          <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
              <div class="icon-wrapper bg-pink-100 text-pink-600 p-3 rounded-lg">
                <i class="fas fa-file-invoice-dollar fa-lg"></i>
              </div>
              <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-1">Log Aktivitas Finance</h1>
                </p>
              </div>
            </div>
          </div>

          <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="summary-card total bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
              <div class="summary-icon text-gray-500 mb-2">
                <i class="fas fa-list-ol fa-lg"></i>
              </div>
              <h3 class="text-sm font-semibold text-gray-600 mb-1">Total Aktivitas</h3>
              <p id="summary-total" class="text-3xl font-bold text-gray-900">-</p>
            </div>
            <div
              class="summary-card bg-white p-4 rounded-xl border border-gray-200 shadow-sm border-l-4 border-l-green-500">
              <div class="summary-icon text-green-500 mb-2">
                <i class="fas fa-plus-circle fa-lg"></i>
              </div>
              <h3 class="text-sm font-semibold text-gray-600 mb-1">Insert</h3>
              <p id="summary-insert" class="text-3xl font-bold text-gray-900">-</p>
            </div>
            <div
              class="summary-card bg-white p-4 rounded-xl border border-gray-200 shadow-sm border-l-4 border-l-blue-500">
              <div class="summary-icon text-blue-500 mb-2">
                <i class="fas fa-edit fa-lg"></i>
              </div>
              <h3 class="text-sm font-semibold text-gray-600 mb-1">Update</h3>
              <p id="summary-update" class="text-3xl font-bold text-gray-900">-</p>
            </div>
            <div
              class="summary-card bg-white p-4 rounded-xl border border-gray-200 shadow-sm border-l-4 border-l-red-500">
              <div class="summary-icon text-red-500 mb-2">
                <i class="fas fa-trash-alt fa-lg"></i>
              </div>
              <h3 class="text-sm font-semibold text-gray-600 mb-1">Delete</h3>
              <p id="summary-delete" class="text-3xl font-bold text-gray-900">-</p>
            </div>
          </div>
        </div>

        <div class="filter-card-simple bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-6">
          <form id="filter-form" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
              <label for="tanggal" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-calendar-alt text-pink-600 mr-1"></i>
                Pilih Tanggal
              </label>
              <input type="date" name="tanggal" id="tanggal" value="<?php echo $selected_date; ?>"
                class="input-modern w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            </div>

            <div class="flex-1 min-w-[200px]">
              <label for="search_ref" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-search text-pink-600 mr-1"></i>
                Cari Ref ID
              </label>
              <input type="text" name="search_ref" id="search_ref" placeholder="Contoh: INV/2023/..."
                class="input-modern w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            </div>

            <button type="submit" id="filter-submit-button"
              class="btn-primary px-6 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded-lg font-medium inline-flex items-center gap-2 transition-colors">
              <i class="fas fa-filter"></i>
              <span>Tampilkan</span>
            </button>
          </form>
        </div>

        <div class="filter-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="p-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">
              <i class="fas fa-history text-pink-600 mr-2"></i>
              Riwayat Perubahan (<span id="tanggal-dipilih-teks"><?php echo htmlspecialchars($selected_date); ?></span>)
            </h3>
          </div>
          <div class="table-container overflow-x-auto">
            <table class="table-modern w-full text-left border-collapse">
              <thead class="bg-gray-100 text-gray-600 uppercase text-xs font-bold">
                <tr>
                  <th class="p-4 border-b"><i class="far fa-clock mr-2"></i>Waktu</th>
                  <th class="p-4 border-b"><i class="fas fa-user mr-2"></i>User</th>
                  <th class="p-4 border-b"><i class="fas fa-table mr-2"></i>Table Name</th>
                  <th class="p-4 border-b"><i class="fas fa-fingerprint mr-2"></i>Ref ID</th>
                  <th class="p-4 border-b"><i class="fas fa-bolt mr-2"></i>Action</th>
                </tr>
              </thead>
              <tbody id="log-table-body" class="text-sm text-gray-700">
                <tr>
                  <td colspan="5" class="text-center p-8">
                    <div
                      class="spinner-simple animate-spin inline-block w-6 h-6 border-2 border-current border-t-transparent text-pink-600 rounded-full">
                    </div>
                    <p class="mt-3 text-gray-500 font-medium">Memuat data...</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="pagination-container"
            class="flex flex-wrap justify-between items-center p-4 border-t border-gray-200">
            <span id="pagination-info" class="text-sm text-gray-600 mb-2 sm:mb-0"></span>
            <div id="pagination-links" class="flex items-center gap-2"></div>
          </div>
        </div>

      </div>
    </section>
  </main>

  <div id="logDetailModal"
    class="modal-overlay fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center p-4"
    style="display: none;">
    <div class="modal-content relative bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[90vh] flex flex-col">
      <div class="flex justify-between items-center border-b border-gray-200 p-6">
        <div>
          <h3 class="text-xl font-bold text-gray-900">
            <i class="fas fa-search text-pink-600 mr-2"></i>
            Detail Perubahan Data
          </h3>
          <p class="text-sm text-gray-500 mt-1">ID Log: <span id="modalLogId" class="font-mono font-bold"></span></p>
        </div>
        <button id="closeLogModal"
          class="text-gray-400 hover:text-gray-600 text-3xl leading-none transition-colors">&times;</button>
      </div>

      <div id="modalBodyContent" class="p-6 overflow-y-auto bg-gray-50 flex-1">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 text-sm">
          <div class="bg-white p-3 rounded border">
            <span class="text-gray-500 block text-xs">IP Address</span>
            <span id="modalIp" class="font-semibold text-gray-800">-</span>
          </div>
          <div class="bg-white p-3 rounded border">
            <span class="text-gray-500 block text-xs">User Agent</span>
            <span id="modalUa" class="font-semibold text-gray-800">-</span>
          </div>
        </div>

        <div class="diff-grid">
          <div class="card-old bg-red-50 border border-red-100 rounded-lg p-4">
            <h4 class="font-bold text-red-700 mb-2 border-b border-red-200 pb-2">DATA LAMA (Old)</h4>
            <pre id="contentOld" class="json-container text-red-800">null</pre>
          </div>
          <div class="card-new bg-green-50 border border-green-100 rounded-lg p-4">
            <h4 class="font-bold text-green-700 mb-2 border-b border-green-200 pb-2">DATA BARU (New)</h4>
            <pre id="contentNew" class="json-container text-green-800">null</pre>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="/src/js/middleware_auth.js"></script>
  <script src="../../js/log_finance/log_handler.js" type="module"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</body>

</html>