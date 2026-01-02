import { sendRequestJSON, sendRequestGET } from "../utils/api_helpers.js";

document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("table-body");
  const filterForm = document.getElementById("filter-form");
  const modalForm = document.getElementById("modal-form");
  const formTransaksi = document.getElementById("form-transaksi");

  // Selectors
  const btnAddData = document.getElementById("btn-add-data");
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

  // --- [UPDATE] Modifikasi Fungsi Populate ---
  function populateStoreDropdowns(stores) {
    // 1. Ambil param dari URL jika ada
    const urlParams = new URLSearchParams(window.location.search);
    const urlKdStore = urlParams.get('kd_store');

    if (filterStoreSelect) {
      const currentFilterVal = filterStoreSelect.value;

      filterStoreSelect.innerHTML = '<option value="" disabled selected>-- Pilih Cabang --</option>';
      stores.forEach((s) =>
        filterStoreSelect.add(
          new Option(`${s.kd_store} - ${s.nm_alias}`, s.kd_store)
        )
      );

      // Prioritas 1: Ambil dari URL jika ada dan cocok dengan opsi
      if (urlKdStore) {
        // Cek apakah value dari URL valid (ada di dalam list options)
        const optionExists = Array.from(filterStoreSelect.options).some(opt => opt.value === urlKdStore);
        if (optionExists) {
          filterStoreSelect.value = urlKdStore;
        }
      }
      // Prioritas 2: Ambil dari value sebelumnya (jika reload JS internal)
      else if (currentFilterVal) {
        filterStoreSelect.value = currentFilterVal;
      }
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

  function openModal(mode, data = null) {
    formTransaksi.reset();
    document.getElementById("form_mode").value = mode;
    const allInputs = formTransaksi.querySelectorAll("input, textarea, select");

    if (mode === "insert") {
      document.getElementById("modal-title").textContent = "Input Uang Brangkas";
      allInputs.forEach((el) => (el.disabled = false));
      divPassword.style.display = "block";
      btnSave.style.display = "inline-flex";
    } else if (mode === "detail") {
      document.getElementById("modal-title").textContent = "Detail Uang Brangkas";
      if (data) {
        if (modalStoreSelect) modalStoreSelect.value = data.kd_store;
        document.getElementById("nama_user_cek").value = data.nama_user_cek_inisial || "";
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

  if (btnAddData) {
    btnAddData.addEventListener("click", () => openModal("insert"));
  }

  [btnCloseModal, btnCancel].forEach((el) => {
    if (el) el.addEventListener("click", () => modalForm.classList.add("hidden"));
  });

  filterForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const selectedStore = filterStoreSelect.value;
    if (!selectedStore || selectedStore === "") {
      Swal.fire({
        icon: 'warning',
        title: 'Pilih Cabang',
        text: 'Harap pilih cabang terlebih dahulu untuk menampilkan data.',
        confirmButtonColor: '#db2777'
      });
      return;
    }

    // --- [NEW] Update URL Params saat submit ---
    const params = new URLSearchParams(new FormData(filterForm));
    const newUrl = `${window.location.pathname}?${params.toString()}`;
    window.history.pushState({}, '', newUrl); // Ubah URL tanpa reload

    loadData(false);
  });

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

        if (filterStoreSelect.value) {
          loadData(false);
        }
      } else {
        Swal.fire("Gagal", result.message, "error");
      }
    } catch (error) {
      Swal.fire("Error", "Gagal menyimpan data", "error");
    }
  });

  window.viewBrangkas = (str) =>
    openModal("detail", JSON.parse(decodeURIComponent(str)));

  // --- [UPDATE] Load Data ---
  async function loadData(isInitial = false) {
    const token = getCookie("admin_token");

    // Gunakan FormData dari form, ini otomatis mengambil value input date & select
    const formData = new FormData(filterForm);
    const params = new URLSearchParams(formData).toString();

    try {
      const response = await fetch(`${API_BASE}/get_data.php?${params}`, {
        headers: { Authorization: "Bearer " + token },
      });
      const result = await response.json();

      if (result.stores) populateStoreDropdowns(result.stores);

      // --- [NEW] Logika Auto Load jika ada URL Params ---
      if (isInitial) {
        // Cek apakah setelah populateStoreDropdowns, filterStoreSelect punya value (dari URL)
        if (filterStoreSelect.value && filterStoreSelect.value !== "") {
          // Jika ada value dari URL, kita paksa load data ulang (recursive)
          // tapi kali ini sebagai 'non-initial' agar tabel dirender
          loadData(false);
          return;
        }
        // Jika tidak ada value di URL, stop rendering (biarkan placeholder)
        return;
      }

      renderTable(result.tabel_data, result.pagination.offset);

    } catch (error) {
      console.error("Load Error:", error);
    }
  }

  function renderTable(data, offset) {
    if (!data || data.length === 0) {
      if (!filterStoreSelect.value) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center p-8">
                    <div class="text-gray-400 mb-2"><i class="fas fa-store fa-3x"></i></div>
                    <p class="text-gray-500 font-medium">Silahkan pilih cabang dan klik Tampilkan</p>
                </td>
            </tr>`;
      } else {
        tableBody.innerHTML = `<tr><td colspan="8" class="text-center p-4">Tidak ada data ditemukan untuk periode ini.</td></tr>`;
      }
      return;
    }

    tableBody.innerHTML = data
      .map(
        (row, i) => `
        <tr class="hover:bg-gray-50 border-b">
            <td class="text-sm">
                <span class="font-medium text-gray-800">${row.tanggal}</span><br>
                <span class="text-xs text-gray-400">${row.jam}</span>
            </td>
            <td class="text-sm">
                <span class="px-2 py-1 bg-pink-50 text-pink-700 rounded-md font-bold text-xs">
                    ${row.display_store ?? "-"}
                </span>
            </td>
            <td class="text-sm text-gray-700">${row.nama_user_hitung}</td>
            <td class="text-sm text-gray-700">${row.nama_user_cek}</td>
            <td class="text-sm font-bold text-pink-600">Rp ${formatRupiah(row.total_nominal)}</td>
            <td class="text-sm text-gray-500 italic">${row.keterangan || "-"}</td>
            <td class="text-center">
                <button onclick="window.viewBrangkas('${encodeURIComponent(JSON.stringify(row))}')" 
                        class="text-gray-400 hover:text-pink-600 transition-colors">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        </tr>
    `).join("");
  }

  // Load Awal
  loadData(true);
});