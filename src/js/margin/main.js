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

  await kodeCabang("cabangFilter");

  const selectCabang = document.getElementById("cabangFilter");
  const start = document.getElementById("startDate");
  const end = document.getElementById("endDate");
  const btnFilter = document.getElementById("filter");

  // 1. Cek URL Params untuk State Management
  const urlParams = new URLSearchParams(window.location.search);
  const hasFilter = urlParams.has("start") && urlParams.has("end");

  if (hasFilter) {
    start.value = urlParams.get("start");
    end.value = urlParams.get("end");
    if (urlParams.get("cabang")) selectCabang.value = urlParams.get("cabang");

    await fetchFilterMargin(start.value, end.value, selectCabang.value);
    paginationMargin(1, 50, "filter_table");
  } else {
    // Default dates (Kemarin & Hari ini)
    const today = new Date();
    const yesterday = new Date();
    yesterday.setDate(today.getDate() - 1);

    start.value = formatDate(yesterday);
    end.value = formatDate(today);

    await fetchMargin();
    paginationMargin(1, 50, "default_table");
  }

  // 2. Event Filter Click
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

    // Update URL
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

  paginationMargin(1, 50, "default_table");

  // 3. Logic Single Check (Event Delegation)
  document.addEventListener("click", function (e) {
    const button = e.target.closest(".checking");
    if (!button) return;
    const currentCabang = selectCabang.value;
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
      nama: sessionStorage.getItem("userName"),
      kd: button.getAttribute("data-store"),
    };
    // Fungsi ini sekarang akan memunculkan popup otorisasi
    fetchUpdateMargin(data, start.value, end.value, currentCabang);
  });

  // 4. Logic Lihat Keterangan
  document.addEventListener("click", async function (e) {
    const button = e.target.closest(".lihat-keterangan");
    if (!button) return;

    const plu = button.getAttribute("data-plu");
    const bon = button.getAttribute("data-bon");
    const kodeCabangAttr = button.getAttribute("data-cabang");

    const ket = document.getElementById("keterangan");
    const namaPIC = document.getElementById("nama_pic");
    const tanggalCek = document.getElementById("tanggal_cek");
    const showInformation = document.getElementById("informasi");

    // Tampilkan loading di modal
    ket.textContent = "Loading...";
    showInformation.classList.remove("hidden");

    const keterangan = await getKeterangan(plu, bon, kodeCabangAttr);

    ket.textContent = keterangan?.data?.[0]?.ket_cek || "-";
    namaPIC.textContent = keterangan?.data?.[0]?.nama_cek || "-";
    tanggalCek.textContent = keterangan?.data?.[0]?.tanggal_cek
      ? new Date(keterangan.data[0].tanggal_cek).toLocaleDateString("id-ID", {
          day: "2-digit",
          month: "long",
          year: "numeric",
        })
      : "-";
  });

  // 5. Logic Bulk Update (Checkbox)
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
      // Value checkbox adalah JSON string dari data item
      const data = JSON.parse(cb.value);
      data.nama = sessionStorage.getItem("userName");
      itemsToUpdate.push(data);
    });

    // Form HTML untuk Bulk Update (sama dengan Single Update)
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
        const success = await fetchBulkUpdateMargin(
          itemsToUpdate,
          result.value, // Mengirim object otorisasi
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
};

init();
