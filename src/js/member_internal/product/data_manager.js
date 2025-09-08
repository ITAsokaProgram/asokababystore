// Data management module for top sales page
import { paginationCard } from '../../transaction_branch/table.js';

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

// Filter data based on search term
const filterData = (searchTerm) => {
  if (!searchTerm.trim()) {
    filteredData = [...topSalesData];
  } else {
    const term = searchTerm.toLowerCase();
    filteredData = topSalesData.filter(
      (item) =>
        (item.barang && item.barang.toLowerCase().includes(term)) ||
        (item.plu && item.plu.toString().includes(term))
    );
  }
  currentPage = 1; // Reset to first page when filtering
  return filteredData;
};

// Sort data
const sortData = (sortBy) => {
  filteredData.sort((a, b) => {
    switch (sortBy) {
      case "terjual":
        return parseInt(b.qty_periode_sekarang) - parseInt(a.qty_periode_sekarang);
      case "persen":
        return parseFloat(b.growth_percent) - parseFloat(a.growth_percent);
      case "plu":
        return a.plu - b.plu;
      default:
        return 0;
    }
  });
  currentPage = 1; // Reset to first page when sorting
  return filteredData;
};

// Get paginated data
const getPaginatedData = (page = currentPage, perPage = itemsPerPage) => {
  const startIndex = (page - 1) * perPage;
  const endIndex = startIndex + perPage;
  return filteredData.slice(startIndex, endIndex);
};

// Update pagination
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

// Calculate total quantities and percentages
const calculateTotals = () => {
  const totalQty = topSalesData.reduce((sum, item) => sum + parseInt(item.qty_periode_sekarang), 0);
  
  return topSalesData.map(item => ({
    ...item,
    persen: totalQty > 0 ? ((parseInt(item.qty_periode_sekarang) / totalQty) * 100).toFixed(2) : '0.00'
  }));
};

// Get summary statistics
const getSummaryStats = () => {
  if (topSalesData.length === 0) {
    return {
      totalProducts: 0,
      totalQty: 0,
      avgQty: 0,
      topProduct: null
    };
  }

  const totalQty = topSalesData.reduce((sum, item) => sum + parseInt(item.qty_periode_sekarang), 0);
  const avgQty = totalQty / topSalesData.length;
  const topProduct = topSalesData[0]; // Assuming data is already sorted by quantity

  return {
    totalProducts: topSalesData.length,
    totalQty: totalQty,
    avgQty: avgQty,
    topProduct: topProduct
  };
};

// Export data to CSV
const exportToCSV = (data) => {
  const headers = ["No", "PLU", "Nama Barang", "Terjual", "Persen"];
  const csvRows = [headers.join(",")];

  data.forEach((item, index) => {
    const values = [
      index + 1,
      item.plu,
      `"${item.barang}"`,
      item.qty_periode_sekarang,
      item.growth_percent
    ];
    csvRows.push(values.join(","));
  });

  return csvRows.join("\n");
};

// Set current page
const setCurrentPage = (page) => {
  currentPage = page;
};

// Get current page
const getCurrentPage = () => {
  return currentPage;
};

// Get items per page
const getItemsPerPage = () => {
  return itemsPerPage;
};

// Set items per page
const setItemsPerPage = (perPage) => {
  itemsPerPage = perPage;
  currentPage = 1; // Reset to first page when changing items per page
};

export {
  initializeData,
  filterData,
  sortData,
  getPaginatedData,
  updatePagination,
  calculateTotals,
  getSummaryStats,
  exportToCSV,
  setCurrentPage,
  getCurrentPage,
  getItemsPerPage,
  setItemsPerPage
}; 