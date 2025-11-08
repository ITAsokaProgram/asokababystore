document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("return-out-table-body");
  const filterForm = document.getElementById("filter-form");
  const filterSubmitButton = document.getElementById("filter-submit-button");
  const filterSelectStore = document.getElementById("kd_store");
  const summaryQty = document.getElementById("summary-qty");
  const summaryNetto = document.getElementById("summary-netto");
  const summaryPPN = document.getElementById("summary-ppn");
  const summaryTotal = document.getElementById("summary-total");
  const pageTitle = document.getElementById("page-title");
  const pageSubtitle = document.getElementById("page-subtitle");
  const paginationContainer = document.getElementById("pagination-container");
  const paginationInfo = document.getElementById("pagination-info");
  const paginationLinks = document.getElementById("pagination-links");
  const exportExcelButton = document.getElementById("export-excel-btn");
  const exportPdfButton = document.getElementById("export-pdf-btn");
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
    setLoadingState(true, false, isPagination);
    const queryString = new URLSearchParams({
      tgl_mulai: params.tgl_mulai,
      tgl_selesai: params.tgl_selesai,
      kd_store: params.kd_store,
      page: params.page,
    }).toString();
    try {
      const response = await fetch(
        `/src/api/return_out/get_all_item.php?${queryString}`
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
        pageSubtitle.textContent = `Laporan Return Out Periode ${params.tgl_mulai} s/d ${params.tgl_selesai} - ${storeName}`;
        if (pageTitle) {
          pageTitle.textContent = `Laporan Return Out (All Item) - ${storeName}`;
        }
      }
      if (data.summary) {
        updateSummaryCards(data.summary);
      }
      renderTable(
        data.tabel_data,
        data.pagination ? data.pagination.offset : 0,
        data.summary,
        data.date_subtotals,
        data.pagination
      );
      renderPagination(data.pagination);
    } catch (error) {
      console.error("Error loading data:", error);
      showTableError(error.message);
      if (pageSubtitle) {
        pageSubtitle.textContent = "Gagal memuat data filter";
      }
      if (pageTitle) {
        pageTitle.textContent = "Laporan Return Out (All Item)";
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
                                    <td colspan="10" class="text-center p-8">
                                        <div class="spinner-simple"></div>
                                        <p class="mt-2 text-gray-500">Memuat data...</p>
                                    </td>
                                </tr>`;
        if (!isPagination) {
          if (summaryQty) summaryQty.textContent = "-";
          if (summaryNetto) summaryNetto.textContent = "-";
          if (summaryPPN) summaryPPN.textContent = "-";
          if (summaryTotal) summaryTotal.textContent = "-";
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
                <td colspan="10" class="text-center p-8 text-red-600">
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
    summaryQty.textContent = formatNumber(summary.total_qty);
    summaryNetto.textContent = formatRupiah(summary.total_netto);
    summaryPPN.textContent = formatRupiah(summary.total_ppn);
    summaryTotal.textContent = formatRupiah(summary.total_grand);
  }
  function renderTable(
    tabel_data,
    offset,
    summary,
    date_subtotals,
    pagination
  ) {
    if (!tabel_data || tabel_data.length === 0) {
      tableBody.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center p-8 text-gray-500">
                        <i class="fas fa-inbox fa-lg mb-2"></i>
                        <p>Tidak ada data ditemukan untuk filter ini.</p>
                    </td>
                </tr>`;
      return;
    }
    let htmlRows = "";
    let faktur_item_counter = 0;
    let current_tanggal = null;
    let current_faktur = null;
    let subtotal_faktur_qty = 0,
      subtotal_faktur_netto = 0,
      subtotal_faktur_ppn = 0,
      subtotal_faktur_total = 0;
    function buildTanggalHeaderRow(tanggal) {
      return `
                <tr class="header-tanggal-row">
                    <td colspan="10" class="px-4 py-2">
                        Tanggal: <span class="font-bold"> ${tanggal} </span>
                    </td>
                </tr>
            `;
    }
    function buildFakturHeaderRow(faktur, kodesupp, namasupp, nama_inisial) {
      const inisial = nama_inisial ? `(${nama_inisial})` : "";
      return `
                <tr class="header-faktur-row">
                    <td colspan="3" class="px-4 py-1 pl-6">No Faktur: <strong>${faktur}</strong></td>
                    <td colspan="7" class="px-4 py-1">Supplier: <strong>${kodesupp} ${
        namasupp ? "-" : ""
      } ${namasupp || ""} ${inisial}</strong></td>
                </tr>
            `;
    }
    function buildSubtotalFakturRow() {
      return `
                <tr class="subtotal-row">
                    <td colspan="6" class="text-right px-4 py-2 font-bold" style="font-style: italic;">
                        Sub Total Faktur: 
                    </td>
                    <td class=" px-2 py-2">${formatNumber(
                      subtotal_faktur_qty
                    )}</td>
                    <td class=" px-2 py-2">${formatRupiah(
                      subtotal_faktur_netto
                    )}</td>
                    <td class=" px-2 py-2">${formatRupiah(
                      subtotal_faktur_ppn
                    )}</td>
                    <td class=" px-2 py-2">${formatRupiah(
                      subtotal_faktur_total
                    )}</td>
                </tr>
            `;
    }
    function buildSubtotalTanggalRow(tanggal) {
      const subtotal = date_subtotals[tanggal] || {
        total_qty: 0,
        total_netto: 0,
        total_ppn: 0,
        total_grand: 0,
      };
      return `
                <tr class="subtotal-tanggal-row">
                    <td colspan="6" class=" px-4 py-2 text-right font-bold" style="font-style: italic;">Sub Total Tanggal:</td>
                    <td class=" px-2 py-2">${formatNumber(
                      subtotal.total_qty
                    )}</td>
                    <td class=" px-2 py-2">${formatRupiah(
                      subtotal.total_netto
                    )}</td>
                    <td class=" px-2 py-2">${formatRupiah(
                      subtotal.total_ppn
                    )}</td>
                    <td class=" px-2 py-2">${formatRupiah(
                      subtotal.total_grand
                    )}</td>
                </tr>
            `;
    }
    tabel_data.forEach((row, index) => {
      const qty = parseFloat(row.qty) || 0;
      const netto = parseFloat(row.netto) || 0;
      const ppn = parseFloat(row.ppn) || 0;
      const total = parseFloat(row.total) || 0;
      if (row.tanggal !== current_tanggal) {
        if (current_tanggal !== null) {
          if (current_faktur !== null) {
            htmlRows += buildSubtotalFakturRow();
            subtotal_faktur_qty = 0;
            subtotal_faktur_netto = 0;
            subtotal_faktur_ppn = 0;
            subtotal_faktur_total = 0;
          }
          htmlRows += buildSubtotalTanggalRow(current_tanggal);
        }
        current_tanggal = row.tanggal;
        current_faktur = null;
        htmlRows += buildTanggalHeaderRow(current_tanggal);
      }
      if (row.faktur !== current_faktur) {
        if (current_faktur !== null) {
          htmlRows += buildSubtotalFakturRow();
          subtotal_faktur_qty = 0;
          subtotal_faktur_netto = 0;
          subtotal_faktur_ppn = 0;
          subtotal_faktur_total = 0;
        }
        current_faktur = row.faktur;
        faktur_item_counter = 1;
        htmlRows += buildFakturHeaderRow(
          row.faktur,
          row.kodesupp,
          row.namasupp,
          row.nama_inisial
        );
      }
      subtotal_faktur_qty += qty;
      subtotal_faktur_netto += netto;
      subtotal_faktur_ppn += ppn;
      subtotal_faktur_total += total;
      htmlRows += `
                <tr>
                    <td>${faktur_item_counter}</td>
                    <td>${row.plu}</td>
                    <td class="text-left">${row.descp}</td>
                    <td class="text-left">${row.satuan}</td>
                    <td class="">${formatNumber(row.conv1)}</td>
                    <td class="">${formatNumber(row.conv2)}</td>
                    <td class="">${formatNumber(qty)}</td>
                    <td class="">${formatRupiah(netto)}</td>
                    <td class="">${formatRupiah(ppn)}</td>
                    <td class="">${formatRupiah(total)}</td>
                </tr>
            `;
      faktur_item_counter++;
    });
    if (current_faktur !== null) {
      htmlRows += buildSubtotalFakturRow();
    }
    if (current_tanggal !== null) {
      const isLastPage =
        pagination && pagination.current_page === pagination.total_pages;
      const isExport = pagination === null;
      if (isLastPage || isExport) {
        htmlRows += buildSubtotalTanggalRow(current_tanggal);
      }
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

  async function fetchAllDataForExport() {
    setLoadingState(true, true);
    const params = getUrlParams();
    const queryString = new URLSearchParams({
      tgl_mulai: params.tgl_mulai,
      tgl_selesai: params.tgl_selesai,
      kd_store: params.kd_store,
      export: true,
    }).toString();
    try {
      const response = await fetch(
        `/src/api/return_out/get_all_item.php?${queryString}`
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
      const { tabel_data, summary } = data;
      const params = getUrlParams();
      const title = [["Laporan Return Out (All Item)"]];
      const info = [
        ["Periode", `${params.tgl_mulai} s/d ${params.tgl_selesai}`],
        [
          "Cabang",
          filterSelectStore.options[filterSelectStore.selectedIndex].text,
        ],
        [],
        ["Total Qty", parseFloat(summary.total_qty) || 0],
        ["Total Netto", parseFloat(summary.total_netto) || 0],
        ["Total PPN", parseFloat(summary.total_ppn) || 0],
        ["Grand Total", parseFloat(summary.total_grand) || 0],
        [],
      ];
      const headers = [
        "No",
        "PLU",
        "Deskripsi",
        "Sat",
        "Conv1",
        "Conv2",
        "Qty",
        "Netto",
        "PPN",
        "Total",
      ];
      const dataRows = [];
      const merges = [];
      let faktur_item_counter = 0;
      let current_tanggal = null,
        current_faktur = null;
      let s_fak_qty = 0,
        s_fak_netto = 0,
        s_fak_ppn = 0,
        s_fak_total = 0;
      let s_tgl_qty = 0,
        s_tgl_netto = 0,
        s_tgl_ppn = 0,
        s_tgl_total = 0;
      const pushSubtotalFakturRow = () => {
        dataRows.push([
          "",
          "",
          "",
          "",
          "",
          "Sub Total Faktur:",
          s_fak_qty,
          s_fak_netto,
          s_fak_ppn,
          s_fak_total,
        ]);
        merges.push({
          s: { r: dataRows.length + info.length + 1, c: 0 },
          e: { r: dataRows.length + info.length + 1, c: 5 },
        });
      };
      const pushSubtotalTanggalRow = () => {
        dataRows.push([
          "",
          "",
          "",
          "",
          "",
          "Sub Total Tanggal:",
          s_tgl_qty,
          s_tgl_netto,
          s_tgl_ppn,
          s_tgl_total,
        ]);
        merges.push({
          s: { r: dataRows.length + info.length + 1, c: 0 },
          e: { r: dataRows.length + info.length + 1, c: 5 },
        });
      };
      tabel_data.forEach((row, index) => {
        const qty = parseFloat(row.qty) || 0;
        const netto = parseFloat(row.netto) || 0;
        const ppn = parseFloat(row.ppn) || 0;
        const total = parseFloat(row.total) || 0;
        if (row.tanggal !== current_tanggal) {
          if (current_faktur !== null) {
            pushSubtotalFakturRow();
            s_fak_qty = 0;
            s_fak_netto = 0;
            s_fak_ppn = 0;
            s_fak_total = 0;
          }
          if (current_tanggal !== null) {
            pushSubtotalTanggalRow();
            s_tgl_qty = 0;
            s_tgl_netto = 0;
            s_tgl_ppn = 0;
            s_tgl_total = 0;
          }
          current_tanggal = row.tanggal;
          current_faktur = null;
          dataRows.push([`Tanggal: ${current_tanggal}`]);
          merges.push({
            s: { r: dataRows.length + info.length + 1, c: 0 },
            e: { r: dataRows.length + info.length + 1, c: 9 },
          });
        }
        if (row.faktur !== current_faktur) {
          if (current_faktur !== null) {
            pushSubtotalFakturRow();
            s_fak_qty = 0;
            s_fak_netto = 0;
            s_fak_ppn = 0;
            s_fak_total = 0;
          }
          current_faktur = row.faktur;
          faktur_item_counter = 1;
          const inisial = row.nama_inisial ? `(${row.nama_inisial})` : "";
          const namaSupp = `${row.kodesupp} - ${row.namasupp || ""} ${inisial}`;
          dataRows.push([`No Faktur: ${current_faktur}`, "", "", namaSupp]);
          merges.push({
            s: { r: dataRows.length + info.length + 1, c: 0 },
            e: { r: dataRows.length + info.length + 1, c: 2 },
          });
          merges.push({
            s: { r: dataRows.length + info.length + 1, c: 3 },
            e: { r: dataRows.length + info.length + 1, c: 9 },
          });
        }
        s_fak_qty += qty;
        s_fak_netto += netto;
        s_fak_ppn += ppn;
        s_fak_total += total;
        s_tgl_qty += qty;
        s_tgl_netto += netto;
        s_tgl_ppn += ppn;
        s_tgl_total += total;
        dataRows.push([
          faktur_item_counter++,
          row.plu,
          row.descp,
          row.satuan,
          parseFloat(row.conv1),
          parseFloat(row.conv2),
          qty,
          netto,
          ppn,
          total,
        ]);
      });
      if (current_faktur !== null) pushSubtotalFakturRow();
      if (current_tanggal !== null) pushSubtotalTanggalRow();
      dataRows.push([]);
      dataRows.push([
        "",
        "",
        "",
        "",
        "",
        "GRAND TOTAL:",
        parseFloat(summary.total_qty) || 0,
        parseFloat(summary.total_netto) || 0,
        parseFloat(summary.total_ppn) || 0,
        parseFloat(summary.total_grand) || 0,
      ]);
      merges.push({
        s: { r: dataRows.length + info.length + 1, c: 0 },
        e: { r: dataRows.length + info.length + 1, c: 5 },
      });
      const ws = XLSX.utils.aoa_to_sheet(title);
      XLSX.utils.sheet_add_aoa(ws, info, { origin: "A2" });
      const headerOrigin = "A" + (info.length + 2);
      XLSX.utils.sheet_add_aoa(ws, [headers], { origin: headerOrigin });
      XLSX.utils.sheet_add_aoa(ws, dataRows, {
        origin: "A" + (info.length + 3),
      });
      ws["!merges"] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 9 } }, ...merges];
      ws["A1"].s = {
        font: { bold: true, sz: 16 },
        alignment: { horizontal: "center" },
      };
      const numFormat = "#,##0";
      const numFormatDec = "#,##0.00";
      const headerStyle = {
        font: { bold: true },
        fill: { fgColor: { rgb: "E0E0E0" } },
      };
      ["B4", "B5", "B6", "B7"].forEach((cell) => {
        if (ws[cell]) {
          ws[cell].t = "n";
          ws[cell].s = { numFmt: numFormat };
        }
      });
      headers.forEach((_, C) => {
        const cell = ws[XLSX.utils.encode_cell({ r: info.length + 1, c: C })];
        if (cell) cell.s = headerStyle;
      });
      const dataRowStartIndex = info.length + 2;
      dataRows.forEach((row, R_idx) => {
        const R = R_idx + dataRowStartIndex;
        if (row.length === 0) return;
        const label = row[0] || row[5];
        if (typeof label === "string") {
          if (label.startsWith("Tanggal:")) {
            ws[XLSX.utils.encode_cell({ r: R, c: 0 })].s = {
              font: { bold: true, sz: 11, color: { rgb: "2C5282" } },
              fill: { fgColor: { rgb: "EBF8FF" } },
            };
          } else if (label.startsWith("No Faktur:")) {
            ws[XLSX.utils.encode_cell({ r: R, c: 0 })].s = {
              font: { bold: true },
              fill: { fgColor: { rgb: "F7FAFC" } },
            };
            ws[XLSX.utils.encode_cell({ r: R, c: 3 })].s = {
              font: { bold: true },
              fill: { fgColor: { rgb: "F7FAFC" } },
            };
          } else if (label.startsWith("Sub Total Faktur:")) {
            const style = {
              font: { bold: true, italic: true },
              fill: { fgColor: { rgb: "F7FAFC" } },
            };
            ws[XLSX.utils.encode_cell({ r: R, c: 5 })].s = style;
            ["G", "H", "I", "J"].forEach((col) => {
              const cell = ws[col + (R + 1)];
              if (cell) cell.s = { numFmt: numFormat, ...style };
            });
            ws["G" + (R + 1)].s = { numFmt: numFormatDec, ...style };
          } else if (label.startsWith("Sub Total Tanggal:")) {
            const style = {
              font: {
                bold: true,
                italic: true,
                sz: 11,
                color: { rgb: "2C5282" },
              },
              fill: { fgColor: { rgb: "EBF8FF" } },
            };
            ws[XLSX.utils.encode_cell({ r: R, c: 5 })].s = style;
            ["G", "H", "I", "J"].forEach((col) => {
              const cell = ws[col + (R + 1)];
              if (cell) cell.s = { numFmt: numFormat, ...style };
            });
            ws["G" + (R + 1)].s = { numFmt: numFormatDec, ...style };
          } else if (label.startsWith("GRAND TOTAL:")) {
            const style = {
              font: { bold: true, sz: 12 },
              fill: { fgColor: { rgb: "E2E8F0" } },
            };
            ws[XLSX.utils.encode_cell({ r: R, c: 5 })].s = style;
            ["G", "H", "I", "J"].forEach((col) => {
              const cell = ws[col + (R + 1)];
              if (cell) cell.s = { numFmt: numFormat, ...style };
            });
            ws["G" + (R + 1)].s = { numFmt: numFormatDec, ...style };
          } else if (row[0] && typeof row[0] === "number") {
            ["E", "F", "G"].forEach((col) => {
              const cell = ws[col + (R + 1)];
              if (cell) cell.s = { numFmt: numFormatDec };
            });
            ["H", "I", "J"].forEach((col) => {
              const cell = ws[col + (R + 1)];
              if (cell) cell.s = { numFmt: numFormat };
            });
          }
        }
      });
      ws["!cols"] = [
        { wch: 5 },
        { wch: 12 },
        { wch: 35 },
        { wch: 5 },
        { wch: 8 },
        { wch: 8 },
        { wch: 10 },
        { wch: 15 },
        { wch: 15 },
        { wch: 17 },
      ];
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, "Return Out");
      const fileName = `Return_Out_All_Item_${params.tgl_mulai}_sd_${params.tgl_selesai}.xlsx`;
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
      const { tabel_data, summary } = data;
      const params = getUrlParams();
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF("landscape");
      doc.setFontSize(18);
      doc.text("Laporan Return Out (All Item)", 14, 22);
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
      doc.text(`Total Netto: ${formatRupiah(summary.total_netto)}`, 280, 30, {
        align: "right",
      });
      doc.text(`Grand Total: ${formatRupiah(summary.total_grand)}`, 280, 36, {
        align: "right",
      });
      const head = [
        [
          "No",
          "PLU",
          "Deskripsi",
          "Sat",
          "C1",
          "C2",
          "Qty",
          "Netto",
          "PPN",
          "Total",
        ],
      ];
      const body = [];
      let faktur_item_counter = 0;
      let current_tanggal = null,
        current_faktur = null;
      let s_fak_qty = 0,
        s_fak_netto = 0,
        s_fak_ppn = 0,
        s_fak_total = 0;
      let s_tgl_qty = 0,
        s_tgl_netto = 0,
        s_tgl_ppn = 0,
        s_tgl_total = 0;
      const headerTanggalStyles = {
        fontStyle: "bold",
        fillColor: [235, 248, 255],
        textColor: [44, 82, 130],
        fontSize: 6,
        halign: "left",
      };
      const headerFakturStyles = {
        fontStyle: "bold",
        fillColor: [247, 250, 252],
        textColor: [74, 85, 104],
        fontSize: 5,
        halign: "left",
      };
      const subtotalFakturStyles = {
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
        fontSize: 6,
      };
      const subtotalFakturValuesStyles = {
        halign: "right",
        fontStyle: "bolditalic",
        fillColor: [254, 253, 232],
        textColor: [113, 63, 18],
        fontSize: 5,
      };
      const subtotalTanggalValuesStyles = {
        halign: "right",
        fontStyle: "bolditalic",
        fillColor: [240, 253, 244],
        textColor: [22, 101, 52],
        fontSize: 6,
      };
      const grandTotalValuesStyles = {
        halign: "right",
        fontStyle: "bold",
        fillColor: [226, 232, 240],
        fontSize: 6,
      };
      const pushSubtotalFakturRowPdf = () => {
        body.push([
          {
            content: "Sub Total Faktur:",
            colSpan: 6,
            styles: subtotalFakturStyles,
          },
          {
            content: formatNumber(s_fak_qty),
            styles: subtotalFakturValuesStyles,
          },
          {
            content: formatRupiah(s_fak_netto),
            styles: subtotalFakturValuesStyles,
          },
          {
            content: formatRupiah(s_fak_ppn),
            styles: subtotalFakturValuesStyles,
          },
          {
            content: formatRupiah(s_fak_total),
            styles: subtotalFakturValuesStyles,
          },
        ]);
      };
      const pushSubtotalTanggalRowPdf = () => {
        body.push([
          {
            content: "Sub Total Tanggal:",
            colSpan: 6,
            styles: subtotalTanggalStyles,
          },
          {
            content: formatNumber(s_tgl_qty),
            styles: subtotalTanggalValuesStyles,
          },
          {
            content: formatRupiah(s_tgl_netto),
            styles: subtotalTanggalValuesStyles,
          },
          {
            content: formatRupiah(s_tgl_ppn),
            styles: subtotalTanggalValuesStyles,
          },
          {
            content: formatRupiah(s_tgl_total),
            styles: subtotalTanggalValuesStyles,
          },
        ]);
      };
      tabel_data.forEach((row, index) => {
        const qty = parseFloat(row.qty) || 0;
        const netto = parseFloat(row.netto) || 0;
        const ppn = parseFloat(row.ppn) || 0;
        const total = parseFloat(row.total) || 0;
        if (row.tanggal !== current_tanggal) {
          if (current_faktur !== null) {
            pushSubtotalFakturRowPdf();
            s_fak_qty = 0;
            s_fak_netto = 0;
            s_fak_ppn = 0;
            s_fak_total = 0;
          }
          if (current_tanggal !== null) {
            pushSubtotalTanggalRowPdf();
            s_tgl_qty = 0;
            s_tgl_netto = 0;
            s_tgl_ppn = 0;
            s_tgl_total = 0;
          }
          current_tanggal = row.tanggal;
          current_faktur = null;
          body.push([
            {
              content: `Tanggal: ${current_tanggal}`,
              colSpan: 10,
              styles: headerTanggalStyles,
            },
          ]);
        }
        if (row.faktur !== current_faktur) {
          if (current_faktur !== null) {
            pushSubtotalFakturRowPdf();
            s_fak_qty = 0;
            s_fak_netto = 0;
            s_fak_ppn = 0;
            s_fak_total = 0;
          }
          current_faktur = row.faktur;
          faktur_item_counter = 1;
          const inisial = row.nama_inisial ? `(${row.nama_inisial})` : "";
          const namaSupp = `${row.kodesupp} - ${row.namasupp || ""} ${inisial}`;
          body.push([
            {
              content: `No Faktur: ${current_faktur}`,
              colSpan: 3,
              styles: headerFakturStyles,
            },
            {
              content: `Supplier: ${namaSupp}`,
              colSpan: 7,
              styles: headerFakturStyles,
            },
          ]);
        }
        s_fak_qty += qty;
        s_fak_netto += netto;
        s_fak_ppn += ppn;
        s_fak_total += total;
        s_tgl_qty += qty;
        s_tgl_netto += netto;
        s_tgl_ppn += ppn;
        s_tgl_total += total;
        body.push([
          faktur_item_counter++,
          row.plu,
          row.descp,
          row.satuan,
          formatNumber(row.conv1),
          formatNumber(row.conv2),
          formatNumber(qty),
          formatRupiah(netto),
          formatRupiah(ppn),
          formatRupiah(total),
        ]);
      });
      if (current_faktur !== null) pushSubtotalFakturRowPdf();
      if (current_tanggal !== null) pushSubtotalTanggalRowPdf();
      body.push([
        { content: "GRAND TOTAL", colSpan: 6, styles: grandTotalStyles },
        {
          content: formatNumber(summary.total_qty),
          styles: grandTotalValuesStyles,
        },
        {
          content: formatRupiah(summary.total_netto),
          styles: grandTotalValuesStyles,
        },
        {
          content: formatRupiah(summary.total_ppn),
          styles: grandTotalValuesStyles,
        },
        {
          content: formatRupiah(summary.total_grand),
          styles: grandTotalValuesStyles,
        },
      ]);
      doc.autoTable({
        margin: { top: 44, left: 14 },
        tableWidth: 266,
        head: head,
        body: body,
        theme: "grid",
        headStyles: {
          fillColor: [220, 220, 220],
          textColor: [0, 0, 0],
          fontSize: 6,
        },
        styles: { fontSize: 5, cellPadding: 1.5 },
        columnStyles: {
          0: { halign: "right", cellWidth: 8 },
          1: { halign: "left", cellWidth: 20 },
          2: { halign: "left", cellWidth: 98 },
          3: { halign: "center", cellWidth: 10 },
          4: { halign: "right", cellWidth: 15 },
          5: { halign: "right", cellWidth: 15 },
          6: { halign: "right", cellWidth: 20 },
          7: { halign: "right", cellWidth: 30 },
          8: { halign: "right", cellWidth: 25 },
          9: { halign: "right", cellWidth: 25 },
        },
      });
      const fileName = `Return_Out_All_Item_${params.tgl_mulai}_sd_${params.tgl_selesai}.pdf`;
      doc.save(fileName);
    } catch (error) {
      console.error("Error exporting to PDF:", error);
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
      const { tabel_data, summary } = data;
      const params = getUrlParams();
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF("landscape");
      doc.setFontSize(18);
      doc.text("Laporan Return Out (All Item)", 14, 22);
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
      doc.text(`Total Netto: ${formatRupiah(summary.total_netto)}`, 280, 30, {
        align: "right",
      });
      doc.text(`Grand Total: ${formatRupiah(summary.total_grand)}`, 280, 36, {
        align: "right",
      });
      const head = [
        [
          "No",
          "PLU",
          "Deskripsi",
          "Sat",
          "C1",
          "C2",
          "Qty",
          "Netto",
          "PPN",
          "Total",
        ],
      ];
      const body = [];
      let item_counter = 1;
      let current_tanggal = null,
        current_faktur = null;
      let s_fak_qty = 0,
        s_fak_netto = 0,
        s_fak_ppn = 0,
        s_fak_total = 0;
      let s_tgl_qty = 0,
        s_tgl_netto = 0,
        s_tgl_ppn = 0,
        s_tgl_total = 0;
      const headerTanggalStyles = {
        fontStyle: "bold",
        fillColor: [235, 248, 255],
        textColor: [44, 82, 130],
        fontSize: 6,
        halign: "left",
      };
      const headerFakturStyles = {
        fontStyle: "bold",
        fillColor: [247, 250, 252],
        textColor: [74, 85, 104],
        fontSize: 5,
        halign: "left",
      };
      const subtotalFakturStyles = {
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
        fontSize: 6,
      };
      const subtotalFakturValuesStyles = {
        halign: "left",
        fontStyle: "bolditalic",
        fillColor: [254, 253, 232],
        textColor: [113, 63, 18],
        fontSize: 5,
      };
      const subtotalTanggalValuesStyles = {
        halign: "left",
        fontStyle: "bolditalic",
        fillColor: [240, 253, 244],
        textColor: [22, 101, 52],
        fontSize: 6,
      };
      const grandTotalValuesStyles = {
        halign: "left",
        fontStyle: "bold",
        fillColor: [226, 232, 240],
        fontSize: 6,
      };
      const pushSubtotalFakturRowPdf = () => {
        body.push([
          {
            content: "Sub Total Faktur:",
            colSpan: 6,
            styles: subtotalFakturStyles,
          },
          {
            content: formatNumber(s_fak_qty),
            styles: subtotalFakturValuesStyles,
          },
          {
            content: formatRupiah(s_fak_netto),
            styles: subtotalFakturValuesStyles,
          },
          {
            content: formatRupiah(s_fak_ppn),
            styles: subtotalFakturValuesStyles,
          },
          {
            content: formatRupiah(s_fak_total),
            styles: subtotalFakturValuesStyles,
          },
        ]);
      };
      const pushSubtotalTanggalRowPdf = () => {
        body.push([
          {
            content: "Sub Total Tanggal:",
            colSpan: 6,
            styles: subtotalTanggalStyles,
          },
          {
            content: formatNumber(s_tgl_qty),
            styles: subtotalTanggalValuesStyles,
          },
          {
            content: formatRupiah(s_tgl_netto),
            styles: subtotalTanggalValuesStyles,
          },
          {
            content: formatRupiah(s_tgl_ppn),
            styles: subtotalTanggalValuesStyles,
          },
          {
            content: formatRupiah(s_tgl_total),
            styles: subtotalTanggalValuesStyles,
          },
        ]);
      };
      tabel_data.forEach((row, index) => {
        const qty = parseFloat(row.qty) || 0;
        const netto = parseFloat(row.netto) || 0;
        const ppn = parseFloat(row.ppn) || 0;
        const total = parseFloat(row.total) || 0;
        if (row.tanggal !== current_tanggal) {
          if (current_faktur !== null) {
            pushSubtotalFakturRowPdf();
            s_fak_qty = 0;
            s_fak_netto = 0;
            s_fak_ppn = 0;
            s_fak_total = 0;
          }
          if (current_tanggal !== null) {
            pushSubtotalTanggalRowPdf();
            s_tgl_qty = 0;
            s_tgl_netto = 0;
            s_tgl_ppn = 0;
            s_tgl_total = 0;
          }
          current_tanggal = row.tanggal;
          current_faktur = null;
          body.push([
            {
              content: `Tanggal: ${current_tanggal}`,
              colSpan: 10,
              styles: headerTanggalStyles,
            },
          ]);
        }
        if (row.faktur !== current_faktur) {
          if (current_faktur !== null) {
            pushSubtotalFakturRowPdf();
            s_fak_qty = 0;
            s_fak_netto = 0;
            s_fak_ppn = 0;
            s_fak_total = 0;
          }
          current_faktur = row.faktur;
          const inisial = row.nama_inisial ? `(${row.nama_inisial})` : "";
          const namaSupp = `${row.kodesupp} - ${row.namasupp || ""} ${inisial}`;
          body.push([
            {
              content: `No Faktur: ${current_faktur}`,
              colSpan: 3,
              styles: headerFakturStyles,
            },
            {
              content: `Supplier: ${namaSupp}`,
              colSpan: 7,
              styles: headerFakturStyles,
            },
          ]);
        }
        s_fak_qty += qty;
        s_fak_netto += netto;
        s_fak_ppn += ppn;
        s_fak_total += total;
        s_tgl_qty += qty;
        s_tgl_netto += netto;
        s_tgl_ppn += ppn;
        s_tgl_total += total;
        body.push([
          item_counter++,
          row.plu,
          row.descp,
          row.satuan,
          formatNumber(row.conv1),
          formatNumber(row.conv2),
          formatNumber(qty),
          formatRupiah(netto),
          formatRupiah(ppn),
          formatRupiah(total),
        ]);
      });
      if (current_faktur !== null) pushSubtotalFakturRowPdf();
      if (current_tanggal !== null) pushSubtotalTanggalRowPdf();
      body.push([
        { content: "GRAND TOTAL", colSpan: 6, styles: grandTotalStyles },
        {
          content: formatNumber(summary.total_qty),
          styles: grandTotalValuesStyles,
        },
        {
          content: formatRupiah(summary.total_netto),
          styles: grandTotalValuesStyles,
        },
        {
          content: formatRupiah(summary.total_ppn),
          styles: grandTotalValuesStyles,
        },
        {
          content: formatRupiah(summary.total_grand),
          styles: grandTotalValuesStyles,
        },
      ]);
      doc.autoTable({
        margin: { top: 44, left: 14 },
        tableWidth: 266,
        head: head,
        body: body,
        theme: "grid",
        headStyles: {
          fillColor: [220, 220, 220],
          textColor: [0, 0, 0],
          fontSize: 6,
        },
        styles: { fontSize: 5, cellPadding: 1.5 },
        columnStyles: {
          0: { halign: "left" },
          1: { halign: "left" },
          2: { halign: "left" },
          3: { halign: "left" },
          4: { halign: "left" },
          5: { halign: "left" },
          6: { halign: "left" },
          7: { halign: "left" },
          8: { halign: "left" },
          9: { halign: "left" },
        },
      });
      const fileName = `Return_Out_All_Item_${params.tgl_mulai}_sd_${params.tgl_selesai}.pdf`;
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
      loadData();
    });
  }
  loadData();
});
