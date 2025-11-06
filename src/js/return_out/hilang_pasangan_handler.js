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
      return "0"; // Ganti dari "Rp 0" agar di tabel lebih rapi
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
        `/src/api/return_out/get_hilang_pasangan.php?${queryString}`
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
        pageSubtitle.textContent = `Laporan Return Out (Hilang Pasangan) Periode ${params.tgl_mulai} s/d ${params.tgl_selesai} - ${storeName}`;
        if (pageTitle) {
          pageTitle.textContent = `Laporan Return Out (Hilang Pasangan) - ${storeName}`;
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
        pageTitle.textContent = "Laporan Return Out (Hilang Pasangan)";
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
    summaryQty.textContent = formatNumber(summary.total_qty);
    summaryNetto.textContent = formatRupiah(summary.total_netto);
    summaryPPN.textContent = formatRupiah(summary.total_ppn);
    summaryTotal.textContent = formatRupiah(summary.total_grand);
  }

  function renderTable(tabel_data, offset, summary) {
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

    let current_tanggal = null;
    let current_faktur = null;

    let subtotal_faktur_qty = 0,
      subtotal_faktur_netto = 0,
      subtotal_faktur_ppn = 0,
      subtotal_faktur_ppnbm = 0,
      subtotal_faktur_total = 0;
    let subtotal_tanggal_qty = 0,
      subtotal_tanggal_netto = 0,
      subtotal_tanggal_ppn = 0,
      subtotal_tanggal_ppnbm = 0,
      subtotal_tanggal_total = 0;

    // --- Helper functions for building rows ---

    function buildTanggalHeaderRow(tanggal) {
      return `
                        <tr class="header-tanggal-row">
                            <td colspan="11" class="px-4 py-2">
                                Tanggal: ${tanggal}
                            </td>
                        </tr>
                    `;
    }

    function buildFakturHeaderRow(faktur, kodesupp, namasupp, nama_inisial) {
      const inisial = nama_inisial ? `(${nama_inisial})` : "";
      return `
                        <tr class="header-faktur-row">
                            <td colspan="3" class="px-4 py-1 pl-6">No Faktur: <strong>${faktur}</strong></td>
                            <td colspan="8" class="px-4 py-1">Supplier: <strong>${kodesupp} - ${
        namasupp || ""
      } ${inisial}</strong></td>
                        </tr>
                    `;
    }

    function buildSubtotalFakturRow() {
      return `
                        <tr class="subtotal-row">
                            <td colspan="6" class="text-right px-4 py-2" style="font-style: italic;"></td>
                            <td class="text-right px-2 py-2">${formatNumber(
                              subtotal_faktur_qty
                            )}</td>
                            <td class="text-right px-2 py-2">${formatRupiah(
                              subtotal_faktur_netto
                            )}</td>
                            <td class="text-right px-2 py-2">${formatRupiah(
                              subtotal_faktur_ppn
                            )}</td>
                            <td class="text-right px-2 py-2">${formatRupiah(
                              subtotal_faktur_ppnbm
                            )}</td>
                            <td class="text-right px-2 py-2">${formatRupiah(
                              subtotal_faktur_total
                            )}</td>
                        </tr>
                    `;
    }

    function buildSubtotalTanggalRow() {
      return `
                        <tr class="subtotal-tanggal-row">
                            <td colspan="6" class="text-right px-4 py-2" style="font-style: italic;">Sub Total Tanggal:</td>
                            <td class="text-right px-2 py-2">${formatNumber(
                              subtotal_tanggal_qty
                            )}</td>
                            <td class="text-right px-2 py-2">${formatRupiah(
                              subtotal_tanggal_netto
                            )}</td>
                            <td class="text-right px-2 py-2">${formatRupiah(
                              subtotal_tanggal_ppn
                            )}</td>
                            <td class="text-right px-2 py-2">${formatRupiah(
                              subtotal_tanggal_ppnbm
                            )}</td>
                            <td class="text-right px-2 py-2">${formatRupiah(
                              subtotal_tanggal_total
                            )}</td>
                        </tr>
                    `;
    }

    // --- Loop through data ---

    tabel_data.forEach((row, index) => {
      const qty = parseFloat(row.qty) || 0;
      const netto = parseFloat(row.netto) || 0;
      const ppn = parseFloat(row.ppn) || 0;
      const ppnbm = parseFloat(row.ppnbm) || 0;
      const total = parseFloat(row.total) || 0;

      // Cek ganti Tanggal
      if (row.tanggal !== current_tanggal) {
        if (current_tanggal !== null) {
          // Tampilkan subtotal faktur terakhir (jika ada)
          if (current_faktur !== null) {
            htmlRows += buildSubtotalFakturRow();
            subtotal_faktur_qty = 0;
            subtotal_faktur_netto = 0;
            subtotal_faktur_ppn = 0;
            subtotal_faktur_ppnbm = 0;
            subtotal_faktur_total = 0;
          }
          // Tampilkan subtotal tanggal
          htmlRows += buildSubtotalTanggalRow();
          subtotal_tanggal_qty = 0;
          subtotal_tanggal_netto = 0;
          subtotal_tanggal_ppn = 0;
          subtotal_tanggal_ppnbm = 0;
          subtotal_tanggal_total = 0;
        }
        current_tanggal = row.tanggal;
        current_faktur = null; // Reset faktur
        htmlRows += buildTanggalHeaderRow(current_tanggal);
      }

      // Cek ganti Faktur
      if (row.faktur !== current_faktur) {
        if (current_faktur !== null) {
          // Tampilkan subtotal faktur sebelumnya
          htmlRows += buildSubtotalFakturRow();
          subtotal_faktur_qty = 0;
          subtotal_faktur_netto = 0;
          subtotal_faktur_ppn = 0;
          subtotal_faktur_ppnbm = 0;
          subtotal_faktur_total = 0;
        }
        current_faktur = row.faktur;
        htmlRows += buildFakturHeaderRow(
          row.faktur,
          row.kodesupp,
          row.namasupp,
          row.nama_inisial
        );
      }

      // Akumulasi total
      subtotal_faktur_qty += qty;
      subtotal_faktur_netto += netto;
      subtotal_faktur_ppn += ppn;
      subtotal_faktur_ppnbm += ppnbm;
      subtotal_faktur_total += total;

      subtotal_tanggal_qty += qty;
      subtotal_tanggal_netto += netto;
      subtotal_tanggal_ppn += ppn;
      subtotal_tanggal_ppnbm += ppnbm;
      subtotal_tanggal_total += total;

      // Render detail row
      htmlRows += `
                        <tr>
                            <td>${item_counter}</td>
                            <td>${row.plu}</td>
                            <td class="text-left">${row.descp}</td>
                            <td class="text-left">${row.satuan}</td>
                            <td class="text-right">${formatNumber(
                              row.conv1
                            )}</td>
                            <td class="text-right">${formatNumber(
                              row.conv2
                            )}</td>
                            <td class="text-right font-semibold">${formatNumber(
                              qty
                            )}</td>
                            <td class="text-right font-semibold">${formatRupiah(
                              netto
                            )}</td>
                            <td class="text-right font-semibold">${formatRupiah(
                              ppn
                            )}</td>
                            <td class="text-right font-semibold">${formatRupiah(
                              ppnbm
                            )}</td>
                            <td class="text-right font-semibold">${formatRupiah(
                              total
                            )}</td>
                        </tr>
                    `;
      item_counter++;
    });

    // Tampilkan subtotal terakhir setelah loop
    if (current_faktur !== null) {
      htmlRows += buildSubtotalFakturRow();
    }
    if (current_tanggal !== null) {
      htmlRows += buildSubtotalTanggalRow();
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
        `/src/api/return_out/get_hilang_pasangan.php?${queryString}`
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
      const title = [["Laporan Return Out (Hilang Pasangan)"]];
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
        ["Total PPNBM", parseFloat(summary.total_ppnbm) || 0],
        [],
      ];

      const headers = [
        "No",
        "Tanggal",
        "No Faktur",
        "Kd Supp",
        "Nama Supp",
        "PLU",
        "Deskripsi",
        "Sat",
        "Conv1",
        "Conv2",
        "Qty",
        "Netto",
        "PPN",
        "PPNBM",
        "Total",
      ];

      const dataRows = [];
      let item_counter = 1;
      let current_tanggal = null,
        current_faktur = null;

      let s_fak_qty = 0,
        s_fak_netto = 0,
        s_fak_ppn = 0,
        s_fak_ppnbm = 0,
        s_fak_total = 0;
      let s_tgl_qty = 0,
        s_tgl_netto = 0,
        s_tgl_ppn = 0,
        s_tgl_ppnbm = 0,
        s_tgl_total = 0;

      const pushSubtotalFakturRow = () => {
        dataRows.push([
          "",
          "",
          "",
          "",
          "",
          "",
          "",
          "",
          "",
          "",
          s_fak_qty,
          s_fak_netto,
          s_fak_ppn,
          s_fak_ppnbm,
          s_fak_total,
        ]);
      };
      const pushSubtotalTanggalRow = () => {
        dataRows.push([
          "",
          "",
          "",
          "",
          "",
          "",
          "",
          "",
          "",
          "Sub Total Tanggal:",
          s_tgl_qty,
          s_tgl_netto,
          s_tgl_ppn,
          s_tgl_ppnbm,
          s_tgl_total,
        ]);
      };

      tabel_data.forEach((row, index) => {
        const qty = parseFloat(row.qty) || 0;
        const netto = parseFloat(row.netto) || 0;
        const ppn = parseFloat(row.ppn) || 0;
        const ppnbm = parseFloat(row.ppnbm) || 0;
        const total = parseFloat(row.total) || 0;

        if (row.tanggal !== current_tanggal) {
          if (current_faktur !== null) {
            pushSubtotalFakturRow();
            s_fak_qty = 0;
            s_fak_netto = 0;
            s_fak_ppn = 0;
            s_fak_ppnbm = 0;
            s_fak_total = 0;
          }
          if (current_tanggal !== null) {
            pushSubtotalTanggalRow();
            s_tgl_qty = 0;
            s_tgl_netto = 0;
            s_tgl_ppn = 0;
            s_tgl_ppnbm = 0;
            s_tgl_total = 0;
          }
          current_tanggal = row.tanggal;
          current_faktur = null;
        }

        if (row.faktur !== current_faktur) {
          if (current_faktur !== null) {
            pushSubtotalFakturRow();
            s_fak_qty = 0;
            s_fak_netto = 0;
            s_fak_ppn = 0;
            s_fak_ppnbm = 0;
            s_fak_total = 0;
          }
          current_faktur = row.faktur;
        }

        s_fak_qty += qty;
        s_fak_netto += netto;
        s_fak_ppn += ppn;
        s_fak_ppnbm += ppnbm;
        s_fak_total += total;
        s_tgl_qty += qty;
        s_tgl_netto += netto;
        s_tgl_ppn += ppn;
        s_tgl_ppnbm += ppnbm;
        s_tgl_total += total;

        dataRows.push([
          item_counter++,
          row.tanggal,
          row.faktur,
          row.kodesupp,
          `${row.namasupp || ""} ${
            row.nama_inisial ? "(" + row.nama_inisial + ")" : ""
          }`,
          row.plu,
          row.descp,
          row.satuan,
          parseFloat(row.conv1),
          parseFloat(row.conv2),
          qty,
          netto,
          ppn,
          ppnbm,
          total,
        ]);
      });

      if (current_faktur !== null) pushSubtotalFakturRow();
      if (current_tanggal !== null) pushSubtotalTanggalRow();

      dataRows.push([]);

      const ws = XLSX.utils.aoa_to_sheet(title);
      XLSX.utils.sheet_add_aoa(ws, info, { origin: "A2" });
      const headerOrigin = "A" + (info.length + 2);
      XLSX.utils.sheet_add_aoa(ws, [headers], { origin: headerOrigin });
      XLSX.utils.sheet_add_aoa(ws, dataRows, {
        origin: "A" + (info.length + 3),
      });

      ws["!merges"] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 14 } }];
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

      ["B5", "B6", "B7", "B8", "B9"].forEach((cell) => {
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
        if (row.length === 0) return;

        const label = row[9];
        if (
          typeof label === "string" &&
          (label.startsWith("Sub Total") || label === "GRAND TOTAL")
        ) {
          const style = {
            font: { bold: true, italic: label.startsWith("Sub Total") },
          };
          if (label === "GRAND TOTAL") style.font.sz = 12;
          ws[XLSX.utils.encode_cell({ r: R, c: 9 })].s = style;

          ["K", "L", "M", "N", "O"].forEach((col) => {
            const cell = ws[col + (R + 1)];
            if (cell) cell.s = { numFmt: numFormat, font: style.font };
          });
        } else if (row[0]) {
          // Detail row
          ["I", "J", "K"].forEach((col) => {
            // conv1, conv2, qty
            const cell = ws[col + (R + 1)];
            if (cell) cell.s = { numFmt: numFormatDec };
          });
          ["L", "M", "N", "O"].forEach((col) => {
            // netto, ppn, ppnbm, total
            const cell = ws[col + (R + 1)];
            if (cell) cell.s = { numFmt: numFormat };
          });
        }
      });

      ws["!cols"] = [
        { wch: 5 },
        { wch: 10 },
        { wch: 18 },
        { wch: 10 },
        { wch: 25 },
        { wch: 12 },
        { wch: 35 },
        { wch: 5 },
        { wch: 8 },
        { wch: 8 },
        { wch: 10 },
        { wch: 15 },
        { wch: 15 },
        { wch: 15 },
        { wch: 17 },
      ];

      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, "Return Out Hilang");
      const fileName = `Return_Out_Hilang_Pasangan_${params.tgl_mulai}_sd_${params.tgl_selesai}.xlsx`;
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
      doc.text("Laporan Return Out (Hilang Pasangan)", 14, 22);
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
          "Tgl",
          "Faktur",
          "Kd Supp",
          "Nama Supp",
          "PLU",
          "Deskripsi",
          "Sat",
          "C1",
          "C2",
          "Qty",
          "Netto",
          "PPN",
          "PPNBM",
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
        s_fak_ppnbm = 0,
        s_fak_total = 0;
      let s_tgl_qty = 0,
        s_tgl_netto = 0,
        s_tgl_ppn = 0,
        s_tgl_ppnbm = 0,
        s_tgl_total = 0;

      const subtotalFakturStyles = {
        halign: "right",
        fontStyle: "bold",
        fillColor: [247, 250, 252],
        fontSize: 5,
      };
      const subtotalTanggalStyles = {
        halign: "right",
        fontStyle: "bold",
        fillColor: [235, 248, 255],
        fontSize: 6,
      };
      const grandTotalStyles = {
        halign: "right",
        fontStyle: "bold",
        fillColor: [226, 232, 240],
        fontSize: 6,
      };

      const pushSubtotalFakturRowPdf = () => {
        body.push([
          {
            content: "",
            colSpan: 10,
            styles: { ...subtotalFakturStyles, fontStyle: "bolditalic" },
          },
          { content: formatNumber(s_fak_qty), styles: subtotalFakturStyles },
          { content: formatRupiah(s_fak_netto), styles: subtotalFakturStyles },
          { content: formatRupiah(s_fak_ppn), styles: subtotalFakturStyles },
          { content: formatRupiah(s_fak_ppnbm), styles: subtotalFakturStyles },
          { content: formatRupiah(s_fak_total), styles: subtotalFakturStyles },
        ]);
      };
      const pushSubtotalTanggalRowPdf = () => {
        body.push([
          {
            content: "Sub Total Tanggal:",
            colSpan: 10,
            styles: { ...subtotalTanggalStyles, fontStyle: "bolditalic" },
          },
          { content: formatNumber(s_tgl_qty), styles: subtotalTanggalStyles },
          { content: formatRupiah(s_tgl_netto), styles: subtotalTanggalStyles },
          { content: formatRupiah(s_tgl_ppn), styles: subtotalTanggalStyles },
          { content: formatRupiah(s_tgl_ppnbm), styles: subtotalTanggalStyles },
          { content: formatRupiah(s_tgl_total), styles: subtotalTanggalStyles },
        ]);
      };

      tabel_data.forEach((row, index) => {
        const qty = parseFloat(row.qty) || 0;
        const netto = parseFloat(row.netto) || 0;
        const ppn = parseFloat(row.ppn) || 0;
        const ppnbm = parseFloat(row.ppnbm) || 0;
        const total = parseFloat(row.total) || 0;

        if (row.tanggal !== current_tanggal) {
          if (current_faktur !== null) {
            pushSubtotalFakturRowPdf();
            s_fak_qty = 0;
            s_fak_netto = 0;
            s_fak_ppn = 0;
            s_fak_ppnbm = 0;
            s_fak_total = 0;
          }
          if (current_tanggal !== null) {
            pushSubtotalTanggalRowPdf();
            s_tgl_qty = 0;
            s_tgl_netto = 0;
            s_tgl_ppn = 0;
            s_tgl_ppnbm = 0;
            s_tgl_total = 0;
          }
          current_tanggal = row.tanggal;
          current_faktur = null;
        }

        if (row.faktur !== current_faktur) {
          if (current_faktur !== null) {
            pushSubtotalFakturRowPdf();
            s_fak_qty = 0;
            s_fak_netto = 0;
            s_fak_ppn = 0;
            s_fak_ppnbm = 0;
            s_fak_total = 0;
          }
          current_faktur = row.faktur;
        }

        s_fak_qty += qty;
        s_fak_netto += netto;
        s_fak_ppn += ppn;
        s_fak_ppnbm += ppnbm;
        s_fak_total += total;
        s_tgl_qty += qty;
        s_tgl_netto += netto;
        s_tgl_ppn += ppn;
        s_tgl_ppnbm += ppnbm;
        s_tgl_total += total;

        body.push([
          item_counter++,
          row.tanggal,
          row.faktur,
          row.kodesupp,
          row.namasupp,
          row.plu,
          row.descp,
          row.satuan,
          formatNumber(row.conv1),
          formatNumber(row.conv2),
          formatNumber(qty),
          formatRupiah(netto),
          formatRupiah(ppn),
          formatRupiah(ppnbm),
          formatRupiah(total),
        ]);
      });

      if (current_faktur !== null) pushSubtotalFakturRowPdf();
      if (current_tanggal !== null) pushSubtotalTanggalRowPdf();

      body.push([
        { content: "GRAND TOTAL", colSpan: 10, styles: grandTotalStyles },
        { content: formatNumber(summary.total_qty), styles: grandTotalStyles },
        {
          content: formatRupiah(summary.total_netto),
          styles: grandTotalStyles,
        },
        { content: formatRupiah(summary.total_ppn), styles: grandTotalStyles },
        {
          content: formatRupiah(summary.total_ppnbm),
          styles: grandTotalStyles,
        },
        {
          content: formatRupiah(summary.total_grand),
          styles: grandTotalStyles,
        },
      ]);

      doc.autoTable({
        startY: 44,
        head: head,
        body: body,
        theme: "grid",
        headStyles: {
          fillColor: [220, 220, 220],
          textColor: [0, 0, 0],
          fontSize: 5,
        },
        styles: { fontSize: 5, cellPadding: 1.5 },
        columnStyles: {
          0: { halign: "right", cellWidth: 7 }, // No
          1: { halign: "left", cellWidth: 10 }, // Tgl
          2: { halign: "left", cellWidth: 18 }, // Faktur
          3: { halign: "left", cellWidth: 10 }, // Kd Supp
          4: { halign: "left", cellWidth: 20 }, // Nama Supp
          5: { halign: "left", cellWidth: 12 }, // PLU
          6: { halign: "left", cellWidth: 35 }, // Deskripsi
          7: { halign: "left", cellWidth: 5 }, // Sat
          8: { halign: "right", cellWidth: 7 }, // C1
          9: { halign: "right", cellWidth: 7 }, // C2
          10: { halign: "right", cellWidth: 10 }, // Qty
          11: { halign: "right", cellWidth: 18 }, // Netto
          12: { halign: "right", cellWidth: 18 }, // PPN
          13: { halign: "right", cellWidth: 18 }, // PPNBM
          14: { halign: "right", cellWidth: 20 }, // Total
        },
      });
      const fileName = `Return_Out_Hilang_Pasangan_${params.tgl_mulai}_sd_${params.tgl_selesai}.pdf`;
      doc.save(fileName);
    } catch (error) {
      console.error("Error exporting to PDF:", error);
      Swal.fire("Export Gagal", "Terjadi kesalahan: " + error.message, "error");
    }
  }

  // --- Event Listeners ---
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

  // Initial load
  loadData();
});
