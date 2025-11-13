import { fetchTopSales, fetchTopMember } from "./fetch_product.js";
import {
  showLoading,
  hideLoading,
  showEmptyState,
  updateLastUpdate,
  showToast,
} from "./ui_helpers.js";
import {
  updateTopMembersPerformance,
  updateTopNonMembersPerformance,
  destroyAllCharts,
} from "./chart_manager.js";
import {
  initializeData,
  filterData,
  sortData,
  getPaginatedData,
  updatePagination,
  getSummaryStats,
  exportToCSV,
  setCurrentPage,
} from "./data_manager.js";
import { renderTableData, updateSummaryCards } from "./table_renderer.js";
import {
  showDetailModal,
  showDetailModalMember,
} from "./detail_transaction.js";
import { fetchTransaction } from "./fetch_transaction.js";

const initTopSalesDisplay = async () => {
  setupEventListeners();
  await loadData();
};

const setupEventListeners = () => {
  const refreshBtn = document.getElementById("refresh-btn");
  if (refreshBtn) {
    refreshBtn.addEventListener("click", async () => {
      await loadData();
      showToast("Data berhasil diperbarui", "success");
    });
  }
  const searchInput = document.getElementById("search-input");
  if (searchInput) {
    searchInput.addEventListener("input", (e) => {
      const filteredData = filterData(e.target.value);
      updatePagination(renderTableData);
    });
  }
  const sortSelect = document.getElementById("sort-select");
  if (sortSelect) {
    sortSelect.addEventListener("change", (e) => {
      const sortedData = sortData(e.target.value);
      updatePagination(renderTableData);
    });
  }

  // Event Listener untuk klik detail tetap sama (dicopy dari kode lama)
  document.addEventListener("click", async (e) => {
    const memberEl = e.target.closest("[data-member]");
    if (memberEl) {
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
      return;
    }
    const nonMemberEl = e.target.closest("[data-non-member]");
    if (nonMemberEl) {
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
      return;
    }
    const detailTransactionEl = e.target.closest("[data-detail-transaction]");
    if (detailTransactionEl) {
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
      return;
    }
  });
};

const infoData = (excludedProducts) => {
  // Fungsi infoData tetap sama seperti sebelumnya
  const infoData = document.getElementById("info-data");
  if (infoData) {
    let dropdown = document.createElement("div");
    dropdown.id = "exclude-dropdown";
    dropdown.className =
      "hidden absolute z-50 mt-2 right-0 bg-white border border-yellow-200 rounded-lg shadow-lg w-72 p-4 text-sm";
    dropdown.innerHTML = `
      <div class="font-bold text-yellow-700 mb-2 flex items-center gap-2">
        <i class="fas fa-ban text-yellow-400"></i> Produk yang di-exclude
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
      </ul>
    `;
    document.body.appendChild(dropdown);
    function toggleDropdown() {
      if (dropdown.classList.contains("hidden")) {
        const rect = infoData.getBoundingClientRect();
        dropdown.style.top = `${window.scrollY + rect.bottom + 4}px`;
        dropdown.style.left = `${window.scrollX + rect.left}px`;
        dropdown.classList.remove("hidden");
      } else {
        dropdown.classList.add("hidden");
      }
    }
    infoData.style.cursor = "pointer";
    infoData.addEventListener("click", (e) => {
      e.stopPropagation();
      toggleDropdown();
    });
    document.addEventListener("click", (e) => {
      if (
        !dropdown.classList.contains("hidden") &&
        !dropdown.contains(e.target) &&
        e.target !== infoData
      ) {
        dropdown.classList.add("hidden");
      }
    });
  }
};

const loadData = async () => {
  showLoading();
  try {
    // Kita fetch top member, karena tabel akan menggunakan data ini
    const topMemberResponse = await fetchTopMember();

    if (
      !topMemberResponse ||
      !topMemberResponse.success ||
      !Array.isArray(topMemberResponse.data)
    ) {
      showEmptyState();
      showToast(topMemberResponse?.message || "Gagal mengambil data", "error");
      return;
    }

    // Gunakan data member untuk tabel utama
    initializeData(topMemberResponse.data);
    updatePagination(renderTableData);

    const excludeProduct = [
      { nama: "Kertas Kado" },
      { nama: "Tas Asoka" },
      { nama: "ASKP" },
    ];
    infoData(excludeProduct);

    // Update chart charts (tetap menggunakan data yang sama)
    updateTopMembersPerformance(topMemberResponse.data);
    updateTopNonMembersPerformance(topMemberResponse.data_non);

    updateLastUpdate();
    hideLoading();
  } catch (error) {
    console.error("Error loading data:", error);
    showEmptyState();
    showToast("Terjadi kesalahan saat memuat data", "error");
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

const handlePaginationClick = (page) => {
  setCurrentPage(page);
  updatePagination(renderTableData);
};

document.addEventListener("DOMContentLoaded", () => {
  initTopSalesDisplay();
});

window.addEventListener("beforeunload", () => {
  destroyAllCharts();
});

window.exportTopSalesData = exportTopSalesData;
window.handlePaginationClick = handlePaginationClick;
