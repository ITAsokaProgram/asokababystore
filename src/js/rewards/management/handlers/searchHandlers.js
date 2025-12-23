// handlers/searchHandlers.js
import { store } from '../services/state.js';
import { renderTransaksiTable } from '../component/transaksiTable.js';
import { updatePaginationInfo, renderPagination } from '../component/pagination.js';

function $(id) {
  return document.getElementById(id);
}

/**
 * Search transaksi dari data yang sudah ada di state
 * @param {string} keyword - Kata kunci pencarian
 */
export function searchFromState(keyword = null) {
  // Jika tidak ada keyword, ambil dari input
  if (!keyword) {
    const searchInput = $('searchInput');
    if (!searchInput) {
      console.error('Search input tidak ditemukan');
      return;
    }
    keyword = searchInput.value.trim();
  }

  const { transaksiData, pagination } = store.getState();

  // Jika keyword kosong, tampilkan semua data
  if (!keyword) {
    store.set({ 
      filteredData: transaksiData,
      searchKeyword: '',
      pagination: {
        ...pagination,
        currentPage: 1
      }
    });
    renderTransaksiTable(transaksiData, 'search');
    updatePaginationInfo(transaksiData.length);
    return;
  }

  // Filter data berdasarkan keyword
  const filteredData = transaksiData.filter(item => {
    const searchText = keyword.toLowerCase();
    
    return (
      // Search by member name
      (item.nama_lengkap && item.nama_lengkap.toLowerCase().includes(searchText)) ||
      // Search by phone number  
      (item.number_phone && item.number_phone.toLowerCase().includes(searchText)) ||
      // Search by transaction ID
      (item.id && item.id.toString().includes(searchText)) ||
      // Search by gift name
      (item.nama_hadiah && item.nama_hadiah.toLowerCase().includes(searchText)) ||
      // Search by status
      (item.status && item.status.toLowerCase().includes(searchText)) ||
      // Search by cabang/branch
      (item.cabang && item.cabang.toLowerCase().includes(searchText)) ||
      // Search by store code
      (item.kd_store && item.kd_store.toLowerCase().includes(searchText))
    );
  });

  // Update state dengan hasil search
  store.set({ 
    filteredData: filteredData,
    searchKeyword: keyword,
    currentFilter: 'search',
    pagination: {
      ...pagination,
      currentPage: 1
    }
  });

  // Render hasil search ke tabel
  renderTransaksiTable(filteredData, 'search');
  updatePaginationInfo(filteredData.length);

  // Show notification
  if (filteredData.length > 0) {
    Toastify({
      text: `Ditemukan ${filteredData.length} transaksi`,
      duration: 3000,
      gravity: "top",
      position: "right",
      backgroundColor: "#22c55e",
    }).showToast();
  } else {
    Toastify({
      text: "Tidak ada transaksi yang ditemukan",
      duration: 3000,
      gravity: "top",
      position: "right",
      backgroundColor: "#f59e0b",
    }).showToast();
  }
}

/**
 * Clear search dan kembali ke data normal
 */
export function clearSearch() {
  const searchInput = $('searchInput');
  const clearBtn = $('clearSearchBtn');
  
  if (searchInput) {
    searchInput.value = '';
  }
  
  if (clearBtn) {
    clearBtn.classList.add('hidden');
  }

  const { transaksiData, pagination } = store.getState();
  
  // Reset ke data normal
  store.set({ 
    filteredData: null,
    searchKeyword: '',
    currentFilter: 'all',
    pagination: {
      ...pagination,
      currentPage: 1
    }
  });

  renderTransaksiTable(transaksiData, 'all');
  updatePaginationInfo(transaksiData.length);
  
  Toastify({
    text: "Pencarian dibersihkan",
    duration: 2000,
    gravity: "top",
    position: "right",
    backgroundColor: "#6b7280",
  }).showToast();
}

/**
 * Setup event listeners untuk search
 */
export function setupSearchListeners() {
  const searchInput = $('searchInput');
  const clearBtn = $('clearSearchBtn');
  
  if (searchInput) {
    // Trigger search saat Enter ditekan
    searchInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        searchFromState();
      }
    });
    
    // Real-time search saat mengetik (dengan debounce)
    let searchTimeout;
    searchInput.addEventListener('input', function(e) {
      const keyword = e.target.value.trim();
      
      // Toggle clear button visibility
      if (clearBtn) {
        if (keyword) {
          clearBtn.classList.remove('hidden');
        } else {
          clearBtn.classList.add('hidden');
        }
      }
      
      clearTimeout(searchTimeout);
      
      searchTimeout = setTimeout(() => {
        if (keyword.length >= 2 || keyword === '') {
          searchFromState(keyword);
        }
      }, 500); // Debounce 500ms
    });
  }
}

/**
 * Highlight search keyword dalam teks
 * @param {string} text - Teks yang akan di-highlight
 * @param {string} keyword - Keyword yang akan di-highlight
 */
export function highlightSearchKeyword(text, keyword) {
  if (!keyword || !text) return text;
  
  const regex = new RegExp(`(${keyword})`, 'gi');
  return text.replace(regex, '<mark class="bg-yellow-200 px-1 rounded">$1</mark>');
}

/**
 * Get search stats untuk ditampilkan
 */
export function getSearchStats() {
  const { transaksiData, filteredData, searchKeyword } = store.getState();
  
  if (!searchKeyword || !filteredData) {
    return {
      isSearching: false,
      keyword: '',
      totalResults: transaksiData.length,
      totalData: transaksiData.length
    };
  }
  
  return {
    isSearching: true,
    keyword: searchKeyword,
    totalResults: filteredData.length,
    totalData: transaksiData.length
  };
}

// Export functions ke window untuk HTML
window.searchFromState = searchFromState;
window.clearSearch = clearSearch;
