import { sendRequestJSON, sendRequestGET } from "../utils/api_helpers.js";

document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("table-body");
  const filterForm = document.getElementById("filter-form");
  const modalForm = document.getElementById("modal-form");
  const formTransaksi = document.getElementById("form-transaksi");

  // Selectors untuk Modal & Filter Cabang
  const btnAddData = document.getElementById("btn-add-data"); // TOMBOL YANG BERMASALAH
  const btnCloseModal = document.getElementById("btn-close-modal");
  const btnCancel = document.getElementById("btn-cancel");
  const btnSave = document.getElementById("btn-save");

  const modalStoreSelect = document.getElementById("modal_kd_store");
  const filterStoreSelect = document.getElementById("kd_store");

  const displayTotal = document.getElementById("display-total-nominal");
  const inputDenoms = document.querySelectorAll(".input-denim");
  const divPassword = document.getElementById("div-password");

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

  function getCookie(name) {
    let nameEQ = name + "=";
    let ca = document.cookie.split(";");
    for (let i = 0; i < ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) == " ") c = c.substring(1, c.length);
      if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
  }

  function formatRupiah(number) {
    return new Intl.NumberFormat("id-ID").format(number);
  }

  // Fungsi untuk mengisi dropdown cabang
  function populateStoreDropdowns(stores) {
    if (filterStoreSelect) {
      const currentFilterVal = filterStoreSelect.value;
      filterStoreSelect.innerHTML =
        '<option value="all">Seluruh Store</option>';
      stores.forEach((s) =>
        filterStoreSelect.add(
          new Option(`${s.kd_store} - ${s.nm_alias}`, s.kd_store)
        )
      );
      if (currentFilterVal) filterStoreSelect.value = currentFilterVal;
    }

    if (modalStoreSelect) {
      modalStoreSelect.innerHTML = '<option value="">-- Pilih Toko --</option>';
      stores.forEach((s) =>
        modalStoreSelect.add(
          new Option(`${s.kd_store} - ${s.nm_alias}`, s.kd_store)
        )
      );
    }
  }

  // Fungsi buka modal (Mode Insert atau Detail)
  function openModal(mode, data = null) {
    formTransaksi.reset();
    document.getElementById("form_mode").value = mode;
    const allInputs = formTransaksi.querySelectorAll("input, textarea, select");

    if (mode === "insert") {
      document.getElementById("modal-title").textContent =
        "Input Uang Brangkas";
      allInputs.forEach((el) => (el.disabled = false));
      divPassword.style.display = "block";
      btnSave.style.display = "inline-flex";
    } else if (mode === "detail") {
      document.getElementById("modal-title").textContent =
        "Detail Uang Brangkas";
      if (data) {
        if (modalStoreSelect) modalStoreSelect.value = data.kd_store;
        document.getElementById("nama_user_cek").value =
          data.nama_user_cek_inisial || "";
        document.getElementById("keterangan").value = data.keterangan;
        Object.keys(NOMINAL_MAP).forEach((key) => {
          const el = formTransaksi.querySelector(`[name="${key}"]`);
          if (el) el.value = data[key];
        });
      }
      allInputs.forEach((el) => (el.disabled = true));
      divPassword.style.display = "none";
      btnSave.style.display = "none";
    }
    calculateFormTotal();
    modalForm.classList.remove("hidden");
  }

  // --- EVENT LISTENERS ---

  // 1. Klik Tombol Input Baru (PENTING)
  if (btnAddData) {
    btnAddData.addEventListener("click", () => openModal("insert"));
  }

  // 2. Tutup Modal
  [btnCloseModal, btnCancel].forEach((el) => {
    if (el)
      el.addEventListener("click", () => modalForm.classList.add("hidden"));
  });

  // 3. Filter Form Submit
  filterForm.addEventListener("submit", (e) => {
    e.preventDefault();
    loadData();
  });

  // 4. Kalkulasi Otomatis saat input angka
  function calculateFormTotal() {
    let total = 0;
    inputDenoms.forEach(
      (inp) =>
        (total += (parseInt(inp.value) || 0) * (NOMINAL_MAP[inp.name] || 0))
    );
    displayTotal.textContent = "Rp " + formatRupiah(total);
  }
  inputDenoms.forEach((inp) =>
    inp.addEventListener("input", calculateFormTotal)
  );

  // 5. Simpan Data (Submit Modal)
  formTransaksi.addEventListener("submit", async (e) => {
    e.preventDefault();
    const mode = document.getElementById("form_mode").value;
    if (mode === "detail") return;

    const token = getCookie("admin_token");
    const jsonData = {};
    new FormData(formTransaksi).forEach((v, k) => (jsonData[k] = v));

    try {
      const response = await fetch(`${API_BASE}/insert.php`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: "Bearer " + token,
        },
        body: JSON.stringify(jsonData),
      });
      const result = await response.json();
      if (result.success) {
        Swal.fire("Berhasil", result.message, "success");
        modalForm.classList.add("hidden");
        loadData();
      } else {
        Swal.fire("Gagal", result.message, "error");
      }
    } catch (error) {
      Swal.fire("Error", "Gagal menyimpan data", "error");
    }
  });

  // 6. Global Function untuk Lihat Detail (Eye Icon)
  window.viewBrangkas = (str) =>
    openModal("detail", JSON.parse(decodeURIComponent(str)));

  // Fungsi Ambil Data dari API
  async function loadData() {
    const token = getCookie("admin_token");
    const params = new URLSearchParams(new FormData(filterForm)).toString();

    try {
      const response = await fetch(`${API_BASE}/get_data.php?${params}`, {
        headers: { Authorization: "Bearer " + token },
      });
      const result = await response.json();

      if (result.stores) populateStoreDropdowns(result.stores);
      renderTable(result.tabel_data, result.pagination.offset);
    } catch (error) {
      console.error("Load Error:", error);
    }
  }
  // Cari fungsi renderTable dan ubah bagian kolom cabangnya
  function renderTable(data, offset) {
    if (!data || data.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="8" class="text-center p-4">Tidak ada data.</td></tr>`;
      return;
    }
    tableBody.innerHTML = data
      .map(
        (row, i) => `
        <tr class="hover:bg-gray-50 border-b">
            <td class="text-sm">
                <span class="font-medium text-gray-800">${
                  row.tanggal
                }</span><br>
                <span class="text-xs text-gray-400">${row.jam}</span>
            </td>
            <td class="text-sm">
                <span class="px-2 py-1 bg-pink-50 text-pink-700 rounded-md font-bold text-xs">
                    ${row.display_store ?? "-"}
                </span>
            </td>
            <td class="text-sm text-gray-700">${row.nama_user_hitung}</td>
            <td class="text-sm text-gray-700">${row.nama_user_cek}</td>
            <td class="text-sm font-bold text-pink-600">Rp ${formatRupiah(
              row.total_nominal
            )}</td>
            <td class="text-sm text-gray-500 italic">${
              row.keterangan || "-"
            }</td>
            <td class="text-center">
                <button onclick="window.viewBrangkas('${encodeURIComponent(
                  JSON.stringify(row)
                )}')" 
                        class="text-gray-400 hover:text-pink-600 transition-colors">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        </tr>
    `
      )
      .join("");
  }
  // Jalankan load data pertama kali
  loadData();
});
