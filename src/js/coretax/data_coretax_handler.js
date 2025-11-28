document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("coretax-table-body");
  const filterForm = document.getElementById("filter-form");
  const filterSubmitButton = document.getElementById("filter-submit-button");
  const filterSelectStore = document.getElementById("kd_store");
  const filterSelectStatus = document.getElementById("status_data"); // Baru
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
      kd_store: params.get("kd_store") || "all",
      status_data: params.get("status_data") || "all", // Baru
      search_supplier: (params.get("search_supplier") || "").trim(),
      page: parseInt(params.get("page") || "1", 10),
    };
  }

  function build_pagination_url(newPage) {
    const params = new URLSearchParams(window.location.search);
    params.set("page", newPage);
    return "?" + params.toString();
  }

  // Populate Filter Store
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

  async function loadData() {
    const params = getUrlParams();
    const isPagination = params.page > 1;
    setLoadingState(true, isPagination);

    const queryString = new URLSearchParams({
      tgl_mulai: params.tgl_mulai,
      tgl_selesai: params.tgl_selesai,
      kd_store: params.kd_store,
      status_data: params.status_data, // Baru
      search_supplier: params.search_supplier,
      page: params.page,
    }).toString();

    try {
      const response = await fetch(
        `/src/api/coretax/get_data_coretax.php?${queryString}`
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

      // --- Handle Store Filter ---
      if (data.stores) {
        populateStoreFilter(data.stores, params.kd_store);
      }

      // --- Update UI Inputs ---
      if (filterInputSupplier)
        filterInputSupplier.value = params.search_supplier;
      if (filterSelectStatus) filterSelectStatus.value = params.status_data;

      // --- Update Subtitle ---
      if (pageSubtitle) {
        let storeName = "";
        if (
          filterSelectStore.options.length > 0 &&
          filterSelectStore.selectedIndex > -1 &&
          filterSelectStore.value !== "all"
        ) {
          storeName =
            " - " +
            filterSelectStore.options[filterSelectStore.selectedIndex].text;
        }
        pageSubtitle.textContent = `Periode ${params.tgl_mulai} s/d ${params.tgl_selesai}${storeName}`;
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
                  <td colspan="13" class="text-center p-8"> <div class="spinner-simple"></div>
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
            <td colspan="13" class="text-center p-8 text-red-600"> <i class="fas fa-exclamation-triangle fa-lg mb-2"></i>
                <p>Gagal memuat data: ${message}</p>
            </td>
        </tr>`;
  }

  function renderTable(tabel_data, offset) {
    if (!tabel_data || tabel_data.length === 0) {
      tableBody.innerHTML = `
            <tr>
                <td colspan="13" class="text-center p-8 text-gray-500"> <i class="fas fa-inbox fa-lg mb-2"></i>
                    <p>Tidak ada data ditemukan untuk filter ini.</p>
                </td>
            </tr>`;
      return;
    }

    let htmlRows = "";
    let item_counter = offset + 1;

    tabel_data.forEach((row, index) => {
      const harga_jual = parseFloat(row.harga_jual) || 0;
      const dpp_nilai_lain = parseFloat(row.dpp_nilai_lain) || 0;
      const ppn = parseFloat(row.ppn) || 0;

      const dateObj = new Date(row.tgl_faktur_pajak);
      const dateFormatted = dateObj.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
      });

      // --- Logic NSFP Duplikat/Pengganti ---
      const currentNsfp = row.nsfp || "";
      const currentSuffix =
        currentNsfp.length >= 8 ? currentNsfp.slice(-8) : currentNsfp;
      const prevRow = index > 0 ? tabel_data[index - 1] : null;
      const prevNsfp = prevRow ? prevRow.nsfp || "" : "";
      const prevSuffix = prevNsfp.length >= 8 ? prevNsfp.slice(-8) : "";
      const nextRow =
        index < tabel_data.length - 1 ? tabel_data[index + 1] : null;
      const nextNsfp = nextRow ? nextRow.nsfp || "" : "";
      const nextSuffix = nextNsfp.length >= 8 ? nextNsfp.slice(-8) : "";

      const isDuplicate =
        currentSuffix === prevSuffix || currentSuffix === nextSuffix;
      let rowClass = "border-b transition-colors ";
      let duplicateBadge = "";

      if (isDuplicate) {
        rowClass += "bg-yellow-50 hover:bg-yellow-100";
        if (currentNsfp.length > 3 && currentNsfp[2] === "1") {
          duplicateBadge = `<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[9px] font-medium bg-red-100 text-red-800">GANTI</span>`;
        } else if (
          currentNsfp.length > 3 &&
          currentNsfp[2] === "0" &&
          (nextNsfp[2] === "1" || prevNsfp[2] === "1")
        ) {
          duplicateBadge = `<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[9px] font-medium bg-gray-100 text-gray-800">DIGANTI</span>`;
        }
      } else {
        rowClass += "hover:bg-gray-50";
      }

      // --- Logic BADGE STATUS (Kolom Terpisah) ---

      // 1. Badge Pembelian
      let badgePembelian = `<span class="text-gray-300 text-xs">-</span>`;
      if (row.ada_pembelian == 1) {
        badgePembelian = `
                    <div class="flex flex-col items-center justify-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800 border border-green-200">
                            <i class="fas fa-check mr-1"></i> OK
                        </span>
                    </div>`;
      } else {
        badgePembelian = `
                    <div class="flex flex-col items-center justify-center">
                         <span class="text-[10px] text-gray-400 italic">-</span>
                    </div>`;
      }

      // 2. Badge Fisik (Gantikan Coretax di file sebelumnya)
      let badgeFisik = `<span class="text-gray-300 text-xs">-</span>`;
      if (row.ada_fisik == 1) {
        badgeFisik = `
                    <div class="flex flex-col items-center justify-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                            <i class="fas fa-file-invoice mr-1"></i> ADA
                        </span>
                    </div>`;
      } else {
        badgeFisik = `
                    <div class="flex flex-col items-center justify-center">
                         <span class="text-[10px] text-gray-400 italic">-</span>
                    </div>`;
      }

      htmlRows += `
            <tr class="${rowClass}">
                <td class="text-center font-medium text-gray-500">${item_counter}</td>
                <td class="whitespace-nowrap">${dateFormatted}</td>
                
                <td class="text-sm">
                     <div class="font-mono text-gray-500 text-xs">${
                       row.npwp_penjual || "-"
                     }</div>
                     <div class="font-medium text-gray-800 truncate max-w-xs" title="${
                       row.nama_penjual
                     }">${row.nama_penjual || "-"}</div>
                </td>

                <td class="font-semibold text-gray-700">
                    <div class="flex items-center">
                        <span class="font-mono">${row.nsfp}</span>
                        ${duplicateBadge}
                    </div>
                </td>
                
                <td class="text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-blue-50 text-blue-800">
                        ${row.Nm_Alias || row.kode_store || "-"}
                    </span>
                </td>

                <td class="text-center text-sm">
                    ${row.masa_pajak || "-"}/${row.tahun || "-"}
                </td>

                 <td class="text-center text-sm text-gray-600">
                    ${row.masa_pajak_pengkreditkan || "-"}/${
        row.tahun_pajak_pengkreditan || "-"
      }
                </td>

                <td class="text-right font-mono text-gray-700">${formatRupiah(
                  harga_jual
                )}</td>
                <td class="text-right font-mono text-gray-600">${formatRupiah(
                  dpp_nilai_lain
                )}</td>
                <td class="text-right font-mono text-red-600 font-bold">${formatRupiah(
                  ppn
                )}</td>
                
                <td class="text-sm text-gray-600 truncate max-w-[100px]" title="${
                  row.perekam
                }">
                    ${row.perekam || "-"}
                </td>

                <td class="text-center align-middle border-l border-gray-100 bg-green-50/30">
                    ${badgePembelian}
                </td>
                <td class="text-center align-middle border-l border-gray-100 bg-blue-50/30">
                    ${badgeFisik}
                </td>
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

      // Manual Trim
      const searchVal = formData.get("search_supplier").toString().trim();
      formData.set("search_supplier", searchVal);

      const params = new URLSearchParams(formData);
      params.set("page", "1");
      window.history.pushState({}, "", `?${params.toString()}`);
      loadData();
    });
  }
  loadData();
});
