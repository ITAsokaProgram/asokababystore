import {
  fetchKategoriByTgl,
  fetchDetailKategori,
  fetchCekData,
  fetchBulkCekData,
  fetchKeterangan,
} from "../fetch/all_kategori.js";
import { openDetailModal } from "./all_kategori.js";
import { paginationKat, paginationDetail } from "./pagination.js";
let start = "",
  end = "";
let selectValuePeriode = "";
let selectValueCabang = "";
const formatDate = (date) => {
  const d = new Date(date);
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const yyyy = d.getFullYear();
  return `${yyyy}-${mm}-${dd}`;
};
export const filterByTanggal = () => {
  const periodeSelect = document.getElementById("periodeFilter");
  const startDateInput = document.getElementById("startDate");
  const endDateInput = document.getElementById("endDate");
  const kategoriSelect = document.getElementById("kategori");
  const cabangSelect = document.getElementById("cabangFilter");
  if (!startDateInput.value) {
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(today.getDate() - 1);
    startDateInput.value = formatDate(yesterday);
    endDateInput.value = formatDate(today);
  }
  periodeSelect.addEventListener("change", () => {
    const today = new Date();
    const value = periodeSelect.value;
    switch (value) {
      case "harian":
        const yesterday = new Date();
        yesterday.setDate(today.getDate() - 1);
        start = formatDate(yesterday);
        end = formatDate(today);
        break;
      case "mingguan":
        const weekStart = new Date(today);
        weekStart.setDate(today.getDate() - 6);
        start = formatDate(weekStart);
        end = formatDate(today);
        break;
      case "bulanan":
        const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
        const monthEnd = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        start = formatDate(monthStart);
        end = formatDate(monthEnd);
        break;
      case "tahunan":
        const yearStart = new Date(today.getFullYear(), 0, 1);
        const yearEnd = new Date(today.getFullYear(), 11, 31);
        start = formatDate(yearStart);
        end = formatDate(yearEnd);
        break;
      default:
        start = "";
        end = "";
        break;
    }
    if (start && end) {
      startDateInput.value = start;
      endDateInput.value = end;
    }
  });
  const btn = document.getElementById("filterTanggalBtn");
  btn.addEventListener("click", async (e) => {
    e.preventDefault();
    const startVal = startDateInput.value;
    const endVal = endDateInput.value;
    const kategoriValue = kategoriSelect.value;
    const periodeVal = periodeSelect.value;
    const cabangVal = cabangSelect.value;
    const params = new URLSearchParams();
    params.set("start", startVal);
    params.set("end", endVal);
    params.set("kategori", kategoriValue);
    if (periodeVal) params.set("periode", periodeVal);
    if (cabangVal) params.set("cabang", cabangVal);
    window.history.pushState(
      {},
      "",
      `${window.location.pathname}?${params.toString()}`
    );
    const data = await fetchKategoriByTgl(
      startVal,
      endVal,
      kategoriValue,
      periodeVal,
      cabangVal
    );
    paginationKat(1, 10, "kategori_by_tanggal");
    initSearchFilter("kategori_by_tanggal", "kategori_search_tanggal");
  });
  document
    .getElementById("allTable")
    .addEventListener("click", async function (e) {
      const btn = e.target.closest(".lihat-detail");
      if (!btn) return;
      const kode = btn.getAttribute("data-kode");
      const kategori = btn.getAttribute("data-kat");
      const split = kategori.split(" ")[0];
      const likeKategori = `%${split}%`;
      const cabangVal = document.getElementById("cabangFilter").value;
      if (kode && kategori) {
        openDetailModal();
        await fetchDetailKategori(
          likeKategori,
          kode,
          startDateInput.value,
          endDateInput.value,
          cabangVal
        );
        paginationDetail(1, 100, "detail_kategori");
      }
    });
  const modalDetail = document.getElementById("detailInvalid");
  const checkAll = document.getElementById("checkAllDetail");
  const btnBulk = document.getElementById("btnBulkUpdate");
  function toggleBulkButton() {
    const checkedBoxes = modalDetail.querySelectorAll(
      ".check-detail-item:checked"
    );
    if (checkedBoxes.length > 0) {
      btnBulk.classList.remove("hidden");
      btnBulk.innerHTML = `<i class="fas fa-edit"></i> Update (${checkedBoxes.length}) Item`;
    } else {
      btnBulk.classList.add("hidden");
    }
  }
  if (checkAll) {
    checkAll.addEventListener("change", function () {
      const checkboxes = modalDetail.querySelectorAll(".check-detail-item");
      checkboxes.forEach((cb) => (cb.checked = this.checked));
      toggleBulkButton();
    });
  }
  modalDetail.addEventListener("change", function (e) {
    if (e.target.classList.contains("check-detail-item")) {
      const allCheckboxes = modalDetail.querySelectorAll(".check-detail-item");
      const allChecked = Array.from(allCheckboxes).every((cb) => cb.checked);
      if (checkAll) checkAll.checked = allChecked;
      toggleBulkButton();
    }
  });
  if (btnBulk) {
    btnBulk.addEventListener("click", function () {
      const checkedBoxes = modalDetail.querySelectorAll(
        ".check-detail-item:checked"
      );
      if (checkedBoxes.length === 0) return;
      const itemsToUpdate = [];
      let kodeSample = "";
      checkedBoxes.forEach((cb) => {
        const data = JSON.parse(cb.value);
        itemsToUpdate.push(data);
        kodeSample = data.kasir;
      });
      let kategoriSample = "";
      const sessionDetail = JSON.parse(
        sessionStorage.getItem("detail_kategori") || "{}"
      );
      const dataRaw = sessionDetail.data || [];
      if (dataRaw.length > 0) {
        const rawKat = dataRaw[0].kategori.split(" ")[0];
        kategoriSample = `%${rawKat}%`;
      }
      const htmlContent = `
        <div class="flex flex-col gap-4 text-left">
            <div class="bg-blue-50 p-3 rounded text-sm text-blue-700 mb-2">
                <i class="fas fa-info-circle"></i> Anda akan mengupdate <b>${itemsToUpdate.length}</b> data sekaligus.
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Keterangan (Massal)</label>
                <input id="swal-bulk-ket" class="swal2-input !m-0 !w-full" placeholder="Keterangan update massal">
            </div>
            <div class="p-3 bg-red-50 border border-red-100 rounded-lg">
                <h4 class="text-xs font-bold text-red-600 mb-2 border-b border-red-200 pb-1">OTORISASI USER CHECK</h4>
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">User Check (Inisial)</label>
                    <input id="swal-bulk-user" class="swal2-input !m-0 !w-full !h-10 !text-sm" placeholder="Contoh: ADM" autocomplete="off">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Kode Otorisasi</label>
                    <input type="password" id="swal-bulk-pass" class="swal2-input !m-0 !w-full !h-10 !text-sm" placeholder="Password Otorisasi">
                </div>
            </div>
        </div>
      `;
      Swal.fire({
        title: "Bulk Update Checking",
        html: htmlContent,
        showCancelButton: true,
        confirmButtonText: "Update Semua",
        confirmButtonColor: "#db2777",
        cancelButtonText: "Batal",
        focusConfirm: false,
        preConfirm: () => {
          const keterangan = document.getElementById("swal-bulk-ket").value;
          const userCheck = document.getElementById("swal-bulk-user").value;
          const passAuth = document.getElementById("swal-bulk-pass").value;
          if (!keterangan) {
            Swal.showValidationMessage("Keterangan tidak boleh kosong");
            return false;
          }
          if (!userCheck) {
            Swal.showValidationMessage("Nama User Check wajib diisi");
            return false;
          }
          if (!passAuth) {
            Swal.showValidationMessage("Kode Otorisasi wajib diisi");
            return false;
          }
          return { keterangan, userCheck, passAuth };
        },
      }).then(async (result) => {
        if (result.isConfirmed) {
          const success = await fetchBulkCekData(
            itemsToUpdate,
            result.value,
            kategoriSample,
            kodeSample,
            startDateInput.value,
            endDateInput.value
          );
          if (success) {
            paginationDetail(1, 100, "detail_kategori");
            if (checkAll) checkAll.checked = false;
            toggleBulkButton();
          }
        }
      });
    });
  }
  document.addEventListener("click", async function (e) {
    const button = e.target.closest(".periksa");
    if (!button) return;
    const kode = button.getAttribute("data-kode");
    const kategori = button.getAttribute("data-kat");
    const data = {
      nama: sessionStorage.getItem("userName"),
      kasir: button.getAttribute("data-kode"),
      plu: button.getAttribute("data-barcode"),
      tgl: button.getAttribute("data-tglU"),
      jam: button.getAttribute("data-jam"),
      kd_store: button.getAttribute("data-toko"),
    };
    await fetchCekData(
      data,
      kategori,
      kode,
      startDateInput.value,
      endDateInput.value
    );
  });
  document.addEventListener("click", async function (e) {
    const button = e.target.closest(".lihat-keterangan");
    const showInformation = document.getElementById("informasi");
    const namaPIC = document.getElementById("nama_pic");
    const keterangan = document.getElementById("ketM");
    if (!button) return;
    const data = {
      kasir: button.getAttribute("data-kode"),
      plu: button.getAttribute("data-barcode"),
      tgl: button.getAttribute("data-tglU"),
      jam: button.getAttribute("data-jam"),
      cabang: button.getAttribute("data-toko"),
    };
    const ket = await fetchKeterangan(
      data.plu,
      data.kasir,
      data.tgl,
      data.jam,
      data.cabang
    );
    showInformation.classList.remove("hidden");
    namaPIC.textContent = ket.data[0].nama_cek;
    keterangan.textContent = ket.data[0].ket_cek;
  });
};
const btnCloseInfo = document.getElementById("btnCloseInformasi");
if (btnCloseInfo) {
  btnCloseInfo.addEventListener("click", () => {
    document.getElementById("informasi").classList.add("hidden");
  });
}
const searchFilter = (options) => {
  const {
    inputId = "search",
    sessionKey = "kategori_invalid",
    searchField = "kode",
    outputKey = "kategori_search",
    renderFunction = paginationKat,
  } = options;
  const search = document.getElementById(inputId);
  if (!search) return;
  search.addEventListener("input", () => {
    const keyword = search.value.trim().toLowerCase();
    const session = sessionStorage.getItem(sessionKey);
    if (!session) return;
    try {
      const parsed = JSON.parse(session);
      const data = parsed.data;
      const filtered = data.filter((item) => {
        const field = item[searchField];
        return field?.toString().toLowerCase().includes(keyword);
      });
      sessionStorage.setItem(outputKey, JSON.stringify({ data: filtered }));
      renderFunction(1, 10, outputKey);
    } catch (err) {
      console.error("Error parsing sessionStorage data:", err);
    }
  });
};
export const initSearchFilter = (mode = "kategori_invalid", output) => {
  const sessionKey = mode;
  const outputKey = output;
  searchFilter({
    inputId: "search",
    sessionKey: sessionKey,
    searchField: "kode",
    outputKey: outputKey,
    renderFunction: paginationKat,
  });
};
export default { initSearchFilter, filterByTanggal };
