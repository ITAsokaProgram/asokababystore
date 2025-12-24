document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("supplier-receipt-table-body");
  const filterForm = document.getElementById("filter-form");
  const filterSubmitButton = document.getElementById("filter-submit-button");
  const filterSelectStore = document.getElementById("kd_store");
  const summaryNetto = document.getElementById("summary-netto");
  const summaryPpn = document.getElementById("summary-ppn");
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
      const token = getCookie("admin_token");
      const response = await fetch(
        `/src/api/penerimaan_receipt/get_by_supplier.php?${queryString}`,
        {
          headers: {
            Accept: "application/json",
            Authorization: "Bearer " + token,
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

      // --- BAGIAN PENGGANTI POPULATE CABANG (Sesuai Logika get_kode) ---
      if (data.stores) {
        const select = filterSelectStore;
        select.innerHTML = "";

        if (data.stores.length > 0) {
          const defaultOption = new Option("Pilih Cabang", "none");
          select.add(defaultOption);

          const allOption = new Option("SEMUA CABANG", "all"); // value 'all' sesuai logic PHP default
          select.add(allOption);

          data.stores.forEach((store) => {
            const option = new Option(store.nm_alias, store.kd_store);
            select.add(option);
          });

          // Mengembalikan value select sesuai dengan params di URL agar tidak kosong
          if (params.kd_store) {
            select.value = params.kd_store;
          }
        } else {
          select.innerHTML =
            '<option value="none">Gagal memuat data cabang</option>';
        }
      }
      // ----------------------------------------------------------------

      if (pageSubtitle) {
        let storeName = "Seluruh Cabang";
        if (
          filterSelectStore.options.length > 0 &&
          filterSelectStore.selectedIndex > -1
        ) {
          storeName =
            filterSelectStore.options[filterSelectStore.selectedIndex].text;
        }
        pageSubtitle.textContent = `Laporan Receipt by Supplier Periode ${params.tgl_mulai} s/d ${params.tgl_selesai} - ${storeName}`;
        if (pageTitle) {
          pageTitle.textContent = `Receipt by Supplier - ${storeName}`;
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
        pageTitle.textContent = "Receipt by Supplier";
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
          if (summaryNetto) summaryNetto.textContent = "-";
          if (summaryPpn) summaryPpn.textContent = "-";
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
    summaryNetto.textContent = formatRupiah(summary.total_netto);
    summaryPpn.textContent = formatRupiah(summary.total_ppn);
    summaryTotal.textContent = formatRupiah(summary.total_total);
  }
  function renderTable(tabel_data, offset, summary) {
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
    let item_counter = offset + 1;
    tabel_data.forEach((row) => {
      htmlRows += `
                <tr>
                    <td>${item_counter}</td>
                    <td class="font-medium text-gray-700">${
                      row.no_lpb || "-"
                    }</td>
                    <td>${row.kodesupp}</td>
                    <td class="text-left">${row.namasupp || "-"}</td>
                    <td class=" font-semibold">${formatRupiah(row.netto)}</td>
                    <td class="">${formatRupiah(row.ppn)}</td>
                    <td class=" font-bold">${formatRupiah(row.total)}</td>
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
      const token = getCookie("admin_token");
      const response = await fetch(
        `/src/api/penerimaan_receipt/get_by_supplier.php?${queryString}`,
        {
          headers: {
            Accept: "application/json",
            Authorization: "Bearer " + token,
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
      const { tabel_data, summary } = data;
      const params = getUrlParams();
      const title = [["Laporan Receipt by Supplier"]];
      const info = [
        ["Periode", `${params.tgl_mulai} s/d ${params.tgl_selesai}`],
        [
          "Cabang",
          filterSelectStore.options[filterSelectStore.selectedIndex].text,
        ],
        [],
        ["Total Netto", parseFloat(summary.total_netto) || 0],
        ["Total PPN", parseFloat(summary.total_ppn) || 0],
        ["Total Keseluruhan", parseFloat(summary.total_total) || 0],
        [],
      ];
      const headers = [
        "No",
        "No LPB",
        "Kode Supp",
        "Nama Supplier",
        "Netto",
        "PPN",
        "Total",
      ];
      const dataRows = [];
      let item_counter = 1;
      tabel_data.forEach((row) => {
        dataRows.push([
          item_counter++,
          row.no_lpb,
          row.kodesupp,
          row.namasupp,
          parseFloat(row.netto) || 0,
          parseFloat(row.ppn) || 0,
          parseFloat(row.total) || 0,
        ]);
      });
      dataRows.push([]);
      dataRows.push([
        "",
        "",
        "",
        "GRAND TOTAL",
        summary.total_netto,
        summary.total_ppn,
        summary.total_total,
      ]);
      const ws = XLSX.utils.aoa_to_sheet(title);
      XLSX.utils.sheet_add_aoa(ws, info, { origin: "A2" });
      const headerOrigin = "A" + (info.length + 2);
      XLSX.utils.sheet_add_aoa(ws, [headers], { origin: headerOrigin });
      XLSX.utils.sheet_add_aoa(ws, dataRows, {
        origin: "A" + (info.length + 3),
      });
      ws["!merges"] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 6 } }];
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
        const cell = ws[XLSX.utils.encode_cell({ r: info.length + 1, c: C })];
        if (cell) cell.s = headerStyle;
      });
      const dataRowStartIndex = info.length + 2;
      dataRows.forEach((row, R_idx) => {
        const R = R_idx + dataRowStartIndex;
        if (row[3] === "GRAND TOTAL") {
          ws[XLSX.utils.encode_cell({ r: R, c: 3 })].s = {
            font: { bold: true, sz: 12 },
            alignment: { horizontal: "right" },
          };
          ["E", "F", "G"].forEach((col) => {
            const cell = ws[col + (R + 1)];
            if (cell) {
              cell.t = "n";
              cell.s = { numFmt: numFormat, font: { bold: true, sz: 12 } };
            }
          });
        } else if (row.length > 0) {
          ["E", "F", "G"].forEach((col) => {
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
        { wch: 15 },
        { wch: 12 },
        { wch: 35 },
        { wch: 17 },
        { wch: 15 },
        { wch: 17 },
      ];
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, "Receipt by Supplier");
      const fileName = `Receipt_by_Supplier_${params.tgl_mulai}_sd_${params.tgl_selesai}.xlsx`;
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
      doc.text("Laporan Receipt by Supplier", 14, 22);
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
      doc.text(`Total Netto: ${formatRupiah(summary.total_netto)}`, 280, 22, {
        align: "right",
      });
      doc.text(`Total PPN: ${formatRupiah(summary.total_ppn)}`, 280, 30, {
        align: "right",
      });
      doc.text(
        `Total Keseluruhan: ${formatRupiah(summary.total_total)}`,
        280,
        36,
        { align: "right" }
      );
      const head = [
        ["No", "No LPB", "Kode Supp", "Nama Supplier", "Netto", "PPN", "Total"],
      ];
      const body = [];
      let item_counter = 1;
      tabel_data.forEach((row) => {
        body.push([
          item_counter++,
          row.no_lpb,
          row.kodesupp,
          row.namasupp,
          formatRupiah(row.netto),
          formatRupiah(row.ppn),
          formatRupiah(row.total),
        ]);
      });
      body.push([
        {
          content: `GRAND TOTAL`,
          colSpan: 4,
          styles: {
            halign: "right",
            fontStyle: "bold",
            fillColor: [226, 232, 240],
            fontSize: 8,
          },
        },
        {
          content: formatRupiah(summary.total_netto),
          styles: {
            halign: "right",
            fontStyle: "bold",
            fillColor: [226, 232, 240],
            fontSize: 8,
          },
        },
        {
          content: formatRupiah(summary.total_ppn),
          styles: {
            halign: "right",
            fontStyle: "bold",
            fillColor: [226, 232, 240],
            fontSize: 8,
          },
        },
        {
          content: formatRupiah(summary.total_total),
          styles: {
            halign: "right",
            fontStyle: "bold",
            fillColor: [226, 232, 240],
            fontSize: 8,
          },
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
          fontSize: 7,
        },
        styles: { fontSize: 7, cellPadding: 1.5 },
        columnStyles: {
          0: { halign: "right" },
          1: { halign: "left" },
          2: { halign: "left" },
          3: { halign: "left" },
          4: { halign: "right" },
          5: { halign: "right" },
          6: { halign: "right" },
        },
      });
      const fileName = `Receipt_by_Supplier_${params.tgl_mulai}_sd_${params.tgl_selesai}.pdf`;
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
