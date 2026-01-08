import * as api from "./member_api_service.js";
const state = {
  filterParams: {
    filter_type: null,
    filter: null,
    start_date: null,
    end_date: null,
  },
  currentCity: null,
  currentPage: 1,
  limit: 10,
  totalPages: 1
};
const els = {
  citySelect: document.getElementById('city-filter'),
  btnSearch: document.getElementById('btn-search'),
  btnExport: document.getElementById('btn-export'),
  initialState: document.getElementById('initial-state'),
  tableBody: document.getElementById('table-body'),
  loading: document.getElementById('loading-spinner'),
  tableContainer: document.getElementById('table-container'),
  errorMsg: document.getElementById('error-message'),
  pagination: document.getElementById('pagination-container'),
  paginationBtns: document.getElementById('pagination-buttons'),
  recordInfo: document.getElementById('record-info')
};
const formatCurrency = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
const formatNumber = new Intl.NumberFormat('id-ID');
document.addEventListener('DOMContentLoaded', async () => {
  const params = new URLSearchParams(window.location.search);
  state.filterParams.filter_type = params.get("filter_type") || 'preset';
  state.filterParams.filter = params.get("filter") || '3bulan';
  state.filterParams.start_date = params.get("start_date");
  state.filterParams.end_date = params.get("end_date");
  await loadCities();
});
if (els.btnSearch) {
  els.btnSearch.addEventListener('click', () => {
    const selectedCity = els.citySelect.value;
    if (!selectedCity || selectedCity === "") {
      Swal.fire({ icon: 'warning', title: 'Pilih Kota', text: 'Silakan pilih kota terlebih dahulu.', timer: 2000, showConfirmButton: false });
      return;
    }
    state.currentCity = selectedCity;
    state.currentPage = 1;
    loadData();
  });
}
if (els.btnExport) {
  els.btnExport.addEventListener('click', handleExportExcel);
}
async function loadCities() {
  els.citySelect.innerHTML = '<option value="" disabled selected>Sedang memuat kota...</option>';
  els.citySelect.disabled = true;
  try {
    const result = await api.getCitiesSimple();
    if (result.success && result.data) {
      let optionsHtml = '<option value="" disabled selected>-- Pilih Kota --</option>';
      optionsHtml += '<option value="all">-- SELURUH KOTA --</option>';
      result.data.forEach(item => {
        if (item.kota) {
          optionsHtml += `<option value="${item.kota}">${item.kota}</option>`;
        }
      });
      els.citySelect.innerHTML = optionsHtml;
      els.citySelect.disabled = false;
    }
  } catch (error) {
    console.error("Gagal memuat list kota:", error);
    els.citySelect.innerHTML = '<option value="" disabled selected>Gagal memuat data</option>';
  }
}
async function loadData() {
  els.initialState.classList.add('hidden');
  showLoading(true);
  showError(false);
  if (els.btnExport) els.btnExport.classList.add('hidden');
  try {
    const result = await api.getTopCustomersByCity(
      state.filterParams,
      state.currentCity,
      state.currentPage,
      state.limit
    );
    if (result.success && result.data) {
      state.totalPages = result.pagination.total_pages;
      renderTable(result.data, result.pagination);
      renderPagination();
      if (result.data.length > 0 && els.btnExport) {
        els.btnExport.classList.remove('hidden');
      }
    } else {
      throw new Error(result.message || "Gagal memuat data.");
    }
  } catch (error) {
    showError(error.message);
    els.tableContainer.classList.add('hidden');
  } finally {
    showLoading(false);
  }
}
function renderTable(data, pagination) {
  if (data.length === 0) {
    els.tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-gray-500">Tidak ada data ditemukan untuk kota <strong>${state.currentCity}</strong>.</td></tr>`;
    els.tableContainer.classList.remove('hidden');
    els.recordInfo.textContent = "0 Data";
    return;
  }
  const startRank = (state.currentPage - 1) * state.limit + 1;
  let html = '';
  data.forEach((item, index) => {
    const rank = startRank + index;
    const rankClass = rank <= 3 ? 'text-yellow-600 font-bold' : 'text-gray-600';
    const trophy = rank === 1 ? '<i class="fa-solid fa-trophy text-yellow-500 mr-1"></i>' : '';
    const detailUrl = `customer.php?kd_cust=${item.kd_cust}&nama_cust=${encodeURIComponent(item.nama_cust)}&filter_type=${state.filterParams.filter_type}&filter=${state.filterParams.filter}&start_date=${state.filterParams.start_date || ''}&end_date=${state.filterParams.end_date || ''}`;
    html += `
            <tr class="hover:bg-blue-50 transition-colors border-b">
                <td class="px-4 py-3 text-center ${rankClass}">${trophy}#${rank}</td>
                <td class="px-4 py-3">
                    <div class="font-semibold text-gray-900">${item.nama_cust}</div>
                    <div class="text-xs text-gray-500">${item.kd_cust}</div>
                </td>
                <td class="px-4 py-3">
                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                        ${item.kota || 'Tidak Diketahui'}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="font-bold text-gray-800">${formatNumber.format(item.freq)}x</span>
                    <span class="text-xs text-gray-500 block">Kunjungan</span>
                </td>
                <td class="px-4 py-3 text-right font-medium text-emerald-600">
                    ${formatCurrency.format(item.omset)}
                </td>
                <td class="px-4 py-3 text-center">
                    <a href="${detailUrl}" 
                        class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors"
                        title="Lihat Detail Belanja">
                        <i class="fa-solid fa-eye"></i> Detail
                    </a>
                </td>
            </tr>
        `;
  });
  els.tableBody.innerHTML = html;
  els.tableContainer.classList.remove('hidden');
  els.recordInfo.textContent = `Menampilkan ${data.length} dari ${formatNumber.format(pagination.total_records)} Customer`;
}
function renderPagination() {
  if (state.totalPages <= 1) {
    els.pagination.classList.add('hidden');
    return;
  }
  let html = '';
  html += `
        <button ${state.currentPage === 1 ? 'disabled' : ''} 
            class="px-3 py-1 rounded bg-white border border-gray-300 text-gray-600 hover:bg-gray-100 disabled:opacity-50 transition"
            onclick="changePage(${state.currentPage - 1})">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
    `;
  let startPage = Math.max(1, state.currentPage - 2);
  let endPage = Math.min(state.totalPages, startPage + 4);
  if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);
  for (let i = startPage; i <= endPage; i++) {
    html += `
            <button class="px-3 py-1 rounded border ${i === state.currentPage ? 'bg-blue-600 text-white border-blue-600' : 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50'}"
                onclick="changePage(${i})">
                ${i}
            </button>
        `;
  }
  html += `
        <button ${state.currentPage === state.totalPages ? 'disabled' : ''} 
            class="px-3 py-1 rounded bg-white border border-gray-300 text-gray-600 hover:bg-gray-100 disabled:opacity-50 transition"
            onclick="changePage(${state.currentPage + 1})">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    `;
  els.paginationBtns.innerHTML = html;
  els.pagination.classList.remove('hidden');
  window.changePage = (page) => {
    if (page < 1 || page > state.totalPages) return;
    state.currentPage = page;
    loadData();
  };
}
function showLoading(show) {
  if (show) {
    els.loading.classList.remove('hidden');
    els.tableContainer.classList.add('hidden');
    els.pagination.classList.add('hidden');
  } else {
    els.loading.classList.add('hidden');
  }
}
function showError(message) {
  if (message) {
    els.errorMsg.textContent = message;
    els.errorMsg.classList.remove('hidden');
    els.initialState.classList.add('hidden');
  } else {
    els.errorMsg.classList.add('hidden');
  }
}
async function handleExportExcel() {
  if (!state.currentCity) {
    Swal.fire("Error", "Pilih kota terlebih dahulu.", "error");
    return;
  }
  Swal.fire({
    title: "Menyiapkan Excel...",
    text: "Mengambil seluruh data customer...",
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });
  try {
    const result = await api.getExportTopCustomersByCity(state.filterParams, state.currentCity);
    if (!result.success || !result.data || result.data.length === 0) {
      throw new Error("Tidak ada data untuk diexport.");
    }
    const data = result.data;
    const workbook = new ExcelJS.Workbook();
    const sheet = workbook.addWorksheet("Top Customer");
    sheet.columns = [
      { key: "rank", width: 8 },
      { key: "nama", width: 35 },
      { key: "kota", width: 20 },
      { key: "no_hp", width: 18 },
      { key: "freq", width: 15 },
      { key: "omset", width: 20 },
    ];
    sheet.mergeCells("A1:G1");
    const titleCell = sheet.getCell("A1");
    const cityName = state.currentCity === 'all' ? 'SELURUH KOTA' : state.currentCity;
    titleCell.value = `TOP CUSTOMER: ${cityName}`;
    titleCell.font = { name: "Arial", size: 14, bold: true };
    titleCell.alignment = { horizontal: "center" };
    sheet.mergeCells("A2:G2");
    const subTitleCell = sheet.getCell("A2");
    let filterTxt = state.filterParams.filter_type === 'custom'
      ? `${state.filterParams.start_date} s/d ${state.filterParams.end_date}`
      : `Filter: ${state.filterParams.filter}`;
    subTitleCell.value = `Periode: ${filterTxt}`;
    subTitleCell.alignment = { horizontal: "center" };
    const tableHeaderRowIdx = 4;
    const headerRow = sheet.getRow(tableHeaderRowIdx);
    headerRow.values = ["Rank", "Nama Customer", "Kota", "No HP", "Frekuensi (x)", "Total Belanja (Rp)"];
    headerRow.eachCell((cell) => {
      cell.font = { bold: true };
      cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF3F4F6' } };
      cell.border = { bottom: { style: 'thin' }, top: { style: 'thin' } };
      cell.alignment = { horizontal: 'center', vertical: 'middle' };
    });
    let currentRow = tableHeaderRowIdx + 1;
    let grandTotalFreq = 0;
    let grandTotalOmset = 0;
    data.forEach((item, index) => {
      grandTotalFreq += item.freq;
      grandTotalOmset += item.omset;
      const r = sheet.getRow(currentRow);
      r.values = [
        index + 1,
        item.nama_cust,
        item.kota,
        item.kd_cust,
        item.freq,
        item.omset
      ];
      r.getCell(1).alignment = { horizontal: 'center' };
      r.getCell(3).alignment = { horizontal: 'center' };
      r.getCell(4).alignment = { horizontal: 'center' };
      r.getCell(5).alignment = { horizontal: 'center' };
      r.getCell(6).numFmt = '#,##0';
      r.getCell(6).alignment = { horizontal: 'center' };
      r.getCell(7).numFmt = '#,##0';
      if (index < 3) {
        r.getCell(1).font = { bold: true, color: { argb: 'FFD97706' } };
      }
      currentRow++;
    });
    currentRow++;
    sheet.mergeCells(`A${currentRow}:E${currentRow}`);
    const rowGrandTotal = sheet.getRow(currentRow);
    const cellGrandLabel = rowGrandTotal.getCell(1);
    cellGrandLabel.value = "GRAND TOTAL:";
    cellGrandLabel.font = { bold: true, size: 12, color: { argb: 'FFFFFFFF' } };
    cellGrandLabel.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF059669' } };
    cellGrandLabel.alignment = { horizontal: 'right', vertical: 'middle' };
    const cellGrandFreq = rowGrandTotal.getCell(6);
    cellGrandFreq.value = grandTotalFreq;
    cellGrandFreq.font = { bold: true, size: 12, color: { argb: 'FFFFFFFF' } };
    cellGrandFreq.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF059669' } };
    cellGrandFreq.alignment = { horizontal: 'center', vertical: 'middle' };
    cellGrandFreq.numFmt = '#,##0';
    const cellGrandOmset = rowGrandTotal.getCell(7);
    cellGrandOmset.value = grandTotalOmset;
    cellGrandOmset.font = { bold: true, size: 12, color: { argb: 'FFFFFFFF' } };
    cellGrandOmset.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF059669' } };
    cellGrandOmset.alignment = { horizontal: 'right', vertical: 'middle' };
    cellGrandOmset.numFmt = '#,##0';
    rowGrandTotal.height = 30;
    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = window.URL.createObjectURL(blob);
    const anchor = document.createElement("a");
    anchor.href = url;
    const timestamp = new Date().toISOString().slice(0, 10);
    const safeCity = cityName.replace(/[^a-zA-Z0-9]/g, '_');
    anchor.download = `TopCust_${safeCity}_${timestamp}.xlsx`;
    anchor.click();
    window.URL.revokeObjectURL(url);
    Swal.fire({
      icon: "success",
      title: "Selesai",
      text: "Data berhasil diexport.",
      timer: 1500,
      showConfirmButton: false,
    });
  } catch (error) {
    console.error(error);
    Swal.fire("Error", error.message, "error");
  }
}