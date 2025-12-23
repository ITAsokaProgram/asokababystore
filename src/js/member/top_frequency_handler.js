import * as api from "./member_api_service.js";
let currentPage = 1;
const LIMIT = 10;
const filterParams = {
  filter_type: null,
  filter: null,
  start_date: null,
  end_date: null,
};
let currentStatus = "";
const formatNumber = (num) => {
  return new Intl.NumberFormat("id-ID").format(num || 0);
};
function buildFilterQueryString() {
  const params = new URLSearchParams();
  if (filterParams && filterParams.filter_type) {
    params.append("filter_type", filterParams.filter_type);
    if (filterParams.filter_type === "custom") {
      params.append("start_date", filterParams.start_date);
      params.append("end_date", filterParams.end_date);
    } else {
      params.append("filter", filterParams.filter);
    }
  }
  return params;
}
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
  const tableContainer = document.getElementById("member-table-container");
  const paginationContainer = document.getElementById("pagination-container");
  if (tableContainer) {
    tableContainer.classList.toggle("hidden", !isShown);
  }
  if (paginationContainer) {
    paginationContainer.classList.toggle("hidden", !isShown);
  }
}
function renderMemberTable(members) {
  const tableBody = document.getElementById("member-table-body");
  if (!tableBody) return;
  tableBody.innerHTML = "";
  if (members.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data member ditemukan untuk kriteria ini.</td></tr>`;
    return;
  }
  const formatter = new Intl.NumberFormat("id-ID");
  members.forEach((member, index) => {
    const rank = (currentPage - 1) * LIMIT + index + 1;
    const urlParams = buildFilterQueryString();
    urlParams.append("status", currentStatus);
    urlParams.append("kd_cust", member.kd_cust);
    urlParams.append("nama_cust", member.nama_cust);
    const href = `customer.php?${urlParams.toString()}`;
    const row = `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${rank}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${
                  member.kd_cust
                }</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">${
                  member.nama_cust
                }</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">${formatter.format(
                  member.total_transactions
                )}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-bold">${formatter.format(
                  member.total_poin_customer || 0
                )}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                    <a href="${href}"
                        class="btn-send-voucher bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-md text-xs shadow-sm"
                        title="Lihat Detail Produk Customer">
                        <i class="fa-solid fa-eye"></i>
                    </a>
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
  infoEl.innerHTML = `Menampilkan <strong>${startRecord}</strong>-<strong>${endRecord}</strong> dari <strong>${total_records}</strong> member`;
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
        loadTopMembers();
      }
    });
  });
}
async function loadTopMembers() {
  showLoading(true);
  showError("");
  showTable(false);
  try {
    const result = await api.getTopMembersByFrequency(
      filterParams,
      currentStatus,
      LIMIT,
      currentPage
    );
    if (result.success === true && result.data) {
      renderMemberTable(result.data);
      renderPagination(result.pagination);
      showTable(true);
    } else {
      throw new Error(result.message || "Gagal memuat data member");
    }
  } catch (error) {
    console.error("Error loading top members:", error);
    showError(`Gagal memuat data: ${error.message}`);
  } finally {
    showLoading(false);
  }
}
async function fetchAllDataForExport() {
  try {
    Swal.fire({
      title: "Sedang Memproses...",
      text: "Mengambil semua data untuk export.",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });
    const result = await api.getTopMembersByFrequency(
      filterParams,
      currentStatus,
      0,
      1,
      true
    );
    Swal.close();
    if (result.success && result.data) {
      return result.data;
    } else {
      throw new Error(result.message || "Gagal mengambil data export");
    }
  } catch (error) {
    Swal.fire(
      "Error",
      "Gagal mengambil data export: " + error.message,
      "error"
    );
    return null;
  }
}
async function exportToExcel() {
  const data = await fetchAllDataForExport();
  if (!data || data.length === 0) {
    Swal.fire("Info", "Tidak ada data untuk diekspor.", "info");
    return;
  }
  try {
    const title = [["Laporan Top Member (Frekuensi)"]];
    let periodeText = "";
    if (filterParams.filter_type === "custom") {
      periodeText = `${filterParams.start_date} s/d ${filterParams.end_date}`;
    } else {
      const map = {
        kemarin: "Kemarin",
        "1minggu": "1 Minggu Terakhir",
        "1bulan": "1 Bulan Terakhir",
        "3bulan": "3 Bulan Terakhir",
        "6bulan": "6 Bulan Terakhir",
        "9bulan": "9 Bulan Terakhir",
        "12bulan": "1 Tahun Terakhir",
        semua: "Semua Waktu",
      };
      periodeText = map[filterParams.filter] || "Semua Waktu";
    }
    const info = [
      ["Periode", periodeText],
      [
        "Status",
        currentStatus === "active"
          ? "Aktif"
          : currentStatus === "inactive"
          ? "Inaktif"
          : "Semua",
      ],
      [],
    ];
    const headers = [
      "No",
      "Kode Customer",
      "Nama Customer",
      "Jumlah Transaksi",
      "Total Poin",
    ];
    const dataRows = [];
    data.forEach((row, index) => {
      dataRows.push([
        index + 1,
        row.kd_cust,
        row.nama_cust,
        parseInt(row.total_transactions) || 0,
        parseInt(row.total_poin_customer) || 0,
      ]);
    });
    const ws = XLSX.utils.aoa_to_sheet(title);
    XLSX.utils.sheet_add_aoa(ws, info, { origin: "A2" });
    const headerOrigin = "A" + (info.length + 2);
    XLSX.utils.sheet_add_aoa(ws, [headers], { origin: headerOrigin });
    XLSX.utils.sheet_add_aoa(ws, dataRows, {
      origin: "A" + (info.length + 3),
    });
    ws["A1"].s = {
      font: { bold: true, sz: 14 },
      alignment: { horizontal: "center" },
    };
    ws["!merges"] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 4 } }];
    const headerStyle = {
      font: { bold: true, color: { rgb: "FFFFFF" } },
      fill: { fgColor: { rgb: "4A5568" } },
      alignment: { horizontal: "center" },
    };
    headers.forEach((_, C) => {
      const cellRef = XLSX.utils.encode_cell({ r: info.length + 1, c: C });
      if (!ws[cellRef]) return;
      ws[cellRef].s = headerStyle;
    });
    ws["!cols"] = [
      { wch: 5 },
      { wch: 15 },
      { wch: 35 },
      { wch: 18 },
      { wch: 18 },
    ];
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Top Member Freq");
    const fileName = `TopMember_Freq_${currentStatus}_${new Date().getTime()}.xlsx`;
    XLSX.writeFile(wb, fileName);
  } catch (error) {
    console.error("Error exporting Excel:", error);
    Swal.fire("Export Gagal", "Terjadi kesalahan: " + error.message, "error");
  }
}
async function exportToPDF() {
  const data = await fetchAllDataForExport();
  if (!data || data.length === 0) {
    Swal.fire("Info", "Tidak ada data untuk diekspor.", "info");
    return;
  }
  try {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.setFontSize(16);
    doc.text("Laporan Top Member (Frekuensi)", 14, 20);
    doc.setFontSize(10);
    doc.setTextColor(100);
    let periodeText = "";
    if (filterParams.filter_type === "custom") {
      periodeText = `${filterParams.start_date} s/d ${filterParams.end_date}`;
    } else {
      const map = {
        kemarin: "Kemarin",
        "1minggu": "1 Minggu Terakhir",
        "1bulan": "1 Bulan Terakhir",
        "3bulan": "3 Bulan Terakhir",
        "6bulan": "6 Bulan Terakhir",
        "9bulan": "9 Bulan Terakhir",
        "12bulan": "1 Tahun Terakhir",
        semua: "Semua Waktu",
      };
      periodeText = map[filterParams.filter] || "Semua Waktu";
    }
    doc.text(`Periode: ${periodeText}`, 14, 28);
    doc.text(
      `Status: ${
        currentStatus === "active"
          ? "Aktif"
          : currentStatus === "inactive"
          ? "Inaktif"
          : "Semua"
      }`,
      14,
      34
    );
    const tableHead = [
      ["No", "Kode Customer", "Nama Customer", "Jml Transaksi", "Total Poin"],
    ];
    const tableBody = data.map((row, index) => [
      index + 1,
      row.kd_cust,
      row.nama_cust,
      formatNumber(row.total_transactions),
      formatNumber(row.total_poin_customer),
    ]);
    doc.autoTable({
      margin: { top: 40 },
      head: tableHead,
      body: tableBody,
      theme: "grid",
      headStyles: { fillColor: [66, 153, 225] },
      styles: { fontSize: 8 },
      columnStyles: {
        0: { halign: "center", cellWidth: 10 },
        1: { cellWidth: 30 },
        2: { cellWidth: "auto" },
        3: { halign: "right", cellWidth: 25 },
        4: { halign: "right", cellWidth: 25 },
      },
    });
    const fileName = `TopMember_Freq_${currentStatus}_${new Date().getTime()}.pdf`;
    doc.save(fileName);
  } catch (error) {
    console.error("Error exporting PDF:", error);
    Swal.fire("Export Gagal", "Terjadi kesalahan: " + error.message, "error");
  }
}
document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  filterParams.filter_type = params.get("filter_type");
  filterParams.filter = params.get("filter");
  filterParams.start_date = params.get("start_date");
  filterParams.end_date = params.get("end_date");
  currentStatus = params.get("status");
  const btnExcel = document.getElementById("exportExcelBtn");
  const btnPdf = document.getElementById("exportPdfBtn");
  if (btnExcel) btnExcel.addEventListener("click", exportToExcel);
  if (btnPdf) btnPdf.addEventListener("click", exportToPDF);
  if (filterParams.filter_type && currentStatus) {
    currentPage = 1;
    loadTopMembers();
  } else {
    console.error("Filter atau Status tidak ditemukan di URL.");
    showLoading(false);
    showError("Parameter filter atau status tidak valid.");
  }
});
