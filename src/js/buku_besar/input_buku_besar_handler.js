import { sendRequestGET, sendRequestJSON } from "../utils/api_helpers.js";
const API_URLS = {
  getFakturDetail: "/src/api/buku_besar/get_pembelian_detail.php",
  saveData: "/src/api/buku_besar/save_buku_besar.php",
  getData: "/src/api/buku_besar/get_latest_buku_besar.php",
  deleteData: "/src/api/buku_besar/delete_buku_besar.php",
  getStores: "/src/api/shared/get_all_store.php",
  searchSupplier: "/src/api/coretax/get_supplier_search.php",
  getExport: "/src/api/buku_besar/get_export_buku_besar.php",
  processImport: "/src/api/buku_besar/process_import_buku_besar.php",
  getGroupDetails: "/src/api/buku_besar/get_group_details.php",
  cekBukuBesar: "/src/api/buku_besar/get_latest_buku_besar.php",
  getHistory: "/src/api/buku_besar/get_angsuran_history.php",
};
const form = document.getElementById("single-form");
const inpId = document.getElementById("inp_id");
const inpKodeStore = document.getElementById("inp_kode_store");
const inpStoreBayar = document.getElementById("inp_store_bayar");
const inpNoFaktur = document.getElementById("inp_no_faktur");
const inpKodeSupplier = document.getElementById("inp_kode_supplier");
const inpNamaSupp = document.getElementById("inp_nama_supplier");
const inpNilaiFaktur = document.getElementById("inp_nilai_faktur");
const inpTglNota = document.getElementById("inp_tgl_nota");
const inpTglBayar = document.getElementById("inp_tgl_bayar");
const inpPotongan = document.getElementById("inp_potongan");
const inpKetPotongan = document.getElementById("inp_ket_potongan");
const inpNilaiTambahan = document.getElementById("inp_nilai_tambahan");
const inpKetTambahan = document.getElementById("inp_ket_tambahan"); 
const btnManageTambahan = document.getElementById("btn-manage-tambahan"); 
const modalTambahan = document.getElementById("modal-tambahan"); 
const containerListTambahan = document.getElementById("container-list-tambahan"); 
const btnAddRowTambahan = document.getElementById("btn-add-row-tambahan"); 
const btnSaveModalTambahan = document.getElementById("btn-save-modal-tambahan"); 
const lblTotalModalTambahan = document.getElementById("lbl-total-modal-tambahan"); 
const inpKet = document.getElementById("inp_ket");
const inpTop = document.getElementById("inp_top");
const inpStatus = document.getElementById("inp_status");
const btnSave = document.getElementById("btn-save");
const btnCancelEdit = document.getElementById("btn-cancel-edit");
const editIndicator = document.getElementById("edit-mode-indicator");
const tableBody = document.getElementById("table-body");
const inpSearchTable = document.getElementById("inp_search_table");
const loaderRow = document.getElementById("loader-row");
const listSupplier = document.getElementById("supplier_list");
const btnExport = document.getElementById("btn-export");
const btnImport = document.getElementById("btn-import");
const inpFileImport = document.getElementById("file_import");
const installmentInfoBox = document.getElementById("installment-info-box");
const infoSudahBayar = document.getElementById("info-sudah-bayar");
const infoSisaHutang = document.getElementById("info-sisa-hutang");
const btnViewHistoryDetail = document.getElementById("btn-view-history-detail");
const modalPotongan = document.getElementById("modal-potongan");
const containerListPotongan = document.getElementById("container-list-potongan");
const btnManagePotongan = document.getElementById("btn-manage-potongan");
const btnAddRowPotongan = document.getElementById("btn-add-row-potongan");
const btnSaveModalPotongan = document.getElementById("btn-save-modal-potongan");
const lblTotalModalPotongan = document.getElementById("lbl-total-modal-potongan");
let tempPotonganList = [];
let tempTambahanList = [];
let currentHistoryData = [];
let editingCartIndex = -1;
let currentGroupId = null;
let deletedCartIds = [];
let isSubmitting = false;
let debounceTimer;
let searchDebounceTimer;
let currentPage = 1;
let isLoadingData = false;
let hasMoreData = true;
let currentSearchTerm = "";
let tableRowIndex = 0;
let currentRequestController = null;
let cartItems = [];
const btnAddItem = document.getElementById("btn-add-item");
const btnSaveBatch = document.getElementById("btn-save-batch");
const tempListBody = document.getElementById("temp-list-body");
const lblCountItem = document.getElementById("lbl_count_item");
const inpKetGlobal = document.getElementById("inp_ket_global");
const inpGlobalTotal = document.getElementById("inp_global_total");
const lblTotalTagihan = document.getElementById("lbl_total_tagihan");
const lblSummaryBayar = document.getElementById("lbl_summary_bayar");
const lblSummarySelisih = document.getElementById("lbl_summary_selisih");
const lblSummarySelisihContainer = document.getElementById("lbl_summary_selisih_container");
const summaryPaymentDetails = document.getElementById("summary-payment-details");
function isFormDirty() {
  const hasFaktur = inpNoFaktur.value.trim() !== "";
  const hasSupplier = inpNamaSupp.value.trim() !== "";
  const hasPotongan = parseNumber(inpPotongan.value) !== 0;
  const hasKet = inpKet.value.trim() !== "";
  return (hasFaktur || hasSupplier || hasPotongan || hasKet);
}
function formatNumber(num) {
  if (isNaN(num) || num === null) return "0";
  return new Intl.NumberFormat("id-ID", {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(num);
}
function resetItemForm() {
  inpNoFaktur.value = "";
  tempPotonganList = [];
  tempTambahanList = [];
  inpNilaiFaktur.value = "0";
  if (inpNilaiTambahan) inpNilaiTambahan.value = "0"; 
  if (inpKetTambahan) inpKetTambahan.value = "";
  inpPotongan.value = "0";
  inpKetPotongan.value = "";
  inpTop.value = "";
  inpStatus.value = "";
  editingCartIndex = -1;
  btnAddItem.innerHTML = `<i class="fas fa-arrow-down mr-1"></i> Tambah ke Daftar`;
  btnAddItem.classList.remove("bg-yellow-500", "hover:bg-amber-600");
  btnAddItem.classList.add("bg-blue-600", "hover:bg-blue-700");
  if (installmentInfoBox) installmentInfoBox.classList.add("hidden");
  currentHistoryData = [];
  document.querySelectorAll("#temp-list-body tr").forEach(tr => tr.classList.remove("bg-amber-50"));
  inpGlobalTotal.disabled = false;
  inpGlobalTotal.classList.remove("bg-gray-100", "cursor-not-allowed");
  inpGlobalTotal.value = "";
  inpGlobalTotal.placeholder = "0";
  inpNoFaktur.focus();
}
function parseNumber(str) {
  if (str === undefined || str === null || str === '') return 0;
  if (typeof str === 'number') return str;
  const strVal = str.toString();
  const cleanStr = strVal.replace(/\./g, "").replace(",", ".");
  return parseFloat(cleanStr) || 0;
}
function showHistoryPopup() {
  if (!currentHistoryData || currentHistoryData.length === 0) {
    Swal.fire("Info", "Belum ada rincian history untuk ditampilkan.", "info");
    return;
  }
  let rows = currentHistoryData.map((h, index) => `
        <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50">
            <td class="py-2 text-gray-500 text-center">${index + 1}</td>
            <td class="py-2 text-gray-700">${formatDate(h.tanggal_bayar)}</td>
            <td class="py-2 text-gray-500 text-[10px] text-center">${h.store_bayar || '-'}</td>
            <td class="py-2 text-gray-500 text-[10px] text-center">${h.ket || '-'}</td>
            <td class="py-2 text-right font-mono font-bold text-gray-700">${formatNumber(h.nominal_bayar)}</td>
        </tr>
    `).join('');
  let html = `
        <div class="text-left">
            <div class="bg-blue-50 p-3 rounded mb-3 text-xs text-blue-800">
                <i class="fas fa-history mr-1"></i> Berikut adalah rincian pembayaran yang sudah masuk.
            </div>
            <div class="overflow-y-auto max-h-[300px] custom-scrollbar border rounded-lg">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-100 text-gray-600 font-bold sticky top-0 shadow-sm">
                        <tr>
                            <th class="py-2 px-2 text-center">#</th>
                            <th class="py-2 px-2">Tanggal</th>
                            <th class="py-2 px-2 text-center">Cabang</th>
                            <th class="py-2 px-2 text-center">MOP</th>
                            <th class="py-2 px-2 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        </div>
    `;
  Swal.fire({
    title: 'Rincian Pembayaran',
    html: html,
    width: '500px',
    showCloseButton: true,
    showConfirmButton: false
  });
}
if (btnViewHistoryDetail) {
  btnViewHistoryDetail.addEventListener("click", showHistoryPopup);
}
function formatDate(dateString) {
  if (!dateString) return "-";
  const dateObj = new Date(dateString);
  return dateObj.toLocaleDateString("id-ID", { day: "2-digit", month: "short", year: "numeric" });
}
async function fetchTableData(reset = false) {
  if (isLoadingData && !reset) return;
  if (reset) {
    currentPage = 1;
    tableRowIndex = 0;
    hasMoreData = true;
    if (currentRequestController) {
      currentRequestController.abort();
    }
    currentRequestController = new AbortController();
    tableBody.innerHTML = `
        <tr>
            <td colspan="15" class="text-center p-8">
                <div class="flex flex-col items-center justify-center">
                    <i class="fas fa-circle-notch fa-spin text-pink-500 text-3xl mb-3"></i>
                    <span class="text-gray-500 font-medium animate-pulse">Memuat data...</span>
                </div>
            </td>
        </tr>
    `;
    loaderRow.classList.add("hidden");
  }
  if (!hasMoreData && !reset) return;
  isLoadingData = true;
  if (!reset) {
    loaderRow.classList.remove("hidden");
  }
  try {
    const params = new URLSearchParams({
      page: currentPage,
      search: currentSearchTerm,
    });
    const signal = reset ? currentRequestController.signal : null;
    const response = await fetch(`${API_URLS.getData}?${params.toString()}`, {
      signal,
    });
    const result = await response.json();
    if (reset) {
      tableBody.innerHTML = "";
    }
    if (result.success && Array.isArray(result.data)) {
      if (result.data.length === 0 && currentPage === 1) {
        tableBody.innerHTML = `<tr><td colspan="15" class="text-center p-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">Data tidak ditemukan</td></tr>`;
        hasMoreData = false;
      } else {
        renderTableRows(result.data);
        hasMoreData = result.has_more;
        if (hasMoreData) currentPage++;
      }
    }
  } catch (error) {
    if (error.name === "AbortError") return;
    console.error(error);
    if (currentPage === 1) {
      tableBody.innerHTML = `<tr><td colspan="15" class="text-center p-4 text-red-500">Terjadi kesalahan koneksi</td></tr>`;
    }
  } finally {
    if (
      !currentRequestController ||
      (currentRequestController && !currentRequestController.signal.aborted)
    ) {
      isLoadingData = false;
      if (hasMoreData) loaderRow.classList.remove("hidden");
      else loaderRow.classList.add("hidden");
    }
  }
}
function renderCart() {
  if (cartItems.length === 0) {
    tempListBody.innerHTML = `<tr><td colspan="5" class="text-center p-10 text-gray-400">Belum ada data</td></tr>`;
    btnSaveBatch.disabled = true;
    btnSaveBatch.classList.add("opacity-50", "cursor-not-allowed");
    lblCountItem.textContent = "0";
    if (lblTotalTagihan) lblTotalTagihan.textContent = "0";
    if (summaryPaymentDetails) summaryPaymentDetails.classList.add("hidden");
    btnSave.style.display = "";
    return;
  }
  btnSave.style.display = "none";
  let html = "";
  cartItems.forEach((item, index) => {
    const activeClass = (index === editingCartIndex) ? "bg-amber-100 border-amber-300" : "hover:bg-blue-50 border-gray-100";
    const nmStore = item.nm_alias || item.nm_store_display || item.kode_store;
    html += `
            <tr class="border-b transition-colors cursor-pointer ${activeClass}" onclick="editCartItem(${index})">
                <td class="p-2 font-medium text-blue-700">
                    ${item.no_faktur}
                    ${item.id ? '<span class="text-[10px] bg-green-100 text-green-600 px-1 rounded ml-1">Saved</span>' : '<span class="text-[10px] bg-gray-100 text-gray-600 px-1 rounded ml-1">New</span>'}
                </td>
                <td class="p-2 text-xs">${nmStore}</td>
                <td class="p-2 text-right text-gray-600">${formatNumber(item.nilai_faktur)}</td>
                <td class="p-2 text-right text-green-600">${item.nilai_tambahan > 0 ? formatNumber(item.nilai_tambahan) : '-'}</td>
                <td class="p-2 text-right text-red-500">${item.potongan > 0 ? formatNumber(item.potongan) : '-'}</td>
                <td class="p-2 text-center" onclick="event.stopPropagation()">
                    <button onclick="removeCartItem(${index})" class="text-red-500 hover:text-red-700 w-6 h-6 rounded hover:bg-red-50 transition-all">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
  });
  tempListBody.innerHTML = html;
  lblCountItem.textContent = cartItems.length;
  btnSaveBatch.disabled = false;
  btnSaveBatch.classList.remove("opacity-50", "cursor-not-allowed");
  calculateSummary();
}
function renderTableRows(data) {
    for (let i = 0; i < data.length; i++) {
        const row = data[i];
        tableRowIndex++;
        
        // Grouping Logic
        const currentGroup = row.group_id;
        const prevGroup = (i > 0) ? data[i - 1].group_id : null;
        const isGroupStart = !currentGroup || (currentGroup !== prevGroup);
        let rowSpan = 1;

        if (currentGroup && isGroupStart) {
            for (let j = i + 1; j < data.length; j++) {
                if (data[j].group_id === currentGroup) {
                    rowSpan++;
                } else {
                    break;
                }
            }
        }

        const tr = document.createElement("tr");
        tr.className = "hover:bg-pink-50 transition-colors border-b border-gray-50";
        if (isGroupStart) tr.classList.add("row-group-start");

        // Basic Values
        const nilaiFaktur = parseFloat(row.nilai_faktur || 0);
        const total = parseFloat(row.total_bayar || 0);
        const storeBayarDisplay = row.store_bayar || "-";

        // --- LOGIKA SUM TAMBAHAN ---
        let htmlTambahan = '';
        let totalTambahan = 0;
        let hasDetailTambahan = false;

        if (row.details_tambahan && Array.isArray(row.details_tambahan) && row.details_tambahan.length > 0) {
            hasDetailTambahan = true;
            row.details_tambahan.forEach(det => totalTambahan += parseFloat(det.nominal || 0));
        } else {
            totalTambahan = parseFloat(row.nilai_tambahan || 0);
            // Jika ada nilai tapi tidak ada detail array, kita buat dummy detail untuk modal
            if(totalTambahan > 0) {
                 row.details_tambahan = [{ nominal: totalTambahan, keterangan: row.ket_tambahan || 'Tambahan Manual' }];
                 hasDetailTambahan = true;
            }
        }

        if (totalTambahan > 0) {
            // Encode data untuk dikirim ke onclick
            const jsonTambahan = JSON.stringify(row.details_tambahan || []).replace(/"/g, '&quot;');
            htmlTambahan = `
                <div class="cursor-pointer hover:bg-green-50 rounded px-1 -mx-1 transition-colors group" 
                     onclick="showSummaryModal('Detail Tambahan', ${jsonTambahan}, '${row.no_faktur}', 'text-green-600')">
                    <div class="text-green-600 font-mono text-sm font-bold border-b border-dashed border-green-300 group-hover:border-green-600 inline-block">
                        ${formatNumber(totalTambahan)}
                    </div>
                    <div class="text-[10px] text-green-400 group-hover:text-green-600">
                        <i class="fas fa-search-plus mr-1"></i>Lihat Detail
                    </div>
                </div>
            `;
        } else {
            htmlTambahan = `<span class="text-gray-300">-</span>`;
        }

        // --- LOGIKA SUM POTONGAN ---
        let htmlPotongan = '';
        let totalPotongan = 0;
        let hasDetailPotongan = false;

        if (row.details_potongan && Array.isArray(row.details_potongan) && row.details_potongan.length > 0) {
            hasDetailPotongan = true;
            row.details_potongan.forEach(det => totalPotongan += parseFloat(det.nominal || 0));
        } else {
            totalPotongan = parseFloat(row.potongan || 0);
             // Jika ada nilai tapi tidak ada detail array
            if(totalPotongan > 0) {
                 row.details_potongan = [{ nominal: totalPotongan, keterangan: row.ket_potongan || 'Potongan Manual' }];
                 hasDetailPotongan = true;
            }
        }

        if (totalPotongan > 0) {
            // Encode data untuk dikirim ke onclick
            const jsonPotongan = JSON.stringify(row.details_potongan || []).replace(/"/g, '&quot;');
            htmlPotongan = `
                <div class="cursor-pointer hover:bg-red-50 rounded px-1 -mx-1 transition-colors group" 
                     onclick="showSummaryModal('Detail Potongan', ${jsonPotongan}, '${row.no_faktur}', 'text-red-600')">
                    <div class="text-red-500 font-mono text-sm font-bold border-b border-dashed border-red-300 group-hover:border-red-600 inline-block">
                        ${formatNumber(totalPotongan)}
                    </div>
                    <div class="text-[10px] text-red-300 group-hover:text-red-500">
                        <i class="fas fa-search-plus mr-1"></i>Lihat Detail
                    </div>
                </div>
            `;
        } else {
            htmlPotongan = `<span class="text-gray-300">-</span>`;
        }


        // --- BUILD HTML ROW ---
        let html = '';
        html += `<td class="text-center text-gray-500 py-3 align-top">${tableRowIndex}</td>`;
        
        // Date & Group Handling
        if (isGroupStart) {
            html += `<td class="text-sm cell-merged font-medium" rowspan="${rowSpan}">${row.tanggal_bayar || "-"}</td>`;
        } else if (!currentGroup) {
            html += `<td class="text-sm align-top">${row.tanggal_bayar || "-"}</td>`;
        }

        html += `<td class="text-sm align-top text-gray-600">${row.tgl_nota || "-"}</td>`;
        html += `<td class="font-medium text-gray-800 text-sm align-top">${row.no_faktur}</td>`;

        // Supplier & Group Handling
        if (isGroupStart) {
            html += `<td class="text-sm cell-merged" rowspan="${rowSpan}">${row.nama_supplier}</td>`;
        } else if (!currentGroup) {
            html += `<td class="text-sm align-top">${row.nama_supplier}</td>`;
        }

        html += `<td class="align-top"><span class="bg-gray-100 text-gray-600 text-[10px] px-2 py-0.5 rounded border border-gray-200">${row.status || '-'}</span></td>`;
        html += `<td class="align-top text-xs text-red-500 font-medium">${row.top || '-'}</td>`;
        html += `<td class="align-top"><span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded border border-gray-200">${row.nm_alias || row.kode_store}</span></td>`;
        
        // Cabang Bayar & MOP Handling
        if (isGroupStart) {
            html += `<td class="cell-merged" rowspan="${rowSpan}"><span class="bg-pink-50 text-pink-700 text-xs px-2 py-1 rounded border border-pink-100 font-bold">${storeBayarDisplay}</span></td>`;
            html += `<td class="cell-merged font-bold text-blue-700 text-xs" rowspan="${rowSpan}">${row.ket || "-"}</td>`;
        } else if (!currentGroup) {
            html += `<td class="align-top"><span class="bg-pink-50 text-pink-700 text-xs px-2 py-1 rounded border border-pink-100 font-bold">${storeBayarDisplay}</span></td>`;
            html += `<td class="align-top font-bold text-blue-700 text-xs">${row.ket || "-"}</td>`;
        }

        // Values Columns
        html += `
            <td class="text-right font-mono text-sm text-gray-600 align-top">${formatNumber(nilaiFaktur)}</td>
            <td class="text-right align-top">
                ${htmlTambahan}
            </td>
            <td class="text-right align-top">
                ${htmlPotongan}
            </td>
            <td class="text-right font-bold font-mono text-pink-600 text-sm align-top">${formatNumber(total)}</td>
            <td class="text-center py-2 align-top">
                <div class="flex justify-center gap-1">
                    <button class="btn-edit-row text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 w-8 h-8 flex items-center justify-center rounded transition-all" title="Edit">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button class="btn-delete-row text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 w-8 h-8 flex items-center justify-center rounded transition-all" data-id="${row.id}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </td>
        `;

        tr.innerHTML = html;
        tr.querySelector(".btn-edit-row").addEventListener("click", () => startEditMode(row));
        tr.querySelector(".btn-delete-row").addEventListener("click", () => handleDelete(row.id));
        tableBody.appendChild(tr);
    }
}

function setupInfinityScroll() {
  const observerOptions = {
    root: document.getElementById("table-scroll-container"),
    rootMargin: "100px",
    threshold: 0.1,
  };
  const observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting && !isLoadingData && hasMoreData) {
      fetchTableData(false);
    }
  }, observerOptions);
  observer.observe(loaderRow);
}
async function loadStoreOptions() {
  try {
    const result = await sendRequestGET(API_URLS.getStores);
    if (result.success && Array.isArray(result.data)) {
      let html = '<option value="">Pilih Cabang</option>';
      result.data.forEach((store) => {
        const displayName = store.Nm_Alias ? `${store.Nm_Alias} (${store.Kd_Store})` : store.Nm_Store;
        html += `<option value="${store.Kd_Store}">${displayName}</option>`;
      });
      inpKodeStore.innerHTML = html;
    }
  } catch (error) {
    console.error("Gagal memuat toko:", error);
  }
}
async function fetchFakturData(noFaktur) {
  if (!noFaktur) return;
  const originalPlaceholder = inpNoFaktur.placeholder;
  inpNoFaktur.placeholder = "Mengecek data...";
  const currentStore = inpKodeStore.value;
  if (installmentInfoBox) installmentInfoBox.classList.add("hidden");
  currentHistoryData = [];
  try {
    const url = `${API_URLS.getFakturDetail}?no_faktur=${encodeURIComponent(noFaktur)}&kode_store=${encodeURIComponent(currentStore)}`;
    const result = await sendRequestGET(url);
    if (result.success && result.found && result.data) {
      const d = result.data;
      if (result.source === 'buku_besar') {
        if (d.details_potongan && Array.isArray(d.details_potongan)) {
          tempPotonganList = d.details_potongan;
        } else {
          tempPotonganList = [];
        }
        if (d.details_tambahan && Array.isArray(d.details_tambahan)) {
            tempTambahanList = d.details_tambahan;
        } else {
            tempTambahanList = [];
        }
        if (d.kode_store) {
          const exists = [...inpKodeStore.options].some(o => o.value == d.kode_store);
          if (!exists) {
            const label = d.nm_alias || d.nm_store_display || d.kode_store;
            const newOpt = new Option(label, d.kode_store, true, true);
            inpKodeStore.add(newOpt);
          }
          inpKodeStore.value = d.kode_store;
        }
        let tambahanHitung = parseFloat(d.nilai_tambahan || 0);
        let nilaiFakturHitung = parseFloat(d.nilai_faktur);
        let sudahBayarHitung = parseFloat(d.total_bayar);
        let potonganHitung = parseFloat(d.potongan);
        let isGroupMode = false;
        if (d.group_totals) {
          nilaiFakturHitung = parseFloat(d.group_totals.nilai_faktur);
          if (d.group_totals.potongan) {
            potonganHitung = parseFloat(d.group_totals.potongan);
          }
          sudahBayarHitung = parseFloat(d.group_totals.total_bayar);
          isGroupMode = true;
        }
        const sisaHutang = (nilaiFakturHitung + tambahanHitung - potonganHitung) - sudahBayarHitung;
        if (sisaHutang > 100 || sudahBayarHitung > 0) {
          if (infoSudahBayar) {
            infoSudahBayar.innerHTML = formatNumber(sudahBayarHitung);
            infoSudahBayar.dataset.original = sudahBayarHitung;
          }
          if (infoSisaHutang) infoSisaHutang.textContent = formatNumber(sisaHutang);
          if (installmentInfoBox) installmentInfoBox.classList.remove("hidden");
          try {
            const histResp = await fetch(`${API_URLS.getHistory}?buku_besar_id=${d.id}`);
            const histJson = await histResp.json();
            if (histJson.success) currentHistoryData = histJson.data;
          } catch (e) { console.error("Gagal load history", e); }
        } else {
          if (installmentInfoBox) installmentInfoBox.classList.add("hidden");
        }
        inpKodeSupplier.value = d.kode_supplier;
        inpNamaSupp.value = d.nama_supplier;
        inpTglNota.value = d.tgl_nota;
        inpTop.value = d.top || "";
        inpStatus.value = d.status || "";
        if (d.store_bayar) inpStoreBayar.value = d.store_bayar;
        const mopValueServer = (d.ket === 'TRANSFER' || d.ket === 'CASH') ? d.ket : "";
        inpKetGlobal.value = mopValueServer;
        if (d.ket) inpKetGlobal.value = d.ket;
        if (inpKetTambahan) inpKetTambahan.value = d.ket_tambahan || "";
        inpNilaiFaktur.value = formatNumber(parseFloat(d.nilai_faktur));
        inpPotongan.value = formatNumber(parseFloat(d.potongan));
        inpKetPotongan.value = d.ket_potongan || "";
        inpNilaiFaktur.readOnly = false; 
        inpNilaiFaktur.classList.remove('bg-gray-100', 'cursor-not-allowed');
        inpNoFaktur.classList.remove("bg-yellow-50");
        inpNoFaktur.classList.add("bg-blue-50", "text-blue-700", "font-bold");
        const suggest = sisaHutang > 0 ? sisaHutang : 0;
        inpGlobalTotal.value = formatNumber(suggest);
        if (sisaHutang <= 100) {
             inpGlobalTotal.disabled = true;
             inpGlobalTotal.classList.add("bg-gray-100", "cursor-not-allowed");
             inpGlobalTotal.value = "0";
             inpGlobalTotal.placeholder = "LUNAS";
        } else {
             inpGlobalTotal.disabled = false;
             inpGlobalTotal.classList.remove("bg-gray-100", "cursor-not-allowed");
        }
        const msg = sisaHutang > 100
          ? `ℹ️ Angsuran Sisa: ${formatNumber(sisaHutang)}`
          : `✅ Lunas.`;
        Toastify({
          text: msg,
          duration: 3000,
          style: { background: sisaHutang > 100 ? "#3b82f6" : "#10b981" }
        }).showToast();
        setTimeout(() => {
          inpGlobalTotal.focus();
          inpGlobalTotal.select();
        }, 300);
        return;
      }
      if (result.source === 'pembelian') {
        if (d.no_faktur) inpNoFaktur.value = d.no_faktur;
        if (d.kode_store) {
          const exists = [...inpKodeStore.options].some(o => o.value == d.kode_store);
          if (!exists) {
            const label = d.nm_alias || d.nm_store_display || d.kode_store;
            const newOpt = new Option(label, d.kode_store, true, true);
            inpKodeStore.add(newOpt);
          }
          inpKodeStore.value = d.kode_store;
        }
        inpKodeSupplier.value = d.kode_supplier || "";
        inpNamaSupp.value = d.nama_supplier || "";
        inpTglNota.value = d.tgl_nota || "";
        inpStatus.value = d.status || "";
        inpNilaiFaktur.value = formatNumber(parseFloat(d.total_bayar) || 0);
        inpPotongan.value = "0";
        inpKetPotongan.value = "";
        inpNilaiFaktur.readOnly = false;
        inpNilaiFaktur.classList.remove('bg-gray-100', 'cursor-not-allowed');
        inpPotongan.classList.remove('bg-gray-100', 'cursor-not-allowed');
        inpNoFaktur.classList.remove("bg-yellow-50");
        inpNoFaktur.classList.add("bg-green-50", "text-green-700");
        if (installmentInfoBox) installmentInfoBox.classList.add("hidden");
        Toastify({ text: "✅ Data Pembelian Ditemukan", duration: 2000, style: { background: "#10b981" } }).showToast();
      }
    } else {
      inpNoFaktur.classList.remove("bg-yellow-50");
      inpNilaiFaktur.readOnly = false;
      inpNilaiFaktur.classList.remove('bg-gray-100', 'cursor-not-allowed');
      inpPotongan.classList.remove('bg-gray-100', 'cursor-not-allowed');
      if (installmentInfoBox) installmentInfoBox.classList.add("hidden");
      Toastify({ text: "ℹ️ Data tidak ditemukan, silakan input manual", duration: 3000, style: { background: "#3b82f6" } }).showToast();
    }
  } catch (error) {
    console.error("Fetch Error", error);
    inpNoFaktur.classList.remove("bg-yellow-50");
  } finally {
    inpNoFaktur.placeholder = originalPlaceholder;
  }
}
async function handleSupplierSearch(e) {
  const term = e.target.value;
  if (term.length < 2) return;
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(async () => {
    try {
      const result = await sendRequestGET(`${API_URLS.searchSupplier}?term=${encodeURIComponent(term)}`);
      if (result.success && Array.isArray(result.data)) {
        let options = "";
        result.data.forEach((name) => { options += `<option value="${name}">`; });
        listSupplier.innerHTML = options;
      }
    } catch (err) { console.error(err); }
  }, 300);
}
async function handleSave() {
  const noFaktur = inpNoFaktur.value.trim();
  const namaSupp = inpNamaSupp.value.trim();
  const storeBayar = inpStoreBayar.value.trim();
  const kodeStore = inpKodeStore.value;
  const tglNota = inpTglNota.value;
  const status = inpStatus.value;
  const mop = inpKetGlobal.value;
  if (!namaSupp) return Swal.fire("Validasi Gagal", "Nama Supplier wajib diisi!", "warning");
  if (!status) return Swal.fire("Validasi Gagal", "Status Pajak wajib dipilih!", "warning");
  if (!noFaktur) return Swal.fire("Validasi Gagal", "Nomor Invoice wajib diisi!", "warning");
  if (!kodeStore) return Swal.fire("Validasi Gagal", "Cabang (Inv) wajib dipilih!", "warning");
  if (!tglNota) return Swal.fire("Validasi Gagal", "Tanggal Nota wajib diisi!", "warning");
  const nilaiFaktur = parseNumber(inpNilaiFaktur.value);
  const potongan = parseNumber(inpPotongan.value);
  const nilaiTambahan = parseNumber(inpNilaiTambahan.value); 
  const inputTotalBayar = parseNumber(inpGlobalTotal.value);
  if (nilaiFaktur <= 0) return Swal.fire("Validasi Gagal", "Nilai Faktur wajib diisi (lebih dari 0)!", "warning");
  let historyBayar = 0;
  if (installmentInfoBox && !installmentInfoBox.classList.contains("hidden")) {
    if (infoSudahBayar.dataset.original) {
        historyBayar = parseFloat(infoSudahBayar.dataset.original);
    } else {
        historyBayar = parseNumber(infoSudahBayar.textContent);
    }
  }
  let totalTransaksi = (nilaiFaktur + nilaiTambahan) - potongan;
  let sisaTagihan = totalTransaksi - historyBayar;
  if (sisaTagihan < 0) sisaTagihan = 0;
  let labelTagihan = "Sisa Tagihan (Netto)";
  const finalTotalBayar = inputTotalBayar;
  if (inputTotalBayar > 0 && Math.abs(inputTotalBayar - sisaTagihan) > 100) {
    const selisih = inputTotalBayar - sisaTagihan;
    const textSelisih = formatNumber(Math.abs(selisih));
    const statusSelisih = selisih > 0 ? "LEBIH" : "KURANG";
    const confirm = await Swal.fire({
      title: '⚠️ NOMINAL TIDAK BALANCE!',
      html: `
        <div class="text-left text-sm bg-red-50 p-4 rounded-lg border border-red-200">
            <div class="flex items-center mb-3 text-red-800 font-bold border-b border-red-200 pb-2">
                <i class="fas fa-exclamation-triangle mr-2"></i> Input Bayar tidak sama dengan Tagihan!
            </div>
            <div class="bg-white p-3 rounded border border-gray-200 mb-3 shadow-sm">
                <p class="text-xs font-bold text-gray-500 mb-2 uppercase tracking-wide">Rincian Perhitungan</p>
                <table class="w-full text-xs">
                    <tr>
                        <td class="py-1 text-gray-600">Nilai Faktur</td>
                        <td class="py-1 text-right font-mono">${formatNumber(nilaiFaktur)}</td>
                    </tr>
                    ${nilaiTambahan > 0 ? `
                    <tr>
                        <td class="py-1 text-gray-600">Nilai Tambahan</td>
                        <td class="py-1 text-right font-mono text-green-600">+ ${formatNumber(nilaiTambahan)}</td>
                    </tr>` : ''}
                    <tr>
                        <td class="py-1 text-gray-600">Total Potongan</td>
                        <td class="py-1 text-right font-mono text-red-500">- ${formatNumber(potongan)}</td>
                    </tr>
                    <tr class="border-b border-dashed border-gray-300">
                        <td class="py-1 text-blue-600 font-medium">Sudah Dibayar (History)</td>
                        <td class="py-1 text-right font-mono text-blue-600 font-medium">- ${formatNumber(historyBayar)}</td>
                    </tr>
                    <tr class="bg-gray-50 font-bold">
                        <td class="py-2 px-1 text-gray-800">${labelTagihan}</td>
                        <td class="py-2 px-1 text-right text-gray-800 border-t border-gray-300">${formatNumber(sisaTagihan)}</td>
                    </tr>
                </table>
            </div>
            <div class="bg-white p-3 rounded border border-red-200 shadow-sm">
                <p class="text-xs font-bold text-gray-500 mb-2 uppercase tracking-wide">Analisa Selisih</p>
                <table class="w-full text-sm">
                    <tr>
                        <td class="py-1 text-gray-600">Sisa Tagihan</td>
                        <td class="py-1 font-bold text-right text-gray-700">${formatNumber(sisaTagihan)}</td>
                    </tr>
                    <tr>
                        <td class="py-1 text-gray-600">Input Bayar (Sekarang)</td>
                        <td class="py-1 font-bold text-right text-blue-600">${formatNumber(inputTotalBayar)}</td>
                    </tr>
                    <tr class="border-t-2 border-red-100">
                        <td class="py-2 font-bold text-red-600">Selisih ${statusSelisih}</td>
                        <td class="py-2 font-bold text-right text-red-600 text-lg">${textSelisih}</td>
                    </tr>
                </table>
            </div>
        </div>
        <p class="mt-4 text-gray-600 font-medium text-center">Yakin ingin menyimpan data ini?</p>
      `,
      icon: 'warning',
      width: '500px',
      showCancelButton: true,
      confirmButtonText: 'Ya, Simpan',
      confirmButtonColor: '#d33',
      cancelButtonText: 'Cek Kembali'
    });
    if (!confirm.isConfirmed) return;
  }
  if (finalTotalBayar === 0) {
    const confirmZero = await Swal.fire({
      title: 'Simpan Tanpa Pembayaran?',
      text: "Total Bayar 0. Data akan disimpan/diupdate (TOP/Potongan/Tambahan) tetapi tidak masuk ke histori pembayaran.",
      icon: 'info',
      showCancelButton: true,
      confirmButtonText: 'Ya, Simpan',
    });
    if (!confirmZero.isConfirmed) return;
  }
  isSubmitting = true;
  const ketValue = inpKetGlobal.value;
  const payload = {
    id: inpId.value || null,
    kode_store: inpKodeStore.value,
    store_bayar: inpStoreBayar.value,
    no_faktur: noFaktur,
    kode_supplier: inpKodeSupplier.value,
    nama_supplier: inpNamaSupp.value,
    tgl_nota: inpTglNota.value,
    tanggal_bayar: inpTglBayar.value,
    potongan: potongan,
    nilai_faktur: nilaiFaktur,
    nilai_tambahan: nilaiTambahan, 
    ket_tambahan: inpKetTambahan.value,
    ket_potongan: inpKetPotongan.value,
    ket: inpKetGlobal.value === "" ? null : inpKetGlobal.value,
    total_bayar: finalTotalBayar,
    top: inpTop.value,
    status: inpStatus.value,
    details_potongan: tempPotonganList,
    details_tambahan: tempTambahanList
  };
  const originalBtnContent = btnSave.innerHTML;
  const originalBtnClass = btnSave.className;
  btnSave.disabled = true;
  btnSave.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Proses...`;
  try {
    const result = await sendRequestJSON(API_URLS.saveData, payload);
    if (result.success) {
      Swal.fire({ 
        icon: "success", 
        title: "Berhasil", 
        text: result.message, 
        timer: 1000, 
        showConfirmButton: false 
      });
      cancelEditMode();
      fetchTableData(true);
    } else {
      throw new Error(result.message);
    }
  } catch (error) {
    Swal.fire("Gagal Simpan", error.message, "error");
  } finally {
    btnSave.disabled = false;
    btnSave.innerHTML = originalBtnContent;
    btnSave.className = originalBtnClass;
    isSubmitting = false;
  }
}
async function startEditMode(data) {
  cancelEditMode();
  Swal.fire({
    title: "Memuat Data...",
    text: "Sedang mengambil rincian...",
    didOpen: () => Swal.showLoading()
  });
  try {
    if (data.group_id) {
      const resp = await sendRequestGET(`${API_URLS.getGroupDetails}?group_id=${data.group_id}`);
      Swal.close();
      if (resp.success && resp.data.length > 0) {
        currentGroupId = data.group_id;
        cartItems = resp.data.map(item => ({
          ...item,
          nilai_faktur: parseFloat(item.nilai_faktur),
          potongan: parseFloat(item.potongan),
          nilai_tambahan: parseFloat(item.nilai_tambahan || 0), 
          total_bayar: 0, 
          sudah_bayar_history: parseFloat(item.total_bayar), 
          details_potongan: item.details_potongan || [],
          details_tambahan: item.details_tambahan || [] 
      }));
        deletedCartIds = [];
        const firstItem = cartItems[0];
        inpNamaSupp.value = firstItem.nama_supplier;
        inpKodeSupplier.value = firstItem.kode_supplier;
        inpTglBayar.value = firstItem.tanggal_bayar;
        inpStoreBayar.value = firstItem.store_bayar;
        inpKetGlobal.value = firstItem.ket;
        editIndicator.classList.remove("hidden");
        editIndicator.innerHTML = `<i class="fas fa-layer-group mr-2"></i> MODE EDIT GROUP (${data.group_id})`;
        btnCancelEdit.classList.remove("hidden");
        renderCart();
        window.scrollTo({ top: 0, behavior: "smooth" });
        Toastify({ text: "Data group dimuat ke keranjang", style: { background: "#db2777" } }).showToast();
      }
    } else {
      const url = `${API_URLS.getFakturDetail}?no_faktur=${encodeURIComponent(data.no_faktur)}&kode_store=${encodeURIComponent(data.kode_store)}`;
      const result = await sendRequestGET(url);
      Swal.close();
      if (result.success && result.found) {
        const detailedData = result.data;
        currentGroupId = null;
        const singleItem = {
          ...detailedData,
          nilai_faktur: parseFloat(detailedData.nilai_faktur),
          potongan: parseFloat(detailedData.potongan),
          tgl_nota: detailedData.tgl_nota, 
          total_bayar: 0, 
          nilai_tambahan: parseFloat(detailedData.nilai_tambahan || 0),
          sudah_bayar_history: parseFloat(detailedData.total_bayar), 
          details_potongan: detailedData.details_potongan || [],
          details_tambahan: detailedData.details_tambahan || []
        };
        cartItems = [singleItem];
        deletedCartIds = [];
        inpNamaSupp.value = detailedData.nama_supplier;
        inpKodeSupplier.value = detailedData.kode_supplier;
        inpTglBayar.value = detailedData.tanggal_bayar || "";
        const mopValue = (detailedData.ket === 'TRANSFER' || detailedData.ket === 'CASH') ? detailedData.ket : "";
        inpKetGlobal.value = mopValue;
        inpKetGlobal.value = detailedData.ket;
        editIndicator.classList.remove("hidden");
        editIndicator.innerHTML = `<i class="fas fa-pencil-alt mr-2"></i> MODE EDIT DATA`;
        btnCancelEdit.classList.remove("hidden");
        renderCart();
        window.scrollTo({ top: 0, behavior: "smooth" });
        editCartItem(0);
      } else {
        throw new Error("Gagal mengambil detail data terbaru dari server.");
      }
    }
  } catch (e) {
    Swal.close();
    console.error(e);
    Swal.fire("Error", "Gagal memuat data edit: " + e.message, "error");
  }
}
function cancelEditMode() {
  inpId.value = "";
  inpKetGlobal.value = "";
  if (inpGlobalTotal) inpGlobalTotal.value = "";
  cartItems = [];
  deletedCartIds = [];
  currentGroupId = null;
  editingCartIndex = -1;
  inpNamaSupp.value = "";
  inpKodeSupplier.value = "";
  inpStoreBayar.value = "";
  inpKodeStore.value = "";
  inpTglNota.value = "";
  resetItemForm();
  renderCart();
  editIndicator.classList.add("hidden");
  btnCancelEdit.classList.add("hidden");
  btnSave.innerHTML = `<i class="fas fa-save"></i> <span>Simpan</span>`;
  btnSave.classList.remove("hidden");
  btnSave.className = "btn-primary shadow-lg shadow-pink-500/30 flex items-center gap-2 px-6 py-2";
  document.querySelector(".input-row-container").classList.remove("border-amber-300", "bg-amber-50");
  inpTglBayar.value = "";
  inpPotongan.value = "0";
}
function handleDelete(id) {
  Swal.fire({
    title: "Hapus Data?",
    text: "Yakin ingin menghapus data ini?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#ef4444",
    confirmButtonText: "Hapus",
    cancelButtonText: "Batal"
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const resp = await sendRequestJSON(API_URLS.deleteData, { id: id });
        if (resp.success) {
          Swal.fire("Terhapus!", resp.message, "success");
          fetchTableData(true);
          if (inpId.value == id) cancelEditMode();
        } else {
          throw new Error(resp.message);
        }
      } catch (err) {
        Swal.fire("Gagal", err.message, "error");
      }
    }
  });
}
async function handleExport() {
  Swal.fire({
    title: "Menyiapkan Excel...",
    text: "Sedang mengambil data...",
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading(),
  });
  try {
    const params = new URLSearchParams({
      search: currentSearchTerm,
    });
    const response = await fetch(`${API_URLS.getExport}?${params.toString()}`);
    const result = await response.json();
    if (!result.success) throw new Error(result.message);
    const data = result.data;
    if (!data || data.length === 0) {
      Swal.fire("Info", "Tidak ada data untuk diexport", "info");
      return;
    }
    const workbook = new ExcelJS.Workbook();
    const sheet = workbook.addWorksheet("Data Buku Besar");
    sheet.columns = [
      { header: "Tgl Bayar", key: "tanggal_bayar", width: 12 },
      { header: "Tgl Nota", key: "tgl_nota", width: 12 },
      { header: "No Faktur", key: "no_faktur", width: 20 },
      { header: "Kode Supp", key: "kode_supplier", width: 15 },
      { header: "Nama Supplier", key: "nama_supplier", width: 30 },
      { header: "Cabang Inv", key: "nm_alias", width: 15 },
      { header: "Cabang Bayar", key: "nm_alias_bayar", width: 15 },
      { header: "Nilai Faktur", key: "nilai_faktur", width: 15 },
      { header: "Potongan", key: "potongan", width: 15 },
      { header: "Ket Potongan", key: "ket_potongan", width: 20 },
      { header: "Total Bayar", key: "total_bayar", width: 15 },
      { header: "MOP", key: "ket", width: 15 },
      { header: "Status Pajak", key: "status", width: 15 },
      { header: "TOP", key: "top", width: 15 }
    ];
    const headerRow = sheet.getRow(1);
    sheet.columns.forEach((col, index) => {
      const cell = headerRow.getCell(index + 1);
      cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
      cell.fill = {
        type: "pattern",
        pattern: "solid",
        fgColor: { argb: "FFEC4899" },
      };
      cell.alignment = { horizontal: "center", vertical: "middle" };
      cell.border = { top: { style: "thin" }, left: { style: "thin" }, bottom: { style: "thin" }, right: { style: "thin" } };
    });
    data.forEach((row) => {
      sheet.addRow({
        tanggal_bayar: row.tanggal_bayar,
        tgl_nota: row.tgl_nota,
        no_faktur: row.no_faktur,
        kode_supplier: row.kode_supplier,
        nama_supplier: row.nama_supplier,
        nm_alias: row.nm_alias || row.kode_store,
        nm_alias_bayar: row.nm_alias_bayar || row.store_bayar,
        nilai_faktur: parseFloat(row.nilai_faktur) || 0,
        potongan: parseFloat(row.potongan) || 0,
        ket_potongan: row.ket_potongan,
        total_bayar: parseFloat(row.total_bayar) || 0,
        ket: row.ket,
        status: row.status,
        top: row.top
      });
    });
    ["nilai_faktur", "potongan", "total_bayar"].forEach((key) => {
      sheet.getColumn(key).numFmt = "#,##0";
    });
    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], {
      type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });
    const url = window.URL.createObjectURL(blob);
    const anchor = document.createElement("a");
    anchor.href = url;
    anchor.download = `Buku_Besar_${new Date().toISOString().slice(0, 10)}.xlsx`;
    anchor.click();
    window.URL.revokeObjectURL(url);
    Swal.close();
  } catch (error) {
    console.error(error);
    Swal.fire("Gagal Export", error.message, "error");
  }
}
async function handleImport(e) {
  const file = e.target.files[0];
  if (!file) return;
  const formData = new FormData();
  formData.append("file_excel", file);
  const token = document.cookie.match("(^|;)\\s*admin_token\\s*=\\s*([^;]+)")?.[2];
  Swal.fire({
    title: "Sedang Mengimport...",
    html: "Mohon tunggu, validasi data sedang berjalan...",
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading(),
  });
  try {
    const headers = {};
    if (token) headers["Authorization"] = `Bearer ${token}`;
    const response = await fetch(API_URLS.processImport, {
      method: "POST",
      headers: headers,
      body: formData,
    });
    const result = await response.json();
    if (result.success) {
      let msgHtml = `<div class="text-center mb-3 text-sm">${result.message.replace(/\n/g, "<br>")}</div>`;
      if (result.logs && result.logs.length > 0) {
        msgHtml += `
            <div class="mt-2 text-left bg-red-50 border border-red-200 rounded-lg p-3">
                <p class="text-xs font-bold text-red-700 mb-2">Detail Error:</p>
                <ul class="list-disc pl-4 text-xs text-red-600 font-mono overflow-y-auto max-h-[150px] custom-scrollbar">
                    ${result.logs.map((log) => `<li>${log}</li>`).join("")}
                </ul>
            </div>
        `;
      }
      Swal.fire({
        title: "Proses Selesai",
        html: msgHtml,
        icon: result.logs && result.logs.length > 0 ? "warning" : "success",
        width: "500px",
      }).then(() => {
        fetchTableData(true);
      });
    } else {
      throw new Error(result.message);
    }
  } catch (error) {
    Swal.fire("Gagal Import", error.message, "error");
  } finally {
    inpFileImport.value = "";
  }
}
document.addEventListener("DOMContentLoaded", async () => {
  await loadStoreOptions();
  fetchTableData(true);
  setupInfinityScroll();
  inpNoFaktur.addEventListener("change", (e) => {
    const val = e.target.value.trim();
    if (val) fetchFakturData(val);
  });
  inpNamaSupp.addEventListener("input", handleSupplierSearch);
  btnSave.addEventListener("click", handleSave);
  btnCancelEdit.addEventListener("click", cancelEditMode);
  inpNoFaktur.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      fetchFakturData(e.target.value.trim());
    }
  });
  if (inpSearchTable) {
    inpSearchTable.addEventListener("input", (e) => {
      clearTimeout(searchDebounceTimer);
      searchDebounceTimer = setTimeout(() => {
        currentSearchTerm = e.target.value;
        fetchTableData(true);
      }, 600);
    });
  }
  if (btnExport) btnExport.addEventListener("click", handleExport);
  if (btnImport) {
    btnImport.addEventListener("click", () => {
      Swal.fire({
        title: "Import Buku Besar",
        html: `
        <div class="text-left text-sm text-gray-600 mb-4">
            <p class="mb-2">Gunakan format Excel (.xlsx) dengan urutan kolom:</p>
            <div class="overflow-x-auto">
                <table class="w-full text-xs border border-gray-200">
                    <tr class="bg-gray-100 font-bold text-center">
                        <td class="p-1 border">A</td><td class="p-1 border">B</td><td class="p-1 border">C</td><td class="p-1 border">D</td>
                        <td class="p-1 border">E</td><td class="p-1 border">F</td><td class="p-1 border">G</td><td class="p-1 border">H</td>
                        <td class="p-1 border">I</td><td class="p-1 border">J</td><td class="p-1 border">K</td><td class="p-1 border">L</td>
                        <td class="p-1 border">M</td><td class="p-1 border">N</td> </tr>
                    <tr>
                        <td class="p-1 border">Tgl Bayar</td>
                        <td class="p-1 border">Tgl Nota</td>
                        <td class="p-1 border">Faktur</td>
                        <td class="p-1 border">Kd Supp</td>
                        <td class="p-1 border">Nm Supp</td>
                        <td class="p-1 border">Cab Inv</td>
                        <td class="p-1 border">Cab Bayar</td>
                        <td class="p-1 border">Nilai</td>
                        <td class="p-1 border">Pot</td>
                        <td class="p-1 border">Ket Pot</td>
                        <td class="p-1 border">Total</td>
                        <td class="p-1 border">MOP</td> 
                        <td class="p-1 border bg-yellow-50">Status</td> <td class="p-1 border bg-yellow-50">TOP</td>    
                    </tr>
                </table>
            </div>
            <p class="mt-2 text-xs italic">*Kolom M (Status) isi: PKP / NON PKP.</p>
            <p class="mt-1 text-xs italic">*Kolom L (MOP) isi: CASH / TRANSFER.</p>
        </div>
        `,
        icon: "info",
        showCancelButton: true,
        confirmButtonText: "Pilih File",
        confirmButtonColor: "#3b82f6",
        width: '600px'
      }).then((result) => {
        if (result.isConfirmed) {
          inpFileImport.click();
        }
      });
    });
  }
  if (inpFileImport) inpFileImport.addEventListener("change", handleImport);
  btnAddItem.addEventListener("click", () => {
    const noFaktur = inpNoFaktur.value.trim();
    const kodeStore = inpKodeStore.value;
    const valTop = inpTop.value;
    const valStatus = inpStatus.value;
    const tglNota = inpTglNota.value;
    const nilai = parseNumber(inpNilaiFaktur.value);
    const potongan = parseNumber(inpPotongan.value);
    const manualTotal = parseNumber(inpGlobalTotal.value);
    const totalItem = manualTotal; 
    if (!noFaktur) return Swal.fire("Validasi Gagal", "Nomor Invoice / Faktur wajib diisi!", "warning");
    if (!kodeStore) return Swal.fire("Validasi Gagal", "Cabang (Inv) wajib dipilih!", "warning");
    if (!tglNota) return Swal.fire("Validasi Gagal", "Tanggal Nota wajib diisi!", "warning");
    if (!valStatus) return Swal.fire("Validasi Gagal", "Status Pajak wajib dipilih!", "warning");
    if (nilai <= 0) return Swal.fire("Validasi Gagal", "Nilai Faktur wajib diisi (lebih dari 0)!", "warning");
    let historyBayar = 0;
    if (installmentInfoBox && !installmentInfoBox.classList.contains("hidden")) {
      historyBayar = parseNumber(infoSudahBayar.textContent);
    }
    const itemData = {
      no_faktur: noFaktur,
      kode_store: kodeStore,
      nm_store_display: inpKodeStore.options[inpKodeStore.selectedIndex].text,
      tgl_nota: inpTglNota.value,
      nilai_faktur: nilai,
      potongan: potongan,
      ket_potongan: inpKetPotongan.value,
      top: valTop,
      status: valStatus,
      total_bayar: totalItem,
      sudah_bayar_history: historyBayar,
      details_potongan: JSON.parse(JSON.stringify(tempPotonganList)),
      details_tambahan: JSON.parse(JSON.stringify(tempTambahanList)), 
      ket_tambahan: inpKetTambahan.value, 
      ket: inpKetGlobal.value,
      nilai_tambahan: parseNumber(inpNilaiTambahan.value),
      mop: inpKetGlobal.value, 
    };
    if (editingCartIndex >= 0) {
      if (cartItems[editingCartIndex].id) {
        itemData.id = cartItems[editingCartIndex].id;
      }
      if (cartItems[editingCartIndex].group_id) {
        itemData.group_id = cartItems[editingCartIndex].group_id;
      }
      cartItems[editingCartIndex] = itemData;
      if (manualTotal <= 0) {
        Toastify({ text: "Item diperbarui", duration: 1000, style: { background: "#f59e0b" } }).showToast();
      }
    } else {
      const exists = cartItems.find(item => item.no_faktur === noFaktur && item.kode_store === kodeStore);
      if (exists) return Swal.fire("Double", "Faktur ini sudah ada di daftar", "error");
      cartItems.push(itemData);
    }
    if (inpGlobalTotal.value.trim() !== "") {
      cartItems.forEach(item => {
        item.total_bayar = manualTotal;
      });
    }
    renderCart();
    resetItemForm();
  });
  btnSaveBatch.addEventListener("click", async () => {
    if (editingCartIndex !== -1) {
      const liveNilaiFaktur = parseNumber(inpNilaiFaktur.value);
      const livePotongan = parseNumber(inpPotongan.value);
      const liveKetPotongan = inpKetPotongan.value;
      const liveTop = inpTop.value;
      const liveStatus = inpStatus.value;
      cartItems[editingCartIndex].nilai_faktur = liveNilaiFaktur;
      cartItems[editingCartIndex].potongan = livePotongan;
      cartItems[editingCartIndex].ket_potongan = liveKetPotongan;
      cartItems[editingCartIndex].ket_tambahan = inpKetTambahan.value;
      cartItems[editingCartIndex].top = liveTop;
      cartItems[editingCartIndex].status = liveStatus;
      cartItems[editingCartIndex].details_potongan = JSON.parse(JSON.stringify(tempPotonganList));
      cartItems[editingCartIndex].details_tambahan = JSON.parse(JSON.stringify(tempTambahanList));
      renderCart();
    }
    const namaSupp = inpNamaSupp.value.trim();
    const kodeSupp = inpKodeSupplier.value.trim();
    const storeBayar = inpStoreBayar.value.trim();
    const tglBayar = inpTglBayar.value;
    const statusPajak = inpStatus.value;
    const mop = inpKetGlobal.value;
    const globalInputVal = parseNumber(inpGlobalTotal.value);
    const isGlobalFilled = inpGlobalTotal.value.trim() !== "";
    if (!namaSupp) return Swal.fire("Validasi Gagal", "Nama Supplier wajib diisi!", "warning");
    const totalTagihan = cartItems.reduce((acc, item) => {
      const nilai = parseNumber(item.nilai_faktur);
      const pot = parseNumber(item.potongan);
      const tambahan = parseNumber(item.nilai_tambahan || 0); 
      const history = parseNumber(item.sudah_bayar_history) || 0;
      let sisaTagihanItem = (nilai + tambahan - pot) - history;
      if (sisaTagihanItem < 0) sisaTagihanItem = 0;
      return acc + sisaTagihanItem;
    }, 0);
    let finalDetails = [];
    let totalRencanaBayar = 0;
    finalDetails = cartItems.map(item => {
        return {
            ...item,
            ket: mop, 
            mop: mop, 
            total_bayar: isGlobalFilled ? globalInputVal : item.total_bayar
        };
    });
    if (isGlobalFilled) {
        totalRencanaBayar = globalInputVal; 
    } else {
        totalRencanaBayar = finalDetails.reduce((acc, item) => acc + parseNumber(item.total_bayar), 0);
    }
    const isInstallmentMode = !installmentInfoBox.classList.contains("hidden") || (currentGroupId != null);
    let swalOptions = {
      title: 'Simpan Transaksi?',
      text: `Total ${finalDetails.length} faktur. Total Bayar: ${formatNumber(totalRencanaBayar)}.`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Simpan',
      confirmButtonColor: '#db2777'
    };
    if (totalRencanaBayar === 0) {
      if (totalTagihan <= 100) { 
         swalOptions = {
            title: 'Simpan Perubahan Data?',
            html: `Status Faktur: <b>LUNAS</b>.<br>Anda tidak menginput pembayaran baru.<br>Hanya data (Nama/Ket/Potongan/Status) yang akan diupdate.`,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ya, Update Data',
            confirmButtonColor: '#10b981' 
         };
      } else {
         swalOptions = {
            title: 'Simpan Perubahan Data?',
            html: ``,
            icon: 'warning', 
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            confirmButtonColor: '#3b82f6'
         };
      }
    }
    else if (!isInstallmentMode && Math.abs(totalRencanaBayar - totalTagihan) > 100) {
      const selisih = totalRencanaBayar - totalTagihan;
      const textSelisih = formatNumber(Math.abs(selisih));
      if (selisih < 0) {
        swalOptions = {
          title: 'Konfirmasi Angsuran',
          html: `
                    <div class="text-left text-sm bg-blue-50 p-4 rounded border border-blue-200">
                        <p class="font-bold text-blue-800 mb-2">Pembayaran Sebagian</p>
                        <table class="w-full">
                            <tr><td>Total Tagihan</td><td class="text-right font-bold">${formatNumber(totalTagihan)}</td></tr>
                            <tr><td>Yang Dibayar</td><td class="text-right font-bold text-blue-600">${formatNumber(totalRencanaBayar)}</td></tr>
                            <tr class="border-t border-blue-200 text-red-600 font-bold">
                                <td>Sisa Hutang</td><td class="text-right">${textSelisih}</td>
                            </tr>
                        </table>
                        <p class="mt-2 text-xs text-gray-500">*Nilai bayar diseragamkan per item.</p>
                    </div>
                `,
          icon: 'info',
          showCancelButton: true,
          confirmButtonText: 'Ya, Proses',
          confirmButtonColor: '#3b82f6'
        };
      } else {
        swalOptions = {
          title: '⚠️ Kelebihan Bayar',
          html: `Anda membayar <b>${formatNumber(totalRencanaBayar)}</b> untuk tagihan <b>${formatNumber(totalTagihan)}</b>.`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Tetap Simpan',
          confirmButtonColor: '#d33'
        };
      }
    }
    const confirm = await Swal.fire(swalOptions);
    if (!confirm.isConfirmed) return;
    btnSaveBatch.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Proses...`;
    btnSaveBatch.disabled = true;
    const payload = {
      header: {
        nama_supplier: namaSupp,
        kode_supplier: kodeSupp,
        store_bayar: storeBayar,
        tanggal_bayar: tglBayar,
        ket: inpKetGlobal.value,
        group_id: currentGroupId
      },
      details: finalDetails,
      deleted_ids: deletedCartIds
    };
    try {
      const result = await sendRequestJSON(API_URLS.saveData, payload);
      if (result.success) {
        Swal.fire("Berhasil", result.message, "success");
        cartItems = [];
        deletedCartIds = [];
        currentGroupId = null;
        cancelEditMode();
        fetchTableData(true);
      } else {
        throw new Error(result.message);
      }
    } catch (error) {
      Swal.fire("Gagal", error.message, "error");
    } finally {
      btnSaveBatch.innerHTML = `<i class="fas fa-save fa-lg"></i> <span>SIMPAN TRANSAKSI</span>`;
      if (cartItems.length > 0) btnSaveBatch.disabled = false;
    }
  });
  inpNilaiFaktur.addEventListener("input", () => {
    calculateSummary();
  });
  inpNilaiFaktur.addEventListener("blur", () => {
    inpNilaiFaktur.value = formatNumber(parseNumber(inpNilaiFaktur.value));
    calculateSummary();
  });
  inpPotongan.addEventListener("input", () => {
    calculateSummary();
  });
  inpPotongan.addEventListener("blur", () => {
    inpPotongan.value = formatNumber(parseNumber(inpPotongan.value));
    calculateSummary();
  });
  if (inpGlobalTotal) {
    inpGlobalTotal.addEventListener("input", () => {
      calculateSummary();
    });
    inpGlobalTotal.addEventListener("blur", (e) => {
      e.target.value = formatNumber(parseNumber(e.target.value));
      calculateSummary();
    });
    inpGlobalTotal.addEventListener("focus", (e) => e.target.select());
  }
  btnManagePotongan.addEventListener("click", () => {
    renderModalPotonganRows();
    modalPotongan.classList.remove("hidden");
  });
  btnAddRowPotongan.addEventListener("click", () => {
    tempPotonganList.push({ nominal: 0, keterangan: "" });
    renderModalPotonganRows();
  });
  btnSaveModalPotongan.addEventListener("click", () => {
    let total = 0;
    let kets = [];
    tempPotonganList.forEach(item => {
      total += parseFloat(item.nominal);
      if (item.keterangan) kets.push(item.keterangan);
    });
    inpPotongan.value = formatNumber(total);
    inpKetPotongan.value = kets.join(", ");
    calculateSummary();
    modalPotongan.classList.add("hidden");
  });
  btnManageTambahan.addEventListener("click", () => {
        renderModalTambahanRows();
        modalTambahan.classList.remove("hidden");
    });
    btnAddRowTambahan.addEventListener("click", () => {
        tempTambahanList.push({ nominal: 0, keterangan: "" });
        renderModalTambahanRows();
    });
    btnSaveModalTambahan.addEventListener("click", () => {
        let total = 0;
        let kets = [];
        tempTambahanList.forEach(item => {
            total += parseFloat(item.nominal);
            if (item.keterangan) kets.push(item.keterangan);
        });
        inpNilaiTambahan.value = formatNumber(total);
        inpKetTambahan.value = kets.join(", ");
        calculateSummary();
        modalTambahan.classList.add("hidden");
    });
});
window.addEventListener('beforeunload', (e) => {
  if (isSubmitting) return undefined;
  if (isFormDirty()) {
    e.preventDefault();
    e.returnValue = '';
    return '';
  }
});
window.editCartItem = async (index) => {
    const item = cartItems[index];
    editingCartIndex = index;
    if (installmentInfoBox) {
        installmentInfoBox.classList.add("hidden");
        if (infoSudahBayar) {
            infoSudahBayar.textContent = "0";
            delete infoSudahBayar.dataset.original;
            infoSudahBayar.parentElement.style.display = "block";
        }
        if (infoSisaHutang) {
            infoSisaHutang.textContent = "0";
            infoSisaHutang.parentElement.style.display = "block";
        }
    }
    currentHistoryData = [];
    inpNoFaktur.classList.remove("bg-blue-50", "text-blue-700", "font-bold");
    inpNilaiFaktur.readOnly = false;
    inpNilaiFaktur.classList.remove('bg-gray-100', 'cursor-not-allowed');
    inpNoFaktur.value = item.no_faktur;
    if (item.kode_store) {
        const exists = [...inpKodeStore.options].some(o => o.value == item.kode_store);
        if (!exists) {
            const label = item.nm_alias || item.nm_store_display || item.kode_store;
            const newOpt = new Option(label, item.kode_store, true, true);
            inpKodeStore.add(newOpt);
        }
        inpKodeStore.value = item.kode_store;
    }
    inpTop.value = item.top || "";
    inpStatus.value = item.status || "";
    inpTglNota.value = item.tgl_nota || "";
    inpNilaiFaktur.value = formatNumber(item.nilai_faktur);
    inpPotongan.value = formatNumber(item.potongan);
    inpKetPotongan.value = item.ket_potongan || "";
    if (inpNilaiTambahan) {
        inpNilaiTambahan.value = formatNumber(item.nilai_tambahan || 0);
    }
    if (item.tanggal_bayar) {
        inpTglBayar.value = item.tanggal_bayar;
    }
    inpKetGlobal.value = item.ket || "";
    btnAddItem.innerHTML = `<i class="fas fa-sync-alt mr-1"></i> Update Item`;
    btnAddItem.classList.remove("bg-blue-600", "hover:bg-blue-700");
    btnAddItem.classList.add("bg-yellow-500", "hover:bg-amber-600");
    if (item.details_potongan && Array.isArray(item.details_potongan) && item.details_potongan.length > 0) {
        tempPotonganList = JSON.parse(JSON.stringify(item.details_potongan));
    } else {
        if (item.potongan > 0) {
            tempPotonganList = [{ nominal: item.potongan, keterangan: item.ket_potongan || "Potongan Manual" }];
        } else {
            tempPotonganList = [];
        }
    }
    if (item.details_tambahan && Array.isArray(item.details_tambahan) && item.details_tambahan.length > 0) {
        tempTambahanList = JSON.parse(JSON.stringify(item.details_tambahan));
    } else {
        if (item.nilai_tambahan > 0) {
            tempTambahanList = [{ nominal: item.nilai_tambahan, keterangan: item.ket_tambahan || "Tambahan Manual" }];
        } else {
            tempTambahanList = [];
        }
    }
    inpKetTambahan.value = item.ket_tambahan || "";
    try {
        const currentStore = item.kode_store;
        const url = `${API_URLS.getFakturDetail}?no_faktur=${encodeURIComponent(item.no_faktur)}&kode_store=${encodeURIComponent(currentStore)}`;
        const result = await sendRequestGET(url);
        if (result.success && result.found && result.source === 'buku_besar') {
            const d = result.data;
            const isGroupMode = !!d.group_totals;
            const isMultiItemGroup = isGroupMode && cartItems.length > 1;
            if (installmentInfoBox) {
                installmentInfoBox.classList.remove("hidden");
                if (isMultiItemGroup) {
                    if (infoSudahBayar) infoSudahBayar.parentElement.style.display = "none";
                    if (infoSisaHutang) infoSisaHutang.parentElement.style.display = "none";
                } else {
                    if (infoSudahBayar) infoSudahBayar.parentElement.style.display = "block";
                    if (infoSisaHutang) infoSisaHutang.parentElement.style.display = "block";
                    let domFaktur = parseNumber(inpNilaiFaktur.value);
                    let domTambahan = parseNumber(inpNilaiTambahan.value);
                    let domPotongan = parseNumber(inpPotongan.value);
                    let totalTagihanBase = domFaktur + domTambahan - domPotongan;
                    let realHistory = parseFloat(item.sudah_bayar_history || 0);
                    const sisaHutang = totalTagihanBase - realHistory;
                    if (sisaHutang <= 100) {
                        inpGlobalTotal.disabled = true;
                        inpGlobalTotal.classList.add("bg-gray-100", "cursor-not-allowed");
                        inpGlobalTotal.placeholder = "LUNAS";
                    } else {
                        inpGlobalTotal.disabled = false;
                        inpGlobalTotal.classList.remove("bg-gray-100", "cursor-not-allowed");
                    }
                    if (infoSudahBayar) {
                        let labelBayar = formatNumber(realHistory);
                        infoSudahBayar.innerHTML = labelBayar;
                        infoSudahBayar.dataset.original = realHistory;
                    }
                    if (infoSisaHutang) {
                        infoSisaHutang.textContent = formatNumber(sisaHutang);
                        if (sisaHutang <= 100) {
                            infoSisaHutang.classList.remove('text-red-600');
                            infoSisaHutang.classList.add('text-green-600');
                        } else {
                            infoSisaHutang.classList.remove('text-green-600');
                            infoSisaHutang.classList.add('text-red-600');
                        }
                    }
                }
                try {
                    if (d.id) {
                        const histResp = await fetch(`${API_URLS.getHistory}?buku_besar_id=${d.id}`);
                        const histJson = await histResp.json();
                        if (histJson.success && Array.isArray(histJson.data)) {
                            currentHistoryData = histJson.data.filter(h => h.id != item.id);
                        }
                    }
                } catch (e) { console.error(e); }
            }
            inpNoFaktur.classList.add("bg-blue-50", "text-blue-700", "font-bold");
        } 
    } catch (error) {
        console.error("Error checking installment status on edit:", error);
    }
    renderCart();
    calculateSummary();
};
window.removeCartItem = (index) => {
  const item = cartItems[index];
  if (item.id) {
    deletedCartIds.push(item.id);
  }
  cartItems.splice(index, 1);
  if (index === editingCartIndex) {
    resetItemForm();
  } else if (index < editingCartIndex) {
    editingCartIndex--;
  }
  renderCart();
};
function calculateSummary() {
    let totalNilaiFaktur = 0;
    let totalPotongan = 0;
    let totalTambahan = 0;
    let groupHistoryMap = {}; 
    let nonGroupHistoryTotal = 0;
    cartItems.forEach((item, index) => {
        let nilai, pot, tambahan, history;
        if (index === editingCartIndex) {
            nilai = parseNumber(inpNilaiFaktur.value);
            pot = parseNumber(inpPotongan.value);
            tambahan = parseNumber(inpNilaiTambahan.value);
            let historyFromDom = 0;
            if (installmentInfoBox && !installmentInfoBox.classList.contains("hidden")) {
                if (infoSudahBayar.dataset.original) {
                    historyFromDom = parseFloat(infoSudahBayar.dataset.original);
                } else {
                    historyFromDom = parseNumber(infoSudahBayar.textContent);
                }
            }
            if (historyFromDom === 0 && item.sudah_bayar_history > 0) {
                history = parseFloat(item.sudah_bayar_history);
            } else {
                history = historyFromDom;
            }
        } else {
            nilai = parseNumber(item.nilai_faktur);
            pot = parseNumber(item.potongan);
            tambahan = parseNumber(item.nilai_tambahan || 0);
            history = parseNumber(item.sudah_bayar_history) || 0;
        }
        totalNilaiFaktur += nilai;
        totalPotongan += pot;
        totalTambahan += tambahan;
        if (item.group_id) {
            groupHistoryMap[item.group_id] = history;
        } else {
            nonGroupHistoryTotal += history;
        }
    });
    let totalGroupHistory = 0;
    for (let groupId in groupHistoryMap) {
        totalGroupHistory += groupHistoryMap[groupId];
    }
    let totalHistoryValid = nonGroupHistoryTotal + totalGroupHistory;
    let grandTotalTagihan = (totalNilaiFaktur + totalTambahan - totalPotongan) - totalHistoryValid;
    if (grandTotalTagihan < 0) grandTotalTagihan = 0;
    const currentTotalBayar = parseNumber(inpGlobalTotal.value);
    const selisih = grandTotalTagihan - currentTotalBayar;
    if (lblTotalTagihan) lblTotalTagihan.textContent = formatNumber(grandTotalTagihan);
    if (cartItems.length > 0) {
        if (summaryPaymentDetails) summaryPaymentDetails.classList.remove("hidden");
        if (lblSummaryBayar) lblSummaryBayar.textContent = formatNumber(currentTotalBayar);
        if (lblSummarySelisih) {
            lblSummarySelisih.textContent = formatNumber(selisih);
            if (selisih === 0) {
                lblSummarySelisihContainer.className = "font-mono ml-1 text-green-600";
                lblSummarySelisih.textContent = "0 (Lunas)";
            } else if (selisih > 0) {
                lblSummarySelisihContainer.className = "font-mono ml-1 text-red-600"; 
            } else {
                lblSummarySelisihContainer.className = "font-mono ml-1 text-amber-600"; 
            }
        }
    } else {
        if (summaryPaymentDetails) summaryPaymentDetails.classList.add("hidden");
    }
    if (installmentInfoBox && !installmentInfoBox.classList.contains("hidden")) {
        const currentNilaiFaktur = parseNumber(inpNilaiFaktur.value);
        const currentPotongan = parseNumber(inpPotongan.value);
        const currentTambahan = parseNumber(inpNilaiTambahan.value); 
        let historyBayarExisting = 0;
        if (infoSudahBayar.dataset.original) {
            historyBayarExisting = parseFloat(infoSudahBayar.dataset.original);
        } else {
            historyBayarExisting = parseNumber(infoSudahBayar.textContent);
        }
        let sisaHutangRealtime = (currentNilaiFaktur + currentTambahan - currentPotongan) - historyBayarExisting;
        if (infoSisaHutang) {
            infoSisaHutang.textContent = formatNumber(sisaHutangRealtime);
            if (sisaHutangRealtime <= 100) {
                infoSisaHutang.classList.remove('text-red-600');
                infoSisaHutang.classList.add('text-green-600');
            } else {
                infoSisaHutang.classList.remove('text-green-600');
                infoSisaHutang.classList.add('text-red-600');
            }
        }
    }
}
function renderModalPotonganRows() {
  containerListPotongan.innerHTML = "";
  let total = 0;
  if (tempPotonganList.length === 0) {
    tempPotonganList.push({ nominal: 0, keterangan: "" });
  }
  tempPotonganList.forEach((item, index) => {
    total += parseFloat(item.nominal);
    const div = document.createElement("div");
    div.className = "flex gap-2 items-center mb-2";
    div.innerHTML = `
            <input type="text" class="input-compact text-sm" placeholder="Keterangan..." value="${item.keterangan}" onchange="updatePotonganItem(${index}, 'keterangan', this.value)">
            <input type="text" class="input-compact w-32 text-right font-mono text-sm" placeholder="0" value="${item.nominal}" onchange="updatePotonganItem(${index}, 'nominal', this.value)" onfocus="this.select()">
            <button type="button" class="text-red-500 hover:text-red-700 ml-1" onclick="removePotonganItem(${index})"><i class="fas fa-times"></i></button>
        `;
    containerListPotongan.appendChild(div);
  });
  lblTotalModalPotongan.innerText = formatNumber(total);
}
window.updatePotonganItem = (index, field, value) => {
  if (field === 'nominal') {
    tempPotonganList[index].nominal = parseNumber(value);
  } else {
    tempPotonganList[index].keterangan = value;
  }
  let total = tempPotonganList.reduce((acc, curr) => acc + curr.nominal, 0);
  lblTotalModalPotongan.innerText = formatNumber(total);
};
window.removePotonganItem = (index) => {
  tempPotonganList.splice(index, 1);
  renderModalPotonganRows();
};
function renderModalTambahanRows() {
    containerListTambahan.innerHTML = "";
    let total = 0;
    if (tempTambahanList.length === 0) {
        tempTambahanList.push({ nominal: 0, keterangan: "" });
    }
    tempTambahanList.forEach((item, index) => {
        total += parseFloat(item.nominal);
        const div = document.createElement("div");
        div.className = "flex gap-2 items-center mb-2";
        div.innerHTML = `
            <input type="text" class="input-compact text-sm" placeholder="Keterangan..." value="${item.keterangan}" onchange="updateTambahanItem(${index}, 'keterangan', this.value)">
            <input type="text" class="input-compact w-32 text-right font-mono text-sm text-green-600 font-bold" placeholder="0" value="${item.nominal}" onchange="updateTambahanItem(${index}, 'nominal', this.value)" onfocus="this.select()">
            <button type="button" class="text-red-500 hover:text-red-700 ml-1" onclick="removeTambahanItem(${index})"><i class="fas fa-times"></i></button>
        `;
        containerListTambahan.appendChild(div);
    });
    lblTotalModalTambahan.innerText = formatNumber(total);
}
window.updateTambahanItem = (index, field, value) => {
    if (field === 'nominal') {
        tempTambahanList[index].nominal = parseNumber(value);
    } else {
        tempTambahanList[index].keterangan = value;
    }
    let total = tempTambahanList.reduce((acc, curr) => acc + curr.nominal, 0);
    lblTotalModalTambahan.innerText = formatNumber(total);
};
window.removeTambahanItem = (index) => {
    tempTambahanList.splice(index, 1);
    renderModalTambahanRows();
};

window.showSummaryModal = (title, detailsArray, noFaktur, colorClass = 'text-gray-800') => {
    const formatRupiah = (number) => {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(number);
    };

    let html = `
        <div class="mb-3">
             <span class="text-xs text-gray-500 uppercase font-bold tracking-wider">No Faktur:</span>
             <span class="font-bold text-gray-800 ml-1">${noFaktur}</span>
        </div>
        <div class="overflow-hidden rounded-lg border border-gray-200">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3">Keterangan Item</th>
                        <th class="px-4 py-3 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
    `;

    let total = 0;
    if (!detailsArray || detailsArray.length === 0) {
        html += `<tr><td colspan="2" class="text-center p-4 italic text-gray-400">Tidak ada rincian item.</td></tr>`;
    } else {
        detailsArray.forEach(row => {
            const nominal = parseFloat(row.nominal);
            total += nominal;
            html += `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 align-top">
                        <div class="text-gray-900 font-medium">${row.keterangan || '-'}</div>
                    </td>
                    <td class="px-4 py-3 text-right font-mono font-bold ${colorClass} align-top">
                        ${formatRupiah(nominal)}
                    </td>
                </tr>
            `;
        });
    }

    html += `
                </tbody>
                <tfoot class="bg-gray-50 font-bold text-gray-900">
                    <tr>
                        <td class="px-4 py-3 text-right uppercase text-xs tracking-wider">Total</td>
                        <td class="px-4 py-3 text-right ${colorClass} text-base border-t border-gray-200">${formatRupiah(total)}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;

    // Dispatch event ke Alpine.js
    window.dispatchEvent(new CustomEvent("show-detail-modal", {
        detail: {
            show: true,
            title: title,
            content: html,
        },
    }));
};

