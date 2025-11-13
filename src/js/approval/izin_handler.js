document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("izin-table-body");
  const filterForm = document.getElementById("filter-form");
  const filterSubmitButton = document.getElementById("filter-submit-button");
  const filterSelectStore = document.getElementById("kd_store");
  const summaryQty = document.getElementById("summary-qty");
  const summarySelisih = document.getElementById("summary-selisih");
  const summaryNilai = document.getElementById("summary-nilai");
  const pageSubtitle = document.getElementById("page-subtitle");
  const paginationInfo = document.getElementById("pagination-info");
  const paginationLinks = document.getElementById("pagination-links");
  const exportExcelButton = document.getElementById("export-excel-btn");
  const exportPdfButton = document.getElementById("export-pdf-btn");
  const formatRupiah = (num) =>
    new Intl.NumberFormat("id-ID", {
      style: "decimal",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(num || 0);
  const formatNumber = (num) =>
    new Intl.NumberFormat("id-ID", {
      minimumFractionDigits: 0,
      maximumFractionDigits: 2,
    }).format(num || 0);
  function getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    return {
      tgl_mulai:
        params.get("tgl_mulai") || yesterday.toISOString().split("T")[0],
      tgl_selesai:
        params.get("tgl_selesai") || yesterday.toISOString().split("T")[0],
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
    setLoadingState(true, false, params.page > 1);
    const queryString = new URLSearchParams(params).toString();
    try {
      const response = await fetch(
        `/src/api/approval/get_izin.php?${queryString}`
      );
      if (!response.ok)
        throw new Error(`HTTP error! status: ${response.status}`);
      const data = await response.json();
      if (data.error) throw new Error(data.error);
      if (data.stores) populateStoreFilter(data.stores, params.kd_store);
      if (pageSubtitle) {
        let storeName = "Seluruh Cabang";
        if (
          filterSelectStore.options.length > 0 &&
          filterSelectStore.selectedIndex > -1
        ) {
          storeName =
            filterSelectStore.options[filterSelectStore.selectedIndex].text;
        }
        pageSubtitle.textContent = `Periode ${params.tgl_mulai} s/d ${params.tgl_selesai} - ${storeName}`;
      }
      if (data.summary) updateSummaryCards(data.summary);
      renderTable(
        data.tabel_data,
        data.pagination ? data.pagination.offset : 0
      );
      renderPagination(data.pagination);
    } catch (error) {
      console.error(error);
      showTableError(error.message);
    } finally {
      setLoadingState(false);
    }
  }
  function renderTable(tabel_data, offset) {
    if (!tabel_data || tabel_data.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="11" class="text-center p-8 text-gray-500"><i class="fas fa-inbox fa-lg mb-2"></i><p>Tidak ada data ditemukan.</p></td></tr>`;
      return;
    }
    let htmlRows = "";
    let current_store = null;
    const buildStoreHeader = (kd, alias) => `
            <tr class="bg-orange-50 border-b border-orange-100">
                <td colspan="10" class="px-4 py-2 font-bold text-orange-800">
                    <i class="fas fa-store mr-2"></i> ${kd} - ${alias}
                </td>
            </tr>`;
    tabel_data.forEach((row) => {
      if (row.kd_store !== current_store) {
        current_store = row.kd_store;
        htmlRows += buildStoreHeader(row.kd_store, row.nm_alias);
      }
      let statusVal = row.izin_koreksi;
      let badgeClass = "bg-gray-100 text-gray-600 border-gray-200";
      let statusText = "Belum Diproses";
      if (statusVal === "Izinkan") {
        badgeClass = "bg-green-100 text-green-700 border-green-200";
        statusText = "Diizinkan";
      } else if (statusVal === "SO_Ulang") {
        badgeClass = "bg-red-100 text-red-700 border-red-200";
        statusText = "SO Ulang";
      }
      htmlRows += `
                <tr class="hover:bg-gray-50 text-xs border-b border-gray-100 align-middle">
                    <td class="text-center py-2">
                        <button onclick="editStatus('${row.no_faktur}', '${
        row.plu
      }', '${row.kd_store}', '${row.izin_koreksi || ""}')" 
                                class="bg-pink-500 hover:bg-pink-600 text-white px-3 py-1 rounded shadow-sm text-[10px] transition-colors">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </td>
                    <td class="text-center text-gray-400 cursor-pointer hover:text-blue-500">
                        <i class="fas fa-eye"></i>
                    </td>
                    <td class="font-mono text-gray-600 px-2">${
                      row.no_faktur
                    }</td>
                    <td class="px-2">${row.tgl_koreksi.split(" ")[0]}</td>
                    <td class="font-mono font-semibold px-2">${row.plu}</td>
                    <td class="truncate max-w-[200px] px-2" title="${
                      row.deskripsi
                    }">${row.deskripsi}</td>
                    <td class="text-right font-bold px-2 ${
                      parseFloat(row.sel_qty) < 0
                        ? "text-red-600"
                        : "text-green-600"
                    }">
                        ${formatNumber(row.sel_qty)}
                    </td>
                    <td class="text-center px-2">${row.kode_supp || "-"}</td>
                    <td class="text-center px-2">
                        <span class="px-2 py-0.5 rounded border text-[10px] font-medium ${badgeClass}">
                            ${statusText}
                        </span>
                    </td>
                </tr>
            `;
    });
    tableBody.innerHTML = htmlRows;
  }
  window.editStatus = function (no_faktur, plu, kd_store, current_status) {
    Swal.fire({
      title: "Update Status Izin",
      text: `Edit status untuk Faktur: ${no_faktur}, PLU: ${plu}`,
      input: "select",
      inputOptions: {
        Izinkan: "Izinkan",
        SO_Ulang: "SO Ulang",
      },
      inputValue: current_status === "null" ? "" : current_status,
      inputPlaceholder: "Pilih status...",
      showCancelButton: true,
      confirmButtonText: "Simpan",
      confirmButtonColor: "#db2777",
      showLoaderOnConfirm: true,
      preConfirm: async (status) => {
        if (!status) {
          Swal.showValidationMessage("Silakan pilih status!");
          return;
        }
        try {
          const response = await fetch(
            "/src/api/approval/update_status_izin.php",
            {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ no_faktur, plu, kd_store, status }),
            }
          );
          if (!response.ok) throw new Error(response.statusText);
          return await response.json();
        } catch (error) {
          Swal.showValidationMessage(`Request failed: ${error}`);
        }
      },
      allowOutsideClick: () => !Swal.isLoading(),
    }).then((result) => {
      if (result.isConfirmed && result.value.success) {
        Swal.fire({
          title: "Berhasil!",
          text: result.value.message,
          icon: "success",
          timer: 1500,
          showConfirmButton: false,
        }).then(() => {
          loadData();
        });
      } else if (result.isConfirmed && !result.value.success) {
        Swal.fire(
          "Gagal",
          result.value.message || "Terjadi kesalahan",
          "error"
        );
      }
    });
  };
  function setLoadingState(
    isLoading,
    isExporting = false,
    isPagination = false
  ) {
    if (isLoading) {
      if (filterSubmitButton) filterSubmitButton.disabled = true;
      if (!isExporting && !isPagination && tableBody) {
        tableBody.innerHTML = `<tr><td colspan="11" class="text-center p-8"><div class="spinner-simple"></div><p>Memuat data...</p></td></tr>`;
      }
    } else {
      if (filterSubmitButton) filterSubmitButton.disabled = false;
    }
  }
  function showTableError(msg) {
    tableBody.innerHTML = `<tr><td colspan="11" class="text-center text-red-500 p-8">Error: ${msg}</td></tr>`;
  }
  function populateStoreFilter(stores, selected) {
    if (filterSelectStore.options.length > 1) return;
    stores.forEach((s) => {
      const opt = document.createElement("option");
      opt.value = s.kd_store;
      opt.textContent = `${s.kd_store} - ${s.nm_alias}`;
      if (s.kd_store === selected) opt.selected = true;
      filterSelectStore.appendChild(opt);
    });
  }
  function updateSummaryCards(summary) {
    summaryQty.textContent = formatNumber(summary.total_item);
    summarySelisih.textContent = formatNumber(summary.total_selisih);
    summaryNilai.textContent = formatRupiah(summary.total_nilai);
  }
  function renderPagination(pagination) {
    if (!pagination || pagination.total_rows === 0) {
      paginationInfo.textContent = "0 data";
      paginationLinks.innerHTML = "";
      return;
    }
    const { current_page, total_pages, offset, limit, total_rows } = pagination;
    paginationInfo.textContent = `${offset + 1}-${Math.min(
      offset + limit,
      total_rows
    )} dari ${total_rows}`;
    let html = `<a href="${
      current_page > 1 ? build_pagination_url(current_page - 1) : "#"
    }" class="pagination-link ${
      current_page === 1 ? "pagination-disabled" : ""
    }"><i class="fas fa-chevron-left"></i></a>`;
    html += `<a href="${
      current_page < total_pages ? build_pagination_url(current_page + 1) : "#"
    }" class="pagination-link ${
      current_page === total_pages ? "pagination-disabled" : ""
    }"><i class="fas fa-chevron-right"></i></a>`;
    paginationLinks.innerHTML = html;
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
