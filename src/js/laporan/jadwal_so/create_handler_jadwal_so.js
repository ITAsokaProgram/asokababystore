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
  dateInput.value = minStr;
  displaySpan.textContent = minStr.split("-").reverse().join("-");
}
function renderCabangList(data) {
  const container = document.getElementById("container-cabang");
  container.innerHTML = "";
  if (!data || data.length === 0) {
    container.innerHTML =
      '<div class="text-gray-500 text-sm p-2">Tidak ada data cabang.</div>';
    return;
  }
  data.forEach((store) => {
    const div = document.createElement("div");
    div.className =
      "flex items-center p-2 hover:bg-blue-50 rounded transition-colors cursor-pointer";
    div.innerHTML = `
            <input type="checkbox" id="store_${store.Kd_Store}" value="${store.Kd_Store}" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 checkbox-store">
            <label for="store_${store.Kd_Store}" class="ml-2 block text-sm text-gray-900 cursor-pointer w-full">
                <span class="font-medium">${store.Kd_Store}</span> - ${store.Nm_Store}
            </label>
        `;
    const checkbox = div.querySelector("input");
    checkbox.addEventListener("change", (e) => {
      if (e.target.checked) {
        state.selectedStores.add(e.target.value);
      } else {
        state.selectedStores.delete(e.target.value);
      }
      handleStoreSelectionChange();
    });
    container.appendChild(div);
  });
}
function renderSupplierList(data) {
  const container = document.getElementById("container-supplier");
  container.innerHTML = "";
  if (!data || data.length === 0) {
    container.innerHTML =
      '<div class="text-gray-500 text-sm p-4 text-center">Tidak ada supplier ditemukan untuk kombinasi cabang ini.</div>';
    return;
  }
  data.forEach((supp) => {
    const div = document.createElement("div");
    div.className =
      "flex items-center p-2 hover:bg-green-50 rounded transition-colors cursor-pointer";
    div.innerHTML = `
            <input type="checkbox" id="supp_${supp.kode_supp}" value="${supp.kode_supp}" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500 checkbox-supp">
            <label for="supp_${supp.kode_supp}" class="ml-2 block text-sm text-gray-900 cursor-pointer w-full">
                <span class="font-bold text-gray-600 w-16 inline-block">${supp.kode_supp}</span> ${supp.nama_supp}
            </label>
        `;
    const checkbox = div.querySelector("input");
    checkbox.addEventListener("change", (e) => {
      if (e.target.checked) {
        state.selectedSuppliers.add(e.target.value);
      } else {
        state.selectedSuppliers.delete(e.target.value);
      }
      updateSupplierCounter();
    });
    container.appendChild(div);
  });
}
function updateSupplierCounter() {
  document.getElementById(
    "supplier-counter"
  ).textContent = `${state.selectedSuppliers.size} supplier dipilih`;
}
async function initPage() {
  setupDateDefaults();
  const loading = document.getElementById("loading-cabang");
  loading.classList.remove("hidden");
  try {
    const response = await fetch(API_URLS.getCabang);
    const result = await response.json();
    if (result.success) {
      renderCabangList(result.data);
    } else {
      Toastify({
        text: "Gagal memuat cabang: " + result.message,
        backgroundColor: "red",
      }).showToast();
    }
  } catch (error) {
    console.error(error);
    Toastify({
      text: "Error koneksi memuat cabang",
      backgroundColor: "red",
    }).showToast();
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
    containerSupplier.innerHTML =
      '<div class="text-center text-gray-400 py-10 text-sm">Silahkan pilih cabang terlebih dahulu</div>';
    return;
  }
  stepSupplier.classList.remove("opacity-50", "pointer-events-none");
  const loading = document.getElementById("loading-supplier");
  loading.classList.remove("hidden");
  try {
    const payload = { store_ids: Array.from(state.selectedStores) };
    const response = await fetch(API_URLS.getSupplier, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    const result = await response.json();
    if (result.success) {
      renderSupplierList(result.data);
    } else {
      Toastify({
        text: "Gagal memuat supplier",
        backgroundColor: "red",
      }).showToast();
    }
  } catch (error) {
    console.error(error);
    Toastify({
      text: "Error fetching suppliers",
      backgroundColor: "red",
    }).showToast();
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
  const confirmMsg = `Anda akan membuat jadwal untuk:\n${
    state.selectedStores.size
  } Cabang\n${state.selectedSuppliers.size} Supplier\n\nTotal: ${
    state.selectedStores.size * state.selectedSuppliers.size
  } Entri Data.`;
  const result = await Swal.fire({
    title: "Konfirmasi",
    text: confirmMsg,
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Ya, Simpan",
    cancelButtonText: "Batal",
  });
  if (result.isConfirmed) {
    const btn = document.getElementById("btn-submit");
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    try {
      const payload = {
        stores: Array.from(state.selectedStores),
        suppliers: Array.from(state.selectedSuppliers),
        tgl_schedule: tglSchedule,
      };
      const token = localStorage.getItem("jwt_token");
      const headers = { "Content-Type": "application/json" };
      if (token) headers["Authorization"] = `Bearer ${token}`;
      const response = await fetch(API_URLS.insertJadwal, {
        method: "POST",
        headers: headers,
        body: JSON.stringify(payload),
      });
      const apiRes = await response.json();
      if (apiRes.success) {
        await Swal.fire("Berhasil!", apiRes.message, "success");
      } else {
        Swal.fire("Gagal", apiRes.message, "error");
      }
    } catch (error) {
      console.error(error);
      Swal.fire("Error", "Terjadi kesalahan server", "error");
    } finally {
      btn.disabled = false;
      btn.innerHTML = originalText;
    }
  }
}
document.addEventListener("DOMContentLoaded", () => {
  initPage();
  document
    .getElementById("btn-select-all-cabang")
    .addEventListener("click", () => {
      document.querySelectorAll(".checkbox-store").forEach((cb) => {
        cb.checked = true;
        state.selectedStores.add(cb.value);
      });
      handleStoreSelectionChange();
    });
  document
    .getElementById("btn-deselect-all-cabang")
    .addEventListener("click", () => {
      document.querySelectorAll(".checkbox-store").forEach((cb) => {
        cb.checked = false;
      });
      state.selectedStores.clear();
      handleStoreSelectionChange();
    });
  document
    .getElementById("btn-select-all-supp")
    .addEventListener("click", () => {
      document.querySelectorAll(".checkbox-supp").forEach((cb) => {
        cb.checked = true;
        state.selectedSuppliers.add(cb.value);
      });
      updateSupplierCounter();
    });
  document
    .getElementById("btn-deselect-all-supp")
    .addEventListener("click", () => {
      document.querySelectorAll(".checkbox-supp").forEach((cb) => {
        cb.checked = false;
      });
      state.selectedSuppliers.clear();
      updateSupplierCounter();
    });
  document
    .getElementById("formJadwalSO")
    .addEventListener("submit", submitJadwal);
});
