import { sendRequestJSON } from "../utils/api_helpers.js";
document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.getElementById("receipt-table-body");
    const modalAuth = document.getElementById("modal-otorisasi");
    const formAuth = document.getElementById("form-otorisasi");
    const authNotaFaktur = document.getElementById("auth_nota_id");
    const authStatusSelect = document.getElementById("auth_status_baru");
    const btnsCloseAuth = document.querySelectorAll(".btn-close-auth");
    const filterForm = document.getElementById("filter-form");
    const filterSubmitButton = document.getElementById("filter-submit-button");
    const filterInputSupplier = document.getElementById("search_supplier");
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
    const authStatusKontra = document.getElementById("auth_status_kontra");
    const authStatusBayar = document.getElementById("auth_status_bayar");
    const authStatusPinjam = document.getElementById("auth_status_pinjam");
    if (exportExcelButton) {
        exportExcelButton.addEventListener("click", handleExportExcel);
    }
    window.openStatusModal = (faktur, sTerima, sKontra, sBayar, sPinjam, penerima, tgl) => {
        formAuth.reset();
        authNotaFaktur.value = faktur;

        authStatusSelect.value = sTerima || 'Belum Terima';
        authStatusKontra.value = sKontra || 'Belum';
        authStatusBayar.value = sBayar || 'Belum';
        authStatusPinjam.value = sPinjam || 'Tidak';

        document.getElementById('auth_penerima').value = penerima || '';

        const inputTgl = document.getElementById('auth_tgl_diterima');
        if (tgl && tgl !== 'null' && tgl !== '-') {
            inputTgl.value = tgl;
        } else {
            inputTgl.valueAsDate = new Date();
        }

        modalAuth.classList.remove("hidden");
    };
    btnsCloseAuth.forEach(btn => {
        btn.addEventListener("click", () => {
            modalAuth.classList.add("hidden");
        });
    });
    if (formAuth) {
        formAuth.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(formAuth);
            const jsonData = Object.fromEntries(formData.entries());
            const token = getCookie("admin_token");
            try {
                Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading() });
                const response = await fetch('/src/api/finance/update_status_serah_terima.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify(jsonData)
                });
                const result = await response.json();
                if (result.success) {
                    Swal.fire("Berhasil", result.message, "success");
                    modalAuth.classList.add("hidden");
                    loadData();
                } else {
                    Swal.fire("Gagal", result.message, "error");
                }
            } catch (error) {
                console.error(error);
                Swal.fire("Error", "Terjadi kesalahan sistem", "error");
            }
        });
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
        if (isNaN(dateObj)) return "-";
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
            text: "Sedang mengambil data...",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });
        try {
            const queryString = new URLSearchParams({
                filter_type: params.filter_type,
                bulan: params.bulan,
                tahun: params.tahun,
                tgl_mulai: params.tgl_mulai,
                tgl_selesai: params.tgl_selesai,
                search_supplier: params.search_supplier,
            }).toString();
            const response = await fetch(
                `/src/api/finance/get_export_laporan_serah_terima_nota.php?${queryString}`
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
            const sheet = workbook.addWorksheet("Serah Terima Nota");
            sheet.columns = [
                { key: "no", width: 5 },
                { key: "tgl_nota", width: 15 },
                { key: "nama_supplier", width: 30 },
                { key: "no_nota", width: 20 },
                { key: "no_rev_nota", width: 15 },
                { key: "no_faktur", width: 20 },
                { key: "nominal_awal", width: 18 },
                { key: "nominal_revisi", width: 18 },
                { key: "selisih_pembayaran", width: 18 },
                { key: "tgl_diserahkan", width: 15 },
                { key: "tgl_diterima", width: 15 },
                { key: "status", width: 15 },
                { key: "diberikan", width: 15 },
                { key: "penerima", width: 15 },
            ];
            sheet.mergeCells("A1:N1");
            const titleCell = sheet.getCell("A1");
            titleCell.value = `LAPORAN SERAH TERIMA NOTA - ${periodeText}`;
            titleCell.font = { name: "Arial", size: 14, bold: true };
            titleCell.alignment = { horizontal: "center" };
            const headers = [
                "No", "Tgl Nota", "Nama Supplier", "No. Nota", "No. Rev. Nota",
                "No Faktur", "Nominal Awal", "Nominal Revisi", "Selisih Pembayaran",
                "Tgl Diserahkan", "Tgl Diterima", "Status", "Diberikan", "Penerima"
            ];
            const headerRow = sheet.getRow(3);
            headerRow.values = headers;
            headerRow.eachCell((cell) => {
                cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
                cell.fill = {
                    type: "pattern",
                    pattern: "solid",
                    fgColor: { argb: "FFDB2777" },
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
                    item.nama_supplier,
                    item.no_nota,
                    item.no_rev_nota || 0,
                    item.no_faktur_format,
                    parseFloat(item.nominal_awal) || 0,
                    parseFloat(item.nominal_revisi) || 0,
                    parseFloat(item.selisih_pembayaran) || 0,
                    item.tgl_diserahkan,
                    item.tgl_diterima,
                    item.status,
                    item.diberikan,
                    item.penerima
                ];
                r.getCell(7).numFmt = currencyFmt;
                r.getCell(8).numFmt = currencyFmt;
                r.getCell(9).numFmt = currencyFmt;
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
            let filename = `Laporan_serah_terima_nota_`;
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
            search_supplier: params.get("search_supplier") || "",
            status_kontra: params.get("status_kontra") || "",
            status_bayar: params.get("status_bayar") || "",
            status_pinjam: params.get("status_pinjam") || "",
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
        setLoadingState(true, isPagination);
        const queryString = new URLSearchParams({
            filter_type: params.filter_type,
            bulan: params.bulan,
            tahun: params.tahun,
            tgl_mulai: params.tgl_mulai,
            tgl_selesai: params.tgl_selesai,
            search_supplier: params.search_supplier,
            status_kontra: params.status_kontra,
            status_bayar: params.status_bayar,
            status_pinjam: params.status_pinjam,
            page: params.page,
        }).toString();
        try {
            const response = await fetch(
                `/src/api/finance/get_laporan_serah_terima_nota.php?${queryString}`
            );
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(
                    errorData.error || `HTTP error! status: ${response.status}`
                );
            }
            const data = await response.json();
            if (data.error) throw new Error(data.error);
            if (filterInputSupplier)
                filterInputSupplier.value = params.search_supplier;
            if (filterTypeSelect) {
                filterTypeSelect.value = params.filter_type;
                toggleFilterMode();
            }
            document.getElementById("filter_status_kontra").value = params.status_kontra;
            document.getElementById("filter_status_bayar").value = params.status_bayar;
            document.getElementById("filter_status_pinjam").value = params.status_pinjam;
            if (filterBulan) filterBulan.value = params.bulan;
            if (filterTahun) filterTahun.value = params.tahun;
            if (filterTglMulai) filterTglMulai.value = params.tgl_mulai;
            if (filterTglSelesai) filterTglSelesai.value = params.tgl_selesai;
            if (pageSubtitle) {
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
                pageSubtitle.textContent = `${periodText}`;
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
    function setLoadingState(isLoading, isPagination = false) {
        if (isLoading) {
            if (filterSubmitButton) filterSubmitButton.disabled = true;
            if (exportExcelButton) exportExcelButton.disabled = true;
            if (filterSubmitButton)
                filterSubmitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span>Memuat...</span>`;
            if (tableBody)
                tableBody.innerHTML = `<tr><td colspan="14" class="text-center p-8"><div class="spinner-simple"></div><p class="mt-2 text-gray-500">Memuat data...</p></td></tr>`;
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
        tableBody.innerHTML = `<tr><td colspan="14" class="text-center p-8 text-red-600"><p>Gagal: ${message}</p></td></tr>`;
    }
    function renderTable(tabel_data, offset) {
        if (!tabel_data || tabel_data.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="16" class="text-center p-8 text-gray-500"> <i class="fas fa-inbox fa-lg mb-2"></i>
                        <p>Tidak ada data ditemukan untuk filter ini.</p>
                    </td>
                </tr>`;
            return;
        }

        let htmlRows = "";
        let item_counter = offset + 1;

        tabel_data.forEach((row) => {
            // --- Definisi Variabel Data Asli ---
            const nominalAwal = parseFloat(row.nominal_awal) || 0;
            const nominalRevisi = parseFloat(row.nominal_revisi) || 0;
            const selisih = parseFloat(row.selisih_pembayaran) || 0;
            const tglNota = formatDate(row.tgl_nota);
            const tglDiserahkan = formatDate(row.tgl_diserahkan);
            const tglDiterima = formatDate(row.tgl_diterima); // Format Tampilan

            // Variabel untuk Modal (Raw Data)
            const rawPenerima = row.penerima ? row.penerima.replace(/'/g, "\\'") : '';
            const rawTglDiterima = row.tgl_diterima ? row.tgl_diterima : '';

            // --- Status Data Baru ---
            const sKontra = row.status_kontra || 'Belum';
            const sBayar = row.status_bayar || 'Belum';
            const sPinjam = row.status_pinjam || 'Tidak';
            const sTerima = row.status || 'Belum Terima';

            // --- Helper Badge Sederhana ---
            const createBadge = (val, type) => {
                let colorClass = 'bg-gray-100 text-gray-600 border-gray-200';

                // Logic Warna
                if (type === 'terima' && val === 'Sudah Terima') colorClass = 'bg-green-100 text-green-800 border-green-200';
                if (type === 'terima' && val === 'Belum Terima') colorClass = 'bg-red-50 text-red-600 border-red-200';

                if (type === 'kontra' && val === 'Sudah') colorClass = 'bg-blue-100 text-blue-700 border-blue-200';
                if (type === 'bayar' && val === 'Sudah') colorClass = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                if (type === 'pinjam' && val === 'Pinjam') colorClass = 'bg-orange-100 text-orange-700 border-orange-200';

                return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border ${colorClass}">${val}</span>`;
            };

            // --- Render Baris ---
            htmlRows += `
            <tr class="hover:bg-gray-50 border-b transition-colors">
                <td class="text-center font-medium text-gray-500">${item_counter}</td>
                
                <td class="whitespace-nowrap text-xs">${tglNota}</td>
                
                <td class="font-semibold text-gray-700 text-sm">
                    ${row.nama_supplier || '-'}
                    ${row.no_nota ? `<br><span class="text-[10px] text-gray-400 font-normal">${row.no_nota}</span>` : ''}
                </td>
                
                <td class="font-mono text-xs text-gray-600">${row.no_faktur || '-'}</td>
                
                <td class="text-right font-mono text-sm text-gray-600">${formatRupiah(nominalAwal)}</td>
                
                <td class="text-right font-mono text-sm font-bold text-gray-800">${formatRupiah(nominalRevisi)}</td>
                
                <td class="text-right font-mono text-sm font-bold ${selisih < 0 ? 'text-red-600' : 'text-green-600'}">
                    ${formatRupiah(selisih)}
                </td>
                
                <td class="text-center text-xs whitespace-nowrap">${tglDiserahkan}</td>
                
                <td class="text-center text-xs whitespace-nowrap">${tglDiterima}</td>
                
                <td class="text-center">${createBadge(sTerima, 'terima')}</td>

                <td class="text-center">${createBadge(sKontra, 'kontra')}</td>
                <td class="text-center">${createBadge(sBayar, 'bayar')}</td>
                <td class="text-center">${createBadge(sPinjam, 'pinjam')}</td>

                <td class="text-center text-xs">${row.diberikan || '-'}</td>
                
                <td class="text-center text-xs">${row.penerima || '-'}</td>

                <td class="text-center">
                    <button type="button" 
                        onclick="window.openStatusModal('${row.no_faktur}', '${sTerima}', '${sKontra}', '${sBayar}', '${sPinjam}', '${rawPenerima}', '${rawTglDiterima}')"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-pink-50 text-pink-600 hover:bg-pink-100 hover:text-pink-800 transition-all shadow-sm border border-pink-100" 
                        title="Edit Semua Status">
                        <i class="fas fa-edit"></i>
                    </button>
                </td>
            </tr>
            `;
            item_counter++;
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
              <a href="${current_page > 1 ? build_pagination_url(current_page - 1) : "#"
            }" 
                 class="pagination-link ${current_page === 1 ? "pagination-disabled" : ""
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
                     class="pagination-link ${page_num === current_page ? "pagination-active" : ""
                }">
                     ${page_num}
                  </a>
              `;
            last_page = page_num;
        }
        linksHtml += `
              <a href="${current_page < total_pages
                ? build_pagination_url(current_page + 1)
                : "#"
            }" 
                 class="pagination-link ${current_page === total_pages ? "pagination-disabled" : ""
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
    loadData();
});