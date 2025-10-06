import getCookie from "../index/utils/cookies.js";


const state = {
    filters: {
        search: '',
        kd_store: '',
        tanggal_mulai: '',
        tanggal_selesai: '',
    },
    pagination: {
        page: 1,
        pageSize: 10,
        totalRecords: 0,
        totalPages: 1,
    },
    isLoading: false,
};


async function fetchAndRenderHistory() {
    if (state.isLoading) return;
    state.isLoading = true;
    showLoadingState();

    const token = getCookie("token");
    const { search, kd_store, tanggal_mulai, tanggal_selesai } = state.filters;
    const { page, pageSize } = state.pagination;

    const params = new URLSearchParams({
        page,
        pageSize,
        search,
        kd_store,
        tanggal_mulai,
        tanggal_selesai,
    });

    try {
        const response = await fetch(`/src/api/rewards/get_histori_penerimaan.php?${params.toString()}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        if (result.success) {
            state.pagination = result.pagination;
            renderTable(result.data);
            renderPagination();
        } else {
            showErrorState(result.message || 'Gagal mengambil data');
        }

    } catch (error) {
        console.error('Fetch error:', error);
        showErrorState(error.message);
    } finally {
        state.isLoading = false;
    }
}
function renderTable(data) {
    const tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = '';

    if (data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="9" class="text-center py-10 text-gray-500">Tidak ada data ditemukan.</td></tr>`;
        return;
    }

    data.forEach(item => {
        
        const waktu = `${formatDate(item.tanggal)} ${item.jam.substring(0, 5)}`;
        
        
        let perubahanPoinHtml = '<span class="text-gray-400">-</span>';
        if (item.ket.toLowerCase().includes('poin')) {
            perubahanPoinHtml = `<span class="font-semibold">${item.old_poin} &rarr; ${item.new_poin}</span>`;
        }
        
        
        let qtyRecHtml = '<span class="text-gray-400">-</span>';
        if (item.ket.toLowerCase().includes('terima') || item.ket.toLowerCase().includes('receive')) {
             qtyRecHtml = `<span class="font-semibold text-green-700">+${item.qty_rec}</span>`;
        }

        
        const jenisClass = item.ket.toLowerCase().includes('poin') 
            ? 'bg-blue-100 text-blue-800' 
            : 'bg-green-100 text-green-800';
        const jenisHtml = `<span class="px-2.5 py-1 text-xs font-medium rounded-full ${jenisClass}">${item.ket}</span>`;

        const row = `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-xs">${waktu}</td>
                <td class="px-6 py-4 whitespace-nowrap font-mono text-gray-500 text-xs">${item.no_hdh}</td>
                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">${item.plu}</td>
                <td class="px-6 py-4 max-w-xs truncate" title="${item.nama_hadiah || ''}">${item.nama_hadiah || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">${qtyRecHtml}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">${perubahanPoinHtml}</td>
                <td class="px-6 py-4 whitespace-nowrap">${item.nama_cabang || item.kd_store}</td>
                <td class="px-6 py-4 whitespace-nowrap">${item.nama_karyawan || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">${jenisHtml}</td>
            </tr>
        `;
        tableBody.insertAdjacentHTML('beforeend', row);
    });
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString + 'T00:00:00'); 
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('id-ID', options);
}


function renderPagination() {
    const { page, totalPages, totalRecords } = state.pagination;
    const container = document.getElementById('pagination-container');
    if (!container) return;

    if (totalRecords === 0) {
        container.innerHTML = '';
        return;
    }

    const startItem = (page - 1) * state.pagination.pageSize + 1;
    const endItem = Math.min(page * state.pagination.pageSize, totalRecords);

    container.innerHTML = `
        <div class="text-sm text-gray-600">
            Menampilkan ${startItem}-${endItem} dari ${totalRecords} data
        </div>
        <div class="flex items-center gap-2">
            <button id="firstPage" class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50" ${page === 1 ? 'disabled' : ''}>
                <i class="fas fa-angle-double-left"></i>
            </button>
            <button id="prevPage" class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50" ${page === 1 ? 'disabled' : ''}>
                <i class="fas fa-angle-left"></i>
            </button>
            <span class="text-sm font-medium">Halaman ${page} dari ${totalPages}</span>
            <button id="nextPage" class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50" ${page >= totalPages ? 'disabled' : ''}>
                <i class="fas fa-angle-right"></i>
            </button>
            <button id="lastPage" class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50" ${page >= totalPages ? 'disabled' : ''}>
                <i class="fas fa-angle-double-right"></i>
            </button>
        </div>
    `;

    
    document.getElementById('firstPage').addEventListener('click', () => { state.pagination.page = 1; fetchAndRenderHistory(); });
    document.getElementById('prevPage').addEventListener('click', () => { if (state.pagination.page > 1) state.pagination.page--; fetchAndRenderHistory(); });
    document.getElementById('nextPage').addEventListener('click', () => { if (state.pagination.page < totalPages) state.pagination.page++; fetchAndRenderHistory(); });
    document.getElementById('lastPage').addEventListener('click', () => { state.pagination.page = totalPages; fetchAndRenderHistory(); });
}



function showLoadingState() {
    const tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-10"><i class="fas fa-spinner fa-spin text-2xl text-indigo-600"></i><p class="mt-2 text-gray-500">Memuat data...</p></td></tr>`;
}

function showErrorState(message) {
    const tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-10 text-red-500">${message}</td></tr>`;
}


function initialize() {
    
    const picker = new Litepicker({
        element: document.getElementById('filterTanggal'),
        singleMode: false,
        format: 'YYYY-MM-DD',
        setup: (picker) => {
            picker.on('selected', (date1, date2) => {
                state.filters.tanggal_mulai = date1.format('YYYY-MM-DD');
                state.filters.tanggal_selesai = date2.format('YYYY-MM-DD');
                state.pagination.page = 1;
                fetchAndRenderHistory();
            });
        }
    });

    
    const searchInput = document.getElementById('filterSearch');
    let debounceTimer;
    searchInput.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            state.filters.search = e.target.value.trim();
            state.pagination.page = 1;
            fetchAndRenderHistory();
        }, 500); 
    });

    
    const cabangSelect = document.getElementById('filterCabang');
    cabangSelect.addEventListener('change', (e) => {
        state.filters.kd_store = e.target.value;
        state.pagination.page = 1;
        fetchAndRenderHistory();
    });

    
    loadCabangOptions();
    fetchAndRenderHistory();
}


async function loadCabangOptions() {
    try {
        const token = getCookie("token");
        const response = await fetch('/src/api/cabang/get_kode', {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const result = await response.json();
        if (result.data) {
            const select = document.getElementById('filterCabang');
            select.innerHTML = '<option value="">Semua Cabang</option>';
            result.data.forEach(cabang => {
                const option = `<option value="${cabang.store}">${cabang.nama_cabang}</option>`;
                select.insertAdjacentHTML('beforeend', option);
            });
        }
    } catch (error) {
        console.error('Gagal memuat data cabang:', error);
    }
}



document.addEventListener('DOMContentLoaded', initialize);