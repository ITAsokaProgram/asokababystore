document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("mutasi-table-body");
  const filterForm = document.getElementById("filter-form");
  const filterSubmitButton = document.getElementById("filter-submit-button");
  const filterSelectStore = document.getElementById("kd_store");
  const pageSubtitle = document.getElementById("page-subtitle");
  const paginationInfo = document.getElementById("pagination-info");
  const paginationLinks = document.getElementById("pagination-links");
  const summaryQty = document.getElementById("summary-qty");
  const summaryNetto = document.getElementById("summary-netto");
  const summaryPPN = document.getElementById("summary-ppn");
  const summaryTotal = document.getElementById("summary-total");
  const exportExcelButton = document.getElementById("export-excel-btn");
  window.changePage = function (page) {
    const url = new URL(window.location);
    url.searchParams.set("page", page);
    window.history.pushState({}, "", url);
    loadData();
    document
      .querySelector(".table-container")
      ?.scrollIntoView({ behavior: "smooth", block: "start" });
  };
  function formatRupiah(number) {
    if (isNaN(number) || number === null) return "0";
    return new Intl.NumberFormat("id-ID", {
      style: "decimal",
      minimumFractionDigits: 0,
    }).format(number);
  }
  function formatNumber(number) {
    if (isNaN(number) || number === null) return "0";
    return new Intl.NumberFormat("id-ID").format(number);
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
      status_cetak: params.get("status_cetak") || "all",
      status_terima: params.get("status_terima") || "all",
      page: parseInt(params.get("page") || "1", 10),
    };
  }
  async function loadData() {
    const params = getUrlParams();
    setLoadingState(true);
    if (document.getElementById("tgl_mulai"))
      document.getElementById("tgl_mulai").value = params.tgl_mulai;
    if (document.getElementById("tgl_selesai"))
      document.getElementById("tgl_selesai").value = params.tgl_selesai;
    if (document.getElementById("kd_store"))
      document.getElementById("kd_store").value = params.kd_store;
    if (document.getElementById("status_cetak"))
      document.getElementById("status_cetak").value = params.status_cetak;
    if (document.getElementById("status_terima"))
      document.getElementById("status_terima").value = params.status_terima;
    const queryString = new URLSearchParams(params).toString();
    try {
      const response = await fetch(
        `/src/api/mutasi_in/get_data.php?${queryString}`
      );
      const data = await response.json();
      if (data.error) throw new Error(data.error);
      if (data.stores) populateStoreFilter(data.stores, params.kd_store);
      if (data.summary) {
        if (summaryQty)
          summaryQty.textContent = formatNumber(data.summary.total_qty);
        if (summaryNetto)
          summaryNetto.textContent = formatRupiah(data.summary.total_netto);
        if (summaryPPN)
          summaryPPN.textContent = formatRupiah(data.summary.total_ppn);
        if (summaryTotal)
          summaryTotal.textContent = formatRupiah(data.summary.total_grand);
      }
      if (pageSubtitle) {
        pageSubtitle.textContent = `Periode ${params.tgl_mulai} s/d ${params.tgl_selesai}`;
      }
      renderTable(data.tabel_data);
      renderPagination(data.pagination);
    } catch (error) {
      console.error("Error:", error);
      tableBody.innerHTML = `<tr><td colspan="11" class="text-center p-4 text-red-600">Error: ${error.message}</td></tr>`;
    } finally {
      setLoadingState(false);
    }
  }
  function setLoadingState(isLoading) {
    if (isLoading) {
      if (filterSubmitButton) {
        filterSubmitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Memuat...`;
        filterSubmitButton.disabled = true;
      }
      if (tableBody) {
        tableBody.innerHTML = `<tr><td colspan="11" class="text-center p-8"><div class="spinner-simple"></div> Memuat data...</td></tr>`;
      }
      if (summaryQty) summaryQty.textContent = "-";
      if (summaryNetto) summaryNetto.textContent = "-";
      if (summaryPPN) summaryPPN.textContent = "-";
      if (summaryTotal) summaryTotal.textContent = "-";
    } else {
      if (filterSubmitButton) {
        filterSubmitButton.innerHTML = `<i class="fas fa-filter"></i> Tampilkan`;
        filterSubmitButton.disabled = false;
      }
    }
  }
  function populateStoreFilter(stores, selectedStore) {
    if (!filterSelectStore) return;
    if (filterSelectStore.options.length <= 1) {
      stores.forEach((store) => {
        const option = document.createElement("option");
        option.value = store.kd_store;
        option.textContent = `${store.kd_store} - ${store.nm_alias}`;
        filterSelectStore.appendChild(option);
      });
    }
    filterSelectStore.value = selectedStore;
  }
  function renderTable(data) {
    if (!data || data.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="11" class="text-center p-8 text-gray-500">Tidak ada data mutasi ditemukan.</td></tr>`;
      return;
    }
    let html = "";
    data.forEach((row, index) => {
      const isReceived = row.receipt === "True";
      const isPrinted = row.cetak === "True";
      const printBtnClass = isReceived
        ? isPrinted
          ? "bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200"
          : "bg-pink-600 text-white hover:bg-pink-700 shadow-sm"
        : "bg-gray-200 text-gray-400 cursor-not-allowed";
      const detailRowId = `detail-${index}`;
      html += `
            <tr class="hover:bg-pink-50 cursor-pointer transition-colors border-b border-gray-100" 
                onclick="toggleDetail('${detailRowId}', '${row.no_faktur}', '${
        row.kode_dari
      }', '${row.tgl_raw}')">
                <td class="px-4 py-3 text-sm">${
                  row.tgl_mutasi.split(" ")[0]
                }</td>
                <td class="px-4 py-3 text-sm font-medium text-pink-700">${
                  row.no_faktur
                }</td>
                <td class="px-4 py-3 text-sm">${row.kode_supp}</td>
                <td class="px-4 py-3 text-sm">
                    <div class="font-semibold">${row.kode_dari}</div>
                    <div class="text-xs text-gray-500">${
                      row.dari_nama || ""
                    }</div>
                </td>
                <td class="px-4 py-3 text-sm">
                    <div class="font-semibold">${row.kode_tujuan}</div>
                    <div class="text-xs text-gray-500">${
                      row.tujuan_nama || ""
                    }</div>
                </td>
                <td class="px-4 py-3 text-sm text-right">${formatRupiah(
                  row.total_netto
                )}</td>
                <td class="px-4 py-3 text-sm text-right">${formatRupiah(
                  row.total_ppn
                )}</td>
                <td class="px-4 py-3 text-sm text-right font-bold">${formatRupiah(
                  row.total_grand
                )}</td>
                <td class="px-4 py-3 text-sm">${row.acc_mutasi || "-"}</td>
                <td class="px-4 py-3 text-center">
                    ${
                      isReceived
                        ? '<span class="px-2 py-1 rounded bg-green-100 text-green-800 text-xs font-bold">Ya</span>'
                        : '<span class="px-2 py-1 rounded bg-red-100 text-red-800 text-xs font-bold">Belum</span>'
                    }
                </td>
                <td class="px-4 py-3 text-center" onclick="event.stopPropagation()">
                    <button 
                        onclick="handlePrint('${row.no_faktur}', '${
        row.kode_dari
      }', '${row.receipt}', '${row.cetak}', '${row.tgl_raw}')"
                        class="px-3 py-1.5 rounded text-xs font-medium transition-colors flex items-center gap-1 mx-auto ${printBtnClass}"
                        ${
                          !isReceived
                            ? 'disabled title="Barang belum diterima"'
                            : `title="${
                                isPrinted ? "Cetak Ulang" : "Cetak Faktur"
                              }"`
                        }>
                        <i class="fas fa-print"></i> ${
                          isPrinted ? "Ulang" : "Cetak"
                        }
                    </button>
                </td>
            </tr>
            <tr id="${detailRowId}" class="hidden bg-gray-50">
                <td colspan="11" class="p-4 border-b border-gray-200 shadow-inner">
                    <div class="detail-content">
                        <div class="flex justify-center"><i class="fas fa-circle-notch fa-spin text-pink-500"></i> Memuat detail...</div>
                    </div>
                </td>
            </tr>
            `;
    });
    tableBody.innerHTML = html;
  }
  function renderPagination(pagination) {
    if (!paginationInfo || !paginationLinks) return;
    if (!pagination) {
      paginationInfo.textContent = "";
      paginationLinks.innerHTML = "";
      return;
    }
    const { current_page, total_pages, total_rows } = pagination;
    paginationInfo.textContent = `Halaman ${current_page} dari ${total_pages} (Total ${formatNumber(
      total_rows
    )} Data)`;
    let html = "";
    const prevDisabled =
      current_page <= 1
        ? "disabled class='px-3 py-1 bg-gray-50 border border-gray-200 rounded-md text-gray-300 cursor-not-allowed mr-1'"
        : "class='px-3 py-1 bg-white border border-gray-300 rounded-md hover:bg-gray-50 text-gray-700 transition-colors mr-1'";
    const prevClick =
      current_page > 1 ? `onclick="changePage(${current_page - 1})"` : "";
    html += `<button ${prevDisabled} ${prevClick}><i class="fas fa-chevron-left"></i></button>`;
    const max_pages_around = 2;
    for (let i = 1; i <= total_pages; i++) {
      if (
        i === 1 ||
        i === total_pages ||
        (i >= current_page - max_pages_around &&
          i <= current_page + max_pages_around)
      ) {
        const activeClass =
          i === current_page
            ? "bg-pink-600 text-white border-pink-600"
            : "bg-white text-gray-700 border-gray-300 hover:bg-gray-50";
        html += `<button onclick="changePage(${i})" class="px-3 py-1 border rounded-md text-sm font-medium transition-colors mx-1 ${activeClass}">${i}</button>`;
      } else if (
        (i === current_page - max_pages_around - 1 && i > 1) ||
        (i === current_page + max_pages_around + 1 && i < total_pages)
      ) {
        html += `<span class="px-2 text-gray-400">...</span>`;
      }
    }
    const nextDisabled =
      current_page >= total_pages
        ? "disabled class='px-3 py-1 bg-gray-50 border border-gray-200 rounded-md text-gray-300 cursor-not-allowed ml-1'"
        : "class='px-3 py-1 bg-white border border-gray-300 rounded-md hover:bg-gray-50 text-gray-700 transition-colors ml-1'";
    const nextClick =
      current_page < total_pages
        ? `onclick="changePage(${current_page + 1})"`
        : "";
    html += `<button ${nextDisabled} ${nextClick}><i class="fas fa-chevron-right"></i></button>`;
    paginationLinks.innerHTML = html;
  }
  if (exportExcelButton) {
    exportExcelButton.addEventListener("click", async () => {
      const params = getUrlParams();
      const queryString = new URLSearchParams({
        ...params,
        export: "true",
      }).toString();
      const originalText = exportExcelButton.innerHTML;
      exportExcelButton.innerHTML =
        '<i class="fas fa-spinner fa-spin"></i> Exporting...';
      exportExcelButton.disabled = true;
      try {
        const response = await fetch(
          `/src/api/mutasi_in/get_data.php?${queryString}`
        );
        const result = await response.json();
        if (result.tabel_data && result.tabel_data.length > 0) {
          const dataToExport = result.tabel_data.map((row) => ({
            Tanggal: row.tgl_mutasi,
            "No Faktur": row.no_faktur,
            "Kode Supp": row.kode_supp,
            Dari: `${row.kode_dari} - ${row.dari_nama}`,
            Tujuan: `${row.kode_tujuan} - ${row.tujuan_nama}`,
            "Total Netto": row.total_netto,
            PPN: row.total_ppn,
            "Grand Total": row.total_grand,
            "Acc Mutasi": row.acc_mutasi,
            "Sudah Terima": row.receipt === "True" ? "Ya" : "Tidak",
            "Sudah Cetak": row.cetak === "True" ? "Ya" : "Tidak",
          }));
          const ws = XLSX.utils.json_to_sheet(dataToExport);
          const wb = XLSX.utils.book_new();
          XLSX.utils.book_append_sheet(wb, ws, "Mutasi In");
          XLSX.writeFile(
            wb,
            `Mutasi_In_${params.tgl_mulai}_${params.tgl_selesai}.xlsx`
          );
        } else {
          Swal.fire("Info", "Tidak ada data untuk diexport", "info");
        }
      } catch (e) {
        Swal.fire("Error", "Gagal export: " + e.message, "error");
      } finally {
        exportExcelButton.innerHTML = originalText;
        exportExcelButton.disabled = false;
      }
    });
  }
  if (filterForm) {
    filterForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const tglMulai = document.getElementById("tgl_mulai").value;
      const tglSelesai = document.getElementById("tgl_selesai").value;
      const kdStore = document.getElementById("kd_store").value;
      const statusCetak = document.getElementById("status_cetak").value;
      const statusTerima = document.getElementById("status_terima").value;
      const url = new URL(window.location);
      url.searchParams.set("tgl_mulai", tglMulai);
      url.searchParams.set("tgl_selesai", tglSelesai);
      url.searchParams.set("kd_store", kdStore);
      url.searchParams.set("status_cetak", statusCetak);
      url.searchParams.set("status_terima", statusTerima);
      url.searchParams.set("page", 1);
      window.history.pushState({}, "", url);
      loadData();
    });
  }
  document.addEventListener("forceLoadData", () => {
    loadData();
  });
  loadData();
});
window.toggleDetail = async function (rowId, noFaktur, kodeDari, tglMutasi) {
  const detailRow = document.getElementById(rowId);
  const contentDiv = detailRow.querySelector(".detail-content");
  if (detailRow.classList.contains("hidden")) {
    detailRow.classList.remove("hidden");
    try {
      const response = await fetch(
        `/src/api/mutasi_in/get_detail.php?no_faktur=${noFaktur}&kode_dari=${kodeDari}&tgl_mutasi=${tglMutasi}`
      );
      const result = await response.json();
      if (result.data && result.data.length > 0) {
        function fmtRp(n) {
          return new Intl.NumberFormat("id-ID", {
            style: "decimal",
            minimumFractionDigits: 0,
          }).format(n);
        }
        let detailHtml = `
                <h4 class="text-xs font-bold uppercase text-gray-500 mb-2 tracking-wider">Detail Item: ${noFaktur}</h4>
                <table class="w-full text-xs bg-white rounded border border-gray-200 overflow-hidden">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="p-2 text-left">No</th>
                            <th class="p-2 text-left">PLU</th>
                            <th class="p-2 text-left">Barcode</th>
                            <th class="p-2 text-left">Nama Barang</th>
                            <th class="p-2 text-right">Qty</th>
                            <th class="p-2 text-center">Satuan</th>
                            <th class="p-2 text-right">Harga Beli</th>
                            <th class="p-2 text-right">PPN</th>
                            <th class="p-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                `;
        result.data.forEach((item, idx) => {
          detailHtml += `
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="p-2">${idx + 1}</td>
                        <td class="p-2 font-mono">${item.plu}</td>
                        <td class="p-2 font-mono">${item.barcode || "-"}</td>
                        <td class="p-2">${item.descp}</td>
                        <td class="p-2 text-right font-bold">${fmtRp(
                          item.qty
                        )}</td>
                        <td class="p-2 text-center">${item.satuan}</td>
                        <td class="p-2 text-right">${fmtRp(item.hrg_beli)}</td>
                        <td class="p-2 text-right">${fmtRp(item.ppn)}</td>
                        <td class="p-2 text-right font-semibold">${fmtRp(
                          item.total_row
                        )}</td>
                    </tr>`;
        });
        detailHtml += `</tbody></table>`;
        contentDiv.innerHTML = detailHtml;
      } else {
        contentDiv.innerHTML = `<p class="text-center text-gray-500 italic">Tidak ada detail item.</p>`;
      }
    } catch (e) {
      contentDiv.innerHTML = `<p class="text-red-500">Gagal memuat detail: ${e.message}</p>`;
    }
  } else {
    detailRow.classList.add("hidden");
  }
};
window.handlePrint = async function (
  noFaktur,
  kodeDari,
  isReceived,
  isPrinted,
  tglMutasi
) {
  if (isReceived !== "True") {
    Swal.fire(
      "Akses Ditolak",
      "Barang belum diterima. Tidak dapat mencetak faktur.",
      "warning"
    );
    return;
  }
  let titleText = "Konfirmasi Cetak Faktur";
  let bodyText =
    "Status dokumen akan diperbarui menjadi 'Sudah Cetak' dan faktur akan diunduh.";
  let confirmText = "Ya, Cetak & Unduh";
  let iconType = "info";
  if (isPrinted === "True") {
    titleText = "Cetak Ulang Faktur?";
    bodyText = "Dokumen ini sudah pernah dicetak sebelumnya. Unduh ulang?";
    confirmText = "Unduh Ulang";
    iconType = "question";
  }
  Swal.fire({
    title: titleText,
    text: bodyText,
    icon: iconType,
    showCancelButton: true,
    confirmButtonColor: "#db2777",
    confirmButtonText: confirmText,
    cancelButtonText: "Batal",
    reverseButtons: true,
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        if (isPrinted !== "True") {
          const formData = new FormData();
          formData.append("no_faktur", noFaktur);
          formData.append("kode_dari", kodeDari);
          const updateResp = await fetch(
            "/src/api/mutasi_in/update_cetak.php",
            {
              method: "POST",
              body: formData,
            }
          );
          const updateRes = await updateResp.json();
          if (!updateRes.success) throw new Error(updateRes.message);
          document.dispatchEvent(new Event("forceLoadData"));
        }
        Swal.fire({
          title: "Menyiapkan Faktur...",
          text: "Sedang menyusun layout Excel...",
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          },
        });
        const invoiceResp = await fetch(
          `/src/api/mutasi_in/get_invoice_excel.php?no_faktur=${noFaktur}&kode_dari=${kodeDari}&tgl_mutasi=${tglMutasi}`
        );
        if (!invoiceResp.ok) throw new Error("Gagal mengambil data faktur");
        const invoiceData = await invoiceResp.json();
        if (invoiceData.error) throw new Error(invoiceData.error);
        const h = invoiceData.header;
        const d = invoiceData.details;
        const workbook = new ExcelJS.Workbook();
        const sheet = workbook.addWorksheet("Faktur");
        sheet.columns = [
          { width: 6 },
          { width: 16 },
          { width: 45 },
          { width: 8 },
          { width: 8 },
          { width: 18 },
          { width: 18 },
          { width: 20 },
        ];
        const borderAll = {
          top: { style: "thin" },
          left: { style: "thin" },
          bottom: { style: "thin" },
          right: { style: "thin" },
        };
        const fontBold = { name: "Arial", size: 10, bold: true };
        const fontRegular = { name: "Arial", size: 10 };
        const alignCenter = { vertical: "middle", horizontal: "center" };
        const alignRight = { vertical: "middle", horizontal: "right" };
        const alignLeft = { vertical: "middle", horizontal: "left" };
        sheet.mergeCells("A1:H1");
        sheet.getCell("A1").value = "FAKTUR MUTASI BARANG";
        sheet.getCell("A1").font = {
          name: "Arial",
          size: 16,
          bold: true,
          underline: true,
        };
        sheet.getCell("A1").alignment = alignCenter;
        sheet.addRow([]);
        sheet.mergeCells("A3:C3");
        sheet.getCell("A3").value = "DARI (PENGIRIM):";
        sheet.getCell("A3").font = fontBold;
        sheet.mergeCells("E3:H3");
        sheet.getCell("E3").value = "KEPADA (PENERIMA):";
        sheet.getCell("E3").font = fontBold;
        sheet.mergeCells("A4:C4");
        sheet.getCell("A4").value = h.d_cv || "-";
        sheet.getCell("A4").font = {
          ...fontBold,
          size: 11,
          color: { argb: "FFDB2777" },
        };
        sheet.mergeCells("E4:H4");
        sheet.getCell("E4").value = h.t_cv || "-";
        sheet.getCell("E4").font = { ...fontBold, size: 11 };
        sheet.mergeCells("A5:C5");
        sheet.getCell("A5").value = h.d_alm || "-";
        sheet.getCell("A5").alignment = {
          wrapText: true,
          vertical: "top",
          horizontal: "left",
        };
        sheet.mergeCells("E5:H5");
        sheet.getCell("E5").value = h.t_alm || "-";
        sheet.getCell("E5").alignment = {
          wrapText: true,
          vertical: "top",
          horizontal: "left",
        };
        sheet.mergeCells("A6:C6");
        sheet.getCell("A6").value = "NPWP: " + (h.d_npwp || "-");
        sheet.getCell("A6").font = { name: "Arial", size: 9 };
        sheet.mergeCells("E6:H6");
        sheet.getCell("E6").value = "NPWP: " + (h.t_npwp || "-");
        sheet.getCell("E6").font = { name: "Arial", size: 9 };
        sheet.addRow([]);
        const rowInfoIdx = 8;
        const rInfo = sheet.getRow(rowInfoIdx);
        sheet.getCell(`A${rowInfoIdx}`).value = "NO. FAKTUR";
        sheet.mergeCells(`A${rowInfoIdx}:B${rowInfoIdx}`);
        sheet.getCell(`A${rowInfoIdx}`).font = fontBold;
        sheet.getCell(`A${rowInfoIdx}`).alignment = alignLeft;
        sheet.getCell(`C${rowInfoIdx}`).value = ": " + h.no_faktur;
        sheet.getCell(`C${rowInfoIdx}`).font = fontBold;
        sheet.getCell(`F${rowInfoIdx}`).value = "TANGGAL";
        sheet.mergeCells(`F${rowInfoIdx}:G${rowInfoIdx}`);
        sheet.getCell(`F${rowInfoIdx}`).font = fontBold;
        sheet.getCell(`F${rowInfoIdx}`).alignment = alignRight;
        sheet.getCell(`H${rowInfoIdx}`).value = ": " + h.tgl_mutasi;
        sheet.getCell(`H${rowInfoIdx}`).font = fontBold;
        sheet.getCell(`H${rowInfoIdx}`).alignment = alignRight;
        sheet.addRow([]);
        const headers = [
          "NO",
          "PLU",
          "DESKRIPSI BARANG",
          "QTY",
          "SATUAN",
          "HARGA",
          "PPN",
          "TOTAL",
        ];
        const rowHeader = sheet.addRow(headers);
        rowHeader.height = 25;
        rowHeader.eachCell((cell) => {
          cell.fill = {
            type: "pattern",
            pattern: "solid",
            fgColor: { argb: "FFEEE0E5" },
          };
          cell.font = fontBold;
          cell.alignment = alignCenter;
          cell.border = borderAll;
        });
        d.forEach((row) => {
          const r = sheet.addRow([
            row.No,
            row.plu,
            row.descp,
            parseFloat(row.qty),
            row.satuan,
            parseFloat(row.netto),
            parseFloat(row.ppn),
            parseFloat(row.total),
          ]);
          r.getCell(1).alignment = alignCenter;
          r.getCell(2).alignment = alignCenter;
          r.getCell(3).alignment = {
            wrapText: true,
            vertical: "middle",
            horizontal: "left",
          };
          r.getCell(4).alignment = alignCenter;
          r.getCell(5).alignment = alignCenter;
          r.getCell(6).numFmt = "#,##0";
          r.getCell(7).numFmt = "#,##0";
          r.getCell(8).numFmt = "#,##0";
          r.eachCell((cell) => {
            cell.border = borderAll;
            cell.font = fontRegular;
          });
        });
        const rowSub = sheet.addRow([
          "",
          "",
          "",
          "",
          "",
          "",
          "SUB TOTAL",
          parseFloat(h.Sub_Total),
        ]);
        rowSub.getCell(7).font = fontBold;
        rowSub.getCell(7).alignment = alignRight;
        rowSub.getCell(8).font = fontBold;
        rowSub.getCell(8).numFmt = "#,##0";
        rowSub.getCell(8).border = borderAll;
        const rowPpn = sheet.addRow([
          "",
          "",
          "",
          "",
          "",
          "",
          "PPN",
          parseFloat(h.Ppn1),
        ]);
        rowPpn.getCell(7).font = fontBold;
        rowPpn.getCell(7).alignment = alignRight;
        rowPpn.getCell(8).font = fontBold;
        rowPpn.getCell(8).numFmt = "#,##0";
        rowPpn.getCell(8).border = borderAll;
        const rowGrand = sheet.addRow([
          "",
          "",
          "",
          "",
          "",
          "",
          "GRAND TOTAL",
          parseFloat(h.Total),
        ]);
        rowGrand.getCell(7).font = { ...fontBold, size: 11 };
        rowGrand.getCell(7).alignment = alignRight;
        rowGrand.getCell(8).font = {
          ...fontBold,
          size: 12,
          color: { argb: "FFFFFFFF" },
        };
        rowGrand.getCell(8).fill = {
          type: "pattern",
          pattern: "solid",
          fgColor: { argb: "FFDB2777" },
        };
        rowGrand.getCell(8).numFmt = "#,##0";
        rowGrand.getCell(8).border = borderAll;
        sheet.addRow([]);
        sheet.addRow([]);
        const sigRowTitle = sheet.addRow([
          "Diterima Oleh,",
          "",
          "",
          "",
          "",
          "Hormat Kami,",
          "",
          "",
        ]);
        sheet.mergeCells(`A${sigRowTitle.number}:C${sigRowTitle.number}`);
        sheet.mergeCells(`F${sigRowTitle.number}:H${sigRowTitle.number}`);
        sheet.getCell(`A${sigRowTitle.number}`).alignment = alignCenter;
        sheet.getCell(`F${sigRowTitle.number}`).alignment = alignCenter;
        sheet.addRow([]);
        sheet.addRow([]);
        sheet.addRow([]);
        sheet.addRow([]);
        const sigRowName = sheet.addRow([
          "( ........................ )",
          "",
          "",
          "",
          "",
          "( ........................ )",
          "",
          "",
        ]);
        sheet.mergeCells(`A${sigRowName.number}:C${sigRowName.number}`);
        sheet.mergeCells(`F${sigRowName.number}:H${sigRowName.number}`);
        sheet.getCell(`A${sigRowName.number}`).alignment = alignCenter;
        sheet.getCell(`F${sigRowName.number}`).alignment = alignCenter;
        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], {
          type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        });
        const url = window.URL.createObjectURL(blob);
        const anchor = document.createElement("a");
        anchor.href = url;
        anchor.download = `Faktur_${noFaktur}.xlsx`;
        anchor.click();
        window.URL.revokeObjectURL(url);
        Swal.fire({
          icon: "success",
          title: "Berhasil",
          text: "Faktur berhasil diunduh.",
          timer: 2000,
          showConfirmButton: false,
        });
      } catch (e) {
        console.error(e);
        Swal.fire("Error", e.message, "error");
      }
    }
  });
};
