import { sendRequestJSON, sendRequestGET } from "../utils/api_helpers.js"; 
// Pastikan path api_helpers.js sesuai, atau buat fungsi fetch manual jika belum ada

document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.getElementById("table-body");
    const filterForm = document.getElementById("filter-form");
    const modalForm = document.getElementById("modal-form");
    const btnAddData = document.getElementById("btn-add-data");
    const btnCloseModal = document.getElementById("btn-close-modal");
    const btnCancel = document.getElementById("btn-cancel");
    const formTransaksi = document.getElementById("form-transaksi");
    
    // Konfigurasi API Base Path
    const API_BASE = "/src/api/whatsapp";

    // --- Load Data ---
    async function loadData() {
        tableBody.innerHTML = `<tr><td colspan="5" class="text-center p-8"><i class="fas fa-spinner fa-spin mr-2"></i> Memuat data...</td></tr>`;
        
        const params = new URLSearchParams(new FormData(filterForm)).toString();
        
        try {
            const response = await fetch(`${API_BASE}/get_data_balasan_otomatis.php?${params}`);
            const result = await response.json();
            
            if (result.error) throw new Error(result.error);
            renderTable(result.data);
        } catch (error) {
            tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-red-500 py-4">${error.message}</td></tr>`;
        }
    }

    // --- Render Table ---
    function renderTable(data) {
        if (!data || data.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-gray-500 py-8">Tidak ada data ditemukan.</td></tr>`;
            return;
        }

        tableBody.innerHTML = data.map((row, index) => {
            const statusBadge = row.status_aktif == '1' 
                ? `<span class="px-2 py-1 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full">Aktif</span>`
                : `<span class="px-2 py-1 text-xs font-semibold leading-5 text-red-800 bg-red-100 rounded-full">Non-Aktif</span>`;

            // Escape string untuk dikirim ke fungsi JS onClick
            const rowDataString = encodeURIComponent(JSON.stringify(row));

            return `
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4 text-center font-medium text-gray-900">${index + 1}</td>
                    <td class="px-6 py-4 font-bold text-gray-800">${row.kata_kunci}</td>
                    <td class="px-6 py-4 text-gray-600 whitespace-pre-wrap line-clamp-2 max-w-xs">${row.isi_balasan.substring(0, 100)}${row.isi_balasan.length > 100 ? '...' : ''}</td>
                    <td class="px-6 py-4 text-center">${statusBadge}</td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex item-center justify-center gap-2">
                            <button onclick="window.editData('${rowDataString}')" class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors" title="Edit">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button onclick="window.deleteData('${row.kata_kunci}')" class="w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join("");
    }

    // --- Modal Handler ---
    function openModal(mode, data = null) {
        formTransaksi.reset();
        document.getElementById("form_mode").value = mode;
        const modalTitle = document.getElementById("modal-title");

        if (mode === "insert") {
            modalTitle.textContent = "Tambah Keyword Baru";
            document.getElementById("old_kata_kunci").value = "";
            document.getElementById("kata_kunci").readOnly = false; // Boleh edit saat insert
        } else if (mode === "update" && data) {
            modalTitle.textContent = "Edit Keyword";
            document.getElementById("kata_kunci").value = data.kata_kunci;
            document.getElementById("old_kata_kunci").value = data.kata_kunci; // Simpan PK lama
            document.getElementById("isi_balasan").value = data.isi_balasan;
            document.getElementById("status_aktif").value = data.status_aktif;
        }

        modalForm.classList.remove("hidden");
    }

    function closeModal() {
        modalForm.classList.add("hidden");
    }

    // --- Event Listeners ---
    btnAddData.addEventListener("click", () => openModal("insert"));
    [btnCloseModal, btnCancel].forEach(el => el.addEventListener("click", closeModal));

    filterForm.addEventListener("submit", (e) => {
        e.preventDefault();
        loadData();
    });

    // --- Save Data (Insert & Update) ---
    formTransaksi.addEventListener("submit", async (e) => {
        e.preventDefault();
        
        const formData = new FormData(formTransaksi);
        const jsonData = Object.fromEntries(formData.entries());
        const endpoint = "/save_balasan_otomatis.php"; // Satu file handle insert & update

        try {
            // Gunakan fetch standard atau helper wrapper
            const response = await fetch(API_BASE + endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(jsonData)
            });
            
            const result = await response.json();

            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: result.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                closeModal();
                loadData();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            Swal.fire('Gagal', error.message, 'error');
        }
    });

    // --- Global Functions (untuk dipanggil dari HTML string) ---
    window.editData = (encodedData) => {
        const data = JSON.parse(decodeURIComponent(encodedData));
        openModal("update", data);
    };

    window.deleteData = async (kataKunci) => {
        const confirm = await Swal.fire({
            title: 'Hapus Keyword?',
            text: `Anda yakin ingin menghapus keyword "${kataKunci}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        });

        if (confirm.isConfirmed) {
            try {
                const response = await fetch(`${API_BASE}/delete_balasan_otomatis.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ kata_kunci: kataKunci })
                });
                const result = await response.json();

                if (result.success) {
                    Swal.fire('Terhapus!', result.message, 'success');
                    loadData();
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        }
    };

    // Init Load
    loadData();
});