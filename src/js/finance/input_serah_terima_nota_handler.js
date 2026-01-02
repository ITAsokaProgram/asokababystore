import { sendRequestGET, sendRequestJSON } from "../utils/api_helpers.js";

const API_URLS = {
    saveData: "/src/api/finance/save_serah_terima_nota.php",
    getData: "/src/api/finance/get_serah_terima_nota.php",
    deleteData: "/src/api/finance/delete_serah_terima_nota.php",
    searchSupplier: "/src/api/coretax/get_supplier_search.php"
};

// DOM Elements
const form = document.getElementById("single-form");
const inpId = document.getElementById("inp_id");
const inpNoNota = document.getElementById("inp_no_nota");
const inpTglNota = document.getElementById("inp_tgl_nota");
const inpNamaSupplier = document.getElementById("inp_nama_supplier");
const inpKodeSupplier = document.getElementById("inp_kode_supplier");
const inpStatus = document.getElementById("inp_status");
const inpNoFakturFormat = document.getElementById("inp_no_faktur_format");
const inpNoFaktur = document.getElementById("inp_no_faktur");
const inpNoRevNota = document.getElementById("inp_no_rev_nota");
const inpDiberikan = document.getElementById("inp_diberikan");
const inpPenerima = document.getElementById("inp_penerima");
const inpNominalAwal = document.getElementById("inp_nominal_awal");
const inpNominalRevisi = document.getElementById("inp_nominal_revisi");
const inpSelisih = document.getElementById("inp_selisih");
const inpTglDiserahkan = document.getElementById("inp_tgl_diserahkan");
const inpTglDiterima = document.getElementById("inp_tgl_diterima");
const listSupplier = document.getElementById("supplier_list");

const btnSave = document.getElementById("btn-save");
const btnCancelEdit = document.getElementById("btn-cancel-edit");
const editIndicator = document.getElementById("edit-mode-indicator");

const tableBody = document.getElementById("table-body");
const inpSearchTable = document.getElementById("inp_search_table");
const filterSort = document.getElementById("filter_sort");
const filterTgl = document.getElementById("filter_tgl");
const loaderRow = document.getElementById("loader-row");

// State Variables
let isSubmitting = false;
let debounceTimer;
let searchDebounceTimer;
let currentPage = 1;
let isLoadingData = false;
let hasMoreData = true;
let currentSearchTerm = "";
let currentDateFilter = "";
let currentSortOption = "created";
let tableRowIndex = 0;

// Formatters
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

// Logic Clean Faktur (Excel Formula Implementation)
// =SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(F5,"-",""),",",""),".",""),"/","")," ",""),"\",""),"'",""),"_","")
function cleanFakturString(str) {
    if (!str) return "";
    return str.replace(/[\-\,\.\/\s\\\'_]/g, "");
}

function calculateSelisih() {
    const awal = parseNumber(inpNominalAwal.value);
    const revisi = parseNumber(inpNominalRevisi.value);
    // Asumsi: Selisih = Awal - Revisi (Sesuaikan jika terbalik)
    const selisih = awal - revisi;
    inpSelisih.value = formatNumber(selisih);
}

// Event Listeners for Calculation & Cleaning
inpNominalAwal.addEventListener('input', () => calculateSelisih());
inpNominalRevisi.addEventListener('input', () => calculateSelisih());
inpNoFakturFormat.addEventListener('input', (e) => {
    const raw = e.target.value;
    inpNoFaktur.value = cleanFakturString(raw);
});

// Format Number on Blur
[inpNominalAwal, inpNominalRevisi].forEach(input => {
    input.addEventListener('blur', (e) => {
        const val = parseNumber(e.target.value);
        e.target.value = formatNumber(val);
        calculateSelisih();
    });
    input.addEventListener('focus', (e) => e.target.select());
});

// --- Data Fetching Logic ---

let currentRequestController = null;

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
            search: currentSearchTerm,
            date: currentDateFilter,
            sort: currentSortOption,
        });

        const signal = reset ? currentRequestController.signal : null;
        const response = await fetch(`${API_URLS.getData}?${params.toString()}`, { signal });
        const result = await response.json();

        if (reset) tableBody.innerHTML = "";

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
        if (error.name === "AbortError") return;
        console.error(error);
    } finally {
        if (!currentRequestController || (currentRequestController && !currentRequestController.signal.aborted)) {
            isLoadingData = false;
            loaderRow.classList.toggle("hidden", !hasMoreData);
        }
    }
}
function renderTableRows(data) {
    data.forEach((row) => {
        tableRowIndex++;
        const badgeColor = row.status === 'Sudah Terima' ? 'green' : 'gray';

        const tr = document.createElement("tr");
        tr.className = "hover:bg-pink-50 transition-colors border-b border-gray-50";

        // Urutan Kolom: 
        // NO, TGL NOTA, SUPPLIER, NO NOTA, REV NOTA, FAKTUR, 
        // NOM AWAL, NOM REVISI, SELISIH, TGL SERAH, TGL TERIMA, 
        // STATUS, DIBERIKAN, PENERIMA, AKSI
        tr.innerHTML = `
            <td class="text-center text-gray-500 py-3">${tableRowIndex}</td>
            <td class="text-sm whitespace-nowrap">${row.tgl_nota || '-'}</td>
            <td class="text-sm whitespace-nowrap">
                <span class="truncate max-w-[200px] inline-block" title="${row.nama_supplier}">
                    ${row.nama_supplier}
                </span>
            </td>
            <td class="font-medium text-gray-800 text-sm whitespace-nowrap">${row.no_nota}</td>
            <td class="text-sm text-gray-600 whitespace-nowrap">${row.no_rev_nota || '-'}</td>
            <td class="text-sm font-mono text-gray-600 whitespace-nowrap">${row.no_faktur_format || '-'}</td>
            <td class="text-right font-mono text-sm whitespace-nowrap">${formatNumber(row.nominal_awal)}</td>
            <td class="text-right font-mono text-sm whitespace-nowrap">${formatNumber(row.nominal_revisi)}</td>
            <td class="text-right font-mono text-sm font-bold whitespace-nowrap text-pink-600">${formatNumber(row.selisih_pembayaran)}</td>
            <td class="text-sm whitespace-nowrap">${row.tgl_diserahkan || '-'}</td>
            <td class="text-sm whitespace-nowrap">${row.tgl_diterima || '-'}</td>
            <td class="text-center whitespace-nowrap">
                <span class="bg-${badgeColor}-100 text-${badgeColor}-700 text-xs px-2 py-1 rounded border border-${badgeColor}-200">
                    ${row.status}
                </span>
            </td>
            <td class="text-sm whitespace-nowrap">${row.diberikan || '-'}</td>
            <td class="text-sm whitespace-nowrap">${row.penerima || '-'}</td>
            <td class="text-center py-2 whitespace-nowrap">
                <div class="flex justify-center gap-1">
                    <button class="btn-edit-row text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 w-8 h-8 flex items-center justify-center rounded transition-all" title="Edit">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button class="btn-delete-row text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 w-8 h-8 flex items-center justify-center rounded transition-all" data-id="${row.id}" data-nota="${row.no_nota}" title="Hapus">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </td>
        `;

        tr.querySelector(".btn-edit-row").addEventListener("click", () => startEditMode(row));
        tr.querySelector(".btn-delete-row").addEventListener("click", function () {
            handleDelete(this.getAttribute("data-id"), this.getAttribute("data-nota"));
        });

        tableBody.appendChild(tr);
    });
}

function startEditMode(data) {
    inpId.value = data.id;
    inpNoNota.value = data.no_nota;
    inpTglNota.value = data.tgl_nota;
    inpNamaSupplier.value = data.nama_supplier;
    inpKodeSupplier.value = data.kode_supplier;
    inpStatus.value = data.status;
    inpNoFakturFormat.value = data.no_faktur_format;
    inpNoFaktur.value = data.no_faktur;
    inpNoRevNota.value = data.no_rev_nota;
    inpDiberikan.value = data.diberikan;
    inpPenerima.value = data.penerima;

    inpNominalAwal.value = formatNumber(data.nominal_awal);
    inpNominalRevisi.value = formatNumber(data.nominal_revisi);
    calculateSelisih(); // Auto update selisih

    inpTglDiserahkan.value = data.tgl_diserahkan;
    inpTglDiterima.value = data.tgl_diterima;

    // UI Updates
    window.scrollTo({ top: 0, behavior: "smooth" });
    document.querySelector(".input-row-container").classList.add("border-amber-300", "bg-amber-50");
    editIndicator.classList.remove("hidden");
    btnCancelEdit.classList.remove("hidden");
    btnSave.innerHTML = `<i class="fas fa-sync-alt"></i> <span>Update</span>`;
    btnSave.className = "btn-warning px-6 py-2 rounded shadow-lg bg-yellow-500 text-white hover:bg-amber-600";
}

function cancelEditMode() {
    form.reset();
    inpId.value = "";
    inpKodeSupplier.value = "";
    inpNoFaktur.value = "";

    document.querySelector(".input-row-container").classList.remove("border-amber-300", "bg-amber-50");
    editIndicator.classList.add("hidden");
    btnCancelEdit.classList.add("hidden");
    btnSave.innerHTML = `<i class="fas fa-save"></i> <span>Simpan</span>`;
    btnSave.className = "btn-primary shadow-lg shadow-pink-500/30 flex items-center gap-2 px-6 py-2";
}

async function handleSave() {
    if (!inpNoNota.value.trim() || !inpNamaSupplier.value.trim()) {
        Swal.fire("Peringatan", "No Nota dan Nama Supplier wajib diisi.", "warning");
        return;
    }

    isSubmitting = true;
    const originalContent = btnSave.innerHTML;
    btnSave.disabled = true;
    btnSave.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Proses...`;

    // Pastikan No Faktur Clean terupdate sebelum kirim
    const cleanFaktur = cleanFakturString(inpNoFakturFormat.value);

    const payload = {
        id: inpId.value || null,
        no_nota: inpNoNota.value.trim(),
        tgl_nota: inpTglNota.value,
        nama_supplier: inpNamaSupplier.value,
        kode_supplier: inpKodeSupplier.value,
        status: inpStatus.value,
        no_faktur_format: inpNoFakturFormat.value,
        no_faktur: cleanFaktur,
        no_rev_nota: inpNoRevNota.value,
        diberikan: inpDiberikan.value,
        penerima: inpPenerima.value,
        nominal_awal: parseNumber(inpNominalAwal.value),
        nominal_revisi: parseNumber(inpNominalRevisi.value),
        selisih_pembayaran: parseNumber(inpSelisih.value),
        tgl_diserahkan: inpTglDiserahkan.value,
        tgl_diterima: inpTglDiterima.value
    };

    try {
        const result = await sendRequestJSON(API_URLS.saveData, payload);
        if (result.success) {
            Swal.fire({
                icon: "success",
                title: "Berhasil",
                text: result.message,
                timer: 1000,
                showConfirmButton: false,
            });
            cancelEditMode();
            fetchTableData(true);
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        Swal.fire("Gagal", error.message, "error");
    } finally {
        btnSave.disabled = false;
        btnSave.innerHTML = originalContent;
        isSubmitting = false;
    }
}

function handleDelete(id, nota) {
    Swal.fire({
        title: "Hapus Data?",
        text: `Anda yakin ingin menghapus Nota ${nota}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        confirmButtonText: "Ya, Hapus!",
        cancelButtonText: "Batal",
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const resp = await sendRequestJSON(API_URLS.deleteData, { id: id });
                if (resp.success) {
                    Swal.fire("Terhapus!", resp.message, "success");
                    fetchTableData(true);
                } else {
                    throw new Error(resp.message);
                }
            } catch (error) {
                Swal.fire("Gagal", error.message, "error");
            }
        }
    });
}

// Search Supplier Logic
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

// Initialization
document.addEventListener("DOMContentLoaded", () => {
    fetchTableData(true);
    setupInfinityScroll();

    // Attach Handlers
    btnSave.addEventListener("click", handleSave);
    btnCancelEdit.addEventListener("click", cancelEditMode);

    inpNamaSupplier.addEventListener("input", handleSupplierSearch);

    inpSearchTable.addEventListener("input", (e) => {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            currentSearchTerm = e.target.value;
            fetchTableData(true);
        }, 600);
    });

    filterSort.addEventListener("change", (e) => {
        currentSortOption = e.target.value;
        fetchTableData(true);
    });

    filterTgl.addEventListener("change", (e) => {
        currentDateFilter = e.target.value;
        fetchTableData(true);
    });
});

function setupInfinityScroll() {
    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !isLoadingData && hasMoreData) {
            fetchTableData(false);
        }
    }, { root: document.getElementById("table-scroll-container"), threshold: 0.1 });
    observer.observe(loaderRow);
}