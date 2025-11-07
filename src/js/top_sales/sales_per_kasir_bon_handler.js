document.addEventListener("DOMContentLoaded", () => {
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

  async function loadTopSalesData() {
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
      renderTable(data.tabel_data, data.pagination, data.summary);
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

  function renderTable(tabel_data, pagination, summary) {
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

    const offset = pagination ? pagination.offset : 0;
    let htmlRows = "";
    let item_counter = offset + 1;
    let current_no_bon = null;

    let subtotal_qty = 0;
    let subtotal_diskon = 0;
    let subtotal_total = 0;

    function buildBonHeaderRow(row) {
      return `
                <tr class="header-faktur-row" style="background-color: #f7fafc; font-size: 13px;">
                    <td colspan="3" class="px-4 py-1 pl-3">
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
                <tr class="subtotal-row" style="font-style: italic; font-weight: bold; background-color: #feffe4;">
                    <td colspan="3" class="text-right px-4 py-2 font-bold">
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

      if (row.no_bon !== current_no_bon) {
        if (current_no_bon !== null) {
          // Tampilkan subtotal untuk grup sebelumnya
          htmlRows += buildBonSubtotalRow(
            subtotal_qty,
            subtotal_diskon,
            subtotal_total
          );
          // Tambahkan baris kosong untuk spasi
          htmlRows += `<tr class="group-spacer"><td colspan="7" style="padding: 2px; background-color: #f1f5f9;"></td></tr>`;
        }

        // Reset untuk grup baru
        current_no_bon = row.no_bon;
        subtotal_qty = 0;
        subtotal_diskon = 0;
        subtotal_total = 0;

        // Tampilkan header untuk grup baru
        htmlRows += buildBonHeaderRow(row);
      }

      // Akumulasi subtotal
      subtotal_qty += qty;
      subtotal_diskon += diskon;
      subtotal_total += total;

      // Tampilkan baris item
      htmlRows += `
                <tr>
                    <td>${item_counter}</td>
                    <td>${row.plu}</td>
                    <td class="text-left">${row.nama_barang}</td>
                    <td class="">${formatNumber(qty)}</td>
                    <td class="">${formatRupiah(row.harga)}</td>
                    <td class="">${formatRupiah(diskon)}</td>
                    <td class="font-semibold">${formatRupiah(total)}</td>
                </tr>
            `;
      item_counter++;
    });

    // Tampilkan subtotal terakhir setelah loop selesai
    if (current_no_bon !== null) {
      htmlRows += buildBonSubtotalRow(
        subtotal_qty,
        subtotal_diskon,
        subtotal_total
      );
    }

    // Tampilkan Grand Total jika ini halaman terakhir
    const isLastPage =
      pagination && pagination.current_page === pagination.total_pages;
    const isExport = pagination === null; // Menandakan ini dari export

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

  /**
   * Mengambil semua data dari API untuk keperluan export.
   */
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

  /**
   * Fungsi untuk export data ke Excel
   */
  async function exportToExcel() {
    const data = await fetchAllDataForExport();
    if (!data || !data.tabel_data || data.tabel_data.length === 0) {
      Swal.fire("Tidak Ada Data", "Tidak ada data untuk diekspor.", "info");
      return;
    }
    try {
      const { tabel_data, summary } = data;
      const params = getUrlParams();
      const title = [["Laporan Sales per Kasir & Bon"]];
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

      // Header tabel baru
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
      let item_counter = 1;
      let current_no_bon = null;

      let subtotal_qty = 0;
      let subtotal_diskon = 0;
      let subtotal_total = 0;

      const pushSubtotalRow = () => {
        dataRows.push([
          "",
          "",
          "Sub Total Bon:",
          subtotal_qty,
          "",
          subtotal_diskon,
          subtotal_total,
        ]);
        merges.push({
          s: { r: dataRows.length + info.length + 1, c: 0 },
          e: { r: dataRows.length + info.length + 1, c: 2 },
        });
      };

      tabel_data.forEach((row, index) => {
        const qty = parseFloat(row.qty) || 0;
        const diskon = parseFloat(row.total_diskon) || 0;
        const total = parseFloat(row.total) || 0;

        if (row.no_bon !== current_no_bon) {
          if (current_no_bon !== null) {
            pushSubtotalRow();
            dataRows.push([]); // Baris kosong
          }
          current_no_bon = row.no_bon;
          subtotal_qty = 0;
          subtotal_diskon = 0;
          subtotal_total = 0;

          // Baris Header Bon
          dataRows.push([
            `No Trans: ${row.no_bon}`,
            "",
            ``,
            "",
            `Kasir: ${row.kode_kasir} - ${row.nama_kasir}`,
          ]);
          merges.push({
            s: { r: dataRows.length + info.length + 1, c: 0 },
            e: { r: dataRows.length + info.length + 1, c: 1 },
          });
          merges.push({
            s: { r: dataRows.length + info.length + 1, c: 2 },
            e: { r: dataRows.length + info.length + 1, c: 3 },
          });
          merges.push({
            s: { r: dataRows.length + info.length + 1, c: 4 },
            e: { r: dataRows.length + info.length + 1, c: 6 },
          });
        }

        // Akumulasi subtotal
        subtotal_qty += qty;
        subtotal_diskon += diskon;
        subtotal_total += total;

        // Baris Item
        dataRows.push([
          item_counter++,
          row.plu,
          row.nama_barang,
          qty,
          parseFloat(row.harga),
          diskon,
          total,
        ]);
      });

      // Push subtotal terakhir
      if (current_no_bon !== null) {
        pushSubtotalRow();
      }

      // Baris Grand Total
      dataRows.push([]); // Spasi
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
        s: { r: dataRows.length + info.length + 1, c: 0 },
        e: { r: dataRows.length + info.length + 1, c: 2 },
      });

      const ws = XLSX.utils.aoa_to_sheet(title);
      XLSX.utils.sheet_add_aoa(ws, info, { origin: "A2" });
      const headerOrigin = "A" + (info.length + 2);
      XLSX.utils.sheet_add_aoa(ws, [headers], { origin: headerOrigin });
      XLSX.utils.sheet_add_aoa(ws, dataRows, {
        origin: "A" + (info.length + 3),
      });

      // Perbaiki merges (tambahkan offset)
      const newMerges = merges.map((m) => ({
        s: { r: m.s.r, c: m.s.c },
        e: { r: m.e.r, c: m.e.c },
      }));

      ws["!merges"] = [
        { s: { r: 0, c: 0 }, e: { r: 0, c: 6 } }, // Title
        ...newMerges,
      ];

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
      const grandTotalStyle = {
        font: { bold: true, sz: 12 },
        fill: { fgColor: { rgb: "EBF8FF" } },
        alignment: { horizontal: "right" },
      };
      const bonHeaderStyle = {
        font: { bold: true },
        fill: { fgColor: { rgb: "F7FAFC" } },
      };
      const subtotalStyle = {
        font: { bold: true, italic: true },
        fill: { fgColor: { rgb: "FEFFE4" } },
        alignment: { horizontal: "right" },
      };

      // Format Info Summary
      ["B4", "B5", "B6", "B7", "B8"].forEach((cell) => {
        if (ws[cell]) {
          ws[cell].t = "n";
          ws[cell].s = { numFmt: numFormat };
        }
      });

      // Format Header Tabel
      headers.forEach((_, C) => {
        const cell = ws[XLSX.utils.encode_cell({ r: info.length + 1, c: C })];
        if (cell) cell.s = headerStyle;
      });

      // Format Data Rows
      const dataRowStartIndex = info.length + 2;
      dataRows.forEach((row, R_idx) => {
        const R = R_idx + dataRowStartIndex;
        if (row.length === 0) return; // Baris spasi

        const label = row[0] || row[2];
        if (typeof label === "string") {
          if (label.startsWith("No Trans:")) {
            // Baris Header Bon
            ["A", "C", "E"].forEach((col) => {
              const cell = ws[col + (R + 1)];
              if (cell) cell.s = bonHeaderStyle;
            });
          } else if (label.startsWith("Sub Total Bon:")) {
            // Baris Subtotal
            ws["C" + (R + 1)].s = { ...subtotalStyle, numFmt: numFormat };
            ws["D" + (R + 1)].s = { ...subtotalStyle, numFmt: numFormatDec };
            ws["F" + (R + 1)].s = { ...subtotalStyle, numFmt: numFormat };
            ws["G" + (R + 1)].s = { ...subtotalStyle, numFmt: numFormat };
          } else if (label.startsWith("GRAND TOTAL")) {
            // Baris Grand Total
            ws["C" + (R + 1)].s = { ...grandTotalStyle, numFmt: numFormat };
            ws["D" + (R + 1)].s = { ...grandTotalStyle, numFmt: numFormatDec };
            ws["F" + (R + 1)].s = { ...grandTotalStyle, numFmt: numFormat };
            ws["G" + (R + 1)].s = { ...grandTotalStyle, numFmt: numFormat };
          } else if (row[0] && typeof row[0] === "number") {
            // Baris Item
            ws["D" + (R + 1)].s = { numFmt: numFormatDec }; // Qty
            ws["E" + (R + 1)].s = { numFmt: numFormat }; // Harga
            ws["F" + (R + 1)].s = { numFmt: numFormat }; // Disc
            ws["G" + (R + 1)].s = { numFmt: numFormat }; // Total
          }
        }
      });

      ws["!cols"] = [
        { wch: 5 }, // No
        { wch: 12 }, // PLU
        { wch: 35 }, // Nama Barang
        { wch: 10 }, // Qty
        { wch: 15 }, // Harga
        { wch: 15 }, // Disc
        { wch: 17 }, // Total
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

  /**
   * Fungsi untuk export data ke PDF
   */
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

      // Header tabel baru
      const head = [
        ["No", "PLU", "Nama Barang", "Qty", "Harga", "Disc", "Total"],
      ];

      const body = [];
      let item_counter = 1;
      let current_no_bon = null;

      let subtotal_qty = 0;
      let subtotal_diskon = 0;
      let subtotal_total = 0;

      const headerBonStyles = {
        fontStyle: "bold",
        fillColor: [247, 250, 252],
        textColor: [74, 85, 104],
        fontSize: 6,
        halign: "left",
      };
      const subtotalBonStyles = {
        halign: "right",
        fontStyle: "bolditalic",
        fillColor: [254, 253, 232],
        textColor: [113, 63, 18],
        fontSize: 6,
      };
      const subtotalBonValuesStyles = {
        ...subtotalBonStyles,
        halign: "right",
      };
      const grandTotalStyles = {
        halign: "right",
        fontStyle: "bold",
        fillColor: [226, 232, 240],
        fontSize: 7,
      };
      const grandTotalValuesStyles = {
        ...grandTotalStyles,
        halign: "right",
      };

      const pushSubtotalRowPdf = () => {
        body.push([
          {
            content: "Sub Total Bon:",
            colSpan: 3,
            styles: subtotalBonStyles,
          },
          {
            content: formatNumber(subtotal_qty),
            styles: subtotalBonValuesStyles,
          },
          { content: "", styles: subtotalBonStyles },
          {
            content: formatRupiah(subtotal_diskon),
            styles: subtotalBonValuesStyles,
          },
          {
            content: formatRupiah(subtotal_total),
            styles: subtotalBonValuesStyles,
          },
        ]);
      };

      tabel_data.forEach((row, index) => {
        const qty = parseFloat(row.qty) || 0;
        const diskon = parseFloat(row.total_diskon) || 0;
        const total = parseFloat(row.total) || 0;

        if (row.no_bon !== current_no_bon) {
          if (current_no_bon !== null) {
            pushSubtotalRowPdf();
          }
          current_no_bon = row.no_bon;
          subtotal_qty = 0;
          subtotal_diskon = 0;
          subtotal_total = 0;

          // Baris Header Bon
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
        }

        // Akumulasi subtotal
        subtotal_qty += qty;
        subtotal_diskon += diskon;
        subtotal_total += total;

        // Baris Item
        body.push([
          item_counter++,
          row.plu,
          row.nama_barang,
          formatNumber(qty),
          formatRupiah(row.harga),
          formatRupiah(diskon),
          formatRupiah(total),
        ]);
      });

      // Push subtotal terakhir
      if (current_no_bon !== null) {
        pushSubtotalRowPdf();
      }

      // Baris Grand Total
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
          0: { halign: "right", cellWidth: 8 }, // No
          1: { halign: "left", cellWidth: 20 }, // PLU
          2: { halign: "left", cellWidth: 100 }, // Nama Barang
          3: { halign: "right", cellWidth: 20 }, // Qty
          4: { halign: "right", cellWidth: 35 }, // Harga
          5: { halign: "right", cellWidth: 35 }, // Disc
          6: { halign: "right", cellWidth: 40 }, // Total
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
