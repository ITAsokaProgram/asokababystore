document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("koreksi-supplier-table-body");
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
      const token = getCookie("admin_token"); // Mengambil token untuk otentikasi
      const response = await fetch(
        `/src/api/koreksi_stock/get_by_supplier.php?${queryString}`,
        {
          headers: {
            Accept: "application/json",
            Authorization: "Bearer " + token, // Menambahkan header sesuai instruksi get_kode
          },
        }
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

      // Bagian memproses data store yang sudah difilter dari server
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
        pageSubtitle.textContent = `Laporan Koreksi (Supplier) Periode ${params.tgl_mulai} s/d ${params.tgl_selesai} - ${storeName}`;
        if (pageTitle) {
          pageTitle.textContent = `Laporan Koreksi (Supplier) - ${storeName}`;
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
        pageTitle.textContent = "Laporan Koreksi (Supplier)";
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
                    <td colspan="12" class="text-center p-8 text-gray-500">
                        <i class="fas fa-inbox fa-lg mb-2"></i>
                        <p>Tidak ada data ditemukan untuk filter ini.</p>
                    </td>
                </tr>`;
      return;
    }
    let htmlRows = "";
    let item_counter = offset + 1;
    let current_supplier_kode = null;
    let current_tanggal = null;
    let subtotal_qtykor = 0;
    let subtotal_t_rp = 0;
    let subtotal_t_selisih = 0;
    function buildTanggalHeaderRow(tanggal) {
      return `
                <tr class="header-tanggal-row">
                    <td colspan="12" class="px-4 py-2">
                        Tanggal: <span class="font-bold"> ${tanggal} </span>
                    </td>
                </tr>
            `;
    }
    function buildSupplierHeaderRow(kode_supp, nama_supp) {
      const nama = nama_supp || "SUPPLIER LAIN/NON-AKTIF";
      return `
                <tr class="header-faktur-row"> 
                    <td colspan="12" class="px-4 py-1 pl-6">Supplier: <strong>${kode_supp} ${
        nama ? "-" : ""
      } ${nama}</strong></td>
                </tr>
            `;
    }
    function buildSubtotalSupplierRow() {
      return `
                <tr class="subtotal-row">
                    <td colspan="6" class="text-right px-4 py-2 font-bold" style="font-style: italic;">
                        Sub Total Supplier: 
                    </td>
                    <td class=" px-2 py-2">${formatNumber(subtotal_qtykor)}</td>
                    <td></td>
                    <td></td>
                    <td class=" px-2 py-2">${formatRupiah(subtotal_t_rp)}</td>
                    <td class=" px-2 py-2">${formatRupiah(
                      subtotal_t_selisih
                    )}</td>
                    <td></td>
                </tr>
            `;
    }
    function buildSubtotalTanggalRow(tanggal) {
      const subtotal = date_subtotals[tanggal] || {
        total_qtykor: 0,
        total_rp_koreksi: 0,
        total_rp_selisih: 0,
      };
      return `
                <tr class="subtotal-tanggal-row">
                    <td colspan="6" class=" px-4 py-2 text-right font-bold" style="font-style: italic;">Sub Total Tanggal:</td>
                    <td class=" px-2 py-2">${formatNumber(
                      subtotal.total_qtykor
                    )}</td>
                    <td></td>
                    <td></td>
                    <td class=" px-2 py-2">${formatRupiah(
                      subtotal.total_rp_koreksi
                    )}</td>
                    <td class=" px-2 py-2">${formatRupiah(
                      subtotal.total_rp_selisih
                    )}</td>
                    <td></td>
                </tr>
            `;
    }
    tabel_data.forEach((row, index) => {
      const qtykor = parseFloat(row.qtykor) || 0;
      const t_rp = parseFloat(row.t_rp) || 0;
      const t_selisih = parseFloat(row.t_selisih) || 0;
      if (row.tanggal !== current_tanggal) {
        if (current_supplier_kode !== null) {
          htmlRows += buildSubtotalSupplierRow();
        }
        if (current_tanggal !== null && date_subtotals) {
          htmlRows += buildSubtotalTanggalRow(current_tanggal);
        }
        current_tanggal = row.tanggal;
        current_supplier_kode = null;
        htmlRows += buildTanggalHeaderRow(current_tanggal);
      }
      if (row.kode_supp !== current_supplier_kode) {
        if (current_supplier_kode !== null) {
          htmlRows += buildSubtotalSupplierRow();
        }
        current_supplier_kode = row.kode_supp;
        htmlRows += buildSupplierHeaderRow(row.kode_supp, row.nama_supp);
        subtotal_qtykor = 0;
        subtotal_t_rp = 0;
        subtotal_t_selisih = 0;
      }
      subtotal_qtykor += qtykor;
      subtotal_t_rp += t_rp;
      subtotal_t_selisih += t_selisih;
      htmlRows += `
                <tr>
                    <td>${item_counter}</td>
                    <td>${row.plu}</td> 
                    <td class="text-left">${row.deskripsi}</td>
                    <td class="text-left">${formatNumber(row.conv1)}</td>
                    <td class="text-left">${formatNumber(row.conv2)}</td>
                    <td class="text-left">${formatRupiah(row.hpp)}</td>
                    <td class="text-left font-semibold">${formatNumber(
                      qtykor
                    )}</td>
                    <td class="text-left">${formatNumber(row.stock)}</td>
                    <td class="text-left">${formatNumber(row.selqty)}</td>
                    <td class="text-left font-semibold">${formatRupiah(
                      t_rp
                    )}</td>
                    <td class="text-left font-semibold">${formatRupiah(
                      t_selisih
                    )}</td>
                    <td class="text-left">${row.ket}</td>
                </tr>
            `;
      item_counter++;
    });
    if (current_supplier_kode !== null) {
      htmlRows += buildSubtotalSupplierRow();
    }
    if (current_tanggal !== null && date_subtotals) {
      const isLastPage =
        pagination && pagination.current_page === pagination.total_pages;
      const isExport = pagination === null;
      if (isLastPage || isExport) {
        htmlRows += buildSubtotalTanggalRow(current_tanggal);
      }
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
      const token = getCookie("admin_token"); // Mengambil token untuk otentikasi
      const response = await fetch(
        `/src/api/koreksi_stock/get_by_supplier.php?${queryString}`,
        {
          headers: {
            Accept: "application/json",
            Authorization: "Bearer " + token, // Menambahkan header sesuai instruksi get_kode
          },
        }
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
      const title = [["Laporan Koreksi (Supplier)"]];
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
      const merges = [];
      let item_counter = 1;
      let current_tanggal = null;
      let current_supplier_kode = null;
      let s_supp_qty = 0,
        s_supp_rp = 0,
        s_supp_selisih = 0;
      let s_tgl_qty = 0,
        s_tgl_rp = 0,
        s_tgl_selisih = 0;
      const rowOffset = info.length + 2;
      const pushSubtotalSupplierRow = () => {
        dataRows.push([
          "",
          "",
          "",
          "",
          "",
          "",
          "Sub Total Supplier:",
          s_supp_qty,
          "",
          "",
          s_supp_rp,
          s_supp_selisih,
          "",
        ]);
        merges.push({
          s: { r: dataRows.length + rowOffset - 1, c: 0 },
          e: { r: dataRows.length + rowOffset - 1, c: 6 },
        });
        s_supp_qty = 0;
        s_supp_rp = 0;
        s_supp_selisih = 0;
      };
      const pushSubtotalTanggalRow = () => {
        const subtotal = date_subtotals[current_tanggal] || {
          total_qtykor: 0,
          total_rp_koreksi: 0,
          total_rp_selisih: 0,
        };
        dataRows.push([
          "",
          "",
          "",
          "",
          "",
          "",
          "Sub Total Tanggal:",
          subtotal.total_qtykor,
          "",
          "",
          subtotal.total_rp_koreksi,
          subtotal.total_rp_selisih,
          "",
        ]);
        merges.push({
          s: { r: dataRows.length + rowOffset - 1, c: 0 },
          e: { r: dataRows.length + rowOffset - 1, c: 6 },
        });
      };
      const pushTanggalHeaderRow = (tanggal) => {
        dataRows.push([`Tanggal: ${tanggal}`]);
        merges.push({
          s: { r: dataRows.length + rowOffset - 1, c: 0 },
          e: { r: dataRows.length + rowOffset - 1, c: 12 },
        });
      };
      const pushSupplierHeaderRow = (kode, nama) => {
        const namaSupp = nama || "SUPPLIER LAIN/NON-AKTIF";
        dataRows.push([`Supplier: ${kode} - ${namaSupp}`]);
        merges.push({
          s: { r: dataRows.length + rowOffset - 1, c: 0 },
          e: { r: dataRows.length + rowOffset - 1, c: 12 },
        });
      };
      tabel_data.forEach((row, index) => {
        const qtykor = parseFloat(row.qtykor) || 0;
        const t_rp = parseFloat(row.t_rp) || 0;
        const t_selisih = parseFloat(row.t_selisih) || 0;
        if (row.tanggal !== current_tanggal) {
          if (current_supplier_kode !== null) pushSubtotalSupplierRow();
          if (current_tanggal !== null) pushSubtotalTanggalRow();
          pushTanggalHeaderRow(row.tanggal);
          current_tanggal = row.tanggal;
          current_supplier_kode = null;
        }
        if (row.kode_supp !== current_supplier_kode) {
          if (current_supplier_kode !== null) pushSubtotalSupplierRow();
          pushSupplierHeaderRow(row.kode_supp, row.nama_supp);
          current_supplier_kode = row.kode_supp;
          s_supp_qty = 0;
          s_supp_rp = 0;
          s_supp_selisih = 0;
        }
        s_supp_qty += qtykor;
        s_supp_rp += t_rp;
        s_supp_selisih += t_selisih;
        dataRows.push([
          item_counter++,
          row.no_faktur,
          row.plu,
          row.deskripsi,
          parseFloat(row.conv1),
          parseFloat(row.conv2),
          parseFloat(row.hpp),
          qtykor,
          parseFloat(row.stock),
          parseFloat(row.selqty),
          t_rp,
          t_selisih,
          row.ket,
        ]);
      });
      if (current_supplier_kode !== null) pushSubtotalSupplierRow();
      if (current_tanggal !== null) pushSubtotalTanggalRow();
      dataRows.push([]);
      dataRows.push([
        "",
        "",
        "",
        "",
        "",
        "",
        "GRAND TOTAL:",
        summary.total_qtykor,
        "",
        "",
        summary.total_rp_koreksi,
        summary.total_rp_selisih,
        "",
      ]);
      merges.push({
        s: { r: dataRows.length + rowOffset - 1, c: 0 },
        e: { r: dataRows.length + rowOffset - 1, c: 6 },
      });
      const ws = XLSX.utils.aoa_to_sheet(title);
      XLSX.utils.sheet_add_aoa(ws, info, { origin: "A2" });
      const headerOrigin = "A" + (info.length + 2);
      XLSX.utils.sheet_add_aoa(ws, [headers], { origin: headerOrigin });
      XLSX.utils.sheet_add_aoa(ws, dataRows, {
        origin: "A" + (info.length + 3),
      });
      ws["!merges"] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 12 } }, ...merges];
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

      if (ws["B5"]) {
        ws["B5"].s = { numFmt: numFormatDec };
      }
      headers.forEach((_, C) => {
        const cell = ws[XLSX.utils.encode_cell({ r: info.length + 1, c: C })];
        if (cell) cell.s = headerStyle;
      });
      const dataRowStartIndex = info.length + 2;
      dataRows.forEach((row, R_idx) => {
        const R = R_idx + dataRowStartIndex;
        if (row.length === 0) return;
        const label = row[0] || row[6];
        if (typeof label === "string") {
          if (label.startsWith("Tanggal:")) {
            ws[XLSX.utils.encode_cell({ r: R, c: 0 })].s = {
              font: { bold: true, sz: 11, color: { rgb: "2C5282" } },
              fill: { fgColor: { rgb: "EBF8FF" } },
            };
          } else if (label.startsWith("Supplier:")) {
            ws[XLSX.utils.encode_cell({ r: R, c: 0 })].s = {
              font: { bold: true },
              fill: { fgColor: { rgb: "F7FAFC" } },
            };
          } else if (label.startsWith("Sub Total Supplier:")) {
            const style = {
              font: { bold: true, italic: true },
              fill: { fgColor: { rgb: "FEFDE8" } },
              alignment: { horizontal: "right" },
            };
            ws[XLSX.utils.encode_cell({ r: R, c: 6 })].s = style;
            ["H", "K", "L"].forEach((col) => {
              const cell = ws[col + (R + 1)];
              if (cell)
                cell.s = {
                  numFmt: col === "H" ? numFormatDec : numFormat,
                  ...style,
                  alignment: "right",
                };
            });
          } else if (label.startsWith("Sub Total Tanggal:")) {
            const style = {
              font: {
                bold: true,
                italic: true,
                sz: 11,
                color: { rgb: "2C5282" },
              },
              fill: { fgColor: { rgb: "EBF8FF" } },
              alignment: { horizontal: "right" },
            };
            ws[XLSX.utils.encode_cell({ r: R, c: 6 })].s = style;
            ["H", "K", "L"].forEach((col) => {
              const cell = ws[col + (R + 1)];
              if (cell)
                cell.s = {
                  numFmt: col === "H" ? numFormatDec : numFormat,
                  ...style,
                  alignment: "right",
                };
            });
          } else if (label.startsWith("GRAND TOTAL:")) {
            const style = {
              font: { bold: true, sz: 12 },
              fill: { fgColor: { rgb: "E2E8F0" } },
              alignment: { horizontal: "right" },
            };
            ws[XLSX.utils.encode_cell({ r: R, c: 6 })].s = style;
            ["H", "K", "L"].forEach((col) => {
              const cell = ws[col + (R + 1)];
              if (cell)
                cell.s = {
                  numFmt: col === "H" ? numFormatDec : numFormat,
                  ...style,
                  alignment: "right",
                };
            });
          } else if (row[0] && typeof row[0] === "number") {
            ["E", "F", "H", "I", "J"].forEach((col) => {
              const cell = ws[col + (R + 1)];
              if (cell) cell.s = { numFmt: numFormatDec };
            });
            ["G", "K", "L"].forEach((col) => {
              const cell = ws[col + (R + 1)];
              if (cell) cell.s = { numFmt: numFormat };
            });
          }
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
      XLSX.utils.book_append_sheet(wb, ws, "Koreksi Supplier");
      const fileName = `Koreksi_Supplier_${params.tgl_mulai}_sd_${params.tgl_selesai}.xlsx`;
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
      doc.text("Laporan Koreksi (Supplier)", 14, 22);
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
      let current_tanggal = null;
      let current_supplier_kode = null;
      let s_supp_qty = 0,
        s_supp_rp = 0,
        s_supp_selisih = 0;
      const headerTanggalStyles = {
        fontStyle: "bold",
        fillColor: [235, 248, 255],
        textColor: [44, 82, 130],
        fontSize: 6,
        halign: "left",
      };
      const headerSupplierStyles = {
        fontStyle: "bold",
        fillColor: [247, 250, 252],
        textColor: [74, 85, 104],
        fontSize: 5,
        halign: "left",
      };
      const subtotalSupplierStyles = {
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
      const subtotalSupplierValuesStyles = {
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
      const pushSubtotalSupplierRowPdf = () => {
        body.push([
          {
            content: "Sub Total Supplier:",
            colSpan: 7,
            styles: subtotalSupplierStyles,
          },
          {
            content: formatNumber(s_supp_qty),
            styles: subtotalSupplierValuesStyles,
          },
          { content: "", styles: subtotalSupplierValuesStyles },
          { content: "", styles: subtotalSupplierValuesStyles },
          {
            content: formatRupiah(s_supp_rp),
            styles: subtotalSupplierValuesStyles,
          },
          {
            content: formatRupiah(s_supp_selisih),
            styles: subtotalSupplierValuesStyles,
          },
          { content: "", styles: subtotalSupplierValuesStyles },
        ]);
        s_supp_qty = 0;
        s_supp_rp = 0;
        s_supp_selisih = 0;
      };
      const pushSubtotalTanggalRowPdf = (tanggal) => {
        const subtotal = date_subtotals[tanggal] || {
          total_qtykor: 0,
          total_rp_koreksi: 0,
          total_rp_selisih: 0,
        };
        body.push([
          {
            content: "Sub Total Tanggal:",
            colSpan: 7,
            styles: subtotalTanggalStyles,
          },
          {
            content: formatNumber(subtotal.total_qtykor),
            styles: subtotalTanggalValuesStyles,
          },
          { content: "", styles: subtotalTanggalValuesStyles },
          { content: "", styles: subtotalTanggalValuesStyles },
          {
            content: formatRupiah(subtotal.total_rp_koreksi),
            styles: subtotalTanggalValuesStyles,
          },
          {
            content: formatRupiah(subtotal.total_rp_selisih),
            styles: subtotalTanggalValuesStyles,
          },
          { content: "", styles: subtotalTanggalValuesStyles },
        ]);
      };
      const pushTanggalHeaderRowPdf = (tanggal) => {
        body.push([
          {
            content: `Tanggal: ${tanggal}`,
            colSpan: 13,
            styles: headerTanggalStyles,
          },
        ]);
      };
      const pushSupplierHeaderRowPdf = (kode, nama) => {
        const namaSupp = nama || "SUPPLIER LAIN/NON-AKTIF";
        body.push([
          {
            content: `Supplier: ${kode} - ${namaSupp}`,
            colSpan: 13,
            styles: headerSupplierStyles,
          },
        ]);
      };
      tabel_data.forEach((row, index) => {
        const qtykor = parseFloat(row.qtykor) || 0;
        const t_rp = parseFloat(row.t_rp) || 0;
        const t_selisih = parseFloat(row.t_selisih) || 0;
        if (row.tanggal !== current_tanggal) {
          if (current_supplier_kode !== null) pushSubtotalSupplierRowPdf();
          if (current_tanggal !== null)
            pushSubtotalTanggalRowPdf(current_tanggal);
          pushTanggalHeaderRowPdf(row.tanggal);
          current_tanggal = row.tanggal;
          current_supplier_kode = null;
        }
        if (row.kode_supp !== current_supplier_kode) {
          if (current_supplier_kode !== null) pushSubtotalSupplierRowPdf();
          pushSupplierHeaderRowPdf(row.kode_supp, row.nama_supp);
          current_supplier_kode = row.kode_supp;
          s_supp_qty = 0;
          s_supp_rp = 0;
          s_supp_selisih = 0;
        }
        s_supp_qty += qtykor;
        s_supp_rp += t_rp;
        s_supp_selisih += t_selisih;
        body.push([
          item_counter++,
          row.no_faktur,
          row.plu,
          row.deskripsi,
          formatNumber(row.conv1),
          formatNumber(row.conv2),
          formatRupiah(row.hpp),
          formatNumber(qtykor),
          formatNumber(row.stock),
          formatNumber(row.selqty),
          formatRupiah(t_rp),
          formatRupiah(t_selisih),
          row.ket,
        ]);
      });
      if (current_supplier_kode !== null) pushSubtotalSupplierRowPdf();
      if (current_tanggal !== null) pushSubtotalTanggalRowPdf(current_tanggal);
      body.push([
        { content: "GRAND TOTAL", colSpan: 7, styles: grandTotalStyles },
        {
          content: formatNumber(summary.total_qtykor),
          styles: grandTotalValuesStyles,
        },
        { content: "", styles: grandTotalValuesStyles },
        { content: "", styles: grandTotalValuesStyles },
        {
          content: formatRupiah(summary.total_rp_koreksi),
          styles: grandTotalValuesStyles,
        },
        {
          content: formatRupiah(summary.total_rp_selisih),
          styles: grandTotalValuesStyles,
        },
        { content: "", styles: grandTotalValuesStyles },
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
          10: { halign: "left" },
          11: { halign: "left" },
          12: { halign: "left" },
        },
      });
      const fileName = `Koreksi_Supplier_${params.tgl_mulai}_sd_${params.tgl_selesai}.pdf`;
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
