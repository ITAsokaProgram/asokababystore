document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("jadwal-table-body");
  const filterForm = document.getElementById("filter-form");
  const filterSubmitButton = document.getElementById("filter-submit-button");
  const filterSelectStore = document.getElementById("kd_store");
  const filterSelectSync = document.getElementById("sync");
  const summaryTotal = document.getElementById("summary-total");
  const summaryProses = document.getElementById("summary-proses");
  const summarySelesai = document.getElementById("summary-selesai");
  const summaryTunggu = document.getElementById("summary-tunggu");
  const pageSubtitle = document.getElementById("page-subtitle");
  const paginationInfo = document.getElementById("pagination-info");
  const paginationLinks = document.getElementById("pagination-links");
  const exportExcelButton = document.getElementById("export-excel-btn");
  const exportPdfButton = document.getElementById("export-pdf-btn");
  function formatDate(dateString) {
    if (!dateString) return "-";
    const cleanDate = dateString.split(" ")[0];
    const parts = cleanDate.split("-");
    if (parts.length !== 3) return dateString;
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  }
  function getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    const today = new Date().toISOString().split("T")[0];
    return {
      tgl_mulai: params.get("tgl_mulai") || today,
      tgl_selesai: params.get("tgl_selesai") || today,
      kd_store: params.get("kd_store") || "all",
      status: params.get("status") || "all",
      sync: params.get("sync") || "all",
      page: parseInt(params.get("page") || "1", 10),
    };
  }
  async function loadData() {
    const params = getUrlParams();
    const token = getCookie("admin_token");
    setLoadingState(true);
    const queryString = new URLSearchParams(params).toString();
    try {
      const response = await fetch(
        `/src/api/laporan/jadwal_so/get_data.php?${queryString}`,
        {
          headers: {
            Accept: "application/json",
            Authorization: "Bearer " + token,
          },
        }
      );
      if (!response.ok) throw new Error(`HTTP Status: ${response.status}`);
      const data = await response.json();
      if (data.error) throw new Error(data.error);
      if (data.stores && filterSelectStore.options.length <= 1) {
        populateStoreFilter(data.stores, params.kd_store);
      }
      if (filterSelectSync) {
        filterSelectSync.value = params.sync;
      }
      updatePageHeader(params);
      updateSummaryCards(data.summary);
      renderTable(
        data.tabel_data,
        data.pagination ? data.pagination.offset : 0
      );
      renderPagination(data.pagination);
    } catch (error) {
      console.error("Load Data Error:", error);
      showTableError(error.message);
    } finally {
      setLoadingState(false);
    }
  }
  function populateStoreFilter(stores, selectedValue) {
    if (!filterSelectStore) return;
    filterSelectStore.innerHTML = "";
    const defaultOption = new Option("Pilih Cabang", "none");
    filterSelectStore.add(defaultOption);
    const allOption = new Option("SEMUA CABANG", "all");
    filterSelectStore.add(allOption);
    stores.forEach((s) => {
      const option = new Option(s.nama_cabang, s.store);
      filterSelectStore.add(option);
    });
    if (selectedValue) {
      filterSelectStore.value = selectedValue;
    }
  }
  function updatePageHeader(params) {
    let storeName = "Seluruh Store";
    if (filterSelectStore.selectedIndex > 0) {
      storeName =
        filterSelectStore.options[filterSelectStore.selectedIndex].text;
    }
    pageSubtitle.textContent = `Periode: ${params.tgl_mulai} s/d ${params.tgl_selesai} • ${storeName} • Sync: ${params.sync}`;
  }
  function updateSummaryCards(summary) {
    if (!summary) return;
    summaryTotal.textContent = summary.total_jadwal || "0";
    summaryProses.textContent = summary.total_proses || "0";
    summarySelesai.textContent = summary.total_selesai || "0";
    summaryTunggu.textContent = summary.total_tunggu || "0";
  }
  function renderTable(data, offset) {
    if (!data || data.length === 0) {
      tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center p-8 text-gray-500">
                        <i class="fas fa-inbox fa-lg mb-2"></i>
                        <p>Tidak ada jadwal ditemukan.</p>
                    </td>
                </tr>`;
      return;
    }
    let html = "";
    data.forEach((row, index) => {
      const no = offset + index + 1;
      let statusBadge = "";
      const status = row.status || "Tunggu";
      if (status === "Selesai") {
        statusBadge = `<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>`;
      } else if (status === "Proses") {
        statusBadge = `<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Proses</span>`;
      } else {
        statusBadge = `<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">${status}</span>`;
      }
      const syncValue = String(row.sync || "").trim();
      let syncBadge = "";
      if (syncValue === "True") {
        syncBadge = `<span class="text-blue-600"><i class="fas fa-check-circle"></i> Synced</span>`;
      } else {
        syncBadge = `<span class="text-gray-400"><i class="fas fa-clock"></i> Pending</span>`;
      }
      html += `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="text-center">${no}</td>
                    <td class="font-medium">${formatDate(row.Tgl_schedule)}</td>
                    <td>
                        <div class="font-semibold text-gray-700">${
                          row.Kd_Store
                        }</div>
                        <div class="text-xs text-gray-500">${
                          row.Nm_Store || "-"
                        }</div>
                    </td>
                    <td>${row.kode_supp}</td>
                    <td>${row.nama_supp || "-"}</td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center text-xs">${syncBadge}</td>
                </tr>
            `;
    });
    tableBody.innerHTML = html;
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
        </a>`;
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
        linksHtml += `<span class="pagination-ellipsis px-2 text-gray-400">...</span>`;
      }
      linksHtml += `
            <a href="${build_pagination_url(page_num)}" 
               class="pagination-link ${
                 page_num === current_page ? "pagination-active" : ""
               }">
                ${page_num}
            </a>`;
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
        </a>`;
    paginationLinks.innerHTML = linksHtml;
  }
  function setLoadingState(isLoading) {
    if (isLoading) {
      filterSubmitButton.disabled = true;
      filterSubmitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Loading...`;
    } else {
      filterSubmitButton.disabled = false;
      filterSubmitButton.innerHTML = `<i class="fas fa-filter"></i> Tampilkan`;
    }
  }
  function showTableError(msg) {
    tableBody.innerHTML = `<tr><td colspan="7" class="text-center text-red-500 p-4">Error: ${msg}</td></tr>`;
  }
  async function fetchDataForExport() {
    const params = getUrlParams();
    const q = new URLSearchParams({ ...params, export: "true" }).toString();
    const res = await fetch(`/src/api/laporan/jadwal_so/get_data.php?${q}`);
    return await res.json();
  }
  async function exportToExcel() {
    const data = await fetchDataForExport();
    if (!data.tabel_data || data.tabel_data.length === 0)
      return Swal.fire("Info", "Tidak ada data export", "info");
    const rows = data.tabel_data.map((row, i) => ({
      No: i + 1,
      "Tanggal Schedule": formatDate(row.Tgl_schedule),
      "Kode Store": row.Kd_Store,
      "Nama Store": row.Nm_Store,
      "Kode Supplier": row.kode_supp,
      "Nama Supplier": row.nama_supp,
      Status: row.status,
      Sync: row.sync,
    }));
    const ws = XLSX.utils.json_to_sheet(rows);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Jadwal SO");
    XLSX.writeFile(wb, "Laporan_Jadwal_SO.xlsx");
  }
  async function exportToPDF() {
    const data = await fetchDataForExport();
    if (!data.tabel_data || data.tabel_data.length === 0)
      return Swal.fire("Info", "Tidak ada data export", "info");
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF("landscape");
    doc.text("Laporan Jadwal Stock Opname", 14, 15);
    doc.setFontSize(10);
    doc.text(`Dicetak pada: ${new Date().toLocaleString()}`, 14, 22);
    const tableBody = data.tabel_data.map((row, i) => [
      i + 1,
      formatDate(row.Tgl_schedule),
      `${row.Kd_Store} - ${row.Nm_Store}`,
      row.kode_supp,
      row.nama_supp,
      row.status,
      row.sync,
    ]);
    doc.autoTable({
      head: [
        ["No", "Tanggal", "Cabang", "Kode Supp", "Nama Supp", "Status", "Sync"],
      ],
      body: tableBody,
      startY: 25,
      theme: "grid",
      styles: { fontSize: 8 },
    });
    doc.save("Laporan_Jadwal_SO.pdf");
  }
  if (filterForm) {
    filterForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const formData = new FormData(filterForm);
      const p = new URLSearchParams(formData);
      p.set("page", "1");
      window.history.pushState({}, "", `?${p.toString()}`);
      loadData();
    });
  }
  if (exportExcelButton)
    exportExcelButton.addEventListener("click", exportToExcel);
  if (exportPdfButton) exportPdfButton.addEventListener("click", exportToPDF);
  loadData();
});
function build_pagination_url(newPage) {
  const params = new URLSearchParams(window.location.search);
  params.set("page", newPage);
  return "?" + params.toString();
}
