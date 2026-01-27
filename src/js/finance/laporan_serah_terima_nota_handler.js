import { sendRequestJSON } from "../utils/api_helpers.js";
document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.getElementById("receipt-table-body");
    const modalAuth = document.getElementById("modal-otorisasi");
    const formAuth = document.getElementById("form-otorisasi");
    const authNotaFaktur = document.getElementById("auth_nota_id");
    const btnsCloseAuth = document.querySelectorAll(".btn-close-auth");
    const authNoFakturBaru = document.getElementById("auth_no_faktur_baru");
    const authNominal = document.getElementById("auth_nominal");
    const authStatusSelect = document.getElementById("auth_status_baru");
    const authStatusKontra = document.getElementById("auth_status_kontra");
    const authStatusBayar = document.getElementById("auth_status_bayar");
    const authStatusPinjam = document.getElementById("auth_status_pinjam");
    const authPenerima = document.getElementById("auth_penerima");
    const authTglDiterima = document.getElementById("auth_tgl_diterima");
    const modalKontra = document.getElementById("modal-otorisasi-kontra");
    const formKontra = document.getElementById("form-otorisasi-kontra");
    const kontraNotaId = document.getElementById("kontra_nota_id");
    const btnsCloseKontra = document.querySelectorAll(".btn-close-kontra");
    const displayFakturKontra = document.getElementById("display_faktur_kontra");
    const el_r_tgl_tukar = document.getElementById("r_tanggal_tukar_faktur");
    const el_r_diterima_oleh = document.getElementById("r_diterima_oleh");
    const el_r_tgl_serah_md = document.getElementById("r_tgl_diserahkan_ke_md");
    const el_ket_resepsionis = document.getElementById("ket_resepsionis");
    const el_md_divalidasi = document.getElementById("md_divalidasi_oleh");
    const el_md_tgl_serah_fin = document.getElementById("md_tgl_diserahkan_ke_finance");
    const el_ket_merchandise = document.getElementById("ket_merchandise");
    const el_fin_divalidasi = document.getElementById("finance_divalidasi_oleh");
    const el_ket_finance = document.getElementById("ket_finance");
    const el_tax_tgl_terima = document.getElementById("tax_tgl_diterima_dari_r");
    const el_tax_divalidasi = document.getElementById("tax_divalidasi_oleh");
    const el_ket_tax = document.getElementById("ket_tax");
    const alertDependency = document.getElementById("alert-dependency");
    const alertLockedPaid = document.getElementById("alert-locked-paid");
    const alertLockedKontra = document.getElementById("alert-locked-kontra");
    const alertLockedBayarStatus = document.getElementById("alert-locked-bayar-status");
    const filterForm = document.getElementById("filter-form");
    const filterSubmitButton = document.getElementById("filter-submit-button");
    const filterInputSupplier = document.getElementById("search_supplier");
    const pageSubtitle = document.getElementById("page-subtitle");
    const paginationInfo = document.getElementById("pagination-info");
    const paginationLinks = document.getElementById("pagination-links");
    const filterTypeSelect = document.getElementById("filter_type");
    const containerMonth = document.getElementById("container-month");
    const containerDateRange = document.getElementById("container-date-range");
    const filterBulan = document.getElementById("bulan");
    const filterTahun = document.getElementById("tahun");
    const filterTglMulai = document.getElementById("tgl_mulai");
    const filterTglSelesai = document.getElementById("tgl_selesai");
    const btnExportExcel = document.getElementById("btn-export-excel");
    const btnPrintBatch = document.getElementById("btn-print-batch-cod");
    let activeSupplier = null;
    let initialKontraState = 'Belum';
    let initialBayarState = 'Belum';
    const updateModalState = () => {
        const isTerima = authStatusSelect.value === 'Sudah Terima';
        const hasPenerima = authPenerima.value.trim() !== '';
        const hasTanggal = authTglDiterima.value !== '';
        if (initialBayarState === 'Sudah') {
            authStatusSelect.disabled = true;
            authTglDiterima.disabled = true;
            authPenerima.disabled = true;
            if (alertLockedPaid) alertLockedPaid.classList.remove("hidden");
        } else {
            authStatusSelect.disabled = false;
            authTglDiterima.disabled = false;
            authPenerima.disabled = false;
            if (alertLockedPaid) alertLockedPaid.classList.add("hidden");
        }
        if (isTerima && !hasPenerima) authPenerima.classList.add('border-red-500');
        else authPenerima.classList.remove('border-red-500');
        if (isTerima && !hasTanggal) authTglDiterima.classList.add('border-red-500');
        else authTglDiterima.classList.remove('border-red-500');
        const isPrerequisitesMet = isTerima && hasPenerima && hasTanggal;
        if (isPrerequisitesMet) {
            alertDependency.classList.add("hidden");
            authStatusKontra.disabled = false;
            authStatusBayar.disabled = false;
            authStatusPinjam.disabled = false;
        } else {
            alertDependency.classList.remove("hidden");
            authStatusKontra.disabled = true;
            authStatusBayar.disabled = true;
            authStatusPinjam.disabled = true;
        }
    };
    [authStatusSelect, authPenerima, authTglDiterima].forEach(el => {
        if (el) {
            el.addEventListener('change', updateModalState);
            el.addEventListener('input', updateModalState);
        }
    });
    window.openStatusModal = (rowDataEncoded) => {
        formAuth.reset();
        const row = JSON.parse(decodeURIComponent(rowDataEncoded));
        authNotaFaktur.value = row.no_faktur;
        authNoFakturBaru.value = row.no_faktur_format || row.no_faktur;
        if (authNominal) authNominal.value = row.nominal || 0;
        authStatusSelect.value = (!row.status || row.status === 'null') ? 'Belum Terima' : row.status;
        authPenerima.value = (row.penerima && row.penerima !== 'null') ? row.penerima : '';
        authTglDiterima.value = (row.tgl_diterima && row.tgl_diterima !== 'null' && row.tgl_diterima !== '0000-00-00') ? row.tgl_diterima : '';
        authStatusKontra.value = (!row.status_kontra) ? 'Belum' : row.status_kontra;
        authStatusBayar.value = (!row.status_bayar) ? 'Belum' : row.status_bayar;
        authStatusPinjam.value = (!row.status_pinjam) ? 'Tidak' : row.status_pinjam;
        initialKontraState = authStatusKontra.value;
        initialBayarState = authStatusBayar.value;
        updateModalState();
        modalAuth.classList.remove("hidden");
    };
    window.openKontraModal = (rowDataEncoded) => {
        formKontra.reset();
        const row = JSON.parse(decodeURIComponent(rowDataEncoded));
        kontraNotaId.value = row.no_faktur;
        if (displayFakturKontra) displayFakturKontra.textContent = row.no_faktur_format || row.no_faktur;
        const setVal = (el, val) => { if (el) el.value = (val && val !== 'null' && val !== '0000-00-00') ? val : ''; };
        setVal(el_r_tgl_tukar, row.r_tanggal_tukar_faktur);
        setVal(el_r_diterima_oleh, row.r_diterima_oleh);
        setVal(el_r_tgl_serah_md, row.r_tgl_diserahkan_ke_md);
        setVal(el_md_divalidasi, row.md_divalidasi_oleh);
        setVal(el_md_tgl_serah_fin, row.md_tgl_diserahkan_ke_finance);
        setVal(el_fin_divalidasi, row.finance_divalidasi_oleh);
        setVal(el_tax_tgl_terima, row.tax_tgl_diterima_dari_r);
        setVal(el_tax_divalidasi, row.tax_divalidasi_oleh);
        try {
            const ketObj = row.ket ? JSON.parse(row.ket) : {};
            if (el_ket_resepsionis) el_ket_resepsionis.value = ketObj.resepsionis || '';
            if (el_ket_merchandise) el_ket_merchandise.value = ketObj.merchandise || '';
            if (el_ket_finance) el_ket_finance.value = ketObj.finance || '';
            if (el_ket_tax) el_ket_tax.value = ketObj.tax || '';
        } catch (e) {
            console.error("Error parsing ket JSON", e);
            if (el_ket_resepsionis) el_ket_resepsionis.value = '';
        }
        modalKontra.classList.remove("hidden");
    };
    window.handleCheckboxChange = (checkbox) => {
        const supplier = checkbox.getAttribute('data-supplier');
        const allCheckboxes = document.querySelectorAll('.cod-checkbox');
        if (checkbox.checked) {
            if (activeSupplier === null) {
                activeSupplier = supplier;
                allCheckboxes.forEach(box => {
                    if (box.getAttribute('data-supplier') !== activeSupplier) {
                        box.disabled = true;
                        box.parentElement.classList.add('opacity-30', 'cursor-not-allowed');
                    }
                });
            }
        } else {
            const anyChecked = document.querySelectorAll('.cod-checkbox:checked').length > 0;
            if (!anyChecked) {
                activeSupplier = null;
                allCheckboxes.forEach(box => {
                    box.disabled = false;
                    box.parentElement.classList.remove('opacity-30', 'cursor-not-allowed');
                });
            }
        }
        const checkedCount = document.querySelectorAll('.cod-checkbox:checked').length;
        if (btnPrintBatch) {
            btnPrintBatch.style.display = checkedCount > 0 ? 'inline-flex' : 'none';
            btnPrintBatch.querySelector('span').textContent = `Cetak Terpilih (${checkedCount})`;
        }
    };
    btnsCloseAuth.forEach(btn => btn.addEventListener("click", () => modalAuth.classList.add("hidden")));
    btnsCloseKontra.forEach(btn => btn.addEventListener("click", () => modalKontra.classList.add("hidden")));
    const handleFormSubmit = async (e, formElement, modalElement) => {
        e.preventDefault();
        const formData = new FormData(formElement);
        if (formElement.id === 'form-otorisasi') {
            if (authStatusKontra.disabled) formData.append("status_kontra", authStatusKontra.value);
            if (authStatusBayar.disabled) formData.append("status_bayar", authStatusBayar.value);
            if (authStatusPinjam.disabled) formData.append("status_pinjam", authStatusPinjam.value);
            if (authStatusSelect.disabled) formData.append("status", authStatusSelect.value);
            if (authTglDiterima.disabled) formData.append("tgl_diterima", authTglDiterima.value);
            if (authPenerima.disabled) formData.append("penerima", authPenerima.value);
        }
        else if (formElement.id === 'form-otorisasi-kontra') {
            const ketJSON = {
                resepsionis: el_ket_resepsionis ? el_ket_resepsionis.value : '',
                merchandise: el_ket_merchandise ? el_ket_merchandise.value : '',
                finance: el_ket_finance ? el_ket_finance.value : '',
                tax: el_ket_tax ? el_ket_tax.value : ''
            };
            formData.append("ket", JSON.stringify(ketJSON));
        }
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
                modalElement.classList.add("hidden");
                loadData();
            } else {
                Swal.fire("Gagal", result.message, "error");
            }
        } catch (error) {
            console.error(error);
            Swal.fire("Error", "Terjadi kesalahan sistem", "error");
        }
    };
    if (formAuth) formAuth.addEventListener("submit", (e) => handleFormSubmit(e, formAuth, modalAuth));
    if (formKontra) formKontra.addEventListener("submit", (e) => handleFormSubmit(e, formKontra, modalKontra));
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
        return new Intl.NumberFormat("id-ID", { style: "decimal", currency: "IDR", minimumFractionDigits: 0 }).format(number);
    }
    function formatDate(dateString) {
        if (!dateString || dateString === '0000-00-00') return "-";
        const dateObj = new Date(dateString);
        return isNaN(dateObj) ? "-" : dateObj.toLocaleDateString("id-ID", { day: "2-digit", month: "2-digit", year: "numeric" });
    }
    function getUrlParams() {
        const params = new URLSearchParams(window.location.search);
        const now = new Date();
        return {
            filter_type: params.get("filter_type") || "month",
            bulan: params.get("bulan") || String(now.getMonth() + 1).padStart(2, "0"),
            tahun: params.get("tahun") || now.getFullYear(),
            tgl_mulai: params.get("tgl_mulai") || now.toISOString().split("T")[0],
            tgl_selesai: params.get("tgl_selesai") || now.toISOString().split("T")[0],
            search_supplier: params.get("search_supplier") || "",
            status_kontra: params.get("status_kontra") || "",
            status_bayar: params.get("status_bayar") || "",
            status_pinjam: params.get("status_pinjam") || "",
            cod: params.get("cod") || "",
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
        setLoadingState(true);
        const queryString = new URLSearchParams(params).toString();
        try {
            const token = getCookie("admin_token");
            const response = await fetch(`/src/api/finance/get_laporan_serah_terima_nota.php?${queryString}`, {
                headers: {
                    Accept: "application/json",
                    Authorization: "Bearer " + token,
                },
            });
            if (!response.ok) throw new Error(`HTTP Error ${response.status}`);
            const data = await response.json();
            if (data.error) throw new Error(data.error);
            if (filterInputSupplier) filterInputSupplier.value = params.search_supplier;
            if (filterTypeSelect) { filterTypeSelect.value = params.filter_type; toggleFilterMode(); }
            document.getElementById("filter_status_kontra").value = params.status_kontra;
            document.getElementById("filter_status_bayar").value = params.status_bayar;
            document.getElementById("filter_status_pinjam").value = params.status_pinjam;
            if (document.getElementById("filter_cod")) document.getElementById("filter_cod").value = params.cod;
            if (filterBulan) filterBulan.value = params.bulan;
            if (filterTahun) filterTahun.value = params.tahun;
            if (filterTglMulai) filterTglMulai.value = params.tgl_mulai;
            if (filterTglSelesai) filterTglSelesai.value = params.tgl_selesai;
            if (pageSubtitle) {
                if (params.filter_type === "month") {
                    pageSubtitle.textContent = `Periode Bulan ${params.bulan}/${params.tahun}`;
                } else {
                    if (params.tgl_mulai === params.tgl_selesai) {
                        pageSubtitle.textContent = `Periode ${params.tgl_mulai}`;
                    } else {
                        pageSubtitle.textContent = `Periode ${params.tgl_mulai} s/d ${params.tgl_selesai}`;
                    }
                }
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
            if (filterSubmitButton) {
                filterSubmitButton.disabled = true;
                filterSubmitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span>...</span>`;
            }
            if (tableBody) tableBody.innerHTML = `<tr><td colspan="14" class="text-center p-8"><div class="spinner-simple"></div></td></tr>`;
        } else {
            if (filterSubmitButton) {
                filterSubmitButton.disabled = false;
                filterSubmitButton.innerHTML = `<i class="fas fa-filter"></i><span>Tampilkan</span>`;
            }
        }
    }
    function showTableError(message) {
        tableBody.innerHTML = `<tr><td colspan="14" class="text-center p-8 text-red-600">Gagal: ${message}</td></tr>`;
    }
    function renderTable(tabel_data, offset) {
        if (!tabel_data || tabel_data.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="14" class="text-center p-8 text-gray-500">Tidak ada data.</td></tr>`;
            return;
        }
        let htmlRows = "";
        const createBadge = (val, type) => {
            const safeVal = val || (type === 'pinjam' ? 'Tidak' : 'Belum');
            let colorClass = 'bg-gray-100 text-gray-600';
            if (type === 'terima') colorClass = (safeVal === 'Sudah Terima') ? 'bg-green-100 text-green-800' : 'bg-red-50 text-red-600';
            else if (type === 'kontra' && safeVal === 'Sudah') colorClass = 'bg-purple-100 text-purple-700';
            else if (type === 'bayar' && safeVal === 'Sudah') colorClass = 'bg-emerald-100 text-emerald-700';
            else if (type === 'pinjam' && safeVal === 'Pinjam') colorClass = 'bg-orange-100 text-orange-700';
            return `<span class="px-2 py-0.5 rounded text-[10px] font-bold border ${colorClass} border-opacity-20">${safeVal}</span>`;
        };
        tabel_data.forEach((row) => {
            const nominal = parseFloat(row.nominal) || 0;
            const rowDataEncoded = encodeURIComponent(JSON.stringify(row)).replace(/'/g, "%27");
            const isCOD = row.cod === 'Ya';
            let btnEditKontra = '';
            if (row.status_kontra === 'Sudah') {
                btnEditKontra = `<button type="button" onclick="event.stopPropagation(); window.openKontraModal('${rowDataEncoded}')" class="inline-flex items-center justify-center w-8 h-8 mr-1 transition-all border rounded-full shadow-sm bg-purple-50 text-purple-600 hover:bg-purple-100 hover:text-purple-800 border-purple-100" title="Update Detail Kontra"><i class="fas fa-book"></i></button>`;
            }
            let checkboxCell = '';
            let printStatusIcon = '';
            if (isCOD) {
                if (row.sudah_dicetak === 'Sudah') {
                    printStatusIcon = `<i class="fas fa-print text-green-500"></i>`;
                }
                let isDisabled = false;
                if (activeSupplier && activeSupplier !== row.nama_supplier) {
                    isDisabled = true;
                }
                checkboxCell = `
                    <div class="flex items-center justify-center ${isDisabled ? 'opacity-30 cursor-not-allowed' : ''}">
                        <input type="checkbox" 
                            class="cod-checkbox w-5 h-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300 transition duration-150 ease-in-out cursor-pointer"
                            data-supplier="${row.nama_supplier}" 
                            data-faktur="${row.no_faktur}"
                            data-json='${JSON.stringify(row)}'
                            onchange="window.handleCheckboxChange(this)"
                            onclick="event.stopPropagation();"
                            ${isDisabled ? 'disabled' : ''}
                        >
                    </div>
                `;
            } else {
                checkboxCell = `<span class="text-gray-300">-</span>`;
            }
            let trClass = "border-b transition-colors " + (isCOD ? "hover:bg-blue-50 cursor-pointer" : "hover:bg-gray-50");
            let trClickAction = isCOD ? `onclick="window.showDetailCod('${row.nota_tanggal_masuk || ''}', '${(row.cabang_penerima || '').replace(/'/g, "\\'")}', '${row.lengkap || ''}', '${(row.no_rek || '').replace(/'/g, "\\'")}', '${(row.nama_bank || '').replace(/'/g, "\\'")}', '${(row.atas_nama_rek || '').replace(/'/g, "\\'")}')"` : "";
            htmlRows += `
            <tr class="${trClass}" ${trClickAction}>
                <td class="px-2 text-center align-middle bg-white/50">
                    ${checkboxCell}
                </td>
                <td class="px-2 text-center whitespace-nowrap">
                    <button type="button" 
                        onclick="event.stopPropagation(); window.openStatusModal('${rowDataEncoded}')"
                        class="inline-flex items-center justify-center w-8 h-8 mr-1 transition-all border rounded-full shadow-sm bg-pink-50 text-pink-600 hover:bg-pink-100 hover:text-pink-800 border-pink-100" 
                        title="Edit Status Utama">
                        <i class="fas fa-edit"></i>
                    </button>
                    ${btnEditKontra}
                    <button type="button" 
                        onclick="event.stopPropagation(); window.deleteNota('${row.no_faktur}')"
                        class="inline-flex items-center justify-center w-8 h-8 transition-all border rounded-full shadow-sm bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-800 border-red-100" 
                        title="Hapus Data">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
                <td class="text-xs whitespace-nowrap">${formatDate(row.tgl_nota)}</td>
                <td class="text-sm font-semibold text-gray-700">
                    <div class="flex flex-col">
                        <span>${row.nama_supplier || '-'}</span>
                        ${isCOD ? '<span class="text-[10px] text-blue-500 flex items-center gap-1"><i class="fa-solid fa-truck-fast"></i>  ' + printStatusIcon + '</span>' : ''}
                    </div>
                </td>
                <td class="font-mono text-xs text-gray-600">${row.no_faktur_format || row.no_faktur || '-'}</td>
                <td class="font-mono text-sm font-bold text-right text-gray-800">${formatRupiah(nominal)}</td>
                <td class="text-center text-xs whitespace-nowrap">${formatDate(row.tgl_diserahkan)}</td>
                <td class="text-center text-xs whitespace-nowrap font-medium text-gray-700">${formatDate(row.tgl_diterima)}</td>
                <td class="text-center">${createBadge(row.status, 'terima')}</td>
                <td class="text-center">${createBadge(row.status_kontra, 'kontra')}</td>
                <td class="text-center">${createBadge(row.status_bayar, 'bayar')}</td>
                <td class="text-center">${createBadge(row.status_pinjam, 'pinjam')}</td>
                <td class="text-center text-xs">${row.diberikan || '-'}</td>
                <td class="text-center text-xs">${row.penerima || '-'}</td>
            </tr>`;
        });
        tableBody.innerHTML = htmlRows;
        activeSupplier = null;
        if (btnPrintBatch) btnPrintBatch.style.display = 'none';
    }
    function renderPagination(pagination) {
        if (!pagination) { paginationInfo.textContent = ""; paginationLinks.innerHTML = ""; return; }
        const { current_page, total_pages, total_rows, limit, offset } = pagination;
        if (total_rows === 0) { paginationInfo.textContent = "0 Data"; paginationLinks.innerHTML = ""; return; }
        const start_row = offset + 1;
        const end_row = Math.min(offset + limit, total_rows);
        paginationInfo.textContent = `${start_row} - ${end_row} dari ${total_rows}`;
        let linksHtml = `<a href="${current_page > 1 ? build_pagination_url(current_page - 1) : "#"}" class="pagination-link ${current_page === 1 ? 'pagination-disabled' : ''}"><i class="fas fa-chevron-left"></i></a>`;
        linksHtml += `<a href="#" class="pagination-link pagination-active">${current_page}</a>`;
        linksHtml += `<a href="${current_page < total_pages ? build_pagination_url(current_page + 1) : "#"}" class="pagination-link ${current_page === total_pages ? 'pagination-disabled' : ''}"><i class="fas fa-chevron-right"></i></a>`;
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
    window.deleteNota = (noFaktur) => {
        Swal.fire({
            title: 'Hapus Nota?',
            html: `Hapus <b>${noFaktur}</b>?<br><input id="d_u" class="swal2-input" placeholder="User"><input id="d_p" type="password" class="swal2-input" placeholder="Pass Otorisasi">`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            preConfirm: () => {
                const u = document.getElementById('d_u').value;
                const p = document.getElementById('d_p').value;
                if (!u || !p) Swal.showValidationMessage('Isi User & Password');
                return { user: u, pass: p };
            }
        }).then(res => {
            if (res.isConfirmed) processDelete(noFaktur, res.value.user, res.value.pass);
        });
    };
    async function processDelete(noFaktur, user, pass) {
        const token = getCookie("admin_token");
        try {
            const res = await fetch('/src/api/finance/delete_serah_terima_nota.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
                body: JSON.stringify({ no_faktur: noFaktur, nama_user_cek: user, kode_otorisasi: pass })
            });
            const dat = await res.json();
            if (dat.success) { Swal.fire('Deleted', dat.message, 'success'); loadData(); }
            else Swal.fire('Gagal', dat.message, 'error');
        } catch (e) { Swal.fire('Error', 'Sistem error', 'error'); }
    }
    window.showDetailCod = (t, c, l, n, b, a) => {
        document.getElementById('cod_tgl_masuk').textContent = formatDate(t);
        document.getElementById('cod_cabang').textContent = c || '-';
        const elB = document.getElementById('cod_lengkap_badge');
        if (l === 'Ya' || l === 'Lengkap') {
            elB.textContent = 'LENGKAP';
            elB.classList.add('bg-green-100', 'text-green-800', 'border-green-200');
        } else if (l === 'Belum') {
            elB.textContent = 'BELUM';
            elB.classList.add('bg-orange-100', 'text-orange-800', 'border-orange-200');
        } else {
            elB.textContent = 'TIDAK';
            elB.classList.add('bg-red-50', 'text-red-600', 'border-red-100');
        }
        const sec = document.getElementById('cod_bank_section');
        const msg = document.getElementById('cod_no_bank_msg');
        if (n && n !== 'null') {
            sec.classList.remove('hidden'); msg.classList.add('hidden');
            document.getElementById('cod_bank_name').textContent = b || '-';
            document.getElementById('cod_no_rek').textContent = n || '-';
            document.getElementById('cod_an_rek').textContent = a || '-';
        } else {
            sec.classList.add('hidden'); msg.classList.remove('hidden');
        }
        document.getElementById('modal-detail-cod').classList.remove('hidden');
    }
    if (btnExportExcel) {
        btnExportExcel.addEventListener("click", handleExportExcel);
    }
    if (btnPrintBatch) {
        btnPrintBatch.addEventListener('click', async () => {
            const checkedBoxes = document.querySelectorAll('.cod-checkbox:checked');
            if (checkedBoxes.length === 0) return;
            const selectedData = [];
            const selectedFakturs = [];
            let supplierName = "";
            checkedBoxes.forEach(box => {
                const data = JSON.parse(box.getAttribute('data-json'));
                selectedData.push(data);
                selectedFakturs.push(data.no_faktur);
                supplierName = data.nama_supplier;
            });
            const result = await Swal.fire({
                title: 'Cetak Tanda Terima?',
                text: `Akan mencetak ${selectedData.length} nota untuk ${supplierName}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Cetak',
                confirmButtonColor: '#2563EB'
            });
            if (!result.isConfirmed) return;
            try {
                const token = getCookie("admin_token");
                await fetch('/src/api/finance/update_print_cod_batch.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify({ fakturs: selectedFakturs })
                });
                loadData();
            } catch (e) {
                console.error("Gagal update status print:", e);
            }
            generateBatchExcel(selectedData, supplierName);
        });
    }
    async function handleExportExcel() {
        const todayStr = new Date().toISOString().split('T')[0];
        const { value: formValues } = await Swal.fire({
            title: 'Export Excel',
            width: '450px',
            html: `
                <div class="flex flex-col gap-4 text-left">
                    <div class="p-3 bg-blue-50 text-blue-800 rounded text-sm border border-blue-100">
                        <i class="fas fa-info-circle mr-1"></i>
                        Data yang diexport hanya nota yang statusnya <b>"Sudah Terima"</b>.
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Mulai Tanggal Terima</label>
                        <input id="swal-input-start" type="date" 
                            class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500 transition-shadow" 
                            value="${todayStr}">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Sampai Tanggal Terima</label>
                        <input id="swal-input-end" type="date" 
                            class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500 transition-shadow" 
                            value="${todayStr}">
                    </div>
                </div>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-file-excel"></i> Download Excel',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#10B981',
            preConfirm: () => {
                const start = document.getElementById('swal-input-start').value;
                const end = document.getElementById('swal-input-end').value;
                if (!start || !end) {
                    Swal.showValidationMessage('Harap isi kedua tanggal!');
                }
                return { start, end };
            }
        });
        if (!formValues) return;
        Swal.fire({
            title: "Memproses Excel...",
            text: "Mengambil data nota yang sudah diterima...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });
        try {
            const currentParams = getUrlParams();
            const queryParams = new URLSearchParams({
                tgl_terima_awal: formValues.start,
                tgl_terima_akhir: formValues.end,
                search_supplier: currentParams.search_supplier || ''
            }).toString();
            const token = getCookie("admin_token");
            const response = await fetch(`/src/api/finance/get_export_serah_terima_nota.php?${queryParams}`, {
                headers: {
                    Accept: "application/json",
                    Authorization: "Bearer " + token,
                },
            });
            if (!response.ok) throw new Error("Gagal mengambil data server");
            const result = await response.json();
            if (result.error) throw new Error(result.error);
            const data = result.data;
            if (!data || data.length === 0) {
                Swal.fire({
                    icon: "info",
                    title: "Tidak Ada Data",
                    text: "Tidak ditemukan nota dengan status 'Sudah Terima' pada rentang tanggal tersebut."
                });
                return;
            }
            const workbook = new ExcelJS.Workbook();
            const sheet = workbook.addWorksheet("Serah Terima Nota");
            sheet.columns = [
                { key: "no", width: 5 },
                { key: "tgl_diterima", width: 15 },
                { key: "tgl_nota", width: 15 },
                { key: "kode_supplier", width: 15 },
                { key: "nama_supplier", width: 35 },
                { key: "no_faktur", width: 25 },
                { key: "ttd_diserahkan", width: 25 },
                { key: "ttd_diterima", width: 25 },
            ];
            const fmtDate = (dStr) => {
                if (!dStr || dStr === '0000-00-00') return '-';
                const d = new Date(dStr);
                return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
            };
            let titleText = `LAPORAN NOTA DITERIMA: ${fmtDate(formValues.start)} s/d ${fmtDate(formValues.end)}`;
            if (formValues.start === formValues.end) {
                titleText = `LAPORAN NOTA DITERIMA TANGGAL: ${fmtDate(formValues.start)}`;
            }
            sheet.mergeCells("A1:H1");
            const titleCell = sheet.getCell("A1");
            titleCell.value = titleText.toUpperCase();
            titleCell.font = { name: "Arial", size: 12, bold: true };
            titleCell.alignment = { horizontal: "center", vertical: "middle" };
            const headers = [
                "NO",
                "TGL DITERIMA",
                "TGL NOTA",
                "KODE SUPP",
                "NAMA SUPPLIER",
                "NO FAKTUR",
                "TTD DISERAHKAN OLEH",
                "TTD DITERIMA OLEH"
            ];
            const headerRow = sheet.getRow(2);
            headerRow.values = headers;
            headerRow.eachCell((cell) => {
                cell.font = { bold: true, size: 10 };
                cell.alignment = { horizontal: "center", vertical: "middle", wrapText: true };
                cell.border = { top: { style: "thin" }, left: { style: "thin" }, bottom: { style: "thin" }, right: { style: "thin" } };
                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFE0E0E0' } };
            });
            let rowNum = 3;
            data.forEach((item, index) => {
                const r = sheet.getRow(rowNum);
                r.values = [
                    index + 1,
                    fmtDate(item.tgl_diterima),
                    fmtDate(item.tgl_nota),
                    item.kode_supplier || '',
                    item.nama_supplier || '',
                    item.no_faktur_format || item.no_faktur,
                    '',
                    ''
                ];
                r.eachCell((cell, colNumber) => {
                    cell.border = { top: { style: "thin" }, left: { style: "thin" }, bottom: { style: "thin" }, right: { style: "thin" } };
                    cell.alignment = { vertical: 'middle' };
                    if ([1, 2, 3].includes(colNumber)) cell.alignment = { vertical: 'middle', horizontal: 'center' };
                });
                rowNum++;
            });
            const buffer = await workbook.xlsx.writeBuffer();
            const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
            const url = window.URL.createObjectURL(blob);
            const anchor = document.createElement("a");
            anchor.href = url;
            anchor.download = `Laporan_Terima_${formValues.start}_sd_${formValues.end}.xlsx`;
            anchor.click();
            window.URL.revokeObjectURL(url);
            loadData();
            Swal.fire({
                icon: "success",
                title: "Selesai",
                text: "Export berhasil didownload.",
                timer: 1500,
                showConfirmButton: false,
            });
        } catch (e) {
            console.error(e);
            Swal.fire("Error", e.message, "error");
        }
    }
    async function generateBatchExcel(dataList, supplierName) {
        const workbook = new ExcelJS.Workbook();
        const sheet = workbook.addWorksheet("COD");
        const borderStyle = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' }, right: { style: 'thin' } };
        const fillHeader = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF0F0F0' } };
        const centerStyle = { horizontal: 'center', vertical: 'middle' };
        const boldFont = { bold: true };
        const today = new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        const firstRow = dataList[0] || {};
        sheet.mergeCells('A1:I1');
        const titleCell = sheet.getCell('A1');
        titleCell.value = "FORM COD / PEMBAYARAN CASH / TEMPO MASA TERTENTU";
        titleCell.alignment = centerStyle;
        titleCell.font = { bold: true, size: 14 };
        const headers = [
            "NO", "TGL NOTA FISIK", "TGL TERIMA NOTA", "TGL KEDATANGAN BARANG",
            "NAMA SUPPLIER", "KODE SUPPLIER", "NO. FAKTUR", "NAMA CABANG", "KETERANGAN"
        ];
        sheet.getRow(2).values = headers;
        sheet.getRow(2).font = boldFont;
        sheet.getRow(2).alignment = { ...centerStyle, wrapText: true };
        for (let i = 1; i <= 9; i++) {
            sheet.getCell(2, i).border = borderStyle;
            sheet.getCell(2, i).fill = fillHeader;
        }
        let currentRowIdx = 3;
        const startDataRow = 3;
        dataList.forEach((row, index) => {
            const { lengkap } = row;
            const rowData = [
                index + 1,
                formatDateExcel(row.tgl_nota),
                formatDateExcel(row.tgl_diterima),
                formatDateExcel(row.nota_tanggal_masuk),
                row.nama_supplier || '-',
                row.kode_supplier || '-',
                row.no_faktur_format || row.no_faktur,
                row.cabang_penerima || '-',
                lengkap === 'Ya' ? "Lengkap" : lengkap === 'Tidak' ? "Tidak Lengkap" : "Belum Lengkap",
            ];
            const currentRow = sheet.getRow(currentRowIdx);
            currentRow.values = rowData;
            currentRow.alignment = { vertical: 'top', wrapText: true };
            currentRow.getCell(1).alignment = { vertical: 'top', horizontal: 'center' };
            for (let i = 1; i <= 9; i++) {
                currentRow.getCell(i).border = borderStyle;
            }
            currentRowIdx++;
        });
        const endDataRow = currentRowIdx - 1;
        if (dataList.length > 1) {
            sheet.mergeCells(startDataRow, 5, endDataRow, 5);
            sheet.mergeCells(startDataRow, 6, endDataRow, 6);
            sheet.getCell(startDataRow, 5).alignment = centerStyle;
            sheet.getCell(startDataRow, 6).alignment = centerStyle;
        } else {
            sheet.getCell(startDataRow, 5).alignment = centerStyle;
            sheet.getCell(startDataRow, 6).alignment = centerStyle;
        }
        const bankHeaderRowIdx = currentRowIdx + 2;
        const bankValueRowIdx = bankHeaderRowIdx + 1;
        sheet.mergeCells(`A${bankHeaderRowIdx}:B${bankHeaderRowIdx}`); sheet.getCell(`A${bankHeaderRowIdx}`).value = "BANK";
        sheet.mergeCells(`C${bankHeaderRowIdx}:D${bankHeaderRowIdx}`); sheet.getCell(`C${bankHeaderRowIdx}`).value = "A/N";
        sheet.mergeCells(`E${bankHeaderRowIdx}:I${bankHeaderRowIdx}`); sheet.getCell(`E${bankHeaderRowIdx}`).value = "NO. REK";
        const bankRow = sheet.getRow(bankHeaderRowIdx);
        bankRow.font = boldFont;
        bankRow.alignment = centerStyle;
        ['A', 'C', 'E'].forEach(col => {
            sheet.getCell(`${col}${bankHeaderRowIdx}`).border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' } };
            sheet.getCell(`${col}${bankHeaderRowIdx}`).fill = fillHeader;
        });
        ['B', 'D', 'I'].forEach(col => {
            sheet.getCell(`${col}${bankHeaderRowIdx}`).border = { top: { style: 'thin' }, right: { style: 'thin' }, bottom: { style: 'thin' } };
        });
        sheet.mergeCells(`A${bankValueRowIdx}:B${bankValueRowIdx}`); sheet.getCell(`A${bankValueRowIdx}`).value = firstRow.nama_bank || '-';
        sheet.mergeCells(`C${bankValueRowIdx}:D${bankValueRowIdx}`); sheet.getCell(`C${bankValueRowIdx}`).value = firstRow.atas_nama_rek || '-';
        sheet.mergeCells(`E${bankValueRowIdx}:I${bankValueRowIdx}`); sheet.getCell(`E${bankValueRowIdx}`).value = firstRow.no_rek ? `${firstRow.no_rek}` : '-';
        const bankValRow = sheet.getRow(bankValueRowIdx);
        bankValRow.alignment = centerStyle;
        ['A', 'C', 'E'].forEach(col => {
            sheet.getCell(`${col}${bankValueRowIdx}`).border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' } };
        });
        ['B', 'D', 'I'].forEach(col => {
            sheet.getCell(`${col}${bankValueRowIdx}`).border = { top: { style: 'thin' }, right: { style: 'thin' }, bottom: { style: 'thin' } };
        });
        const startRowTTD = bankValueRowIdx + 3;
        sheet.mergeCells(`A${startRowTTD}:B${startRowTTD}`); sheet.getCell(`A${startRowTTD}`).value = "DIBUAT OLEH";
        sheet.mergeCells(`C${startRowTTD}:D${startRowTTD}`); sheet.getCell(`C${startRowTTD}`).value = "DIPERIKSA OLEH";
        sheet.mergeCells(`E${startRowTTD}:F${startRowTTD}`); sheet.getCell(`E${startRowTTD}`).value = "DITERIMA OLEH";
        sheet.mergeCells(`G${startRowTTD}:I${startRowTTD}`); sheet.getCell(`G${startRowTTD}`).value = "DISETUJUI OLEH";
        const ttdHeaderRow = sheet.getRow(startRowTTD);
        ttdHeaderRow.font = boldFont;
        ttdHeaderRow.alignment = centerStyle;
        ['A', 'C', 'E', 'G'].forEach(col => {
            sheet.getCell(`${col}${startRowTTD}`).border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' } };
            sheet.getCell(`${col}${startRowTTD}`).fill = fillHeader;
        });
        ['B', 'D', 'F', 'I'].forEach(col => {
            sheet.getCell(`${col}${startRowTTD}`).border = { top: { style: 'thin' }, right: { style: 'thin' }, bottom: { style: 'thin' } };
        });
        for (let r = startRowTTD + 1; r < startRowTTD + 5; r++) {
            ['A', 'C', 'E', 'G'].forEach(col => {
                sheet.getCell(`${col}${r}`).border = { left: { style: 'thin' } };
            });
            ['B', 'D', 'F', 'I'].forEach(col => {
                sheet.getCell(`${col}${r}`).border = { right: { style: 'thin' } };
            });
            sheet.mergeCells(`A${r}:B${r}`);
            sheet.mergeCells(`C${r}:D${r}`);
            sheet.mergeCells(`E${r}:F${r}`);
            sheet.mergeCells(`G${r}:I${r}`);
        }
        const rowNama = startRowTTD + 5;
        const rowTgl = startRowTTD + 6;
        const setFooterContent = (colStart, colEnd, valNama) => {
            sheet.mergeCells(`${colStart}${rowNama}:${colEnd}${rowNama}`);
            sheet.getCell(`${colStart}${rowNama}`).value = valNama;
            sheet.mergeCells(`${colStart}${rowTgl}:${colEnd}${rowTgl}`);
            sheet.getCell(`${colStart}${rowTgl}`).value = "Tgl: ....................";
            sheet.getCell(`${colStart}${rowNama}`).border = { left: { style: 'thin' } };
            sheet.getCell(`${colEnd}${rowNama}`).border = { right: { style: 'thin' } };
            sheet.getCell(`${colStart}${rowTgl}`).border = { left: { style: 'thin' }, bottom: { style: 'thin' } };
            sheet.getCell(`${colEnd}${rowTgl}`).border = { right: { style: 'thin' }, bottom: { style: 'thin' } };
        };
        setFooterContent('A', 'B', "(....................)");
        setFooterContent('C', 'D', "(....................)");
        setFooterContent('E', 'F', "(....................)");
        setFooterContent('G', 'I', "(....................)");
        sheet.getRow(rowNama).alignment = { horizontal: 'center', vertical: 'bottom' };
        sheet.getRow(rowNama).height = 20;
        sheet.getRow(rowTgl).font = { size: 9, italic: true };
        sheet.getRow(rowTgl).alignment = { horizontal: 'center', vertical: 'top' };
        sheet.getColumn(1).width = 5;
        sheet.getColumn(2).width = 15;
        sheet.getColumn(3).width = 15;
        sheet.getColumn(4).width = 15;
        sheet.getColumn(5).width = 25;
        sheet.getColumn(6).width = 15;
        sheet.getColumn(7).width = 20;
        sheet.getColumn(8).width = 20;
        sheet.getColumn(9).width = 30;
        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
        const url = window.URL.createObjectURL(blob);
        const anchor = document.createElement("a");
        anchor.href = url;
        const safeSupplier = supplierName.replace(/[^a-zA-Z0-9]/g, '_');
        anchor.download = `COD_${safeSupplier}.xlsx`;
        anchor.click();
        window.URL.revokeObjectURL(url);
    }
    function formatDateExcel(dateString) {
        if (!dateString || dateString === '0000-00-00') return "-";
        const d = new Date(dateString);
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }
    loadData();
}); 