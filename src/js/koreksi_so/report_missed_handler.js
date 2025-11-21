document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("missed-item-table-body");
  const filterForm = document.getElementById("filter-form");
  const filterSubmitButton = document.getElementById("filter-submit-button");
  const filterSelectStore = document.getElementById("kd_store");
  const summaryQty = document.getElementById("summary-qty");
  const paginationInfo = document.getElementById("pagination-info");
  const paginationLinks = document.getElementById("pagination-links");
  const exportExcelButton = document.getElementById("export-excel-btn");
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
    return new Intl.NumberFormat("id-ID").format(number);
  }
  function getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    const now = new Date();
    const thisMonth15 = new Date(now.getFullYear(), now.getMonth(), 15);
    const lastMonth16 = new Date(now.getFullYear(), now.getMonth() - 1, 16);
    const formatDate = (date) => {
      const d = new Date(date);
      let month = "" + (d.getMonth() + 1);
      let day = "" + d.getDate();
      const year = d.getFullYear();
      if (month.length < 2) month = "0" + month;
      if (day.length < 2) day = "0" + day;
      return [year, month, day].join("-");
    };
    const inputMulai = document.getElementById("tgl_mulai");
    const inputSelesai = document.getElementById("tgl_selesai");
    const defaultStart = inputMulai
      ? inputMulai.value
      : formatDate(lastMonth16);
    const defaultEnd = inputSelesai
      ? inputSelesai.value
      : formatDate(thisMonth15);
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
      if (data.stores && filterSelectStore.options.length <= 1) {
        populateStoreFilter(data.stores, params.kd_store);
      } else {
        filterSelectStore.value = params.kd_store;
      }
      if (data.summary) {
        summaryQty.textContent = formatNumber(data.summary.total_items);
      }
      renderTable(data.tabel_data);
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
        if (exportExcelButton)
          exportExcelButton.innerHTML =
            '<i class="fas fa-spinner fa-spin"></i>';
      }
    } else {
      if (filterSubmitButton) {
        filterSubmitButton.disabled = false;
        filterSubmitButton.innerHTML = `<i class="fas fa-filter"></i> Tampilkan`;
      }
      if (exportExcelButton)
        exportExcelButton.innerHTML =
          '<i class="fas fa-file-excel"></i> Export Excel';
    }
  }
  function showTableError(message) {
    tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-8 text-red-600"><i class="fas fa-exclamation-triangle mb-2"></i><p>${message}</p></td></tr>`;
  }
  function populateStoreFilter(stores, selectedStore) {
    filterSelectStore.innerHTML = '<option value="all">Seluruh Store</option>';
    stores.forEach((store) => {
      const option = document.createElement("option");
      option.value = store.kd_store;
      option.textContent = `${store.kd_store} - ${store.nm_alias}`;
      if (store.kd_store === selectedStore) option.selected = true;
      filterSelectStore.appendChild(option);
    });
    filterSelectStore.value = selectedStore;
  }
  function renderTable(tabel_data) {
    if (!tabel_data || tabel_data.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-8 text-gray-500"><i class="fas fa-check-circle fa-lg mb-2"></i><p>Tidak ada item missed (Semua aman atau Stok 0).</p></td></tr>`;
      return;
    }
    let htmlRows = "";
    let current_supp = null;
    let current_tanggal = null;
    let item_counter = 1;
    const buildTanggalHeader = (tanggal) => `
        <tr class="bg-gray-100 border-b border-gray-200">
            <td colspan="6" class="px-4 py-2 font-bold text-gray-700">
                <i class="fas fa-calendar-day text-pink-600 mr-2"></i> Tanggal Jadwal: ${tanggal}
            </td>
        </tr>
    `;
    const buildSupplierHeader = (code, name) => `
        <tr class="bg-white border-b border-gray-100">
            <td colspan="6" class="px-4 py-2 font-semibold text-blue-800 pl-8 bg-blue-50">
                <i class="fas fa-truck text-blue-500 mr-2"></i> ${code} - ${
      name || "Unknown Vendor"
    }
            </td>
        </tr>
    `;
    tabel_data.forEach((row) => {
      if (row.tgl_jadwal !== current_tanggal) {
        current_tanggal = row.tgl_jadwal;
        current_supp = null;
        htmlRows += buildTanggalHeader(current_tanggal);
      }
      if (row.kode_supp !== current_supp) {
        current_supp = row.kode_supp;
        htmlRows += buildSupplierHeader(row.kode_supp, row.nama_supp);
        item_counter = 1;
      }
      const satuanDisplay = row.satuan ? row.satuan : "PCS";
      htmlRows += `
                <tr class="hover:bg-gray-50 transition-colors border-b border-gray-100">
                    <td class="px-4 py-2 text-gray-600 w-12 pl-12  border-r border-gray-50">${item_counter++}</td>
                    <td class="px-4 py-2 font-medium text-gray-900">${
                      row.plu
                    }</td>
                    <td class="px-4 py-2 text-gray-700">${row.deskripsi}</td>
                    <td class="px-4 py-2 text-gray-600 ">${satuanDisplay}</td>
                    <td class="px-4 py-2  font-mono text-red-600 font-bold">${formatNumber(
                      row.stock
                    )}</td>
                    <td class="px-4 py-2  font-mono text-gray-800">${formatRupiah(
                      row.avg_cost
                    )}</td>
                </tr>
            `;
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
  async function fetchExportData() {
    const params = getUrlParams();
    const qs = new URLSearchParams({ ...params, export: "true" }).toString();
    const res = await fetch(`/src/api/koreksi_so/get_missed_items.php?${qs}`);
    if (!res.ok) throw new Error("Network response was not ok");
    return await res.json();
  }
  async function exportToExcel() {
    try {
      setLoadingState(true, true);
      const data = await fetchExportData();
      if (!data.tabel_data || data.tabel_data.length === 0) {
        Swal.fire("Info", "Tidak ada data untuk diexport", "info");
        return;
      }
      const rows = [];
      rows.push(["Laporan Barang Belum Scan (Missed Items)"]);
      rows.push([
        "Periode",
        `${data.params.tgl_mulai} s/d ${data.params.tgl_selesai}`,
      ]);
      rows.push([]);
      const headers = [
        "Kode Supp",
        "Nama Supp",
        "PLU",
        "Deskripsi",
        "Satuan",
        "Stock Komp",
        "Harga Beli",
      ];
      let current_tanggal = null;
      let current_supp = null;
      data.tabel_data.forEach((item) => {
        if (item.tgl_jadwal !== current_tanggal) {
          current_tanggal = item.tgl_jadwal;
          current_supp = null;
          rows.push([`Tanggal: ${current_tanggal}`]);
          rows.push(headers);
        }
        rows.push([
          item.kode_supp,
          item.nama_supp,
          item.plu,
          item.deskripsi,
          item.satuan || "PCS",
          parseFloat(item.stock),
          parseFloat(item.avg_cost),
        ]);
      });
      const ws = XLSX.utils.aoa_to_sheet(rows);
      ws["!cols"] = [
        { wch: 10 },
        { wch: 20 },
        { wch: 15 },
        { wch: 40 },
        { wch: 8 },
        { wch: 10 },
        { wch: 15 },
      ];
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, "Missed Items");
      XLSX.writeFile(wb, `Missed_Items_${data.params.tgl_mulai}.xlsx`);
    } catch (e) {
      console.error(e);
      Swal.fire("Error", "Gagal Export Excel: " + e.message, "error");
    } finally {
      setLoadingState(false, true);
    }
  }
  if (exportExcelButton)
    exportExcelButton.addEventListener("click", exportToExcel);
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
