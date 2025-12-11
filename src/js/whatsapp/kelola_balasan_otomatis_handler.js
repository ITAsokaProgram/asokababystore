document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.getElementById("table-body");
    const filterForm = document.getElementById("filter-form");
    const modalForm = document.getElementById("modal-form");
    const paginationContainer = document.getElementById("pagination-container");
    const btnAddData = document.getElementById("btn-add-data");
    const btnCloseModal = document.getElementById("btn-close-modal");
    const btnCancel = document.getElementById("btn-cancel");
    const formTransaksi = document.getElementById("form-transaksi");
    const isiBalasan = document.getElementById("isi_balasan");
    const charCount = document.getElementById("char-count");
    const API_BASE = "/src/api/whatsapp";
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
    if (isiBalasan && charCount) {
        isiBalasan.addEventListener("input", () => {
            const count = isiBalasan.value.length;
            charCount.textContent = count;
            if (count > 900) {
                charCount.parentElement.classList.add("text-red-600");
                charCount.parentElement.classList.remove("text-gray-600");
            } else {
                charCount.parentElement.classList.remove("text-red-600");
                charCount.parentElement.classList.add("text-gray-600");
            }
        });
    }
    async function loadData() {
        const urlParams = getUrlParams();
        const inputSearch = filterForm.querySelector('input[name="search_keyword"]');
        if(inputSearch) inputSearch.value = urlParams.search_keyword;
        tableBody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center p-8">
                    <div class="spinner-simple"></div>
                    <p class="mt-3 text-gray-500">Memuat data...</p>
                </td>
            </tr>`;
        const queryString = new URLSearchParams({
            search_keyword: urlParams.search_keyword,
            page: urlParams.page
        }).toString();
        try {
            const response = await fetch(`${API_BASE}/get_data_balasan_otomatis.php?${queryString}`);
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
        tableBody.innerHTML = data.map((row, index) => {
            const statusBadge = row.status_aktif == '1' 
                ? `<span class="badge-status badge-aktif"><i class="fas fa-check-circle"></i> Aktif</span>`
                : `<span class="badge-status badge-nonaktif"><i class="fas fa-times-circle"></i> Non-Aktif</span>`;
            const rowDataString = encodeURIComponent(JSON.stringify(row));
            let displayBalasan = row.isi_balasan;
            if (displayBalasan.length > 120) {
                displayBalasan = displayBalasan.substring(0, 120) + '...';
            }
            displayBalasan = displayBalasan.replace(/\n/g, '<br>');
            return `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="text-center font-semibold text-gray-500">${startNumber + index + 1}</td>
                    <td>
                         <span class="font-bold text-gray-800">${row.kata_kunci}</span>
                    </td>
                    <td class="text-gray-600 text-sm">
                        <div class="line-clamp-3">${displayBalasan}</div>
                    </td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="window.editData('${rowDataString}')" 
                                class="rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 hover:scale-110 flex items-center justify-center transition-all w-8 h-8" 
                                title="Edit Data">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button onclick="window.deleteData('${row.kata_kunci}')" 
                                class="rounded-lg bg-red-50 text-red-600 hover:bg-red-100 hover:scale-110 flex items-center justify-center transition-all w-8 h-8" 
                                title="Hapus Data">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join("");
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
              <a href="${current_page > 1 ? build_pagination_url(current_page - 1) : "#"}" 
                 class="pagination-link ${current_page === 1 ? "pagination-disabled" : ""}">
                 <i class="fas fa-chevron-left"></i>
              </a>
          `;
        const pages_to_show = [];
        const max_pages_around = 2;
        for (let i = 1; i <= total_pages; i++) {
            if (
                i === 1 ||
                i === total_pages ||
                (i >= current_page - max_pages_around && i <= current_page + max_pages_around)
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
                   class="pagination-link ${page_num === current_page ? "pagination-active" : ""}">
                   ${page_num}
                </a>
            `;
            last_page = page_num;
        }
        html += `
              <a href="${current_page < total_pages ? build_pagination_url(current_page + 1) : "#"}" 
                 class="pagination-link ${current_page === total_pages ? "pagination-disabled" : ""}">
                 <i class="fas fa-chevron-right"></i>
              </a>
          `;
        html += `</div></div>`; 
        paginationContainer.innerHTML = html;
    }
    function openModal(mode, data = null) {
        formTransaksi.reset();
        document.getElementById("form_mode").value = mode;
        const modalTitle = document.getElementById("modal-title");
        const kataKunciInput = document.getElementById("kata_kunci");
        if (charCount) charCount.textContent = "0";
        if (mode === "insert") {
            modalTitle.textContent = "Tambah Keyword Baru";
            modalTitle.nextElementSibling.textContent = "Buat balasan otomatis baru";
            document.getElementById("old_kata_kunci").value = "";
            kataKunciInput.readOnly = false;
            kataKunciInput.classList.remove("bg-gray-100");
        } else if (mode === "update" && data) {
            modalTitle.textContent = "Edit Keyword";
            modalTitle.nextElementSibling.textContent = "Perbarui informasi balasan";
            kataKunciInput.value = data.kata_kunci;
            document.getElementById("old_kata_kunci").value = data.kata_kunci;
            document.getElementById("isi_balasan").value = data.isi_balasan;
            document.getElementById("status_aktif").value = data.status_aktif;
            if (charCount) charCount.textContent = data.isi_balasan.length;
            kataKunciInput.readOnly = true;
            kataKunciInput.classList.add("bg-gray-100");
        }
        modalForm.classList.remove("hidden");
        setTimeout(() => {
            if (mode === "insert") {
                kataKunciInput.focus();
            } else {
                document.getElementById("isi_balasan").focus();
            }
        }, 100);
    }
    function closeModal() {
        modalForm.classList.add("hidden");
    }
    btnAddData.addEventListener("click", () => openModal("insert"));
    [btnCloseModal, btnCancel].forEach(el => el.addEventListener("click", closeModal));
    document.getElementById("modal-backdrop").addEventListener("click", closeModal);
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
        if(searchVal) params.set("search_keyword", searchVal);
        params.set("page", "1"); 
        window.history.pushState({}, "", `?${params.toString()}`);
        loadData();
    });
    formTransaksi.addEventListener("submit", async (e) => {
        e.preventDefault();
        const btnSave = document.getElementById("btn-save");
        const originalHTML = btnSave.innerHTML;
        btnSave.disabled = true;
        btnSave.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';
        const formData = new FormData(formTransaksi);
        const jsonData = Object.fromEntries(formData.entries());
        try {
            const response = await fetch(`${API_BASE}/save_balasan_otomatis.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(jsonData)
            });
            const result = await response.json();
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: result.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                closeModal();
                loadData();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: error.message,
                confirmButtonColor: '#10b981'
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
    window.deleteData = async (kataKunci) => {
        const confirm = await Swal.fire({
            title: 'Hapus Keyword?',
            html: `Anda yakin ingin menghapus keyword:<br><strong class="text-lg">"${kataKunci}"</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        });
        if (confirm.isConfirmed) {
            try {
                Swal.fire({
                    title: 'Menghapus...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                const response = await fetch(`${API_BASE}/delete_balasan_otomatis.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ kata_kunci: kataKunci })
                });
                const result = await response.json();
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: result.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadData();
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: error.message,
                    confirmButtonColor: '#10b981'
                });
            }
        }
    };
    loadData();
});