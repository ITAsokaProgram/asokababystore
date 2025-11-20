import { sendRequestGET, sendRequestJSON } from "../utils/api_helpers.js";
const API_URLS = {
  getCabang: "/src/api/laporan/jadwal_so/get_store_data.php",
  insertVoucher: "/src/api/voucher/insert_voucher.php",
};
const state = {
  selectedStores: new Set(),
};
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
      updateStoreCounter();
    });
    container.appendChild(div);
  });
}
function updateStoreCounter() {
  const count = state.selectedStores.size;
  const counterEl = document.getElementById("store-counter");
  counterEl.textContent = `${count} toko dipilih`;
  if (count > 0) {
    counterEl.classList.remove("badge-warning");
    counterEl.classList.add("badge-success");
  } else {
    counterEl.classList.add("badge-warning");
    counterEl.classList.remove("badge-success");
  }
}
async function initPage() {
  const today = new Date().toISOString().split("T")[0];
  document.getElementById("tgl_mulai").value = today;
  const nextMonth = new Date();
  nextMonth.setMonth(nextMonth.getMonth() + 1);
  document.getElementById("tgl_akhir").value = nextMonth
    .toISOString()
    .split("T")[0];
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
document
  .getElementById("nama_voucher_manual")
  .addEventListener("input", function () {
    this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, "");
  });
document.getElementById("pemilik").addEventListener("input", function () {
  this.value = this.value.toUpperCase();
});
async function submitVoucher(e) {
  e.preventDefault();
  const namaManual = document.getElementById("nama_voucher_manual").value;
  const jumlah = document.getElementById("jumlah_voucher").value;
  const nilai = document.getElementById("nilai_voucher").value;
  const tglMulai = document.getElementById("tgl_mulai").value;
  const tglAkhir = document.getElementById("tgl_akhir").value;
  const pemilik = document.getElementById("pemilik").value;
  if (state.selectedStores.size === 0) {
    Swal.fire("Perhatian", "Pilih minimal 1 Cabang Toko", "warning");
    return;
  }
  if (!namaManual) {
    Swal.fire("Perhatian", "Nama Voucher Manual harus diisi", "warning");
    return;
  }
  if (jumlah < 1) {
    Swal.fire("Perhatian", "Jumlah Voucher minimal 1", "warning");
    return;
  }
  if (nilai <= 0) {
    Swal.fire("Perhatian", "Nilai Voucher tidak boleh 0", "warning");
    return;
  }
  const totalToko = state.selectedStores.size;
  const totalVoucherGenerated = totalToko * parseInt(jumlah);
  const result = await Swal.fire({
    title: "Konfirmasi Generate",
    html: `
        <div class="text-left text-sm text-gray-600 bg-gray-50 p-4 rounded-lg border border-gray-200">
            <div class="flex justify-between mb-1"><span>Toko Terpilih:</span> <span class="font-bold text-gray-800">${totalToko}</span></div>
            <div class="flex justify-between mb-1"><span>Qty per Toko:</span> <span class="font-bold text-gray-800">${jumlah}</span></div>
            <div class="flex justify-between mb-1"><span>Nilai:</span> <span class="font-bold text-green-600">Rp ${parseInt(
              nilai
            ).toLocaleString("id-ID")}</span></div>
            <hr class="my-2 border-gray-200">
            <div class="flex justify-between font-bold"><span>Total Generate:</span> <span>${totalVoucherGenerated} baris</span></div>
        </div>
        <p class="mt-3 text-sm">Yakin ingin membuat voucher ini?</p>
    `,
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Ya, Generate",
    cancelButtonText: "Batal",
    confirmButtonColor: "#ec4899",
    cancelButtonColor: "#6b7280",
  });
  if (result.isConfirmed) {
    const btn = document.getElementById("btn-submit");
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML =
      '<i class="fas fa-spinner fa-spin"></i> <span>Memproses...</span>';
    try {
      const payload = {
        stores: Array.from(state.selectedStores),
        nama_manual: namaManual,
        jumlah: parseInt(jumlah),
        nilai: parseInt(nilai),
        tgl_mulai: tglMulai,
        tgl_akhir: tglAkhir,
        pemilik: pemilik,
      };
      const apiRes = await sendRequestJSON(API_URLS.insertVoucher, payload);
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
      Swal.fire("Error", error.message || "Terjadi kesalahan sistem", "error");
    } finally {
      btn.disabled = false;
      btn.innerHTML = originalHTML;
    }
  }
}
document.addEventListener("DOMContentLoaded", () => {
  console.log("TEST");
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
      updateStoreCounter();
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
      updateStoreCounter();
    });
  document
    .getElementById("formVoucher")
    .addEventListener("submit", submitVoucher);
});
