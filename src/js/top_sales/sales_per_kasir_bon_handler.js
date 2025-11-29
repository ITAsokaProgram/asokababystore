document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("search-input"); // Ambil elemen input
  const tableBody = document.getElementById("top-sales-table-body");
  const filterForm = document.getElementById("filter-form");
  const filterSubmitButton = document.getElementById("filter-submit-button");
  const filterSelectStore = document.getElementById("kd_store");
  const summaryNetSales = document.getElementById("summary-net-sales");
  const summaryGrsMargin = document.getElementById("summary-grs-margin");
  const summaryHpp = document.getElementById("summary-hpp");
  const pageTitle = document.getElementById("page-title");
  const pageSubtitle = document.getElementById("page-subtitle");
  const paginationContainer = document.getElementById("pagination-container");
  const paginationInfo = document.getElementById("pagination-info");
  const paginationLinks = document.getElementById("pagination-links");
  const exportExcelButton = document.getElementById("export-excel-btn");
  const exportPdfButton = document.getElementById("export-pdf-btn");
  const params = getUrlParams();
  if (searchInput && params.search) {
    searchInput.value = params.search;
  }
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
  function formatNumber(number) {
    if (isNaN(number) || number === null) {
      return "0";
    }
    return new Intl.NumberFormat("id-ID", {
      minimumFractionDigits: 0,
      maximumFractionDigits: 2,
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
      search: params.get("search") || "", // <--- TAMBAHKAN INI
      page: parseInt(params.get("page") || "1", 10),
    };
  }
  function build_pagination_url(newPage) {
    const params = new URLSearchParams(window.location.search);
    params.set("page", newPage);
    return "?" + params.toString();
  }
  async function loadTopSalesData() {
    const params = getUrlParams();
    const isPagination = params.page > 1;
    setLoadingState(true, false, isPagination);

    // Update query string
    const queryString = new URLSearchParams({
      tgl_mulai: params.tgl_mulai,
      tgl_selesai: params.tgl_selesai,
      kd_store: params.kd_store,
      search: params.search, // <--- TAMBAHKAN INI
      page: params.page,
    }).toString();
    try {
      const response = await fetch(
        `/src/api/top_sales/get_sales_per_kasir_bon.php?${queryString}`
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
        pageSubtitle.textContent = `Sales per Kasir & Bon Periode ${params.tgl_mulai} s/d ${params.tgl_selesai} - ${storeName}`;
        if (pageTitle) {
          pageTitle.textContent = `Sales per Kasir & Bon - ${storeName}`;
        }
      }
      if (data.summary) {
        updateSummaryCards(data.summary);
      }
      renderTable(
        data.tabel_data,
        data.pagination,
        data.summary,
        data.date_subtotals
      );
      renderPagination(data.pagination);
    } catch (error) {
      console.error("Error loading top sales data:", error);
      showTableError(error.message);
      if (pageSubtitle) {
        pageSubtitle.textContent = "Gagal memuat data filter";
      }
      if (pageTitle) {
        pageTitle.textContent = "Sales per Kasir & Bon";
      }
    } finally {
      setLoadingState(false);
    }
  }
  function setLoadingState(
    isLoading,
    isExporting = false,
    isPagination = false
  ) {
    if (isLoading) {
      if (filterSubmitButton) filterSubmitButton.disabled = true;
      if (isExporting) {
        if (exportExcelButton) {
          exportExcelButton.disabled = true;
          exportExcelButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span>Exporting...</span>`;
        }
        if (exportPdfButton) {
          exportPdfButton.disabled = true;
          exportPdfButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span>Exporting...</span>`;
        }
      } else {
        if (filterSubmitButton)
          filterSubmitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span>Memuat...</span>`;
        if (tableBody)
          tableBody.innerHTML = `
                                    <tr>
                                        <td colspan="7" class="text-center p-8">
                                            <div class="spinner-simple"></div>
                                            <p class="mt-2 text-gray-500">Memuat data...</p>
                                        </td>
                                    </tr>`;
        if (!isPagination) {
          if (summaryNetSales) summaryNetSales.textContent = "-";
          if (summaryGrsMargin) summaryGrsMargin.textContent = "-";
          if (summaryHpp) summaryHpp.textContent = "-";
        }
        if (paginationInfo) paginationInfo.textContent = "";
        if (paginationLinks) paginationLinks.innerHTML = "";
      }
    } else {
      if (filterSubmitButton) {
        filterSubmitButton.disabled = false;
        filterSubmitButton.innerHTML = `<i class="fas fa-filter"></i><span>Tampilkan</span>`;
      }
      if (exportExcelButton) {
        exportExcelButton.disabled = false;
        exportExcelButton.innerHTML = `<i class="fas fa-file-excel"></i><span>Export Excel</span>`;
      }
      if (exportPdfButton) {
        exportPdfButton.disabled = false;
        exportPdfButton.innerHTML = `<i class="fas fa-file-pdf"></i><span>Export PDF</span>`;
      }
    }
  }
  function showTableError(message) {
    tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center p-8 text-red-600">
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
  function updateSummaryCards(summary) {
    summaryNetSales.textContent = formatRupiah(summary.total_net_sales);
    summaryGrsMargin.textContent = formatRupiah(summary.total_grs_margin);
    summaryHpp.textContent = formatRupiah(summary.total_hpp);
  }
  function renderTable(tabel_data, pagination, summary, date_subtotals) {
    if (!tabel_data || tabel_data.length === 0) {
      tableBody.innerHTML = `
                                <tr>
                                    <td colspan="7" class="text-center p-8 text-gray-500">
                                        <i class="fas fa-inbox fa-lg mb-2"></i>
                                        <p>Tidak ada data ditemukan untuk filter ini.</p>
                                    </td>
                                </tr>`;
      return;
    }
    let htmlRows = "";
    let bon_item_counter = 0;
    let current_no_bon = null;
    let current_tanggal = null;
    let subtotal_qty = 0;
    let subtotal_diskon = 0;
    let subtotal_total = 0;
    function buildTanggalHeaderRow(tanggal) {
      return `
                <tr class="header-tanggal-row">
                    <td colspan="7" class="px-4 py-2">
                        Tanggal: <span class="font-bold"> ${tanggal} </span>
                    </td>
                </tr>
            `;
    }
    function buildSubtotalTanggalRow(tanggal) {
      const subtotal = date_subtotals[tanggal] || {
        total_qty: 0,
        total_total_diskon: 0,
        total_total: 0,
      };
      return `
                <tr class="subtotal-tanggal-row">
                    <td colspan="3" class=" px-4 py-2 text-right font-bold" style="font-style: italic;">Sub Total Tanggal:</td>
                    <td class=" px-2 py-2">${formatNumber(
                      subtotal.total_qty
                    )}</td>
                    <td class=" px-2 py-2"></td>
                    <td class=" px-2 py-2">${formatRupiah(
                      subtotal.total_total_diskon
                    )}</td>
                    <td class=" px-2 py-2">${formatRupiah(
                      subtotal.total_total
                    )}</td>
                </tr>
            `;
    }
    function buildBonHeaderRow(row) {
      return `
                <tr class="header-faktur-row">
                    <td colspan="3" class="px-4 py-1 pl-6">
                        No Trans: <strong>${row.no_bon}</strong>
                    </td>
                    <td colspan="4" class="px-4 py-1">
                        Kasir: <strong>${row.kode_kasir} - ${row.nama_kasir}</strong>
                    </td>
                </tr>
            `;
    }
    function buildBonSubtotalRow(qty, diskon, total) {
      return `
                <tr class="subtotal-row">
                    <td colspan="3" class="text-right px-4 py-2 font-bold" style="font-style: italic;">
                        Sub Total Bon:
                    </td>
                    <td class="px-2 py-2">${formatNumber(qty)}</td>
                    <td class="px-2 py-2"></td>
                    <td class="px-2 py-2">${formatRupiah(diskon)}</td>
                    <td class="px-2 py-2">${formatRupiah(total)}</td>
                </tr>
            `;
    }
    tabel_data.forEach((row, index) => {
      const qty = parseFloat(row.qty) || 0;
      const diskon = parseFloat(row.total_diskon) || 0;
      const total = parseFloat(row.total) || 0;
      if (row.tanggal !== current_tanggal) {
        if (current_no_bon !== null) {
          htmlRows += buildBonSubtotalRow(
            subtotal_qty,
            subtotal_diskon,
            subtotal_total
          );
          htmlRows += `<tr class="group-spacer"><td colspan="7" style="padding: 2px; background-color: #f1f5f9;"></td></tr>`;
        }
        if (current_tanggal !== null && date_subtotals) {
          htmlRows += buildSubtotalTanggalRow(current_tanggal);
        }
        current_tanggal = row.tanggal;
        current_no_bon = null;
        htmlRows += buildTanggalHeaderRow(current_tanggal);
      }
      if (row.no_bon !== current_no_bon) {
        if (current_no_bon !== null) {
          htmlRows += buildBonSubtotalRow(
            subtotal_qty,
            subtotal_diskon,
            subtotal_total
          );
          htmlRows += `<tr class="group-spacer"><td colspan="7" style="padding: 2px; background-color: #f1f5f9;"></td></tr>`;
        }
        current_no_bon = row.no_bon;
        bon_item_counter = 1;
        subtotal_qty = 0;
        subtotal_diskon = 0;
        subtotal_total = 0;
        htmlRows += buildBonHeaderRow(row);
      }
      subtotal_qty += qty;
      subtotal_diskon += diskon;
      subtotal_total += total;
      htmlRows += `
                <tr>
                    <td>${bon_item_counter}</td> <td>${row.plu}</td>
                    <td class="text-left">${row.nama_barang}</td>
                    <td class="">${formatNumber(qty)}</td>
                    <td class="">${formatRupiah(row.harga)}</td>
                    <td class="">${formatRupiah(diskon)}</td>
                    <td class="font-semibold">${formatRupiah(total)}</td>
                </tr>
            `;
      bon_item_counter++;
    });
    if (current_no_bon !== null) {
      htmlRows += buildBonSubtotalRow(
        subtotal_qty,
        subtotal_diskon,
        subtotal_total
      );
    }
    if (current_tanggal !== null && date_subtotals) {
      const isLastPage =
        pagination && pagination.current_page === pagination.total_pages;
      const isExport = pagination === null;
      if (isLastPage || isExport) {
        htmlRows += buildSubtotalTanggalRow(current_tanggal);
      }
    }
    const isLastPage =
      pagination && pagination.current_page === pagination.total_pages;
    const isExport = pagination === null;
    if (tabel_data.length > 0 && summary && (isLastPage || isExport)) {
      htmlRows += `
                <tr class="grand-total-row" style="background-color: #EBF8FF; font-weight: bold; color: #2C5282; font-size: 14px;">
                    <td colspan="3" class="text-right px-4 py-2 font-bold">GRAND TOTAL</td>
                    <td class="px-2 py-2">${formatNumber(
                      summary.total_qty
                    )}</td>
                    <td class="px-2 py-2"></td>
                    <td class="px-2 py-2">${formatRupiah(
                      summary.total_total_diskon
                    )}</td>
                    <td class="px-2 py-2">${formatRupiah(
                      summary.total_total
                    )}</td>
                </tr>
            `;
    }
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
                                  current_page > 1
                                    ? build_pagination_url(current_page - 1)
                                    : "#"
                                }" 
                                   class="pagination-link ${
                                     current_page === 1
                                       ? "pagination-disabled"
                                       : ""
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
                                         page_num === current_page
                                           ? "pagination-active"
                                           : ""
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
                                     current_page === total_pages
                                       ? "pagination-disabled"
                                       : ""
                                   }">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            `;
    paginationLinks.innerHTML = linksHtml;
  }
  async function fetchAllDataForExport() {
    setLoadingState(true, true);
    const params = getUrlParams();

    const queryString = new URLSearchParams({
      tgl_mulai: params.tgl_mulai,
      tgl_selesai: params.tgl_selesai,
      kd_store: params.kd_store,
      search: params.search, // <--- TAMBAHKAN INI
      export: true,
    }).toString();
    try {
      const response = await fetch(
        `/src/api/top_sales/get_sales_per_kasir_bon.php?${queryString}`
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
      return data;
    } catch (error) {
      console.error("Error fetching data for export:", error);
      Swal.fire(
        "Export Gagal",
        "Gagal mengambil data: " + error.message,
        "error"
      );
      return null;
    } finally {
      setLoadingState(false);
    }
  }
  async function exportToExcel() {
    const data = await fetchAllDataForExport();
    if (!data || !data.tabel_data || data.tabel_data.length === 0) {
      Swal.fire("Tidak Ada Data", "Tidak ada data untuk diekspor.", "info");
      return;
    }

    try {
      const { tabel_data, summary, date_subtotals } = data;
      const params = getUrlParams();
      const title = [["Laporan Sales per Kasir & Bon"]];

      // Susunan Info:
      // A2: Periode
      // A3: Cabang
      // A4: [] (Kosong) -> Baris 4 KOSONG
      // A5: Total Qty
      // ... dst
      const info = [
        ["Periode", `${params.tgl_mulai} s/d ${params.tgl_selesai}`],
        [
          "Cabang",
          filterSelectStore.options[filterSelectStore.selectedIndex].text,
        ],
        [],
        ["Total Qty", parseFloat(summary.total_qty) || 0],
        ["Total Diskon", parseFloat(summary.total_total_diskon) || 0],
        ["Total (Net Sales)", parseFloat(summary.total_total) || 0],
        ["Total HPP", parseFloat(summary.total_hpp) || 0],
        ["Total Grs Margin", parseFloat(summary.total_grs_margin) || 0],
        [],
      ];

      const headers = [
        "No",
        "PLU",
        "Nama Barang",
        "Qty",
        "Harga",
        "Disc",
        "Total",
      ];

      const dataRows = [];
      const merges = [];
      let bon_item_counter = 0;
      let current_no_bon = null;
      let current_tanggal = null;
      let s_bon_qty = 0,
        s_bon_diskon = 0,
        s_bon_total = 0;

      const rowOffset = info.length + 2;

      const pushSubtotalBonRow = () => {
        dataRows.push([
          "",
          "",
          "Sub Total Bon:",
          s_bon_qty,
          "",
          s_bon_diskon,
          s_bon_total,
        ]);
        merges.push({
          s: { r: dataRows.length + rowOffset - 1, c: 0 },
          e: { r: dataRows.length + rowOffset - 1, c: 2 },
        });
        s_bon_qty = 0;
        s_bon_diskon = 0;
        s_bon_total = 0;
      };

      const pushSubtotalTanggalRow = () => {
        const subtotal = date_subtotals[current_tanggal] || {
          total_qty: 0,
          total_total_diskon: 0,
          total_total: 0,
        };
        dataRows.push([
          "",
          "",
          "Sub Total Tanggal:",
          subtotal.total_qty,
          "",
          subtotal.total_total_diskon,
          subtotal.total_total,
        ]);
        merges.push({
          s: { r: dataRows.length + rowOffset - 1, c: 0 },
          e: { r: dataRows.length + rowOffset - 1, c: 2 },
        });
      };

      const pushTanggalHeaderRow = (tanggal) => {
        dataRows.push([`Tanggal: ${tanggal}`]);
        merges.push({
          s: { r: dataRows.length + rowOffset - 1, c: 0 },
          e: { r: dataRows.length + rowOffset - 1, c: 6 },
        });
      };

      const pushBonHeaderRow = (row) => {
        dataRows.push([
          `No Trans: ${row.no_bon}`,
          "",
          ``,
          "",
          `Kasir: ${row.kode_kasir} - ${row.nama_kasir}`,
        ]);
        merges.push({
          s: { r: dataRows.length + rowOffset - 1, c: 0 },
          e: { r: dataRows.length + rowOffset - 1, c: 1 },
        });
        merges.push({
          s: { r: dataRows.length + rowOffset - 1, c: 2 },
          e: { r: dataRows.length + rowOffset - 1, c: 3 },
        });
        merges.push({
          s: { r: dataRows.length + rowOffset - 1, c: 4 },
          e: { r: dataRows.length + rowOffset - 1, c: 6 },
        });
      };

      tabel_data.forEach((row, index) => {
        const qty = parseFloat(row.qty) || 0;
        const diskon = parseFloat(row.total_diskon) || 0;
        const total = parseFloat(row.total) || 0;

        if (row.tanggal !== current_tanggal) {
          if (current_no_bon !== null) pushSubtotalBonRow();
          if (current_tanggal !== null) pushSubtotalTanggalRow();

          pushTanggalHeaderRow(row.tanggal);
          current_tanggal = row.tanggal;
          current_no_bon = null;
        }

        if (row.no_bon !== current_no_bon) {
          if (current_no_bon !== null) pushSubtotalBonRow();
          pushBonHeaderRow(row);
          current_no_bon = row.no_bon;
          bon_item_counter = 1;
          s_bon_qty = 0;
          s_bon_diskon = 0;
          s_bon_total = 0;
        }

        s_bon_qty += qty;
        s_bon_diskon += diskon;
        s_bon_total += total;

        dataRows.push([
          bon_item_counter++,
          row.plu,
          row.nama_barang,
          qty,
          parseFloat(row.harga),
          diskon,
          total,
        ]);
      });

      if (current_no_bon !== null) pushSubtotalBonRow();
      if (current_tanggal !== null) pushSubtotalTanggalRow();

      dataRows.push([]);
      dataRows.push([
        "",
        "",
        "GRAND TOTAL",
        parseFloat(summary.total_qty) || 0,
        "",
        parseFloat(summary.total_total_diskon) || 0,
        parseFloat(summary.total_total) || 0,
      ]);
      merges.push({
        s: { r: dataRows.length + rowOffset - 1, c: 0 },
        e: { r: dataRows.length + rowOffset - 1, c: 2 },
      });

      const ws = XLSX.utils.aoa_to_sheet(title);
      XLSX.utils.sheet_add_aoa(ws, info, { origin: "A2" });
      const headerOrigin = "A" + (info.length + 2);
      XLSX.utils.sheet_add_aoa(ws, [headers], { origin: headerOrigin });
      XLSX.utils.sheet_add_aoa(ws, dataRows, {
        origin: "A" + (info.length + 3),
      });

      ws["!merges"] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 6 } }, ...merges];

      // Helper untuk menerapkan style dengan aman (Mencegah error undefined)
      const safeApplyStyle = (cellRef, styleObj) => {
        if (!ws[cellRef]) return; // Skip jika cell tidak ada
        ws[cellRef].s = styleObj;
      };

      const safeApplyFormat = (cellRef, format) => {
        if (!ws[cellRef]) return;
        ws[cellRef].t = "n";
        ws[cellRef].z = format; // .z lebih standar di sheetjs pro/style, .s.numFmt di community
        if (!ws[cellRef].s) ws[cellRef].s = {};
        ws[cellRef].s.numFmt = format;
      };

      // Styling Title
      safeApplyStyle("A1", {
        font: { bold: true, sz: 16 },
        alignment: { horizontal: "center" },
      });

      const numFormat = "#,##0";
      const numFormatDec = "#,##0.00";

      const headerStyle = {
        font: { bold: true },
        fill: { fgColor: { rgb: "E0E0E0" } },
      };
      const headerTanggalStyle = {
        font: { bold: true, sz: 11, color: { rgb: "2C5282" } },
        fill: { fgColor: { rgb: "EBF8FF" } },
      };
      const headerBonStyle = {
        font: { bold: true },
        fill: { fgColor: { rgb: "F7FAFC" } },
      };
      const subtotalBonStyle = {
        font: { bold: true, italic: true },
        fill: { fgColor: { rgb: "FEFFE4" } },
        alignment: { horizontal: "right" },
      };
      const subtotalTanggalStyle = {
        font: { bold: true, italic: true, sz: 11, color: { rgb: "2C5282" } },
        fill: { fgColor: { rgb: "EBF8FF" } },
        alignment: { horizontal: "right" },
      };
      const grandTotalStyle = {
        font: { bold: true, sz: 12 },
        fill: { fgColor: { rgb: "E2E8F0" } },
        alignment: { horizontal: "right" },
      };

      // --- PERBAIKAN TARGET FORMAT SUMMARY ---
      // A4 adalah baris kosong, Data summary mulai di baris 5 (B5) sampai baris 9 (B9)
      ["B5", "B6", "B7", "B8", "B9"].forEach((cell) => {
        safeApplyFormat(cell, numFormat);
      });
      // Jika Net Sales (Row 7) perlu desimal:
      safeApplyFormat("B7", numFormatDec);

      // Header Column Styles
      headers.forEach((_, C) => {
        const cellRef = XLSX.utils.encode_cell({ r: info.length + 1, c: C });
        safeApplyStyle(cellRef, headerStyle);
      });

      const dataRowStartIndex = info.length + 2;

      // Styling Data Rows
      dataRows.forEach((row, R_idx) => {
        const R = R_idx + dataRowStartIndex;
        if (!row || row.length === 0) return;

        const label = row[0] || row[2]; // Label biasanya di col 0 atau col 2 (untuk subtotal)

        if (typeof label === "string") {
          const cellA = XLSX.utils.encode_cell({ r: R, c: 0 });
          const cellE = XLSX.utils.encode_cell({ r: R, c: 4 });

          // Kolom Harga/Total untuk Subtotal
          const cellC_next = XLSX.utils.encode_cell({ r: R + 1, c: 2 }); // C
          const cellD_next = XLSX.utils.encode_cell({ r: R + 1, c: 3 }); // D (Qty)
          const cellF_next = XLSX.utils.encode_cell({ r: R + 1, c: 5 }); // F (Diskon)
          const cellG_next = XLSX.utils.encode_cell({ r: R + 1, c: 6 }); // G (Total)

          if (label.startsWith("Tanggal:")) {
            safeApplyStyle(cellA, headerTanggalStyle);
          } else if (label.startsWith("No Trans:")) {
            safeApplyStyle(cellA, headerBonStyle);
            safeApplyStyle(cellE, headerBonStyle); // Kolom E (Kasir)
          } else if (label.startsWith("Sub Total Bon:")) {
            // Kita harus hati-hati, karena cell mungkin undefined jika data kosong
            // Disini row sekarang (R) adalah row label, nilai ada di kolom C, D, F, G
            // Tapi kode lama menggunakan R+1 yang aneh karena loop ini sudah di row data.
            // Jika row[0] kosong, label ada di row[2], ini row yang sama.

            // Koreksi: Gunakan `encode_cell` pada row `R` (bukan R+1) karena kita sedang iterasi row tersebut
            safeApplyStyle(XLSX.utils.encode_cell({ r: R, c: 2 }), {
              ...subtotalBonStyle,
            }); // Label

            const styleNum = { ...subtotalBonStyle, numFmt: numFormat };
            const styleDec = { ...subtotalBonStyle, numFmt: numFormatDec };

            safeApplyStyle(XLSX.utils.encode_cell({ r: R, c: 3 }), styleDec); // Qty
            safeApplyStyle(XLSX.utils.encode_cell({ r: R, c: 5 }), styleNum); // Diskon
            safeApplyStyle(XLSX.utils.encode_cell({ r: R, c: 6 }), styleNum); // Total
          } else if (label.startsWith("Sub Total Tanggal:")) {
            safeApplyStyle(XLSX.utils.encode_cell({ r: R, c: 2 }), {
              ...subtotalTanggalStyle,
            });

            const styleNum = { ...subtotalTanggalStyle, numFmt: numFormat };
            const styleDec = { ...subtotalTanggalStyle, numFmt: numFormatDec };

            safeApplyStyle(XLSX.utils.encode_cell({ r: R, c: 3 }), styleDec);
            safeApplyStyle(XLSX.utils.encode_cell({ r: R, c: 5 }), styleNum);
            safeApplyStyle(XLSX.utils.encode_cell({ r: R, c: 6 }), styleNum);
          } else if (label.startsWith("GRAND TOTAL")) {
            safeApplyStyle(XLSX.utils.encode_cell({ r: R, c: 2 }), {
              ...grandTotalStyle,
            });

            const styleNum = { ...grandTotalStyle, numFmt: numFormat };
            const styleDec = { ...grandTotalStyle, numFmt: numFormatDec };

            safeApplyStyle(XLSX.utils.encode_cell({ r: R, c: 3 }), styleDec);
            safeApplyStyle(XLSX.utils.encode_cell({ r: R, c: 5 }), styleNum);
            safeApplyStyle(XLSX.utils.encode_cell({ r: R, c: 6 }), styleNum);
          } else if (row[0] && typeof row[0] === "number") {
            // Baris Data Item
            safeApplyFormat(
              XLSX.utils.encode_cell({ r: R, c: 3 }),
              numFormatDec
            ); // Qty
            safeApplyFormat(XLSX.utils.encode_cell({ r: R, c: 4 }), numFormat); // Harga
            safeApplyFormat(XLSX.utils.encode_cell({ r: R, c: 5 }), numFormat); // Disc
            safeApplyFormat(XLSX.utils.encode_cell({ r: R, c: 6 }), numFormat); // Total
          }
        }
      });

      ws["!cols"] = [
        { wch: 5 },
        { wch: 12 },
        { wch: 35 },
        { wch: 10 },
        { wch: 15 },
        { wch: 15 },
        { wch: 17 },
      ];

      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, "Sales Kasir Bon");
      const fileName = `Sales_Kasir_Bon_${params.tgl_mulai}_sd_${params.tgl_selesai}.xlsx`;
      XLSX.writeFile(wb, fileName);
    } catch (error) {
      console.error("Error exporting to Excel:", error);
      Swal.fire("Export Gagal", "Terjadi kesalahan: " + error.message, "error");
    }
  }
  async function exportToPDF() {
    const data = await fetchAllDataForExport();
    if (!data || !data.tabel_data || data.tabel_data.length === 0) {
      Swal.fire("Tidak Ada Data", "Tidak ada data untuk diekspor.", "info");
      return;
    }
    try {
      const { tabel_data, summary, date_subtotals } = data;
      const params = getUrlParams();
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF("landscape");
      doc.setFontSize(18);
      doc.text("Laporan Sales per Kasir & Bon", 14, 22);
      doc.setFontSize(11);
      doc.setTextColor(100);
      doc.text(
        `Periode: ${params.tgl_mulai} s/d ${params.tgl_selesai}`,
        14,
        30
      );
      const storeText =
        filterSelectStore.options[filterSelectStore.selectedIndex].text;
      doc.text(`Cabang: ${storeText}`, 14, 36);
      doc.text(`Total Qty: ${formatNumber(summary.total_qty)}`, 280, 22, {
        align: "right",
      });
      doc.text(
        `Total Diskon: ${formatRupiah(summary.total_total_diskon)}`,
        280,
        28,
        { align: "right" }
      );
      doc.text(
        `Total (Net Sales): ${formatRupiah(summary.total_total)}`,
        280,
        34,
        { align: "right" }
      );
      doc.text(`Total HPP: ${formatRupiah(summary.total_hpp)}`, 280, 40, {
        align: "right",
      });
      doc.text(
        `Total Grs Margin: ${formatRupiah(summary.total_grs_margin)}`,
        280,
        46,
        { align: "right" }
      );
      const head = [
        ["No", "PLU", "Nama Barang", "Qty", "Harga", "Disc", "Total"],
      ];
      const body = [];
      let bon_item_counter = 0;
      let current_no_bon = null;
      let current_tanggal = null;
      let s_bon_qty = 0,
        s_bon_diskon = 0,
        s_bon_total = 0;
      const headerTanggalStyles = {
        fontStyle: "bold",
        fillColor: [235, 248, 255],
        textColor: [44, 82, 130],
        fontSize: 6,
        halign: "left",
      };
      const headerBonStyles = {
        fontStyle: "bold",
        fillColor: [247, 250, 252],
        textColor: [74, 85, 104],
        fontSize: 5,
        halign: "left",
      };
      const subtotalBonStyles = {
        halign: "right",
        fontStyle: "bolditalic",
        fillColor: [254, 253, 232],
        textColor: [113, 63, 18],
        fontSize: 5,
      };
      const subtotalTanggalStyles = {
        halign: "right",
        fontStyle: "bolditalic",
        fillColor: [240, 253, 244],
        textColor: [22, 101, 52],
        fontSize: 6,
      };
      const grandTotalStyles = {
        halign: "right",
        fontStyle: "bold",
        fillColor: [226, 232, 240],
        fontSize: 7,
      };
      const subtotalBonValuesStyles = {
        ...subtotalBonStyles,
        halign: "left",
      };
      const subtotalTanggalValuesStyles = {
        ...subtotalTanggalStyles,
        halign: "left",
      };
      const grandTotalValuesStyles = {
        ...grandTotalStyles,
        halign: "left",
      };
      const pushSubtotalBonRowPdf = () => {
        body.push([
          { content: "Sub Total Bon:", colSpan: 3, styles: subtotalBonStyles },
          { content: formatNumber(s_bon_qty), styles: subtotalBonValuesStyles },
          { content: "", styles: subtotalBonValuesStyles },
          {
            content: formatRupiah(s_bon_diskon),
            styles: subtotalBonValuesStyles,
          },
          {
            content: formatRupiah(s_bon_total),
            styles: subtotalBonValuesStyles,
          },
        ]);
        s_bon_qty = 0;
        s_bon_diskon = 0;
        s_bon_total = 0;
      };
      const pushSubtotalTanggalRowPdf = (tanggal) => {
        const subtotal = date_subtotals[tanggal] || {
          total_qty: 0,
          total_total_diskon: 0,
          total_total: 0,
        };
        body.push([
          {
            content: "Sub Total Tanggal:",
            colSpan: 3,
            styles: subtotalTanggalStyles,
          },
          {
            content: formatNumber(subtotal.total_qty),
            styles: subtotalTanggalValuesStyles,
          },
          { content: "", styles: subtotalTanggalValuesStyles },
          {
            content: formatRupiah(subtotal.total_total_diskon),
            styles: subtotalTanggalValuesStyles,
          },
          {
            content: formatRupiah(subtotal.total_total),
            styles: subtotalTanggalValuesStyles,
          },
        ]);
      };
      const pushTanggalHeaderRowPdf = (tanggal) => {
        body.push([
          {
            content: `Tanggal: ${tanggal}`,
            colSpan: 7,
            styles: headerTanggalStyles,
          },
        ]);
      };
      const pushBonHeaderRowPdf = (row) => {
        body.push([
          {
            content: `No Trans: ${row.no_bon}`,
            colSpan: 3,
            styles: headerBonStyles,
          },
          {
            content: `Kasir: ${row.kode_kasir} - ${row.nama_kasir}`,
            colSpan: 4,
            styles: headerBonStyles,
          },
        ]);
      };
      tabel_data.forEach((row, index) => {
        const qty = parseFloat(row.qty) || 0;
        const diskon = parseFloat(row.total_diskon) || 0;
        const total = parseFloat(row.total) || 0;
        if (row.tanggal !== current_tanggal) {
          if (current_no_bon !== null) pushSubtotalBonRowPdf();
          if (current_tanggal !== null)
            pushSubtotalTanggalRowPdf(current_tanggal);
          pushTanggalHeaderRowPdf(row.tanggal);
          current_tanggal = row.tanggal;
          current_no_bon = null;
        }
        if (row.no_bon !== current_no_bon) {
          if (current_no_bon !== null) pushSubtotalBonRowPdf();
          pushBonHeaderRowPdf(row);
          current_no_bon = row.no_bon;
          bon_item_counter = 1;
          s_bon_qty = 0;
          s_bon_diskon = 0;
          s_bon_total = 0;
        }
        s_bon_qty += qty;
        s_bon_diskon += diskon;
        s_bon_total += total;
        body.push([
          bon_item_counter++,
          row.plu,
          row.nama_barang,
          formatNumber(qty),
          formatRupiah(row.harga),
          formatRupiah(diskon),
          formatRupiah(total),
        ]);
      });
      if (current_no_bon !== null) pushSubtotalBonRowPdf();
      if (current_tanggal !== null) pushSubtotalTanggalRowPdf(current_tanggal);
      body.push([
        {
          content: "GRAND TOTAL",
          colSpan: 3,
          styles: grandTotalStyles,
        },
        {
          content: formatNumber(summary.total_qty),
          styles: grandTotalValuesStyles,
        },
        { content: "", styles: grandTotalStyles },
        {
          content: formatRupiah(summary.total_total_diskon),
          styles: grandTotalValuesStyles,
        },
        {
          content: formatRupiah(summary.total_total),
          styles: grandTotalValuesStyles,
        },
      ]);
      doc.autoTable({
        startY: 52,
        head: head,
        body: body,
        theme: "grid",
        headStyles: {
          fillColor: [220, 220, 220],
          textColor: [0, 0, 0],
          fontSize: 8,
        },
        styles: { fontSize: 7, cellPadding: 1.5 },
        columnStyles: {
          0: { halign: "right", cellWidth: 8 },
          1: { halign: "left", cellWidth: 20 },
          2: { halign: "left", cellWidth: 100 },
          3: { halign: "left", cellWidth: 20 },
          4: { halign: "left", cellWidth: 35 },
          5: { halign: "left", cellWidth: 35 },
          6: { halign: "left", cellWidth: 40 },
        },
      });
      const fileName = `Sales_Kasir_Bon_${params.tgl_mulai}_sd_${params.tgl_selesai}.pdf`;
      doc.save(fileName);
    } catch (error) {
      console.error("Error exporting to PDF:", error);
      Swal.fire("Export Gagal", "Terjadi kesalahan: " + error.message, "error");
    }
  }
  if (exportExcelButton) {
    exportExcelButton.addEventListener("click", exportToExcel);
  }
  if (exportPdfButton) {
    exportPdfButton.addEventListener("click", exportToPDF);
  }
  if (filterForm) {
    filterForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const formData = new FormData(filterForm);
      const params = new URLSearchParams(formData);
      params.set("page", "1");
      window.history.pushState({}, "", `?${params.toString()}`);
      loadTopSalesData();
    });
  }
  loadTopSalesData();
});
