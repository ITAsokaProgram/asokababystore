import { fetchTopMember, fetchPaginatedMembers } from "./fetch_product.js";
import {
  showLoading,
  hideLoading,
  showEmptyState,
  showToast,
  showGlobalLoading,
  hideGlobalLoading,
} from "./ui_helpers.js";
import {
  updateTopMembersPerformance,
  updateTopNonMembersPerformance,
  destroyAllCharts,
} from "./chart_manager.js";
import { renderTableData, updateSummaryCards } from "./table_renderer.js";
import {
  showDetailModal,
  showDetailModalMember,
} from "./detail_transaction.js";
import { fetchTransaction } from "./fetch_transaction.js";
let currentDateFilter = {
  startDate: null,
  endDate: null,
};
let currentPage = 1;
let itemsPerPage = 10;
let currentSearch = "";
let currentSort = "belanja";
let totalPages = 1;
const initTopSalesDisplay = async () => {
  setupEventListeners();
  const urlParams = new URLSearchParams(window.location.search);
  const filterParam = urlParams.get("filter");
  const startDateInput = document.getElementById("start-date");
  const endDateInput = document.getElementById("end-date");
  await loadTop50Cards(startDateInput.value, endDateInput.value);
  await loadTableData(1);
};
const setupEventListeners = () => {
  setupDateFilterListeners();
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
  document.addEventListener("click", async (e) => {
    const memberEl = e.target.closest("[data-member]");
    if (memberEl) {
      showGlobalLoading();
      try {
        const member = memberEl.dataset.member;
        const cabang = memberEl.dataset.cabang;
        const transactionResponse = await fetchTransaction({
          member: member,
          cabang: cabang,
        });
        if (
          transactionResponse &&
          transactionResponse.detail_transaction &&
          transactionResponse.detail_transaction.length > 0
        ) {
          showDetailModal(transactionResponse.detail_transaction);
        } else {
          showDetailModal({});
        }
      } catch (error) {
        console.error("Error fetching member transaction:", error);
        showToast("Gagal memuat detail transaksi", "error");
      } finally {
        hideGlobalLoading();
      }
      return;
    }
    const nonMemberEl = e.target.closest("[data-non-member]");
    if (nonMemberEl) {
      showGlobalLoading();
      try {
        const nonMember = nonMemberEl.dataset.nonMember;
        const transactionResponse = await fetchTransaction({
          no_trans: nonMember,
        });
        if (
          transactionResponse &&
          transactionResponse.detail_transaction &&
          transactionResponse.detail_transaction.length > 0
        ) {
          showDetailModal(transactionResponse.detail_transaction);
        } else {
          showDetailModal({});
        }
      } catch (error) {
        console.error("Error fetching non-member transaction:", error);
        showToast("Gagal memuat detail transaksi", "error");
      } finally {
        hideGlobalLoading();
      }
      return;
    }
    const detailTransactionEl = e.target.closest("[data-detail-transaction]");
    if (detailTransactionEl) {
      showGlobalLoading();
      try {
        const no_trans = detailTransactionEl.dataset.detailTransaction;
        const transactionResponse = await fetchTransaction({
          no_trans: no_trans,
        });
        if (
          transactionResponse &&
          transactionResponse.detail_transaction &&
          transactionResponse.detail_transaction.length > 0
        ) {
          showDetailModalMember(transactionResponse.detail_transaction);
        } else {
          showDetailModalMember({});
        }
      } catch (error) {
        console.error("Error fetching transaction detail:", error);
        showToast("Gagal memuat detail transaksi", "error");
      } finally {
        hideGlobalLoading();
      }
      return;
    }
  });
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
    console.warn("Date filter elements not found on top_sales.php");
    return;
  }
  const urlParams = new URLSearchParams(window.location.search);
  const filterParam = urlParams.get("filter");
  const today = new Date();
  let startDate;
  let endDate;
  if (filterParam === "kemarin") {
    startDate = new Date(today);
    startDate.setDate(today.getDate() - 1);
    endDate = new Date(startDate);
  } else if (filterParam) {
    startDate = calculateStartDateFromFilter(filterParam);
    endDate = new Date(today);
  } else {
    startDate = new Date(today);
    startDate.setDate(today.getDate() - 1);
    endDate = new Date(startDate);
  }
  startDateInput.value = startDate.toISOString().split("T")[0];
  endDateInput.value = endDate.toISOString().split("T")[0];
  endDateInput.max = today.toISOString().split("T")[0];
  const timeDiff = endDate.getTime() - startDate.getTime();
  const daysDiff = Math.max(1, Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1);
  dateRangeDisplay.innerHTML = ` <i class="fas fa-info-circle text-yellow-500 mr-1"></i>(Data ${daysDiff} hari)`;
  currentDateFilter.startDate = startDateInput.value;
  currentDateFilter.endDate = endDateInput.value;
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
    const s = new Date(startVal);
    const e = new Date(endVal);
    const diff = Math.max(1, Math.ceil((e - s) / (1000 * 3600 * 24)) + 1);
    currentDateFilter.startDate = startVal;
    currentDateFilter.endDate = endVal;
    dateRangeDisplay.innerHTML = ` <i class="fas fa-info-circle text-yellow-500 mr-1"></i>(Data ${diff} hari)`;
    window.history.pushState({}, document.title, window.location.pathname);
    await loadTop50Cards(startVal, endVal);
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
    currentDateFilter.startDate = startVal;
    currentDateFilter.endDate = endVal;
    window.history.pushState({}, document.title, window.location.pathname);
    await loadTop50Cards(startVal, endVal);
    await loadTableData(1);
  });
};
const calculateStartDateFromFilter = (filter) => {
  const today = new Date();
  const startDate = new Date(today);
  switch (filter) {
    case "kemarin":
      startDate.setDate(today.getDate() - 1);
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
      break;
  }
  return startDate;
};
const loadTop50Cards = async (startDate, endDate) => {
  const start = startDate || currentDateFilter.startDate;
  const end = endDate || currentDateFilter.endDate;
  showGlobalLoading();
  try {
    const topMemberResponse = await fetchTopMember(start, end);
    if (topMemberResponse && topMemberResponse.success) {
      updateTopMembersPerformance(topMemberResponse.data);
      updateTopNonMembersPerformance(topMemberResponse.data_non || []);
    } else {
      updateTopMembersPerformance([]);
      updateTopNonMembersPerformance([]);
    }
    const excludeProduct = [
      { nama: "Kertas Kado" },
      { nama: "Tas Asoka" },
      { nama: "ASKP" },
    ];
    infoData(excludeProduct);
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
    start_date: currentDateFilter.startDate,
    end_date: currentDateFilter.endDate,
    page: currentPage,
    limit: itemsPerPage,
    search: currentSearch,
    sort_by: currentSort,
  };
  try {
    const response = await fetchPaginatedMembers(params);
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
    showToast(response?.message || "Data tabel tidak ditemukan", "error");
    showEmptyState();
    renderPagination(null);
    return;
  }
  renderTableData(response.data, response.pagination.offset);
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
const infoData = (excludedProducts) => {
  const infoDataEl = document.getElementById("date-range-display");
  if (infoDataEl) {
    let dropdown = document.getElementById("exclude-dropdown");
    if (!dropdown) {
      dropdown = document.createElement("div");
      dropdown.id = "exclude-dropdown";
      dropdown.className =
        "hidden absolute z-50 mt-2 right-0 bg-white border border-yellow-200 rounded-lg shadow-lg w-72 p-2 text-sm";
      document.body.appendChild(dropdown);
      infoDataEl.style.cursor = "pointer";
      infoDataEl.addEventListener("click", (e) => {
        e.stopPropagation();
        toggleDropdown();
      });
      document.addEventListener("click", (e) => {
        if (
          !dropdown.classList.contains("hidden") &&
          !dropdown.contains(e.target) &&
          e.target !== infoDataEl
        ) {
          dropdown.classList.add("hidden");
        }
      });
    }
    dropdown.innerHTML = `
            <div class="font-bold text-yellow-700 mb-1 flex items-center gap-2"> <i class="fas fa-ban text-yellow-400"></i> Produk yang di-exclude
            </div>
            <ul class="max-h-48 overflow-y-auto space-y-1">
                ${
                  excludedProducts.length === 0
                    ? '<li class="text-gray-400">Tidak ada produk exclude</li>'
                    : excludedProducts
                        .map(
                          (p) =>
                            `<li class="flex gap-2 items-center"> <span class="truncate" title="${p.nama}">${p.nama}</span></li>`
                        )
                        .join("")
                }
            </ul>`;
    function toggleDropdown() {
      if (dropdown.classList.contains("hidden")) {
        const rect = infoDataEl.getBoundingClientRect();
        dropdown.style.top = `${window.scrollY + rect.bottom + 4}px`;
        dropdown.style.left = `${
          window.scrollX + rect.left - dropdown.offsetWidth / 2 + rect.width / 2
        }px`;
        dropdown.classList.remove("hidden");
      } else {
        dropdown.classList.add("hidden");
      }
    }
  }
};
const exportTopSalesData = () => {
  try {
    const csvContent = exportToCSV(filterData(""));
    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute(
      "download",
      `top_sales_member_${new Date().toISOString().split("T")[0]}.csv`
    );
    link.style.visibility = "hidden";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    showToast("Data berhasil diexport", "success");
  } catch (error) {
    console.error("Error exporting data:", error);
    showToast("Gagal mengexport data", "error");
  }
};
document.addEventListener("DOMContentLoaded", () => {
  initTopSalesDisplay();
});
window.addEventListener("beforeunload", () => {
  destroyAllCharts();
});
window.exportTopSalesData = exportTopSalesData;
window.handlePaginationClick = (page) => {
  loadTableData(page);
};
