document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("voucher-table-body");
  const filterForm = document.getElementById("filter-form");
  const filterSubmitButton = document.getElementById("filter-submit-button");
  const filterSelectStore = document.getElementById("kd_store");
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
  function formatDateTime(dateString) {
    if (!dateString) return "-";
    return dateString.substring(0, 19);
  }
  function formatJustDate(dateString) {
    if (!dateString) return "-";
    return dateString.substring(0, 10);
  }
  function getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    const today = new Date();
    const todayString = today.toLocaleDateString("en-CA");
    const lastMonth = new Date();
    lastMonth.setMonth(lastMonth.getMonth() - 1);
    const lastMonthString = lastMonth.toLocaleDateString("en-CA");
    return {
      tgl_mulai: params.get("tgl_mulai") || lastMonthString,
      tgl_selesai: params.get("tgl_selesai") || todayString,
      kd_store: params.get("kd_store") || "all",
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
      kd_store: params.kd_store,
      page: params.page,
    }).toString();
    try {
      const response = await fetch(
        `/src/api/voucher/get_vouchers.php?${queryString}`
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
      if (data.stores) {
        populateStoreFilter(data.stores, params.kd_store);
      }
      if (pageSubtitle) {
        let storeName = "Seluruh Cabang";
        if (
          filterSelectStore.options.length > 0 &&
          filterSelectStore.selectedIndex > -1
        ) {
          storeName =
            filterSelectStore.options[filterSelectStore.selectedIndex].text;
        }
        pageSubtitle.textContent = `Periode Tgl Awal ${params.tgl_mulai} s/d ${params.tgl_selesai} - ${storeName}`;
      }
      renderTable(
        data.tabel_data,
        data.pagination ? data.pagination.offset : 0,
        data.date_subtotals,
        data.pagination
      );
      renderPagination(data.pagination);
    } catch (error) {
      console.error("Error loading data:", error);
      showTableError(error.message);
      if (pageSubtitle) {
        pageSubtitle.textContent = "Gagal memuat data";
      }
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
                        <td colspan="11" class="text-center p-8">
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
                <td colspan="11" class="text-center p-8 text-red-600">
                    <i class="fas fa-exclamation-triangle fa-lg mb-2"></i>
                    <p>Gagal memuat data: ${message}</p>
                </td>
            </tr>`;
  }
  function populateStoreFilter(stores, selectedStore) {
    if (!filterSelectStore || filterSelectStore.options.length > 1) {
      filterSelectStore.value = selectedStore;
      return;
    }
    stores.forEach((store) => {
      const option = document.createElement("option");
      option.value = store.kd_store;
      option.textContent = `${store.kd_store} - ${store.nm_alias}`;
      if (store.kd_store === selectedStore) {
        option.selected = true;
      }
      filterSelectStore.appendChild(option);
    });
    filterSelectStore.value = selectedStore;
  }
  function renderTable(tabel_data, offset, date_subtotals, pagination) {
    if (!tabel_data || tabel_data.length === 0) {
      tableBody.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center p-8 text-gray-500">
                        <i class="fas fa-inbox fa-lg mb-2"></i>
                        <p>Tidak ada voucher ditemukan untuk filter ini.</p>
                    </td>
                </tr>`;
      return;
    }
    let htmlRows = "";
    let current_tanggal = null;
    let item_counter = offset + 1;
    function buildTanggalHeaderRow(tanggal) {
      const displayTanggal =
        tanggal && tanggal !== "unknown"
          ? formatJustDate(tanggal)
          : "Tanggal Tidak Valid";
      return `
                <tr class="header-tanggal-row">
                    <td colspan="11" class="px-4 py-2">
                        Tgl Awal: <span class="font-bold"> ${displayTanggal} </span>
                    </td>
                </tr>
            `;
    }
    tabel_data.forEach((row) => {
      const rawTanggal = row.tgl_awal;
      const rowDateOnly = rawTanggal ? rawTanggal.split(" ")[0] : "unknown";
      if (rowDateOnly !== current_tanggal) {
        current_tanggal = rowDateOnly;
        htmlRows += buildTanggalHeaderRow(rawTanggal);
      }
      const isExpired = new Date(row.tgl_akhir) < new Date();
      const isHabis = parseFloat(row.sisa) <= 0;
      let statusBadge = "";
      if (isHabis) {
        statusBadge = `<span class="px-2 py-1 bg-gray-200 text-gray-600 rounded text-xs">Habis</span>`;
      } else if (isExpired) {
        statusBadge = `<span class="px-2 py-1 bg-red-100 text-red-600 rounded text-xs">Expired</span>`;
      } else {
        statusBadge = `<span class="px-2 py-1 bg-green-100 text-green-600 rounded text-xs">Aktif</span>`;
      }
      htmlRows += `
                <tr class="hover:bg-gray-50">
                    <td class="text-center text-gray-500 text-sm">${item_counter}</td>
                    <td class="font-medium text-gray-900">${row.kd_voucher}</td>
                    <td class="">
                        <div class="text-sm font-semibold">${
                          row.pemilik || "-"
                        }</div>
                        <div class="text-xs text-gray-500">${row.kd_cust}</div>
                    </td>
                    <td class="text-center">${row.kd_store}</td>
                    <td class="">${formatRupiah(row.nilai)}</td>
                    <td class="">${formatRupiah(row.pakai)}</td>
                    <td class="font-bold text-gray-700">${formatRupiah(
                      row.sisa
                    )}</td>
                    <td class="text-sm">${formatDateTime(row.tgl_awal)}</td>
                    <td class="text-sm">${formatDateTime(row.tgl_akhir)}</td>
                    <td class="text-sm">${formatJustDate(row.tgl_buat)}</td>
                    <td class="text-center">${statusBadge}</td>
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
