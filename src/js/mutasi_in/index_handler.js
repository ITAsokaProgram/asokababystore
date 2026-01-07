import getCookie from "../index/utils/cookies.js";
let currentSelectedRow = null;
let userCanPrint = false;
document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("mutasi-table-body");
  const filterForm = document.getElementById("filter-form");
  const filterSubmitButton = document.getElementById("filter-submit-button");
  const pageSubtitle = document.getElementById("page-subtitle");
  const paginationInfo = document.getElementById("pagination-info");
  const paginationLinks = document.getElementById("pagination-links");
  const summaryQty = document.getElementById("summary-qty");
  const summaryNetto = document.getElementById("summary-netto");
  const summaryPPN = document.getElementById("summary-ppn");
  const summaryTotal = document.getElementById("summary-total");
  window.changePage = function (page) {
    const url = new URL(window.location);
    url.searchParams.set("page", page);
    window.history.pushState({}, "", url);
    loadData();
  };
  function formatRupiah(number) {
    if (isNaN(number) || number === null) return "0";
    return new Intl.NumberFormat("id-ID", {
      style: "decimal",
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(number);
  }
  function formatNumber(number) {
    if (isNaN(number) || number === null) return "0";
    return new Intl.NumberFormat("id-ID", {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(number);
  }
  function getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    const today = new Date();
    const oneMonthAgo = new Date();
    oneMonthAgo.setMonth(today.getMonth() - 1);
    const todayString = today.toISOString().split("T")[0];
    const oneMonthAgoString = oneMonthAgo.toISOString().split("T")[0];
    return {
      tgl_mulai: params.get("tgl_mulai") || oneMonthAgoString,
      tgl_selesai: params.get("tgl_selesai") || todayString,
      kd_store: params.get("kd_store") || "all",
      kd_store_tujuan: params.get("kd_store_tujuan") || "all",
      status_cetak: params.get("status_cetak") || "all",
      status_terima: params.get("status_terima") || "all",
      search_query: params.get("search_query") || "",
      page: parseInt(params.get("page") || "1", 10),
    };
  }
  async function loadData() {
    const params = getUrlParams();
    setLoadingState(true);
    if (document.getElementById("search_query"))
      document.getElementById("search_query").value = params.search_query;
    if (document.getElementById("tgl_mulai"))
      document.getElementById("tgl_mulai").value = params.tgl_mulai;
    if (document.getElementById("tgl_selesai"))
      document.getElementById("tgl_selesai").value = params.tgl_selesai;
    if (document.getElementById("kd_store"))
      document.getElementById("kd_store").value = params.kd_store;
    if (document.getElementById("kd_store_tujuan"))
      document.getElementById("kd_store_tujuan").value = params.kd_store_tujuan;
    if (document.getElementById("status_cetak"))
      document.getElementById("status_cetak").value = params.status_cetak;
    if (document.getElementById("status_terima"))
      document.getElementById("status_terima").value = params.status_terima;
    const queryString = new URLSearchParams(params).toString();
    const token = getCookie("admin_token");
    try {
      const response = await fetch(
        `/src/api/mutasi_in/get_data.php?${queryString}`,
        {
          method: "GET",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
          },
        }
      );
      const data = await response.json();
      if (data.error) throw new Error(data.error);
      userCanPrint = data.allow_print === true;
      if (data.stores) {
        populateStoreFilter("kd_store", data.stores, params.kd_store);
        populateStoreFilter(
          "kd_store_tujuan",
          data.stores,
          params.kd_store_tujuan
        );
      }
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
  function populateStoreFilter(elementId, stores, selectedStore) {
    const selectEl = document.getElementById(elementId);
    if (!selectEl) return;
    if (selectEl.options.length <= 1) {
      stores.forEach((store) => {
        const option = document.createElement("option");
        option.value = store.kd_store;
        option.textContent = `${store.kd_store} - ${store.nm_alias}`;
        selectEl.appendChild(option);
      });
    }
    selectEl.value = selectedStore;
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
      let printBtnClass = "";
      let printIcon = '<i class="fas fa-print"></i>';
      let btnTitle = "";
      let btnLabel = isPrinted ? "Ulang" : "Cetak";
      if (!userCanPrint) {
        printBtnClass =
          "bg-gray-100 text-gray-400 border border-gray-200 cursor-not-allowed opacity-75";
        printIcon = '<i class="fas fa-lock"></i>';
        btnTitle = "Anda tidak memiliki akses untuk mencetak invoice";
      } else if (!isReceived) {
        printBtnClass = "bg-gray-200 text-gray-400 cursor-not-allowed";
        btnTitle = "Barang belum diterima";
      } else if (isPrinted) {
        printBtnClass =
          "bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200";
        btnTitle = "Cetak Ulang";
      } else {
        printBtnClass = "bg-pink-600 text-white hover:bg-pink-700 shadow-sm";
        btnTitle = "Cetak Faktur";
      }
      const rowId = `row-${index}`;
      html += `
      <tr id="${rowId}" class="hover:bg-pink-50 cursor-pointer transition-colors border-b border-gray-100" 
          onclick="showDetailFaktur('${rowId}', '${row.no_faktur}', '${row.kode_dari
        }', '${row.tgl_raw}')">
          <td class="px-4 py-3 text-sm align-top">${row.tgl_mutasi.split(" ")[0]
        }</td>
          <td class="px-4 py-3 text-sm font-medium text-pink-700 align-top">${row.no_faktur
        }</td>
          <td class="px-4 py-3 text-sm align-top">${row.kode_supp}</td>
          <td class="px-4 py-3 text-sm align-top">
              <div class="flex flex-col gap-0.5">
                <div class="font-bold text-gray-800">${row.kode_dari} - ${row.dari_nama || ""
        }</div>
                <div class="text-xs font-medium text-gray-600">${row.dari_nama_npwp || "-"
        }</div>
                <div class="text-[10px] text-gray-400 italic leading-tight">${row.dari_alm_npwp || "-"
        }</div>
              </div>
          </td>
          <td class="px-4 py-3 text-sm align-top">
                <div class="flex flex-col gap-0.5">
                <div class="font-bold text-gray-800">${row.kode_tujuan} - ${row.tujuan_nama || ""
        }</div>
                <div class="text-xs font-medium text-gray-600">${row.tujuan_nama_npwp || "-"
        }</div>
                <div class="text-[10px] text-gray-400 italic leading-tight">${row.tujuan_alm_npwp || "-"
        }</div>
              </div>
          </td>
          <td class="px-4 py-3 text-sm align-top text-right">${formatRupiah(
          row.total_netto
        )}</td>
          <td class="px-4 py-3 text-sm align-top text-right">${formatRupiah(
          row.total_ppn
        )}</td>
          <td class="px-4 py-3 text-sm font-bold align-top text-right">${formatRupiah(
          row.total_grand
        )}</td>
          <td class="px-4 py-3 text-sm align-top">${row.acc_mutasi || "-"}</td>
          <td class="px-4 py-3 text-center align-top">
              ${isReceived
          ? '<span class="px-2 py-1 rounded bg-green-100 text-green-800 text-xs font-bold">Ya</span>'
          : '<span class="px-2 py-1 rounded bg-red-100 text-red-800 text-xs font-bold">Belum</span>'
        }
          </td>
          <td class="px-4 py-3 text-center align-top" onclick="event.stopPropagation()">
              <button 
                  onclick="handlePrint('${row.no_faktur}', '${row.kode_dari
        }', '${row.receipt}', '${row.cetak}', '${row.tgl_raw}')"
                  class="px-3 py-1.5 rounded text-xs font-medium transition-colors flex items-center gap-1 mx-auto ${printBtnClass}"
                  title="${btnTitle}">
                  ${printIcon} ${btnLabel}
              </button>
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
  if (filterForm) {
    filterForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const searchQuery = document.getElementById("search_query").value;
      const tglMulai = document.getElementById("tgl_mulai").value;
      const tglSelesai = document.getElementById("tgl_selesai").value;
      const kdStore = document.getElementById("kd_store").value;
      const kdStoreTujuan = document.getElementById("kd_store_tujuan").value;
      const statusCetak = document.getElementById("status_cetak").value;
      const statusTerima = document.getElementById("status_terima").value;
      const url = new URL(window.location);
      url.searchParams.set("search_query", searchQuery);
      url.searchParams.set("tgl_mulai", tglMulai);
      url.searchParams.set("tgl_selesai", tglSelesai);
      url.searchParams.set("kd_store", kdStore);
      url.searchParams.set("kd_store_tujuan", kdStoreTujuan);
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
window.showDetailFaktur = async function (
  rowId,
  noFaktur,
  kodeDari,
  tglMutasi
) {
  const detailContent = document.getElementById("detail-faktur-content");
  const detailSubtitle = document.getElementById("detail-subtitle");
  const clickedRow = document.getElementById(rowId);
  if (currentSelectedRow) {
    currentSelectedRow.classList.remove("selected-row");
  }
  clickedRow.classList.add("selected-row");
  currentSelectedRow = clickedRow;
  if (detailSubtitle) {
    detailSubtitle.textContent = `Faktur: ${noFaktur}`;
  }
  detailContent.innerHTML = `
    <div class="detail-loading">
      <div class="spinner-simple"></div>
      <p>Memuat detail faktur...</p>
    </div>
  `;
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
          maximumFractionDigits: 0,
        }).format(n);
      }
      let detailHtml = `
        <div class="detail-table-scroll-container overflow-y-auto max-h-[500px] relative border border-gray-200 rounded">
          <table class="w-full text-xs bg-white table-modern">
            <thead class="bg-gray-50">
              <tr>
                <th class="sticky top-0 z-10 bg-gray-50 p-2 text-left border-b border-gray-200">No</th>
                <th class="sticky top-0 z-10 bg-gray-50 p-2 text-left border-b border-gray-200">PLU</th>
                <th class="sticky top-0 z-10 bg-gray-50 p-2 text-left border-b border-gray-200">Barcode</th>
                <th class="sticky top-0 z-10 bg-gray-50 p-2 text-left border-b border-gray-200">Nama Barang</th>
                <th class="sticky top-0 z-10 bg-gray-50 p-2 border-b border-gray-200">Qty</th>
                <th class="sticky top-0 z-10 bg-gray-50 p-2 border-b border-gray-200">Satuan</th>
                <th class="sticky top-0 z-10 bg-gray-50 p-2 text-right border-b border-gray-200">Harga Beli</th>
                <th class="sticky top-0 z-10 bg-gray-50 p-2 text-right border-b border-gray-200">PPN</th>
                <th class="sticky top-0 z-10 bg-gray-50 p-2 text-right border-b border-gray-200">Total</th>
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
            <td class="p-2 font-bold">${fmtRp(item.qty)}</td>
            <td class="p-2">${item.satuan}</td>
            <td class="p-2 text-right">${fmtRp(item.hrg_beli)}</td>
            <td class="p-2 text-right">${fmtRp(item.ppn)}</td>
            <td class="p-2 font-semibold text-right">${fmtRp(
          item.total_row
        )}</td>
          </tr>
        `;
      });
      detailHtml += `
            </tbody>
          </table>
        </div>
      `;
      detailContent.innerHTML = detailHtml;
    } else {
      detailContent.innerHTML = `
        <div class="detail-faktur-empty">
          <i class="fas fa-box-open"></i>
          <p>Tidak ada detail item untuk faktur ini</p>
        </div>
      `;
    }
  } catch (e) {
    detailContent.innerHTML = `
      <div class="detail-faktur-empty">
        <i class="fas fa-exclamation-triangle text-red-400"></i>
        <p class="text-red-600">Gagal memuat detail: ${e.message}</p>
      </div>
    `;
  }
};
window.handlePrint = async function (
  noFaktur,
  kodeDari,
  isReceived,
  isPrinted,
  tglMutasi
) {
  if (!userCanPrint) {
    Swal.fire(
      "Akses Ditolak",
      "Anda tidak memiliki izin untuk mencetak invoice/faktur. Silahkan hubungi IT.",
      "error"
    );
    return;
  }
  if (isReceived !== "True") {
    Swal.fire(
      "Belum Diterima",
      "Barang belum diterima. Tidak dapat mencetak faktur.",
      "warning"
    );
    return;
  }
  const { value: authData } = await Swal.fire({
    title: 'Otorisasi Cetak Invoice',
    html: `
      <div class="text-left">
        <p class="text-xs text-gray-500 mb-4">Silahkan masukkan kredensial otorisasi untuk melanjutkan cetak <b>${noFaktur}</b></p>
        <label class="block text-xs font-semibold mb-1">NIK</label>
        <input id="swal-user" class="swal2-input !mt-0 !w-full" placeholder="Masukkan NIK">
        <label class="block text-xs font-semibold mt-3 mb-1">Password Otorisasi</label>
        <input id="swal-pass" type="password" class="swal2-input !mt-0 !w-full" placeholder="******">
      </div>
    `,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Verifikasi & Cetak',
    confirmButtonColor: "#db2777",
    cancelButtonText: 'Batal',
    preConfirm: () => {
      const user = document.getElementById('swal-user').value;
      const pass = document.getElementById('swal-pass').value;
      if (!user || !pass) {
        Swal.showValidationMessage('User ID dan Password wajib diisi!');
      }
      return { user, pass };
    }
  });
  if (!authData) return;
  try {
    Swal.fire({
      title: "Memverifikasi Otorisasi...",
      text: "Mohon tunggu sejenak",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });
    const formData = new FormData();
    formData.append("no_faktur", noFaktur);
    formData.append("kode_dari", kodeDari);
    formData.append("user_otorisasi", authData.user);
    formData.append("pass_otorisasi", authData.pass);
    const updateResp = await fetch("/src/api/mutasi_in/update_cetak.php", {
      method: "POST",
      body: formData,
    });
    const updateRes = await updateResp.json();
    if (!updateRes.success) throw new Error(updateRes.message);
    document.dispatchEvent(new Event("forceLoadData"));
    Swal.update({
      title: "Menyiapkan Faktur...",
      text: "Sedang menyusun layout Excel...",
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
      { key: "no", width: 5 },
      { key: "barcode", width: 18 },
      { key: "nama", width: 48 },
      { key: "qty", width: 8 },
      { key: "sat", width: 8 },
      { key: "harga", width: 15 },
      { key: "ppn", width: 15 },
      { key: "total", width: 18 },
    ];
    const fontBold = { name: "Arial", size: 10, bold: true };
    const fontRegular = { name: "Arial", size: 10 };
    const fontSmall = { name: "Arial", size: 9 };
    const borderAll = {
      top: { style: "thin" },
      left: { style: "thin" },
      bottom: { style: "thin" },
      right: { style: "thin" },
    };
    const alignCenter = { vertical: "middle", horizontal: "center" };
    const alignRight = { vertical: "middle", horizontal: "right" };
    const alignTopLeft = {
      vertical: "top",
      horizontal: "left",
      wrapText: true,
    };
    sheet.mergeCells("A2:D2");
    sheet.getCell("A2").value = h.d_cv || "NAMA PERUSAHAAN";
    sheet.getCell("A2").font = { name: "Arial", size: 11, bold: true };
    sheet.getCell("G2").value = "No Invoice :";
    sheet.getCell("H2").value = h.no_faktur;
    sheet.getCell("H2").font = fontBold;
    sheet.getCell("H2").alignment = alignRight;
    sheet.mergeCells("A3:D5");
    sheet.getCell("A3").value = h.d_alm || "Alamat Perusahaan...";
    sheet.getCell("A3").font = fontRegular;
    sheet.getCell("A3").alignment = alignTopLeft;
    sheet.getCell("G3").value = "Tanggal :";
    sheet.getCell("H3").value = h.tgl_mutasi;
    sheet.getCell("H3").alignment = alignRight;
    const headers = ["No", "Barcode", "Nama Barang", "Qty", "Sat", "Harga", "Ppn", "Total"];
    const headerRow = sheet.getRow(13);
    headers.forEach((txt, i) => {
      const cell = headerRow.getCell(i + 1);
      cell.value = txt;
      cell.font = fontBold;
      cell.alignment = alignCenter;
      cell.border = borderAll;
      cell.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FFEEE0E5" } };
    });
    d.forEach((row, index) => {
      const r = sheet.addRow([
        index + 1,
        (row.barcode || row.plu || "-").toString(),
        row.descp,
        parseFloat(row.qty),
        row.satuan,
        parseFloat(row.netto),
        parseFloat(row.ppn),
        parseFloat(row.total),
      ]);
      r.eachCell((cell, i) => {
        cell.border = borderAll;
        cell.font = fontRegular;
        if (i === 1 || i === 2 || i === 4 || i === 5) cell.alignment = alignCenter;
        if (i === 6 || i === 7 || i === 8) cell.numFmt = "#,##0";
      });
    });
    const subTotalRow = sheet.addRow([]);
    subTotalRow.getCell(7).value = "Sub Total";
    subTotalRow.getCell(8).value = parseFloat(h.Sub_Total);
    subTotalRow.getCell(8).numFmt = "#,##0";
    subTotalRow.getCell(8).font = fontBold;
    subTotalRow.getCell(8).border = borderAll;
    const grandRow = sheet.addRow([]);
    grandRow.getCell(7).value = "TOTAL";
    grandRow.getCell(8).value = parseFloat(h.Total);
    grandRow.getCell(8).numFmt = "#,##0";
    grandRow.getCell(8).font = { ...fontBold, color: { argb: "FFFFFFFF" } };
    grandRow.getCell(8).fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FFDB2777" } };
    grandRow.getCell(8).border = borderAll;
    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = window.URL.createObjectURL(blob);
    const anchor = document.createElement("a");
    anchor.href = url;
    anchor.download = `Faktur_${noFaktur}.xlsx`;
    anchor.click();
    window.URL.revokeObjectURL(url);
    Swal.fire({
      icon: "success",
      title: "Berhasil",
      text: "Otorisasi diterima dan faktur berhasil diunduh.",
      timer: 2000,
      showConfirmButton: false,
    });
  } catch (e) {
    console.error(e);
    Swal.fire("Error", e.message, "error");
  }
};