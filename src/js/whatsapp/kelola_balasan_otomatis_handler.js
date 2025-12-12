document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("table-body");
  const filterForm = document.getElementById("filter-form");
  const modalForm = document.getElementById("modal-form");
  const paginationContainer = document.getElementById("pagination-container");
  const btnAddData = document.getElementById("btn-add-data");
  const btnCloseModal = document.getElementById("btn-close-modal");
  const btnCancel = document.getElementById("btn-cancel");
  const formTransaksi = document.getElementById("form-transaksi");
  const btnAddMessage = document.getElementById("btn-add-message");
  const messageContainer = document.getElementById("message-container");

  const API_BASE = "/src/api/whatsapp";

  // --- HELPER: Message Bubble Input UI ---
  function createMessageInput(value = "") {
    const wrapper = document.createElement("div");
    wrapper.className = "relative group animate-fade-in";

    // Nomor urut dinamis
    const currentCount = messageContainer.children.length + 1;

    wrapper.innerHTML = `
            <div class="flex gap-2 items-start bg-gray-50 p-3 rounded-lg border border-gray-200">
                <div class="mt-1 flex flex-col items-center gap-1">
                    <span class="text-xs font-bold text-white bg-gray-400 w-6 h-6 flex items-center justify-center rounded-full select-none msg-number">${currentCount}</span>
                </div>
                <div class="flex-1">
                    <textarea name="isi_balasan[]" rows="3"
                        class="input-enhanced w-full px-3 py-2 border border-gray-300 rounded bg-white focus:outline-none text-sm resize-y"
                        placeholder="Tulis pesan balasan di sini..." required maxlength="1000">${value}</textarea>
                    <div class="text-right text-[10px] text-gray-400 mt-1">Max 1000 char</div>
                </div>
                <div class="flex flex-col gap-1">
                    <button type="button" class="btn-remove-msg text-gray-400 hover:text-red-500 hover:bg-red-50 p-1.5 rounded transition-colors" title="Hapus pesan ini">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        `;

    // Event listener hapus
    const btnDel = wrapper.querySelector(".btn-remove-msg");
    if (btnDel) {
      btnDel.addEventListener("click", () => {
        // Cek jika ini satu-satunya pesan, jangan hapus (opsional, atau clear value saja)
        if (messageContainer.children.length > 1) {
          wrapper.remove();
          renumberMessages();
        } else {
          wrapper.querySelector("textarea").value = "";
          wrapper.querySelector("textarea").focus();
        }
      });
    }
    return wrapper;
  }

  // Fungsi untuk mengurutkan ulang nomor (1, 2, 3...) setelah delete
  function renumberMessages() {
    Array.from(messageContainer.children).forEach((child, index) => {
      const span = child.querySelector(".msg-number");
      if (span) span.textContent = index + 1;
    });
  }

  btnAddMessage.addEventListener("click", () => {
    messageContainer.appendChild(createMessageInput(""));
    // Scroll ke bawah
    messageContainer.scrollTop = messageContainer.scrollHeight;
  });
  // --- END HELPER ---

  function getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    return {
      search_keyword: (params.get("search_keyword") || "").trim(),
      page: parseInt(params.get("page") || "1", 10),
    };
  }

  function build_pagination_url(newPage) {
    const params = new URLSearchParams(window.location.search);
    params.set("page", newPage);
    return "?" + params.toString();
  }

  async function loadData() {
    const urlParams = getUrlParams();
    const inputSearch = filterForm.querySelector(
      'input[name="search_keyword"]'
    );
    if (inputSearch) inputSearch.value = urlParams.search_keyword;

    tableBody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center p-8">
                    <div class="spinner-simple"></div>
                    <p class="mt-3 text-gray-500">Memuat data...</p>
                </td>
            </tr>`;

    const queryString = new URLSearchParams({
      search_keyword: urlParams.search_keyword,
      page: urlParams.page,
    }).toString();

    try {
      const response = await fetch(
        `${API_BASE}/get_data_balasan_otomatis.php?${queryString}`
      );
      const result = await response.json();
      if (result.error) throw new Error(result.error);
      renderTable(result.data, result.pagination);
      renderPagination(result.pagination);
    } catch (error) {
      console.error(error);
      tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center p-8 text-red-600">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <p class="font-semibold">Gagal memuat data: ${error.message}</p>
                    </td>
                </tr>`;
      paginationContainer.innerHTML = "";
    }
  }

  function renderTable(data, pagination) {
    if (!data || data.length === 0) {
      tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center p-12 text-gray-500">
                        <i class="fas fa-inbox fa-3x mb-3 text-gray-300"></i>
                        <p class="text-base font-semibold">Tidak ada data ditemukan</p>
                        <p class="text-sm mt-1">Silakan tambah keyword baru</p>
                    </td>
                </tr>`;
      return;
    }

    const startNumber = (pagination.current_page - 1) * pagination.limit;

    tableBody.innerHTML = data
      .map((row, index) => {
        const statusBadge =
          row.status_aktif == "1"
            ? `<span class="badge-status badge-aktif"><i class="fas fa-check-circle"></i> Aktif</span>`
            : `<span class="badge-status badge-nonaktif"><i class="fas fa-times-circle"></i> Non-Aktif</span>`;

        // Logic menampilkan preview pesan
        const messages = row.list_pesan || [];
        let displayBalasan = "<em class='text-gray-400'>Tidak ada pesan</em>";
        let moreCount = 0;

        if (messages.length > 0) {
          // Ambil pesan pertama
          displayBalasan = messages[0];
          if (displayBalasan.length > 100) {
            displayBalasan = displayBalasan.substring(0, 100) + "...";
          }
          displayBalasan = displayBalasan.replace(/\n/g, " "); // Hapus enter untuk tampilan tabel

          // Hitung sisa pesan
          moreCount = messages.length - 1;
        }

        const moreBadge =
          moreCount > 0
            ? `<span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded-full ml-2 border border-blue-200" title="${moreCount} pesan lainnya">+${moreCount}</span>`
            : "";

        // Encode data untuk dikirim ke fungsi edit
        const rowDataString = encodeURIComponent(JSON.stringify(row));

        return `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="text-center font-semibold text-gray-500">${
                      startNumber + index + 1
                    }</td>
                    <td>
                         <span class="font-bold text-gray-800 bg-gray-100 px-2 py-1 rounded text-sm">${
                           row.kata_kunci
                         }</span>
                    </td>
                    <td class="text-gray-600 text-sm">
                        <div class="flex items-center">
                            <span class="line-clamp-1">${displayBalasan}</span>
                            ${moreBadge}
                        </div>
                    </td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="window.editData('${rowDataString}')" 
                                class="rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 hover:scale-110 flex items-center justify-center transition-all w-8 h-8" 
                                title="Edit Data">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button onclick="window.deleteData('${row.id}', '${
          row.kata_kunci
        }')" 
                                class="rounded-lg bg-red-50 text-red-600 hover:bg-red-100 hover:scale-110 flex items-center justify-center transition-all w-8 h-8" 
                                title="Hapus Data">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
      })
      .join("");
  }

  function renderPagination(pagination) {
    if (!pagination) {
      paginationContainer.innerHTML = "";
      return;
    }

    const { current_page, total_pages, total_rows, limit } = pagination;
    const offset = (current_page - 1) * limit;

    if (total_rows === 0) {
      paginationContainer.innerHTML = "";
      return;
    }

    const start_row = offset + 1;
    const end_row = Math.min(offset + limit, total_rows);

    let html = `
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <span class="text-sm text-gray-600">
                    Menampilkan ${start_row} - ${end_row} dari ${total_rows} data
                </span>
                <div class="flex items-center gap-2">
        `;

    html += `
              <a href="${
                current_page > 1 ? build_pagination_url(current_page - 1) : "#"
              }" 
                 class="pagination-link ${
                   current_page === 1 ? "pagination-disabled" : ""
                 }">
                 <i class="fas fa-chevron-left"></i>
              </a>
          `;

    const pages_to_show = [];
    const max_pages_around = 2;

    for (let i = 1; i <= total_pages; i++) {
      if (
        i === 1 ||
        i === total_pages ||
        (i >= current_page - max_pages_around &&
          i <= current_page + max_pages_around)
      ) {
        pages_to_show.push(i);
      }
    }

    let last_page = 0;
    for (const page_num of pages_to_show) {
      if (last_page !== 0 && page_num > last_page + 1) {
        html += `<span class="pagination-ellipsis px-2 text-gray-400">...</span>`;
      }
      html += `
                <a href="${build_pagination_url(page_num)}" 
                   class="pagination-link ${
                     page_num === current_page ? "pagination-active" : ""
                   }">
                   ${page_num}
                </a>
            `;
      last_page = page_num;
    }

    html += `
              <a href="${
                current_page < total_pages
                  ? build_pagination_url(current_page + 1)
                  : "#"
              }" 
                 class="pagination-link ${
                   current_page === total_pages ? "pagination-disabled" : ""
                 }">
                 <i class="fas fa-chevron-right"></i>
              </a>
          `;

    html += `</div></div>`;
    paginationContainer.innerHTML = html;
  }

  function openModal(mode, data = null) {
    formTransaksi.reset();
    document.getElementById("form_mode").value = mode;
    messageContainer.innerHTML = ""; // Bersihkan pesan sebelumnya

    const modalTitle = document.getElementById("modal-title");
    const kataKunciInput = document.getElementById("kata_kunci");
    const idInput = document.getElementById("data_id");

    if (mode === "insert") {
      modalTitle.textContent = "Tambah Keyword Baru";
      modalTitle.nextElementSibling.textContent = "Buat balasan otomatis baru";
      idInput.value = "";
      kataKunciInput.readOnly = false;
      kataKunciInput.classList.remove("bg-gray-100");

      // Tambah 1 kolom pesan kosong secara default
      messageContainer.appendChild(createMessageInput(""));
    } else if (mode === "update" && data) {
      modalTitle.textContent = "Edit Keyword & Pesan";
      modalTitle.nextElementSibling.textContent = "Perbarui informasi balasan";

      idInput.value = data.id;
      kataKunciInput.value = data.kata_kunci;
      document.getElementById("status_aktif").value = data.status_aktif;

      // Load pesan-pesan yang ada
      if (data.list_pesan && data.list_pesan.length > 0) {
        data.list_pesan.forEach((msg) => {
          messageContainer.appendChild(createMessageInput(msg));
        });
      } else {
        messageContainer.appendChild(createMessageInput(""));
      }

      kataKunciInput.readOnly = false;
    }

    modalForm.classList.remove("hidden");
    setTimeout(() => {
      if (mode === "insert") {
        kataKunciInput.focus();
      }
    }, 100);
  }

  function closeModal() {
    modalForm.classList.add("hidden");
  }

  btnAddData.addEventListener("click", () => openModal("insert"));
  [btnCloseModal, btnCancel].forEach((el) =>
    el.addEventListener("click", closeModal)
  );
  document
    .getElementById("modal-backdrop")
    .addEventListener("click", closeModal);

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !modalForm.classList.contains("hidden")) {
      closeModal();
    }
  });

  filterForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(filterForm);
    const searchVal = formData.get("search_keyword").toString().trim();
    const params = new URLSearchParams();
    if (searchVal) params.set("search_keyword", searchVal);
    params.set("page", "1");
    window.history.pushState({}, "", `?${params.toString()}`);
    loadData();
  });

  formTransaksi.addEventListener("submit", async (e) => {
    e.preventDefault();

    // Ambil data form
    const formData = new FormData(formTransaksi);

    // Perbaikan: Karena FormData kadang sulit menghandle array secara langsung dengan format yang kita mau di PHP,
    // Kita construct objek JSON manual.
    const listPesan = formData.getAll("isi_balasan[]");

    const jsonData = {
      mode: formData.get("mode"),
      id: formData.get("id"),
      kata_kunci: formData.get("kata_kunci"),
      status_aktif: formData.get("status_aktif"),
      isi_balasan: listPesan, // Array pesan
    };

    const btnSave = document.getElementById("btn-save");
    const originalHTML = btnSave.innerHTML;
    btnSave.disabled = true;
    btnSave.innerHTML =
      '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';

    try {
      const response = await fetch(`${API_BASE}/save_balasan_otomatis.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(jsonData),
      });

      const result = await response.json();
      if (result.success) {
        Swal.fire({
          icon: "success",
          title: "Berhasil!",
          text: result.message,
          timer: 2000,
          showConfirmButton: false,
        });
        closeModal();
        loadData();
      } else {
        throw new Error(result.message);
      }
    } catch (error) {
      Swal.fire({
        icon: "error",
        title: "Gagal!",
        text: error.message,
        confirmButtonColor: "#10b981",
      });
    } finally {
      btnSave.disabled = false;
      btnSave.innerHTML = originalHTML;
    }
  });

  window.editData = (encodedData) => {
    const data = JSON.parse(decodeURIComponent(encodedData));
    openModal("update", data);
  };

  window.deleteData = async (id, kataKunci) => {
    const confirm = await Swal.fire({
      title: "Hapus Keyword?",
      html: `Anda yakin ingin menghapus keyword:<br><strong class="text-lg">"${kataKunci}"</strong><br>beserta seluruh pesan balasannya?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#ef4444",
      cancelButtonColor: "#6b7280",
      confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus!',
      cancelButtonText: "Batal",
      reverseButtons: true,
    });

    if (confirm.isConfirmed) {
      try {
        Swal.fire({
          title: "Menghapus...",
          text: "Mohon tunggu sebentar",
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          },
        });

        const response = await fetch(
          `${API_BASE}/delete_balasan_otomatis.php`,
          {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: id }), // Kirim ID bukan kata kunci
          }
        );

        const result = await response.json();

        if (result.success) {
          Swal.fire({
            icon: "success",
            title: "Terhapus!",
            text: result.message,
            timer: 2000,
            showConfirmButton: false,
          });
          loadData();
        } else {
          throw new Error(result.message);
        }
      } catch (error) {
        Swal.fire({
          icon: "error",
          title: "Gagal!",
          text: error.message,
          confirmButtonColor: "#10b981",
        });
      }
    }
  };

  loadData();
});
