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
};
const form = document.getElementById("single-form");
const inpId = document.getElementById("inp_id");
const inpKodeStore = document.getElementById("inp_kode_store");
const inpStoreBayar = document.getElementById("inp_store_bayar"); // TAMBAHKAN INI
const inpNoFaktur = document.getElementById("inp_no_faktur");
const inpKodeSupplier = document.getElementById("inp_kode_supplier");
const inpNamaSupp = document.getElementById("inp_nama_supplier");
const inpNilaiFaktur = document.getElementById("inp_nilai_faktur");
const inpTglNota = document.getElementById("inp_tgl_nota");
const inpTglBayar = document.getElementById("inp_tgl_bayar");
const inpPotongan = document.getElementById("inp_potongan");
const inpKetPotongan = document.getElementById("inp_ket_potongan");
const inpKet = document.getElementById("inp_ket");
const inpTotalBayar = document.getElementById("inp_total_bayar");
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
let isSubmitting = false;
let debounceTimer;
let searchDebounceTimer;
let currentPage = 1;
let isLoadingData = false;
let hasMoreData = true;
let currentSearchTerm = "";
let tableRowIndex = 0;
let currentRequestController = null; 
function isFormDirty() {
    const hasFaktur = inpNoFaktur.value.trim() !== "";
    const hasSupplier = inpNamaSupp.value.trim() !== "";
    const hasTotal = parseNumber(inpTotalBayar.value) !== 0;
    const hasPotongan = parseNumber(inpPotongan.value) !== 0;
    const hasKet = inpKet.value.trim() !== "";

    return (hasFaktur || hasSupplier || hasTotal || hasPotongan || hasKet);
}
function formatNumber(num) {
  if (isNaN(num) || num === null) return "0";
  return new Intl.NumberFormat("id-ID", {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(num);
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
function renderTableRows(data) {
  data.forEach((row) => {
    tableRowIndex++;
    const tr = document.createElement("tr");
    tr.className = "hover:bg-pink-50 transition-colors border-b border-gray-50";
    
    const potongan = parseFloat(row.potongan || 0);
    const nilaiFaktur = parseFloat(row.nilai_faktur || 0);
    const total = parseFloat(row.total_bayar || 0);
    const storeBayarDisplay = row.nm_alias_bayar || row.store_bayar || "-";

    // Modifikasi template di bawah ini:
    tr.innerHTML = `
        <td class="text-center text-gray-500 py-3">${tableRowIndex}</td>
        <td class="text-sm">${row.tanggal_bayar || "-"}</td>
        <td class="text-sm">${row.tgl_nota || "-"}</td>
        <td class="font-medium text-gray-800 text-sm">${row.no_faktur}</td>
        <td class="text-sm">${row.nama_supplier}</td>
        <td><span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded border border-gray-200">${row.nm_alias || row.kode_store}</span></td>
        <td><span class="bg-pink-50 text-pink-700 text-xs px-2 py-1 rounded border border-pink-100 font-bold">${storeBayarDisplay}</span></td>
        <td class="text-sm text-gray-500 italic max-w-xs truncate" title="${row.ket || ''}">${row.ket || "-"}</td>
        <td class="text-right font-mono text-sm text-red-500">${formatNumber(nilaiFaktur)}</td>
        
        <td class="text-right font-mono text-sm">
            <div class="text-red-500">${formatNumber(potongan)}</div>
            ${row.ket_potongan ? `<div class="text-[10px] text-gray-400 italic leading-tight mt-1" title="Ket: ${row.ket_potongan}">${row.ket_potongan}</div>` : ''}
        </td>

        <td class="text-right font-bold font-mono text-pink-600 text-sm">${formatNumber(total)}</td>
        <td class="text-center py-2">
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
    tr.querySelector(".btn-edit-row").addEventListener("click", () => startEditMode(row));
    tr.querySelector(".btn-delete-row").addEventListener("click", () => handleDelete(row.id));
    tableBody.appendChild(tr);
  });
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
  // if (inpStoreBayar.value === "") { Swal.fire("Gagal", "Pilih Cabang Bayar", "warning"); return; }
  isSubmitting = true;
  const payload = {
    id: inpId.value || null,
    kode_store: inpKodeStore.value,
    store_bayar: inpStoreBayar.value, // TAMBAHKAN INI
    no_faktur: noFaktur,
    kode_supplier: inpKodeSupplier.value,
    nama_supplier: inpNamaSupp.value,
    tgl_nota: inpTglNota.value,
    tanggal_bayar: inpTglBayar.value,
    potongan: parseNumber(inpPotongan.value),
    nilai_faktur: parseNumber(inpNilaiFaktur.value),
    ket_potongan: inpKetPotongan.value,
    ket: inpKet.value,
    total_bayar: parseNumber(inpTotalBayar.value)
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
function startEditMode(data) {
  console.log(data)
  inpId.value = data.id;
  const kodeStoreVal = data.kode_store ? data.kode_store.trim() : "";
  inpKodeStore.value = kodeStoreVal;
  const storeBayarVal = data.store_bayar ? data.store_bayar.trim() : "";
  inpStoreBayar.value = storeBayarVal;
  inpNoFaktur.value = data.no_faktur;
  inpKodeSupplier.value = data.kode_supplier;
  inpNilaiFaktur.value = data.nilai_faktur;
  inpNamaSupp.value = data.nama_supplier;
  inpTglNota.value = data.tgl_nota;
  inpTglBayar.value = data.tanggal_bayar;
  inpPotongan.value = formatNumber(data.potongan);
  inpKetPotongan.value = data.ket_potongan;
  inpKet.value = data.ket;
  inpTotalBayar.value = formatNumber(data.total_bayar);
  editIndicator.classList.remove("hidden");
  btnCancelEdit.classList.remove("hidden");
  btnSave.innerHTML = `<i class="fas fa-sync-alt"></i> <span>Update</span>`;
  btnSave.className = "btn-warning px-6 py-2 rounded shadow-lg bg-amber-500 text-white hover:bg-amber-600";
  window.scrollTo({ top: 0, behavior: "smooth" });
  document.querySelector(".input-row-container").classList.add("border-amber-300", "bg-amber-50");
}
function cancelEditMode() {
  form.reset();
  inpId.value = "";
  editIndicator.classList.add("hidden");
  btnCancelEdit.classList.add("hidden");
  btnSave.innerHTML = `<i class="fas fa-save"></i> <span>Simpan</span>`;
  btnSave.className = "btn-primary shadow-lg shadow-pink-500/30 flex items-center gap-2 px-6 py-2";
  document.querySelector(".input-row-container").classList.remove("border-amber-300", "bg-amber-50");
  inpTglBayar.value = new Date().toISOString().split('T')[0];
  inpTotalBayar.value = "0";
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

    // UPDATE: Kolom diperlengkap
    sheet.columns = [
      { header: "Tgl Bayar", key: "tanggal_bayar", width: 12 },
      { header: "Tgl Nota", key: "tgl_nota", width: 12 },         // BARU
      { header: "No Faktur", key: "no_faktur", width: 20 },
      { header: "Kode Supp", key: "kode_supplier", width: 15 },   // BARU
      { header: "Nama Supplier", key: "nama_supplier", width: 30 },
      { header: "Cabang Inv", key: "nm_alias", width: 15 },
      { header: "Cabang Bayar", key: "nm_alias_bayar", width: 15 },
      { header: "Nilai Faktur", key: "nilai_faktur", width: 15 }, // BARU
      { header: "Potongan", key: "potongan", width: 15 },
      { header: "Ket Potongan", key: "ket_potongan", width: 20 }, // BARU
      { header: "Total Bayar", key: "total_bayar", width: 15 },
      { header: "Keterangan", key: "ket", width: 30 },
    ];

    // Styling Header
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

    // Isi Data
    data.forEach((row) => {
      sheet.addRow({
        tanggal_bayar: row.tanggal_bayar,
        tgl_nota: row.tgl_nota,          // BARU
        no_faktur: row.no_faktur,
        kode_supplier: row.kode_supplier, // BARU
        nama_supplier: row.nama_supplier,
        nm_alias: row.nm_alias || row.kode_store,
        nm_alias_bayar: row.nm_alias_bayar || row.store_bayar,
        nilai_faktur: parseFloat(row.nilai_faktur) || 0, // BARU
        potongan: parseFloat(row.potongan) || 0,
        ket_potongan: row.ket_potongan,   // BARU
        total_bayar: parseFloat(row.total_bayar) || 0,
        ket: row.ket,
      });
    });

    // Format Angka
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

// --- FUNGSI BARU UNTUK IMPORT ---
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
  [inpPotongan, inpTotalBayar].forEach(input => {
    input.addEventListener("blur", (e) => {
      e.target.value = formatNumber(parseNumber(e.target.value));
    });
    input.addEventListener("focus", (e) => e.target.select());
  });
  if (btnExport) btnExport.addEventListener("click", handleExport);
  
  if (btnImport) {
    btnImport.addEventListener("click", () => {
      // UPDATE: Panduan kolom disesuaikan dengan format baru (A-L)
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
});

window.addEventListener('beforeunload', (e) => {
    if (isSubmitting) return undefined;

    if (isFormDirty()) {
        e.preventDefault();
        e.returnValue = ''; 
        return '';
    }
});;