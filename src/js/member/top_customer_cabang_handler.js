import * as api from "./member_api_service.js";

const state = {
  filterParams: {
    filter_type: null,
    filter: null,
    start_date: null,
    end_date: null,
  },
  currentCabang: null,
  currentPage: 1,
  limit: 10,
  totalPages: 1
};

const els = {
  cabangSelect: document.getElementById('cabang-filter'),
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
  // Ambil parameter filter dari URL (dikirim dari halaman report utama jika ada)
  const params = new URLSearchParams(window.location.search);
  state.filterParams.filter_type = params.get("filter_type") || 'preset';
  state.filterParams.filter = params.get("filter") || '3bulan';
  state.filterParams.start_date = params.get("start_date");
  state.filterParams.end_date = params.get("end_date");

  await loadCabang();
});

if (els.btnSearch) {
  els.btnSearch.addEventListener('click', () => {
    const selectedCabang = els.cabangSelect.value;
    if (!selectedCabang || selectedCabang === "") {
      Swal.fire({ icon: 'warning', title: 'Pilih Cabang', text: 'Silakan pilih cabang terlebih dahulu.', timer: 2000, showConfirmButton: false });
      return;
    }
    state.currentCabang = selectedCabang;
    state.currentPage = 1;
    loadData();
  });
}

if (els.btnExport) {
  els.btnExport.addEventListener('click', handleExportExcel);
}

// === LOAD LIST CABANG ===
async function loadCabang() {
  els.cabangSelect.innerHTML = '<option value="" disabled selected>Sedang memuat cabang...</option>';
  els.cabangSelect.disabled = true;

  try {
    const result = await api.getCabangOptions();
    if (result.success && result.data) {
      let optionsHtml = '<option value="" disabled selected>-- Pilih Cabang --</option>';
      // optionsHtml += '<option value="all">-- SELURUH CABANG --</option>'; // Aktifkan jika butuh semua cabang

      result.data.forEach(item => {
        // Menggunakan Nm_Alias sesuai request
        optionsHtml += `<option value="${item.Nm_Alias}">${item.Nm_Alias}</option>`;
      });

      els.cabangSelect.innerHTML = optionsHtml;
      els.cabangSelect.disabled = false;
    }
  } catch (error) {
    console.error("Gagal memuat list cabang:", error);
    els.cabangSelect.innerHTML = '<option value="" disabled selected>Gagal memuat data</option>';
  }
}

// === LOAD DATA TABLE ===
async function loadData() {
  els.initialState.classList.add('hidden');
  showLoading(true);
  showError(false);
  if (els.btnExport) els.btnExport.classList.add('hidden');

  try {
    const result = await api.getTopCustomersByCabang(
      state.filterParams,
      state.currentCabang,
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
    els.tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-gray-500">Tidak ada data ditemukan untuk cabang <strong>${state.currentCabang}</strong>.</td></tr>`;
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

    // Link detail masih mengarah ke detail customer umum (bisa disesuaikan)
    const detailUrl = `customer.php?kd_cust=${item.kd_cust}&nama_cust=${encodeURIComponent(item.nama_cust)}&filter_type=${state.filterParams.filter_type}&filter=${state.filterParams.filter}`;

    html += `
            <tr class="hover:bg-blue-50 transition-colors border-b">
                <td class="px-4 py-3 text-center ${rankClass}">${trophy}#${rank}</td>
                <td class="px-4 py-3">
                    <div class="font-semibold text-gray-900">${item.nama_cust}</div>
                    <div class="text-xs text-gray-500">${item.kd_cust}</div>
                </td>
                <td class="px-4 py-3">
                    <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">
                        <i class="fa-solid fa-map-pin mr-1 text-gray-400"></i>${item.kota || '-'}
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

  // Previous Button
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

  // Next Button
  html += `
        <button ${state.currentPage === state.totalPages ? 'disabled' : ''} 
            class="px-3 py-1 rounded bg-white border border-gray-300 text-gray-600 hover:bg-gray-100 disabled:opacity-50 transition"
            onclick="changePage(${state.currentPage + 1})">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    `;

  els.paginationBtns.innerHTML = html;
  els.pagination.classList.remove('hidden');

  // Attach function to global window for onclick events in HTML string
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
  if (!state.currentCabang) {
    Swal.fire("Error", "Pilih cabang terlebih dahulu.", "error");
    return;
  }

  Swal.fire({
    title: "Menyiapkan Excel...",
    text: "Mengambil data customer cabang " + state.currentCabang + "...",
    allowOutsideClick: false,
    didOpen: () => { Swal.showLoading(); },
  });

  try {
    const result = await api.getExportTopCustomersByCabang(state.filterParams, state.currentCabang);

    if (!result.success || !result.data || result.data.length === 0) {
      throw new Error("Tidak ada data untuk diexport.");
    }

    const data = result.data;
    const workbook = new ExcelJS.Workbook();
    const sheet = workbook.addWorksheet("Top Cust Cabang");

    // 1. Setup Kolom
    sheet.columns = [
      { key: "rank", width: 8 },    // A
      { key: "nama", width: 35 },   // B
      { key: "kota", width: 20 },   // C
      { key: "no_hp", width: 18 },  // D
      { key: "freq", width: 15 },   // E (Kolom ke-5)
      { key: "omset", width: 20 },  // F (Kolom ke-6)
    ];

    // 2. Header Judul
    sheet.mergeCells("A1:F1"); // Merge sampai F
    const titleCell = sheet.getCell("A1");
    titleCell.value = `TOP CUSTOMER CABANG: ${state.currentCabang}`;
    titleCell.font = { name: "Arial", size: 14, bold: true };
    titleCell.alignment = { horizontal: "center" };

    // 3. Sub Header Periode
    sheet.mergeCells("A2:F2"); // Merge sampai F
    const subTitleCell = sheet.getCell("A2");
    let filterTxt = state.filterParams.filter_type === 'custom'
      ? `${state.filterParams.start_date} s/d ${state.filterParams.end_date}`
      : `Filter: ${state.filterParams.filter}`;
    subTitleCell.value = `Periode: ${filterTxt}`;
    subTitleCell.alignment = { horizontal: "center" };

    // 4. Header Table
    const tableHeaderRowIdx = 4;
    const headerRow = sheet.getRow(tableHeaderRowIdx);
    headerRow.values = ["Rank", "Nama Customer", "Domisili Cust.", "No HP", "Frekuensi (x)", "Total Belanja (Rp)"];

    headerRow.eachCell((cell) => {
      cell.font = { bold: true };
      cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF3F4F6' } };
      cell.border = { bottom: { style: 'thin' }, top: { style: 'thin' } };
      cell.alignment = { horizontal: 'center', vertical: 'middle' };
    });

    // 5. Isi Data
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
        item.no_hp,
        item.freq,
        item.omset
      ];

      // Formatting cells
      r.getCell(1).alignment = { horizontal: 'center' }; // Rank
      r.getCell(5).alignment = { horizontal: 'center' }; // Freq
      r.getCell(6).numFmt = '#,##0'; // Omset

      currentRow++;
    });

    // 6. Footer Total (BAGIAN YANG DIPERBAIKI)
    currentRow++; // Pindah ke baris baru untuk Total

    // Merge Kolom A sampai D untuk label "TOTAL:"
    // Tidak perlu merge A-E lalu unmerge, langsung A-D saja.
    sheet.mergeCells(`A${currentRow}:D${currentRow}`);

    const cellLabel = sheet.getCell(`A${currentRow}`);
    cellLabel.value = "TOTAL:";
    cellLabel.font = { bold: true, size: 11 };
    cellLabel.alignment = { horizontal: 'right', vertical: 'middle' };
    cellLabel.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFE5E7EB' } }; // Abu-abu muda

    // Kolom E (Freq)
    const cellTotalFreq = sheet.getCell(currentRow, 5); // Kolom 5
    cellTotalFreq.value = grandTotalFreq;
    cellTotalFreq.font = { bold: true, size: 11 };
    cellTotalFreq.numFmt = '#,##0';
    cellTotalFreq.alignment = { horizontal: 'center', vertical: 'middle' };
    cellTotalFreq.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFE5E7EB' } };

    // Kolom F (Omset)
    const cellTotalOmset = sheet.getCell(currentRow, 6); // Kolom 6
    cellTotalOmset.value = grandTotalOmset;
    cellTotalOmset.font = { bold: true, size: 11 };
    cellTotalOmset.numFmt = '#,##0';
    cellTotalOmset.alignment = { horizontal: 'right', vertical: 'middle' }; // Rata kanan untuk uang
    cellTotalOmset.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFE5E7EB' } };

    // Download File
    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = window.URL.createObjectURL(blob);
    const anchor = document.createElement("a");
    anchor.href = url;
    const timestamp = new Date().toISOString().slice(0, 10);
    const safeCabang = state.currentCabang.replace(/[^a-zA-Z0-9]/g, '_');
    anchor.download = `TopCust_Cabang_${safeCabang}_${timestamp}.xlsx`;
    anchor.click();
    window.URL.revokeObjectURL(url);

    Swal.fire({ icon: "success", title: "Selesai", text: "Data berhasil diexport.", timer: 1500, showConfirmButton: false });

  } catch (error) {
    console.error(error);
    Swal.fire("Error", error.message, "error");
  }
}
