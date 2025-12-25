document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.getElementById("ledger-table-body");
    const filterForm = document.getElementById("filter-form");
    const filterSubmitButton = document.getElementById("filter-submit-button");
    const filterSelectStore = document.getElementById("kd_store");
    const filterInputQuery = document.getElementById("search_query");
    
    const pageTitle = document.getElementById("page-title");
    const pageSubtitle = document.getElementById("page-subtitle");
    
    const paginationContainer = document.getElementById("pagination-container");
    const paginationInfo = document.getElementById("pagination-info");
    const paginationLinks = document.getElementById("pagination-links");
    
    const filterTypeSelect = document.getElementById("filter_type");
    const containerMonth = document.getElementById("container-month");
    const containerDateRange = document.getElementById("container-date-range");
    const filterBulan = document.getElementById("bulan");
    const filterTahun = document.getElementById("tahun");
    const filterTglMulai = document.getElementById("tgl_mulai");
    const filterTglSelesai = document.getElementById("tgl_selesai");
    
    const exportExcelButton = document.getElementById("export-excel-button");
  
    if (exportExcelButton) {
      exportExcelButton.addEventListener("click", handleExportExcel);
    }
  
    function toggleFilterMode() {
      const mode = filterTypeSelect.value;
      if (mode === "month") {
        containerMonth.style.display = "contents";
        containerDateRange.style.display = "none";
      } else {
        containerMonth.style.display = "none";
        containerDateRange.style.display = "contents";
      }
    }
  
    if (filterTypeSelect) {
      filterTypeSelect.addEventListener("change", toggleFilterMode);
      toggleFilterMode();
    }
  
    function formatRupiah(number) {
      if (isNaN(number) || number === null) return "0";
      return new Intl.NumberFormat("id-ID", {
        style: "decimal",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(number);
    }
    
    function formatDate(dateString) {
        if (!dateString) return "-";
        const dateObj = new Date(dateString);
        return dateObj.toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
        });
    }

    async function handleExportExcel() {
        const params = getUrlParams();
        const currencyFmt = "#,##0";
        let periodeText = "";
    
        if (params.filter_type === "month") {
            const monthNames = [
                "Januari", "Februari", "Maret", "April", "Mei", "Juni",
                "Juli", "Agustus", "September", "Oktober", "November", "Desember",
            ];
            const mIndex = parseInt(params.bulan) - 1;
            periodeText = `BULAN ${monthNames[mIndex].toUpperCase()} ${params.tahun}`;
        } else {
            periodeText = `${params.tgl_mulai} s/d ${params.tgl_selesai}`;
        }
    
        Swal.fire({
            title: "Menyiapkan Excel...",
            text: "Sedang mengambil seluruh data...",
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); },
        });
    
        try {
            const queryString = new URLSearchParams({
                filter_type: params.filter_type,
                bulan: params.bulan,
                tahun: params.tahun,
                tgl_mulai: params.tgl_mulai,
                tgl_selesai: params.tgl_selesai,
                kd_store: params.kd_store,
                search_query: params.search_query,
            }).toString();
    
            const response = await fetch(
                `/src/api/buku_besar/get_export_laporan_buku_besar.php?${queryString}`
            );
    
            if (!response.ok) throw new Error("Gagal mengambil data export");
            const result = await response.json();
            if (result.error) throw new Error(result.error);
            
            const data = result.data;
            if (!data || data.length === 0) {
                Swal.fire("Info", "Tidak ada data untuk diexport", "info");
                return;
            }
    
            const workbook = new ExcelJS.Workbook();
            const sheet = workbook.addWorksheet("Laporan Buku Besar");
    
            sheet.columns = [
                { key: "no", width: 5 },
                { key: "tgl_nota", width: 12 },
                { key: "no_faktur", width: 25 },
                { key: "cabang", width: 15 },
                { key: "supplier", width: 35 },
                { key: "ket", width: 30 },
                { key: "nilai_faktur", width: 15 },
                { key: "potongan", width: 15 },
                { key: "ket_potongan", width: 20 },
                { key: "total_bayar", width: 15 },
                { key: "tanggal_bayar", width: 12 },
            ];
    
            sheet.mergeCells("A1:K1");
            const titleCell = sheet.getCell("A1");
            titleCell.value = `LAPORAN BUKU BESAR - ${periodeText}`;
            titleCell.font = { name: "Arial", size: 14, bold: true };
            titleCell.alignment = { horizontal: "center" };
    
            const headers = [
                "No", "Tgl Nota", "No Faktur", "Cabang", "Nama Supplier", 
                "Keterangan", "Nilai Faktur", "Potongan", "Ket Potongan", "Total Bayar", "Tgl Bayar"
            ];
    
            const headerRow = sheet.getRow(3);
            headerRow.values = headers;
            headerRow.eachCell((cell) => {
                cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
                cell.fill = {
                    type: "pattern",
                    pattern: "solid",
                    fgColor: { argb: "FFDB2777" }, // Pink Theme
                };
                cell.alignment = { horizontal: "center", vertical: "middle" };
                cell.border = {
                    top: { style: "thin" },
                    left: { style: "thin" },
                    bottom: { style: "thin" },
                    right: { style: "thin" },
                };
            });
    
            let rowNum = 4;
            data.forEach((item, index) => {
                const r = sheet.getRow(rowNum);
                r.values = [
                    index + 1,
                    item.tgl_nota,
                    item.no_faktur,
                    item.Nm_Alias || item.kode_store,
                    item.nama_supplier,
                    item.ket || "",
                    parseFloat(item.nilai_faktur) || 0,
                    parseFloat(item.potongan) || 0,
                    item.ket_potongan || "",
                    parseFloat(item.total_bayar) || 0,
                    item.tanggal_bayar
                ];
                
                // Format Currency Columns
                [7, 8, 10].forEach(idx => {
                    r.getCell(idx).numFmt = currencyFmt;
                });

                r.eachCell((cell) => {
                    cell.border = {
                        top: { style: "thin" },
                        left: { style: "thin" },
                        bottom: { style: "thin" },
                        right: { style: "thin" },
                    };
                });
                rowNum++;
            });
    
            const buffer = await workbook.xlsx.writeBuffer();
            const blob = new Blob([buffer], {
                type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            });
            const url = window.URL.createObjectURL(blob);
            const anchor = document.createElement("a");
            anchor.href = url;
            let filename = `Buku_Besar_`;
            
            if (params.filter_type === "month") {
                filename += `${params.bulan}_${params.tahun}`;
            } else {
                filename += `${params.tgl_mulai}_sd_${params.tgl_selesai}`;
            }
            anchor.download = `${filename}.xlsx`;
            anchor.click();
            window.URL.revokeObjectURL(url);
    
            Swal.fire({
                icon: "success",
                title: "Berhasil",
                text: "Data berhasil diexport ke Excel.",
                timer: 1500,
                showConfirmButton: false,
            });
        } catch (e) {
            console.error(e);
            Swal.fire("Error", e.message, "error");
        }
    }
  
    function getUrlParams() {
      const params = new URLSearchParams(window.location.search);
      const yesterday = new Date();
      yesterday.setDate(yesterday.getDate() - 1);
      const yesterdayString = yesterday.toISOString().split("T")[0];
      const now = new Date();
      const currentMonth = String(now.getMonth() + 1).padStart(2, "0");
      const currentYear = now.getFullYear();
  
      return {
        filter_type: params.get("filter_type") || "month",
        bulan: params.get("bulan") || currentMonth,
        tahun: params.get("tahun") || currentYear,
        tgl_mulai: params.get("tgl_mulai") || yesterdayString,
        tgl_selesai: params.get("tgl_selesai") || yesterdayString,
        kd_store: params.get("kd_store") || "all",
        search_query: params.get("search_query") || "",
        page: parseInt(params.get("page") || "1", 10),
      };
    }
  
    function build_pagination_url(newPage) {
      const params = new URLSearchParams(window.location.search);
      params.set("page", newPage);
      return "?" + params.toString();
    }
  
    function populateStoreFilter(stores, selectedStore) {
      if (!filterSelectStore || filterSelectStore.options.length > 1) {
        filterSelectStore.value = selectedStore;
        return;
      }
      stores.forEach((store) => {
        const option = document.createElement("option");
        option.value = store.kd_store;
        option.textContent = `${store.kd_store} - ${store.nm_alias}`;
        if (store.kd_store === selectedStore) {
          option.selected = true;
        }
        filterSelectStore.appendChild(option);
      });
      filterSelectStore.value = selectedStore;
    }
  
    async function loadData() {
      const params = getUrlParams();
      const isPagination = params.page > 1;
      setLoadingState(true, isPagination);
  
      const queryString = new URLSearchParams({
        filter_type: params.filter_type,
        bulan: params.bulan,
        tahun: params.tahun,
        tgl_mulai: params.tgl_mulai,
        tgl_selesai: params.tgl_selesai,
        kd_store: params.kd_store,
        search_query: params.search_query,
        page: params.page,
      }).toString();
  
      try {
        const response = await fetch(
          `/src/api/buku_besar/get_laporan_buku_besar.php?${queryString}`
        );
  
        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.error || `HTTP error! status: ${response.status}`);
        }
  
        const data = await response.json();
        if (data.error) throw new Error(data.error);
  
        if (data.stores) {
          populateStoreFilter(data.stores, params.kd_store);
        }
  
        // Set Filter Values
        if (filterInputQuery) filterInputQuery.value = params.search_query;
        if (filterTypeSelect) {
            filterTypeSelect.value = params.filter_type;
            toggleFilterMode();
        }
        if (filterBulan) filterBulan.value = params.bulan;
        if (filterTahun) filterTahun.value = params.tahun;
        if (filterTglMulai) filterTglMulai.value = params.tgl_mulai;
        if (filterTglSelesai) filterTglSelesai.value = params.tgl_selesai;
  
        // Update Subtitle
        if (pageSubtitle) {
          let storeName = "";
          if (
            filterSelectStore.options.length > 0 &&
            filterSelectStore.selectedIndex > -1 &&
            filterSelectStore.value !== "all"
          ) {
            storeName = " - " + filterSelectStore.options[filterSelectStore.selectedIndex].text;
          }
  
          let periodText = "";
          if (params.filter_type === "month") {
            const monthNames = [
              "Januari", "Februari", "Maret", "April", "Mei", "Juni",
              "Juli", "Agustus", "September", "Oktober", "November", "Desember",
            ];
            const monthIndex = parseInt(params.bulan) - 1;
            const monthName = monthNames[monthIndex] || params.bulan;
            periodText = `Periode Bulan ${monthName} ${params.tahun}`;
          } else {
            periodText = `Periode ${params.tgl_mulai} s/d ${params.tgl_selesai}`;
          }
          pageSubtitle.textContent = `${periodText}${storeName}`;
        }
  
        renderTable(data.tabel_data, data.pagination ? data.pagination.offset : 0);
        renderPagination(data.pagination);
  
      } catch (error) {
        console.error("Error loading data:", error);
        showTableError(error.message);
      } finally {
        setLoadingState(false);
      }
    }
  
    function setLoadingState(isLoading, isPagination = false) {
      if (isLoading) {
        if (filterSubmitButton) filterSubmitButton.disabled = true;
        if (exportExcelButton) exportExcelButton.disabled = true;
        if (filterSubmitButton)
          filterSubmitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span>Memuat...</span>`;
        if (tableBody)
          tableBody.innerHTML = `<tr><td colspan="10" class="text-center p-8"><div class="spinner-simple"></div><p class="mt-2 text-gray-500">Memuat data...</p></td></tr>`;
        if (paginationInfo) paginationInfo.textContent = "";
        if (paginationLinks) paginationLinks.innerHTML = "";
      } else {
        if (filterSubmitButton) {
          filterSubmitButton.disabled = false;
          filterSubmitButton.innerHTML = `<i class="fas fa-filter"></i><span>Tampilkan</span>`;
        }
        if (exportExcelButton) exportExcelButton.disabled = false;
      }
    }
  
    function showTableError(message) {
      tableBody.innerHTML = `<tr><td colspan="10" class="text-center p-8 text-red-600"><p>Gagal: ${message}</p></td></tr>`;
    }
  
    function renderTable(tabel_data, offset) {
      if (!tabel_data || tabel_data.length === 0) {
        tableBody.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center p-8 text-gray-500">
                        <i class="fas fa-inbox fa-lg mb-2"></i>
                        <p>Tidak ada data ditemukan untuk filter ini.</p>
                    </td>
                </tr>`;
        return;
      }
  
      let htmlRows = "";
      let item_counter = offset + 1;
  
      tabel_data.forEach((row) => {
        const nilaiFaktur = parseFloat(row.nilai_faktur) || 0;
        const potongan = parseFloat(row.potongan) || 0;
        const totalBayar = parseFloat(row.total_bayar) || 0;
        
        let ketPotonganHtml = "";
        if (row.ket_potongan) {
             ketPotonganHtml = `<div class="text-[10px] text-gray-500 italic mt-1">${row.ket_potongan}</div>`;
        }

        htmlRows += `
            <tr class="hover:bg-gray-50">
                <td class="text-center font-medium text-gray-500">${item_counter}</td>
                <td>${formatDate(row.tgl_nota)}</td>
                <td class="font-semibold text-gray-700">${row.no_faktur || "-"}</td>
                <td class="">
                    <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded border border-gray-200">
                        ${row.Nm_Alias || row.kode_store || "-"}
                    </span>
                </td>
                <td>${row.nama_supplier}</td>
                <td class="text-sm text-gray-600 italic ${row.ket ? "cursor-pointer hover:text-pink-600 hover:underline" : ""}" 
                    ${row.ket ? `onclick="showDetailModal('Keterangan', '${(row.ket || "").replace(/'/g, "\\'")}')"` : ""}>
                    ${truncateText(row.ket, 30)}
                </td>
                
                <td class="text-right font-mono text-gray-700">${formatRupiah(nilaiFaktur)}</td>
                
                <td class="text-right font-mono text-red-600">
                    ${potongan > 0 ? '' + formatRupiah(potongan) : '0'}
                    ${ketPotonganHtml}
                </td>
                
                <td class="text-right font-bold text-gray-800">${formatRupiah(totalBayar)}</td>
                
                <td class="text-center text-sm text-gray-600">
                     ${formatDate(row.tanggal_bayar)}
                </td>
            </tr>
        `;
        item_counter++;
      });
  
      tableBody.innerHTML = htmlRows;
    }
  
    // ... Function renderPagination (sama seperti sebelumnya, disingkat untuk hemat tempat) ...
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
                <a href="${current_page > 1 ? build_pagination_url(current_page - 1) : "#"}" 
                   class="pagination-link ${current_page === 1 ? "pagination-disabled" : ""}">
                   <i class="fas fa-chevron-left"></i>
                </a>
            `;
        const pages_to_show = [];
        const max_pages_around = 2;
        for (let i = 1; i <= total_pages; i++) {
          if (i === 1 || i === total_pages || (i >= current_page - max_pages_around && i <= current_page + max_pages_around)) {
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
                       class="pagination-link ${page_num === current_page ? "pagination-active" : ""}">
                       ${page_num}
                    </a>
                `;
          last_page = page_num;
        }
        linksHtml += `
                <a href="${current_page < total_pages ? build_pagination_url(current_page + 1) : "#"}" 
                   class="pagination-link ${current_page === total_pages ? "pagination-disabled" : ""}">
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
  
    loadData();
});
  
function showDetailModal(title, content) {
    window.dispatchEvent(
      new CustomEvent("show-detail-modal", {
        detail: {
          show: true,
          title: title,
          content: content || "-",
        },
      })
    );
}
  
function truncateText(text, maxLength = 30) {
    if (!text || text === "-") return "-";
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + "...";
}
window.showDetailModal = showDetailModal;