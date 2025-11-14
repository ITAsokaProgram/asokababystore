import { formatNumber, formatCurrency } from "./ui_helpers.js";
const renderTableData = (paginatedData, offset = 0, excludedProducts = []) => {
  const tbody = document.getElementById("top-sales-table-body");
  if (!tbody) return;
  if (paginatedData.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="px-4 py-4 text-center text-gray-500"> <i class="fas fa-search text-2xl mb-1"></i> <p>Tidak ada data yang ditemukan</p>
        </td>
      </tr>
    `;
    return;
  }
  tbody.innerHTML = paginatedData
    .map((item, index) => {
      const rowNumber = offset + index + 1;
      const qty = parseInt(item.total_qty || 0);
      const belanja = parseFloat(item.total_penjualan || 0);
      return `
          <tr class="hover:bg-gray-50 transition-colors duration-200 cursor-pointer" data-member="${
            item.kd_cust
          }" data-cabang="${item.kd_store}">
            <td class="px-3 py-2 whitespace-nowrap text-center"> <div class="flex items-center justify-center">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 font-bold text-sm">
                  ${rowNumber}
                </span>
              </div>
            </td>
            <td class="px-3 py-2"> <div class="text-sm font-bold text-gray-900">${
              item.nama_cust
            }</div>
            </td>
            <td class="px-3 py-2 whitespace-nowrap"> <div class="text-sm text-gray-600 font-mono">${
              item.kd_cust
            }</div>
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-center"> <span class="font-bold text-gray-700">${formatNumber(
              qty
            )} pcs</span>
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-right"> <span class="font-bold text-green-600">${formatCurrency(
              belanja
            )}</span>
            </td>
          </tr>
        `;
    })
    .join("");
};
const renderEmptyState = () => {
  const tbody = document.getElementById("top-sales-table-body");
  if (!tbody) return;
  tbody.innerHTML = `
    <tr>
      <td colspan="5" class="px-4 py-4 text-center text-gray-500"> <i class="fas fa-trophy text-4xl mb-2 block"></i> <h3 class="text-lg font-medium text-gray-900 mb-1">Belum ada data</h3> <p class="text-gray-500">Data top sales akan muncul di sini</p>
      </td>
    </tr>
  `;
};
const renderLoadingState = () => {
  const tbody = document.getElementById("top-sales-table-body");
  if (!tbody) return;
  tbody.innerHTML = `
    <tr>
      <td colspan="5" class="px-4 py-4 text-center"> <div class="inline-flex items-center">
          <i class="fas fa-spinner fa-spin text-2xl text-blue-500 mr-2"></i> <span class="text-gray-600">Memuat data...</span>
        </div>
      </td>
    </tr>
  `;
};
const updateSummaryCards = (summaryData) => {};
export {
  renderTableData,
  renderEmptyState,
  renderLoadingState,
  updateSummaryCards,
};
