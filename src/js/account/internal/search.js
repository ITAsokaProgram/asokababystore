// Search functionality for user management
let allUserData = [];
let filteredData = [];
let searchTimeout = null;

export const initSearch = (userData) => {
  allUserData = userData;
  filteredData = [...userData];
  
  const searchInput = document.getElementById('searchInput');
  const clearSearchBtn = document.getElementById('clearSearch');
  
  if (searchInput) {
    searchInput.addEventListener('input', handleSearch);
  }
  
  if (clearSearchBtn) {
    clearSearchBtn.addEventListener('click', clearSearch);
  }
};

const handleSearch = (event) => {
  const searchTerm = event.target.value.toLowerCase().trim();
  
  // Clear previous timeout
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }
  
  // Add loading state
  const tableBody = document.querySelector("tbody");
  if (tableBody) {
    tableBody.innerHTML = `
      <tr>
        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
          <div class="flex flex-col items-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-pink-500 mb-2"></div>
            <p class="text-sm">Mencari...</p>
          </div>
        </td>
      </tr>
    `;
  }
  
  // Debounce search with 300ms delay
  searchTimeout = setTimeout(() => {
    if (searchTerm === '') {
      filteredData = [...allUserData];
    } else {
      filteredData = allUserData.filter(user => 
        user.nama.toLowerCase().includes(searchTerm) ||
        user.kode.toString().includes(searchTerm) ||
        user.hak.toLowerCase().includes(searchTerm)
      );
    }
    
    // Re-render table with filtered data
    if (typeof window.renderTableUserInternal === 'function') {
      window.renderTableUserInternal(filteredData);
    }
    
    // Update pagination info
    updateSearchInfo();
  }, 300);
};

const clearSearch = () => {
  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    searchInput.value = '';
  }
  
  filteredData = [...allUserData];
  
  if (typeof window.renderTableUserInternal === 'function') {
    window.renderTableUserInternal(filteredData);
  }
  
  updateSearchInfo();
};

const updateSearchInfo = () => {
  const viewDataElement = document.getElementById('viewData');
  if (viewDataElement) {
    const totalResults = filteredData.length;
    const totalOriginal = allUserData.length;
    
    if (totalResults === totalOriginal) {
      viewDataElement.textContent = `Menampilkan ${totalResults} dari ${totalOriginal} pengguna`;
    } else {
      viewDataElement.textContent = `Menampilkan ${totalResults} hasil pencarian dari ${totalOriginal} pengguna`;
    }
  }
};

export const getFilteredData = () => {
  return filteredData;
};

export const updateAllUserData = (newData) => {
  allUserData = newData;
  filteredData = [...newData];
}; 