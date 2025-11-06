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
        `/src/api/top_sales/get_by_supplier.php?${queryString}`
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
        pageSubtitle.textContent = `Top Sales by Supplier Periode ${params.tgl_mulai} s/d ${params.tgl_selesai} - ${storeName}`;
        if (pageTitle) {
          pageTitle.textContent = `Top Sales by (Supplier) - ${storeName}`;
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
        pageTitle.textContent = "Top Sales by (Supplier)";
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
    tabel_data.forEach((row, index) => {
      htmlRows += `
                                <tr>
                                    <td>${offset + index + 1}</td>
                                    <td>${row.kode_supp}</td>
                                    <td class="text-left font-semibold">${
                                      row.nama_supp
                                    }</td>
                                    <td class="">${formatNumber(row.qty)}</td>
                                    <td class="">${formatRupiah(
                                      row.gross_sales
                                    )}</td>
                                    <td class="">${formatRupiah(row.ppn)}</td>
                                    <td class="">${formatRupiah(
                                      row.total_diskon
                                    )}</td>
                                    <td class=" font-semibold">${formatRupiah(
                                      row.net_sales
                                    )}</td>
                                    <td class="">${formatRupiah(row.hpp)}</td>
                                    <td class="">${formatRupiah(
                                      row.grs_margin
                                    )}</td>
                                    <td class="">${formatPercent(
                                      row.margin_percent
                                    )}</td>
                                </tr>
                            `;
    });
    tableBody.innerHTML = htmlRows;
  }

  /**
   * Merender navigasi pagination.
   */
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
        `/src/api/top_sales/get_by_supplier.php?${queryString}`
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
      const title = [["Laporan Top Sales (Supplier)"]];
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
        "Kode Supplier",
        "Nama Supplier",
        "Qty",
        "Gross Sales",
        "PPN",
        "Disc",
        "Net Sales",
        "HPP",
        "Grs Magin",
        "%",
      ];
      const dataRows = tabel_data.map((row, index) => [
        index + 1,
        row.kode_supp,
        row.nama_supp,
        parseFloat(row.qty),
        parseFloat(row.gross_sales),
        parseFloat(row.ppn),
        parseFloat(row.total_diskon),
        parseFloat(row.net_sales),
        parseFloat(row.hpp),
        parseFloat(row.grs_margin),
        parseFloat(row.margin_percent) / 100,
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
      const percentFormat = "0.00%";
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
        ws[XLSX.utils.encode_cell({ r: R, c: 3 })].s = { numFmt: numFormat };
        [4, 5, 6, 7, 8, 9].forEach((C) => {
          ws[XLSX.utils.encode_cell({ r: R, c: C })].s = { numFmt: numFormat };
        });
        ws[XLSX.utils.encode_cell({ r: R, c: 10 })].s = {
          numFmt: percentFormat,
        };
      });
      ws["!cols"] = [
        { wch: 5 },
        { wch: 15 },
        { wch: 40 },
        { wch: 10 },
        { wch: 15 },
        { wch: 15 },
        { wch: 15 },
        { wch: 15 },
        { wch: 15 },
        { wch: 15 },
        { wch: 10 },
      ];
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, "Top Sales Supplier");
      const fileName = `Top_Sales_Supplier_${params.tgl_mulai}_sd_${params.tgl_selesai}.xlsx`;
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
      doc.text("Laporan Top Sales (Supplier)", 14, 22);
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
          "Kode Supplier",
          "Nama Supplier",
          "Qty",
          "Gross Sales",
          "PPN",
          "Disc",
          "Net Sales",
          "HPP",
          "Grs Magin",
          "%",
        ],
      ];
      const body = tabel_data.map((row, index) => [
        index + 1,
        row.kode_supp,
        row.nama_supp,
        formatNumber(row.qty),
        formatRupiah(row.gross_sales),
        formatRupiah(row.ppn),
        formatRupiah(row.total_diskon),
        formatRupiah(row.net_sales),
        formatRupiah(row.hpp),
        formatRupiah(row.grs_margin),
        formatPercent(row.margin_percent),
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
          0: { halign: "right", cellWidth: 12 },
          1: { halign: "left", cellWidth: 20 },
          2: { halign: "left", cellWidth: 55 },
          3: { halign: "right" },
          4: { halign: "right" },
          5: { halign: "right" },
          6: { halign: "right" },
          7: { halign: "right" },
          8: { halign: "right" },
          9: { halign: "right" },
          10: { halign: "right" },
        },
      });
      const fileName = `Top_Sales_Supplier_${params.tgl_mulai}_sd_${params.tgl_selesai}.pdf`;
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
