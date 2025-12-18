document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("izin-table-body");
  const filterForm = document.getElementById("filter-form");
  const filterSubmitButton = document.getElementById("filter-submit-button");
  const filterSelectStore = document.getElementById("kd_store");
  const summaryQty = document.getElementById("summary-qty");
  const summarySelisih = document.getElementById("summary-selisih");
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
    const today = new Date();
    const lastMonth = new Date();
    lastMonth.setMonth(today.getMonth() - 1);
    const formatDate = (date) => {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const day = String(date.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}`;
    };
    return {
      tgl_mulai: params.get("tgl_mulai") || formatDate(lastMonth),
      tgl_selesai: params.get("tgl_selesai") || formatDate(today),
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
    const bulkActionBar = document.getElementById("bulk-action-bar");
    const params = getUrlParams();
    setLoadingState(true, false, params.page > 1);
    const queryString = new URLSearchParams(params).toString();

    // Mengambil token dari cookie untuk dikirim ke header
    const token = getCookie("admin_token");

    try {
      const response = await fetch(
        `/src/api/approval/get_izin.php?${queryString}`,
        {
          headers: {
            Accept: "application/json",
            Authorization: "Bearer " + token, // Penting agar PHP bisa filter cabang
          },
        }
      );

      if (!response.ok)
        throw new Error(`HTTP error! status: ${response.status}`);

      const data = await response.json();
      if (data.error) throw new Error(data.error);

      // Memperbarui dropdown cabang jika ada data stores
      if (data.stores) {
        // Pastikan fungsi populateStoreFilter menangani format 'kd_store' dan 'nm_alias'
        populateStoreFilter(data.stores, params.kd_store);
      }

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
      bulkActionBar.classList.add("translate-y-24", "opacity-0");
    }
  }

  /** * Fungsi Helper untuk mengisi select (digunakan di dalam loadData)
   * Mengikuti logika dari file get_kode.php yang Anda berikan
   */
  function populateStoreFilter(stores, currentSelected) {
    const select = document.getElementById("cabang") || filterSelectStore;
    if (!select) return;

    // Simpan nilai yang sedang dipilih agar tidak hilang saat re-render
    const valBefore = currentSelected || select.value;

    select.innerHTML = "";

    if (stores.length > 0) {
      const defaultOption = new Option("Pilih Cabang", "none");
      select.add(defaultOption);

      const allOption = new Option("SEMUA CABANG", "all"); // value 'all' sesuai logika PHP get_izin
      select.add(allOption);

      stores.forEach((store) => {
        // Menggunakan properti dari query PHP di atas: kd_store dan nm_alias
        const option = new Option(store.nm_alias, store.kd_store);
        select.add(option);
      });

      // Set kembali nilai yang dipilih
      select.value = valBefore;
    } else {
      select.innerHTML =
        '<option value="none">Gagal memuat data cabang</option>';
    }
  }
  function renderTable(tabel_data, offset) {
    if (!tabel_data || tabel_data.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="10" class=" p-8 text-gray-500"><i class="fas fa-inbox fa-lg mb-2"></i><p>Tidak ada data ditemukan.</p></td></tr>`;
      return;
    }
    let htmlRows = "";
    let current_store = null;
    let current_faktur = null;
    const buildStoreHeader = (kd, alias) => `
            <tr class="bg-orange-50 border-b border-orange-100">
                <td colspan="10" class="px-4 py-2 font-bold text-orange-800">
                     <i class="fas fa-store mr-2"></i> ${alias}
                </td>
            </tr>`;
    const buildFakturHeader = (row, isFullyDisabled) => {
      const fakturGroupId = `group-${row.kd_store}-${row.no_faktur}`;
      const formattedDate = row.tgl_koreksi.split(" ")[0];
      const disabledAttr = isFullyDisabled ? "disabled" : "";
      const opacityClass = isFullyDisabled
        ? "opacity-50 bg-gray-200 cursor-not-allowed"
        : "bg-gray-100 hover:bg-gray-100";
      return `
                <tr class="faktur-header transition-colors ${opacityClass}">
                    <td class=" px-2 py-2 border-r border-gray-200">
                        <input type="checkbox" 
                               class="custom-checkbox faktur-checkbox" 
                               data-group="${fakturGroupId}"
                               title="${
                                 isFullyDisabled
                                   ? "Semua item sudah diproses"
                                   : "Pilih seluruh item di faktur ini"
                               }"
                               ${disabledAttr}>
                    </td>
                    <td colspan="9" class="px-3 py-2 text-gray-700 font-semibold text-sm">
                        <div class="flex items-center gap-4 flex-wrap">
                            <span class="font-mono text-blue-700"><i class="fas fa-file-invoice mr-1"></i> ${
                              row.no_faktur
                            }</span>
                            <span class="text-gray-500 text-xs"><i class="far fa-calendar mr-1"></i> ${formattedDate}</span>
                            ${
                              row.kode_supp
                                ? `<span class="text-gray-500 text-xs"><i class="fas fa-truck mr-1"></i> ${row.kode_supp}</span>`
                                : ""
                            }
                            ${
                              isFullyDisabled
                                ? '<span class="text-[10px] bg-gray-200 text-gray-500 px-2 rounded-full font-medium">Selesai</span>'
                                : ""
                            }
                        </div>
                    </td>
                </tr>
            `;
    };
    tabel_data.forEach((row) => {
      if (row.kd_store !== current_store) {
        current_store = row.kd_store;
        htmlRows += buildStoreHeader(row.kd_store, row.nm_alias);
        current_faktur = null;
      }
      if (row.no_faktur !== current_faktur) {
        current_faktur = row.no_faktur;
        const itemsInThisFaktur = tabel_data.filter(
          (item) =>
            item.no_faktur === row.no_faktur && item.kd_store === row.kd_store
        );
        const isFakturFullyProcessed = itemsInThisFaktur.every(
          (item) =>
            item.izin_koreksi === "Izinkan" || item.izin_koreksi === "SO_Ulang"
        );
        htmlRows += buildFakturHeader(row, isFakturFullyProcessed);
      }
      let statusVal = row.izin_koreksi;
      let badgeClass = "bg-gray-100 text-gray-600 border-gray-200";
      let statusText = "Belum Diproses";
      let actionButtonHtml = "";
      let isProcessed = false;
      let checkboxDisabledAttr = "";
      let rowOpacity = "";
      if (statusVal === "Izinkan") {
        badgeClass = "bg-green-100 text-green-700 border-green-200";
        statusText = "Diizinkan";
        actionButtonHtml = `<span class="text-gray-300 text-lg font-bold">-</span>`;
        isProcessed = true;
      } else if (statusVal === "SO_Ulang") {
        badgeClass = "bg-red-100 text-red-700 border-red-200";
        statusText = "SO Ulang";
        actionButtonHtml = `<span class="text-gray-300 text-lg font-bold">-</span>`;
        isProcessed = true;
      } else {
        actionButtonHtml = `
                    <button onclick="editStatus('${row.no_faktur}', '${
          row.plu
        }', '${row.kd_store}', '${row.izin_koreksi || ""}')" 
                        class="bg-pink-500 hover:bg-pink-600 text-white px-3 py-1 rounded shadow-sm text-[10px] transition-colors">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                `;
      }
      if (isProcessed) {
        checkboxDisabledAttr = "disabled";
        rowOpacity = "opacity-60 bg-gray-50";
      }
      const fakturGroupId = `group-${row.kd_store}-${row.no_faktur}`;
      htmlRows += `
                <tr class="hover:bg-gray-50 text-xs border-b border-gray-100 align-middle ${rowOpacity}">
                    <td class=" px-2 bg-white border-r border-gray-100">
                        <input type="checkbox" 
                               class="custom-checkbox item-checkbox ${fakturGroupId}" 
                               data-parent="${fakturGroupId}"
                               value="${row.no_faktur}|${row.plu}|${
        row.kd_store
      }"
                               ${checkboxDisabledAttr}>
                    </td>
                    <td class="px-2 text-gray-400 text-[10px]">-</td>
                    <td class="font-mono font-semibold px-2 text-gray-700">${
                      row.plu
                    }</td>
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
                    <td class=" px-2 text-gray-400">${row.kode_supp || "-"}</td>
                    <td class=" px-2">
                        <span class="px-2 py-0.5 rounded border text-[10px] font-medium ${badgeClass}">
                            ${statusText}
                        </span>
                    </td>
                    <td class=" px-2 font-mono text-gray-600">
                        ${row.status_k || "-"}
                    </td>
                    <td class=" py-2">
                        ${actionButtonHtml}
                    </td>
                </tr>
            `;
    });
    tableBody.innerHTML = htmlRows;
  }
  tableBody.addEventListener("change", (e) => {
    if (e.target.classList.contains("faktur-checkbox")) {
      const groupId = e.target.dataset.group;
      const isChecked = e.target.checked;
      const activeChildCheckboxes = document.querySelectorAll(
        `.${groupId}:not(:disabled)`
      );
      activeChildCheckboxes.forEach((child) => {
        child.checked = isChecked;
      });
    }
    if (e.target.classList.contains("item-checkbox")) {
      const parentId = e.target.dataset.parent;
      const parentCheckbox = document.querySelector(
        `.faktur-checkbox[data-group="${parentId}"]`
      );
      if (parentCheckbox) {
        const activeSiblings = document.querySelectorAll(
          `.${parentId}:not(:disabled)`
        );
        if (activeSiblings.length > 0) {
          const allChecked = Array.from(activeSiblings).every(
            (cb) => cb.checked
          );
          parentCheckbox.checked = allChecked;
        }
      }
    }
    updateBulkActionBar();
  });
  async function fetchAllDataForExport() {
    setLoadingState(true, true);
    const params = getUrlParams();
    const queryString = new URLSearchParams({
      tgl_mulai: params.tgl_mulai,
      tgl_selesai: params.tgl_selesai,
      kd_store: params.kd_store,
      export: true,
    }).toString();
    try {
      const response = await fetch(
        `/src/api/approval/get_izin.php?${queryString}`
      );
      if (!response.ok)
        throw new Error(`HTTP error! status: ${response.status}`);
      const data = await response.json();
      if (data.error) throw new Error(data.error);
      return data;
    } catch (error) {
      Swal.fire(
        "Export Gagal",
        "Gagal mengambil data: " + error.message,
        "error"
      );
      return null;
    } finally {
      setLoadingState(false);
    }
  }
  async function exportToExcel() {
    const data = await fetchAllDataForExport();
    if (!data || !data.tabel_data || data.tabel_data.length === 0) {
      Swal.fire("Info", "Tidak ada data untuk diekspor.", "info");
      return;
    }
    try {
      const { tabel_data, summary } = data;
      const params = getUrlParams();
      const title = [["Laporan Approval Izin Koreksi"]];
      const info = [
        ["Periode", `${params.tgl_mulai} s/d ${params.tgl_selesai}`],
        ["Total Selisih Qty", parseFloat(summary.total_selisih) || 0],
        [],
      ];
      const headers = [
        "No Faktur",
        "Tgl",
        "PLU",
        "Deskripsi",
        "Selisih",
        "Supp",
        "Status",
        "Status SO",
      ];
      const dataRows = [];
      let current_store = null;
      tabel_data.forEach((row) => {
        if (row.kd_store !== current_store) {
          current_store = row.kd_store;
          dataRows.push([
            `${row.kd_store} - ${row.nm_alias}`,
            "",
            "",
            "",
            "",
            "",
            "",
            "",
          ]);
        }
        let statusText = row.izin_koreksi;
        if (!statusText) statusText = "Pending";
        dataRows.push([
          row.no_faktur,
          row.tgl_koreksi.split(" ")[0],
          row.plu,
          row.deskripsi,
          parseFloat(row.sel_qty),
          row.kode_supp,
          statusText,
          row.status_k || "-",
        ]);
      });
      const ws = XLSX.utils.aoa_to_sheet(title);
      XLSX.utils.sheet_add_aoa(ws, info, { origin: "A2" });
      XLSX.utils.sheet_add_aoa(ws, [headers], { origin: "A6" });
      XLSX.utils.sheet_add_aoa(ws, dataRows, { origin: "A7" });
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, "Data");
      XLSX.writeFile(wb, `Izin_Koreksi_${params.tgl_mulai}.xlsx`);
    } catch (error) {
      Swal.fire("Export Gagal", error.message, "error");
    }
  }
  async function exportToPDF() {
    const data = await fetchAllDataForExport();
    if (!data || !data.tabel_data || data.tabel_data.length === 0) {
      Swal.fire("Info", "Tidak ada data untuk diekspor.", "info");
      return;
    }
    try {
      const { tabel_data } = data;
      const params = getUrlParams();
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();
      doc.setFontSize(16);
      doc.text("Laporan Approval Izin Koreksi", 14, 20);
      doc.setFontSize(10);
      doc.text(
        `Periode: ${params.tgl_mulai} s/d ${params.tgl_selesai}`,
        14,
        28
      );
      const columns = [
        "Faktur",
        "Tgl",
        "PLU",
        "Deskripsi",
        "Selisih",
        "Supp",
        "Status",
        "Sts SO",
      ];
      const rows = [];
      let current_store = null;
      tabel_data.forEach((row) => {
        if (row.kd_store !== current_store) {
          current_store = row.kd_store;
          rows.push([
            {
              content: `${row.kd_store} - ${row.nm_alias}`,
              colSpan: 8,
              styles: {
                fillColor: [255, 240, 200],
                fontStyle: "bold",
                textColor: [0, 0, 0],
              },
            },
          ]);
        }
        let statusText = row.izin_koreksi || "Pending";
        rows.push([
          row.no_faktur,
          row.tgl_koreksi.split(" ")[0],
          row.plu,
          row.deskripsi.substring(0, 20),
          formatNumber(row.sel_qty),
          row.kode_supp || "-",
          statusText,
          row.status_k || "-",
        ]);
      });
      doc.autoTable({
        startY: 35,
        head: [columns],
        body: rows,
        theme: "grid",
        styles: { fontSize: 8, cellPadding: 1 },
        headStyles: { fillColor: [50, 50, 50] },
      });
      doc.save(`Izin_Koreksi_${params.tgl_mulai}.pdf`);
    } catch (error) {
      Swal.fire("Export Gagal", error.message, "error");
    }
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
      if (isExporting) {
        if (exportExcelButton)
          exportExcelButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ...`;
        if (exportPdfButton)
          exportPdfButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ...`;
      } else if (!isPagination && tableBody) {
        tableBody.innerHTML = `<tr><td colspan="9" class=" p-8"><div class="spinner-simple"></div><p>Memuat data...</p></td></tr>`;
      }
    } else {
      if (filterSubmitButton) filterSubmitButton.disabled = false;
      if (exportExcelButton)
        exportExcelButton.innerHTML = `<i class="fas fa-file-excel"></i> <span>Export Excel</span>`;
      if (exportPdfButton)
        exportPdfButton.innerHTML = `<i class="fas fa-file-pdf"></i> <span>Export PDF</span>`;
    }
  }
  function showTableError(msg) {
    tableBody.innerHTML = `<tr><td colspan="9" class=" text-red-500 p-8">Error: ${msg}</td></tr>`;
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
  if (exportExcelButton)
    exportExcelButton.addEventListener("click", exportToExcel);
  if (exportPdfButton) exportPdfButton.addEventListener("click", exportToPDF);
  const bulkActionBar = document.getElementById("bulk-action-bar");
  const selectedCountBadge = document.getElementById("selected-count");
  function updateBulkActionBar() {
    const checkedItems = document.querySelectorAll(
      ".item-checkbox:checked:not(:disabled)"
    );
    const count = checkedItems.length;
    selectedCountBadge.textContent = count;
    if (count > 0) {
      bulkActionBar.classList.remove("translate-y-24", "opacity-0");
    } else {
      bulkActionBar.classList.add("translate-y-24", "opacity-0");
    }
  }
  tableBody.addEventListener("change", (e) => {
    if (e.target.classList.contains("faktur-checkbox")) {
      const groupId = e.target.dataset.group;
      const isChecked = e.target.checked;
      const childCheckboxes = document.querySelectorAll(`.${groupId}`);
      childCheckboxes.forEach((child) => {
        if (!child.disabled) {
          child.checked = isChecked;
        }
      });
    }
    if (e.target.classList.contains("item-checkbox")) {
      const parentId = e.target.dataset.parent;
      const parentCheckbox = document.querySelector(
        `.faktur-checkbox[data-group="${parentId}"]`
      );
      if (parentCheckbox) {
        const allSiblings = document.querySelectorAll(`.${parentId}`);
        const enabledSiblings = Array.from(allSiblings).filter(
          (cb) => !cb.disabled
        );
        if (enabledSiblings.length > 0) {
          const allChecked = enabledSiblings.every((cb) => cb.checked);
          parentCheckbox.checked = allChecked;
        }
      }
    }
    updateBulkActionBar();
  });
  window.bulkUpdateStatus = function (status) {
    const checkedItems = document.querySelectorAll(
      ".item-checkbox:checked:not(:disabled)"
    );
    if (checkedItems.length === 0) return;
    const statusLabel = status === "Izinkan" ? "Diizinkan" : "SO Ulang";
    const color = status === "Izinkan" ? "#16a34a" : "#dc2626";
    Swal.fire({
      title: `Proses ${checkedItems.length} Item?`,
      html: `Anda akan mengubah status menjadi <b>${statusLabel}</b> untuk <b>${checkedItems.length}</b> item terpilih.`,
      icon: "question",
      showCancelButton: true,
      confirmButtonText: `Ya, ${statusLabel}`,
      confirmButtonColor: color,
      cancelButtonText: "Batal",
      showLoaderOnConfirm: true,
      preConfirm: async () => {
        const itemsToUpdate = [];
        checkedItems.forEach((cb) => {
          const parts = cb.value.split("|");
          itemsToUpdate.push({
            no_faktur: parts[0],
            plu: parts[1],
            kd_store: parts[2],
          });
        });
        try {
          const response = await fetch(
            "/src/api/approval/update_status_izin.php",
            {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({
                mode: "bulk",
                status: status,
                items: itemsToUpdate,
              }),
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
          text: `Berhasil memproses ${result.value.affected} item.`,
          icon: "success",
          timer: 1500,
          showConfirmButton: false,
        }).then(() => {
          bulkActionBar.classList.add("translate-y-24", "opacity-0");
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
  loadData();
});
