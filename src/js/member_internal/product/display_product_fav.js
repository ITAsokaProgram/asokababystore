import { filterProductFav } from "./fetch_product.js";
let charts = {};
let currentPage = 1;
let itemsPerPage = 10;
let currentSearch = "";
let currentSort = "qty";
let totalPages = 1;
let currentDateFilter = {
  filter_type: null,
  filter: null,
  start_date: null,
  end_date: null,
};
let currentStatus = null;
const formatDateLocal = (date) => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};
const initProductFavoriteDisplay = async () => {
  setupEventListeners();
  await loadTableData(1);
  await loadTrendData();
  await loadProductPerformance();
};
const setupEventListeners = () => {
  document.getElementById("refresh-btn").addEventListener("click", () => {
    loadTableData(1);
  });
  const searchInput = document.getElementById("search-input");
  if (searchInput) {
    searchInput.addEventListener("change", (e) => {
      currentSearch = e.target.value;
      loadTableData(1);
    });
  }
  const sortSelect = document.getElementById("sort-select");
  if (sortSelect) {
    sortSelect.addEventListener("change", (e) => {
      currentSort = e.target.value;
      loadTableData(1);
    });
  }
  setupDateFilterListeners();
};
const getDatesFromPreset = (filter) => {
  const today = new Date();
  let startDate = new Date(today);
  let endDate = new Date(today);
  switch (filter) {
    case "kemarin":
      startDate.setDate(today.getDate() - 1);
      endDate.setDate(today.getDate() - 1);
      break;
    case "1minggu":
      startDate.setDate(today.getDate() - 7);
      break;
    case "1bulan":
      startDate.setMonth(today.getMonth() - 1);
      break;
    case "3bulan":
      startDate.setMonth(today.getMonth() - 3);
      break;
    case "6bulan":
      startDate.setMonth(today.getMonth() - 6);
      break;
    case "9bulan":
      startDate.setMonth(today.getMonth() - 9);
      break;
    case "12bulan":
      startDate.setFullYear(today.getFullYear() - 1);
      break;
    default:
      startDate.setDate(today.getDate() - 1);
      endDate.setDate(today.getDate() - 1);
      break;
  }
  return {
    start: formatDateLocal(startDate),
    end: formatDateLocal(endDate),
  };
};
const setupDateFilterListeners = () => {
  const startDateInput = document.getElementById("start-date");
  const endDateInput = document.getElementById("end-date");
  const applyFilterBtn = document.getElementById("apply-date-filter");
  const resetFilterBtn = document.getElementById("reset-date-filter");
  const dateRangeDisplay = document.getElementById("date-range-display");
  if (!startDateInput || !endDateInput || !applyFilterBtn || !resetFilterBtn) {
    return;
  }
  const urlParams = new URLSearchParams(window.location.search);
  const todayDateObj = new Date();
  const today = formatDateLocal(todayDateObj);
  const urlFilterType = urlParams.get("filter_type");
  const urlFilter = urlParams.get("filter");
  const urlStartDate = urlParams.get("start_date");
  const urlEndDate = urlParams.get("end_date");
  currentStatus = urlParams.get("status");
  let displayStartDate, displayEndDate;
  if (urlFilterType === "preset" && urlFilter) {
    const dates = getDatesFromPreset(urlFilter);
    displayStartDate = dates.start;
    displayEndDate = dates.end;
    currentDateFilter.filter_type = "custom";
    currentDateFilter.filter = null;
    currentDateFilter.start_date = displayStartDate;
    currentDateFilter.end_date = displayEndDate;
  } else if (urlFilterType === "custom" && urlStartDate) {
    displayStartDate = urlStartDate;
    displayEndDate = urlEndDate;
    currentDateFilter.filter_type = urlFilterType;
    currentDateFilter.filter = null;
    currentDateFilter.start_date = urlStartDate;
    currentDateFilter.end_date = urlEndDate;
  } else {
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    displayStartDate = formatDateLocal(yesterday);
    displayEndDate = displayStartDate;
    currentDateFilter.filter_type = "custom";
    currentDateFilter.start_date = displayStartDate;
    currentDateFilter.end_date = displayEndDate;
  }
  startDateInput.value = displayStartDate;
  endDateInput.value = displayEndDate;
  endDateInput.max = today;
  const s = new Date(displayStartDate + "T00:00:00");
  const e = new Date(displayEndDate + "T00:00:00");
  const timeDiff = e.getTime() - s.getTime();
  const daysDiff = Math.max(1, Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1);
  if (dateRangeDisplay) {
    dateRangeDisplay.innerHTML = ` <i class="fas fa-info-circle text-emerald-400 mr-1"></i>(Data ${daysDiff} hari)`;
  }
  applyFilterBtn.addEventListener("click", async () => {
    const startVal = startDateInput.value;
    const endVal = endDateInput.value;
    if (!startVal || !endVal) {
      showToast("Pilih tanggal awal dan akhir", "warning");
      return;
    }
    if (new Date(startVal) > new Date(endVal)) {
      showToast(
        "Tanggal awal tidak boleh lebih besar dari tanggal akhir",
        "warning"
      );
      return;
    }
    currentDateFilter.filter_type = "custom";
    currentDateFilter.start_date = startVal;
    currentDateFilter.end_date = endVal;
    currentDateFilter.filter = null;
    const s = new Date(startVal + "T00:00:00");
    const e = new Date(endVal + "T00:00:00");
    const diff = Math.max(1, Math.ceil((e - s) / (1000 * 3600 * 24)) + 1);
    if (dateRangeDisplay) {
      dateRangeDisplay.innerHTML = ` <i class="fas fa-info-circle text-emerald-400 mr-1"></i>(Data ${diff} hari)`;
    }
    window.history.pushState({}, document.title, window.location.pathname);
    loadTableData(1);
  });
  resetFilterBtn.addEventListener("click", async () => {
    const d = new Date();
    d.setDate(d.getDate() - 1);
    const yesterdayISO = formatDateLocal(d);
    if (dateRangeDisplay) {
      dateRangeDisplay.innerHTML = ` <i class="fas fa-info-circle text-emerald-400 mr-1"></i>(Data 1 hari lalu)`;
    }
    startDateInput.value = yesterdayISO;
    endDateInput.value = yesterdayISO;
    currentDateFilter.filter_type = "custom";
    currentDateFilter.start_date = yesterdayISO;
    currentDateFilter.end_date = yesterdayISO;
    currentDateFilter.filter = null;
    window.history.pushState({}, document.title, window.location.pathname);
    loadTableData(1);
  });
};
const loadTableData = async (page = 1) => {
  showLoading();
  currentPage = page;
  const params = {
    filter_type: currentDateFilter.filter_type,
    filter: currentDateFilter.filter,
    start_date: currentDateFilter.start_date,
    end_date: currentDateFilter.end_date,
    status: currentStatus,
    page: currentPage,
    limit: itemsPerPage,
    search: currentSearch,
    sort_by: currentSort,
  };
  Object.keys(params).forEach((key) => {
    if (params[key] === null || params[key] === undefined) {
      delete params[key];
    }
  });
  try {
    const response = await filterProductFav(params);
    handleTableResponse(response);
    updateLastUpdate();
  } catch (error) {
    console.error("Error loading table data:", error);
    showEmptyState();
    showToast("Terjadi kesalahan saat memuat data tabel", "error");
  }
};
const handleTableResponse = (response) => {
  if (!response || response.success === false) {
    showToast(response?.message || "Data tabel tidak ditemukan", "error");
    showEmptyState();
    renderPagination(null);
    return;
  }
  renderTableData(response.data, response.pagination.offset);
  renderPagination(response.pagination);
  hideLoading();
};
const showToast = (message, type = "info") => {
  const background =
    type === "error" ? "#f87171" : type === "warning" ? "#fbbf24" : "#34d399";
  Toastify({
    text: message,
    duration: 3000,
    gravity: "top",
    position: "right",
    style: {
      background: background,
      color: "#fff",
    },
  }).showToast();
};
const loadTrendData = async () => {
  try {
    const response = await fetch(
      `/src/api/member/product/get_trend_pembelian.php`
    );
    const data = await response.json();
    if (data.success && data.data && Array.isArray(data.data)) {
      updateMonthlyTrendChart(data.data);
    }
  } catch (error) {
    console.error("Error loading trend data:", error);
  }
};
const loadProductPerformance = async () => {
  try {
    const response = await fetch(
      "/src/api/member/product/get_product_performa.php"
    );
    const data = await response.json();
    if (data.success && data.data && Array.isArray(data.data)) {
      updateProductPerformance(data.data);
    }
  } catch (error) {
    console.error("Error loading product performance data:", error);
  }
};
const showLoading = () => {
  document.getElementById("loading-state").classList.remove("hidden");
  document.getElementById("empty-state").classList.add("hidden");
  document.getElementById("member-table-body").innerHTML = "";
};
const hideLoading = () => {
  document.getElementById("loading-state").classList.add("hidden");
};
const showEmptyState = () => {
  document.getElementById("loading-state").classList.add("hidden");
  document.getElementById("empty-state").classList.remove("hidden");
  document.getElementById("member-table-body").innerHTML = "";
};
const renderTableData = (paginatedData, offset) => {
  const tbody = document.getElementById("member-table-body");
  if (paginatedData.length === 0) {
    tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-search text-2xl mb-2"></i>
                    <p>Tidak ada data yang ditemukan</p>
                </td>
            </tr>
        `;
    return;
  }
  tbody.innerHTML = paginatedData
    .map(
      (item, index) => `
        <tr class="hover:bg-gray-50 transition-colors duration-200">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-400 to-blue-600 flex items-center justify-center">
                            <span class="text-white font-bold text-sm">${getInitials(
        item.nama_customer
      )}</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">${item.nama_customer
        }</div>
                        <div class="text-sm text-gray-500">${item.kd_cust}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 font-medium">${item.barang
        }</div>
                <div class="text-sm text-gray-500">PLU: ${item.plu}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                <span class="font-bold text-green-600">${parseInt(
          item.total_qty
        ).toLocaleString()} pcs</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                <span class="font-bold">Rp ${parseInt(
          item.total_hrg
        ).toLocaleString()}</span>
            </td>
        </tr>
    `
    )
    .join("");
};
const renderPagination = (pagination) => {
  const paginationContainer = document.getElementById("paginationContainer");
  const viewData = document.getElementById("viewData");
  if (!paginationContainer || !viewData || !pagination) {
    if (paginationContainer) paginationContainer.innerHTML = "";
    if (viewData) viewData.innerHTML = "";
    return;
  }
  const { current_page, total_pages, total_records } = pagination;
  totalPages = total_pages;
  const startRecord = (current_page - 1) * itemsPerPage + 1;
  const endRecord = Math.min(startRecord + itemsPerPage - 1, total_records);
  viewData.textContent = `Menampilkan ${startRecord} - ${endRecord} dari ${total_records} data`;
  let paginationHTML = "";
  if (current_page > 1) {
    paginationHTML += `<button onclick="handlePaginationClick(${current_page - 1
      })" class="px-2 py-1 rounded-md bg-gray-200 hover:bg-gray-300 text-gray-700">&laquo; Prev</button>`;
  }
  const maxPagesToShow = 5;
  let startPage = Math.max(1, current_page - Math.floor(maxPagesToShow / 2));
  let endPage = Math.min(total_pages, startPage + maxPagesToShow - 1);
  if (endPage - startPage + 1 < maxPagesToShow) {
    startPage = Math.max(1, endPage - maxPagesToShow + 1);
  }
  if (startPage > 1) {
    paginationHTML += `<button onclick="handlePaginationClick(1)" class="px-2 py-1 rounded-md bg-gray-200 hover:bg-gray-300 text-gray-700">1</button>`;
    if (startPage > 2) {
      paginationHTML += `<span class="px-2 py-1 text-gray-500">...</span>`;
    }
  }
  for (let i = startPage; i <= endPage; i++) {
    if (i === current_page) {
      paginationHTML += `<button class="px-2 py-1 rounded-md bg-emerald-500 text-white font-bold">${i}</button>`;
    } else {
      paginationHTML += `<button onclick="handlePaginationClick(${i})" class="px-2 py-1 rounded-md bg-gray-200 hover:bg-gray-300 text-gray-700">${i}</button>`;
    }
  }
  if (endPage < total_pages) {
    if (endPage < total_pages - 1) {
      paginationHTML += `<span class="px-2 py-1 text-gray-500">...</span>`;
    }
    paginationHTML += `<button onclick="handlePaginationClick(${total_pages})" class="px-2 py-1 rounded-md bg-gray-200 hover:bg-gray-300 text-gray-700">${total_pages}</button>`;
  }
  if (current_page < total_pages) {
    paginationHTML += `<button onclick="handlePaginationClick(${current_page + 1
      })" class="px-2 py-1 rounded-md bg-gray-200 hover:bg-gray-300 text-gray-700">Next &raquo;</button>`;
  }
  paginationContainer.innerHTML = paginationHTML;
};
const updateProductPerformance = (performanceData) => {
  const performanceContainer = document.getElementById("product-performance");
  const top4Data = performanceData.slice(0, 6);
  performanceContainer.innerHTML = `
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      ${top4Data
      .map((product, index) => {
        const currentQty = parseInt(product.qty_periode_sekarang);
        const previousQty = parseInt(product.qty_periode_sebelumnya);
        const difference = currentQty - previousQty;
        const percentageChange =
          previousQty > 0 ? ((difference / previousQty) * 100).toFixed(1) : 0;
        const isIncrease = difference > 0;
        const isDecrease = difference < 0;
        let borderColor = "border-gray-400";
        let iconColor = "text-gray-500";
        let icon = "fa-minus";
        let changeText = "Tidak berubah";
        if (isIncrease) {
          borderColor = "border-green-500";
          iconColor = "text-green-500";
          icon = "fa-arrow-up";
          changeText = `+${percentageChange}%`;
        } else if (isDecrease) {
          borderColor = "border-red-500";
          iconColor = "text-red-500";
          icon = "fa-arrow-down";
          changeText = `${percentageChange}%`;
        }
        return `
                <div class="bg-white rounded-lg border-l-4 ${borderColor} shadow-sm hover:shadow-md transition-shadow duration-200">
                  <div class="p-4">
                    <div class="flex items-center justify-between mb-3">
                      <div class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold">
                        ${index + 1}
                      </div>
                      <div class="flex items-center space-x-1">
                        <i class="fas ${icon} text-xs ${iconColor}"></i>
                        <span class="text-xs font-medium ${iconColor}">${changeText}</span>
                      </div>
                    </div>
                    <div class="mb-3">
                      <h4 class="font-semibold text-gray-900 text-sm leading-tight mb-1">${product.barang
          }</h4>
                      <p class="text-xs text-gray-500">PLU: ${product.plu}</p>
                    </div>
                    <div class="flex items-center justify-between">
                      <div>
                        <p class="text-xs text-gray-500">Periode Sekarang</p>
                        <p class="font-bold text-green-600 text-sm">${currentQty.toLocaleString()} pcs</p>
                      </div>
                      <div class="text-right">
                        <p class="text-xs text-gray-500">Periode Sebelumnya</p>
                        <p class="font-medium text-gray-700 text-sm">${previousQty.toLocaleString()} pcs</p>
                      </div>
                    </div>
                  </div>
                </div>
              `;
      })
      .join("")}
    </div>
  `;
};
const updateCharts = async () => {
  await loadTrendData();
};
const updateMonthlyTrendChart = (trendData) => {
  const ctx = document.getElementById("monthlyTrendChart");
  if (charts.monthlyTrendChart) {
    charts.monthlyTrendChart.destroy();
  }
  const monthNames = [
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "Desember",
  ];
  const sortedData = trendData.sort((a, b) => a.bulan - b.bulan);
  const labels = sortedData.map((item) => monthNames[item.bulan - 1]);
  const quantities = sortedData.map((item) => parseInt(item.total_qty));
  charts.monthlyTrendChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Total Quantity",
          data: quantities,
          borderColor: "#3B82F6",
          backgroundColor: "rgba(59, 130, 246, 0.1)",
          borderWidth: 3,
          fill: true,
          tension: 0.4,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: "rgba(0, 0, 0, 0.1)" },
          ticks: {
            callback: function (value) {
              return value.toLocaleString() + " pcs";
            },
          },
        },
        x: { grid: { display: false } },
      },
    },
  });
};
const updateLastUpdate = () => {
  const now = new Date();
  document.getElementById("last-update").textContent =
    now.toLocaleString("id-ID");
};
const getInitials = (name) => {
  if (!name || typeof name !== "string") {
    return "NA";
  }
  try {
    return name
      .split(" ")
      .map((word) => word.charAt(0))
      .join("")
      .toUpperCase()
      .slice(0, 2);
  } catch (error) {
    return "NA";
  }
};
const exportMemberData = (kdCust) => { };
const exportAllDataToExcel = async () => {
  // 1. Tampilkan Loading SweetAlert
  Swal.fire({
    title: "Menyiapkan Excel...",
    text: "Sedang mengambil dan menyusun data...",
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });

  try {
    // 2. Siapkan Parameter (Ambil SEMUA data dengan limit besar)
    const params = {
      filter_type: currentDateFilter.filter_type,
      filter: currentDateFilter.filter,
      start_date: currentDateFilter.start_date,
      end_date: currentDateFilter.end_date,
      status: currentStatus,
      page: 1,
      limit: 999999, // Limit besar untuk mengambil semua data
      search: currentSearch,
      sort_by: currentSort,
    };

    // Bersihkan parameter null/undefined
    Object.keys(params).forEach((key) => {
      if (params[key] === null || params[key] === undefined) {
        delete params[key];
      }
    });

    // 3. Fetch Data dari API
    const response = await filterProductFav(params);

    if (
      !response ||
      response.success === false ||
      !response.data ||
      response.data.length === 0
    ) {
      Swal.fire("Info", "Tidak ada data untuk diexport", "info");
      return;
    }

    const data = response.data;

    // 4. Setup ExcelJS
    // Pastikan ExcelJS sudah di-load di HTML via CDN
    const workbook = new ExcelJS.Workbook();
    const sheet = workbook.addWorksheet("Produk Favorit");

    // Tentukan Teks Periode untuk Judul
    let periodeText = "";
    if (currentDateFilter.start_date && currentDateFilter.end_date) {
      periodeText = `${currentDateFilter.start_date} s/d ${currentDateFilter.end_date}`;
    } else {
      periodeText = "Semua Periode";
    }

    // 5. Definisi Kolom
    sheet.columns = [
      { key: "no", width: 5 },
      { key: "kd_cust", width: 15 },
      { key: "nama_customer", width: 30 },
      { key: "barang", width: 35 },
      { key: "plu", width: 15 },
      { key: "total_qty", width: 15 },
      { key: "total_hrg", width: 20 },
    ];

    // 6. Buat Judul (Merged Cells)
    sheet.mergeCells("A1:G1");
    const titleCell = sheet.getCell("A1");
    titleCell.value = `LAPORAN PRODUK FAVORIT MEMBER - ${periodeText}`;
    titleCell.font = { name: "Arial", size: 14, bold: true };
    titleCell.alignment = { horizontal: "center", vertical: "middle" };

    // 7. Buat Header Table
    const headers = [
      "No", "Kode Member", "Nama Member", "Nama Produk", "PLU", "Total Qty", "Total Harga (Rp)"
    ];

    const headerRow = sheet.getRow(3);
    headerRow.values = headers;

    // Styling Header (Background Hijau Emerald, Teks Putih)
    headerRow.eachCell((cell) => {
      cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
      cell.fill = {
        type: "pattern",
        pattern: "solid",
        fgColor: { argb: "FF10B981" }, // Warna Emerald-500
      };
      cell.alignment = { horizontal: "center", vertical: "middle" };
      cell.border = {
        top: { style: "thin" },
        left: { style: "thin" },
        bottom: { style: "thin" },
        right: { style: "thin" },
      };
    });

    // 8. Isi Data ke Rows
    const currencyFmt = "#,##0"; // Format angka
    let rowNum = 4;

    data.forEach((item, index) => {
      const r = sheet.getRow(rowNum);

      // Pastikan tipe data number benar agar bisa dijumlah/diformat di Excel
      const qty = parseInt(item.total_qty) || 0;
      const harga = parseFloat(item.total_hrg) || 0;

      r.values = [
        index + 1,
        item.kd_cust,
        item.nama_customer,
        item.barang,
        item.plu,
        qty,
        harga
      ];

      // Format Angka & Currency
      r.getCell(6).numFmt = "#,##0"; // Qty
      r.getCell(6).alignment = { horizontal: "center" };

      r.getCell(7).numFmt = currencyFmt; // Total Harga
      r.getCell(7).alignment = { horizontal: "right" };

      // Beri Border pada setiap sel
      r.eachCell((cell) => {
        cell.border = {
          top: { style: "thin" },
          left: { style: "thin" },
          bottom: { style: "thin" },
          right: { style: "thin" },
        };
      });

      rowNum++;
    });

    // 9. Download File
    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], {
      type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });
    const url = window.URL.createObjectURL(blob);
    const anchor = document.createElement("a");
    anchor.href = url;

    const todayStr = formatDateLocal(new Date());
    const filename = `Laporan_Produk_Favorit_${todayStr}.xlsx`;

    anchor.download = filename;
    anchor.click();
    window.URL.revokeObjectURL(url);

    // 10. Tutup Loading & Beri Notifikasi Sukses
    Swal.fire({
      icon: "success",
      title: "Berhasil",
      text: "Data berhasil diexport ke Excel.",
      timer: 1500,
      showConfirmButton: false,
    });

  } catch (e) {
    console.error(e);
    Swal.fire("Error", "Gagal melakukan export data: " + e.message, "error");
  }
};
const convertToCSV = (data) => { };
const calculateStartDateFromFilter = (filter) => { };
document.addEventListener("DOMContentLoaded", () => {
  initProductFavoriteDisplay();
});
window.exportMemberData = exportMemberData;
window.exportAllDataToExcel = exportAllDataToExcel;
window.handlePaginationClick = (page) => {
  loadTableData(page);
};
