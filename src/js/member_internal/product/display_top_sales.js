// Main display module for top sales page
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

// Initialize the application
const initTopSalesDisplay = async () => {
  setupEventListeners();
  await loadData();
};

// Setup event listeners
const setupEventListeners = () => {
  // Refresh button
  const refreshBtn = document.getElementById("refresh-btn");
  if (refreshBtn) {
    refreshBtn.addEventListener("click", async () => {
      await loadData();
      showToast("Data berhasil diperbarui", "success");
    });
  }

  // Search functionality
  const searchInput = document.getElementById("search-input");
  if (searchInput) {
    searchInput.addEventListener("input", (e) => {
      const filteredData = filterData(e.target.value);
      updatePagination(renderTableData);
    });
  }

  // Sort functionality
  const sortSelect = document.getElementById("sort-select");
  if (sortSelect) {
    sortSelect.addEventListener("change", (e) => {
      const sortedData = sortData(e.target.value);
      updatePagination(renderTableData);
    });
  }

  document.addEventListener("click", async (e) => {
    // Untuk member
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
    // Untuk non-member
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
  const infoData = document.getElementById("info-data");
  if (infoData) {
    // Buat dropdown element
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
    // Tempel ke body, posisinya akan diatur relatif ke infoData
    document.body.appendChild(dropdown);

    // Fungsi untuk toggle dropdown
    function toggleDropdown() {
      if (dropdown.classList.contains("hidden")) {
        // Hitung posisi infoData
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

    // Klik di luar dropdown akan menutup
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

// Load all data
const loadData = async () => {
  showLoading();

  try {
    // Load main top sales data
    const topSalesResponse = await fetchTopSales();
    if (!topSalesResponse || !topSalesResponse.success) {
      showEmptyState();
      showToast(topSalesResponse?.message || "Gagal mengambil data", "error");
      return;
    }
    // Update summary card for top member
    const excludeProduct = [
      { nama: "Kertas Kado" },
      { nama: "Tas Asoka" },
      { nama: "ASKP" },
    ];
    const topMember = topSalesResponse.data;
    // Initialize data
    initializeData(topSalesResponse.data);

    // Update table
    updatePagination(renderTableData);

    // Update summary cards
    const summaryStats = getSummaryStats();
    updateSummaryCards(summaryStats);

    // Fetch top member data for summary and top 50
    const topMemberResponse = await fetchTopMember();
    if (
      !topMemberResponse ||
      !topMemberResponse.success ||
      !Array.isArray(topMemberResponse.data) ||
      topMemberResponse.data.length === 0
    ) {
      showEmptyState();
      showToast(
        topMemberResponse?.message || "Gagal mengambil data top member",
        "error"
      );
      return;
    }

    cardSummary(topMember.slice(0, 4));
    infoData(excludeProduct);
    // Update Top 5 Member section
    updateTopMembersPerformance(topMemberResponse.data);
    updateTopNonMembersPerformance(topMemberResponse.data_non);

    // Update last update timestamp
    updateLastUpdate();

    hideLoading();
  } catch (error) {
    console.error("Error loading data:", error);
    showEmptyState();
    showToast("Terjadi kesalahan saat memuat data", "error");
  }
};

const cardSummary = (topMember) => {
  // Input validation
  if (!topMember || !Array.isArray(topMember)) {
    console.warn("Invalid input: topMember must be an array");
    return;
  }

  const cardSummary = document.getElementById("card-summary");

  if (!cardSummary) {
    console.error('Container element with ID "card-summary" not found');
    return;
  }

  // Handle empty data
  if (topMember.length === 0) {
    cardSummary.innerHTML = `
      <div class="col-span-full flex flex-col items-center justify-center py-12 text-center">
        <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
        <p class="text-gray-500 text-lg">Tidak ada data barang terlaris</p>
      </div>
    `;
    return;
  }

  // Generate responsive cards with enhanced Tailwind classes
  let row = "";
  topMember.forEach((item, index) => {
    const isPositiveGrowth = item.growth_percent > 0;
    const growthColorClass = isPositiveGrowth
      ? "text-green-600"
      : "text-red-600";
    const growthBgClass = isPositiveGrowth ? "bg-green-50" : "bg-red-50";
    const growthIcon = isPositiveGrowth ? "fa-arrow-up" : "fa-arrow-down";

    row += `
      <!-- Enhanced Responsive Top Barang Terlaris Card -->
      <div class="group relative bg-white/80 backdrop-blur-sm rounded-xl p-4 sm:p-6 border-l-4 border-yellow-500 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-[1.02]">
        
        <!-- Ranking Badge -->
        <div class="absolute -top-2 -right-2 w-6 h-6 sm:w-8 sm:h-8 bg-yellow-500 rounded-full flex items-center justify-center shadow-lg">
          <span class="text-white text-xs sm:text-sm font-bold">${
            index + 1
          }</span>
        </div>
        
        <!-- Main Content Container -->
        <div class="flex items-start justify-between gap-3 sm:gap-4">
          
          <!-- Left Content Section -->
          <div class="flex-1 min-w-0 space-y-2 sm:space-y-3">
            
            <!-- Header Label -->
            <div class="flex items-center gap-2 flex-wrap">
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                Top Seller
              </span>
              <span class="text-xs text-gray-500 hidden sm:inline">Barang Terlaris</span>
            </div>
            
            <!-- Product Name -->
            <h3 class="text-sm sm:text-base lg:text-lg font-semibold text-gray-900 leading-tight line-clamp-2" 
                title="${item.barang || "Unknown Item"}">
              ${item.barang || "Unknown Item"}
            </h3>
            
            <!-- Growth Indicator -->
            <div class="flex items-center gap-2 flex-wrap">
  <div class="flex items-center gap-1 px-2 py-1 rounded-full ${growthBgClass}">
    <i class="fas ${growthIcon} text-xs ${growthColorClass}"></i>
    <span class="text-xs sm:text-sm font-medium ${growthColorClass}">
      ${Math.abs(item.growth_percent || 0).toFixed(1)}%
    </span>
  </div>
  <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">
    Sebelumnya: <span class="font-semibold">${
      item.qty_periode_sebelumnya
    }</span>
  </span>
  <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded">
    Sekarang: <span class="font-semibold">${item.qty_periode_sekarang}</span>
  </span>
</div>
            
          </div>
          
          <!-- Right Icon Section -->
          <div class="flex-shrink-0">
            <div class="w-10 h-10 sm:w-12 sm:h-12 lg:w-14 lg:h-14 bg-gradient-to-br from-yellow-100 via-yellow-200 to-yellow-300 rounded-xl flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow duration-300">
              <i class="fas fa-crown text-yellow-600 text-lg sm:text-xl lg:text-2xl group-hover:scale-110 transition-transform duration-300"></i>
            </div>
          </div>
          
        </div>
        
        <!-- Bottom Progress Bar (Optional Visual Enhancement) -->
        <div class="mt-4 pt-3 border-t border-gray-100">
          <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
            <span>Performa</span>
            <span>${Math.abs(item.growth_percent || 0).toFixed(1)}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-1.5">
            <div class="h-1.5 rounded-full transition-all duration-500 ${
              isPositiveGrowth ? "bg-green-500" : "bg-red-500"
            }" 
                 style="width: ${Math.min(
                   Math.abs(item.growth_percent || 0),
                   100
                 )}%"></div>
          </div>
        </div>
        
      </div>
    `;
  });

  // Insert generated HTML
  cardSummary.innerHTML = row;

  // Add staggered animation effect
  requestAnimationFrame(() => {
    const cards = cardSummary.querySelectorAll(".group");
    cards.forEach((card, index) => {
      card.style.animationDelay = `${index * 100}ms`;
      card.classList.add("animate-fade-in-up");
    });
  });
};

// Export top sales data to Excel
const exportTopSalesData = () => {
  try {
    const csvContent = exportToCSV(filterData(""));
    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute(
      "download",
      `top_sales_products_${new Date().toISOString().split("T")[0]}.csv`
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

// Handle pagination click
const handlePaginationClick = (page) => {
  setCurrentPage(page);
  updatePagination(renderTableData);
};

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  initTopSalesDisplay();
});

// Cleanup on page unload
window.addEventListener("beforeunload", () => {
  destroyAllCharts();
});

// Make functions available globally for onclick handlers
window.exportTopSalesData = exportTopSalesData;
window.handlePaginationClick = handlePaginationClick;
