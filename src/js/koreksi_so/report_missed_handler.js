document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("missed-item-table-body");
  const filterForm = document.getElementById("filter-form");
  const filterSubmitButton = document.getElementById("filter-submit-button");
  const filterSelectStore = document.getElementById("kd_store");
  const summaryQty = document.getElementById("summary-qty");
  const pageTitle = document.getElementById("page-title");
  const pageSubtitle = document.getElementById("page-subtitle");
  const paginationContainer = document.getElementById("pagination-container");
  const paginationInfo = document.getElementById("pagination-info");
  const paginationLinks = document.getElementById("pagination-links");
  const exportExcelButton = document.getElementById("export-excel-btn");
  const exportPdfButton = document.getElementById("export-pdf-btn");

  function formatRupiah(number) {
    if (isNaN(number) || number === null) return "0";
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(number);
  }

  function formatNumber(number) {
    if (isNaN(number) || number === null) return "0";
    return new Intl.NumberFormat("id-ID").format(number);
  }

  function getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    // Default fallback dates handled in PHP, here we just read inputs if empty
    const now = new Date();
    const thisMonth15 = new Date(now.getFullYear(), now.getMonth(), 15);
    const lastMonth16 = new Date(now.getFullYear(), now.getMonth() - 1, 16);

    const defaultStart = lastMonth16.toISOString().split("T")[0];
    const defaultEnd = thisMonth15.toISOString().split("T")[0];

    return {
      tgl_mulai: params.get("tgl_mulai") || defaultStart,
      tgl_selesai: params.get("tgl_selesai") || defaultEnd,
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
        `/src/api/koreksi_so/get_missed_items.php?${queryString}`
      );
      if (!response.ok)
        throw new Error(`HTTP error! status: ${response.status}`);

      const data = await response.json();
      if (data.error) throw new Error(data.error);

      if (data.stores) populateStoreFilter(data.stores, params.kd_store);

      if (data.summary) {
        summaryQty.textContent = formatNumber(data.summary.total_items);
      }

      renderTable(
        data.tabel_data,
        data.pagination ? data.pagination.offset : 0
      );
      renderPagination(data.pagination);
    } catch (error) {
      console.error("Error loading data:", error);
      showTableError(error.message);
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
      if (!isExporting) {
        if (filterSubmitButton)
          filterSubmitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Loading...`;
        if (!isPagination)
          tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-8"><div class="spinner-simple inline-block w-6 h-6 border-2 border-pink-600 border-t-transparent rounded-full animate-spin"></div><p>Memuat data...</p></td></tr>`;
      } else {
        // Handle export loading text if needed
      }
    } else {
      if (filterSubmitButton) {
        filterSubmitButton.disabled = false;
        filterSubmitButton.innerHTML = `<i class="fas fa-filter"></i> Tampilkan`;
      }
    }
  }

  function showTableError(message) {
    tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-8 text-red-600"><i class="fas fa-exclamation-triangle mb-2"></i><p>${message}</p></td></tr>`;
  }

  function populateStoreFilter(stores, selectedStore) {
    if (filterSelectStore.options.length > 1) return; // Prevent duplicates
    stores.forEach((store) => {
      const option = document.createElement("option");
      option.value = store.kd_store;
      option.textContent = `${store.kd_store} - ${store.nm_alias}`;
      if (store.kd_store === selectedStore) option.selected = true;
      filterSelectStore.appendChild(option);
    });
  }

  function renderTable(tabel_data, offset) {
    if (!tabel_data || tabel_data.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-8 text-gray-500"><i class="fas fa-check-circle fa-lg mb-2"></i><p>Semua item sudah di-scan (Clean).</p></td></tr>`;
      return;
    }

    let htmlRows = "";
    let current_supp = null;
    let item_counter = 1;

    // Helper untuk membuat header Supplier
    const buildSupplierHeader = (code, name) => `
            <tr class="bg-gray-100 border-b border-gray-200">
                <td colspan="6" class="px-4 py-2 font-bold text-gray-800">
                    <i class="fas fa-truck text-pink-600 mr-2"></i> ${code} - ${
      name || "Unknown Vendor"
    }
                </td>
            </tr>
        `;

    tabel_data.forEach((row, index) => {
      // Grouping by Supplier
      if (row.kode_supp !== current_supp) {
        current_supp = row.kode_supp;
        htmlRows += buildSupplierHeader(row.kode_supp, row.nama_supp);
        item_counter = 1; // Reset counter per supplier
      }

      htmlRows += `
                <tr class="hover:bg-pink-50 transition-colors border-b border-gray-100">
                    <td class="px-4 py-2 text-gray-600">${item_counter++}</td>
                    <td class="px-4 py-2 font-medium text-gray-900">${
                      row.plu
                    }</td>
                    <td class="px-4 py-2 text-gray-700">${row.deskripsi}</td>
                    <td class="px-4 py-2 text-gray-600">${row.satuan}</td>
                    <td class="px-4 py-2 text-right font-mono">${formatNumber(
                      row.stock
                    )}</td>
                    <td class="px-4 py-2 text-right font-mono">${formatRupiah(
                      row.avg_cost
                    )}</td>
                </tr>
            `;
    });

    tableBody.innerHTML = htmlRows;
  }

  function renderPagination(pagination) {
    if (!pagination || pagination.total_rows === 0) {
      paginationInfo.textContent = "0 Data";
      paginationLinks.innerHTML = "";
      return;
    }

    const { current_page, total_pages, total_rows, limit, offset } = pagination;
    const start = offset + 1;
    const end = Math.min(offset + limit, total_rows);

    paginationInfo.textContent = `${start}-${end} dari ${total_rows} item`;

    let html = "";
    // Simple prev/next for brevity
    if (current_page > 1)
      html += `<a href="${build_pagination_url(
        current_page - 1
      )}" class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300"><</a>`;
    html += `<span class="px-2 py-1 font-bold">${current_page}</span>`;
    if (current_page < total_pages)
      html += `<a href="${build_pagination_url(
        current_page + 1
      )}" class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300">></a>`;

    paginationLinks.innerHTML = html;
  }

  // --- Export Functions (Simplified Logic) ---
  async function fetchExportData() {
    const params = getUrlParams();
    const qs = new URLSearchParams({ ...params, export: "true" }).toString();
    const res = await fetch(`/src/api/koreksi_so/get_missed_items.php?${qs}`);
    return await res.json();
  }

  async function exportToExcel() {
    try {
      exportExcelButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
      const data = await fetchExportData();
      if (!data.tabel_data.length)
        return Swal.fire("Info", "Tidak ada data", "info");

      const rows = [];
      // Header info
      rows.push(["Laporan Barang Belum Scan"]);
      rows.push([
        "Periode",
        `${data.params.tgl_mulai} s/d ${data.params.tgl_selesai}`,
      ]);
      rows.push([]);
      rows.push([
        "Kode Supp",
        "Nama Supp",
        "PLU",
        "Deskripsi",
        "Satuan",
        "Stock Komp",
        "Harga Beli",
      ]);

      data.tabel_data.forEach((item) => {
        rows.push([
          item.kode_supp,
          item.nama_supp,
          item.plu,
          item.deskripsi,
          item.satuan,
          parseFloat(item.stock),
          parseFloat(item.avg_cost),
        ]);
      });

      const ws = XLSX.utils.aoa_to_sheet(rows);
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, "Missed Items");
      XLSX.writeFile(wb, `Missed_Item_${data.params.tgl_mulai}.xlsx`);
    } catch (e) {
      console.error(e);
      Swal.fire("Error", "Export Gagal", "error");
    } finally {
      exportExcelButton.innerHTML =
        '<i class="fas fa-file-excel"></i> Export Excel';
    }
  }

  if (exportExcelButton)
    exportExcelButton.addEventListener("click", exportToExcel);

  loadData();
});
