import { sendRequestGET, sendRequestJSON } from "../utils/api_helpers.js";
document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.getElementById("program-table-body");
    const filterForm = document.getElementById("filter-form");
    const filterSubmitButton = document.getElementById("filter-submit-button");
    const filterSelectStore = document.getElementById("kd_store");
    const filterStatusBukpot = document.getElementById("status_bukpot");
    const filterInputQuery = document.getElementById("search_query");
    const pageTitle = document.getElementById("page-title");
    const pageSubtitle = document.getElementById("page-subtitle");
    const paginationContainer = document.getElementById("pagination-container");
    const paginationInfo = document.getElementById("pagination-info");
    const paginationLinks = document.getElementById("pagination-links");
    const exportExcelButton = document.getElementById("export-excel-button");
    const modalFinance = document.getElementById("modal-finance");
    const modalTax = document.getElementById("modal-tax");
    const formFinance = document.getElementById("form-finance");
    const formTax = document.getElementById("form-tax");
    function formatInputNumber(num) {
        if (!num && num !== 0) return "";
        let str = num.toString().replace(".", ",");
        return new Intl.NumberFormat("id-ID", { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(num);
    }
    function parseInputNumber(str) {
        if (!str) return 0;
        return parseFloat(str.toString().replace(/\./g, "").replace(",", ".")) || 0;
    }
    const moneyInputs = ["fin_nilai_transfer", "tax_dpp", "tax_ppn", "tax_pph"];
    moneyInputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener("blur", (e) => {
                e.target.value = formatInputNumber(parseInputNumber(e.target.value));
            });
            el.addEventListener("focus", (e) => e.target.select());
        }
    });
    if (exportExcelButton) {
        exportExcelButton.addEventListener("click", handleExportExcel);
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
        if (!dateString || dateString === '0000-00-00') return "-";
        const dateObj = new Date(dateString);
        if (isNaN(dateObj.getTime())) return dateString;
        return dateObj.toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
        });
    }
    function formatMultiLine(str) {
        if (!str) return "-";
        return str.split(',').map(s => `<div class="py-0.5 whitespace-normal break-words" style="max-width: 200px;">${s.trim()}</div>`).join('');
    }
    function getUrlParams() {
        const params = new URLSearchParams(window.location.search);
        return {
            kd_store: params.get("kd_store") || "all",
            status_bukpot: params.get("status_bukpot") || "all", 
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
        if (!filterSelectStore || (filterSelectStore.options.length > 1 && filterSelectStore.getAttribute('data-loaded') === 'true')) {
            filterSelectStore.value = selectedStore;
            return;
        }
        filterSelectStore.innerHTML = '<option value="all">Seluruh Store</option>';
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
        filterSelectStore.setAttribute('data-loaded', 'true');
    }
    function setLoadingState(isLoading) {
        if (isLoading) {
            if (filterSubmitButton) {
                filterSubmitButton.disabled = true;
                filterSubmitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span>Memuat...</span>`;
            }
            if (tableBody) {
                tableBody.innerHTML = `<tr><td colspan="20" class="text-center p-8"><div class="spinner-simple"></div><p class="mt-2 text-gray-500">Memuat data...</p></td></tr>`;
            }
        } else {
            if (filterSubmitButton) {
                filterSubmitButton.disabled = false;
                filterSubmitButton.innerHTML = `<i class="fas fa-filter"></i><span>Tampilkan</span>`;
            }
        }
    }
    function showTableError(message) {
        tableBody.innerHTML = `<tr><td colspan="20" class="text-center p-8 text-red-600"><div class="p-4 bg-red-50 rounded border border-red-100"><strong>Gagal:</strong> ${message}</div></td></tr>`;
    }
    async function loadData() {
        const params = getUrlParams();
        setLoadingState(true);
        const queryString = new URLSearchParams(params).toString();
        try {
            const response = await fetch(`/src/api/program_supplier/get_laporan_program_supplier.php?${queryString}`);
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.error || `HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            if (data.error) throw new Error(data.error);
            if (data.stores) {
                populateStoreFilter(data.stores, params.kd_store);
            }
            if (filterStatusBukpot) {
                filterStatusBukpot.value = params.status_bukpot;
            }
            if (filterInputQuery) filterInputQuery.value = params.search_query;
            if (pageSubtitle) {
                pageSubtitle.textContent = "Menampilkan seluruh data";
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
    function renderTable(tabel_data, offset) {
        if (!tabel_data || tabel_data.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="20" class="text-center p-8 text-gray-500">
                        <i class="fas fa-inbox fa-lg mb-2 text-gray-300 block"></i>
                        <p>Tidak ada data ditemukan.</p>
                    </td>
                </tr>`;
            return;
        }
        let htmlRows = "";
        tabel_data.forEach((row) => {
            const cabangDisplay = row.nama_cabang ? `<span class="text-[10px] text-gray-500">${row.nama_cabang}</span>` : row.kode_cabang;
            let mopClass = '';
            if (row.mop === 'Transfer') {
                mopClass = 'bg-blue-100 text-blue-700 border-blue-200';
            } else if (row.mop === 'Cash') {
                mopClass = 'bg-green-100 text-green-700 border-green-200';
            } else {
                mopClass = 'bg-orange-100 text-orange-700 border-orange-200';
            }
            const picFormatted = formatMultiLine(row.pic);
            const docFormatted = formatMultiLine(row.nomor_dokumen);
            const rowDataEncoded = encodeURIComponent(JSON.stringify(row));
            const noProgDisplay = row.nomor_program
                ? `<div class="font-mono text-xs font-bold text-purple-700 bg-purple-50 px-2 py-1 rounded inline-block whitespace-nowrap border border-purple-100 shadow-sm">${row.nomor_program}</div>`
                : '-';
            const ppnBadge = row.status_ppn === 'PPN'
                ? '<span class="text-[10px] font-bold text-teal-700 bg-teal-50 border border-teal-200 px-2 py-0.5 rounded uppercase tracking-wider">PPN</span>'
                : '<span class="text-[10px] font-bold text-gray-500 bg-gray-100 border border-gray-200 px-2 py-0.5 rounded uppercase tracking-wider">Non</span>';
            let keteranganDisplay = "-";
            if (row.keterangan) {
                const safeKeterangan = row.keterangan.replace(/'/g, "\\'");
                const safeTitle = `Keterangan - ${row.nomor_program || ''}`.replace(/'/g, "\\'");
                
                keteranganDisplay = `
                    <div class="truncate cursor-pointer text-gray-600 hover:text-pink-600 border-b border-transparent hover:border-pink-300 transition-all"
                        title="Klik untuk baca selengkapnya" 
                        style="max-width: 200px;"
                        onclick="window.dispatchEvent(new CustomEvent('show-detail-modal', { 
                            detail: { 
                                title: '${safeTitle}', 
                                content: '${safeKeterangan}' 
                            } 
                        }))">
                        ${row.keterangan}
                    </div>
                `;
            }
            htmlRows += `
                <tr class="hover:bg-gray-50 align-top border-b border-gray-100 transition-colors">
                    <td class="text-center py-3 align-top whitespace-nowrap">
                        <div class="flex items-center justify-center gap-1">
                            <button type="button" onclick="window.openFinanceModal('${rowDataEncoded}')" 
                                class="inline-flex items-center justify-center w-7 h-7 transition-all border rounded shadow-sm bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-800 border-blue-200" 
                                title="Update Finance">
                                <i class="fas fa-wallet text-xs"></i>
                            </button>
                            <button type="button" onclick="window.openTaxModal('${rowDataEncoded}')" 
                                class="inline-flex items-center justify-center w-7 h-7 transition-all border rounded shadow-sm bg-purple-50 text-purple-600 hover:bg-purple-100 hover:text-purple-800 border-purple-200" 
                                title="Update Tax">
                                <i class="fas fa-file-invoice-dollar text-xs"></i>
                            </button>
                        </div>
                    </td>
                    <td class="text-sm py-3 align-top">
                        ${noProgDisplay}
                    </td>
                    <td class="pt-3 font-medium text-gray-700 align-top text-[11px] leading-relaxed">
                        ${picFormatted}
                    </td>
                    <td class="pt-3 align-top">
                        <div class="flex flex-col gap-1">
                            <div class="font-bold text-gray-800 leading-tight text-xs">${row.nama_supplier || '-'}</div>
                            ${row.npwp
                    ? `<div class="text-[10px] text-gray-500 font-mono flex items-center gap-1 bg-gray-50 w-fit px-1.5 py-0.5 rounded border border-gray-100"><i class="fas fa-id-card text-gray-400"></i> ${row.npwp}</div>`
                    : ''}
                        </div>
                    </td>
                    <td class="pt-3 text-center leading-tight align-top text-[11px]">${cabangDisplay}</td>
                    <td class="pt-3 text-center align-top">
                        ${ppnBadge}
                    </td>
                    <td class="pt-3 text-center text-gray-600 align-top">${row.periode_program || "-"}</td>
                    <td class="pt-3 text-gray-700 align-top font-medium">${row.nama_program || "-"}</td>
                    <td class="pt-3 font-mono text-gray-600 text-[11px] align-top leading-relaxed text-blue-800" >
                        ${docFormatted}
                    </td>
                    <td class="pt-3 text-right font-mono font-medium align-top">${formatRupiah(row.nilai_program)}</td>
                    <td class="pt-3 text-center align-top">
                        <span class="${mopClass} px-2 py-0.5 rounded text-[10px] font-bold border border-opacity-50 inline-block">${row.mop || "-"}</span>
                    </td>
                    <td class="pt-3 text-center text-gray-600 font-mono align-top text-[11px]">${formatDate(row.top_date)}</td>
                    <td class="pt-3 text-right font-mono text-green-700 align-top font-medium">${formatRupiah(row.nilai_transfer)}</td>
                    <td class="pt-3 text-center text-gray-600 font-mono align-top text-[11px]">${formatDate(row.tanggal_transfer)}</td>
                    <td class="pt-3 text-center text-gray-600 font-mono align-top text-[11px]">${formatDate(row.tgl_fpk)}</td>
                    <td class="pt-3 text-gray-600 font-mono text-[11px] align-top whitespace-nowrap">${row.nsfp || "-"}</td>
                    <td class="pt-3 text-right font-mono text-gray-500 align-top text-[11px]">${formatRupiah(row.dpp)}</td>
                    <td class="pt-3 text-right font-mono text-gray-500 align-top text-[11px]">${formatRupiah(row.ppn)}</td>
                    <td class="pt-3 text-right font-mono text-gray-500 align-top text-[11px]">${formatRupiah(row.pph)}</td>
                    <td class="pt-3 text-gray-600 font-mono text-[11px] text-center align-top whitespace-nowrap">${row.nomor_bukpot || "-"}</td>
                    <td class="pt-3 text-[11px] align-top font-mono">
                        ${keteranganDisplay}
                    </td>
                </tr>
            `;
        });
        tableBody.innerHTML = htmlRows;
    }
    window.openFinanceModal = (rowDataEncoded) => {
        const row = JSON.parse(decodeURIComponent(rowDataEncoded));
        document.getElementById("fin_nomor_dokumen").value = row.nomor_dokumen;
        document.getElementById("fin_display_doc").textContent = row.nomor_dokumen;
        document.getElementById("fin_nilai_transfer").value = formatInputNumber(row.nilai_transfer);
        document.getElementById("fin_tanggal_transfer").value = row.tanggal_transfer || "";
        modalFinance.classList.remove("hidden");
    };
    window.openTaxModal = (rowDataEncoded) => {
        const row = JSON.parse(decodeURIComponent(rowDataEncoded));
        document.getElementById("tax_nomor_dokumen").value = row.nomor_dokumen;
        document.getElementById("tax_display_doc").textContent = row.nomor_dokumen;
        document.getElementById("tax_tgl_fpk").value = row.tgl_fpk || "";
        document.getElementById("tax_nsfp").value = row.nsfp || "";
        document.getElementById("tax_dpp").value = formatInputNumber(row.dpp);
        document.getElementById("tax_ppn").value = formatInputNumber(row.ppn);
        document.getElementById("tax_pph").value = formatInputNumber(row.pph);
        document.getElementById("tax_nomor_bukpot").value = row.nomor_bukpot || "";
        modalTax.classList.remove("hidden");
    };
    document.querySelectorAll(".btn-close-finance").forEach(btn => {
        btn.addEventListener("click", () => modalFinance.classList.add("hidden"));
    });
    document.querySelectorAll(".btn-close-tax").forEach(btn => {
        btn.addEventListener("click", () => modalTax.classList.add("hidden"));
    });
    async function handleUpdateSubmit(event, endpoint, modalEl) {
        event.preventDefault();
        const form = event.target;
        const submitBtn = form.querySelector("button[type='submit']");
        const originalText = submitBtn.innerHTML;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        if (data.mode === 'tax') {
            const cleanNsfp = (data.nsfp || "").replace(/\D/g, ""); 
            const cleanBukpot = (data.nomor_bukpot || "").replace(/[^a-zA-Z0-9]/g, "");
            if (data.nsfp && cleanNsfp.length !== 17) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Input Tidak Lengkap',
                    text: `Kolom NSFP harus berisi 17 digit angka! (Saat ini: ${cleanNsfp.length} digit)`,
                    confirmButtonColor: '#d33'
                });
                return;
            }
            if (data.nomor_bukpot && cleanBukpot.length !== 9) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Input Tidak Lengkap',
                    text: `Nomor Bukti Potong harus berisi 9 karakter (huruf & angka)! (Saat ini: ${cleanBukpot.length} karakter)`,
                    confirmButtonColor: '#d33'
                });
                return;
            }
        }
        if (data.nilai_transfer) data.nilai_transfer = parseInputNumber(data.nilai_transfer);
        if (data.dpp) data.dpp = parseInputNumber(data.dpp);
        if (data.ppn) data.ppn = parseInputNumber(data.ppn);
        if (data.pph) data.pph = parseInputNumber(data.pph);
        if (data.nsfp) {
            data.nsfp = data.nsfp.replace(/[^a-zA-Z0-9]/g, "");
        }
        if (data.nomor_bukpot) {
            data.nomor_bukpot = data.nomor_bukpot.replace(/[^a-zA-Z0-9]/g, "");
        }
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Menyimpan...`;
        try {
            const result = await sendRequestJSON("/src/api/program_supplier/update_program_supplier_partial.php", data);
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: result.message,
                    timer: 1000,
                    showConfirmButton: false
                });
                modalEl.classList.add("hidden");
                loadData();
            } else {
                throw new Error(result.error || "Gagal update data");
            }
        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }
    if (formFinance) {
        formFinance.addEventListener("submit", (e) => handleUpdateSubmit(e, null, modalFinance));
    }
    if (formTax) {
        formTax.addEventListener("submit", (e) => handleUpdateSubmit(e, null, modalTax));
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
    async function handleExportExcel() {
        const params = getUrlParams();
        const currencyFmt = "#,##0";
        let periodeText = "SEMUA DATA";
        Swal.fire({
            title: "Menyiapkan Excel...",
            text: "Sedang mengambil data...",
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); },
        });
        try {
            const queryString = new URLSearchParams(params).toString();
            const response = await fetch(`/src/api/program_supplier/get_export_program_supplier.php?${queryString}`);
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
            sheet.columns = [
                { key: "nomor_program", width: 25 }, 
                { key: "pic", width: 25 },
                { key: "nama_supplier", width: 35 },
                { key: "npwp", width: 20 }, 
                { key: "cabang", width: 25 },
                { key: "status_ppn", width: 15 }, 
                { key: "periode_program", width: 15 },
                { key: "nama_program", width: 30 },
                { key: "nomor_dokumen", width: 30 },
                { key: "nilai_program", width: 18 },
                { key: "mop", width: 15 },
                { key: "top_date", width: 15 },
                { key: "nilai_transfer", width: 18 },
                { key: "tanggal_transfer", width: 15 },
                { key: "tgl_fpk", width: 15 },
                { key: "nsfp", width: 20 },
                { key: "dpp", width: 18 },
                { key: "ppn", width: 18 },
                { key: "pph", width: 18 },
                { key: "nomor_bukpot", width: 20 },
            ];
            sheet.mergeCells("A1:T1");
            const titleCell = sheet.getCell("A1");
            titleCell.value = `LAPORAN PROGRAM SUPPLIER - ${periodeText}`;
            titleCell.font = { name: "Arial", size: 14, bold: true };
            titleCell.alignment = { horizontal: "center" };
            const headers = [
                "No Program", "PIC", "Supplier", "NPWP", "Cabang", "Sts PPN", "Periode Prg", "Nama Program", "No Dokumen",
                "Nilai Program", "MOP", "TOP", "Nilai Transfer", "Tgl Transfer",
                "Tgl FPK", "NSFP", "DPP", "PPN", "PPH", "Bukpot"
            ];
            const headerRow = sheet.getRow(3);
            headerRow.values = headers;
            headerRow.eachCell((cell) => {
                cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
                cell.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FFDB2777" } };
                cell.alignment = { horizontal: "center", vertical: "middle" };
                cell.border = { top: { style: "thin" }, left: { style: "thin" }, bottom: { style: "thin" }, right: { style: "thin" } };
            });
            let rowNum = 4;
            data.forEach((item, index) => {
                const r = sheet.getRow(rowNum);
                const cabangFull = item.nama_cabang ? `${item.kode_cabang} - ${item.nama_cabang}` : item.kode_cabang;
                const picExcel = item.pic ? item.pic.split(',').map(s => s.trim()).join('\n') : "";
                const docExcel = item.nomor_dokumen ? item.nomor_dokumen.split(',').map(s => s.trim()).join('\n') : "";
                r.values = [
                    item.nomor_program || "",
                    picExcel,
                    item.nama_supplier || "",
                    item.npwp || "",
                    cabangFull || "",
                    item.status_ppn || "",
                    item.periode_program || "",
                    item.nama_program || "",
                    docExcel,
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
                [10, 13, 17, 18, 19].forEach(colIdx => {
                    r.getCell(colIdx).numFmt = currencyFmt;
                });
                r.eachCell((cell) => {
                    cell.alignment = { vertical: "top", wrapText: true };
                    cell.border = { top: { style: "thin" }, left: { style: "thin" }, bottom: { style: "thin" }, right: { style: "thin" } };
                });
                rowNum++;
            });
            const buffer = await workbook.xlsx.writeBuffer();
            const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
            const url = window.URL.createObjectURL(blob);
            const anchor = document.createElement("a");
            anchor.href = url;
            let filename = `Program_Supplier_Export.xlsx`;
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
    loadData();
});