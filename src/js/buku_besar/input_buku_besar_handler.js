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
const inpKet = document.getElementById("inp_ket");
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
    inpNilaiFaktur.value = "0";
    inpPotongan.value = "0";
    inpKetPotongan.value = "";
    editingCartIndex = -1;
    btnAddItem.innerHTML = `<i class="fas fa-arrow-down mr-1"></i> Tambah ke Daftar`;
    btnAddItem.classList.remove("bg-yellow-500", "hover:bg-amber-600");
    btnAddItem.classList.add("bg-blue-600", "hover:bg-blue-700");
    document.querySelectorAll("#temp-list-body tr").forEach(tr => tr.classList.remove("bg-amber-50"));
    inpNoFaktur.focus();
}
function parseNumber(str) {
  if (!str) return 0;
  const cleanStr = str.toString().replace(/\./g, "").replace(",", ".");
  return parseFloat(cleanStr) || 0;
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
            <td colspan="9" class="text-center p-8">
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
        tableBody.innerHTML = `<tr><td colspan="9" class="text-center p-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">Data tidak ditemukan</td></tr>`;
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
      tableBody.innerHTML = `<tr><td colspan="9" class="text-center p-4 text-red-500">Terjadi kesalahan koneksi</td></tr>`;
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
        tempListBody.innerHTML = `<tr><td colspan="6" class="text-center p-10 text-gray-400">Belum ada data</td></tr>`;
        btnSaveBatch.disabled = true;
        btnSaveBatch.classList.add("opacity-50", "cursor-not-allowed");
        lblCountItem.textContent = "0";
        btnSave.style.display = "";
        return;


        
    }
    btnSave.style.display = "none";
    let html = "";
    let grandTotal = 0; 
    cartItems.forEach((item, index) => {
        grandTotal += parseFloat(item.total_bayar);
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
                <td class="p-2 text-right text-red-500">${item.potongan > 0 ? formatNumber(item.potongan) : '-'}</td>
                <td class="p-2 text-right font-bold text-gray-800">${formatNumber(item.total_bayar)}</td>
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
}
function renderTableRows(data) {
    for (let i = 0; i < data.length; i++) {
        const row = data[i];
        tableRowIndex++; 
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
        if (isGroupStart) {
            tr.classList.add("row-group-start"); 
        }
        const potongan = parseFloat(row.potongan || 0);
        const nilaiFaktur = parseFloat(row.nilai_faktur || 0);
        const total = parseFloat(row.total_bayar || 0);
        const storeBayarDisplay = row.nm_alias_bayar || row.store_bayar || "-";
        let html = '';
        html += `<td class="text-center text-gray-500 py-3 align-top">${tableRowIndex}</td>`;
        if (isGroupStart) {
            html += `<td class="text-sm cell-merged font-medium" rowspan="${rowSpan}">${row.tanggal_bayar || "-"}</td>`;
        } else if (!currentGroup) {
            html += `<td class="text-sm align-top">${row.tanggal_bayar || "-"}</td>`;
        }
        html += `<td class="text-sm align-top text-gray-600">${row.tgl_nota || "-"}</td>`;
        html += `<td class="font-medium text-gray-800 text-sm align-top">${row.no_faktur}</td>`;
        if (isGroupStart) {
            html += `<td class="text-sm cell-merged" rowspan="${rowSpan}">${row.nama_supplier}</td>`;
        } else if (!currentGroup) {
            html += `<td class="text-sm align-top">${row.nama_supplier}</td>`;
        }
        html += `<td class="align-top"><span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded border border-gray-200">${row.nm_alias || row.kode_store}</span></td>`;
        if (isGroupStart) {
             html += `<td class="cell-merged" rowspan="${rowSpan}"><span class="bg-pink-50 text-pink-700 text-xs px-2 py-1 rounded border border-pink-100 font-bold">${storeBayarDisplay}</span></td>`;
        } else if (!currentGroup) {
             html += `<td class="align-top"><span class="bg-pink-50 text-pink-700 text-xs px-2 py-1 rounded border border-pink-100 font-bold">${storeBayarDisplay}</span></td>`;
        }
        html += `
            <td class="text-sm text-gray-500 italic max-w-xs truncate align-top" title="${row.ket || ''}">${row.ket || "-"}</td>
            <td class="text-right font-mono text-sm text-red-500 align-top">${formatNumber(nilaiFaktur)}</td>
            <td class="text-right font-mono text-sm align-top">
                <div class="text-red-500">${formatNumber(potongan)}</div>
                ${row.ket_potongan ? `<div class="text-[10px] text-gray-400 italic leading-tight mt-1" title="Ket: ${row.ket_potongan}">${row.ket_potongan}</div>` : ''}
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
      inpStoreBayar.innerHTML = html;
    }
  } catch (error) {
    console.error("Gagal memuat toko:", error);
  }
}
async function fetchFakturData(noFaktur) {
  if (!noFaktur) return;
  const originalPlaceholder = inpNoFaktur.placeholder;
  inpNoFaktur.placeholder = "Mencari...";
  inpNoFaktur.classList.add("bg-yellow-50");
  try {
    const result = await sendRequestGET(
      `${API_URLS.getFakturDetail}?no_faktur=${encodeURIComponent(noFaktur)}`
    );
    if (result.success && result.found && result.data) {
      const d = result.data;
      if (d.no_faktur) {
        inpNoFaktur.value = d.no_faktur;
      }
      if(d.kode_store) inpKodeStore.value = d.kode_store;
      inpKodeSupplier.value = d.kode_supplier || "";
      inpNamaSupp.value = d.nama_supplier || "";
      inpTglNota.value = d.tgl_nota || "";
      inpNilaiFaktur.value = formatNumber(parseFloat(d.total_bayar) || 0);
       inpPotongan.value = "0"; 
       inpNoFaktur.classList.remove("bg-yellow-50");
      inpNoFaktur.classList.add("bg-green-50", "text-green-700");
      let msgText = "✅ Data Ditemukan!";
      if(noFaktur !== d.no_faktur) {
         msgText += ` (Invoice ${noFaktur} dikonversi ke Faktur)`;
      }
      Toastify({
        text: msgText,
        duration: 3000,
        style: { background: "#10b981" },
      }).showToast();
      setTimeout(() => inpNoFaktur.classList.remove("bg-green-50", "text-green-700"), 1000);
      inpTglBayar.focus();
    } else {
      inpNoFaktur.classList.remove("bg-yellow-50");
      Toastify({
        text: "ℹ️ Data tidak ditemukan di Pembelian. Silakan input manual.",
        duration: 3000,
        style: { background: "#3b82f6" },
      }).showToast();
      inpNamaSupp.focus();
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
    if (!noFaktur) {
        Swal.fire("Gagal", "Nomor Faktur wajib diisi", "warning");
        return;
    }
    if (inpKodeStore.value === "") {
        Swal.fire("Gagal", "Pilih Cabang", "warning");
        return;
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
        potongan: parseNumber(inpPotongan.value),
        nilai_faktur: parseNumber(inpNilaiFaktur.value),
        ket_potongan: inpKetPotongan.value,
        ket: ketValue, 
        total_bayar: parseNumber(inpGlobalTotal.value)
    };
  const originalBtnContent = btnSave.innerHTML;
  const originalBtnClass = btnSave.className;
  btnSave.disabled = true;
  btnSave.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Proses...`;
  isSubmitting = true;
  try {
    const result = await sendRequestJSON(API_URLS.saveData, payload);
    if (result.success) {
      Swal.fire({ icon: "success", title: "Berhasil", text: result.message, timer: 1000, showConfirmButton: false });
      cancelEditMode();
      fetchTableData(true);
    } else {
      throw new Error(result.message);
    }
  } catch (error) {
    Swal.fire("Gagal Simpan", error.message, "error");
    isSubmitting = false;
  } finally {
    btnSave.disabled = false;
    btnSave.innerHTML = originalBtnContent;
    btnSave.className = originalBtnClass;
    isSubmitting = false;
  }
}
async function startEditMode(data) {
    cancelEditMode(); 
    if (data.group_id) {
        Swal.fire({
            title: "Memuat Group...",
            didOpen: () => Swal.showLoading()
        });
        try {
            const resp = await sendRequestGET(`${API_URLS.getGroupDetails}?group_id=${data.group_id}`);
            Swal.close();
            if (resp.success && resp.data.length > 0) {
                currentGroupId = data.group_id;
                cartItems = resp.data; 
                deletedCartIds = [];
                const firstItem = resp.data[0];
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
        } catch (e) {
            Swal.fire("Error", "Gagal memuat detail group", "error");
        }
    } else {
        currentGroupId = null; 
        cartItems = [data]; 
        deletedCartIds = [];
        inpNamaSupp.value = data.nama_supplier;
        inpKodeSupplier.value = data.kode_supplier;
        inpTglBayar.value = data.tanggal_bayar;
        inpStoreBayar.value = data.store_bayar; 
        inpKetGlobal.value = data.ket;
        editIndicator.classList.remove("hidden");
        editIndicator.innerHTML = `<i class="fas fa-pencil-alt mr-2"></i> MODE EDIT DATA`;
        btnCancelEdit.classList.remove("hidden");
        renderCart();
        window.scrollTo({ top: 0, behavior: "smooth" });
        editCartItem(0);
    }
}
function cancelEditMode() {
    inpId.value = ""; 
    inpKetGlobal.value = "";
    if(inpGlobalTotal) inpGlobalTotal.value = "";
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
    inpTglBayar.value = new Date().toISOString().split('T')[0];
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
      { header: "Keterangan", key: "ket", width: 30 },
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
      let msgHtml = `<div class="text-center mb-3 text-sm">${result.message.replace(/\n/g,"<br>")}</div>`;
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
  inpNoFaktur.addEventListener("change", (e) => {    const val = e.target.value.trim();
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
                    </tr>
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
                        <td class="p-1 border">Ket</td>
                    </tr>
                </table>
            </div>
            <p class="mt-2 text-xs italic">*Kolom F & G diisi <b>Nama Alias</b> (Contoh: ADET, ASOKA).</p>
            <p class="mt-1 text-xs italic">*Jika Tgl Nota/Kode Supplier kosong, biarkan kosong di Excel.</p>
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
    const nilai = parseNumber(inpNilaiFaktur.value);
    const potongan = parseNumber(inpPotongan.value);
    const manualTotal = parseNumber(inpGlobalTotal.value);
    const totalItem = manualTotal > 0 ? manualTotal : (nilai - potongan);
    if (!noFaktur) return Swal.fire("Warning", "Isi No Faktur", "warning");
    if (!kodeStore) return Swal.fire("Warning", "Pilih Cabang Faktur", "warning");
    const itemData = {
        no_faktur: noFaktur,
        kode_store: kodeStore,
        nm_store_display: inpKodeStore.options[inpKodeStore.selectedIndex].text,
        tgl_nota: inpTglNota.value,
        nilai_faktur: nilai,
        potongan: potongan,
        ket_potongan: inpKetPotongan.value,
        total_bayar: totalItem, 
        ket: "" 
    };
    if (editingCartIndex >= 0) {
        if (cartItems[editingCartIndex].id) {
            itemData.id = cartItems[editingCartIndex].id;
        }
        if (cartItems[editingCartIndex].group_id) {
             itemData.group_id = cartItems[editingCartIndex].group_id;
        }
        cartItems[editingCartIndex] = itemData;
        if(manualTotal <= 0) {
            Toastify({ text: "Item diperbarui", duration: 1000, style: { background: "#f59e0b" } }).showToast();
        }
    } else {
        const exists = cartItems.find(item => item.no_faktur === noFaktur && item.kode_store === kodeStore);
        if (exists) return Swal.fire("Double", "Faktur ini sudah ada di daftar", "error");
        cartItems.push(itemData);
        if(manualTotal <= 0) {
            Toastify({ text: "Ditambahkan ke daftar", duration: 1000, style: { background: "#10b981" } }).showToast();
        }
    }
    if (manualTotal > 0) {
        cartItems.forEach(item => {
            item.total_bayar = manualTotal;
        });
        Toastify({ 
            text: `Updated: Semua item diubah menjadi ${formatNumber(manualTotal)}`, 
            duration: 2000, 
            style: { background: "#3b82f6" } 
        }).showToast();
    }
    renderCart();
    resetItemForm();
  });
btnSaveBatch.addEventListener("click", async () => {
    const namaSupp = inpNamaSupp.value.trim();
    const kodeSupp = inpKodeSupplier.value.trim();
    const storeBayar = inpStoreBayar.value;
    const tglBayar = inpTglBayar.value;
    const globalTotalVal = parseNumber(inpGlobalTotal.value);
    if (!namaSupp) return Swal.fire("Gagal", "Isi Nama Supplier", "warning");
    const confirm = await Swal.fire({
        title: 'Simpan Perubahan?',
        text: `Total ${cartItems.length} item akan disimpan.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan',
        confirmButtonColor: '#db2777'
    });
    if (!confirm.isConfirmed) return;
    let finalDetails = cartItems;
    if (globalTotalVal > 0) {
        finalDetails = cartItems.map(item => ({
            ...item,
            total_bayar: globalTotalVal 
        }));
    }
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
    btnSaveBatch.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Proses...`;
    btnSaveBatch.disabled = true;
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
  inpNilaiFaktur.addEventListener("blur", () => {
      inpNilaiFaktur.value = formatNumber(parseNumber(inpNilaiFaktur.value));
  });
  inpPotongan.addEventListener("blur", () => {
      inpPotongan.value = formatNumber(parseNumber(inpPotongan.value));
  });
  if(inpGlobalTotal) {
        inpGlobalTotal.addEventListener("blur", (e) => {
             e.target.value = formatNumber(parseNumber(e.target.value));
        });
        inpGlobalTotal.addEventListener("focus", (e) => e.target.select());
    }
});
window.addEventListener('beforeunload', (e) => {
    if (isSubmitting) return undefined;
    if (isFormDirty()) {
        e.preventDefault();
        e.returnValue = ''; 
        return '';
    }
});
window.editCartItem = (index) => {
    const item = cartItems[index];
    editingCartIndex = index;
    inpNoFaktur.value = item.no_faktur;
    if (item.kode_store) {
        if (![...inpKodeStore.options].some(o => o.value == item.kode_store)) {
            const newOpt = new Option(item.nm_alias || item.kode_store, item.kode_store, true, true);
            inpKodeStore.add(newOpt);
        }
        inpKodeStore.value = item.kode_store;
    }
    inpTglNota.value = item.tgl_nota || "";
    inpNilaiFaktur.value = formatNumber(item.nilai_faktur);
    inpPotongan.value = formatNumber(item.potongan);
    inpKetPotongan.value = item.ket_potongan || "";
    if (inpGlobalTotal) {
        inpGlobalTotal.value = formatNumber(item.total_bayar);
    }
    btnAddItem.innerHTML = `<i class="fas fa-sync-alt mr-1"></i> Update Item`;
    btnAddItem.classList.remove("bg-blue-600", "hover:bg-blue-700");
    btnAddItem.classList.add("bg-yellow-500", "hover:bg-amber-600");
    renderCart();
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