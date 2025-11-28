document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("receipt-table-body");
  const filterForm = document.getElementById("filter-form");
  const filterSubmitButton = document.getElementById("filter-submit-button");
  const filterInputSupplier = document.getElementById("search_supplier");
  const pageTitle = document.getElementById("page-title");
  const pageSubtitle = document.getElementById("page-subtitle");
  const paginationContainer = document.getElementById("pagination-container");
  const paginationInfo = document.getElementById("pagination-info");
  const paginationLinks = document.getElementById("pagination-links");
  function formatRupiah(number) {
    if (isNaN(number) || number === null) {
      return "0";
    }
    return new Intl.NumberFormat("id-ID", {
      style: "decimal",
      currency: "IDR",
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(number);
  }
  function getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    const yesterdayString = yesterday.toISOString().split("T")[0];
    return {
      tgl_mulai: params.get("tgl_mulai") || yesterdayString,
      tgl_selesai: params.get("tgl_selesai") || yesterdayString,
      search_supplier: params.get("search_supplier") || "",
      page: parseInt(params.get("page") || "1", 10),
    };
  }
  function build_pagination_url(newPage) {
    const params = new URLSearchParams(window.location.search);
    params.set("page", newPage);
    return "?" + params.toString();
  }
  async function loadData() {
    const params = getUrlParams();
    const isPagination = params.page > 1;
    setLoadingState(true, isPagination);
    const queryString = new URLSearchParams({
      tgl_mulai: params.tgl_mulai,
      tgl_selesai: params.tgl_selesai,
      search_supplier: params.search_supplier,
      page: params.page,
    }).toString();
    try {
      const response = await fetch(
        `/src/api/coretax/get_laporan_faktur_pajak.php?${queryString}`
      );
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(
          errorData.error || `HTTP error! status: ${response.status}`
        );
      }
      const data = await response.json();
      if (data.error) {
        throw new Error(data.error);
      }
      if (filterInputSupplier) {
        filterInputSupplier.value = params.search_supplier;
      }
      if (pageSubtitle) {
        pageSubtitle.textContent = `Periode ${params.tgl_mulai} s/d ${params.tgl_selesai}`;
      }
      renderTable(
        data.tabel_data,
        data.pagination ? data.pagination.offset : 0
      );
      renderPagination(data.pagination);
    } catch (error) {
      console.error("Error loading data:", error);
      showTableError(error.message);
    } finally {
      setLoadingState(false);
    }
  }
  function setLoadingState(isLoading, isPagination = false) {
    if (isLoading) {
      if (filterSubmitButton) filterSubmitButton.disabled = true;
      if (filterSubmitButton)
        filterSubmitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span>Memuat...</span>`;
      if (tableBody)
        tableBody.innerHTML = `
              <tr>
                  <td colspan="8" class="text-center p-8">
                      <div class="spinner-simple"></div>
                      <p class="mt-2 text-gray-500">Memuat data...</p>
                  </td>
              </tr>`;
      if (paginationInfo) paginationInfo.textContent = "";
      if (paginationLinks) paginationLinks.innerHTML = "";
    } else {
      if (filterSubmitButton) {
        filterSubmitButton.disabled = false;
        filterSubmitButton.innerHTML = `<i class="fas fa-filter"></i><span>Tampilkan</span>`;
      }
    }
  }
  function showTableError(message) {
    tableBody.innerHTML = `
        <tr>
            <td colspan="8" class="text-center p-8 text-red-600">
                <i class="fas fa-exclamation-triangle fa-lg mb-2"></i>
                <p>Gagal memuat data: ${message}</p>
            </td>
        </tr>`;
  }
  function renderTable(tabel_data, offset) {
    if (!tabel_data || tabel_data.length === 0) {
      tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center p-8 text-gray-500">
                    <i class="fas fa-inbox fa-lg mb-2"></i>
                    <p>Tidak ada data ditemukan untuk filter ini.</p>
                </td>
            </tr>`;
      return;
    }
    let htmlRows = "";
    let item_counter = offset + 1;
    tabel_data.forEach((row) => {
      const dpp = parseFloat(row.dpp) || 0;
      const dpp_lain = parseFloat(row.dpp_nilai_lain) || 0;
      const ppn = parseFloat(row.ppn) || 0;
      const total = parseFloat(row.total) || 0;
      const dateObj = new Date(row.tgl_faktur);
      const dateFormatted = dateObj.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
      });
      let syncBadges = "";
      if (row.ada_pembelian == 1) {
        syncBadges += `<span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800 border border-green-200" title="Terhubung dengan Data Pembelian"><i class="fas fa-check mr-1"></i>BELI</span>`;
      }
      if (row.ada_coretax == 1) {
        syncBadges += `<span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200" title="Ada Data Coretax"><i class="fas fa-cloud mr-1"></i>CORETAX</span>`;
      }
      htmlRows += `
            <tr class="hover:bg-gray-50">
                <td class="text-center font-medium text-gray-500">${item_counter}</td>
                <td class="font-semibold text-gray-700">
                    <div class="flex flex-col items-start">
                        <span class="font-mono">${row.nsfp}</span>
                        <div class="flex flex-wrap gap-1 mt-0.5">
                            ${syncBadges}
                        </div>
                    </div>
                </td>
                <td>${dateFormatted}</td>
                <td class="text-sm font-medium text-gray-800">${
                  row.nama_supplier || "-"
                }</td>
                <td class="text-right font-mono text-gray-700">${formatRupiah(
                  dpp
                )}</td>
                <td class="text-right font-mono text-gray-700">${formatRupiah(
                  dpp_lain
                )}</td>
                <td class="text-right font-mono text-red-600">${formatRupiah(
                  ppn
                )}</td>
                <td class="text-right font-bold text-gray-800">${formatRupiah(
                  total
                )}</td>
            </tr>
        `;
      item_counter++;
    });
    tableBody.innerHTML = htmlRows;
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
    linksHtml += `
          <a href="${
            current_page > 1 ? build_pagination_url(current_page - 1) : "#"
          }" 
             class="pagination-link ${
               current_page === 1 ? "pagination-disabled" : ""
             }">
              <i class="fas fa-chevron-left"></i>
          </a>
      `;
    const pages_to_show = [];
    const max_pages_around = 2;
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
            <a href="${build_pagination_url(page_num)}" 
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
            current_page < total_pages
              ? build_pagination_url(current_page + 1)
              : "#"
          }" 
             class="pagination-link ${
               current_page === total_pages ? "pagination-disabled" : ""
             }">
              <i class="fas fa-chevron-right"></i>
          </a>
      `;
    paginationLinks.innerHTML = linksHtml;
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
  loadData();
});
