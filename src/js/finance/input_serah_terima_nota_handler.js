import { sendRequestGET, sendRequestJSON } from "../utils/api_helpers.js";
const API_URLS = {
    saveData: "/src/api/finance/save_serah_terima_nota.php",
    getData: "/src/api/finance/get_serah_terima_nota.php",
    deleteData: "/src/api/finance/delete_serah_terima_nota.php",
    searchSupplier: "/src/api/coretax/get_supplier_search.php"
};
const form = document.getElementById("single-form");
const inpTglNota = document.getElementById("inp_tgl_nota");
const inpNamaSupplier = document.getElementById("inp_nama_supplier");
const inpKodeSupplier = document.getElementById("inp_kode_supplier");
const inpNoFakturFormat = document.getElementById("inp_no_faktur_format");
const inpNoFaktur = document.getElementById("inp_no_faktur");
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
let isSubmitting = false;
let currentEditKey = null;
let debounceTimer;
let searchDebounceTimer;
let currentPage = 1;
let isLoadingData = false;
let hasMoreData = true;
let currentSearchTerm = "";
let currentDateFilter = "";
let currentSortOption = "created";
let tableRowIndex = 0;
function sanitizeNumberInput(e) {
    e.target.value = e.target.value.replace(/[^0-9.]/g, '');
    calculateSelisih();
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
function cleanFakturString(str) {
    if (!str) return "";
    return str.replace(/[\-\,\.\/\s\\\'_]/g, "");
}
function calculateSelisih() {
    const awal = parseNumber(inpNominalAwal.value);
    const revisi = parseNumber(inpNominalRevisi.value);
    const selisih = awal - revisi;
    inpSelisih.value = formatNumber(selisih);
}
inpNominalAwal.addEventListener('input', sanitizeNumberInput);
inpNominalRevisi.addEventListener('input', sanitizeNumberInput);
inpNominalAwal.addEventListener('input', () => calculateSelisih());
inpNominalRevisi.addEventListener('input', () => calculateSelisih());
inpNoFakturFormat.addEventListener('input', (e) => {
    const raw = e.target.value;
    inpNoFaktur.value = cleanFakturString(raw);
});
[inpNominalAwal, inpNominalRevisi].forEach(input => {
    input.addEventListener('blur', (e) => {
        const val = parseNumber(e.target.value);
        e.target.value = formatNumber(val);
        calculateSelisih();
    });
    input.addEventListener('focus', (e) => e.target.select());
});
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
    tableBody.innerHTML = "";
    data.forEach((row) => {
        tableRowIndex++;
        const editKey = row.no_faktur;
        const tr = document.createElement("tr");
        tr.className = "hover:bg-pink-50 transition-colors border-b border-gray-50";
        tr.innerHTML = `
            <td class="text-center text-gray-500 py-3">${tableRowIndex}</td>
            <td class="text-sm whitespace-nowrap">${row.tgl_nota || '-'}</td>
            <td class="text-sm whitespace-nowrap">${row.nama_supplier}</td>
            <td class="text-sm font-mono text-gray-600 whitespace-nowrap">${row.no_faktur_format || '-'}</td>
            <td class="text-right font-mono text-sm whitespace-nowrap">${formatNumber(row.nominal_awal)}</td>
            <td class="text-right font-mono text-sm whitespace-nowrap">${formatNumber(row.nominal_revisi)}</td>
            <td class="text-right font-mono text-sm font-bold text-pink-600">${formatNumber(row.selisih_pembayaran)}</td>
            <td class="text-sm whitespace-nowrap">${row.tgl_diserahkan || '-'}</td>
            <td class="text-sm whitespace-nowrap">${row.tgl_diterima || '-'}</td>
            <td class="text-center whitespace-nowrap">
                <span class="bg-${row.status === 'Sudah Terima' ? 'green' : 'gray'}-100 text-xs px-2 py-1 rounded">
                    ${row.status}
                </span>
            </td>
            <td class="text-sm whitespace-nowrap">${row.diberikan || '-'}</td>
            <td class="text-sm whitespace-nowrap">${row.penerima || '-'}</td>
            <td class="text-center py-2 whitespace-nowrap">
                <div class="flex justify-center gap-1">
                    <button class="btn-edit-row text-blue-600 bg-blue-50 w-8 h-8 rounded" title="Edit">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button class="btn-delete-row text-red-600 bg-red-50 w-8 h-8 rounded" data-faktur="${editKey}" title="Hapus">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </td>
        `;
        tr.querySelector(".btn-edit-row").addEventListener("click", () => startEditMode(row));
        tr.querySelector(".btn-delete-row").addEventListener("click", function () {
            handleDelete(this.getAttribute("data-faktur"));
        });
        tableBody.appendChild(tr);
    });
}
function startEditMode(data) {
    currentEditKey = data.no_faktur;

    let safeTglNota = data.tgl_nota;
    if (safeTglNota && safeTglNota.includes(' ')) {
        safeTglNota = safeTglNota.split(' ')[0];
    }
    inpTglNota.value = safeTglNota;
    inpNamaSupplier.value = data.nama_supplier;
    inpKodeSupplier.value = data.kode_supplier;
    // inpStatus.value = data.status; // DIHAPUS

    inpNoFakturFormat.value = data.no_faktur_format;
    inpNoFaktur.value = data.no_faktur;

    // Set value
    inpDiberikan.value = data.diberikan;
    inpTglDiserahkan.value = data.tgl_diserahkan;
    inpNominalAwal.value = formatNumber(data.nominal_awal);

    // LOGIKA BARU: Kunci Field saat Mode Edit
    inpNominalAwal.readOnly = true;
    inpNominalAwal.classList.add('input-readonly');

    inpTglDiserahkan.readOnly = true;
    inpTglDiserahkan.classList.add('input-readonly');

    inpDiberikan.readOnly = true;
    inpDiberikan.classList.add('input-readonly');

    const containerRevisi = document.getElementById("container-nominal-revisi");
    if (containerRevisi) {
        containerRevisi.classList.remove("hidden");
        inpNominalRevisi.value = formatNumber(data.nominal_revisi);
    }

    calculateSelisih();

    window.scrollTo({ top: 0, behavior: "smooth" });

    const rowContainer = document.querySelector(".input-row-container");
    if (rowContainer) {
        rowContainer.classList.add("border-amber-300", "bg-amber-50");
    }

    if (editIndicator) editIndicator.classList.remove("hidden");
    if (btnCancelEdit) btnCancelEdit.classList.remove("hidden");

    btnSave.innerHTML = `<i class="fas fa-edit"></i> <span>Update</span>`;
    btnSave.className = "btn-warning flex items-center gap-2 px-6 py-2 shadow-lg shadow-orange-500/30 bg-orange-500 text-white rounded hover:bg-orange-600 transition-colors";
}

function cancelEditMode() {
    form.reset();
    currentEditKey = null;

    // LOGIKA BARU: Buka Kunci Field
    inpNominalAwal.readOnly = false;
    inpNominalAwal.classList.remove('input-readonly');

    inpTglDiserahkan.readOnly = false;
    inpTglDiserahkan.classList.remove('input-readonly');

    inpDiberikan.readOnly = false;
    inpDiberikan.classList.remove('input-readonly');

    const containerRevisi = document.getElementById("container-nominal-revisi");
    if (containerRevisi) {
        containerRevisi.classList.add("hidden");
    }

    const rowContainer = document.querySelector(".input-row-container");
    if (rowContainer) {
        rowContainer.classList.remove("border-amber-300", "bg-amber-50");
    }

    if (editIndicator) editIndicator.classList.add("hidden");
    if (btnCancelEdit) btnCancelEdit.classList.add("hidden");

    btnSave.innerHTML = `<i class="fas fa-save"></i> <span>Simpan</span>`;
    btnSave.className = "btn-primary flex items-center gap-2 px-6 py-2 shadow-lg shadow-pink-500/30";

    inpSelisih.value = "0";
}

async function handleSave() {
    if (!inpNoFakturFormat.value.trim() || !inpNamaSupplier.value.trim()) {
        Swal.fire("Peringatan", "No Faktur dan Nama Supplier wajib diisi.", "warning");
        return;
    }

    if (isSubmitting) return;
    isSubmitting = true;

    const originalContent = btnSave.innerHTML;
    btnSave.disabled = true;
    btnSave.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Proses...`;

    const cleanFaktur = cleanFakturString(inpNoFakturFormat.value);

    const payload = {
        original_no_faktur: currentEditKey ? currentEditKey : null,
        tgl_nota: inpTglNota.value,
        nama_supplier: inpNamaSupplier.value,
        kode_supplier: inpKodeSupplier.value,
        // status: inpStatus.value, // DIHAPUS, biar backend handle default
        no_faktur_format: inpNoFakturFormat.value,
        no_faktur: cleanFaktur,
        diberikan: inpDiberikan.value,
        nominal_awal: parseNumber(inpNominalAwal.value),
        nominal_revisi: parseNumber(inpNominalRevisi.value),
        selisih_pembayaran: parseNumber(inpSelisih.value),
        tgl_diserahkan: inpTglDiserahkan.value
    };

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
        Swal.fire("Gagal", error.message, "error");
        btnSave.innerHTML = originalContent;
    } finally {
        btnSave.disabled = false;
        isSubmitting = false;
    }
}

function handleDelete(faktur) {
    Swal.fire({
        title: "Hapus Data?",
        text: `Anda yakin ingin menghapus Faktur ${faktur}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        confirmButtonText: "Ya, Hapus!",
        cancelButtonText: "Batal",
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const resp = await sendRequestJSON(API_URLS.deleteData, { no_faktur: faktur });
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
    fetchTableData(true);
    setupInfinityScroll();
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