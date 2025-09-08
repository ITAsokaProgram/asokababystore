// Table renderer module for top sales page
import { formatNumber, formatPercentage } from './ui_helpers.js';

// Render table data
const renderTableData = (paginatedData, offset = 0, excludedProducts = []) => {
  const tbody = document.getElementById("top-sales-table-body");
  excludedProducts = ["kertas kado", "tas asoka", "askp"];  
  
  if (!tbody) return;

  if (paginatedData.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
          <i class="fas fa-search text-2xl mb-2"></i>
          <p>Tidak ada data yang ditemukan</p>
        </td>
      </tr>
    `;
    return;
  }

  // Calculate total for percentage
  const totalQty = paginatedData.reduce((sum, item) => sum + parseInt(item.qty_periode_sekarang), 0);
  // paginatedData = paginatedData.filter(item => !excludedProducts.some(product => item.barang.toLowerCase().includes(product.toLowerCase())));
  tbody.innerHTML = paginatedData
    .map(
      (item, index) => {
        const rowNumber = offset + index + 1;
        const qty = parseInt(item.qty_periode_sekarang);
        
        return `
          <tr class="hover:bg-gray-50 transition-colors duration-200">
            <td class="px-6 py-4 whitespace-nowrap text-center">
              <div class="flex items-center justify-center">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 font-bold text-sm">
                  ${rowNumber}
                </span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">${item.plu}</div>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm text-gray-900 font-medium">${item.barang}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
              <span class="font-bold">${formatNumber(qty)} pcs</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
              ${item.growth_percent >= 0 ? `
                <span class="font-bold text-green-600">+${item.growth_percent}%</span>
              ` : `
                <span class="font-bold text-red-600">${item.growth_percent}%</span>
              `}
            </td>
          </tr>
        `;
      }
    )
    .join("");
};

// Render empty state
const renderEmptyState = () => {
  const tbody = document.getElementById("top-sales-table-body");
  
  if (!tbody) return;

  tbody.innerHTML = `
    <tr>
      <td colspan="5" class="px-6 py-8 text-center text-gray-500">
        <i class="fas fa-trophy text-4xl mb-4 block"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada data top sales</h3>
        <p class="text-gray-500">Data top sales produk akan muncul di sini</p>
      </td>
    </tr>
  `;
};

// Render loading state
const renderLoadingState = () => {
  const tbody = document.getElementById("top-sales-table-body");
  
  if (!tbody) return;

  tbody.innerHTML = `
    <tr>
      <td colspan="5" class="px-6 py-8 text-center">
        <div class="inline-flex items-center">
          <i class="fas fa-spinner fa-spin text-2xl text-blue-500 mr-3"></i>
          <span class="text-gray-600">Memuat data...</span>
        </div>
      </td>
    </tr>
  `;
};

// Update summary cards
const updateSummaryCards = (summaryData) => {
  // Update top member card
  const topMemberElement = document.getElementById("top-member");
  if (topMemberElement && summaryData.topProduct) {
    topMemberElement.textContent = summaryData.topProduct.barang.substring(0, 30) + (summaryData.topProduct.barang.length > 30 ? "..." : "");
  }


};

export {
  renderTableData,
  renderEmptyState,
  renderLoadingState,
  updateSummaryCards
}; 