document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("table-body");
  const filterForm = document.getElementById("filter-form");
  const paginationInfo = document.getElementById("pagination-info");
  const paginationLinks = document.getElementById("pagination-links");
  const modalForm = document.getElementById("modal-form");
  const btnAddData = document.getElementById("btn-add-data");
  const btnCloseModal = document.getElementById("btn-close-modal");
  const btnCancel = document.getElementById("btn-cancel");
  const formTransaksi = document.getElementById("form-transaksi");
  const modalTitle = document.getElementById("modal-title");
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
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(number);
  }
  function formatDateTime(dateString, timeString) {
    return `${dateString} ${timeString}`;
  }
  async function loadData() {
    tableBody.innerHTML = `<tr><td colspan="7" class="text-center p-8"><div class="spinner-simple"></div></td></tr>`;
    const formData = new FormData(filterForm);
    const params = new URLSearchParams(formData);
    try {
      const response = await fetch(
        `${API_BASE}/get_data.php?${params.toString()}`
      );
      const result = await response.json();
      if (result.error) throw new Error(result.error);
      renderTable(result.tabel_data, result.pagination.offset);
      renderPagination(result.pagination);
    } catch (error) {
      console.error(error);
      tableBody.innerHTML = `<tr><td colspan="7" class="text-center p-4 text-red-500">Gagal memuat data: ${error.message}</td></tr>`;
    }
  }
  function renderTable(data, offset) {
    if (!data || data.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="7" class="text-center p-4 text-gray-500">Tidak ada data ditemukan.</td></tr>`;
      return;
    }
    let html = "";
    data.forEach((row, index) => {
      const rowData = encodeURIComponent(JSON.stringify(row));
      html += `
                <tr class="hover:bg-gray-50 border-b border-gray-100">
                    <td class="text-center text-sm text-gray-500 py-3">${
                      offset + index + 1
                    }</td>
                    <td class="text-sm font-medium text-gray-800">
                        ${row.tanggal}<br>
                        <span class="text-xs text-gray-500">${row.jam}</span>
                    </td>
                    <td class="text-sm text-gray-700 text-center">${
                      row.user_hitung
                    }</td>
                    <td class="text-sm text-gray-700 text-center">${
                      row.user_cek
                    }</td>
                    <td class="text-sm font-bold text-pink-600 text-right pr-8">
                        ${formatRupiah(row.total_nominal)}
                    </td>
                    <td class="text-sm text-gray-600 italic max-w-xs truncate">${
                      row.keterangan || "-"
                    }</td>
                    <td class="text-center">
                        <button onclick="window.editBrangkas('${rowData}')" 
                            class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit Data">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
            `;
    });
    tableBody.innerHTML = html;
  }
  function renderPagination(pagination) {
    const { current_page, total_pages } = pagination;
    paginationInfo.textContent = `Halaman ${current_page} dari ${total_pages}`;
    let links = "";
    if (current_page > 1) {
      links += `<button onclick="window.changePage(${
        current_page - 1
      })" class="pagination-link"><i class="fas fa-chevron-left"></i></button>`;
    }
    if (current_page < total_pages) {
      links += `<button onclick="window.changePage(${
        current_page + 1
      })" class="pagination-link"><i class="fas fa-chevron-right"></i></button>`;
    }
    paginationLinks.innerHTML = links;
  }
  function openModal(mode, data = null) {
    formTransaksi.reset();
    document.getElementById("form_mode").value = mode;
    if (mode === "insert") {
      modalTitle.textContent = "Input Uang Brangkas Baru";
      document.getElementById("pk_tanggal").value = "";
      document.getElementById("user_hitung").readOnly = false;
      calculateFormTotal();
    } else {
      modalTitle.textContent = "Edit Data Uang Brangkas";
      document.getElementById("pk_tanggal").value = data.tanggal;
      document.getElementById("pk_jam").value = data.jam;
      document.getElementById("pk_user_hitung").value = data.user_hitung;
      document.getElementById("user_hitung").value = data.user_hitung;
      document.getElementById("user_hitung").readOnly = true;
      document.getElementById("user_cek").value = data.user_cek;
      document.getElementById("keterangan").value = data.keterangan;
      for (const [key, val] of Object.entries(NOMINAL_MAP)) {
        const input = document.querySelector(`input[name="${key}"]`);
        if (input) input.value = data[key];
      }
      calculateFormTotal();
    }
    modalForm.classList.remove("hidden");
  }
  function closeModal() {
    modalForm.classList.add("hidden");
  }
  function calculateFormTotal() {
    let total = 0;
    inputDenoms.forEach((input) => {
      const name = input.getAttribute("name");
      const qty = parseInt(input.value) || 0;
      const nominal = NOMINAL_MAP[name] || 0;
      total += qty * nominal;
    });
    displayTotal.textContent = formatRupiah(total);
  }
  inputDenoms.forEach((input) => {
    input.addEventListener("input", calculateFormTotal);
  });
  btnAddData.addEventListener("click", () => openModal("insert"));
  btnCloseModal.addEventListener("click", closeModal);
  btnCancel.addEventListener("click", closeModal);
  formTransaksi.addEventListener("submit", async (e) => {
    e.preventDefault();
    const mode = document.getElementById("form_mode").value;
    const endpoint = mode === "insert" ? "/insert.php" : "/update.php";
    const formData = new FormData(formTransaksi);
    const jsonData = {};
    formData.forEach((value, key) => (jsonData[key] = value));
    if (!jsonData.kode_otorisasi) {
      Swal.fire("Gagal", "Kode Otorisasi wajib diisi!", "warning");
      return;
    }
    try {
      const btnSave = document.getElementById("btn-save");
      const originalText = btnSave.innerHTML;
      btnSave.disabled = true;
      btnSave.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Memproses...`;
      const response = await fetch(API_BASE + endpoint, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(jsonData),
      });
      const result = await response.json();
      if (result.success) {
        Swal.fire("Berhasil", result.message, "success");
        closeModal();
        loadData();
      } else {
        Swal.fire(
          "Gagal",
          result.message || "Terjadi kesalahan server",
          "error"
        );
      }
    } catch (error) {
      Swal.fire("Error", "Koneksi terputus: " + error.message, "error");
    } finally {
      const btnSave = document.getElementById("btn-save");
      btnSave.disabled = false;
      btnSave.innerHTML = `<i class="fas fa-save mr-2 mt-1"></i> Simpan Data`;
    }
  });
  filterForm.addEventListener("submit", (e) => {
    e.preventDefault();
    document.getElementById("page_input").value = 1;
    loadData();
  });
  window.editBrangkas = (encodedJson) => {
    const data = JSON.parse(decodeURIComponent(encodedJson));
    openModal("update", data);
  };
  window.changePage = (newPage) => {
    document.getElementById("page_input").value = newPage;
    loadData();
  };
  loadData();
});
