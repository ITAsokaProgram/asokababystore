import { sendRequestGET, sendRequestJSON } from "../utils/api_helpers.js";
const API_URLS = {
  saveData: "/src/api/coretax/save_faktur_pajak.php",
  getData: "/src/api/coretax/get_latest_faktur_pajak.php",
  checkDuplicate: "/src/api/coretax/check_duplicate_fp.php",
};
const form = document.getElementById("fp-form");
const inpId = document.getElementById("inp_id");
const inpNoSeri = document.getElementById("inp_no_seri");
const errNoSeri = document.getElementById("err_no_seri");
const inpNamaSupp = document.getElementById("inp_nama_supplier");
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
  const dppLain = parseNumber(inpDppLain.value);
  const ppn = parseNumber(inpPpn.value);
  const total = dpp + dppLain + ppn;
  inpTotal.value = formatNumber(total);
}
async function checkDuplicateFP(noSeri) {
  if (!noSeri) return false;
  const currentId = inpId.value || 0;
  try {
    const result = await sendRequestGET(
      `${API_URLS.checkDuplicate}?no_seri_fp=${encodeURIComponent(
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
        duration: 3000,
        gravity: "top",
        position: "center",
        style: {
          background: "#ef4444",
          color: "#fff",
          boxShadow: "0 10px 15px -3px rgb(0 0 0 / 0.1)",
          borderRadius: "8px",
          fontWeight: "bold",
        },
      }).showToast();
      return true;
    } else {
      resetErrorState();
      return false;
    }
  } catch (error) {
    console.error("Check Duplicate Error:", error);
    return false;
  }
}
function resetErrorState() {
  inpNoSeri.classList.remove("border-red-500", "bg-red-50", "text-red-700");
  inpNoSeri.classList.add("border-gray-300");
  errNoSeri.classList.add("hidden");
  errNoSeri.textContent = "";
}
async function loadTableData() {
  tableBody.innerHTML = `<tr><td colspan="9" class="text-center p-4"><i class="fas fa-spinner fa-spin text-pink-500"></i> Memuat data...</td></tr>`;
  try {
    const result = await sendRequestGET(API_URLS.getData);
    if (result.success && Array.isArray(result.data)) {
      renderTable(result.data);
    } else {
      tableBody.innerHTML = `<tr><td colspan="9" class="text-center p-4 text-red-500">Gagal memuat data</td></tr>`;
    }
  } catch (error) {
    console.error("Load Table Error:", error);
    tableBody.innerHTML = `<tr><td colspan="9" class="text-center p-4 text-red-500">Terjadi kesalahan koneksi</td></tr>`;
  }
}
function renderTable(data) {
  if (data.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="9" class="text-center p-6 text-gray-500">Belum ada data</td></tr>`;
    return;
  }
  let html = "";
  data.forEach((row, index) => {
    const dpp = parseFloat(row.dpp);
    const dppLain = parseFloat(row.dpp_nilai_lain);
    const ppn = parseFloat(row.ppn);
    const total = parseFloat(row.total);
    const safeJson = JSON.stringify(row).replace(/"/g, "&quot;");
    html += `
            <tr class="hover:bg-pink-50 transition-colors">
                <td class="text-center text-gray-500">${index + 1}</td>
                <td>${row.tgl_faktur || "-"}</td>
                <td class="font-medium text-gray-800">${row.no_seri_fp}</td>
                <td class="text-sm">${row.nama_supplier || "-"}</td>
                <td class="text-right font-mono">${formatNumber(dpp)}</td>
                <td class="text-right font-mono">${formatNumber(dppLain)}</td>
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
  resetErrorState();
  inpId.value = data.id;
  inpNoSeri.value = data.no_seri_fp;
  inpNamaSupp.value = data.nama_supplier;
  inpTgl.value = data.tgl_faktur;
  inpDpp.value = formatNumber(data.dpp);
  inpDppLain.value = formatNumber(data.dpp_nilai_lain);
  inpPpn.value = formatNumber(data.ppn);
  calculateTotal();
  inpNoSeri.focus();
  window.scrollTo({
    top: 0,
    behavior: "smooth",
  });
  document
    .querySelector(".input-row-container")
    .classList.add("border-amber-300", "bg-amber-50");
  editIndicator.classList.remove("hidden");
  btnCancelEdit.classList.remove("hidden");
  btnSave.innerHTML = `<i class="fas fa-sync-alt"></i> <span>Update</span>`;
  btnSave.className = "btn-warning";
}
function cancelEditMode() {
  form.reset();
  resetErrorState();
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
  const noSeri = inpNoSeri.value.trim();
  const namaSupp = inpNamaSupp.value.trim();
  if (!noSeri) {
    Swal.fire("Gagal", "No Seri Faktur Pajak harus diisi", "warning");
    return;
  }
  if (inpNoSeri.classList.contains("border-red-500")) {
    Toastify({
      text: "⚠️ Mohon perbaiki data duplikat sebelum menyimpan.",
      style: { background: "#ef4444" },
      duration: 3000,
    }).showToast();
    inpNoSeri.focus();
    return;
  }
  isSubmitting = true;
  const payload = {
    id: inpId.value || null,
    no_seri_fp: noSeri,
    nama_supplier: namaSupp,
    tgl_faktur: inpTgl.value,
    dpp: parseNumber(inpDpp.value),
    dpp_nilai_lain: parseNumber(inpDppLain.value),
    ppn: parseNumber(inpPpn.value),
    total: parseNumber(inpTotal.value),
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
      inpNoSeri.focus();
    } else {
      throw new Error(result.message || "Gagal menyimpan data");
    }
  } catch (error) {
    console.error("Save Error:", error);
    let errorMessage = error.message || "Terjadi kesalahan sistem";
    if (errorMessage.includes("Duplicate entry")) {
      errorMessage = "Data Duplikat: No Seri Faktur tersebut sudah ada.";
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
  [inpDpp, inpDppLain, inpPpn].forEach((input) => {
    input.addEventListener("input", calculateTotal);
    input.addEventListener("blur", (e) => {
      const val = parseNumber(e.target.value);
      e.target.value = formatNumber(val);
      calculateTotal();
    });
    input.addEventListener("focus", (e) => e.target.select());
  });
  inpNoSeri.addEventListener("change", (e) => {
    checkDuplicateFP(e.target.value.trim());
  });
  inpNoSeri.addEventListener("input", () => {
    if (inpNoSeri.classList.contains("border-red-500")) {
      resetErrorState();
    }
  });
  const formInputs = Array.from(
    form.querySelectorAll("input:not([type='hidden'])")
  );
  formInputs.forEach((input, index) => {
    input.addEventListener("keydown", async (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        if (input === inpNoSeri) {
          const val = input.value.trim();
          if (val) await checkDuplicateFP(val);
          if (inpNamaSupp) inpNamaSupp.focus();
          return;
        }
        const isReadyToSave = inpNoSeri.value;
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
});
