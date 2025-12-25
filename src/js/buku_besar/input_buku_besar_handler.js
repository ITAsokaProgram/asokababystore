import { sendRequestGET, sendRequestJSON } from "../utils/api_helpers.js";

const API_URLS = {
  getFakturDetail: "/src/api/buku_besar/get_pembelian_detail.php",
  saveData: "/src/api/buku_besar/save_buku_besar.php",
  getData: "/src/api/buku_besar/get_latest_buku_besar.php",
  deleteData: "/src/api/buku_besar/delete_buku_besar.php",
  getStores: "/src/api/shared/get_all_store.php",
  searchSupplier: "/src/api/coretax/get_supplier_search.php",
};

// DOM Elements
const form = document.getElementById("single-form");
const inpId = document.getElementById("inp_id");
const inpKodeStore = document.getElementById("inp_kode_store");
const inpNoFaktur = document.getElementById("inp_no_faktur");
const inpKodeSupplier = document.getElementById("inp_kode_supplier");
const inpNamaSupp = document.getElementById("inp_nama_supplier");
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

// State Variables
let isSubmitting = false;
let debounceTimer;
let searchDebounceTimer;
let currentPage = 1;
let isLoadingData = false;
let hasMoreData = true;
let currentSearchTerm = "";
let tableRowIndex = 0;
let currentRequestController = null; // Untuk handle abort request

// --- Helper Functions ---
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

// --- Logic Data Loading (Infinite Scroll) ---

async function fetchTableData(reset = false) {
  if (isLoadingData && !reset) return;

  if (reset) {
    currentPage = 1;
    tableRowIndex = 0;
    hasMoreData = true;
    
    // Abort request sebelumnya jika ada (biar gak balapan)
    if (currentRequestController) {
      currentRequestController.abort();
    }
    currentRequestController = new AbortController();

    // Tampilkan loader besar saat reset/search awal
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
    loaderRow.classList.remove("hidden"); // Tampilkan loader bawah saat scroll
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
    if (error.name === "AbortError") return; // Abaikan jika dibatalkan user
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
    
    // Potongan & Total
    const potongan = parseFloat(row.potongan || 0);
    const total = parseFloat(row.total_bayar || 0);

    tr.innerHTML = `
        <td class="text-center text-gray-500 py-3">${tableRowIndex}</td>
        <td class="text-sm">${row.tanggal_bayar || "-"}</td>
        <td class="font-medium text-gray-800 text-sm">${row.no_faktur}</td>
        <td class="text-sm">${row.nama_supplier}</td>
        <td><span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded border border-gray-200">${row.nm_alias || row.kode_store}</span></td>
        <td class="text-sm text-gray-500 italic max-w-xs truncate" title="${row.ket || ''}">${row.ket || "-"}</td>
        <td class="text-right font-mono text-sm text-red-500">${formatNumber(potongan)}</td>
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
    rootMargin: "100px", // Load lebih awal sebelum mentok bawah
    threshold: 0.1,
  };

  const observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting && !isLoadingData && hasMoreData) {
      fetchTableData(false);
    }
  }, observerOptions);

  observer.observe(loaderRow);
}

// --- Logic Lainnya (Store, Search, CRUD) ---

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
      inpTotalBayar.value = formatNumber(parseFloat(d.total_bayar) || 0);
      
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
  const payload = {
    id: inpId.value || null,
    kode_store: inpKodeStore.value,
    no_faktur: noFaktur,
    kode_supplier: inpKodeSupplier.value,
    nama_supplier: inpNamaSupp.value,
    tgl_nota: inpTglNota.value,
    tanggal_bayar: inpTglBayar.value,
    potongan: parseNumber(inpPotongan.value),
    ket_potongan: inpKetPotongan.value,
    ket: inpKet.value,
    total_bayar: parseNumber(inpTotalBayar.value)
  };

  const originalBtnContent = btnSave.innerHTML;
  const originalBtnClass = btnSave.className;
  btnSave.disabled = true;
  btnSave.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Proses...`;

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
  } finally {
    btnSave.disabled = false;
    btnSave.innerHTML = originalBtnContent;
    btnSave.className = originalBtnClass;
    isSubmitting = false;
  }
}
function startEditMode(data) {
  inpId.value = data.id;

  // PERBAIKAN DI SINI:
  // 1. Cek apakah kode_store ada
  // 2. Gunakan .trim() untuk membuang spasi kosong yang mungkin terbawa dari database
  // 3. Jika null/undefined, set ke string kosong
  const kodeStoreVal = data.kode_store ? data.kode_store.trim() : "";
  inpKodeStore.value = kodeStoreVal;

  // Debugging: Jika masih kosong, cek console browser
  if (inpKodeStore.value !== kodeStoreVal) {
      console.warn("Data store tidak cocok dengan opsi yang tersedia.", {
          data_dari_tabel: kodeStoreVal,
          opsi_tersedia: Array.from(inpKodeStore.options).map(o => o.value)
      });
  }

  inpNoFaktur.value = data.no_faktur;
  inpKodeSupplier.value = data.kode_supplier;
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
  
  // Default values
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

// --- Event Listeners ---

document.addEventListener("DOMContentLoaded", async () => {
  // Tambahkan await agar dropdown terisi DULUAN sebelum data tabel diambil
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

  // Handle Search dengan Debounce & Reset Table
  if (inpSearchTable) {
    inpSearchTable.addEventListener("input", (e) => {
      clearTimeout(searchDebounceTimer);
      searchDebounceTimer = setTimeout(() => {
        currentSearchTerm = e.target.value;
        fetchTableData(true); // Reset = true
      }, 600);
    });
  }
  
  // Formatting Money
  [inpPotongan, inpTotalBayar].forEach(input => {
    input.addEventListener("blur", (e) => {
      e.target.value = formatNumber(parseNumber(e.target.value));
    });
    input.addEventListener("focus", (e) => e.target.select());
  });
});