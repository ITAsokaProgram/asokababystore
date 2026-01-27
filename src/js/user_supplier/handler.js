document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.getElementById("supplier-table-body");
    const filterForm = document.getElementById("filter-form");
    const paginationInfo = document.getElementById("pagination-info");
    const paginationLinks = document.getElementById("pagination-links");
    let currentTableData = [];
    if (paginationLinks) {
      paginationLinks.addEventListener("click", (e) => {
        const link = e.target.closest("a.pagination-link");
        if (!link || link.classList.contains("pagination-disabled")) return;
        e.preventDefault();
        const url = link.getAttribute("href");
        window.history.pushState({}, "", url);
        loadData();
      });
    }
    if (filterForm) {
      filterForm.addEventListener("submit", (e) => {
        e.preventDefault();
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        params.set("page", "1"); 
        window.history.pushState({}, "", `?${params.toString()}`);
        loadData();
      });
    }
    async function loadData() {
      setLoading(true);
      const urlParams = new URLSearchParams(window.location.search);
      if (!urlParams.has('page')) urlParams.set('page', '1');
      try {
        const response = await fetch(
          `/src/api/user_supplier/get_suppliers.php?${urlParams.toString()}`
        );
        const data = await response.json();
        if (data.error) throw new Error(data.error);
        currentTableData = data.tabel_data || [];
        renderTable(data.tabel_data, data.pagination.offset);
        renderPagination(data.pagination);
      } catch (error) {
        console.error(error);
        tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-red-500 p-4">Error: ${error.message}</td></tr>`;
      } finally {
        setLoading(false);
      }
    }
    function setLoading(isLoading) {
      if (isLoading) {
        tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-8"><div class="spinner-simple"></div></td></tr>`;
      }
    }
    function renderTable(rows, offset) {
      if (!rows || rows.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-8 text-gray-500">Tidak ada data ditemukan.</td></tr>`;
        return;
      }
      let html = "";
      rows.forEach((row, index) => {
        let wilayahBadges = '-';
        if(row.wilayah) {
             const wilayahArray = row.wilayah.split(',');
             wilayahBadges = wilayahArray.map(w => 
                `<span class="inline-block px-2 py-0.5 mb-1 text-[10px] rounded bg-blue-50 text-blue-600 border border-blue-100">${w.trim()}</span>`
             ).join(' ');
        }
        let rowClass = "hover:bg-gray-50";
        html += `
           <tr class="${rowClass} border-b border-gray-100 transition group">
               <td class="text-center text-gray-500 text-sm py-3">${offset + index + 1}</td>
               <td class="text-sm font-semibold text-gray-700">
                   ${row.nama}
               </td> 
               <td class="text-sm text-gray-600 font-mono">
                   ${row.no_telpon || '-'}
               </td>
               <td class="text-sm text-gray-600">
                   ${row.email || '-'}
               </td>
               <td class="text-sm text-gray-600">
                   ${wilayahBadges}
               </td>
               <td >
                    <div class="flex gap-2">
                         <button type="button" onclick="openDetailRow(${index})" 
                            class="text-gray-400 hover:text-blue-600 transition" title="Lihat Detail">
                            <i class="fas fa-eye"></i>
                         </button>
                         <a href="update.php?id=${row.kode}" 
                            class="text-gray-400 hover:text-orange-500 transition" title="Edit">
                            <i class="fas fa-pencil-alt"></i>
                         </a>
                         <button type="button" onclick="confirmDelete(${row.kode}, '${row.nama}')" 
                            class="text-gray-400 hover:text-red-500 transition" title="Hapus">
                            <i class="fas fa-trash"></i>
                         </button>
                    </div>
               </td>
           </tr>
          `;
      });
      tableBody.innerHTML = html;
    }
    window.openDetailRow = function (index) {
        const rowData = currentTableData[index];
        if (rowData) {
            window.dispatchEvent(
                new CustomEvent("open-detail-modal", { detail: rowData })
            );
        }
    };
    window.confirmDelete = function (id, name) {
        if(confirm(`Apakah Anda yakin ingin menghapus data supplier: ${name}?`)) {
            fetch('/src/api/user_supplier/delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert(data.message); 
                    loadData(); 
                } else {
                    alert('Gagal: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan sistem.');
            });
        }
    }
    function renderPagination(pagination) {
      if (!pagination) {
        paginationInfo.textContent = "";
        paginationLinks.innerHTML = "";
        return;
      }
      const { current_page, total_pages, total_rows, limit, offset } = pagination;
      if (total_rows === 0) {
        paginationInfo.textContent = "Menampilkan 0 dari 0 data";
        paginationLinks.innerHTML = "";
        return;
      }
      const start_row = offset + 1;
      const end_row = Math.min(offset + limit, total_rows);
      paginationInfo.textContent = `Menampilkan ${start_row} - ${end_row} dari ${total_rows} data`;
      let linksHtml = "";
      linksHtml += `<a href="${current_page > 1 ? build_pagination_url(current_page - 1) : "#"}" class="pagination-link ${current_page === 1 ? "pagination-disabled" : ""}"><i class="fas fa-chevron-left"></i></a>`;
      for (let i = 1; i <= total_pages; i++) {
        if (i === 1 || i === total_pages || (i >= current_page - 1 && i <= current_page + 1)) {
            linksHtml += `<a href="${build_pagination_url(i)}" class="pagination-link ${i === Number(current_page) ? "pagination-active" : ""}">${i}</a>`;
        } else if (i === current_page - 2 || i === current_page + 2) {
            linksHtml += `<span class="pagination-ellipsis">...</span>`;
        }
      }
      linksHtml += `<a href="${current_page < total_pages ? build_pagination_url(current_page + 1) : "#"}" class="pagination-link ${current_page === total_pages ? "pagination-disabled" : ""}"><i class="fas fa-chevron-right"></i></a>`;
      paginationLinks.innerHTML = linksHtml;
    }
    function build_pagination_url(newPage) {
      const params = new URLSearchParams(window.location.search);
      params.set("page", newPage);
      return "?" + params.toString();
    }
    loadData();
});