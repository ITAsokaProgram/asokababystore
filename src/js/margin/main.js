import { kodeCabang } from "./../kode_cabang/kd.js";
import { paginationMargin } from "./table/pagination.js";
import { fetchFilterMargin, fetchMargin } from "./fetch/get_margin.js";
import { fetchUpdateMargin, fetchBulkUpdateMargin } from "./fetch/post_cek.js";
import { getKeterangan } from "./fetch/get_keterangan.js";
const formatDate = (date) => {
  return date.toISOString().split("T")[0];
};
const init = async () => {
  sessionStorage.removeItem("default_table");
  sessionStorage.removeItem("filter_table");
  sessionStorage.removeItem("search_table");
  await kodeCabang("cabangFilter");
  const selectCabang = document.getElementById("cabangFilter");
  const start = document.getElementById("startDate");
  const end = document.getElementById("endDate");
  const btnFilter = document.getElementById("filter");
  const searchInput = document.getElementById("globalSearch");
  const urlParams = new URLSearchParams(window.location.search);
  const hasFilter = urlParams.has("start") && urlParams.has("end");
  const getActiveBaseKey = () => {
    const currentParams = new URLSearchParams(window.location.search);
    if (currentParams.has("start") && currentParams.has("end")) {
      return "filter_table";
    }
    return "default_table";
  };
  if (hasFilter) {
    start.value = urlParams.get("start");
    end.value = urlParams.get("end");
    if (urlParams.get("cabang")) selectCabang.value = urlParams.get("cabang");
    await fetchFilterMargin(start.value, end.value, selectCabang.value);
    paginationMargin(1, 50, "filter_table");
  } else {
    const today = new Date();
    const yesterday = new Date();
    yesterday.setDate(today.getDate() - 1);
    start.value = formatDate(yesterday);
    end.value = formatDate(today);
    await fetchMargin();
    paginationMargin(1, 50, "default_table");
  }
  if (searchInput) {
    searchInput.addEventListener("keyup", function (e) {
      const keyword = e.target.value.toLowerCase().trim();
      const baseKey = getActiveBaseKey();
      const sessionData = sessionStorage.getItem(baseKey);
      if (!sessionData) return;
      try {
        const parsed = JSON.parse(sessionData);
        const originalData = parsed.data || [];
        if (keyword === "") {
          paginationMargin(1, 50, baseKey);
          return;
        }
        const filteredData = originalData.filter((item) => {
          return (
            item.plu?.toString().toLowerCase().includes(keyword) ||
            item.no_trans?.toLowerCase().includes(keyword) ||
            item.descp?.toLowerCase().includes(keyword) ||
            item.cabang?.toLowerCase().includes(keyword)
          );
        });
        sessionStorage.setItem(
          "search_table",
          JSON.stringify({ data: filteredData })
        );
        paginationMargin(1, 50, "search_table");
      } catch (err) {
        console.error("Error filtering data:", err);
      }
    });
  }
  btnFilter.addEventListener("click", async (e) => {
    const cabangValue = selectCabang.value;
    if (!cabangValue) {
      Toastify({
        text: "Silahkan pilih cabang terlebih dahulu",
        duration: 2000,
        style: { background: "#f59e0b" },
      }).showToast();
      return;
    }
    const params = new URLSearchParams();
    params.set("start", start.value);
    params.set("end", end.value);
    params.set("cabang", cabangValue);
    window.history.pushState(
      {},
      "",
      `${window.location.pathname}?${params.toString()}`
    );
    await fetchFilterMargin(start.value, end.value, cabangValue);
    paginationMargin(1, 50, "filter_table");
  });
  document.addEventListener("click", function (e) {
    const button = e.target.closest(".periksa");
    if (!button) return;
    const currentCabang = selectCabang.value;
    const tipeCek = button.getAttribute("data-type");
    const data = {
      plu: button.getAttribute("data-plu"),
      bon: button.getAttribute("data-bon"),
      barang: button.getAttribute("data-barang"),
      qty: button.getAttribute("data-qty"),
      gros: button.getAttribute("data-gros"),
      net: button.getAttribute("data-net"),
      avg: button.getAttribute("data-avg"),
      ppn: button.getAttribute("data-ppn"),
      margin: button.getAttribute("data-margin"),
      tgl: button.getAttribute("data-tgl"),
      cabang: button.getAttribute("data-cabang"),
      kd: button.getAttribute("data-store"),
      tipe_cek: tipeCek,
    };
    fetchUpdateMargin(data, start.value, end.value, currentCabang);
  });
  document.addEventListener("click", async function (e) {
    const button = e.target.closest(".lihat-keterangan");
    if (!button) return;
    const picName = button.getAttribute("data-pic");
    const picKet = button.getAttribute("data-keterangan");
    const ketEl = document.getElementById("keterangan");
    const namaPIC = document.getElementById("nama_pic");
    const showInformation = document.getElementById("informasi");
    showInformation.classList.remove("hidden");
    namaPIC.textContent = picName;
    ketEl.textContent = picKet || "-";
    document.getElementById("tanggal_cek").textContent = "-";
  });
  const checkAll = document.getElementById("checkAll");
  const btnBulk = document.getElementById("btnBulkUpdate");
  const tableBody = document.getElementById("kategoriTable");
  const toggleBulkButton = () => {
    const checkedBoxes = tableBody.querySelectorAll(".check-item:checked");
    if (checkedBoxes.length > 0) {
      btnBulk.classList.remove("hidden");
      btnBulk.innerHTML = `<i class="fas fa-edit"></i> Update (${checkedBoxes.length}) Item`;
    } else {
      btnBulk.classList.add("hidden");
    }
  };
  if (checkAll) {
    checkAll.addEventListener("change", function () {
      const checkboxes = tableBody.querySelectorAll(".check-item");
      checkboxes.forEach((cb) => (cb.checked = this.checked));
      toggleBulkButton();
    });
  }
  tableBody.addEventListener("change", function (e) {
    if (e.target.classList.contains("check-item")) {
      const allCheckboxes = tableBody.querySelectorAll(".check-item");
      const allChecked = Array.from(allCheckboxes).every((cb) => cb.checked);
      if (checkAll) checkAll.checked = allChecked;
      toggleBulkButton();
    }
  });
  btnBulk.addEventListener("click", function () {
    const checkedBoxes = tableBody.querySelectorAll(".check-item:checked");
    if (checkedBoxes.length === 0) return;
    const itemsToUpdate = [];
    checkedBoxes.forEach((cb) => {
      const data = JSON.parse(cb.value);
      itemsToUpdate.push(data);
    });
    const htmlContent = `
        <div class="flex flex-col gap-4 text-left">
            <div class="bg-blue-50 p-3 rounded text-sm text-blue-700 mb-2">
                <i class="fas fa-info-circle"></i> Update <b>${itemsToUpdate.length}</b> data.
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Update Sebagai:</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="swal-role" value="area" class="w-4 h-4 text-pink-600" checked>
                        <span class="text-sm">Area</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="swal-role" value="leader" class="w-4 h-4 text-pink-600">
                        <span class="text-sm">Leader</span>
                    </label>
                </div>
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
      preConfirm: () => {
        const role = document.querySelector(
          'input[name="swal-role"]:checked'
        ).value;
        const keterangan = document.getElementById("swal-bulk-ket").value;
        const userCheck = document.getElementById("swal-bulk-user").value;
        const passAuth = document.getElementById("swal-bulk-pass").value;
        if (!keterangan || !userCheck || !passAuth) {
          Swal.showValidationMessage("Semua field wajib diisi");
          return false;
        }
        return { keterangan, userCheck, passAuth, tipe_cek: role };
      },
    }).then(async (result) => {
      if (result.isConfirmed) {
        const success = await fetchBulkUpdateMargin(
          itemsToUpdate,
          result.value,
          start.value,
          end.value,
          selectCabang.value
        );
        if (success) {
          if (checkAll) checkAll.checked = false;
          btnBulk.classList.add("hidden");
        }
      }
    });
  });
  window.closeModal = () => {
    document.getElementById("informasi")?.classList.add("hidden");
  };
  const btnCloseDetail = document.getElementById("btnCloseDetail");
  const modalDetail = document.getElementById("detailInvalid");
  if (btnCloseDetail && modalDetail) {
    btnCloseDetail.addEventListener("click", () => {
      modalDetail.classList.add("hidden");
    });
  }
};
init();
