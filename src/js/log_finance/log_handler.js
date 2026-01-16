document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("logDetailModal");
  const closeButton = document.getElementById("closeLogModal");
  const tableBody = document.getElementById("log-table-body");
  const filterForm = document.getElementById("filter-form");
  const filterInputTanggal = document.getElementById("tanggal");
  const filterInputSearch = document.getElementById("search_ref");
  const filterSubmitButton = document.getElementById("filter-submit-button");
  const tanggalDipilihTeks = document.getElementById("tanggal-dipilih-teks");
  const summaryTotal = document.getElementById("summary-total");
  const summaryInsert = document.getElementById("summary-insert");
  const summaryUpdate = document.getElementById("summary-update");
  const summaryDelete = document.getElementById("summary-delete");
  const paginationInfo = document.getElementById("pagination-info");
  const paginationLinks = document.getElementById("pagination-links");
  const modalLogId = document.getElementById("modalLogId");
  const modalIp = document.getElementById("modalIp");
  const modalUa = document.getElementById("modalUa");
  const contentOld = document.getElementById("contentOld");
  const contentNew = document.getElementById("contentNew");
  function getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    const dateValue = filterInputTanggal ? filterInputTanggal.value : "";
    const searchValue = filterInputSearch ? filterInputSearch.value : "";
    return {
      tanggal: params.get("tanggal") || dateValue,
      search: params.get("search") || searchValue,
      page: parseInt(params.get("page") || "1", 10),
    };
  }
  function updateUrl(params) {
    const newUrl = `${window.location.pathname}?${new URLSearchParams(
      params
    ).toString()}`;
    window.history.pushState({}, "", newUrl);
  }
  async function loadLogData() {
    const params = getUrlParams();
    setLoadingState(true);
    try {
      const queryString = new URLSearchParams(params).toString();
      const response = await fetch(
        `/src/api/log_finance/get_log_summary.php?${queryString}`
      );
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();
      if (data.error) {
        throw new Error(data.error);
      }
      updateSummaryCards(data.summary);
      renderTable(data.tabel_data, data.pagination);
      renderPagination(data.pagination);
      tanggalDipilihTeks.textContent = params.tanggal;
      if (filterInputTanggal) filterInputTanggal.value = params.tanggal;
      if (filterInputSearch) filterInputSearch.value = params.search;
    } catch (error) {
      console.error("Error loading log data:", error);
      showTableError(error.message);
    } finally {
      setLoadingState(false);
    }
  }
  function setLoadingState(isLoading) {
    if (isLoading) {
      filterSubmitButton.disabled = true;
      filterSubmitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span>Memuat...</span>`;
      tableBody.innerHTML = `
        <tr>
            <td colspan="5" class="text-center p-8">
                <div class="spinner-simple animate-spin inline-block w-6 h-6 border-2 border-current border-t-transparent text-pink-600 rounded-full"></div>
                <p class="mt-2 text-gray-500">Sedang mengambil data...</p>
            </td>
        </tr>`;
      resetSummary();
      if (paginationInfo) paginationInfo.textContent = "";
      if (paginationLinks) paginationLinks.innerHTML = "";
    } else {
      filterSubmitButton.disabled = false;
      filterSubmitButton.innerHTML = `<i class="fas fa-filter"></i><span>Tampilkan</span>`;
    }
  }
  function resetSummary() {
    summaryTotal.textContent = "-";
    summaryInsert.textContent = "-";
    summaryUpdate.textContent = "-";
    summaryDelete.textContent = "-";
  }
  function updateSummaryCards(summary) {
    if (!summary) return;
    summaryTotal.textContent = summary.total || 0;
    summaryInsert.textContent = summary.insert || 0;
    summaryUpdate.textContent = summary.update || 0;
    summaryDelete.textContent = summary.delete || 0;
  }
  function renderTable(data, pagination) {
    if (!data || data.length === 0) {
      tableBody.innerHTML = `
        <tr>
            <td colspan="5" class="text-center p-8 text-gray-500">
                <i class="fas fa-inbox fa-2x mb-3 text-gray-300"></i>
                <p>Tidak ada aktivitas ditemukan.</p>
            </td>
        </tr>`;
      return;
    }
    let htmlRows = "";
    data.forEach((row) => {
      let badgeClass = "bg-gray-100 text-gray-800";
      let icon = "fa-circle";
      if (row.action === "INSERT") {
        badgeClass = "bg-green-100 text-green-800 border border-green-200";
        icon = "fa-plus";
      } else if (row.action === "UPDATE") {
        badgeClass = "bg-blue-100 text-blue-800 border border-blue-200";
        icon = "fa-pencil-alt";
      } else if (row.action === "DELETE") {
        badgeClass = "bg-red-100 text-red-800 border border-red-200";
        icon = "fa-trash";
      }
      const safeOldData = encodeURIComponent(row.old_data || "null");
      const safeNewData = encodeURIComponent(row.new_data || "null");
      const safeIp = row.ip_address || "-";
      const safeUa = row.user_agent || "-";
      const refIdDisplay = row.ref_id || '-';
      const copyBtnHtml = row.ref_id ? `
        <button type="button" 
                class="btn-copy ml-2 text-gray-400 hover:text-pink-600 transition-colors p-1 rounded hover:bg-pink-50"
                data-copy="${row.ref_id}" 
                title="Salin Ref ID">
            <i class="far fa-copy"></i>
        </button>
      ` : '';
      htmlRows += `
        <tr class="clickable-row border-b border-gray-100 transition-colors hover:bg-pink-50"
            data-id="${row.id}"
            data-ip="${safeIp}"
            data-ua="${safeUa}"
            data-old="${safeOldData}"
            data-new="${safeNewData}"
            title="Klik untuk melihat detail">
            <td class="p-4 whitespace-nowrap text-gray-600">
                ${row.created_at}
            </td>
            <td class="p-4 font-semibold text-gray-800">
                ${row.user_inisial} 
            </td>
            <td class="p-4 text-pink-600 font-medium">
                ${row.table_name}
            </td>
            <td class="p-4 font-mono text-gray-600 text-xs">
                <div class="flex items-center">
                    <span>${refIdDisplay}</span>
                    ${copyBtnHtml}
                </div>
            </td>
            <td class="p-4">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass}">
                    <i class="fas ${icon} text-[10px]"></i> ${row.action}
                </span>
            </td>
        </tr>
      `;
    });
    tableBody.innerHTML = htmlRows;
  }
  function renderPagination(pagination) {
    if (!paginationInfo || !paginationLinks) return;
    if (!pagination || pagination.total_rows === 0) {
      paginationInfo.textContent = "";
      paginationLinks.innerHTML = "";
      return;
    }
    const { current_page, total_pages, total_rows, limit, offset } = pagination;
    const start_row = offset + 1;
    const end_row = Math.min(offset + limit, total_rows);
    paginationInfo.textContent = `Menampilkan ${start_row} - ${end_row} dari ${total_rows} data`;
    let linksHtml = "";
    linksHtml += `
            <a href="${current_page > 1 ? "#" : "javascript:void(0)"}" 
               data-page="${current_page - 1}"
               class="pagination-link btn-pagination px-3 py-1 rounded border ${current_page === 1
        ? "bg-gray-100 text-gray-400 cursor-not-allowed border-gray-200"
        : "bg-white text-gray-700 hover:bg-gray-50 border-gray-300"
      }">
                <i class="fas fa-chevron-left"></i>
            </a>
        `;
    const max_pages_around = 2;
    let pages_to_show = [];
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
        linksHtml += `<span class="pagination-ellipsis px-2 text-gray-500">...</span>`;
      }
      const isActive = page_num === current_page;
      linksHtml += `
                <a href="#" data-page="${page_num}" 
                   class="pagination-link px-3 py-1 rounded border transition-colors ${isActive
          ? "bg-pink-600 text-white border-pink-600 pagination-active"
          : "bg-white text-gray-700 hover:bg-gray-50 border-gray-300"
        }">
                    ${page_num}
                </a>
            `;
      last_page = page_num;
    }
    linksHtml += `
            <a href="${current_page < total_pages ? "#" : "javascript:void(0)"}" 
               data-page="${current_page + 1}"
               class="pagination-link btn-pagination px-3 py-1 rounded border ${current_page === total_pages
        ? "bg-gray-100 text-gray-400 cursor-not-allowed border-gray-200"
        : "bg-white text-gray-700 hover:bg-gray-50 border-gray-300"
      }">
                <i class="fas fa-chevron-right"></i>
            </a>
        `;
    paginationLinks.innerHTML = linksHtml;
    document.querySelectorAll(".pagination-link").forEach((link) => {
      link.addEventListener("click", (e) => {
        e.preventDefault();
        if (!link.classList.contains("cursor-not-allowed") && !link.classList.contains("pagination-active")) {
          const page = link.getAttribute("data-page");
          const params = getUrlParams();
          params.page = page;
          updateUrl(params);
          loadLogData();
        }
      });
    });
  }
  function showTableError(message) {
    tableBody.innerHTML = `
        <tr>
            <td colspan="5" class="text-center p-8 text-red-600">
                <i class="fas fa-exclamation-triangle fa-lg mb-2"></i>
                <p>Gagal memuat data: ${message}</p>
            </td>
        </tr>`;
  }
  tableBody.addEventListener("click", (e) => {
    const copyBtn = e.target.closest(".btn-copy");
    if (copyBtn) {
      e.stopPropagation();
      const textToCopy = copyBtn.dataset.copy;
      navigator.clipboard.writeText(textToCopy).then(() => {
        const icon = copyBtn.querySelector("i");
        const originalClass = icon.className;
        icon.className = "fas fa-check text-green-500";

      }).catch(err => {
        console.error("Gagal menyalin:", err);
      });
      return;
    }
    const tr = e.target.closest("tr");
    if (tr && tr.classList.contains("clickable-row")) {
      const id = tr.dataset.id;
      const ip = tr.dataset.ip;
      const ua = tr.dataset.ua;
      const oldData = decodeURIComponent(tr.dataset.old);
      const newData = decodeURIComponent(tr.dataset.new);
      openModal(id, ip, ua, oldData, newData);
    }
  });
  function openModal(id, ip, ua, oldDataStr, newDataStr) {
    modalLogId.textContent = "#" + id;
    modalIp.textContent = ip;
    modalUa.textContent = ua;
    try {
      const oldJson = oldDataStr !== "null" ? JSON.parse(oldDataStr) : null;
      contentOld.textContent = oldJson ? JSON.stringify(oldJson, null, 2) : "NULL";
    } catch (e) {
      contentOld.textContent = oldDataStr;
    }
    try {
      const newJson = newDataStr !== "null" ? JSON.parse(newDataStr) : null;
      contentNew.textContent = newJson ? JSON.stringify(newJson, null, 2) : "NULL";
    } catch (e) {
      contentNew.textContent = newDataStr;
    }
    modal.style.display = "flex";
  }
  function closeModal() {
    modal.style.display = "none";
    modalLogId.textContent = "";
    contentOld.textContent = "";
    contentNew.textContent = "";
  }
  if (filterForm) {
    filterForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const params = getUrlParams();
      params.page = 1;
      params.tanggal = filterInputTanggal.value;
      params.search = filterInputSearch.value;
      updateUrl(params);
      loadLogData();
    });
  }
  if (closeButton) closeButton.addEventListener("click", closeModal);
  if (modal) {
    modal.addEventListener("click", (e) => {
      if (e.target === modal) closeModal();
    });
  }
  loadLogData();
});