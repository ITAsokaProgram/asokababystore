// component/pagination.js
import { store } from "../services/state.js";
import { getRewardTrade } from "../handlers/transaksiHandlers.js";
import { api } from "../services/api.js";
import { renderTransaksiTable } from "./transaksiTable.js";

export function initPagination() {
  const pageSize = document.getElementById('pageSize');
  const firstPage = document.getElementById('firstPage');
  const prevPage = document.getElementById('prevPage');
  const nextPage = document.getElementById('nextPage');
  const lastPage = document.getElementById('lastPage');

  // Event listener untuk perubahan page size
  if (pageSize) {
    pageSize.addEventListener('change', async (e) => {
      const newLimit = parseInt(e.target.value);
      const { pagination } = store.getState();
      
      store.set({
        pagination: {
          ...pagination,
          limit: newLimit,
          currentPage: 1 // Reset ke halaman 1
        }
      });
      
      await getRewardTrade({ limit: newLimit, offset: 0 });
    });
  }

  // Event listeners untuk tombol navigasi
  if (firstPage) {
    firstPage.addEventListener('click', () => changePage(1));
  }
  
  if (prevPage) {
    prevPage.addEventListener('click', () => {
      const { pagination } = store.getState();
      if (pagination.currentPage > 1) {
        changePage(pagination.currentPage - 1);
      }
    });
  }
  
  if (nextPage) {
    nextPage.addEventListener('click', () => {
      const { pagination } = store.getState();
      if (pagination.currentPage < pagination.totalPages) {
        changePage(pagination.currentPage + 1);
      }
    });
  }
  
  if (lastPage) {
    lastPage.addEventListener('click', () => {
      const { pagination } = store.getState();
      changePage(pagination.totalPages);
    });
  }
}

export function renderPagination() {
  const { pagination } = store.getState();
  const { currentPage, totalPages, total, limit } = pagination;
  
  // Update info data
  updateDataInfo();
  
  // Update page numbers
  updatePageNumbers();
  
  // Update navigation buttons state
  updateNavigationButtons();
}

function updateDataInfo() {
  const { pagination } = store.getState();
  const { currentPage, total, limit } = pagination;
  const dataInfo = document.getElementById('dataInfo');
  
  if (dataInfo && total > 0) {
    const startItem = (currentPage - 1) * limit + 1;
    const endItem = Math.min(currentPage * limit, total);
    dataInfo.textContent = `Menampilkan ${startItem}-${endItem} dari ${total} data`;
  } else if (dataInfo) {
    dataInfo.textContent = 'Tidak ada data';
  }
}

function updatePageNumbers() {
  const { pagination } = store.getState();
  const { currentPage, totalPages } = pagination;
  const pageNumbers = document.getElementById('pageNumbers');
  
  if (!pageNumbers || totalPages <= 1) {
    if (pageNumbers) pageNumbers.innerHTML = '';
    return;
  }

  let buttons = [];
  const maxVisible = 5;
  
  // Tentukan range halaman yang akan ditampilkan
  let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
  let endPage = Math.min(totalPages, startPage + maxVisible - 1);
  
  // Adjust jika tidak cukup halaman di akhir
  if (endPage - startPage + 1 < maxVisible) {
    startPage = Math.max(1, endPage - maxVisible + 1);
  }

  // Buat tombol halaman
  for (let i = startPage; i <= endPage; i++) {
    const isActive = i === currentPage;
    buttons.push(`
      <button 
        class="px-3 py-1 rounded-lg text-sm transition-all duration-200 ${
          isActive 
            ? 'bg-blue-500 text-white' 
            : 'hover:bg-blue-50 text-blue-600'
        }" 
        onclick="changePage(${i})"
      >
        ${i}
      </button>
    `);
  }
  
  // Tambah ellipsis dan tombol first/last jika perlu
  if (startPage > 1) {
    buttons.unshift(`
      <button class="px-3 py-1 rounded-lg text-sm hover:bg-blue-50 text-blue-600" onclick="changePage(1)">
        1
      </button>
    `);
    if (startPage > 2) {
      buttons.splice(1, 0, '<span class="px-2 text-gray-400">...</span>');
    }
  }
  
  if (endPage < totalPages) {
    if (endPage < totalPages - 1) {
      buttons.push('<span class="px-2 text-gray-400">...</span>');
    }
    buttons.push(`
      <button class="px-3 py-1 rounded-lg text-sm hover:bg-blue-50 text-blue-600" onclick="changePage(${totalPages})">
        ${totalPages}
      </button>
    `);
  }

  pageNumbers.innerHTML = buttons.join('');
}

function updateNavigationButtons() {
  const { pagination } = store.getState();
  const { currentPage, totalPages } = pagination;
  
  const firstPage = document.getElementById('firstPage');
  const prevPage = document.getElementById('prevPage');
  const nextPage = document.getElementById('nextPage');
  const lastPage = document.getElementById('lastPage');

  // Disable/enable buttons
  if (firstPage) {
    firstPage.disabled = currentPage === 1;
    firstPage.classList.toggle('opacity-50', currentPage === 1);
  }
  
  if (prevPage) {
    prevPage.disabled = currentPage === 1;
    prevPage.classList.toggle('opacity-50', currentPage === 1);
  }
  
  if (nextPage) {
    nextPage.disabled = currentPage === totalPages || totalPages === 0;
    nextPage.classList.toggle('opacity-50', currentPage === totalPages || totalPages === 0);
  }
  
  if (lastPage) {
    lastPage.disabled = currentPage === totalPages || totalPages === 0;
    lastPage.classList.toggle('opacity-50', currentPage === totalPages || totalPages === 0);
  }
}

async function changePage(newPage) {
  const { pagination, currentFilter, dateFilter } = store.getState();
  
  if (newPage < 1 || newPage > pagination.totalPages) return;
  
  // Update state
  store.set({
    pagination: {
      ...pagination,
      currentPage: newPage
    }
  });

  const offset = (newPage - 1) * pagination.limit;
  
  try {
    // Jika sedang menggunakan filter tanggal
    if (currentFilter === "date_filtered" && dateFilter) {
      const filterData = await api.getDataByFilter({
        start: dateFilter.start,
        end: dateFilter.end,
        cabang: dateFilter.cabang || "",
        limit: pagination.limit,
        offset: offset
      });
      
      if (filterData.status === "success") {
        const transformedData = filterData.data.map((item) => ({
          id: item.id,
          nama_lengkap: item.nama_lengkap || `Member ${item.id}`,
          number_phone: item.number_phone || "-",
          nama_hadiah: item.nama_hadiah,
          qty: item.qty || 1,
          poin_tukar: item.poin_tukar,
          dibuat_tanggal: item.dibuat_tanggal || "-",
          status: item.status,
          cabang: item.cabang || "-",
          kd_store: item.kd_store,
          expired_at: item.expired_at
        }));
        
        store.set({ transaksiData: transformedData });
        renderTransaksiTable(transformedData, "date_filtered");
      }
    } else {
      // Fetch data normal dengan filter status
      await getRewardTrade({ 
        limit: pagination.limit,
        offset: offset 
      });
    }
  } catch (error) {
    console.error("Error changing page:", error);
    alert("Error saat mengubah halaman: " + error.message);
  }
}

export function updatePaginationInfo(total, currentData = []) {
  const { pagination } = store.getState();
  const totalPages = Math.ceil(total / pagination.limit);
  
  store.set({
    pagination: {
      ...pagination,
      total: total,
      totalPages: totalPages
    }
  });
  
  // Render pagination setelah update info
  renderPagination();
}

// Export changePage untuk bisa dipanggil dari HTML
window.changePage = changePage;
