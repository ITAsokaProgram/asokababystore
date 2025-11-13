import { paginationCard } from "../../transaction_branch/table.js";

// Global variables
let topSalesData = [];
let filteredData = [];
let currentPage = 1;
let itemsPerPage = 10;

// Initialize data
const initializeData = (data) => {
  topSalesData = data;
  filteredData = [...data];
  currentPage = 1;
};

// Filter data based on search term (Updated for Customer Name/Code)
const filterData = (searchTerm) => {
  if (!searchTerm.trim()) {
    filteredData = [...topSalesData];
  } else {
    const term = searchTerm.toLowerCase();
    filteredData = topSalesData.filter(
      (item) =>
        (item.nama_cust && item.nama_cust.toLowerCase().includes(term)) ||
        (item.kd_cust && item.kd_cust.toString().includes(term))
    );
  }
  currentPage = 1;
  return filteredData;
};

// Sort data (Updated options)
const sortData = (sortBy) => {
  filteredData.sort((a, b) => {
    switch (sortBy) {
      case "belanja":
        return parseFloat(b.total_penjualan) - parseFloat(a.total_penjualan);
      case "qty":
        return parseInt(b.total_qty) - parseInt(a.total_qty);
      case "nama":
        return a.nama_cust.localeCompare(b.nama_cust);
      default:
        return 0;
    }
  });
  currentPage = 1;
  return filteredData;
};

const getPaginatedData = (page = currentPage, perPage = itemsPerPage) => {
  const startIndex = (page - 1) * perPage;
  const endIndex = startIndex + perPage;
  return filteredData.slice(startIndex, endIndex);
};

const updatePagination = (renderCallback) => {
  paginationCard(
    currentPage,
    itemsPerPage,
    filteredData,
    renderCallback,
    "viewData",
    "paginationContainer"
  );
};

const getSummaryStats = () => {
  // Logic summary jika diperlukan, bisa disesuaikan
  return {};
};

// Export data to CSV (Updated Headers)
const exportToCSV = (data) => {
  const headers = [
    "No",
    "Nama",
    "Kode Customer",
    "Total Quantity",
    "Total Belanja",
  ];
  const csvRows = [headers.join(",")];

  data.forEach((item, index) => {
    const values = [
      index + 1,
      `"${item.nama_cust || ""}"`,
      `"${item.kd_cust || ""}"`,
      item.total_qty,
      item.total_penjualan,
    ];
    csvRows.push(values.join(","));
  });

  return csvRows.join("\n");
};

const setCurrentPage = (page) => {
  currentPage = page;
};

const getCurrentPage = () => {
  return currentPage;
};

const getItemsPerPage = () => {
  return itemsPerPage;
};

const setItemsPerPage = (perPage) => {
  itemsPerPage = perPage;
  currentPage = 1;
};

export {
  initializeData,
  filterData,
  sortData,
  getPaginatedData,
  updatePagination,
  getSummaryStats,
  exportToCSV,
  setCurrentPage,
  getCurrentPage,
  getItemsPerPage,
  setItemsPerPage,
};
