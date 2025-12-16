document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("table-body");
  const filterForm = document.getElementById("filter-form");
  const filterSubmitButton = document.getElementById("filter-submit-button");
  const summaryTotalReq = document.getElementById("summary-total-req");
  const summaryEmail = document.getElementById("summary-email");
  const summaryHp = document.getElementById("summary-hp");
  const summarySuccess = document.getElementById("summary-success");
  const paginationInfo = document.getElementById("pagination-info");
  const paginationLinks = document.getElementById("pagination-links");
  function formatDateIndo(dateString) {
    if (!dateString) return "-";
    const date = new Date(dateString);
    return new Intl.DateTimeFormat("id-ID", {
      day: "numeric",
      month: "short",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
    }).format(date);
  }
  function formatNumber(num) {
    return new Intl.NumberFormat("id-ID").format(num || 0);
  }
  function getUrlParams() {
    const params = new URLSearchParams(window.location.search);

    const inputMulai = document.getElementById("tgl_mulai");
    const inputSelesai = document.getElementById("tgl_selesai");
    const inputSearch = document.getElementById("search");

    return {
      tgl_mulai:
        params.get("tgl_mulai") || (inputMulai ? inputMulai.value : ""),
      tgl_selesai:
        params.get("tgl_selesai") || (inputSelesai ? inputSelesai.value : ""),
      search: params.get("search") || (inputSearch ? inputSearch.value : ""),
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
    const queryString = new URLSearchParams(params).toString();
    try {
      const response = await fetch(
        `/src/api/logs/get_password_reset.php?${queryString}`
      );
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();
      if (data.error) {
        throw new Error(data.error);
      }
      if (data.summary) {
        if (summaryTotalReq)
          summaryTotalReq.textContent = formatNumber(
            data.summary.total_request
          );
        if (summaryEmail)
          summaryEmail.textContent = formatNumber(data.summary.total_email);
        if (summaryHp)
          summaryHp.textContent = formatNumber(data.summary.total_hp);
        if (summarySuccess)
          summarySuccess.textContent = formatNumber(data.summary.total_success);
      }
      renderTable(data.tabel_data, data.pagination);
      renderPagination(data.pagination);
    } catch (error) {
      console.error("Error loading log data:", error);
      if (tableBody) {
        tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center p-8 text-red-600">
                            <i class="fas fa-exclamation-triangle fa-lg mb-2"></i>
                            <p>Gagal memuat data: ${error.message}</p>
                        </td>
                    </tr>`;
      }
    } finally {
      setLoadingState(false);
    }
  }
  function setLoadingState(isLoading) {
    if (isLoading) {
      if (filterSubmitButton) {
        filterSubmitButton.disabled = true;
        filterSubmitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span>Memuat...</span>`;
      }
      if (tableBody) {
        tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center p-8">
                            <div class="spinner-simple"></div>
                            <p class="mt-2 text-gray-500">Sedang mengambil data...</p>
                        </td>
                    </tr>`;
      }
      if (summaryTotalReq) summaryTotalReq.textContent = "-";
      if (summaryEmail) summaryEmail.textContent = "-";
      if (summaryHp) summaryHp.textContent = "-";
      if (summarySuccess) summarySuccess.textContent = "-";
    } else {
      if (filterSubmitButton) {
        filterSubmitButton.disabled = false;
        filterSubmitButton.innerHTML = `<i class="fas fa-filter"></i><span>Tampilkan</span>`;
      }
    }
  }
  function renderTable(data, pagination) {
    if (!tableBody) return;
    if (!data || data.length === 0) {
      tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center p-8 text-gray-500">
                        <i class="fas fa-search fa-lg mb-2 text-gray-300"></i>
                        <p>Tidak ada data ditemukan.</p>
                    </td>
                </tr>`;
      return;
    }
    const offset = pagination.offset || 0;
    let htmlRows = "";
    data.forEach((row, index) => {
      let userInfo = `<span class="text-gray-400 italic">Tidak diketahui</span>`;
      if (row.email) {
        userInfo = `
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 text-xs">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <span class="font-medium text-gray-700">${row.email}</span>
                    </div>`;
      } else if (row.no_hp) {
        userInfo = `
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center text-green-600 text-xs">
                            <i class="fas fa-phone"></i>
                        </div>
                        <span class="font-medium text-gray-700">${row.no_hp}</span>
                    </div>`;
      }
      const isUsed = parseInt(row.used) === 1;
      const statusBadge = isUsed
        ? `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200">
                     <i class="fas fa-check mr-1"></i> Diganti
                   </span>`
        : `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                     <i class="fas fa-clock mr-1"></i> Pending
                   </span>`;
      const shortToken = row.token ? row.token.substring(0, 20) + "..." : "-";
      htmlRows += `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td>${offset + index + 1}</td>
                    <td>${userInfo}</td>
                    <td >${statusBadge}</td>
                    <td class="text-gray-600">
                        ${formatDateIndo(row.dibuat_tgl)}
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
               class="pagination-link ${
                 current_page === 1 ? "pagination-disabled" : ""
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
        linksHtml += `<span class="pagination-ellipsis">...</span>`;
      }
      linksHtml += `
                <a href="#" data-page="${page_num}" 
                   class="pagination-link ${
                     page_num === current_page ? "pagination-active" : ""
                   }">
                    ${page_num}
                </a>
            `;
      last_page = page_num;
    }
    linksHtml += `
            <a href="${
              current_page < total_pages ? "#" : "javascript:void(0)"
            }" 
               data-page="${current_page + 1}"
               class="pagination-link ${
                 current_page === total_pages ? "pagination-disabled" : ""
               }">
                <i class="fas fa-chevron-right"></i>
            </a>
        `;
    paginationLinks.innerHTML = linksHtml;
    document.querySelectorAll(".pagination-link").forEach((link) => {
      link.addEventListener("click", (e) => {
        e.preventDefault();
        if (!link.classList.contains("pagination-disabled")) {
          const page = link.getAttribute("data-page");
          const params = getUrlParams();
          params.page = page;
          updateUrl(params);
          loadLogData();
        }
      });
    });
  }
  if (filterForm) {
    filterForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const formData = new FormData(filterForm);
      const params = {};
      formData.forEach((value, key) => {
        params[key] = value;
      });
      params.page = 1;
      updateUrl(params);
      loadLogData();
    });
  }
  loadLogData();
});
