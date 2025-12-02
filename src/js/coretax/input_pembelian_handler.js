import { sendRequestGET, sendRequestJSON } from "../utils/api_helpers.js";
const API_URLS = {
  getReceipt: "/src/api/coretax/get_receipt_detail.php",
  saveData: "/src/api/coretax/save_pembelian_single.php",
  getData: "/src/api/coretax/get_latest_pembelian.php",
  checkDuplicate: "/src/api/coretax/check_duplicate_invoice.php",
  getStores: "/src/api/shared/get_all_store.php",
  searchSupplier: "/src/api/coretax/get_supplier_search.php",
  deleteData: "/src/api/coretax/delete_pembelian_single.php",
};
const form = document.getElementById("single-form");
const inpId = document.getElementById("inp_id");
const inpNoLpb = document.getElementById("inp_no_lpb");
const errNoLpb = document.getElementById("err_no_lpb");
const inpKodeStore = document.getElementById("inp_kode_store");
const inpStatus = document.getElementById("inp_status");
const inpNamaSupp = document.getElementById("inp_nama_supplier");
const listSupplier = document.getElementById("supplier_list");
const inpTgl = document.getElementById("inp_tgl_nota");
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
  const ppn = parseNumber(inpPpn.value);

  const total = dpp + ppn;

  inpTotal.value = formatNumber(total);
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
    console.error("Gagal memuat toko:", error);
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
async function checkDuplicateInvoice(noLpb) {
  if (!noLpb) return false;
  const currentId = inpId.value || 0;
  try {
    const result = await sendRequestGET(
      `${API_URLS.checkDuplicate}?no_faktur=${encodeURIComponent(
        noLpb
      )}&exclude_id=${currentId}`
    );
    if (result.exists) {
      inpNoLpb.classList.add("border-red-500", "bg-red-50", "text-red-700");
      inpNoLpb.classList.remove("border-gray-300");
      errNoLpb.textContent = result.message;
      errNoLpb.classList.remove("hidden");
      Toastify({
        text: `⚠️ ${result.message}`,
        duration: 3000,
        style: { background: "#ef4444" },
      }).showToast();
      return true;
    } else {
      resetErrorState();
      return false;
    }
  } catch (error) {
    return false;
  }
}
function resetErrorState() {
  inpNoLpb.classList.remove("border-red-500", "bg-red-50", "text-red-700");
  inpNoLpb.classList.add("border-gray-300");
  errNoLpb.classList.add("hidden");
  errNoLpb.textContent = "";
}
async function fetchReceiptData(noLpb) {
  if (!noLpb) return;
  const isDuplicate = await checkDuplicateInvoice(noLpb);
  inpNoLpb.classList.add("bg-yellow-50", "text-yellow-700");
  const originalPlaceholder = inpNoLpb.placeholder;
  inpNoLpb.placeholder = "Mencari...";
  try {
    const result = await sendRequestGET(
      `${API_URLS.getReceipt}?no_lpb=${encodeURIComponent(noLpb)}`
    );
    if (result.success && result.data) {
      const d = result.data;
      inpNamaSupp.value = d.nama_supplier || "";
      if (d.kode_store) inpKodeStore.value = d.kode_store;
      inpDpp.value = formatNumber(parseFloat(d.dpp) || 0);
      inpPpn.value = formatNumber(parseFloat(d.ppn) || 0);
      calculateTotal();
      if (!isDuplicate) {
        inpNoLpb.classList.remove("bg-yellow-50", "text-yellow-700");
        inpNoLpb.classList.add("bg-green-50", "text-green-700");
        setTimeout(
          () => inpNoLpb.classList.remove("bg-green-50", "text-green-700"),
          1000
        );
      }
      inpNamaSupp.focus();
    } else {
      if (!isDuplicate) {
        inpNoLpb.classList.remove("bg-yellow-50", "text-yellow-700");
        Toastify({
          text: "Info: Data invoice baru (input manual)",
          duration: 2000,
          style: { background: "#3b82f6" },
        }).showToast();
      }
    }
  } catch (error) {
    console.error("Fetch Error", error);
  } finally {
    inpNoLpb.classList.remove("bg-yellow-50", "text-yellow-700");
    if (isDuplicate)
      inpNoLpb.classList.add("border-red-500", "bg-red-50", "text-red-700");
    inpNoLpb.placeholder = originalPlaceholder;
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
    tableBody.innerHTML = `<tr><td colspan="11" class="text-center p-4 text-red-500">Terjadi kesalahan koneksi</td></tr>`;
  }
}
function renderTable(data) {
  if (data.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="11" class="text-center p-6 text-gray-500">Belum ada data</td></tr>`;
    return;
  }
  let html = "";
  data.forEach((row, index) => {
    const dpp = parseFloat(row.dpp);
    const dppLain = parseFloat(row.dpp_nilai_lain || 0);
    const ppn = parseFloat(row.ppn);
    const total = parseFloat(row.total_terima_fp);

    const safeJson = JSON.stringify(row).replace(/"/g, "&quot;");
    let badgeStatus = "";
    if (row.status === "BTKP") {
      badgeStatus =
        '<span class="bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded font-bold border border-purple-200">BTKP</span>';
    } else if (row.status === "NON PKP") {
      badgeStatus =
        '<span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded font-bold border border-gray-200">NON PKP</span>';
    } else {
      badgeStatus =
        '<span class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded font-bold border border-blue-200">PKP</span>';
    }
    html += `
            <tr class="hover:bg-pink-50 transition-colors border-b border-gray-50">
                <td class="text-center text-gray-500 py-3">${index + 1}</td>
                <td class="text-sm">${row.tgl_nota}</td>
                <td class="font-medium text-gray-800 text-sm">${
                  row.no_faktur
                }</td>
                <td><span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded border border-gray-200">${
                  row.nm_alias || "-"
                }</span></td>
                <td class="text-center">${badgeStatus}</td>
                <td class="text-sm truncate max-w-[150px]" title="${
                  row.nama_supplier
                }">${row.nama_supplier}</td>
                <td class="text-right font-mono text-sm">${formatNumber(
                  dpp
                )}</td>
                <td class="text-right font-mono text-gray-500 text-sm">${formatNumber(
                  dppLain
                )}</td>
                <td class="text-right font-mono text-sm">${formatNumber(
                  ppn
                )}</td>
                <td class="text-right font-bold font-mono text-gray-800 text-sm">${formatNumber(
                  total
                )}</td>
                <td class="text-center py-2">
                    <div class="flex justify-center gap-1">
                        <button class="btn-edit-row text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 w-8 h-8 flex items-center justify-center rounded transition-all" 
                            data-row="${safeJson}" title="Edit Data">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button class="btn-delete-row text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 w-8 h-8 flex items-center justify-center rounded transition-all" 
                            data-id="${row.id}" data-invoice="${
      row.no_faktur
    }" title="Hapus Data">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
  });
  tableBody.innerHTML = html;
  document.querySelectorAll(".btn-edit-row").forEach((btn) => {
    btn.addEventListener("click", function () {
      const data = JSON.parse(this.getAttribute("data-row"));
      startEditMode(data);
    });
  });
  document.querySelectorAll(".btn-delete-row").forEach((btn) => {
    btn.addEventListener("click", function () {
      const id = this.getAttribute("data-id");
      const invoice = this.getAttribute("data-invoice");
      handleDelete(id, invoice);
    });
  });
}
function startEditMode(data) {
  resetErrorState();
  inpId.value = data.id;
  inpNoLpb.value = data.no_faktur;
  inpKodeStore.value = data.kode_store || "";

  inpStatus.value = data.status || "PKP";

  inpNamaSupp.value = data.nama_supplier;
  inpTgl.value = data.tgl_nota;
  inpDpp.value = formatNumber(data.dpp);
  inpDppLain.value = formatNumber(data.dpp_nilai_lain || 0);
  inpPpn.value = formatNumber(data.ppn);

  calculateTotal();
  inpNoLpb.focus();
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
  inpTotal.value = "0";
  inpKodeStore.value = "";
  inpStatus.value = "PKP";
  document
    .querySelector(".input-row-container")
    .classList.remove("border-amber-300", "bg-amber-50");
  editIndicator.classList.add("hidden");
  btnCancelEdit.classList.add("hidden");
  btnSave.innerHTML = `<i class="fas fa-save"></i> <span>Simpan</span>`;
  btnSave.className =
    "btn-primary shadow-lg shadow-pink-500/30 flex items-center gap-2 px-6 py-2";
}
function handleDelete(id, invoice) {
  Swal.fire({
    title: "Hapus Data?",
    text: `Anda yakin ingin menghapus Invoice ${invoice}?`,
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
          text: "Sedang mengecek keterkaitan data...",
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
  const noLpb = inpNoLpb.value.trim();
  const namaSupp = inpNamaSupp.value.trim();
  if (inpKodeStore.value === "") {
    Swal.fire("Gagal", "Pilih Cabang", "warning");
    return;
  }
  if (!noLpb || !namaSupp) {
    Swal.fire("Gagal", "No Invoice dan Nama Supplier harus diisi", "warning");
    return;
  }
  if (inpNoLpb.classList.contains("border-red-500")) {
    inpNoLpb.focus();
    return;
  }
  isSubmitting = true;
  const payload = {
    id: inpId.value || null,
    no_lpb: noLpb,
    no_faktur: noLpb,
    kode_store: inpKodeStore.value,

    // Update Payload Status
    status: inpStatus.value,

    nama_supplier: namaSupp,
    tgl_nota: inpTgl.value,
    dpp: parseNumber(inpDpp.value),
    dpp_nilai_lain: parseNumber(inpDppLain.value), // Ambil value manual
    ppn: parseNumber(inpPpn.value),
    total_terima_fp: parseNumber(inpTotal.value),
  };
  const originalBtnContent = btnSave.innerHTML;
  const originalBtnClass = btnSave.className;
  btnSave.disabled = true;
  btnSave.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Proses...`;
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
        showConfirmButton: false,
      });
      cancelEditMode();
      loadTableData();
      inpNoLpb.focus();
    } else {
      throw new Error(result.message || "Gagal menyimpan data");
    }
  } catch (error) {
    console.error("Save Error:", error);
    let errorMessage = error.message || "Terjadi kesalahan sistem";
    if (errorMessage.includes("Duplicate entry")) {
      errorMessage = "Data Duplikat: No Faktur tersebut sudah ada.";
    }
    Swal.fire("Gagal Simpan", errorMessage, "error");
  } finally {
    btnSave.disabled = false;
    isSubmitting = false;
    if (!isSuccess) {
      btnSave.innerHTML = originalBtnContent;
      btnSave.className = originalBtnClass;
    }
  }
}
document.addEventListener("DOMContentLoaded", () => {
  loadStoreOptions();
  loadTableData();
  [inpDpp, inpPpn, inpDppLain].forEach((input) => {
    input.addEventListener("input", () => {
      if (input !== inpDppLain) calculateTotal();
    });
    input.addEventListener("blur", (e) => {
      const val = parseNumber(e.target.value);
      e.target.value = formatNumber(val);
      calculateTotal();
    });
    input.addEventListener("focus", (e) => e.target.select());
  });
  inpNamaSupp.addEventListener("input", handleSupplierSearch);
  inpNoLpb.addEventListener("change", (e) => {
    const val = e.target.value.trim();
    if (val !== "") {
      fetchReceiptData(val);
    } else {
      resetErrorState();
    }
  });
  inpNoLpb.addEventListener("input", () => {
    if (inpNoLpb.classList.contains("border-red-500")) {
      resetErrorState();
    }
  });
  const formInputs = Array.from(
    form.querySelectorAll("input:not([type='hidden']), select")
  );
  formInputs.forEach((input, index) => {
    input.addEventListener("keydown", async (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        if (input.type === "checkbox") return;
        if (input === inpNoLpb) {
          const val = input.value.trim();
          if (val) await fetchReceiptData(val);
          if (inpKodeStore && !inpKodeStore.disabled) {
            inpKodeStore.focus();
            return;
          }
        }
        const isReadyToSave =
          inpNoLpb.value && inpNamaSupp.value && inpKodeStore.value;
        const isLastInput = input.id === "inp_ppn";
        if (isReadyToSave && (isLastInput || e.ctrlKey)) {
          handleSave();
        } else {
          let nextIndex = index + 1;
          let nextInput = formInputs[nextIndex];
          while (
            nextInput &&
            (nextInput.disabled ||
              nextInput.readOnly ||
              nextInput.type === "hidden")
          ) {
            nextIndex++;
            nextInput = formInputs[nextIndex];
          }
          if (nextInput) {
            nextInput.focus();
          } else if (isReadyToSave) {
            handleSave();
          }
        }
      }
    });
  });
  btnSave.addEventListener("click", handleSave);
  btnCancelEdit.addEventListener("click", cancelEditMode);
});
