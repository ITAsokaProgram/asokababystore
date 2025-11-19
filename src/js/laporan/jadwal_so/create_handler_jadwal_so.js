import { sendRequestGET, sendRequestJSON } from "../../utils/api_helpers.js";
const API_URLS = {
  getCabang: "/src/api/laporan/jadwal_so/get_store_data.php",
  getSupplier: "/src/api/laporan/jadwal_so/get_supplier_by_store.php",
  insertJadwal: "/src/api/laporan/jadwal_so/insert_jadwal_so.php",
};
const state = {
  selectedStores: new Set(),
  selectedSuppliers: new Set(),
};
function formatDateInput(date) {
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, "0");
  const d = String(date.getDate()).padStart(2, "0");
  return `${y}-${m}-${d}`;
}
function setupDateDefaults() {
  const dateInput = document.getElementById("tgl_schedule");
  const displaySpan = document.getElementById("default-date-display");
  const today = new Date();
  const minDate = new Date(today);
  minDate.setDate(today.getDate() + 3);
  const minStr = formatDateInput(minDate);
  dateInput.min = minStr;
  if (displaySpan) {
    displaySpan.textContent = minDate.toLocaleDateString("id-ID", {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
    });
  }
}
function renderCabangList(data) {
  const container = document.getElementById("container-cabang");
  container.innerHTML = "";
  if (!data || data.length === 0) {
    container.innerHTML =
      '<div class="text-gray-500 text-xs p-2 text-center">Tidak ada data cabang.</div>';
    return;
  }
  data.forEach((store) => {
    const div = document.createElement("div");
    div.className =
      "flex items-center p-2 hover:bg-pink-50 rounded-md transition-colors cursor-pointer border border-transparent hover:border-pink-100";
    div.innerHTML = `
            <div class="flex items-center h-5">
                <input type="checkbox" id="store_${store.Kd_Store}" value="${store.Kd_Store}" 
                    class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500 checkbox-store cursor-pointer">
            </div>
            <label for="store_${store.Kd_Store}" class="ml-3 text-xs cursor-pointer select-none w-full">
                <span class="font-bold text-gray-800 block">${store.Kd_Store}</span>
                <span class="text-gray-500 text-[10px] uppercase tracking-wide">${store.Nm_Store}</span>
            </label>
        `;
    const checkbox = div.querySelector("input");
    checkbox.addEventListener("change", (e) => {
      if (e.target.checked) {
        state.selectedStores.add(e.target.value);
        div.classList.add("bg-pink-50", "border-pink-200");
      } else {
        state.selectedStores.delete(e.target.value);
        div.classList.remove("bg-pink-50", "border-pink-200");
      }
      handleStoreSelectionChange();
    });
    container.appendChild(div);
  });
}
function renderSupplierList(data) {
  const container = document.getElementById("container-supplier");
  container.innerHTML = "";
  container.classList.remove("bg-gray-50");
  if (!data || data.length === 0) {
    container.innerHTML =
      '<div class="text-gray-500 text-xs p-4 text-center flex flex-col items-center gap-2"><i class="fas fa-search-minus fa-lg"></i> Tidak ada supplier ditemukan.</div>';
    container.classList.add("bg-gray-50");
    return;
  }
  data.forEach((supp) => {
    const div = document.createElement("div");
    div.className =
      "flex items-center p-2 hover:bg-pink-50 rounded-md transition-colors cursor-pointer border border-transparent hover:border-pink-100";
    div.innerHTML = `
            <div class="flex items-center h-5">
                <input type="checkbox" id="supp_${supp.kode_supp}" value="${supp.kode_supp}" 
                    class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500 checkbox-supp cursor-pointer">
            </div>
            <label for="supp_${supp.kode_supp}" class="ml-3 text-xs cursor-pointer select-none w-full">
                <span class="font-bold text-gray-700 mr-1">${supp.kode_supp}</span> 
                <span class="text-gray-600">${supp.nama_supp}</span>
            </label>
        `;
    const checkbox = div.querySelector("input");
    checkbox.addEventListener("change", (e) => {
      if (e.target.checked) {
        state.selectedSuppliers.add(e.target.value);
        div.classList.add("bg-pink-50", "border-pink-200");
      } else {
        state.selectedSuppliers.delete(e.target.value);
        div.classList.remove("bg-pink-50", "border-pink-200");
      }
      updateSupplierCounter();
    });
    container.appendChild(div);
  });
}
function updateSupplierCounter() {
  const count = state.selectedSuppliers.size;
  const counterEl = document.getElementById("supplier-counter");
  counterEl.textContent = `${count} supplier dipilih`;
  if (count > 0) {
    counterEl.classList.remove("badge-warning");
    counterEl.classList.add("badge-success");
  } else {
    counterEl.classList.add("badge-warning");
    counterEl.classList.remove("badge-success");
  }
}
async function initPage() {
  setupDateDefaults();
  const loading = document.getElementById("loading-cabang");
  loading.classList.remove("hidden");
  try {
    const result = await sendRequestGET(API_URLS.getCabang);
    if (result.success) {
      renderCabangList(result.data);
    } else {
      Swal.fire(
        "Error",
        "Gagal memuat cabang: " + (result.message || "Unknown error"),
        "error"
      );
    }
  } catch (error) {
    console.error(error);
    Swal.fire("Error", "Terjadi kesalahan koneksi", "error");
  } finally {
    loading.classList.add("hidden");
  }
}
async function handleStoreSelectionChange() {
  const stepSupplier = document.getElementById("step-supplier");
  const containerSupplier = document.getElementById("container-supplier");
  state.selectedSuppliers.clear();
  updateSupplierCounter();
  if (state.selectedStores.size === 0) {
    stepSupplier.classList.add("opacity-50", "pointer-events-none");
    containerSupplier.classList.add("bg-gray-50");
    containerSupplier.innerHTML = `<div class="flex flex-col items-center justify-center h-full text-gray-400 text-xs gap-2">
          <i class="fas fa-store-slash fa-2x opacity-50"></i>
          <p>Pilih cabang terlebih dahulu</p>
       </div>`;
    return;
  }
  stepSupplier.classList.remove("opacity-50", "pointer-events-none");
  const loading = document.getElementById("loading-supplier");
  containerSupplier.innerHTML = "";
  loading.classList.remove("hidden");
  try {
    const payload = { store_ids: Array.from(state.selectedStores) };
    const result = await sendRequestJSON(API_URLS.getSupplier, payload);
    if (result.success) {
      renderSupplierList(result.data);
    } else {
      Swal.fire("Info", "Gagal memuat supplier: " + result.message, "warning");
    }
  } catch (error) {
    console.error(error);
  } finally {
    loading.classList.add("hidden");
  }
}
async function submitJadwal(e) {
  e.preventDefault();
  const tglSchedule = document.getElementById("tgl_schedule").value;
  if (state.selectedStores.size === 0) {
    Swal.fire("Perhatian", "Pilih minimal 1 Cabang", "warning");
    return;
  }
  if (state.selectedSuppliers.size === 0) {
    Swal.fire("Perhatian", "Pilih minimal 1 Supplier", "warning");
    return;
  }
  if (!tglSchedule) {
    Swal.fire("Perhatian", "Tanggal wajib diisi", "warning");
    return;
  }
  const totalData = state.selectedStores.size * state.selectedSuppliers.size;
  const result = await Swal.fire({
    title: "Konfirmasi Jadwal",
    html: `
        <div class="text-left text-sm text-gray-600 bg-gray-50 p-4 rounded-lg border border-gray-200">
            <div class="flex justify-between mb-1"><span>Cabang:</span> <span class="font-bold text-gray-800">${state.selectedStores.size}</span></div>
            <div class="flex justify-between mb-1"><span>Supplier:</span> <span class="font-bold text-gray-800">${state.selectedSuppliers.size}</span></div>
            <div class="flex justify-between mb-1"><span>Tanggal:</span> <span class="font-bold text-pink-600">${tglSchedule}</span></div>
            <hr class="my-2 border-gray-200">
            <div class="flex justify-between font-bold"><span>Total Data:</span> <span>${totalData}</span></div>
        </div>
        <p class="mt-3 text-sm">Apakah Anda yakin ingin menyimpan jadwal ini?</p>
    `,
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Ya, Simpan",
    cancelButtonText: "Batal",
    confirmButtonColor: "#ec4899",
    cancelButtonColor: "#6b7280",
  });
  if (result.isConfirmed) {
    const btn = document.getElementById("btn-submit");
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML =
      '<i class="fas fa-spinner fa-spin"></i> <span>Menyimpan...</span>';
    try {
      const payload = {
        stores: Array.from(state.selectedStores),
        suppliers: Array.from(state.selectedSuppliers),
        tgl_schedule: tglSchedule,
      };
      const apiRes = await sendRequestJSON(API_URLS.insertJadwal, payload);
      if (apiRes.success) {
        await Swal.fire({
          title: "Berhasil!",
          text: apiRes.message,
          icon: "success",
          confirmButtonColor: "#10b981",
        });
        window.location.href = "index.php";
      } else {
        Swal.fire("Gagal", apiRes.message || "Gagal menyimpan data", "error");
      }
    } catch (error) {
      console.error(error);
      let errMsg = "Terjadi kesalahan saat memproses permintaan";
      if (error && error.message) {
        errMsg = error.message;
      }
      Swal.fire("Error", errMsg, "error");
    } finally {
      btn.disabled = false;
      btn.innerHTML = originalHTML;
    }
  }
}
document.addEventListener("DOMContentLoaded", () => {
  initPage();
  document
    .getElementById("btn-select-all-cabang")
    .addEventListener("click", () => {
      const checkboxes = document.querySelectorAll(".checkbox-store");
      checkboxes.forEach((cb) => {
        cb.checked = true;
        state.selectedStores.add(cb.value);
        cb.closest("div.flex").parentElement.classList.add(
          "bg-pink-50",
          "border-pink-200"
        );
      });
      handleStoreSelectionChange();
    });
  document
    .getElementById("btn-deselect-all-cabang")
    .addEventListener("click", () => {
      const checkboxes = document.querySelectorAll(".checkbox-store");
      checkboxes.forEach((cb) => {
        cb.checked = false;
        cb.closest("div.flex").parentElement.classList.remove(
          "bg-pink-50",
          "border-pink-200"
        );
      });
      state.selectedStores.clear();
      handleStoreSelectionChange();
    });
  document
    .getElementById("btn-select-all-supp")
    .addEventListener("click", () => {
      const checkboxes = document.querySelectorAll(".checkbox-supp");
      checkboxes.forEach((cb) => {
        cb.checked = true;
        state.selectedSuppliers.add(cb.value);
        cb.closest("div.flex").parentElement.classList.add(
          "bg-pink-50",
          "border-pink-200"
        );
      });
      updateSupplierCounter();
    });
  document
    .getElementById("btn-deselect-all-supp")
    .addEventListener("click", () => {
      const checkboxes = document.querySelectorAll(".checkbox-supp");
      checkboxes.forEach((cb) => {
        cb.checked = false;
        cb.closest("div.flex").parentElement.classList.remove(
          "bg-pink-50",
          "border-pink-200"
        );
      });
      state.selectedSuppliers.clear();
      updateSupplierCounter();
    });
  document
    .getElementById("formJadwalSO")
    .addEventListener("submit", submitJadwal);
});
