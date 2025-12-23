import { fetchTopProducts, fetchPaginatedProducts } from "./fetch_product.js";
import {
  showGlobalLoading,
  hideGlobalLoading,
  showToast,
  formatCurrency,
  formatNumber,
} from "./ui_helpers.js";
import { lazyLoadList } from "./lazy_load_list.js";
let currentDateFilter = {
  filter_type: null,
  filter: null,
  start_date: null,
  end_date: null,
};
let currentStatus = null;
let currentType = "all";
let currentPage = 1;
let itemsPerPage = 10;
let currentSearch = "";
let currentSort = "belanja";
let totalPages = 1;
const formatDescription = (descp) => {
  if (!descp) return "Nama Produk Tidak Ditemukan";
  return descp.length > 50 ? descp.substring(0, 50) + "..." : descp;
};
const updateTopProductsMember = (productData) => {
  lazyLoadList(productData, "top-products-member", (product, index) => {
    const getRankColors = (rank) => {
      if (rank === 1)
        return {
          border: "border-l-amber-400",
          bg: "bg-gradient-to-r from-amber-50 to-yellow-50",
          rankBg: "bg-gradient-to-r from-amber-400 to-yellow-500",
          rankText: "text-white",
          shadow: "shadow-amber-100",
        };
      if (rank === 2)
        return {
          border: "border-l-slate-400",
          bg: "bg-gradient-to-r from-slate-50 to-gray-50",
          rankBg: "bg-gradient-to-r from-slate-400 to-gray-500",
          rankText: "text-white",
          shadow: "shadow-slate-100",
        };
      if (rank === 3)
        return {
          border: "border-l-orange-400",
          bg: "bg-gradient-to-r from-orange-50 to-amber-50",
          rankBg: "bg-gradient-to-r from-orange-400 to-amber-500",
          rankText: "text-white",
          shadow: "shadow-orange-100",
        };
      return {
        border: "border-l-blue-400",
        bg: "bg-gradient-to-r from-blue-50 to-indigo-50",
        rankBg: "bg-gradient-to-r from-blue-400 to-indigo-500",
        rankText: "text-white",
        shadow: "shadow-blue-100",
      };
    };
    const colors = getRankColors(index + 1);
    const rank = index + 1;
    const widthPercentage = Math.min(
      100,
      (product.total_penjualan /
        Math.max(...productData.map((p) => p.total_penjualan))) *
        100
    );
    return `
        <div class="${colors.bg} rounded-xl ${colors.border} border-l-4 ${
      colors.shadow
    } shadow-lg group overflow-hidden">
            <div class="p-3 relative">
                <div class="flex items-center justify-between">
                    <div class="flex items-center min-w-0">
                        <div class="w-10 h-10 ${colors.rankBg} ${
      colors.rankText
    } rounded-full flex items-center justify-center text-sm font-bold mr-2 shadow-lg">${rank}</div>
                        <div class="space-y-1 min-w-0">
                            <div class="font-bold text-gray-800 text-sm leading-tight truncate" title="${
                              product.descp
                            }">
                                ${formatDescription(product.descp)}
                            </div>
                            <div class="text-sm text-gray-600 font-medium bg-gray-100 px-2 py-1 rounded-md inline-block">
                                ${product.plu}
                            </div>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0 ml-2">
                        <div class="font-bold text-emerald-600 text-sm mb-1">${formatCurrency(
                          product.total_penjualan
                        )}</div>
                        <div class="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full">Qty: ${formatNumber(
                          product.total_qty
                        )}</div>
                    </div>
                </div>
                <div class="mt-2 bg-gray-200 rounded-full h-2">
                    <div class="${
                      colors.rankBg
                    } h-full rounded-full" style="width: ${widthPercentage}%"></div>
                </div>
            </div>
        </div>`;
  });
};
const updateTopProductsNonMember = (productData) => {
  lazyLoadList(productData, "top-products-non-member", (product, index) => {
    const getRankColors = (rank) => {
      if (rank === 1)
        return {
          border: "border-l-purple-400",
          bg: "bg-gradient-to-r from-purple-50 to-violet-50",
          rankBg: "bg-gradient-to-r from-purple-400 to-violet-500",
          rankText: "text-white",
          shadow: "shadow-purple-100",
        };
      if (rank === 2)
        return {
          border: "border-l-pink-400",
          bg: "bg-gradient-to-r from-pink-50 to-rose-50",
          rankBg: "bg-gradient-to-r from-pink-400 to-rose-500",
          rankText: "text-white",
          shadow: "shadow-pink-100",
        };
      if (rank === 3)
        return {
          border: "border-l-cyan-400",
          bg: "bg-gradient-to-r from-cyan-50 to-teal-50",
          rankBg: "bg-gradient-to-r from-cyan-400 to-teal-500",
          rankText: "text-white",
          shadow: "shadow-cyan-100",
        };
      return {
        border: "border-l-slate-400",
        bg: "bg-gradient-to-r from-slate-50 to-gray-50",
        rankBg: "bg-gradient-to-r from-slate-400 to-gray-500",
        rankText: "text-white",
        shadow: "shadow-slate-100",
      };
    };
    const colors = getRankColors(index + 1);
    const rank = index + 1;
    const widthPercentage = Math.min(
      100,
      (product.total_penjualan /
        Math.max(...productData.map((p) => p.total_penjualan))) *
        100
    );
    return `
        <div class="${colors.bg} rounded-xl ${colors.border} border-l-4 ${
      colors.shadow
    } shadow-lg group overflow-hidden">
            <div class="p-3 relative">
                <div class="flex items-center justify-between">
                    <div class="flex items-center min-w-0">
                        <div class="w-10 h-10 ${colors.rankBg} ${
      colors.rankText
    } rounded-full flex items-center justify-center text-sm font-bold mr-2 shadow-lg">${rank}</div>
                        <div class="space-y-1 min-w-0">
                            <div class="font-bold text-gray-800 text-sm leading-tight truncate" title="${
                              product.descp
                            }">
                                ${formatDescription(product.descp)}
                            </div>
                            <div class="text-sm text-gray-600 font-medium bg-gray-100 px-2 py-1 rounded-md inline-block">
                                ${product.plu}
                            </div>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0 ml-2">
                        <div class="font-bold text-emerald-600 text-sm mb-1">${formatCurrency(
                          product.total_penjualan
                        )}</div>
                        <div class="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full">Qty: ${formatNumber(
                          product.total_qty
                        )}</div>
                    </div>
                </div>
                <div class="mt-2 bg-gray-200 rounded-full h-2">
                    <div class="${
                      colors.rankBg
                    } h-full rounded-full" style="width: ${widthPercentage}%"></div>
                </div>
            </div>
        </div>`;
  });
};
const renderProductTableData = (productData, offset = 0) => {
  const tbody = document.getElementById("top-products-table-body");
  if (!tbody) return;
  if (productData.length === 0) {
    tbody.innerHTML = `
        <tr>
            <td colspan="5" class="px-4 py-4 text-center text-gray-500">
                <i class="fas fa-search text-2xl mb-1"></i>
                <p>Tidak ada data yang ditemukan</p>
            </td>
        </tr>`;
    return;
  }
  tbody.innerHTML = productData
    .map((item, index) => {
      const rowNumber = offset + index + 1;
      const qty = parseInt(item.total_qty || 0);
      const belanja = parseFloat(item.total_penjualan || 0);
      return `
        <tr class="hover:bg-gray-50 transition-colors duration-200">
            <td class="px-3 py-2 whitespace-nowrap text-center">
                <div class="flex items-center justify-center">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 font-bold text-sm">
                        ${rowNumber}
                    </span>
                </div>
            </td>
            <td class="px-3 py-2">
                <div class="text-sm font-bold text-gray-900" title="${
                  item.descp
                }">
                    ${formatDescription(item.descp)}
                </div>
            </td>
            <td class="px-3 py-2 whitespace-nowrap">
                <div class="text-sm text-gray-600 font-mono">${item.plu}</div>
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-center">
                <span class="font-bold text-gray-700">${formatNumber(
                  qty
                )} pcs</span>
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-right">
                <span class="font-bold text-green-600">${formatCurrency(
                  belanja
                )}</span>
            </td>
        </tr>`;
    })
    .join("");
};
const showLoading = () => {
  document.getElementById("loading-state").classList.remove("hidden");
  document.getElementById("empty-state").classList.add("hidden");
  document.getElementById("top-products-table-body").innerHTML = "";
};
const hideLoading = () => {
  document.getElementById("loading-state").classList.add("hidden");
  document.getElementById("empty-state").classList.add("hidden");
};
const showEmptyState = () => {
  document.getElementById("loading-state").classList.add("hidden");
  document.getElementById("empty-state").classList.remove("hidden");
  document.getElementById("top-products-table-body").innerHTML = "";
};
const initTopProductsDisplay = async () => {
  setupEventListeners();
  await loadTop50Cards();
  await loadTableData(1);
};
const setupEventListeners = () => {
  setupDateFilterListeners();
  document.getElementById("search-input")?.addEventListener("change", (e) => {
    currentSearch = e.target.value;
    loadTableData(1);
  });
  document.getElementById("sort-select")?.addEventListener("change", (e) => {
    currentSort = e.target.value;
    loadTableData(1);
  });
  document.getElementById("type-select")?.addEventListener("change", (e) => {
    currentType = e.target.value;
    loadTableData(1);
  });
};
const getDatesFromPreset = (filter) => {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
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
    start: startDate.toISOString().split("T")[0],
    end: endDate.toISOString().split("T")[0],
  };
};
const setupDateFilterListeners = () => {
  const startDateInput = document.getElementById("start-date");
  const endDateInput = document.getElementById("end-date");
  const applyFilterBtn = document.getElementById("apply-date-filter");
  const resetFilterBtn = document.getElementById("reset-date-filter");
  const dateRangeDisplay = document.getElementById("date-range-display");
  if (
    !startDateInput ||
    !endDateInput ||
    !applyFilterBtn ||
    !resetFilterBtn ||
    !dateRangeDisplay
  ) {
    console.warn("Date filter elements not found on top_products.php");
    return;
  }
  const urlParams = new URLSearchParams(window.location.search);
  const today = new Date().toISOString().split("T")[0];
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
    currentDateFilter.filter_type = urlFilterType;
    currentDateFilter.filter = urlFilter;
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
    displayStartDate = yesterday.toISOString().split("T")[0];
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
  dateRangeDisplay.innerHTML = ` <i class="fas fa-info-circle text-yellow-500 mr-1"></i>(Data ${daysDiff} hari)`;
  applyFilterBtn.addEventListener("click", async () => {
    const startVal = startDateInput.value;
    const endVal = endDateInput.value;
    if (!startVal || !endVal) {
      showToast("Silakan pilih tanggal mulai dan tanggal akhir", "warning");
      return;
    }
    if (new Date(startVal) > new Date(endVal)) {
      showToast(
        "Tanggal mulai tidak boleh lebih besar dari tanggal akhir",
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
    dateRangeDisplay.innerHTML = ` <i class="fas fa-info-circle text-yellow-500 mr-1"></i>(Data ${diff} hari)`;
    window.history.pushState({}, document.title, window.location.pathname);
    await loadTop50Cards();
    await loadTableData(1);
  });
  resetFilterBtn.addEventListener("click", async () => {
    const d = new Date();
    const y = new Date(d);
    y.setDate(d.getDate() - 1);
    const startVal = y.toISOString().split("T")[0];
    const endVal = startVal;
    dateRangeDisplay.innerHTML = ` <i class="fas fa-info-circle text-yellow-500 mr-1"></i>(Data 1 hari)`;
    startDateInput.value = startVal;
    endDateInput.value = endVal;
    currentDateFilter.filter_type = "custom";
    currentDateFilter.start_date = startVal;
    currentDateFilter.end_date = endVal;
    currentDateFilter.filter = null;
    window.history.pushState({}, document.title, window.location.pathname);
    await loadTop50Cards();
    await loadTableData(1);
  });
};
const loadTop50Cards = async () => {
  showGlobalLoading();
  try {
    const topProductResponse = await fetchTopProducts(
      currentDateFilter,
      currentStatus
    );
    if (topProductResponse && topProductResponse.success) {
      updateTopProductsMember(topProductResponse.data);
      updateTopProductsNonMember(topProductResponse.data_non || []);
    } else {
      updateTopProductsMember([]);
      updateTopProductsNonMember([]);
    }
  } catch (error) {
    console.error("Error loading top 50 card data:", error);
    showToast("Gagal memuat data kartu Top 50", "error");
  } finally {
    hideGlobalLoading();
  }
};
const loadTableData = async (page = 1) => {
  showLoading();
  showGlobalLoading();
  currentPage = page;
  const params = {
    filter_type: currentDateFilter.filter_type,
    filter: currentDateFilter.filter,
    start_date: currentDateFilter.start_date,
    end_date: currentDateFilter.end_date,
    status: currentStatus,
    type: currentType,
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
    const response = await fetchPaginatedProducts(params);
    handleTableResponse(response);
  } catch (error) {
    console.error("Error loading table data:", error);
    showEmptyState();
    showToast("Terjadi kesalahan saat memuat data tabel", "error");
  } finally {
    hideGlobalLoading();
  }
};
const handleTableResponse = (response) => {
  if (!response || response.success === false) {
    showToast(response?.message || "Data tabel tidak ditemukan", "warning");
    showEmptyState();
    renderPagination(null);
    return;
  }
  renderProductTableData(response.data, response.pagination.offset);
  renderPagination(response.pagination);
  hideLoading();
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
    paginationHTML += `<button onclick="handlePaginationClick(${
      current_page - 1
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
      paginationHTML += `<button class="px-2 py-1 rounded-md bg-yellow-500 text-white font-bold">${i}</button>`;
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
    paginationHTML += `<button onclick="handlePaginationClick(${
      current_page + 1
    })" class="px-2 py-1 rounded-md bg-gray-200 hover:bg-gray-300 text-gray-700">Next &raquo;</button>`;
  }
  paginationContainer.innerHTML = paginationHTML;
};
const exportProductsToCSV = (data) => {
  const headers = [
    "No",
    "PLU",
    "Nama Produk",
    "Total Quantity",
    "Total Belanja",
  ];
  const csvRows = [headers.join(",")];
  data.forEach((item, index) => {
    const descp = `"${(item.descp || "").replace(/"/g, '""')}"`;
    const values = [
      index + 1,
      `"${item.plu || ""}"`,
      descp,
      item.total_qty,
      item.total_penjualan,
    ];
    csvRows.push(values.join(","));
  });
  return csvRows.join("\n");
};
const exportTopProductsData = async () => {
  showGlobalLoading();
  try {
    const params = {
      filter_type: currentDateFilter.filter_type,
      filter: currentDateFilter.filter,
      start_date: currentDateFilter.start_date,
      end_date: currentDateFilter.end_date,
      status: currentStatus,
      type: currentType,
      search: currentSearch,
      sort_by: currentSort,
      page: 1,
      limit: 999999,
    };
    Object.keys(params).forEach((key) => {
      if (params[key] === null || params[key] === undefined) {
        delete params[key];
      }
    });
    const response = await fetchPaginatedProducts(params);
    if (
      !response ||
      response.success === false ||
      !response.data ||
      response.data.length === 0
    ) {
      showToast("Tidak ada data untuk diexport", "warning");
      return;
    }
    const csvContent = exportProductsToCSV(response.data);
    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute(
      "download",
      `top_products_${new Date().toISOString().split("T")[0]}.csv`
    );
    link.style.visibility = "hidden";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    showToast("Data berhasil diexport", "success");
  } catch (error) {
    console.error("Error exporting data:", error);
    showToast("Gagal mengexport data", "error");
  } finally {
    hideGlobalLoading();
  }
};
document.addEventListener("DOMContentLoaded", () => {
  initTopProductsDisplay();
});
window.exportTopProductsData = exportTopProductsData;
window.handlePaginationClick = (page) => {
  loadTableData(page);
};
