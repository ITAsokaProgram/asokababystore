import { sendRequestGET, sendRequestJSON } from "../utils/api_helpers.js";
const API_URLS = {
    saveData: "/src/api/finance/save_serah_terima_nota.php",
    getData: "/src/api/finance/get_serah_terima_nota.php",
    searchSupplier: "/src/api/coretax/get_supplier_search.php"
};
const form = document.getElementById("single-form");
const inpTglNota = document.getElementById("inp_tgl_nota");
const inpNamaSupplier = document.getElementById("inp_nama_supplier");
const inpKodeSupplier = document.getElementById("inp_kode_supplier");
const inpNoFakturFormat = document.getElementById("inp_no_faktur_format");
const inpNoFaktur = document.getElementById("inp_no_faktur");
const inpDiberikan = document.getElementById("inp_diberikan");
const inpNominalAwal = document.getElementById("inp_nominal_awal");
const inpNominalRevisi = document.getElementById("inp_nominal_revisi");
const inpSelisih = document.getElementById("inp_selisih");
const inpTglDiserahkan = document.getElementById("inp_tgl_diserahkan");
const listSupplier = document.getElementById("supplier_list");
const btnSave = document.getElementById("btn-save");
const tableBody = document.getElementById("table-body");
const inpSearchTable = document.getElementById("inp_search_table");
const filterSort = document.getElementById("filter_sort");
const filterTgl = document.getElementById("filter_tgl");
let isSubmitting = false;
let debounceTimer;
let searchDebounceTimer;
let isLoadingData = false;
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
async function fetchTableData() {
    if (isLoadingData) return;
    isLoadingData = true;
    if (currentRequestController) {
        currentRequestController.abort();
    }
    currentRequestController = new AbortController();
    tableRowIndex = 0;
    tableBody.innerHTML = `
        <tr>
            <td colspan="12" class="text-center p-8">
                <div class="flex flex-col items-center justify-center">
                    <i class="fas fa-circle-notch fa-spin text-pink-500 text-3xl mb-3"></i>
                    <span class="text-gray-500 font-medium animate-pulse">Memuat data...</span>
                </div>
            </td>
        </tr>
    `;
    try {
        const params = new URLSearchParams({
            search: currentSearchTerm,
            date: currentDateFilter,
            sort: currentSortOption,
        });
        const response = await fetch(`${API_URLS.getData}?${params.toString()}`, { signal: currentRequestController.signal });
        const result = await response.json();
        if (result.success && Array.isArray(result.data)) {
            if (result.data.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="12" class="text-center p-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">Data tidak ditemukan</td></tr>`;
            } else {
                renderTableRows(result.data);
            }
        }
    } catch (error) {
        if (error.name === "AbortError") return;
        console.error(error);
        tableBody.innerHTML = `<tr><td colspan="12" class="text-center text-red-500 p-4">Gagal memuat data</td></tr>`;
    } finally {
        if (!currentRequestController || (currentRequestController && !currentRequestController.signal.aborted)) {
            isLoadingData = false;
        }
    }
}
function renderTableRows(data) {
    tableBody.innerHTML = "";
    data.forEach((row) => {
        tableRowIndex++;
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
        `;
        tableBody.appendChild(tr);
    });
}
async function handleSave() {
    ''
    if (!inpTglNota.value) {
        Swal.fire("Peringatan", "Tanggal Invoice wajib diisi.", "warning");
        return;
    }
    if (!inpNamaSupplier.value.trim()) {
        Swal.fire("Peringatan", "Nama Supplier wajib diisi.", "warning");
        return;
    }
    if (!inpNoFakturFormat.value.trim()) {
        Swal.fire("Peringatan", "No Faktur wajib diisi.", "warning");
        return;
    }
    if (!inpNominalAwal.value || parseNumber(inpNominalAwal.value) === 0) {
        Swal.fire("Peringatan", "Nominal Awal wajib diisi.", "warning");
        return;
    }
    if (!inpTglDiserahkan.value) {
        Swal.fire("Peringatan", "Tanggal Diserahkan wajib diisi.", "warning");
        return;
    }
    if (!inpDiberikan.value.trim()) {
        Swal.fire("Peringatan", "Kolom Diberikan Oleh wajib diisi.", "warning");
        return;
    }
    if (isSubmitting) return;
    isSubmitting = true;
    const originalContent = btnSave.innerHTML;
    btnSave.disabled = true;
    btnSave.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Proses...`;
    const cleanFaktur = cleanFakturString(inpNoFakturFormat.value);
    const payload = {
        tgl_nota: inpTglNota.value,
        nama_supplier: inpNamaSupplier.value,
        kode_supplier: inpKodeSupplier.value,
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
            Swal.fire({
                icon: "success",
                title: "Berhasil",
                text: result.message,
                timer: 1000,
                showConfirmButton: false
            });
            inpNoFakturFormat.value = "";
            inpNoFaktur.value = "";
            inpNominalAwal.value = "0";
            inpNominalRevisi.value = "0";
            inpSelisih.value = "0";
            inpNoFakturFormat.focus();
            fetchTableData();
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        Swal.fire("Gagal", error.message, "error");
    } finally {
        btnSave.innerHTML = originalContent;
        btnSave.disabled = false;
        isSubmitting = false;
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
} document.addEventListener("DOMContentLoaded", () => {
    fetchTableData();
    const savedTglNota = localStorage.getItem('stn_tgl_nota');
    const savedNamaSupplier = localStorage.getItem('stn_nama_supplier');
    const savedKodeSupplier = localStorage.getItem('stn_kode_supplier');
    const savedTglDiserahkan = localStorage.getItem('stn_tgl_diserahkan');
    const savedDiberikan = localStorage.getItem('stn_diberikan');
    if (savedTglNota) inpTglNota.value = savedTglNota;
    if (savedNamaSupplier) inpNamaSupplier.value = savedNamaSupplier;
    if (savedKodeSupplier) inpKodeSupplier.value = savedKodeSupplier;
    if (savedTglDiserahkan) inpTglDiserahkan.value = savedTglDiserahkan;
    if (savedDiberikan) inpDiberikan.value = savedDiberikan;
    const saveToLS = (key, value) => localStorage.setItem(key, value);
    inpTglNota.addEventListener('change', (e) => saveToLS('stn_tgl_nota', e.target.value));
    inpNamaSupplier.addEventListener('change', (e) => {
        saveToLS('stn_nama_supplier', e.target.value);
        setTimeout(() => {
            saveToLS('stn_kode_supplier', inpKodeSupplier.value);
        }, 500);
    });
    inpTglDiserahkan.addEventListener('change', (e) => saveToLS('stn_tgl_diserahkan', e.target.value));
    inpDiberikan.addEventListener('input', (e) => saveToLS('stn_diberikan', e.target.value));
    btnSave.addEventListener("click", handleSave);
    inpNamaSupplier.addEventListener("input", handleSupplierSearch);
    inpSearchTable.addEventListener("input", (e) => {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            currentSearchTerm = e.target.value;
            fetchTableData();
        }, 600);
    });
    filterSort.addEventListener("change", (e) => {
        currentSortOption = e.target.value;
        fetchTableData();
    });
    filterTgl.addEventListener("change", (e) => {
        currentDateFilter = e.target.value;
        fetchTableData();
    });
});