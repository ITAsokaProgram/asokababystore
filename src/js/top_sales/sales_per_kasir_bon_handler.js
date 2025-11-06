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
      return "Rp 0";
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
    return new Intl.NumberFormat("id-ID").format(number);
  }
  function formatPercent(number) {
    if (isNaN(number) || number === null) {
      return "0.00 ";
    }
    return parseFloat(number).toFixed(2) + " ";
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
      renderTable(data.tabel_data, data.pagination.offset);
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
                                    <td colspan="11" class="text-center p-8">
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
  function updateSummaryCards(summary) {
    summaryNetSales.textContent = formatRupiah(summary.total_net_sales);
    summaryGrsMargin.textContent = formatRupiah(summary.total_grs_margin);
    summaryHpp.textContent = formatRupiah(summary.total_hpp);
  }
  function renderTable(tabel_data, offset) {
    if (!tabel_data || tabel_data.length === 0) {
      tableBody.innerHTML = `
                                <tr>
                                    <td colspan="11" class="text-center p-8 text-gray-500">
                                        <i class="fas fa-inbox fa-lg mb-2"></i>
                                        <p>Tidak ada data ditemukan untuk filter ini.</p>
                                    </td>
                                </tr>`;
      return;
    }
    let htmlRows = "";
    let item_counter = offset + 1;
    let current_no_bon = null;
    let subtotal_qty = 0;
    let subtotal_diskon = 0;
    let subtotal_total = 0;
    function buildSubtotalRow(no_bon, qty, diskon, total) {
      return `
                <tr class="subtotal-row" style=" font-weight: bold; border-top: 2px solid #e2e8f0; background-color: aqua;">
                    <td colspan="7" class=" px-4 py-2" style="font-style: italic; font-weight: bold; text-align: right;">${no_bon}</td>
                    <td class=" px-2 font-bold py-2">${formatNumber(qty)}</td>
                    <td class="px-2 py-2"></td> <td class=" px-2 py-2 font-bold">${formatRupiah(
                      diskon
                    )}</td>
                    <td class=" px-2 py-2 font-bold">${formatRupiah(total)}</td>
                </tr>
            `;
    }
    tabel_data.forEach((row, index) => {
      if (index === 0) {
        current_no_bon = row.no_bon;
      }
      if (row.no_bon !== current_no_bon) {
        htmlRows += buildSubtotalRow(
          current_no_bon,
          subtotal_qty,
          subtotal_diskon,
          subtotal_total
        );
        current_no_bon = row.no_bon;
        subtotal_qty = 0;
        subtotal_diskon = 0;
        subtotal_total = 0;
      }
      subtotal_qty += parseFloat(row.qty) || 0;
      subtotal_diskon += parseFloat(row.total_diskon) || 0;
      subtotal_total += parseFloat(row.total) || 0;
      htmlRows += `
                <tr>
                    <td>${item_counter}</td>
                    <td>${row.tanggal}</td>
                    <td>${row.kode_kasir}</td>
                    <td class="text-left">${row.nama_kasir}</td>
                    <td>${row.no_bon}</td>
                    <td>${row.plu}</td>
                    <td class="text-left font-semibold">${row.nama_barang}</td>
                    <td class="">${formatNumber(row.qty)}</td>
                    <td class="">${formatRupiah(row.harga)}</td>
                    <td class="">${formatRupiah(row.total_diskon)}</td>
                    <td class=" font-semibold">${formatRupiah(row.total)}</td>
                </tr>
            `;
      item_counter++;
    });
    if (current_no_bon !== null) {
      htmlRows += buildSubtotalRow(
        current_no_bon,
        subtotal_qty,
        subtotal_diskon,
        subtotal_total
      );
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
        ["Total Net Sales", parseFloat(summary.total_net_sales) || 0],
        ["Total Gross Margin", parseFloat(summary.total_grs_margin) || 0],
        ["Total HPP", parseFloat(summary.total_hpp) || 0],
        [],
      ];
      const headers = [
        "No",
        "Tanggal",
        "Kd Kasir",
        "Nama Kasir",
        "No Trans",
        "PLU",
        "Nama Barang",
        "Qty",
        "Harga",
        "Disc",
        "Total",
      ];
      const dataRows = tabel_data.map((row, index) => [
        index + 1,
        row.tanggal,
        row.kode_kasir,
        row.nama_kasir,
        row.no_bon,
        row.plu,
        row.nama_barang,
        parseFloat(row.qty),
        parseFloat(row.harga),
        parseFloat(row.total_diskon),
        parseFloat(row.total),
      ]);
      const ws = XLSX.utils.aoa_to_sheet(title);
      XLSX.utils.sheet_add_aoa(ws, info, { origin: "A2" });
      XLSX.utils.sheet_add_aoa(ws, [headers], { origin: "A9" });
      XLSX.utils.sheet_add_aoa(ws, dataRows, { origin: "A10" });
      ws["!merges"] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 10 } }];
      ws["A1"].s = {
        font: { bold: true, sz: 16 },
        alignment: { horizontal: "center" },
      };
      const numFormat = "#,##0";
      const headerStyle = {
        font: { bold: true },
        fill: { fgColor: { rgb: "E0E0E0" } },
      };
      ["B5", "B6", "B7"].forEach((cell) => {
        if (ws[cell]) {
          ws[cell].t = "n";
          ws[cell].s = { numFmt: numFormat };
        }
      });
      headers.forEach((_, C) => {
        if (ws[XLSX.utils.encode_cell({ r: 8, c: C })])
          ws[XLSX.utils.encode_cell({ r: 8, c: C })].s = headerStyle;
      });
      dataRows.forEach((_, R_idx) => {
        const R = R_idx + 9;
        ws[XLSX.utils.encode_cell({ r: R, c: 7 })].s = { numFmt: numFormat };
        [8, 9, 10].forEach((C) => {
          ws[XLSX.utils.encode_cell({ r: R, c: C })].s = { numFmt: numFormat };
        });
      });
      ws["!cols"] = [
        { wch: 5 },
        { wch: 12 },
        { wch: 10 },
        { wch: 25 },
        { wch: 15 },
        { wch: 10 },
        { wch: 40 },
        { wch: 10 },
        { wch: 15 },
        { wch: 15 },
        { wch: 15 },
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
      doc.text(
        `Total Net Sales: ${formatRupiah(summary.total_net_sales)}`,
        180,
        22
      );
      doc.text(
        `Total Gross Margin: ${formatRupiah(summary.total_grs_margin)}`,
        180,
        30
      );
      doc.text(`Total HPP: ${formatRupiah(summary.total_hpp)}`, 180, 36);
      const head = [
        [
          "No",
          "Tanggal",
          "Kd Kasir",
          "Nama Kasir",
          "No Trans",
          "PLU",
          "Nama Barang",
          "Qty",
          "Harga",
          "Disc",
          "Total",
        ],
      ];
      const body = tabel_data.map((row, index) => [
        index + 1,
        row.tanggal,
        row.kode_kasir,
        row.nama_kasir,
        row.no_bon,
        row.plu,
        row.nama_barang,
        formatNumber(row.qty),
        formatRupiah(row.harga),
        formatRupiah(row.total_diskon),
        formatRupiah(row.total),
      ]);
      doc.autoTable({
        startY: 44,
        head: head,
        body: body,
        theme: "grid",
        headStyles: {
          fillColor: [220, 220, 220],
          textColor: [0, 0, 0],
          fontSize: 8,
        },
        styles: { fontSize: 7 },
        columnStyles: {
          0: { halign: "right", cellWidth: 8 },
          1: { halign: "left", cellWidth: 16 },
          2: { halign: "left", cellWidth: 15 },
          3: { halign: "left", cellWidth: 30 },
          4: { halign: "left", cellWidth: 16 },
          5: { halign: "left", cellWidth: 15 },
          6: { halign: "left", cellWidth: 50 },
          7: { halign: "right" },
          8: { halign: "right" },
          9: { halign: "right" },
          10: { halign: "right" },
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
  loadTopSalesData();
});
