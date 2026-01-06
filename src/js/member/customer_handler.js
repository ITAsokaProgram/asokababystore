import * as api from "./member_api_service.js";

let currentPage = 1;
const LIMIT = 10;
let exportExcelButton;
// BUAT OBJEK UNTUK MENYIMPAN SEMUA PARAMETER FILTER
const filterParams = {
  filter_type: null,
  filter: null,
  start_date: null,
  end_date: null,
};
let currentKdCust = "";
let modal, modalCloseBtn, modalTitle, modalProductName, modalBody, modalSpinner;

function showLoading(isLoading) {
  const spinner = document.getElementById("loading-spinner");
  if (spinner) {
    spinner.classList.toggle("hidden", !isLoading);
  }
}

function showError(message) {
  const errorEl = document.getElementById("error-message");
  if (errorEl) {
    if (message) {
      errorEl.textContent = message;
      errorEl.classList.remove("hidden");
    } else {
      errorEl.textContent = "";
      errorEl.classList.add("hidden");
    }
  }
}

function showTable(isShown) {
  const tableContainer = document.getElementById("product-table-container");
  const paginationContainer = document.getElementById("pagination-container");
  if (tableContainer) {
    tableContainer.classList.toggle("hidden", !isShown);
  }
  if (paginationContainer) {
    paginationContainer.classList.toggle("hidden", !isShown);
  }
}

function renderProductTable(products) {
  const tableBody = document.getElementById("product-table-body");
  if (!tableBody) return;
  tableBody.innerHTML = "";
  if (products.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada data produk terlaris ditemukan untuk kriteria ini.</td></tr>`;
    return;
  }
  const formatter = new Intl.NumberFormat("id-ID");
  products.forEach((product, index) => {
    const rank = (currentPage - 1) * LIMIT + index + 1;
    const row = `
            <tr class="hover:bg-gray-50 cursor-pointer product-row" 
                data-plu="${product.plu}"
                data-descp="${product.descp}">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${rank}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.plu
      }</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">${product.descp
      }</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">${formatter.format(
        product.total_qty
      )}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                    <button 
                        class="btn-send-voucher bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-md text-xs shadow-sm" 
                        data-descp="${product.descp}"
                        title="Kirim pesan voucher untuk produk ini">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </td>
            </tr>
        `;
    tableBody.innerHTML += row;
  });
}

function renderPagination(pagination) {
  const { total_records, current_page, limit, total_pages } = pagination;
  const infoEl = document.getElementById("pagination-info");
  const buttonsEl = document.getElementById("pagination-buttons");
  if (!infoEl || !buttonsEl) return;
  if (total_records === 0) {
    infoEl.innerHTML = "";
    buttonsEl.innerHTML = "";
    return;
  }
  const startRecord = (current_page - 1) * limit + 1;
  const endRecord = Math.min(current_page * limit, total_records);
  infoEl.innerHTML = `Menampilkan <strong>${startRecord}</strong>-<strong>${endRecord}</strong> dari <strong>${total_records}</strong> produk`;

  let buttonsHTML = "";
  buttonsHTML += `
        <button 
            class="pagination-btn ${current_page === 1 ? "" : ""}" 
            ${current_page === 1 ? "disabled" : ""}
            data-page="${current_page - 1}"
        >
            <i class="fa-solid fa-chevron-left"></i>
        </button>
    `;

  const maxPagesToShow = 5;
  let startPage = Math.max(1, current_page - Math.floor(maxPagesToShow / 2));
  let endPage = Math.min(total_pages, startPage + maxPagesToShow - 1);
  if (
    endPage - startPage + 1 < maxPagesToShow &&
    total_pages >= maxPagesToShow
  ) {
    startPage = Math.max(1, endPage - maxPagesToShow + 1);
  }

  if (startPage > 1) {
    buttonsHTML += `<button class="pagination-btn" data-page="1">1</button>`;
    if (startPage > 2) {
      buttonsHTML += `<span class="pagination-ellipsis">...</span>`;
    }
  }

  for (let i = startPage; i <= endPage; i++) {
    buttonsHTML += `
            <button 
                class="pagination-btn ${i === current_page ? "active" : ""}"
                data-page="${i}"
            >
                ${i}
            </button>
        `;
  }

  if (endPage < total_pages) {
    if (endPage < total_pages - 1) {
      buttonsHTML += `<span class="pagination-ellipsis">...</span>`;
    }
    buttonsHTML += `<button class="pagination-btn" data-page="${total_pages}">${total_pages}</button>`;
  }

  buttonsHTML += `
        <button 
            class="pagination-btn" 
            ${current_page === total_pages ? "disabled" : ""}
            data-page="${current_page + 1}"
        >
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    `;

  buttonsEl.innerHTML = buttonsHTML;
  buttonsEl.querySelectorAll("button").forEach((button) => {
    button.addEventListener("click", () => {
      const page = parseInt(button.dataset.page);
      if (page !== currentPage) {
        currentPage = page;
        loadTopProducts(); // Panggil tanpa parameter
      }
    });
  });
}

// UBAH FUNGSI INI AGAR MENGGUNAKAN GLOBAL filterParams
async function loadTopProducts() {
  showLoading(true);
  showError("");
  showTable(false);
  try {
    // KIRIM OBJEK filterParams, BUKAN STRING filter
    const result = await api.getTopProductsByCustomer(
      filterParams,
      currentKdCust,
      currentPage,
      LIMIT
    );
    if (result.success === true && result.data) {
      renderProductTable(result.data);
      renderPagination(result.pagination);
      showTable(true);
    } else {
      throw new Error(result.message || "Gagal memuat data produk");
    }
  } catch (error) {
    console.error("Error loading top products:", error);
    showError(`Gagal memuat data: ${error.message}`);
  } finally {
    showLoading(false);
  }
}

async function handleSendVoucherClick(e) {
  const button = e.target.closest(".btn-send-voucher");
  if (!button) {
    return;
  }
  e.stopPropagation();
  const productName = button.dataset.descp;
  const defaultMessage = `Hai! Kami ada voucher diskon spesial untuk produk favorit Anda: ${productName}. \n\Ketik VCR:PROMO10K untuk mengklaim!`;
  const message = prompt(
    "Masukkan pesan yang akan dikirim ke customer:",
    defaultMessage
  );
  if (message === null || message.trim() === "") {
    return;
  }
  button.disabled = true;
  button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
  try {
    const result = await api.sendProactiveMessage(currentKdCust, message);
    if (result.success) {
      Toastify({
        text: "Pesan voucher berhasil dikirim!",
        duration: 3000,
        gravity: "top",
        position: "right",
        style: { background: "#10b981", color: "#fff" },
      }).showToast();
    }
  } catch (error) {
    console.error("Gagal mengirim pesan:", error.message);
  } finally {
    button.disabled = false;
    button.innerHTML = '<i class="fa-solid fa-paper-plane"></i>';
  }
}

async function handleSendGeneralMessageClick() {
  const button = document.getElementById("btn-send-general-wa");
  if (!button) return;
  const defaultMessage = `Hai! Kami ada promo spesial untuk Anda. \n\Ketik VCR:PROMO10K untuk mengklaim!`;
  const message = prompt(
    "Masukkan pesan yang akan dikirim ke customer:",
    defaultMessage
  );
  if (message === null || message.trim() === "") {
    return;
  }
  const originalHtml = button.innerHTML;
  button.disabled = true;
  button.innerHTML =
    '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Mengirim...';
  try {
    const result = await api.sendProactiveMessage(currentKdCust, message);
    if (result.success) {
      Toastify({
        text: "Pesan berhasil dikirim!",
        duration: 3000,
        gravity: "top",
        position: "right",
        style: { background: "#10b981", color: "#fff" },
      }).showToast();
    }
  } catch (error) {
    console.error("Gagal mengirim pesan general:", error.message);
  } finally {
    button.disabled = false;
    button.innerHTML = originalHtml;
  }
}

function showModal(show = true) {
  if (modal) {
    modal.classList.toggle("hidden", !show);
  }
}

function renderModalTable(transactions) {
  modalBody.innerHTML = "";
  if (transactions.length === 0) {
    modalBody.innerHTML = `<tr><td colspan="4" class="text-center text-gray-500 py-4">Tidak ada detail transaksi ditemukan.</td></tr>`;
    return;
  }
  const formatter = new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  });
  const numFormatter = new Intl.NumberFormat("id-ID");

  let htmlRows = "";
  let current_tanggal = null;
  let current_bon = null;
  let subtotal_bon_qty = 0;
  let subtotal_bon_total = 0;

  function buildSubtotalBonRow() {
    if (current_bon === null) return "";
    const row = `
            <tr class="subtotal-row" style="background-color: #FEFCE8; font-style: italic;">
                <td class="px-4 py-2 text-right font-bold text-sm text-yellow-800">
                    Sub Total Bon:
                </td>
                <td class="px-4 py-2 text-right font-bold text-sm text-yellow-800">
                    ${numFormatter.format(subtotal_bon_qty)}
                </td>
                <td class="px-4 py-2"></td>
                <td class="px-4 py-2 text-right font-bold text-sm text-yellow-800">
                    ${formatter.format(subtotal_bon_total)}
                </td>
            </tr>`;
    subtotal_bon_qty = 0;
    subtotal_bon_total = 0;
    return row;
  }

  function buildTanggalHeaderRow(tanggal) {
    return `
            <tr class="header-tanggal-row" style="background-color: #EBF8FF;">
                <td colspan="4" class="px-4 py-2 text-sm font-bold" style="color: #2C5282;">
                    <i class="fa-solid fa-calendar-alt mr-2"></i>
                    Tanggal: ${tanggal}
                </td>
            </tr>`;
  }

  function buildBonHeaderRow(noBon, jam) {
    return `
            <tr class="header-faktur-row" style="background-color: #F7FAFC;">
                <td colspan="2" class="px-4 py-2 pl-6 text-sm font-semibold text-gray-700">
                    No. Bon: ${noBon}
                </td>
                <td colspan="2" class="px-4 py-2 text-sm text-gray-600 text-right">
                    Jam: ${jam}
                </td>
            </tr>`;
  }

  transactions.forEach((tx) => {
    const qty = tx.qty || 0;
    const harga = tx.harga || 0;
    const total_harga = qty * harga;

    if (tx.tgl_trans_date !== current_tanggal) {
      htmlRows += buildSubtotalBonRow();
      current_tanggal = tx.tgl_trans_date;
      current_bon = null;
      htmlRows += buildTanggalHeaderRow(current_tanggal);
    }

    if (tx.no_bon !== current_bon) {
      htmlRows += buildSubtotalBonRow();
      current_bon = tx.no_bon;
      htmlRows += buildBonHeaderRow(current_bon, tx.jam_trs);
    }

    subtotal_bon_qty += qty;
    subtotal_bon_total += total_harga;

    htmlRows += `
            <tr class="item-row hover:bg-gray-50">
                <td class="px-4 py-2 text-sm text-gray-800 text-left">
                    ${tx.descp}
                </td>
                <td class="px-4 py-2 text-sm text-right">
                    ${numFormatter.format(qty)}
                </td>
                <td class="px-4 py-2 text-sm text-right whitespace-nowrap">
                    ${formatter.format(harga)}
                </td>
                <td class="px-4 py-2 text-sm text-right whitespace-nowrap">
                    ${formatter.format(total_harga)}
                </td>
            </tr>
        `;
  });

  htmlRows += buildSubtotalBonRow();
  modalBody.innerHTML = htmlRows;
}

// UBAH FUNGSI INI AGAR MENGGUNAKAN GLOBAL filterParams
async function handleProductRowClick(e) {
  if (e.target.closest(".btn-send-voucher")) {
    return;
  }
  const row = e.target.closest(".product-row");
  if (!row) {
    return;
  }

  const plu = row.dataset.plu;
  const descp = row.dataset.descp;
  showModal(true);
  modalProductName.textContent = `Produk: ${descp} (${plu})`;
  modalBody.innerHTML = "";
  modalSpinner.classList.remove("hidden");

  try {
    // KIRIM OBJEK filterParams, BUKAN STRING filter
    const result = await api.getTransactionDetails(
      filterParams,
      currentKdCust,
      plu
    );
    if (result.success && result.data) {
      renderModalTable(result.data);
    } else {
      throw new Error(result.message || "Data tidak ditemukan");
    }
  } catch (error) {
    console.error("Error fetching transaction details:", error);
    modalBody.innerHTML = `<tr><td colspan="4" class="text-center text-red-500 py-4">Gagal memuat data: ${error.message}</td></tr>`;
  } finally {
    modalSpinner.classList.add("hidden");
  }
}

function initModalElements() {
  modal = document.getElementById("transaction-detail-modal");
  modalCloseBtn = document.getElementById("modal-close-btn");
  modalTitle = document.getElementById("modal-title");
  modalProductName = document.getElementById("modal-product-name");
  modalBody = document.getElementById("modal-table-body");
  modalSpinner = document.getElementById("modal-loading-spinner");

  if (modalCloseBtn) {
    modalCloseBtn.addEventListener("click", () => showModal(false));
  }
  if (modal) {
    modal.addEventListener("click", (e) => {
      if (e.target === modal) {
        showModal(false);
      }
    });
  }
}
// ... (Bagian import dan setup awal tetap sama) ...

async function handleExportExcel() {
  if (!currentKdCust) {
    Swal.fire("Error", "Kode customer tidak ditemukan.", "error");
    return;
  }

  Swal.fire({
    title: "Menyiapkan Excel...",
    text: "Mengambil data dan menghitung total...",
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });

  try {
    const result = await api.getExportCustomerProducts(filterParams, currentKdCust);

    if (!result.success || !result.data || result.data.length === 0) {
      throw new Error("Tidak ada data transaksi ditemukan.");
    }

    const data = result.data;
    const workbook = new ExcelJS.Workbook();
    const sheet = workbook.addWorksheet("Riwayat Belanja");

    // Lebar kolom
    sheet.columns = [
      { key: "A", width: 5 },  // No
      { key: "B", width: 10 }, // Jam
      { key: "C", width: 18 }, // No Bon
      { key: "D", width: 15 }, // PLU
      { key: "E", width: 40 }, // Nama Produk
      { key: "F", width: 10 }, // Qty
      { key: "G", width: 15 }, // Harga
      { key: "H", width: 18 }, // Subtotal
    ];

    // --- Header Judul ---
    sheet.mergeCells("A1:H1");
    const titleCell = sheet.getCell("A1");
    titleCell.value = `RIWAYAT BELANJA: ${currentKdCust}`;
    titleCell.font = { name: "Arial", size: 14, bold: true };
    titleCell.alignment = { horizontal: "center" };

    sheet.mergeCells("A2:H2");
    const subTitleCell = sheet.getCell("A2");
    let filterTxt = filterParams.filter_type === 'custom'
      ? `${filterParams.start_date} s/d ${filterParams.end_date}`
      : `Filter: ${filterParams.filter}`;
    subTitleCell.value = `Periode: ${filterTxt} (Diurutkan berdasarkan Qty Tertinggi per Hari)`;
    subTitleCell.alignment = { horizontal: "center" };

    let currentRow = 4;
    let grandTotalQty = 0;
    let grandTotalOmset = 0;

    // --- Loop Data (Per Tanggal) ---
    data.forEach((group) => {
      // Akumulasi ke Grand Total
      grandTotalQty += group.total_daily_qty;
      grandTotalOmset += group.total_daily_omset;

      // 1. Header Tanggal
      const dateObj = new Date(group.date);
      const dateStr = dateObj.toLocaleDateString('id-ID', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
      });

      sheet.mergeCells(`A${currentRow}:H${currentRow}`);
      const rowDate = sheet.getCell(`A${currentRow}`);
      rowDate.value = `TANGGAL: ${dateStr.toUpperCase()} (${group.date})`;

      // Style Header Tanggal (Kuning)
      rowDate.font = { bold: true, size: 11 };
      rowDate.fill = {
        type: "pattern",
        pattern: "solid",
        fgColor: { argb: "FFFFE082" }, // Amber-200
      };
      rowDate.alignment = { vertical: 'middle', indent: 1 };
      rowDate.border = { top: { style: 'thin' }, bottom: { style: 'thin' } };
      currentRow++;

      // 2. Header Table Items
      const tableHeaders = ["No", "Jam", "No Bon", "PLU", "Nama Produk", "Qty", "Harga", "Subtotal"];
      const rowHeader = sheet.getRow(currentRow);
      rowHeader.values = tableHeaders;

      rowHeader.eachCell((cell) => {
        cell.font = { bold: true };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF3F4F6' } };
        cell.border = { bottom: { style: 'thin' } };
        cell.alignment = { horizontal: 'center' };
      });
      currentRow++;

      // 3. List Item
      group.items.forEach((item, index) => {
        const r = sheet.getRow(currentRow);
        r.values = [
          index + 1,
          item.jam,
          item.no_bon,
          item.plu,
          item.descp,
          item.qty,
          item.harga,
          item.subtotal
        ];
        r.getCell(1).alignment = { horizontal: 'center' };
        r.getCell(2).alignment = { horizontal: 'center' };
        r.getCell(3).alignment = { horizontal: 'center' };
        r.getCell(4).alignment = { horizontal: 'center' };

        r.getCell(6).numFmt = '#,##0'; // Qty
        r.getCell(6).alignment = { horizontal: 'center' };

        r.getCell(7).numFmt = '#,##0'; // Harga
        r.getCell(8).numFmt = '#,##0'; // Subtotal
        currentRow++;
      });

      // 4. Footer Total Harian
      const rowTotal = sheet.getRow(currentRow);
      rowTotal.values = [
        '', '', '', '', 'TOTAL TANGGAL INI:',
        group.total_daily_qty,
        '',
        group.total_daily_omset
      ];

      // Style Total Harian
      const cellLabel = rowTotal.getCell(5);
      cellLabel.font = { bold: true, italic: true, color: { argb: 'FF4B5563' } }; // Gray-600
      cellLabel.alignment = { horizontal: 'right' };

      const cellQty = rowTotal.getCell(6);
      cellQty.font = { bold: true };
      cellQty.alignment = { horizontal: 'center' };
      cellQty.numFmt = '#,##0';
      cellQty.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFECFDF5' } }; // Green-50 (light)

      const cellOmset = rowTotal.getCell(8);
      cellOmset.font = { bold: true };
      cellOmset.numFmt = '#,##0';
      cellOmset.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFECFDF5' } }; // Green-50 (light)

      // Garis pemisah total
      rowTotal.eachCell((cell, colNum) => {
        if (colNum >= 5) cell.border = { top: { style: 'thin' } };
      });

      currentRow += 2; // Spasi antar tanggal
    });

    // --- 5. GRAND TOTAL (Total Keseluruhan) ---
    currentRow++;
    sheet.mergeCells(`A${currentRow}:E${currentRow}`); // Merge label
    const rowGrandTotal = sheet.getRow(currentRow);

    // Label
    const cellGrandLabel = rowGrandTotal.getCell(1);
    cellGrandLabel.value = "GRAND TOTAL (SELURUH PERIODE):";
    cellGrandLabel.font = { bold: true, size: 12, color: { argb: 'FFFFFFFF' } };
    cellGrandLabel.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF059669' } }; // Emerald-600
    cellGrandLabel.alignment = { horizontal: 'right', vertical: 'middle' };

    // Nilai Qty
    const cellGrandQty = rowGrandTotal.getCell(6);
    cellGrandQty.value = grandTotalQty;
    cellGrandQty.font = { bold: true, size: 12, color: { argb: 'FFFFFFFF' } };
    cellGrandQty.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF059669' } };
    cellGrandQty.alignment = { horizontal: 'center', vertical: 'middle' };
    cellGrandQty.numFmt = '#,##0';

    // Spacer
    const cellGrandSpacer = rowGrandTotal.getCell(7);
    cellGrandSpacer.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF059669' } };

    // Nilai Omset
    const cellGrandOmset = rowGrandTotal.getCell(8);
    cellGrandOmset.value = grandTotalOmset;
    cellGrandOmset.font = { bold: true, size: 12, color: { argb: 'FFFFFFFF' } };
    cellGrandOmset.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF059669' } };
    cellGrandOmset.alignment = { horizontal: 'right', vertical: 'middle' };
    cellGrandOmset.numFmt = '#,##0';

    // Tinggi Baris Grand Total
    rowGrandTotal.height = 30;

    // --- Download File ---
    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = window.URL.createObjectURL(blob);
    const anchor = document.createElement("a");
    anchor.href = url;
    const timestamp = new Date().toISOString().slice(0, 10);
    anchor.download = `Riwayat_${currentKdCust}_${timestamp}.xlsx`;
    anchor.click();
    window.URL.revokeObjectURL(url);

    Swal.fire({
      icon: "success",
      title: "Selesai",
      text: "Data berhasil diexport.",
      timer: 1500,
      showConfirmButton: false,
    });

  } catch (error) {
    console.error(error);
    Swal.fire("Error", error.message, "error");
  }
}



document.addEventListener("DOMContentLoaded", () => {
  initModalElements();
  const params = new URLSearchParams(window.location.search);

  // ISI OBJEK filterParams
  filterParams.filter_type = params.get("filter_type");
  filterParams.filter = params.get("filter");
  filterParams.start_date = params.get("start_date");
  filterParams.end_date = params.get("end_date");
  currentKdCust = params.get("kd_cust");

  // PERIKSA filter_type BUKAN filter
  if (filterParams.filter_type && currentKdCust) {
    currentPage = 1;
    loadTopProducts(); // Panggil tanpa parameter
  } else {
    console.error("Filter atau Kode Customer tidak ditemukan di URL.");
    showLoading(false);
    showError("Parameter filter atau kode customer tidak valid.");
  }

  const tableBody = document.getElementById("product-table-body");
  if (tableBody) {
    tableBody.addEventListener("click", handleSendVoucherClick);
    tableBody.addEventListener("click", handleProductRowClick);
  }

  const generalSendButton = document.getElementById("btn-send-general-wa");
  if (generalSendButton) {
    generalSendButton.addEventListener("click", handleSendGeneralMessageClick);
  }
  exportExcelButton = document.getElementById("export-excel-button");
  if (exportExcelButton) {
    exportExcelButton.addEventListener("click", handleExportExcel);
  }
});

