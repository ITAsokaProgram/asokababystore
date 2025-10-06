import { fetchRewards } from "./fetch.js";
import FilterHandler from "./filterHandler.js";

const formatDate = (dateString) => {
    if (!dateString) return '';
    const options = { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return new Date(dateString).toLocaleString('id-ID', options);
};


let currentPageSize = 10;
let currentOffset = 0;
let totalRecords = 0;
let currentPage = 1;
let totalPages = 1;

const updatePaginationUI = () => {
    const paginationContainer = document.getElementById('paginationContainer');
    const pageNumbers = document.getElementById('pageNumbers');
    const firstPageBtn = document.getElementById('firstPage');
    const prevPageBtn = document.getElementById('prevPage');
    const nextPageBtn = document.getElementById('nextPage');
    const lastPageBtn = document.getElementById('lastPage');
    const dataInfo = document.getElementById('dataInfo');
    
    if (!paginationContainer) return;

    
    totalPages = Math.ceil(totalRecords / currentPageSize);
    currentPage = Math.floor(currentOffset / currentPageSize) + 1;

    
    const startItem = totalRecords > 0 ? currentOffset + 1 : 0;
    const endItem = Math.min(currentOffset + currentPageSize, totalRecords);
    dataInfo.textContent = `Menampilkan ${startItem}-${endItem || 0} dari ${totalRecords || 0} hadiah`;

    
    firstPageBtn.disabled = currentPage === 1;
    prevPageBtn.disabled = currentPage === 1;
    nextPageBtn.disabled = currentPage >= totalPages;
    lastPageBtn.disabled = currentPage >= totalPages;

    
    pageNumbers.innerHTML = '';
    const maxPagesToShow = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

    if (endPage - startPage + 1 < maxPagesToShow) {
        startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }

    
    for (let i = startPage; i <= endPage; i++) {
        const pageBtn = document.createElement('button');
        pageBtn.textContent = i;
        pageBtn.className = `w-10 h-10 rounded-lg flex items-center justify-center ${
            i === currentPage 
                ? 'bg-pink-600 text-white' 
                : 'text-pink-600 hover:bg-pink-50'
        }`;
        pageBtn.addEventListener('click', () => {
            currentOffset = (i - 1) * currentPageSize;
            renderTableRewards();
        });
        pageNumbers.appendChild(pageBtn);
    }
};


const initPagination = () => {
    
    const pageSizeSelect = document.getElementById('pageSize');
    if (pageSizeSelect) {
        pageSizeSelect.value = currentPageSize;
        pageSizeSelect.addEventListener('change', (e) => {
            currentPageSize = parseInt(e.target.value);
            currentOffset = 0; 
            renderTableRewards();
        });
    }

    
    document.getElementById('firstPage')?.addEventListener('click', () => {
        if (currentPage > 1) {
            currentOffset = 0;
            renderTableRewards();
        }
    });

    document.getElementById('prevPage')?.addEventListener('click', () => {
        if (currentPage > 1) {
            currentOffset = Math.max(0, currentOffset - currentPageSize);
            renderTableRewards();
        }
    });

    document.getElementById('nextPage')?.addEventListener('click', () => {
        if (currentPage < totalPages) {
            currentOffset += currentPageSize;
            renderTableRewards();
        }
    });

    document.getElementById('lastPage')?.addEventListener('click', () => {
        if (currentPage < totalPages) {
            currentOffset = (totalPages - 1) * currentPageSize;
            renderTableRewards();
        }
    });
};


const renderTableRewards = async () => {
    const rewards = await fetchRewards(currentPageSize, currentOffset);
    totalRecords = rewards.total;
    const tableBody = document.getElementById('tableBody');
    
    if (tableBody) {
        tableBody.innerHTML = '';
        
        if (rewards.data.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td colspan="10" class="px-4 py-6 text-center text-gray-500">
                    Tidak ada data hadiah yang tersedia
                </td>
            `;
            tableBody.appendChild(row);
        } else {
            rewards.data.forEach((reward, index) => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-amber-50 transition-colors duration-200';
                
                row.innerHTML = `
                    <td class="px-4 py-3 text-sm text-gray-700">${currentOffset + index + 1}</td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">${reward.plu || '-'}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">${reward.nama_hadiah || '-'}</td>
                    <td class="px-4 py-3 text-sm text-center font-medium">
                        <span class="px-2 py-1 bg-amber-100 text-amber-800 rounded-full text-xs">
                            ${reward.poin || '0'}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900">${reward.nama_karyawan || '-'}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">${reward.kode_karyawan || '-'}</td>
                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                        ${formatDate(reward.tanggal_dibuat) || '-'}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                        ${formatDate(reward.tanggal_diubah) || '-'}
                    </td>
                    <td class="px-4 py-3 text-sm text-center font-medium">
                        <span class="px-2 py-1 ${reward.qty > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'} rounded-full text-xs">
                            ${reward.qty || '0'}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 truncate" title="${reward.nm_alias || '-'}">${formatAlias(reward.nm_alias) || '-'}</td>
                    <td class="px-4 py-3 text-sm text-center">
                        <div class="flex items-center justify-center space-x-2">
                            <button class="edit-btn text-blue-600 hover:text-blue-800 transition-colors p-1 rounded-full hover:bg-blue-50" 
                                    data-id="${reward.id_hadiah}" title="Edit">
                                <i class="fas fa-edit w-4 h-4"></i>
                            </button>
                            <button class="delete-btn text-red-600 hover:text-red-800 transition-colors p-1 rounded-full hover:bg-red-50" 
                                    data-id="${reward.id_hadiah}" title="Hapus">
                                <i class="fas fa-trash w-4 h-4"></i>
                            </button>
                             <span class="border-l h-5 border-gray-300"></span> <button class="receive-stock-btn text-green-600 hover:text-green-800 p-1 rounded-full hover:bg-green-50" 
                                    data-plu="${reward.plu}" data-kd_store="${reward.kd_store}" title="Terima Stok">
                                <i class="fas fa-plus-circle w-4 h-4"></i>
                            </button>
                            <button class="update-point-btn text-yellow-600 hover:text-yellow-800 p-1 rounded-full hover:bg-yellow-50" 
                                    data-plu="${reward.plu}" data-kd_store="${reward.kd_store}" title="Update Poin">
                                <i class="fas fa-coins w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        }
    }
    
    updatePaginationUI();
    
};

const formatAlias = (alias) => {
    if(!alias) return '-';
    const aliasArr = alias.split(',').map(item => item.trim());
    if(aliasArr.length <= 3) return aliasArr.join(", ");
    return aliasArr.slice(0, 3).join(', ') + '...';
}


const filterHandler = new FilterHandler;

document.addEventListener('DOMContentLoaded', () => {
    
    const pageSizeSelect = document.getElementById('pageSize');
    if (pageSizeSelect) {
        pageSizeSelect.addEventListener('change', (e) => {
            filterHandler.setPageSize(parseInt(e.target.value));
        });
    }

    
    document.getElementById('firstPage')?.addEventListener('click', () => {
        filterHandler.goToPage(1);
    });

    document.getElementById('prevPage')?.addEventListener('click', () => {
        filterHandler.goToPage(filterHandler.currentFilters.page - 1);
    });

    document.getElementById('nextPage')?.addEventListener('click', () => {
        filterHandler.goToPage(filterHandler.currentFilters.page + 1);
    });

    document.getElementById('lastPage')?.addEventListener('click', () => {
        filterHandler.goToPage(window.totalPages);
    });

    
    filterHandler.applyFilters();
});

export { renderTableRewards };