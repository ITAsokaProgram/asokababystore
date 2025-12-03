import { sendRequestJSON, sendRequestGET } from "../utils/api_helpers.js";
document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("table-body");
  const filterForm = document.getElementById("filter-form");
  const modalForm = document.getElementById("modal-form");
  const btnAddData = document.getElementById("btn-add-data");
  const btnCloseModal = document.getElementById("btn-close-modal");
  const btnCancel = document.getElementById("btn-cancel");
  const formTransaksi = document.getElementById("form-transaksi");
  const displayTotal = document.getElementById("display-total-nominal");
  const inputDenoms = document.querySelectorAll(".input-denim");
  const API_BASE = "/src/api/uang_brangkas";
  const NOMINAL_MAP = {
    qty_100rb: 100000,
    qty_50rb: 50000,
    qty_20rb: 20000,
    qty_10rb: 10000,
    qty_5rb: 5000,
    qty_2rb: 2000,
    qty_1rb: 1000,
    qty_1000_koin: 1000,
    qty_500_koin: 500,
    qty_200_koin: 200,
    qty_100_koin: 100,
  };
  function formatRupiah(number) {
    return new Intl.NumberFormat("id-ID", {
      style: "decimal",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(number);
  }
  async function loadData() {
    tableBody.innerHTML = `<tr><td colspan="7" class="text-center p-8"><div class="spinner-simple"></div></td></tr>`;
    const params = new URLSearchParams(new FormData(filterForm)).toString();
    try {
      const response = await fetch(`${API_BASE}/get_data.php?${params}`);
      const result = await response.json();
      if (result.error) throw new Error(result.error);
      renderTable(result.tabel_data, result.pagination.offset);
    } catch (error) {
      tableBody.innerHTML = `<tr><td colspan="7" class="text-center text-red-500">${error.message}</td></tr>`;
    }
  }
  function renderTable(data, offset) {
    if (!data || data.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="7" class="text-center text-gray-500">Tidak ada data.</td></tr>`;
      return;
    }
    tableBody.innerHTML = data
      .map(
        (row, i) => `
            <tr class="hover:bg-gray-50 border-b border-gray-100">
                <td class="text-center text-sm text-gray-500">${
                  offset + i + 1
                }</td>
                <td class="text-sm text-gray-800">${
                  row.tanggal
                }<br><span class="text-xs text-gray-500">${row.jam}</span></td>
                <td class="text-left text-sm">${row.user_hitung}</td>
                <td class="text-left text-sm">${row.user_cek}</td>
                <td class="text-left text-sm font-bold text-pink-600 pr-4">${formatRupiah(
                  row.total_nominal
                )}</td>
                <td class="text-sm text-gray-600 truncate max-w-xs">${
                  row.keterangan || "-"
                }</td>
                <td class="text-left">
                    <button onclick="window.editBrangkas('${encodeURIComponent(
                      JSON.stringify(row)
                    )}')" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-edit"></i>
                    </button>
                </td>
            </tr>
        `
      )
      .join("");
  }
  function openModal(mode, data = null) {
    formTransaksi.reset();
    document.getElementById("form_mode").value = mode;
    document.getElementById("modal-title").textContent =
      mode === "insert" ? "Input Uang Brangkas" : "Edit Uang Brangkas";
    if (mode === "update" && data) {
      document.getElementById("pk_tanggal").value = data.tanggal;
      document.getElementById("pk_jam").value = data.jam;
      document.getElementById("pk_user_hitung").value = data.user_hitung;
      document.getElementById("user_cek").value = data.user_cek;
      document.getElementById("keterangan").value = data.keterangan;
      for (const [key] of Object.entries(NOMINAL_MAP)) {
        const el = formTransaksi.querySelector(`[name="${key}"]`);
        if (el) el.value = data[key];
      }
    }
    calculateFormTotal();
    modalForm.classList.remove("hidden");
  }
  function calculateFormTotal() {
    let total = 0;
    inputDenoms.forEach((input) => {
      total += (parseInt(input.value) || 0) * (NOMINAL_MAP[input.name] || 0);
    });
    displayTotal.textContent = formatRupiah(total);
  }
  inputDenoms.forEach((inp) =>
    inp.addEventListener("input", calculateFormTotal)
  );
  btnAddData.addEventListener("click", () => openModal("insert"));
  [btnCloseModal, btnCancel].forEach((el) =>
    el.addEventListener("click", () => modalForm.classList.add("hidden"))
  );
  filterForm.addEventListener("submit", (e) => {
    e.preventDefault();
    loadData();
  });
  formTransaksi.addEventListener("submit", async (e) => {
    e.preventDefault();
    const mode = document.getElementById("form_mode").value;
    const endpoint = mode === "insert" ? "/insert.php" : "/update.php";
    const jsonData = {};
    new FormData(formTransaksi).forEach((v, k) => (jsonData[k] = v));
    try {
      const result = await sendRequestJSON(API_BASE + endpoint, jsonData);
      if (result.success) {
        Swal.fire("Berhasil", result.message, "success");
        modalForm.classList.add("hidden");
        loadData();
      } else {
        Swal.fire("Gagal", result.message, "error");
      }
    } catch (error) {
      Swal.fire("Error", error.message, "error");
    }
  });
  window.editBrangkas = (str) =>
    openModal("update", JSON.parse(decodeURIComponent(str)));
  loadData();
});
