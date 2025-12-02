import { sendRequestGET, sendRequestJSON } from "../utils/api_helpers.js";
const API_URLS = {
  saveData: "/src/api/coretax/save_faktur_pajak.php",
  getData: "/src/api/coretax/get_latest_faktur_pajak.php",
  checkDuplicate: "/src/api/coretax/check_duplicate_fp.php",
  checkDuplicateInv: "/src/api/coretax/check_duplicate_invoice_fp.php",
  getCoretax: "/src/api/coretax/get_coretax_detail.php",
  searchSupplier: "/src/api/coretax/get_supplier_search.php",
  getStores: "/src/api/shared/get_all_store.php",
  getReceipt: "/src/api/coretax/get_receipt_detail.php",
  deleteData: "/src/api/coretax/delete_faktur_pajak.php",
};
const form = document.getElementById("fp-form");
const inpId = document.getElementById("inp_id");
const inpKodeStore = document.getElementById("inp_kode_store");
const inpNoInvoice = document.getElementById("inp_no_invoice");
const errNoInvoice = document.getElementById("err_no_invoice");
const inpNoSeri = document.getElementById("inp_no_seri");
const errNoSeri = document.getElementById("err_no_seri");
const inpNamaSupp = document.getElementById("inp_nama_supplier");
const listSupplier = document.getElementById("supplier_list");
const inpTgl = document.getElementById("inp_tgl_faktur");
const inpDpp = document.getElementById("inp_dpp");
const inpDppLain = document.getElementById("inp_dpp_lain");
const inpPpn = document.getElementById("inp_ppn");
const inpTotal = document.getElementById("inp_total");
const btnSave = document.getElementById("btn-save");
const btnCancelEdit = document.getElementById("btn-cancel-edit");
const editIndicator = document.getElementById("edit-mode-indicator");
const tableBody = document.getElementById("table-body");
let isSubmitting = false;
let debounceTimer;
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
function calculateTotal() {
  const dpp = parseNumber(inpDpp.value);
  const hitungDppLain = Math.round((dpp * 11) / 12);
  inpDppLain.value = formatNumber(hitungDppLain);
  const dppLain = hitungDppLain;
  const ppn = parseNumber(inpPpn.value);
  inpTotal.value = formatNumber(dpp + dppLain + ppn);
}
function resetErrorState() {
  inpNoSeri.classList.remove("border-red-500", "bg-red-50", "text-red-700");
  inpNoSeri.classList.add("border-gray-300");
  errNoSeri.classList.add("hidden");
  errNoSeri.textContent = "";
  if (errNoInvoice) {
    inpNoInvoice.classList.remove(
      "border-red-500",
      "bg-red-50",
      "text-red-700"
    );
    inpNoInvoice.classList.add("border-gray-300");
    errNoInvoice.classList.add("hidden");
    errNoInvoice.textContent = "";
  }
}
async function loadStoreOptions() {
  try {
    const result = await sendRequestGET(API_URLS.getStores);
    if (result.success && Array.isArray(result.data)) {
      let html = '<option value="">Pilih Toko...</option>';
      result.data.forEach((store) => {
        const displayName = store.Nm_Alias
          ? `${store.Nm_Alias} (${store.Kd_Store})`
          : store.Nm_Store;
        html += `<option value="${store.Kd_Store}">${displayName}</option>`;
      });
      inpKodeStore.innerHTML = html;
    }
  } catch (error) {
    console.error(error);
  }
}
async function handleSupplierSearch(e) {
  const term = e.target.value;
  if (term.length < 2) return;
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(async () => {
    try {
      const result = await sendRequestGET(
        `${API_URLS.searchSupplier}?term=${encodeURIComponent(term)}`
      );
      if (result.success && Array.isArray(result.data)) {
        let options = "";
        result.data.forEach((name) => {
          options += `<option value="${name}">`;
        });
        listSupplier.innerHTML = options;
      }
    } catch (err) {
      console.error(err);
    }
  }, 300);
}
async function checkDuplicateFP(noSeri) {
  if (!noSeri) return false;
  const currentId = inpId.value || 0;
  try {
    const result = await sendRequestGET(
      `${API_URLS.checkDuplicate}?nsfp=${encodeURIComponent(
        noSeri
      )}&exclude_id=${currentId}`
    );
    if (result.exists) {
      inpNoSeri.classList.add("border-red-500", "bg-red-50", "text-red-700");
      inpNoSeri.classList.remove("border-gray-300");
      errNoSeri.textContent = result.message;
      errNoSeri.classList.remove("hidden");
      Toastify({
        text: `⚠️ ${result.message}`,
        style: { background: "#ef4444" },
      }).showToast();
      return true;
    } else {
      inpNoSeri.classList.remove("border-red-500", "bg-red-50", "text-red-700");
      inpNoSeri.classList.add("border-gray-300");
      errNoSeri.classList.add("hidden");
      return false;
    }
  } catch (error) {
    return false;
  }
}
async function checkDuplicateInvoiceFunc(noInvoice) {
  if (!noInvoice) return false;
  const currentId = inpId.value || 0;
  try {
    const result = await sendRequestGET(
      `${API_URLS.checkDuplicateInv}?no_invoice=${encodeURIComponent(
        noInvoice
      )}&exclude_id=${currentId}`
    );
    if (result.exists) {
      inpNoInvoice.classList.add("border-red-500", "bg-red-50", "text-red-700");
      inpNoInvoice.classList.remove("border-gray-300");
      if (errNoInvoice) {
        errNoInvoice.textContent = result.message;
        errNoInvoice.classList.remove("hidden");
      }
      Toastify({
        text: `⚠️ ${result.message}`,
        duration: 3000,
        style: { background: "#ef4444" },
      }).showToast();
      return true;
    } else {
      inpNoInvoice.classList.remove(
        "border-red-500",
        "bg-red-50",
        "text-red-700"
      );
      inpNoInvoice.classList.add("border-gray-300");
      if (errNoInvoice) errNoInvoice.classList.add("hidden");
      return false;
    }
  } catch (error) {
    console.error("Check Duplicate Inv Error:", error);
    return false;
  }
}
async function fetchCoretaxData(nsfp) {
  if (!nsfp) return;
  if (await checkDuplicateFP(nsfp)) return;
  inpNoSeri.classList.add("bg-yellow-50", "text-yellow-700");
  const originalPlaceholder = inpNoSeri.placeholder;
  inpNoSeri.placeholder = "Mencari di Coretax...";
  try {
    const result = await sendRequestGET(
      `${API_URLS.getCoretax}?nsfp=${encodeURIComponent(nsfp)}`
    );
    if (result.success && result.found) {
      const d = result.data;
      inpNamaSupp.value = d.nama_supplier || "";
      if (d.kode_store) inpKodeStore.value = d.kode_store;
      inpDpp.value = formatNumber(d.dpp);
      inpDppLain.value = formatNumber(d.dpp_nilai_lain);
      inpPpn.value = formatNumber(d.ppn);
      calculateTotal();
      inpNoSeri.classList.remove("bg-yellow-50", "text-yellow-700");
      inpNoSeri.classList.add("bg-green-50", "text-green-700");
      Toastify({
        text: "✅ Data ditemukan di Coretax",
        duration: 2000,
        style: { background: "#10b981" },
      }).showToast();
      setTimeout(
        () => inpNoSeri.classList.remove("bg-green-50", "text-green-700"),
        1000
      );
      inpNamaSupp.focus();
    }
  } catch (error) {
    console.error(error);
  } finally {
    inpNoSeri.classList.remove("bg-yellow-50", "text-yellow-700");
    inpNoSeri.placeholder = originalPlaceholder;
  }
}
async function fetchReceiptData(noInvoice) {
  if (!noInvoice) return;
  const isDuplicate = await checkDuplicateInvoiceFunc(noInvoice);
  if (isDuplicate) return;
  inpNoInvoice.classList.add("bg-yellow-50", "text-yellow-700");
  const originalPlaceholder = inpNoInvoice.placeholder;
  inpNoInvoice.placeholder = "Mencari...";
  try {
    const result = await sendRequestGET(
      `${API_URLS.getReceipt}?no_lpb=${encodeURIComponent(noInvoice)}`
    );
    if (result.success && result.data) {
      const d = result.data;
      inpNamaSupp.value = d.nama_supplier || "";
      if (d.kode_store) {
        inpKodeStore.value = d.kode_store;
      }
      inpDpp.value = formatNumber(d.dpp);
      inpPpn.value = formatNumber(d.ppn);
      calculateTotal();
      inpNoInvoice.classList.remove("bg-yellow-50", "text-yellow-700");
      inpNoInvoice.classList.add("bg-green-50", "text-green-700");
      Toastify({
        text: "✅ Data Invoice ditemukan",
        duration: 2000,
        style: { background: "#10b981" },
      }).showToast();
      setTimeout(
        () => inpNoInvoice.classList.remove("bg-green-50", "text-green-700"),
        1000
      );
      if (!inpNoSeri.value) inpNoSeri.focus();
    } else {
      Toastify({
        text: "ℹ️ Data Invoice tidak ditemukan (Input Manual)",
        duration: 2000,
        style: { background: "#3b82f6" },
      }).showToast();
    }
  } catch (error) {
    console.error("Fetch Receipt Error:", error);
  } finally {
    inpNoInvoice.classList.remove("bg-yellow-50", "text-yellow-700");
    inpNoInvoice.placeholder = originalPlaceholder;
  }
}
async function loadTableData() {
  tableBody.innerHTML = `<tr><td colspan="11" class="text-center p-4"><i class="fas fa-spinner fa-spin text-pink-500"></i> Memuat data...</td></tr>`;
  try {
    const result = await sendRequestGET(API_URLS.getData);
    if (result.success && Array.isArray(result.data)) {
      renderTable(result.data);
    } else {
      tableBody.innerHTML = `<tr><td colspan="11" class="text-center p-4 text-red-500">Gagal memuat data</td></tr>`;
    }
  } catch (error) {
    tableBody.innerHTML = `<tr><td colspan="11" class="text-center p-4 text-red-500">Koneksi Error</td></tr>`;
  }
}
function renderTable(data) {
  if (data.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="11" class="text-center p-6 text-gray-500">Belum ada data</td></tr>`;
    return;
  }
  let html = "";
  data.forEach((row, index) => {
    const safeJson = JSON.stringify(row).replace(/"/g, "&quot;");
    const storeBadge = row.nm_alias
      ? `<span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded border border-gray-200">${row.nm_alias}</span>`
      : `<span class="text-gray-400">-</span>`;
    html += `
            <tr class="hover:bg-pink-50 transition-colors border-b border-gray-50">
                <td class="text-center text-gray-500 py-3">${index + 1}</td>
                <td class="text-sm">${row.tgl_faktur || "-"}</td>
                <td class="font-medium text-gray-800 text-sm">${
                  row.no_faktur || "-"
                }</td> 
                <td class="text-sm text-gray-600">${row.nsfp}</td>
                <td class="text-center">${storeBadge}</td>
                <td class="text-sm truncate max-w-[150px]" title="${
                  row.nama_supplier
                }">${row.nama_supplier || "-"}</td>
                <td class="text-right font-mono text-sm">${formatNumber(
                  row.dpp
                )}</td>
                <td class="text-right font-mono text-gray-500 text-sm">${formatNumber(
                  row.dpp_nilai_lain
                )}</td>
                <td class="text-right font-mono text-sm">${formatNumber(
                  row.ppn
                )}</td>
                <td class="text-right font-bold font-mono text-gray-800 text-sm">${formatNumber(
                  row.total
                )}</td>
                <td class="text-center py-2">
                    <div class="flex justify-center gap-1">
                        <button class="btn-edit-row text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 w-8 h-8 flex items-center justify-center rounded transition-all" 
                            data-row="${safeJson}" title="Edit Data">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button class="btn-delete-row text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 w-8 h-8 flex items-center justify-center rounded transition-all" 
                            data-id="${row.id}" data-nsfp="${
      row.nsfp
    }" data-invoice="${row.no_faktur || "-"}" title="Hapus Data">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
  });
  tableBody.innerHTML = html;
  document.querySelectorAll(".btn-edit-row").forEach((btn) => {
    btn.addEventListener("click", function () {
      startEditMode(JSON.parse(this.getAttribute("data-row")));
    });
  });
  document.querySelectorAll(".btn-delete-row").forEach((btn) => {
    btn.addEventListener("click", function () {
      const id = this.getAttribute("data-id");
      const invoice = this.getAttribute("data-invoice");
      const nsfp = this.getAttribute("data-nsfp");
      handleDelete(id, invoice, nsfp);
    });
  });
}
function startEditMode(data) {
  resetErrorState();
  inpId.value = data.id;
  inpNoSeri.value = data.nsfp;
  inpNoInvoice.value = data.no_faktur || "";
  inpNamaSupp.value = data.nama_supplier;
  inpKodeStore.value = data.kode_store || "";
  inpTgl.value = data.tgl_faktur;
  inpDpp.value = formatNumber(data.dpp);
  inpDppLain.value = formatNumber(data.dpp_nilai_lain);
  inpPpn.value = formatNumber(data.ppn);
  calculateTotal();
  inpNoInvoice.focus();
  window.scrollTo({ top: 0, behavior: "smooth" });
  document
    .querySelector(".input-row-container")
    .classList.add("border-amber-300", "bg-amber-50");
  editIndicator.classList.remove("hidden");
  btnCancelEdit.classList.remove("hidden");
  btnSave.innerHTML = `<i class="fas fa-sync-alt"></i> <span>Update</span>`;
  btnSave.className =
    "btn-warning px-6 py-2 rounded shadow-lg bg-amber-500 text-white hover:bg-amber-600";
}
function cancelEditMode() {
  form.reset();
  resetErrorState();
  inpId.value = "";
  inpKodeStore.value = "";
  inpTotal.value = "0";
  document
    .querySelector(".input-row-container")
    .classList.remove("border-amber-300", "bg-amber-50");
  editIndicator.classList.add("hidden");
  btnCancelEdit.classList.add("hidden");
  btnSave.innerHTML = `<i class="fas fa-save"></i> <span>Simpan</span>`;
  btnSave.className =
    "btn-primary shadow-lg shadow-pink-500/30 flex items-center gap-2 px-6 py-2";
}
function handleDelete(id, invoice, nsfp) {
  Swal.fire({
    title: "Hapus Faktur Pajak?",
    text: `Anda yakin ingin menghapus Invoice ${invoice} (NSFP: ${nsfp})?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#ef4444",
    cancelButtonColor: "#6b7280",
    confirmButtonText: "Ya, Hapus!",
    cancelButtonText: "Batal",
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        Swal.fire({
          title: "Memproses...",
          text: "Sedang mengecek validasi data...",
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          },
        });
        const resp = await sendRequestJSON(API_URLS.deleteData, { id: id });
        if (resp.success) {
          Swal.fire("Terhapus!", resp.message, "success");
          loadTableData();
          if (inpId.value == id) cancelEditMode();
        } else {
          throw new Error(resp.message || "Gagal menghapus data");
        }
      } catch (error) {
        console.error("Delete Error:", error);
        const errorMsg = error.message || "Terjadi kesalahan sistem";
        Swal.fire("Gagal", errorMsg, "error");
      }
    }
  });
}
async function handleSave() {
  if (inpKodeStore.value === "") {
    Swal.fire("Gagal", "Pilih Cabang", "warning");
    return;
  }
  const noSeri = inpNoSeri.value.trim();
  const noInvoice = inpNoInvoice.value.trim();
  const tglFaktur = inpTgl.value;
  if (!noSeri) {
    Swal.fire("Gagal", "NSFP wajib diisi", "warning");
    return;
  }
  if (!noInvoice) {
    Swal.fire("Gagal", "No Invoice wajib diisi", "warning");
    return;
  }
  if (!tglFaktur) {
    Swal.fire("Gagal", "Tanggal Faktur wajib diisi", "warning");
    try {
      inpTgl.showPicker();
    } catch (e) {
      inpTgl.focus();
    }
    return;
  }
  if (inpNoSeri.classList.contains("border-red-500")) {
    inpNoSeri.focus();
    return;
  }
  if (inpNoSeri.classList.contains("border-red-500")) {
    inpNoSeri.focus();
    return;
  }
  if (inpNoInvoice.classList.contains("border-red-500")) {
    inpNoInvoice.focus();
    return;
  }
  isSubmitting = true;
  const payload = {
    id: inpId.value || null,
    nsfp: noSeri,
    no_invoice: noInvoice,
    nama_supplier: inpNamaSupp.value.trim(),
    kode_store: inpKodeStore.value,
    tgl_faktur: inpTgl.value,
    dpp: parseNumber(inpDpp.value),
    dpp_nilai_lain: parseNumber(inpDppLain.value),
    ppn: parseNumber(inpPpn.value),
    total: parseNumber(inpTotal.value),
  };
  const originalBtn = btnSave.innerHTML;
  const originalClass = btnSave.className;
  btnSave.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Proses...`;
  btnSave.disabled = true;
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
      loadTableData();
      inpNoInvoice.focus();
    } else {
      throw new Error(result.message);
    }
  } catch (error) {
    let msg = error.message;
    if (msg.includes("Duplicate entry") || msg.includes("duplikat")) {
      if (msg.includes("Invoice") || msg.includes("no_faktur")) {
        msg = "Gagal: Nomor Invoice sudah digunakan.";
      } else if (msg.includes("NSFP") || msg.includes("nsfp")) {
        msg = "Gagal: NSFP sudah digunakan.";
      }
    }
    Swal.fire("Gagal", msg, "error");
  } finally {
    btnSave.disabled = false;
    btnSave.innerHTML = originalBtn;
    btnSave.className = originalClass;
    isSubmitting = false;
  }
}
document.addEventListener("DOMContentLoaded", () => {
  loadStoreOptions();
  loadTableData();
  [inpDpp, inpPpn].forEach((input) => {
    input.addEventListener("input", calculateTotal);
    input.addEventListener("blur", (e) => {
      e.target.value = formatNumber(parseNumber(e.target.value));
      calculateTotal();
    });
    input.addEventListener("focus", (e) => e.target.select());
  });
  inpNamaSupp.addEventListener("input", handleSupplierSearch);
  inpNoInvoice.addEventListener("change", (e) => {
    const val = e.target.value.trim();
    if (val) {
      fetchReceiptData(val);
    } else {
      if (inpNoInvoice.classList.contains("border-red-500")) {
        inpNoInvoice.classList.remove(
          "border-red-500",
          "bg-red-50",
          "text-red-700"
        );
        inpNoInvoice.classList.add("border-gray-300");
        if (errNoInvoice) errNoInvoice.classList.add("hidden");
      }
    }
  });
  inpNoInvoice.addEventListener("input", () => {
    if (inpNoInvoice.classList.contains("border-red-500")) {
      inpNoInvoice.classList.remove(
        "border-red-500",
        "bg-red-50",
        "text-red-700"
      );
      inpNoInvoice.classList.add("border-gray-300");
      if (errNoInvoice) errNoInvoice.classList.add("hidden");
    }
  });
  inpNoSeri.addEventListener("change", async (e) => {
    const val = e.target.value.trim();
    if (val) await fetchCoretaxData(val);
    else resetErrorState();
  });
  inpNoSeri.addEventListener("input", () => {
    if (inpNoSeri.classList.contains("border-red-500")) resetErrorState();
  });
  const formInputs = Array.from(
    form.querySelectorAll("input:not([type='hidden']), select")
  );
  formInputs.forEach((input, index) => {
    input.addEventListener("keydown", async (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        if (input === inpNoInvoice) {
          const val = input.value.trim();
          if (val) await fetchReceiptData(val);
          if (!inpNoInvoice.classList.contains("border-red-500")) {
            inpNoSeri.focus();
          }
          return;
        }
        if (input === inpNoSeri) {
          const val = input.value.trim();
          if (val) await fetchCoretaxData(val);
          if (inpNamaSupp) inpNamaSupp.focus();
          return;
        }
        const isReady =
          inpNoSeri.value && inpNamaSupp.value && inpNoInvoice.value;
        const isLast = input.id === "inp_ppn";
        if (isReady && (isLast || e.ctrlKey)) {
          handleSave();
        } else {
          let nextIndex = index + 1;
          let nextInput = formInputs[nextIndex];
          while (nextInput && (nextInput.disabled || nextInput.readOnly)) {
            nextIndex++;
            nextInput = formInputs[nextIndex];
          }
          if (nextInput) {
            nextInput.focus();
          }
        }
      }
    });
  });
  btnSave.addEventListener("click", handleSave);
  btnCancelEdit.addEventListener("click", cancelEditMode);
});
