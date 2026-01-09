import { sendRequestGET, sendRequestJSON } from "../utils/api_helpers.js";
const API_URLS = {
    saveData: "/src/api/program_supplier/save_program_supplier.php",
    getData: "/src/api/program_supplier/get_latest_program_supplier.php",
    deleteData: "/src/api/program_supplier/delete_program_supplier.php",
    getStores: "/src/api/shared/get_all_store.php",
    searchSupplier: "/src/api/coretax/get_supplier_search.php",
};
const form = document.getElementById("program-form");
const inpOldNomorDokumen = document.getElementById("inp_old_nomor_dokumen");
const inpKodeCabang = document.getElementById("inp_kode_cabang");
const inpNamaSupplier = document.getElementById("inp_nama_supplier");
const listSupplier = document.getElementById("supplier_list");
const inpPic = document.getElementById("inp_pic");
const inpPeriode = document.getElementById("inp_periode_program");
const inpNamaProgram = document.getElementById("inp_nama_program");
const inpNomorDokumen = document.getElementById("inp_nomor_dokumen");
const inpNilaiProgram = document.getElementById("inp_nilai_program");
const inpMop = document.getElementById("inp_mop");
const inpTopDate = document.getElementById("inp_top_date");
const inpNilaiTransfer = document.getElementById("inp_nilai_transfer");
const inpTglTransfer = document.getElementById("inp_tanggal_transfer");
const inpTglFpk = document.getElementById("inp_tgl_fpk");
const inpNsfp = document.getElementById("inp_nsfp");
const inpNomorBukpot = document.getElementById("inp_nomor_bukpot");
const inpDpp = document.getElementById("inp_dpp");
const inpPpn = document.getElementById("inp_ppn");
const inpPph = document.getElementById("inp_pph");
const btnSave = document.getElementById("btn-save");
const btnCancelEdit = document.getElementById("btn-cancel-edit");
const editIndicator = document.getElementById("edit-mode-indicator");
const tableBody = document.getElementById("table-body");
const inpSearchTable = document.getElementById("inp_search_table");
const loaderRow = document.getElementById("loader-row");
let isSubmitting = false;
let debounceTimer;
let searchDebounceTimer;
let currentPage = 1;
let isLoadingData = false;
let hasMoreData = true;
let currentSearchTerm = "";
let tableRowIndex = 0;
let currentRequestController = null;
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
function formatDate(dateString) {
    if (!dateString || dateString === '0000-00-00') return "-";
    return new Date(dateString).toLocaleDateString("id-ID", { day: '2-digit', month: 'short', year: 'numeric' });
}
function formatStackedList(str) {
    if (!str) return '-';
    return str.split(',').map(s => {
        const cleanS = s.trim();
        if (!cleanS) return '';
        return `<div class="truncate max-w-[180px] text-gray-700 font-medium leading-tight mb-1 last:mb-0">${cleanS}</div>`;
    }).join('');
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
            inpKodeCabang.innerHTML = html;
        }
    } catch (error) {
        console.error("Gagal memuat cabang:", error);
    }
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
                <td colspan="10" class="text-center p-8">
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
    if (!reset) loaderRow.classList.remove("hidden");
    try {
        const params = new URLSearchParams({
            page: currentPage,
            search: currentSearchTerm
        });
        const response = await fetch(`${API_URLS.getData}?${params.toString()}`, {
            signal: reset ? currentRequestController.signal : null
        });
        const result = await response.json();
        if (reset) {
            tableBody.innerHTML = "";
        }
        if (result.success && Array.isArray(result.data)) {
            if (result.data.length === 0 && currentPage === 1) {
                tableBody.innerHTML = `<tr><td colspan="10" class="text-center p-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">Data tidak ditemukan</td></tr>`;
                hasMoreData = false;
            } else {
                renderTableRows(result.data);
                hasMoreData = result.has_more;
                if (hasMoreData) currentPage++;
            }
        }
    } catch (error) {
        if (error.name === 'AbortError') return;
        console.error(error);
        if (currentPage === 1) {
            tableBody.innerHTML = `<tr><td colspan="10" class="text-center p-4 text-red-500">Gagal memuat data</td></tr>`;
        }
    } finally {
        if (!currentRequestController || (currentRequestController && !currentRequestController.signal.aborted)) {
            isLoadingData = false;
            if (hasMoreData) loaderRow.classList.remove("hidden");
            else loaderRow.classList.add("hidden");
        }
    }
}
function renderTableRows(data) {
    data.forEach((row) => {
        console.log(row)
        tableRowIndex++;
        const picStacked = formatStackedList(row.pic);
        const docStacked = formatStackedList(row.nomor_dokumen);
        const mopBadge = row.mop === 'Transfer'
            ? '<span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded border border-blue-200 font-bold">Transfer</span>'
            : '<span class="bg-orange-100 text-orange-700 text-xs px-2 py-0.5 rounded border border-orange-200 font-bold">Potong Tagihan</span>';
        const tr = document.createElement("tr");
        tr.className = "hover:bg-pink-50 transition-colors border-b border-gray-50 align-top";
        tr.innerHTML = `
        <td class="text-center text-gray-500 py-3 font-medium text-sm">${tableRowIndex}</td>
        <td class="text-sm py-3 align-top">
             ${picStacked}
        </td>
        <td class="text-sm py-3 align-top">
            <div class="font-bold text-gray-800">${row.nama_supplier || '-'}</div>
            <div class="text-xs text-gray-500 mt-1 bg-gray-100 px-1.5 py-0.5 rounded w-fit border border-gray-200">
                ${row.nama_cabang || '-'}
            </div>
        </td>
        <td class="text-sm py-3 align-top">
            <div class="text-pink-600 font-medium">${row.nama_program || '-'}</div>
            <div class="text-xs text-gray-500 mt-0.5">${row.periode_program || '-'}</div>
        </td>
        <td class="text-xs py-3 align-top font-mono text-blue-700">
             ${docStacked}
        </td>
        <td class="text-right text-sm py-3 align-top font-mono font-bold text-gray-700">
            ${formatNumber(row.nilai_program)}
        </td>
        <td class="text-center py-3 align-top">
            <div class="mb-1">${mopBadge}</div>
            <div class="text-[10px] text-gray-500 whitespace-nowrap">TOP: ${formatDate(row.top_date)}</div>
        </td>
        <td class="text-right text-sm py-3 align-top">
            <div class="font-mono text-green-700 font-bold">${formatNumber(row.nilai_transfer)}</div>
            <div class="text-[10px] text-gray-400 mt-1">${formatDate(row.tanggal_transfer)}</div>
        </td>
        <td class="text-[10px] text-gray-600 py-3 align-top min-w-[120px]">
             <div class="flex justify-between border-b border-dashed border-gray-200 pb-1 mb-1">
                <span>DPP</span> <span class="font-mono text-gray-800">${formatNumber(row.dpp)}</span>
             </div>
             <div class="flex justify-between border-b border-dashed border-gray-200 pb-1 mb-1">
                <span>PPN</span> <span class="font-mono text-gray-800">${formatNumber(row.ppn)}</span>
             </div>
             <div class="flex justify-between">
                <span>PPH</span> <span class="font-mono text-gray-800">${formatNumber(row.pph)}</span>
             </div>
        </td>
        <td class="text-center py-3 align-top">
             <div class="flex justify-center gap-1">
                <button class="btn-edit-row text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 w-8 h-8 flex items-center justify-center rounded transition-all" title="Edit">
                    <i class="fas fa-pencil-alt"></i>
                </button>
                <button class="btn-delete-row text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 w-8 h-8 flex items-center justify-center rounded transition-all" 
                    data-doc="${row.nomor_dokumen}" title="Hapus">
                    <i class="fas fa-trash-alt"></i>
                </button>
             </div>
        </td>
        `;
        tr.querySelector(".btn-edit-row").addEventListener("click", () => startEditMode(row));
        tr.querySelector(".btn-delete-row").addEventListener("click", function () {
            handleDelete(this.getAttribute("data-doc"));
        });
        tableBody.appendChild(tr);
    });
}
async function handleSave() {
    const docVal = inpNomorDokumen.value.trim();
    if (!docVal) {
        Swal.fire("Gagal", "Nomor Dokumen harus diisi", "warning");
        return;
    }
    if (!inpNamaSupplier.value) {
        Swal.fire("Gagal", "Nama Supplier harus diisi", "warning");
        return;
    }
    isSubmitting = true;
    const originalBtnContent = btnSave.innerHTML;
    const originalBtnClass = btnSave.className;
    btnSave.disabled = true;
    btnSave.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Proses...`;
    const payload = {
        old_nomor_dokumen: inpOldNomorDokumen.value || null,
        nomor_dokumen: docVal,
        kode_cabang: inpKodeCabang.value,
        nama_supplier: inpNamaSupplier.value.trim(),
        pic: inpPic.value.trim(),
        periode_program: inpPeriode.value.trim(),
        nama_program: inpNamaProgram.value.trim(),
        nilai_program: parseNumber(inpNilaiProgram.value),
        mop: inpMop.value,
        top_date: inpTopDate.value || null,
        nilai_transfer: parseNumber(inpNilaiTransfer.value),
        tanggal_transfer: inpTglTransfer.value || null,
        tgl_fpk: inpTglFpk.value || null,
        nsfp: inpNsfp.value.trim(),
        nomor_bukpot: inpNomorBukpot.value.trim(),
        dpp: parseNumber(inpDpp.value),
        ppn: parseNumber(inpPpn.value),
        pph: parseNumber(inpPph.value)
    };
    let isSuccess = false;
    try {
        const result = await sendRequestJSON(API_URLS.saveData, payload);
        if (result.success) {
            isSuccess = true;
            Swal.fire({
                icon: "success",
                title: "Berhasil",
                text: result.message,
                timer: 1000,
                showConfirmButton: false
            });
            cancelEditMode();
            fetchTableData(true);
            inpNomorDokumen.focus();
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error("Save Error:", error);
        Swal.fire("Gagal", error.message || "Terjadi kesalahan sistem", "error");
    } finally {
        btnSave.disabled = false;
        isSubmitting = false;
        if (!isSuccess) {
            btnSave.innerHTML = originalBtnContent;
            btnSave.className = originalBtnClass;
        }
    }
}
function startEditMode(data) {
    inpOldNomorDokumen.value = data.nomor_dokumen;
    inpKodeCabang.value = data.kode_cabang || "";
    inpNamaSupplier.value = data.nama_supplier;
    inpPic.value = data.pic || "";
    inpPeriode.value = data.periode_program || "";
    inpNamaProgram.value = data.nama_program || "";
    inpNomorDokumen.value = data.nomor_dokumen;
    inpNilaiProgram.value = formatNumber(data.nilai_program);
    inpMop.value = data.mop || "Potong Tagihan";
    inpTopDate.value = data.top_date;
    inpNilaiTransfer.value = formatNumber(data.nilai_transfer);
    inpTglTransfer.value = data.tanggal_transfer;
    inpTglFpk.value = data.tgl_fpk;
    inpNsfp.value = data.nsfp || "";
    inpNomorBukpot.value = data.nomor_bukpot || "";
    inpDpp.value = formatNumber(data.dpp);
    inpPpn.value = formatNumber(data.ppn);
    inpPph.value = formatNumber(data.pph);
    window.scrollTo({ top: 0, behavior: "smooth" });
    document.querySelector(".input-row-container").classList.add("border-amber-300", "bg-amber-50");
    editIndicator.classList.remove("hidden");
    btnCancelEdit.classList.remove("hidden");
    btnSave.innerHTML = `<i class="fas fa-sync-alt"></i> <span>Update</span>`;
    btnSave.className = "btn-warning px-6 py-2 rounded shadow-lg bg-yellow-500 text-white hover:bg-amber-600 flex items-center gap-2 font-medium";
    inpNomorDokumen.focus();
}
function cancelEditMode() {
    form.reset();
    inpOldNomorDokumen.value = "";
    document.querySelector(".input-row-container").classList.remove("border-amber-300", "bg-amber-50");
    editIndicator.classList.add("hidden");
    btnCancelEdit.classList.add("hidden");
    btnSave.disabled = false;
    btnSave.innerHTML = `<i class="fas fa-save"></i> <span>Simpan</span>`;
    btnSave.className = "btn-primary flex items-center gap-2 px-6 py-2 shadow-lg shadow-pink-500/30 rounded text-white bg-pink-600 hover:bg-pink-700 transition-all font-medium";
}
function handleDelete(doc) {
    Swal.fire({
        title: "Hapus Data?",
        text: `Nomor Dokumen: ${doc} akan dihapus.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        confirmButtonText: "Ya, Hapus!",
        cancelButtonText: "Batal"
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                Swal.fire({
                    title: "Memproses...",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                const resp = await sendRequestJSON(API_URLS.deleteData, { nomor_dokumen: doc });
                if (resp.success) {
                    Swal.fire("Terhapus!", resp.message, "success");
                    fetchTableData(true);
                    if (inpOldNomorDokumen.value === doc) cancelEditMode();
                } else {
                    throw new Error(resp.message);
                }
            } catch (error) {
                Swal.fire("Gagal", error.message, "error");
            }
        }
    });
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
document.addEventListener("DOMContentLoaded", () => {
    loadStoreOptions();
    fetchTableData(true);
    [inpNilaiProgram, inpNilaiTransfer, inpDpp, inpPpn, inpPph].forEach(input => {
        input.addEventListener("blur", (e) => {
            e.target.value = formatNumber(parseNumber(e.target.value));
        });
        input.addEventListener("focus", (e) => e.target.select());
    });
    btnSave.addEventListener("click", handleSave);
    btnCancelEdit.addEventListener("click", cancelEditMode);
    if (inpSearchTable) {
        inpSearchTable.addEventListener("input", (e) => {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => {
                currentSearchTerm = e.target.value;
                fetchTableData(true);
            }, 600);
        });
    }
    inpNamaSupplier.addEventListener("input", handleSupplierSearch);
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
    if (loaderRow) observer.observe(loaderRow);
});