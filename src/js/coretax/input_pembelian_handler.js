import { sendRequestGET, sendRequestJSON } from "../utils/api_helpers.js";
const API_URLS = {
  getReceipt: "/src/api/coretax/get_receipt_detail.php",
  saveData: "/src/api/coretax/save_pembelian_single.php",
  getData: "/src/api/coretax/get_latest_pembelian.php",
};
const form = document.getElementById("single-form");
const inpId = document.getElementById("inp_id");
const inpNoLpb = document.getElementById("inp_no_lpb");
const inpKodeSupp = document.getElementById("inp_kode_supplier");
const inpNamaSupp = document.getElementById("inp_nama_supplier");
const inpTgl = document.getElementById("inp_tgl_nota");
const inpDpp = document.getElementById("inp_dpp");
const inpPpn = document.getElementById("inp_ppn");
const inpTotal = document.getElementById("inp_total");
const btnSave = document.getElementById("btn-save");
const btnCancelEdit = document.getElementById("btn-cancel-edit");
const editIndicator = document.getElementById("edit-mode-indicator");
const tableBody = document.getElementById("table-body");
let isSubmitting = false;
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
async function fetchReceiptData(noLpb) {
  if (!noLpb) return;
  inpNoLpb.classList.add("bg-yellow-50", "text-yellow-700");
  const originalPlaceholder = inpNoLpb.placeholder;
  inpNoLpb.placeholder = "Mencari...";
  try {
    const result = await sendRequestGET(
      `${API_URLS.getReceipt}?no_lpb=${encodeURIComponent(noLpb)}`
    );
    if (result.success && result.data) {
      const d = result.data;
      inpKodeSupp.value = d.kode_supplier || "";
      inpDpp.value = formatNumber(parseFloat(d.dpp) || 0);
      inpPpn.value = formatNumber(parseFloat(d.ppn) || 0);
      calculateTotal();
      inpNoLpb.classList.remove("bg-yellow-50", "text-yellow-700");
      inpNoLpb.classList.add("bg-green-50", "text-green-700");
      setTimeout(
        () => inpNoLpb.classList.remove("bg-green-50", "text-green-700"),
        1000
      );
      inpNamaSupp.focus();
    } else {
      inpNoLpb.classList.remove("bg-yellow-50", "text-yellow-700");
      const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
      });
      Toast.fire({
        icon: "info",
        title: "Data tidak ditemukan, silakan input manual",
      });
    }
  } catch (error) {
    console.error("Fetch Receipt Error:", error);
  } finally {
    inpNoLpb.classList.remove("bg-yellow-50", "text-yellow-700");
    inpNoLpb.placeholder = originalPlaceholder;
  }
}
async function loadTableData() {
  tableBody.innerHTML = `<tr><td colspan="8" class="text-center p-4"><i class="fas fa-spinner fa-spin text-pink-500"></i> Memuat data...</td></tr>`;
  try {
    const result = await sendRequestGET(API_URLS.getData);
    if (result.success && Array.isArray(result.data)) {
      renderTable(result.data);
    } else {
      tableBody.innerHTML = `<tr><td colspan="8" class="text-center p-4 text-red-500">Gagal memuat data</td></tr>`;
    }
  } catch (error) {
    console.error("Load Table Error:", error);
    tableBody.innerHTML = `<tr><td colspan="8" class="text-center p-4 text-red-500">Terjadi kesalahan koneksi</td></tr>`;
  }
}
function renderTable(data) {
  if (data.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="8" class="text-center p-6 text-gray-500">Belum ada data</td></tr>`;
    return;
  }
  let html = "";
  data.forEach((row, index) => {
    const dpp = parseFloat(row.dpp);
    const ppn = parseFloat(row.ppn);
    const total = parseFloat(row.total_terima_fp);
    const safeJson = JSON.stringify(row).replace(/"/g, "&quot;");
    html += `
            <tr class="hover:bg-pink-50 transition-colors">
                <td class="text-center text-gray-500">${index + 1}</td>
                <td>${row.tgl_nota}</td>
                <td class="font-medium text-gray-800">${row.no_faktur}</td>
                <td class="text-sm">${row.nama_supplier}</td>
                <td class="text-right font-mono">${formatNumber(dpp)}</td>
                <td class="text-right font-mono">${formatNumber(ppn)}</td>
                <td class="text-right font-bold font-mono text-gray-800">${formatNumber(
                  total
                )}</td>
                <td class="text-center">
                    <button class="btn-edit-row text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 p-2 rounded transition-all" 
                            data-row="${safeJson}" title="Edit Data">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
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
}
function startEditMode(data) {
  inpId.value = data.id;
  inpNoLpb.value = data.no_faktur;
  inpKodeSupp.value = data.kode_supplier;
  inpNamaSupp.value = data.nama_supplier;
  inpTgl.value = data.tgl_nota;
  inpDpp.value = formatNumber(data.dpp);
  inpPpn.value = formatNumber(data.ppn);
  calculateTotal();
  inpNoLpb.focus();
  window.scrollTo({
    top: 0,
    behavior: "smooth",
  });
  document
    .querySelector(".input-row-container")
    .classList.add("border-amber-300", "bg-amber-50");
  editIndicator.classList.remove("hidden");
  btnCancelEdit.classList.remove("hidden");
  btnSave.innerHTML = `<i class="fas fa-sync-alt"></i> <span>Update Data</span>`;
  btnSave.className = "btn-warning";
}
function cancelEditMode() {
  form.reset();
  inpId.value = "";
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
async function handleSave() {
  const noLpb = inpNoLpb.value.trim();
  const namaSupp = inpNamaSupp.value.trim();
  if (!noLpb || !namaSupp) {
    Swal.fire("Gagal", "No Invoice dan Nama Supplier harus diisi", "warning");
    return;
  }
  isSubmitting = true;
  const payload = {
    id: inpId.value || null,
    no_lpb: noLpb,
    no_faktur: noLpb,
    kode_supplier: inpKodeSupp.value,
    nama_supplier: namaSupp,
    tgl_nota: inpTgl.value,
    dpp: parseNumber(inpDpp.value),
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
      errorMessage = "Data Duplikat: No Faktur tersebut sudah ada di database.";
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
  loadTableData();
  [inpDpp, inpPpn].forEach((input) => {
    input.addEventListener("input", calculateTotal);
    input.addEventListener("blur", (e) => {
      const val = parseNumber(e.target.value);
      e.target.value = formatNumber(val);
      calculateTotal();
    });
    input.addEventListener("focus", (e) => e.target.select());
  });
  inpNoLpb.addEventListener("change", (e) => {
    if (e.target.value.trim() !== "") {
      fetchReceiptData(e.target.value);
    }
  });
  const formInputs = Array.from(
    form.querySelectorAll("input:not([type='hidden'])")
  );
  formInputs.forEach((input, index) => {
    input.addEventListener("keydown", async (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        if (input === inpNoLpb) {
          const val = input.value.trim();
          if (val) {
            await fetchReceiptData(val);
          }
          if (inpNamaSupp) inpNamaSupp.focus();
          return;
        }
        const isReadyToSave = inpNoLpb.value && inpNamaSupp.value;
        const isLastInput = input.id === "inp_ppn";
        if (isReadyToSave && (isLastInput || e.ctrlKey)) {
          handleSave();
        } else {
          const nextInput = formInputs[index + 1];
          if (nextInput && !nextInput.readOnly) {
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
  window.addEventListener("beforeunload", function (e) {
    const hasData =
      inpNoLpb.value.trim() !== "" ||
      inpNamaSupp.value.trim() !== "" ||
      parseNumber(inpDpp.value) > 0;
    if (hasData && !isSubmitting) {
      e.preventDefault();
      e.returnValue = "";
      return "";
    }
  });
});
