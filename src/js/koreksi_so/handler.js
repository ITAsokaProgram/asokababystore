document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("koreksi-table-body");
  const filterForm = document.getElementById("filter-form");
  const filterSubmitButton = document.getElementById("filter-submit-button");
  const filterSelectStore = document.getElementById("kd_store");
  const summaryQty = document.getElementById("summary-qty");
  const summaryNetto = document.getElementById("summary-netto");
  const summaryPPN = document.getElementById("summary-ppn");
  const summaryTotal = document.getElementById("summary-total");
  const pageSubtitle = document.getElementById("page-subtitle");
  const paginationInfo = document.getElementById("pagination-info");
  const paginationLinks = document.getElementById("pagination-links");
  const btnExportExcel = document.getElementById("btn-export-excel");
  if (btnExportExcel) {
    btnExportExcel.addEventListener("click", handleExportExcel);
  }
  window.formatRupiah = function (number) {
    if (isNaN(number) || number === null) return "Rp 0";
    return new Intl.NumberFormat("id-ID", {
      currency: "IDR",
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(number);
  };
  window.formatNumber = function (number) {
    if (isNaN(number) || number === null) return "0";
    return new Intl.NumberFormat("id-ID").format(number);
  };
  function getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    const now = new Date();
    const defaultMulai = new Date();
    defaultMulai.setMonth(now.getMonth() - 1);
    defaultMulai.setDate(16);
    const defaultSelesai = new Date();
    defaultSelesai.setDate(15);
    return {
      tgl_mulai:
        params.get("tgl_mulai") || defaultMulai.toISOString().split("T")[0],
      tgl_selesai:
        params.get("tgl_selesai") || defaultSelesai.toISOString().split("T")[0],
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
    const token = getCookie("admin_token"); // Ambil token dari cookie

    setLoadingState(true, params.page > 1);
    if (!new URLSearchParams(window.location.search).has("tgl_mulai")) {
      document.getElementById("tgl_mulai").value = params.tgl_mulai;
      document.getElementById("tgl_selesai").value = params.tgl_selesai;
    }
    const queryString = new URLSearchParams({
      tgl_mulai: params.tgl_mulai,
      tgl_selesai: params.tgl_selesai,
      kd_store: params.kd_store,
      page: params.page,
    }).toString();
    try {
      const response = await fetch(
        `/src/api/koreksi_so/get_koreksi.php?${queryString}`,
        {
          headers: {
            Accept: "application/json",
            Authorization: "Bearer " + token, // Tambahkan header token di sini
          },
        }
      );
      if (!response.ok)
        throw new Error(`HTTP error! status: ${response.status}`);
      const data = await response.json();
      if (data.error) throw new Error(data.error);
      if (data.stores) populateStoreFilter(data.stores, params.kd_store);

      if (pageSubtitle) {
        // Pastikan elemen select diambil agar tidak error 'options of null'
        const filterSelectStore = document.getElementById("cabang");
        let storeName =
          filterSelectStore?.options[filterSelectStore.selectedIndex]?.text ||
          "Seluruh Cabang";
        pageSubtitle.textContent = `Periode ${params.tgl_mulai} s/d ${params.tgl_selesai} - ${storeName}`;
      }
      if (data.summary) updateSummaryCards(data.summary);
      renderTable(data.tabel_data);
      renderPagination(data.pagination);
    } catch (error) {
      console.error("Error:", error);
      showTableError(error.message);
    } finally {
      setLoadingState(false);
    }
  }
  function setLoadingState(isLoading, isPagination = false) {
    if (isLoading) {
      if (filterSubmitButton) {
        filterSubmitButton.disabled = true;
        filterSubmitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span>Memuat...</span>`;
      }
      if (!isPagination && tableBody) {
        tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-8"><div class="spinner-simple"></div><p class="mt-2 text-gray-500">Memuat data...</p></td></tr>`;
      }
    } else {
      if (filterSubmitButton) {
        filterSubmitButton.disabled = false;
        filterSubmitButton.innerHTML = `<i class="fas fa-filter"></i><span>Tampilkan</span>`;
      }
    }
  }
  function populateStoreFilter(stores, selectedStore) {
    if (filterSelectStore.options.length > 1) {
      if (filterSelectStore.value !== selectedStore)
        filterSelectStore.value = selectedStore;
      return;
    }
    stores.forEach((store) => {
      const option = document.createElement("option");
      option.value = store.kd_store;
      option.textContent = `${store.kd_store} - ${store.nm_alias}`;
      if (store.kd_store === selectedStore) option.selected = true;
      filterSelectStore.appendChild(option);
    });
    filterSelectStore.value = selectedStore;
  }
  function updateSummaryCards(summary) {
    summaryQty.textContent = formatNumber(summary.total_qty);
    summaryNetto.textContent = formatRupiah(summary.total_netto);
    summaryPPN.textContent = formatRupiah(summary.total_ppn);
    summaryTotal.textContent = formatRupiah(summary.total_grand);
  }
  function renderTable(data) {
    if (!data || data.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-8 text-gray-500"><i class="fas fa-inbox fa-lg mb-2"></i><p>Tidak ada data ditemukan.</p></td></tr>`;
      return;
    }
    let html = "";
    let current_tanggal = null;
    data.forEach((row) => {
      if (row.tgl_koreksi !== current_tanggal) {
        current_tanggal = row.tgl_koreksi;
        html += `
            <tr class="bg-pink-50 border-b border-pink-100">
                <td colspan="6" class="px-4 py-2 font-bold text-pink-700 text-sm">
                    <i class="far fa-calendar-alt mr-2"></i> Tanggal Koreksi: ${current_tanggal}
                </td>
            </tr>
        `;
      }
      const qtyClass =
        row.grp_qty < 0 ? "text-red-600 font-bold" : "text-gray-700";
      html += `
        <tr class="hover:bg-gray-50 transition-colors duration-150 border-b border-gray-100">
            <td class="font-semibold text-gray-800 pl-8">${row.kode_supp}</td>
            <td class=" ${qtyClass}">${formatNumber(row.grp_qty)}</td>
            <td class="">${formatRupiah(row.grp_netto)}</td>
            <td class="">${formatRupiah(row.grp_ppn)}</td>
            <td class=" font-bold text-gray-900">${formatRupiah(
              row.grp_total
            )}</td>
            <td class="">
                <button onclick="bukaModalDetail('${row.tgl_koreksi}', '${
        row.kode_supp
      }')"
                    class="btn-secondary-outline px-2 py-1 text-xs rounded hover:bg-pink-50 text-pink-600 border-pink-200 transition-all">
                    <i class="fas fa-list-ul"></i> Detail
                </button>
            </td>
        </tr>
      `;
    });
    tableBody.innerHTML = html;
  }
  window.bukaModalDetail = async (tgl, supp) => {
    window.dispatchEvent(
      new CustomEvent("open-modal", {
        detail: { title: `Detail Koreksi: ${supp} (${tgl})` },
      })
    );
    const store = document.getElementById("kd_store").value;
    try {
      const res = await fetch(
        `/src/api/koreksi_so/get_detail.php?tgl=${tgl}&supp=${supp}&store=${store}`
      );
      const items = await res.json();
      window.dispatchEvent(
        new CustomEvent("update-modal", {
          detail: { items: items },
        })
      );
    } catch (e) {
      console.error(e);
      Swal.fire("Gagal", "Gagal memuat detail data", "error");
    }
  };
  function showTableError(msg) {
    tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-8 text-red-600"><p>Error: ${msg}</p></td></tr>`;
  }
  function renderPagination(pagination) {
    if (!pagination) return;
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
  async function handleExportExcel() {
    const params = getUrlParams(); // Ambil filter saat ini

    // Tampilkan Loading Swal
    Swal.fire({
      title: "Memproses Data",
      text: "Sedang mengambil data dan menyusun Excel...",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });

    try {
      // Fetch data detail dari API baru
      const queryString = new URLSearchParams({
        tgl_mulai: params.tgl_mulai,
        tgl_selesai: params.tgl_selesai,
        kd_store: params.kd_store,
      }).toString();

      const response = await fetch(
        `/src/api/koreksi_so/get_export_data.php?${queryString}`
      );
      const result = await response.json();

      if (!result.success)
        throw new Error(result.error || "Gagal mengambil data");
      if (result.data.length === 0)
        throw new Error("Tidak ada data untuk diexport pada periode ini.");

      // Generate Excel dengan ExcelJS
      await generateExcelFile(result.data, params);

      Swal.close();

      // Notifikasi sukses kecil (Toast)
      const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
      });
      Toast.fire({ icon: "success", title: "Excel berhasil diunduh" });
    } catch (error) {
      console.error(error);
      Swal.fire("Gagal", error.message, "error");
    }
  }

  // 3. Fungsi Generate File (ExcelJS Logic)
  async function generateExcelFile(data, params) {
    const workbook = new ExcelJS.Workbook();
    const sheet = workbook.addWorksheet("Laporan Koreksi SO");

    // Setup Kolom
    sheet.columns = [
      { key: "tgl", width: 12 }, // A
      { key: "store", width: 8 }, // B
      { key: "supp", width: 10 }, // C
      { key: "faktur", width: 15 }, // D
      { key: "plu", width: 10 }, // E
      { key: "desc", width: 35 }, // F
      { key: "qty", width: 8 }, // G
      { key: "cost", width: 15 }, // H
      { key: "ppn", width: 12 }, // I
      { key: "total", width: 18 }, // J
      { key: "ket", width: 20 }, // K (No Kor / Acc)
    ];

    // Styling Variable
    const borderAll = {
      top: { style: "thin" },
      left: { style: "thin" },
      bottom: { style: "thin" },
      right: { style: "thin" },
    };
    const fontHeader = { name: "Arial", size: 10, bold: true };
    const fillHeader = {
      type: "pattern",
      pattern: "solid",
      fgColor: { argb: "FFEEEEEE" }, // Abu-abu muda
    };

    // Header Judul
    sheet.mergeCells("A1:K1");
    sheet.getCell("A1").value = "LAPORAN DETAIL KOREKSI SO";
    sheet.getCell("A1").font = { size: 14, bold: true };
    sheet.getCell("A1").alignment = { horizontal: "center" };

    sheet.mergeCells("A2:K2");
    sheet.getCell(
      "A2"
    ).value = `Periode: ${params.tgl_mulai} s/d ${params.tgl_selesai} | Store: ${params.kd_store}`;
    sheet.getCell("A2").alignment = { horizontal: "center" };

    // Header Tabel (Baris 4)
    const headerRow = sheet.getRow(4);
    headerRow.values = [
      "Tgl Koreksi",
      "Cabang",
      "Supplier",
      "No Faktur",
      "PLU",
      "Barang",
      "Selisih Qty",
      "Avg Cost",
      "PPN",
      "Total",
      "No Koreksi",
    ];

    headerRow.eachCell((cell) => {
      cell.font = fontHeader;
      cell.fill = fillHeader;
      cell.border = borderAll;
      cell.alignment = { vertical: "middle", horizontal: "center" };
    });
    headerRow.height = 25;

    // Isi Data
    let grandTotalQty = 0;
    let grandTotalRupiah = 0;

    data.forEach((item) => {
      grandTotalQty += item.sel_qty;
      grandTotalRupiah += item.total_row;

      const row = sheet.addRow([
        item.tgl_koreksi,
        item.kd_store,
        item.kode_supp,
        item.no_faktur,
        item.plu,
        item.deskripsi,
        item.sel_qty,
        item.avg_cost,
        item.ppn_kor,
        item.total_row,
        `${item.no_kor} (${item.acc_kor})`,
      ]);

      // Formatting per baris
      row.getCell(7).numFmt = "#,##0"; // Qty

      // Warna merah jika qty minus
      if (item.sel_qty < 0) {
        row.getCell(7).font = { color: { argb: "FFFF0000" }, bold: true };
      }

      row.getCell(8).numFmt = "#,##0"; // Cost
      row.getCell(9).numFmt = "#,##0"; // PPN
      row.getCell(10).numFmt = "#,##0"; // Total

      row.eachCell({ includeEmpty: false }, (cell) => {
        cell.border = borderAll;
        cell.font = { name: "Arial", size: 9 };
      });
    });

    // Footer Total
    const totalRow = sheet.addRow([
      "",
      "",
      "",
      "",
      "",
      "GRAND TOTAL",
      grandTotalQty,
      "",
      "",
      grandTotalRupiah,
      "",
    ]);

    totalRow.getCell(6).font = { bold: true };
    totalRow.getCell(6).alignment = { horizontal: "right" };

    totalRow.getCell(7).font = { bold: true };
    totalRow.getCell(7).numFmt = "#,##0";
    totalRow.getCell(7).border = borderAll;

    totalRow.getCell(10).font = { bold: true };
    totalRow.getCell(10).numFmt = "#,##0";
    totalRow.getCell(10).border = borderAll;

    // Download File
    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], {
      type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });
    const url = window.URL.createObjectURL(blob);
    const anchor = document.createElement("a");
    anchor.href = url;
    anchor.download = `Laporan_Koreksi_SO_${params.tgl_mulai}_${params.tgl_selesai}.xlsx`;
    anchor.click();
    window.URL.revokeObjectURL(url);
  }
  loadData();
});
