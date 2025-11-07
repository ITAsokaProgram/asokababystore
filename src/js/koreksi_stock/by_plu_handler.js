document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("koreksi-plu-table-body");
  const filterForm = document.getElementById("filter-form");
  const filterSubmitButton = document.getElementById("filter-submit-button");
  const filterSelectStore = document.getElementById("kd_store");
  const summaryQty = document.getElementById("summary-qty");
  const summaryRp = document.getElementById("summary-rp");
  const summarySelisih = document.getElementById("summary-selisih");
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
        `/src/api/koreksi_stock/get_by_plu.php?${queryString}`
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
        pageSubtitle.textContent = `Laporan Koreksi (PLU) Periode ${params.tgl_mulai} s/d ${params.tgl_selesai} - ${storeName}`;
        if (pageTitle) {
          pageTitle.textContent = `Laporan Koreksi (PLU) - ${storeName}`;
        }
      }
      if (data.summary) {
        updateSummaryCards(data.summary);
      }
      renderTable(data.tabel_data, data.pagination.offset, data.summary);
      renderPagination(data.pagination);
    } catch (error) {
      console.error("Error loading data:", error);
      showTableError(error.message);
      if (pageSubtitle) {
        pageSubtitle.textContent = "Gagal memuat data filter";
      }
      if (pageTitle) {
        pageTitle.textContent = "Laporan Koreksi (PLU)";
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
                            <td colspan="12" class="text-center p-8">
                                <div class="spinner-simple"></div>
                                <p class="mt-2 text-gray-500">Memuat data...</p>
                            </td>
                        </tr>`;
        if (!isPagination) {
          if (summaryQty) summaryQty.textContent = "-";
          if (summaryRp) summaryRp.textContent = "-";
          if (summarySelisih) summarySelisih.textContent = "-";
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
                <td colspan="12" class="text-center p-8 text-red-600">
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
    summaryQty.textContent = formatNumber(summary.total_qtykor);
    summaryRp.textContent = formatRupiah(summary.total_rp_koreksi);
    summarySelisih.textContent = formatRupiah(summary.total_rp_selisih);
  }
  function renderTable(tabel_data, offset, summary) {
    if (!tabel_data || tabel_data.length === 0) {
      tableBody.innerHTML = `
                <tr>
                    <td colspan="12" class="text-center p-8 text-gray-500">
                        <i class="fas fa-inbox fa-lg mb-2"></i>
                        <p>Tidak ada data ditemukan untuk filter ini.</p>
                    </td>
                </tr>`;
      return;
    }
    let htmlRows = "";
    let item_counter = offset + 1;
    let current_no_faktur = null;
    let subtotal_qtykor = 0;
    let subtotal_t_rp = 0;
    let subtotal_t_selisih = 0;
    function buildFakturHeaderRow(no_faktur) {
      return `
                <tr class="faktur-header-row" style="background-color: #f3f4f6; font-weight: bold;">
                    <td colspan="12" style="padding: 0.75rem 1rem; color: #1f2937; border-top: 1px solid #e5e7eb;">
                        No Faktur: ${no_faktur}
                    </td>
                </tr>
            `;
    }
    function buildSubtotalRow(no_faktur, qtykor, t_rp, t_selisih) {
      return `
                <tr class="subtotal-row">
                    <td colspan="6" class="text-right px-4 py-2" style="font-style: italic; text-align: right;">${no_faktur}</td>
                    <td class="text-left px-2 py-2">${formatNumber(qtykor)}</td>
                    <td colspan="2"></td> 
                    <td class="text-left px-2 py-2">${formatRupiah(t_rp)}</td>
                    <td class="text-left px-2 py-2">${formatRupiah(
                      t_selisih
                    )}</td>
                    <td></td>
                </tr>
            `;
    }
    tabel_data.forEach((row, index) => {
      if (row.no_faktur !== current_no_faktur) {
        if (index > 0) {
          htmlRows += buildSubtotalRow(
            current_no_faktur,
            subtotal_qtykor,
            subtotal_t_rp,
            subtotal_t_selisih
          );
          subtotal_qtykor = 0;
          subtotal_t_rp = 0;
          subtotal_t_selisih = 0;
        }
        current_no_faktur = row.no_faktur;
        htmlRows += buildFakturHeaderRow(current_no_faktur);
      }
      subtotal_qtykor += parseFloat(row.qtykor) || 0;
      subtotal_t_rp += parseFloat(row.t_rp) || 0;
      subtotal_t_selisih += parseFloat(row.t_selisih) || 0;
      htmlRows += `
                <tr>
                    <td>${item_counter}</td>
                    <td>${row.plu}</td> 
                    <td class="text-left">${row.deskripsi}</td>
                    <td class="text-left">${formatNumber(row.conv1)}</td>
                    <td class="text-left">${formatNumber(row.conv2)}</td>
                    <td class="text-left">${formatRupiah(row.hpp)}</td>
                    <td class="text-left font-semibold">${formatNumber(
                      row.qtykor
                    )}</td>
                    <td class="text-left">${formatNumber(row.stock)}</td>
                    <td class="text-left">${formatNumber(row.selqty)}</td>
                    <td class="text-left font-semibold">${formatRupiah(
                      row.t_rp
                    )}</td>
                    <td class="text-left font-semibold">${formatRupiah(
                      row.t_selisih
                    )}</td>
                    <td class="text-left">${row.ket}</td>
                </tr>
            `;
      item_counter++;
    });
    if (current_no_faktur !== null) {
      htmlRows += buildSubtotalRow(
        current_no_faktur,
        subtotal_qtykor,
        subtotal_t_rp,
        subtotal_t_selisih
      );
    }
    if (summary) {
      htmlRows += `
                <tr style="border-top: 4px solid #4A5568; background-color: #E2E8F0; font-weight: bold; font-size: 1.05em;">
                    <td colspan="6" class="text-left px-4 py-3" style="font-style: italic;">
                        GRAND TOTAL
                    </td>
                    <td class="text-left px-2 py-3">${formatNumber(
                      summary.total_qtykor
                    )}</td>
                    <td colspan="2"></td>
                    <td class="text-left px-2 py-3">${formatRupiah(
                      summary.total_rp_koreksi
                    )}</td>
                    <td class="text-left px-2 py-3">${formatRupiah(
                      summary.total_rp_selisih
                    )}</td>
                    <td></td>
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
        `/src/api/koreksi_stock/get_by_plu.php?${queryString}`
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
      const title = [["Laporan Koreksi (PLU)"]];
      const info = [
        ["Periode", `${params.tgl_mulai} s/d ${params.tgl_selesai}`],
        [
          "Cabang",
          filterSelectStore.options[filterSelectStore.selectedIndex].text,
        ],
        [],
        ["Total Qty Koreksi", parseFloat(summary.total_qtykor) || 0],
        ["Total Rp Koreksi", parseFloat(summary.total_rp_koreksi) || 0],
        ["Total Rp Selisih", parseFloat(summary.total_rp_selisih) || 0],
        [],
      ];
      const headers = [
        "No",
        "No Faktur",
        "PLU",
        "Nama Barang",
        "Conv1",
        "Conv2",
        "HPP",
        "Qty Koreksi",
        "Stock",
        "Selisih Qty",
        "Total Rp Koreksi",
        "Total Rp Selisih",
        "Keterangan",
      ];
      const dataRows = [];
      let item_counter = 1;
      let current_no_faktur = null;
      let subtotal_qtykor = 0,
        subtotal_t_rp = 0,
        subtotal_t_selisih = 0;
      const pushSubtotalRow = () => {
        dataRows.push([
          "",
          "",
          "",
          "",
          "",
          "",
          `${current_no_faktur}`,
          subtotal_qtykor,
          "",
          "",
          subtotal_t_rp,
          subtotal_t_selisih,
          "",
        ]);
      };
      tabel_data.forEach((row, index) => {
        if (index === 0) {
          current_no_faktur = row.no_faktur;
        }
        if (row.no_faktur !== current_no_faktur && current_no_faktur !== null) {
          pushSubtotalRow();
          subtotal_qtykor = 0;
          subtotal_t_rp = 0;
          subtotal_t_selisih = 0;
          current_no_faktur = row.no_faktur;
        }
        subtotal_qtykor += parseFloat(row.qtykor) || 0;
        subtotal_t_rp += parseFloat(row.t_rp) || 0;
        subtotal_t_selisih += parseFloat(row.t_selisih) || 0;
        dataRows.push([
          item_counter++,
          row.no_faktur,
          row.plu,
          row.deskripsi,
          parseFloat(row.conv1),
          parseFloat(row.conv2),
          parseFloat(row.hpp),
          parseFloat(row.qtykor),
          parseFloat(row.stock),
          parseFloat(row.selqty),
          parseFloat(row.t_rp),
          parseFloat(row.t_selisih),
          row.ket,
        ]);
      });
      if (current_no_faktur !== null) {
        pushSubtotalRow();
      }
      dataRows.push([]);
      dataRows.push([
        "",
        "",
        "",
        "",
        "",
        "",
        "GRAND TOTAL",
        summary.total_qtykor,
        "",
        "",
        summary.total_rp_koreksi,
        summary.total_rp_selisih,
        "",
      ]);
      const ws = XLSX.utils.aoa_to_sheet(title);
      XLSX.utils.sheet_add_aoa(ws, info, { origin: "A2" });
      const headerOrigin = "A" + (info.length + 2);
      XLSX.utils.sheet_add_aoa(ws, [headers], { origin: headerOrigin });
      XLSX.utils.sheet_add_aoa(ws, dataRows, {
        origin: "A" + (info.length + 3),
      });
      ws["!merges"] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 12 } }];
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
      ["B5", "B6", "B7"].forEach((cell) => {
        if (ws[cell]) {
          ws[cell].t = "n";
          ws[cell].s = { numFmt: numFormat };
        }
      });
      headers.forEach((_, C) => {
        const cell = ws[XLSX.utils.encode_cell({ r: info.length + 1, c: C })];
        if (cell) cell.s = headerStyle;
      });
      dataRows.forEach((row, R_idx) => {
        const R = R_idx + info.length + 2;
        if (typeof row[6] === "string" && row[6].startsWith("Sub Total")) {
          ws[XLSX.utils.encode_cell({ r: R, c: 6 })].s = {
            font: { bold: true, italic: true },
            alignment: { horizontal: "right" },
          };
          ["H", "K", "L"].forEach((col) => {
            const cell = ws[col + (R + 1)];
            if (cell) {
              cell.t = "n";
              cell.s = { numFmt: numFormat, font: { bold: true } };
            }
          });
          ws["!merges"] = ws["!merges"] || [];
          ws["!merges"].push({ s: { r: R, c: 0 }, e: { r: R, c: 6 } });
        } else if (typeof row[6] === "string" && row[6] === "GRAND TOTAL") {
          ws[XLSX.utils.encode_cell({ r: R, c: 6 })].s = {
            font: { bold: true, sz: 12 },
            alignment: { horizontal: "right" },
          };
          ["H", "K", "L"].forEach((col) => {
            const cell = ws[col + (R + 1)];
            if (cell) {
              cell.t = "n";
              cell.s = { numFmt: numFormat, font: { bold: true, sz: 12 } };
            }
          });
          ws["!merges"] = ws["!merges"] || [];
          ws["!merges"].push({ s: { r: R, c: 0 }, e: { r: R, c: 6 } });
        } else if (row.length > 0) {
          ["E", "F", "H", "I", "J"].forEach((col) => {
            const cell = ws[col + (R + 1)];
            if (cell) cell.s = { numFmt: numFormatDec };
          });
          ["G", "K", "L"].forEach((col) => {
            const cell = ws[col + (R + 1)];
            if (cell) {
              cell.t = "n";
              cell.s = { numFmt: numFormat };
            }
          });
        }
      });
      ws["!cols"] = [
        { wch: 5 },
        { wch: 18 },
        { wch: 12 },
        { wch: 35 },
        { wch: 8 },
        { wch: 8 },
        { wch: 15 },
        { wch: 10 },
        { wch: 10 },
        { wch: 10 },
        { wch: 17 },
        { wch: 17 },
        { wch: 15 },
      ];
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, "Koreksi PLU");
      const fileName = `Koreksi_PLU_${params.tgl_mulai}_sd_${params.tgl_selesai}.xlsx`;
      XLSX.writeFile(wb, fileName);
    } catch (error) {
      console.error("Error exporting to Excel:", error);
      Swal.fire("Export Gagal", "Terjadi kesalahan: " + error.message, "error");
    }
  }
  /**
   * Fungsi untuk export data ke PDF
   */
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
      doc.text("Laporan Koreksi (PLU)", 14, 22);
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
        `Total Qty Koreksi: ${formatNumber(summary.total_qtykor)}`,
        280,
        22,
        {
          align: "right",
        }
      );
      doc.text(
        `Total Rp Koreksi: ${formatRupiah(summary.total_rp_koreksi)}`,
        280,
        30,
        {
          align: "right",
        }
      );
      doc.text(
        `Total Rp Selisih: ${formatRupiah(summary.total_rp_selisih)}`,
        280,
        36,
        {
          align: "right",
        }
      );
      const head = [
        [
          "No",
          "No Faktur",
          "PLU",
          "Nama Barang",
          "C1",
          "C2",
          "HPP",
          "QtyKor",
          "Stock",
          "SelQty",
          "T.Rp Kor",
          "T.Rp Sel",
          "Ket",
        ],
      ];
      const body = [];
      let item_counter = 1;
      let current_no_faktur = null;
      let subtotal_qtykor = 0,
        subtotal_t_rp = 0,
        subtotal_t_selisih = 0;

      const pushSubtotalRowPdf = () => {
        body.push([
          {
            content: `${current_no_faktur}`,
            // ================== PERUBAHAN 1 ==================
            // Diubah dari 6 menjadi 7, untuk mencakup kolom HPP
            colSpan: 7,
            styles: {
              halign: "right",
              fontStyle: "bolditalic",
              fillColor: [247, 250, 252],
            },
          },
          {
            // Data ini sekarang masuk ke kolom 8 (QtyKor) - BENAR
            content: formatNumber(subtotal_qtykor),
            styles: {
              halign: "right",
              fontStyle: "bold",
              fillColor: [247, 250, 252],
            },
          },
          // Kolom 9 (Stock) - kosong
          { content: "", styles: { fillColor: [247, 250, 252] } },
          // Kolom 10 (SelQty) - kosong
          { content: "", styles: { fillColor: [247, 250, 252] } },
          {
            // Data ini sekarang masuk ke kolom 11 (T.Rp Kor) - BENAR
            content: formatRupiah(subtotal_t_rp),
            styles: {
              halign: "right",
              fontStyle: "bold",
              fillColor: [247, 250, 252],
            },
          },
          {
            // Data ini sekarang masuk ke kolom 12 (T.Rp Sel) - BENAR
            content: formatRupiah(subtotal_t_selisih),
            styles: {
              halign: "right",
              fontStyle: "bold",
              fillColor: [247, 250, 252],
            },
          },
          // ================== PERUBAHAN 2 ==================
          // Menambahkan 1 sel kosong untuk kolom ke-13 (Ket)
          { content: "", styles: { fillColor: [247, 250, 252] } },
        ]);
      };

      tabel_data.forEach((row, index) => {
        if (index === 0) {
          current_no_faktur = row.no_faktur;
        }
        if (row.no_faktur !== current_no_faktur && current_no_faktur !== null) {
          pushSubtotalRowPdf();
          subtotal_qtykor = 0;
          subtotal_t_rp = 0;
          subtotal_t_selisih = 0;
          current_no_faktur = row.no_faktur;
        }
        subtotal_qtykor += parseFloat(row.qtykor) || 0;
        subtotal_t_rp += parseFloat(row.t_rp) || 0;
        subtotal_t_selisih += parseFloat(row.t_selisih) || 0;

        body.push([
          item_counter++,
          row.no_faktur,
          row.plu,
          row.deskripsi,
          formatNumber(row.conv1),
          formatNumber(row.conv2),
          formatRupiah(row.hpp),
          formatNumber(row.qtykor),
          formatNumber(row.stock),
          formatNumber(row.selqty),
          formatRupiah(row.t_rp),
          formatRupiah(row.t_selisih),
          row.ket,
        ]);
      });

      if (current_no_faktur !== null) {
        pushSubtotalRowPdf();
      }

      body.push([
        {
          content: `GRAND TOTAL`,
          // ================== PERUBAHAN 3 ==================
          // Diubah dari 6 menjadi 7
          colSpan: 7,
          styles: {
            halign: "right",
            fontStyle: "bold",
            fillColor: [226, 232, 240],
            fontSize: 8,
          },
        },
        {
          // Kolom 8 (QtyKor)
          content: formatNumber(summary.total_qtykor),
          styles: {
            halign: "right",
            fontStyle: "bold",
            fillColor: [226, 232, 240],
            fontSize: 8,
          },
        },
        // Kolom 9 (Stock)
        { content: "", styles: { fillColor: [226, 232, 240] } },
        // Kolom 10 (SelQty)
        { content: "", styles: { fillColor: [226, 232, 240] } },
        {
          // Kolom 11 (T.Rp Kor)
          content: formatRupiah(summary.total_rp_koreksi),
          styles: {
            halign: "right",
            fontStyle: "bold",
            fillColor: [226, 232, 240],
            fontSize: 8,
          },
        },
        {
          // Kolom 12 (T.Rp Sel)
          content: formatRupiah(summary.total_rp_selisih),
          styles: {
            halign: "right",
            fontStyle: "bold",
            fillColor: [226, 232, 240],
            fontSize: 8,
          },
        },
        // ================== PERUBAHAN 4 ==================
        // Menambahkan 1 sel kosong untuk kolom ke-13 (Ket)
        { content: "", styles: { fillColor: [226, 232, 240] } },
      ]);

      doc.autoTable({
        startY: 44,
        head: head,
        body: body,
        theme: "grid",
        headStyles: {
          fillColor: [220, 220, 220],
          textColor: [0, 0, 0],
          fontSize: 6,
        },
        styles: { fontSize: 6, cellPadding: 1.5 },
        columnStyles: {
          0: { halign: "right", cellWidth: 7 },
          1: { halign: "left", cellWidth: 20 },
          2: { halign: "left", cellWidth: 15 },
          3: { halign: "left", cellWidth: 45 },
          4: { halign: "right", cellWidth: 7 },
          5: { halign: "right", cellWidth: 7 },
          6: { halign: "right", cellWidth: 18 },
          7: { halign: "right", cellWidth: 10 },
          8: { halign: "right", cellWidth: 10 },
          9: { halign: "right", cellWidth: 10 },
          10: { halign: "right", cellWidth: 20 },
          11: { halign: "right", cellWidth: 20 },
          12: { halign: "left", cellWidth: 18 },
        },
      });
      const fileName = `Koreksi_PLU_${params.tgl_mulai}_sd_${params.tgl_selesai}.pdf`;
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
