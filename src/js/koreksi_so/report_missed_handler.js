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
    const inputMode = document.getElementById("mode");
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
      mode: params.get("mode") || (inputMode ? inputMode.value : "jadwal"),
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

    const token = getCookie("admin_token");

    // Pastikan jika kd_store kosong/undefined, defaultnya adalah SEMUA CABANG
    const selectedStore = params.kd_store || "SEMUA CABANG";

    const queryString = new URLSearchParams({
      tgl_mulai: params.tgl_mulai,
      tgl_selesai: params.tgl_selesai,
      kd_store: selectedStore,
      mode: params.mode,
      page: params.page,
    }).toString();

    try {
      const response = await fetch(
        `/src/api/koreksi_so/get_missed_items.php?${queryString}`,
        {
          headers: {
            Accept: "application/json",
            Authorization: "Bearer " + token,
          },
        }
      );

      if (!response.ok)
        throw new Error(`HTTP error! status: ${response.status}`);

      const data = await response.json();
      if (data.error) throw new Error(data.error);

      // --- BAGIAN UPDATE DROPDOWN CABANG ---
      if (data.stores) {
        // Kita isi ulang HANYA jika select-nya kosong atau baru pertama kali load
        if (filterSelectStore.options.length <= 1) {
          filterSelectStore.innerHTML = ""; // Bersihkan dulu

          // 1. Tambahkan "Pilih Cabang" (Value: none)
          const defaultOpt = document.createElement("option");
          defaultOpt.value = "none";
          defaultOpt.text = "Pilih Cabang";
          filterSelectStore.add(defaultOpt);

          // 2. Tambahkan "SEMUA CABANG" (Value: SEMUA CABANG)
          const allOpt = document.createElement("option");
          allOpt.value = "SEMUA CABANG";
          allOpt.text = "SEMUA CABANG";
          filterSelectStore.add(allOpt);

          // 3. Tambahkan daftar cabang dari database
          data.stores.forEach((store) => {
            const option = new Option(store.nm_alias, store.kd_store);
            filterSelectStore.add(option);
          });
        }

        // SINKRONISASI VALUE:
        // Jika value dari URL ada di dalam list, pakai itu.
        // Jika tidak (atau kosong), paksa ke "SEMUA CABANG".
        filterSelectStore.value = selectedStore;

        // Jika setelah di-set ternyata value-nya masih kosong (karena value tidak ditemukan),
        // kembalikan ke SEMUA CABANG
        if (filterSelectStore.value === "") {
          filterSelectStore.value = "SEMUA CABANG";
        }
      }

      const modeEl = document.getElementById("mode");
      if (modeEl && params.mode) {
        modeEl.value = params.mode;
      }

      if (data.summary) {
        summaryQty.textContent = formatNumber(data.summary.total_items);
      }

      const startIndex = data.start_group_index || 0;
      renderTable(data.tabel_data, params.mode, startIndex);
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
  function renderTable(tabel_data, mode, startIndex = 0) {
    if (!tabel_data || tabel_data.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-8 text-gray-500"><i class="fas fa-check-circle fa-lg mb-2"></i><p>Tidak ada item missed (Semua aman atau Stok 0).</p></td></tr>`;
      return;
    }
    const groupedData = {};
    tabel_data.forEach((item) => {
      const key = `${item.tgl_jadwal}_${item.kode_supp}`;
      if (!groupedData[key]) {
        groupedData[key] = {
          tgl_jadwal: item.tgl_jadwal,
          kode_supp: item.kode_supp,
          nama_supp: item.nama_supp,
          items: [],
          total_nilai: 0,
        };
      }
      groupedData[key].items.push(item);
      const val =
        (parseFloat(item.stock) || 0) * (parseFloat(item.avg_cost) || 0);
      groupedData[key].total_nilai += val;
    });
    let htmlRows = "";
    let groupIndex = startIndex + 1;
    for (const key in groupedData) {
      const group = groupedData[key];
      const rowId = `detail-${group.kode_supp}-${groupIndex}`;
      const totalItems = group.items.length;
      htmlRows += `
                <tr class="hover:bg-pink-50 transition-colors border-b border-gray-200 cursor-pointer" onclick="document.getElementById('${rowId}').classList.toggle('hidden');">
                    <td class="px-4 py-3 font-semibold text-gray-600">${groupIndex}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-col">
                            <span class="font-bold text-gray-800">${
                              group.nama_supp || "Unknown"
                            }</span>
                            <span class="text-xs text-gray-500 font-mono">${
                              group.kode_supp
                            }</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-700">
                        <i class="fas fa-calendar-day text-pink-600 mr-1"></i> ${
                          group.tgl_jadwal
                        }
                    </td>
                    <td class="px-4 py-3 font-bold text-blue-600">
                        ${formatNumber(totalItems)} Items
                    </td>
                    <td class="px-4 py-3 font-mono text-gray-700">
                        Rp ${formatRupiah(group.total_nilai)}
                    </td>
                    <td class="px-4 py-3 text-center text-gray-400">
                        <i class="fas fa-chevron-down"></i>
                    </td>
                </tr>
            `;
      htmlRows += `
                <tr id="${rowId}" class="hidden bg-gray-50 shadow-inner">
                    <td colspan="6" class="p-0 border-b border-gray-200">
                        <div class="p-4 pl-12">
                            <table class="w-full text-sm text-left border border-gray-200 rounded-lg bg-white overflow-hidden">
                                <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                                    <tr>
                                        <th class="px-3 py-2">PLU</th>
                                        <th class="px-3 py-2">Deskripsi</th>
                                        <th class="px-3 py-2">Satuan</th>
                                        <th class="px-3 py-2 text-right">Stock</th>
                                        <th class="px-3 py-2" style="text-align: right;">Harga Beli</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
            `;
      group.items.forEach((item) => {
        const satuanDisplay = item.satuan ? item.satuan : "PCS";
        htmlRows += `
                    <tr class="hover:bg-yellow-50">
                        <td class="px-3 py-2 font-mono text-gray-700 font-medium">${
                          item.plu
                        }</td>
                        <td class="px-3 py-2 text-gray-600">${
                          item.deskripsi
                        }</td>
                        <td class="px-3 py-2 text-gray-500 text-xs">${satuanDisplay}</td>
                        <td class="px-3 py-2 font-bold text-red-600">${formatNumber(
                          item.stock
                        )}</td>
                        <td class="px-3 py-2 text-right font-mono">${formatRupiah(
                          item.avg_cost
                        )}</td>
                    </tr>
                `;
      });
      htmlRows += `
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            `;
      groupIndex++;
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
    paginationInfo.textContent = `Menampilkan ${start_row} - ${end_row} dari ${total_rows} items`;
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
      let modeText = "";
      if (data.params.mode === "non_jadwal") {
        modeText = "(Mode Semua Master Tanpa Jadwal)";
      } else if (data.params.mode === "master_exclude_jadwal") {
        modeText = "(Mode Master YANG TIDAK ADA di Jadwal)";
      }
      rows.push(["Laporan Barang Belum Scan " + modeText]);
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
      data.tabel_data.forEach((item) => {
        if (item.tgl_jadwal !== current_tanggal) {
          current_tanggal = item.tgl_jadwal;
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
