import { fetchProductFav, filterProductFav } from "./fetch_product.js";
import { paginationCard } from "../../transaction_branch/table.js";
// Global variables
let productData = [];
let filteredData = [];
let charts = {};
let currentPage = 1;
let itemsPerPage = 10;
let currentDateFilter = {
  startDate: null,
  endDate: null,
};

// Initialize the application
const initProductFavoriteDisplay = async () => {
  setupEventListeners();
  await loadData();
  await loadTrendData();
  await loadProductPerformance();
};

// Setup event listeners
const setupEventListeners = () => {
  // Refresh button
  document.getElementById("refresh-btn").addEventListener("click", () => {
    loadData();
    loadTrendData();
    loadProductPerformance();
  });

  // Search functionality
  document.getElementById("search-input").addEventListener("input", (e) => {
    filterData(e.target.value);
  });

  // Sort functionality
  document.getElementById("sort-select").addEventListener("change", (e) => {
    sortData(e.target.value);
  });

  // Date filter functionality
  setupDateFilterListeners();
};

// Setup date filter event listeners
const setupDateFilterListeners = () => {
  const startDateInput = document.getElementById("start-date");
  const endDateInput = document.getElementById("end-date");
  const applyFilterBtn = document.getElementById("apply-date-filter");
  const resetFilterBtn = document.getElementById("reset-date-filter");
  const dateRangeDisplay = document.getElementById("date-range-display");
  // Check if elements exist
  if (!startDateInput || !endDateInput || !applyFilterBtn || !resetFilterBtn) {
    console.error("Date filter elements not found:", {
      startDateInput: !!startDateInput,
      endDateInput: !!endDateInput,
      applyFilterBtn: !!applyFilterBtn,
      resetFilterBtn: !!resetFilterBtn,
    });
    return;
  }

  // Set default date range (3 months ago to today)
  const today = new Date();
  const yesterday = new Date(today);
  yesterday.setDate(today.getDate() - 1);
  startDateInput.value = yesterday.toISOString().split("T")[0];
  endDateInput.value = today.toISOString().split("T")[0];

  // Set max date - end date maksimal hari ini, start date bisa pilih semua tanggal
  endDateInput.max = today.toISOString().split("T")[0];

  // Apply filter button
  applyFilterBtn.addEventListener("click", async () => {
    const startDate = startDateInput.value;
    const endDate = endDateInput.value;

    if (!startDate || !endDate) {
      showToast("Pilih tanggal awal dan akhir", "warning");
      return;
    }

    if (new Date(startDate) > new Date(endDate)) {
      showToast(
        "Tanggal awal tidak boleh lebih besar dari tanggal akhir",
        "warning"
      );
      return;
    }

    // Check if date range is more than 3 months (perhitungan yang lebih akurat)
    const start = new Date(startDate);
    const end = new Date(endDate);

    // Hitung selisih dalam milisecond
    const timeDiff = end.getTime() - start.getTime();
    const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
    const monthsDiff = daysDiff / 30.44; // Rata-rata hari dalam sebulan

    if (monthsDiff > 3) {
      showToast("Data maksimal 3 bulan", "warning");
      return;
    }

    currentDateFilter.startDate = startDate;
    currentDateFilter.endDate = endDate;
    showLoading();
    dateRangeDisplay.innerHTML = ` <i class="fas fa-info-circle text-blue-500 mr-1"></i>(Data ${daysDiff} hari lalu)`;
    const data = await filterProductFav(startDate, endDate);
    if (!data) {
      showToast("Data tidak ditemukan", "error");
      hideLoading();
      showEmptyState();
      return;
    }

    if (data.status === true) {
      productData = data.data;
      filteredData = [...data.data];
      updateTable();
      hideLoading();
    } else {
      showToast(data.message, "error");
      hideLoading();
      showEmptyState();
    }
  });

  // Reset filter button
  resetFilterBtn.addEventListener("click", async () => {
    const yesterday = new Date(today);
    yesterday.setDate(today.getDate() - 1);
    dateRangeDisplay.innerHTML = ` <i class="fas fa-info-circle text-blue-500 mr-1"></i>(Data 1 hari lalu)`;
    startDateInput.value = yesterday.toISOString().split("T")[0];
    endDateInput.value = today.toISOString().split("T")[0];

    currentDateFilter.startDate = null;
    currentDateFilter.endDate = null;

    // Here you can add your custom logic for resetting date filter
    showLoading();
    const data = await filterProductFav(
      startDateInput.value,
      endDateInput.value
    );
    if (!data) {
      showToast("Data tidak ditemukan", "error");
      hideLoading();
      showEmptyState();
      return;
    }
    if (data.status === true) {
      productData = data.data;
      filteredData = [...data.data];
      updateTable();
      hideLoading();
    } else {
      showToast(data.message, "error");
      hideLoading();
      showEmptyState();
    }
  });

  // Prevent selecting end date more than today
  endDateInput.addEventListener("change", () => {
    const endDate = new Date(endDateInput.value);
    const today = new Date();

    if (endDate > today) {
      endDateInput.value = today.toISOString().split("T")[0];
      showToast("Tanggal akhir tidak boleh lebih dari hari ini", "warning");
    }
  });
};

// Show toast notification
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

// Load data from API
const loadData = async () => {
  showLoading();

  try {
    const response = await fetchProductFav();

    // Check if response has the correct structure
    if (
      response &&
      response.status === true &&
      response.data &&
      Array.isArray(response.data) &&
      response.data.length > 0
    ) {
      productData = response.data;
      filteredData = [...response.data];
      updateTable();
      updateCharts();
      loadProductPerformance();
      updateLastUpdate();
      hideLoading();
    } else {
      showEmptyState();
    }
  } catch (error) {
    showEmptyState();
  }
};

// Load trend data from API
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

// Load product performance data from API
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

// Show loading state
const showLoading = () => {
  document.getElementById("loading-state").classList.remove("hidden");
  document.getElementById("empty-state").classList.add("hidden");
  document.getElementById("member-table-body").innerHTML = "";
};

// Hide loading state
const hideLoading = () => {
  document.getElementById("loading-state").classList.add("hidden");
  document.getElementById("empty-state").classList.add("hidden");
};

// Show empty state
const showEmptyState = () => {
  document.getElementById("loading-state").classList.add("hidden");
  document.getElementById("empty-state").classList.remove("hidden");
  document.getElementById("member-table-body").innerHTML = "";
};

// Filter data based on search term
const filterData = (searchTerm) => {
  if (!searchTerm.trim()) {
    filteredData = [...productData];
  } else {
    const term = searchTerm.toLowerCase();
    filteredData = productData.filter(
      (item) =>
        (item.nama_customer &&
          item.nama_customer.toLowerCase().includes(term)) ||
        (item.barang && item.barang.toLowerCase().includes(term)) ||
        (item.kd_cust && item.kd_cust.toLowerCase().includes(term))
    );
  }
  currentPage = 1; // Reset to first page when filtering
  updateTable();
};

// Sort data
const sortData = (sortBy) => {
  filteredData.sort((a, b) => {
    switch (sortBy) {
      case "qty":
        return parseInt(b.total_qty) - parseInt(a.total_qty);
      case "harga":
        return b.total_hrg - a.total_hrg;
      default:
        return 0;
    }
  });
  currentPage = 1; // Reset to first page when sorting
  updateTable();
};

// Update table with data
const updateTable = () => {
  const tbody = document.getElementById("member-table-body");

  if (filteredData.length === 0) {
    tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-search text-2xl mb-2"></i>
                    <p>Tidak ada data yang ditemukan</p>
                </td>
            </tr>
        `;
    // Hide pagination when no data
    document.getElementById("paginationContainer").innerHTML = "";
    document.getElementById("viewData").textContent = "";
    return;
  }

  // Use paginationCard to handle pagination
  paginationCard(
    currentPage,
    itemsPerPage,
    filteredData,
    renderTableData,
    "viewData",
    "paginationContainer"
  );
};

// Render table data (callback for paginationCard)
const renderTableData = (paginatedData, offset) => {
  const tbody = document.getElementById("member-table-body");

  tbody.innerHTML = paginatedData
    .map(
      (item) => `
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
                        <div class="text-sm font-medium text-gray-900">${
                          item.nama_customer
                        }</div>
                        <div class="text-sm text-gray-500">${item.kd_cust}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 font-medium">${
                  item.barang
                }</div>
                <div class="text-sm text-gray-500">PLU: ${item.plu}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                <span class="font-bold text-green-600">${parseInt(
                  item.total_qty
                ).toLocaleString()} pcs</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                <span class="font-bold">Rp ${item.total_hrg.toLocaleString()}</span>
            </td>
        </tr>
    `
    )
    .join("");
};

// Update product performance section
const updateProductPerformance = (performanceData) => {
  const performanceContainer = document.getElementById("product-performance");

  // Take only the first 4 items
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

          // Determine if it's an increase or decrease
          const isIncrease = difference > 0;
          const isDecrease = difference < 0;
          const isSame = difference === 0;

          // Set colors and icons based on performance
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
                    <h4 class="font-semibold text-gray-900 text-sm leading-tight mb-1">${
                      product.barang
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

// Update charts
const updateCharts = () => {
  loadTrendData();
};

// Update monthly trend chart
const updateMonthlyTrendChart = (trendData) => {
  const ctx = document.getElementById("monthlyTrendChart");

  if (charts.monthlyTrendChart) {
    charts.monthlyTrendChart.destroy();
  }

  // Convert month numbers to month names
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

  // Sort data by month and create labels and data arrays
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
      plugins: {
        legend: {
          display: false,
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: "rgba(0, 0, 0, 0.1)",
          },
          ticks: {
            callback: function (value) {
              return value.toLocaleString() + " pcs";
            },
          },
        },
        x: {
          grid: {
            display: false,
          },
        },
      },
    },
  });
};

// Update last update timestamp
const updateLastUpdate = () => {
  const now = new Date();
  document.getElementById("last-update").textContent =
    now.toLocaleString("id-ID");
};

// Get initials from name
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

// Export member data to CSV
const exportMemberData = (kdCust) => {
  const memberData = productData.filter((item) => item.kd_cust === kdCust);

  if (memberData.length > 0) {
    const csvContent = convertToCSV(memberData);
    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", `member_${kdCust}_favorites.csv`);
    link.style.visibility = "hidden";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
};

// Export all data to Excel grouped by kd_cust
const exportAllDataToExcel = () => {
  // Group data by kd_cust
  const groupedData = {};
  productData.forEach((item) => {
    if (!groupedData[item.kd_cust]) {
      groupedData[item.kd_cust] = [];
    }
    groupedData[item.kd_cust].push(item);
  });

  // Sort each group by total_qty (descending) and then by total_hrg (descending)
  Object.keys(groupedData).forEach((kdCust) => {
    groupedData[kdCust].sort((a, b) => {
      const qtyDiff = parseInt(b.total_qty) - parseInt(a.total_qty);
      if (qtyDiff !== 0) return qtyDiff;
      return b.total_hrg - a.total_hrg;
    });
  });

  // Sort kd_cust groups by total transactions (descending)
  const sortedKdCusts = Object.keys(groupedData).sort((a, b) => {
    return groupedData[b].length - groupedData[a].length;
  });

  // Create Excel content
  let excelContent =
    "Kode Customer,Nama Customer,Barang,PLU,Total Quantity,Total Harga\n";

  sortedKdCusts.forEach((kdCust) => {
    const customerData = groupedData[kdCust];

    // Add customer data
    customerData.forEach((item) => {
      excelContent += `"${item.kd_cust}","${item.nama_customer}","${item.barang}","${item.plu}","${item.total_qty}","${item.total_hrg}"\n`;
    });
  });

  // Create and download file
  const blob = new Blob([excelContent], { type: "text/csv;charset=utf-8;" });
  const link = document.createElement("a");
  const url = URL.createObjectURL(blob);
  link.setAttribute("href", url);
  link.setAttribute(
    "download",
    `product_favorites_all_data_${new Date().toISOString().split("T")[0]}.csv`
  );
  link.style.visibility = "hidden";
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

// Convert data to CSV format
const convertToCSV = (data) => {
  const headers = [
    "kd_cust",
    "nama_customer",
    "barang",
    "plu",
    "total_qty",
    "total_hrg",
  ];
  const csvRows = [headers.join(",")];

  data.forEach((item) => {
    const values = headers.map((header) => `"${item[header]}"`);
    csvRows.push(values.join(","));
  });

  return csvRows.join("\n");
};

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  initProductFavoriteDisplay();
});

// Make functions available globally for onclick handlers
window.exportMemberData = exportMemberData;
window.exportAllDataToExcel = exportAllDataToExcel;
