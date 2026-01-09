document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.getElementById("program-table-body");
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
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(number);
    }

    function formatDate(dateString) {
        if (!dateString || dateString === '0000-00-00') return "-";
        const dateObj = new Date(dateString);
        if (isNaN(dateObj.getTime())) return dateString; // Jika format sudah text/invalid, kembalikan as is
        return dateObj.toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
        });
    }

    // --- LOGIC EXPORT EXCEL ---
    async function handleExportExcel() {
        const params = getUrlParams();
        const currencyFmt = "#,##0.00";
        let periodeText = "";

        if (params.filter_type === "month") {
            const monthNames = [
                "Januari", "Februari", "Maret", "April", "Mei", "Juni",
                "Juli", "Agustus", "September", "Oktober", "November", "Desember",
            ];
            const mIndex = parseInt(params.bulan) - 1;
            periodeText = `BULAN ${monthNames[mIndex].toUpperCase()} ${params.tahun} (TOP)`;
        } else {
            periodeText = `TOP ${params.tgl_mulai} s/d ${params.tgl_selesai}`;
        }

        Swal.fire({
            title: "Menyiapkan Excel...",
            text: "Sedang mengambil data...",
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); },
        });

        try {
            const queryString = new URLSearchParams(params).toString();
            const response = await fetch(
                `/src/api/program_supplier/get_export_program_supplier.php?${queryString}`
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
            const sheet = workbook.addWorksheet("Program Supplier");

            // Definisi Kolom Excel
            sheet.columns = [
                { key: "no", width: 5 },
                { key: "pic", width: 15 },
                { key: "nama_supplier", width: 30 },
                { key: "cabang", width: 20 },
                { key: "periode_program", width: 15 },
                { key: "nama_program", width: 25 },
                { key: "nomor_dokumen", width: 25 },
                { key: "nilai_program", width: 18 },
                { key: "mop", width: 15 },
                { key: "top_date", width: 12 },
                { key: "nilai_transfer", width: 18 },
                { key: "tanggal_transfer", width: 12 },
                { key: "tgl_fpk", width: 12 },
                { key: "nsfp", width: 20 },
                { key: "dpp", width: 18 },
                { key: "ppn", width: 18 },
                { key: "pph", width: 18 },
                { key: "nomor_bukpot", width: 20 },
            ];

            // Header Title
            sheet.mergeCells("A1:R1");
            const titleCell = sheet.getCell("A1");
            titleCell.value = `LAPORAN PROGRAM SUPPLIER - ${periodeText}`;
            titleCell.font = { name: "Arial", size: 14, bold: true };
            titleCell.alignment = { horizontal: "center" };

            // Header Table
            const headers = [
                "No", "PIC", "Supplier", "Cabang", "Periode Prg", "Nama Program", "No Dokumen",
                "Nilai Program", "MOP", "TOP", "Nilai Transfer", "Tgl Transfer",
                "Tgl FPK", "NSFP", "DPP", "PPN", "PPH", "Bukpot"
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
                cell.border = { top: { style: "thin" }, left: { style: "thin" }, bottom: { style: "thin" }, right: { style: "thin" } };
            });

            let rowNum = 4;
            data.forEach((item, index) => {
                const r = sheet.getRow(rowNum);
                const cabangFull = item.nama_cabang ? `${item.kode_cabang} - ${item.nama_cabang}` : item.kode_cabang;

                r.values = [
                    index + 1,
                    item.pic || "",
                    item.nama_supplier || "",
                    cabangFull || "",
                    item.periode_program || "",
                    item.nama_program || "",
                    item.nomor_dokumen || "",
                    parseFloat(item.nilai_program) || 0,
                    item.mop || "",
                    formatDate(item.top_date),
                    parseFloat(item.nilai_transfer) || 0,
                    formatDate(item.tanggal_transfer),
                    formatDate(item.tgl_fpk),
                    item.nsfp || "",
                    parseFloat(item.dpp) || 0,
                    parseFloat(item.ppn) || 0,
                    parseFloat(item.pph) || 0,
                    item.nomor_bukpot || ""
                ];

                // Formatting Number Columns
                [8, 11, 15, 16, 17].forEach(colIdx => {
                    r.getCell(colIdx).numFmt = currencyFmt;
                });

                // Styling row
                r.eachCell((cell) => {
                    cell.alignment = { vertical: "middle", wrapText: true };
                    cell.border = { top: { style: "thin" }, left: { style: "thin" }, bottom: { style: "thin" }, right: { style: "thin" } };
                });
                // Center specific columns
                [1, 9, 10, 12, 13].forEach(colIdx => {
                    r.getCell(colIdx).alignment = { horizontal: "center", vertical: "middle" };
                });

                rowNum++;
            });

            const buffer = await workbook.xlsx.writeBuffer();
            const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
            const url = window.URL.createObjectURL(blob);
            const anchor = document.createElement("a");
            anchor.href = url;
            let filename = `Program_Supplier_${params.filter_type === 'month' ? params.bulan + '_' + params.tahun : params.tgl_mulai + '_to_' + params.tgl_selesai}.xlsx`;
            anchor.download = filename;
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
        const now = new Date();
        const currentMonth = String(now.getMonth() + 1).padStart(2, "0");
        const currentYear = now.getFullYear();
        const firstDay = `${currentYear}-${currentMonth}-01`;
        const today = now.toISOString().split("T")[0];

        return {
            filter_type: params.get("filter_type") || "month",
            bulan: params.get("bulan") || currentMonth,
            tahun: params.get("tahun") || currentYear,
            tgl_mulai: params.get("tgl_mulai") || firstDay,
            tgl_selesai: params.get("tgl_selesai") || today,
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
        setLoadingState(true);

        const queryString = new URLSearchParams(params).toString();

        try {
            const response = await fetch(`/src/api/program_supplier/get_laporan_program_supplier.php?${queryString}`);
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || `HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            if (data.error) throw new Error(data.error);

            if (data.stores) {
                populateStoreFilter(data.stores, params.kd_store);
            }

            // Update UI Filter Values sync with URL
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
                let periodText = "";
                if (params.filter_type === "month") {
                    const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                    const monthName = monthNames[parseInt(params.bulan) - 1];
                    periodText = `Periode TOP: ${monthName} ${params.tahun}`;
                } else {
                    periodText = `Periode TOP: ${formatDate(params.tgl_mulai)} s/d ${formatDate(params.tgl_selesai)}`;
                }
                pageSubtitle.textContent = periodText;
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

    function setLoadingState(isLoading) {
        if (isLoading) {
            if (filterSubmitButton) filterSubmitButton.disabled = true;
            if (filterSubmitButton) filterSubmitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span>Memuat...</span>`;
            if (tableBody) tableBody.innerHTML = `<tr><td colspan="18" class="text-center p-8"><div class="spinner-simple"></div><p class="mt-2 text-gray-500">Memuat data...</p></td></tr>`;
        } else {
            if (filterSubmitButton) {
                filterSubmitButton.disabled = false;
                filterSubmitButton.innerHTML = `<i class="fas fa-filter"></i><span>Tampilkan</span>`;
            }
        }
    }

    function showTableError(message) {
        tableBody.innerHTML = `<tr><td colspan="18" class="text-center p-8 text-red-600"><p>Gagal: ${message}</p></td></tr>`;
    }

    function renderTable(tabel_data, offset) {
        if (!tabel_data || tabel_data.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="18" class="text-center p-8 text-gray-500">
                        <i class="fas fa-inbox fa-lg mb-2"></i>
                        <p>Tidak ada data ditemukan untuk filter ini.</p>
                    </td>
                </tr>`;
            return;
        }

        let htmlRows = "";
        let item_counter = offset + 1;

        tabel_data.forEach((row) => {
            const cabangDisplay = row.nama_cabang ? `${row.kode_cabang}<br><span class="text-[10px] text-gray-500">${row.nama_cabang}</span>` : row.kode_cabang;
            const mopClass = row.mop === 'Transfer' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700';

            htmlRows += `
                <tr class="hover:bg-gray-50 align-top border-b border-gray-100">
                    <td class="text-center font-medium text-gray-500 pt-3">${item_counter}</td>
                    <td class="pt-3 font-medium text-gray-700">${row.pic || "-"}</td>
                    <td class="pt-3 font-semibold text-gray-800">${row.nama_supplier || "-"}</td>
                    <td class="pt-3 text-center leading-tight">${cabangDisplay}</td>
                    <td class="pt-3 text-center text-gray-600">${row.periode_program || "-"}</td>
                    <td class="pt-3 text-gray-700">${row.nama_program || "-"}</td>
                    <td class="pt-3 font-mono text-gray-600 break-all text-[11px]">${row.nomor_dokumen || "-"}</td>
                    <td class="pt-3 text-right font-mono font-medium">${formatRupiah(row.nilai_program)}</td>
                    <td class="pt-3 text-center"><span class="${mopClass} px-2 py-0.5 rounded text-[10px] font-bold border border-opacity-20">${row.mop || "-"}</span></td>
                    <td class="pt-3 text-center text-gray-600 font-mono">${formatDate(row.top_date)}</td>
                    <td class="pt-3 text-right font-mono text-green-700">${formatRupiah(row.nilai_transfer)}</td>
                    <td class="pt-3 text-center text-gray-600 font-mono">${formatDate(row.tanggal_transfer)}</td>
                    <td class="pt-3 text-center text-gray-600 font-mono">${formatDate(row.tgl_fpk)}</td>
                    <td class="pt-3 text-gray-600 font-mono text-[11px]">${row.nsfp || "-"}</td>
                    <td class="pt-3 text-right font-mono text-gray-500">${formatRupiah(row.dpp)}</td>
                    <td class="pt-3 text-right font-mono text-gray-500">${formatRupiah(row.ppn)}</td>
                    <td class="pt-3 text-right font-mono text-gray-500">${formatRupiah(row.pph)}</td>
                    <td class="pt-3 text-gray-600 font-mono text-[11px] text-center">${row.nomor_bukpot || "-"}</td>
                </tr>
            `;
            item_counter++;
        });
        tableBody.innerHTML = htmlRows;
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

        let linksHtml = `
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