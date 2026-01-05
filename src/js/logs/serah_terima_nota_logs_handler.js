document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.getElementById("table-body");
    const filterForm = document.getElementById("filter-form");
    const filterSubmitButton = document.getElementById("filter-submit-button");
    const summaryTotalLogs = document.getElementById("summary-total-logs");
    const summaryInsert = document.getElementById("summary-insert");
    const summaryUpdate = document.getElementById("summary-update");
    const summaryDelete = document.getElementById("summary-delete");
    const paginationInfo = document.getElementById("pagination-info");
    const paginationLinks = document.getElementById("pagination-links");
    function formatDateIndo(dateString) {
        if (!dateString) return "-";
        const date = new Date(dateString);
        return new Intl.DateTimeFormat("id-ID", {
            day: "numeric",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
        }).format(date);
    }
    function formatNumber(num) {
        return new Intl.NumberFormat("id-ID").format(num || 0);
    }
    function getUrlParams() {
        const params = new URLSearchParams(window.location.search);
        const inputMulai = document.getElementById("tgl_mulai");
        const inputSelesai = document.getElementById("tgl_selesai");
        const inputSearch = document.getElementById("search");
        return {
            tgl_mulai: params.get("tgl_mulai") || (inputMulai ? inputMulai.value : ""),
            tgl_selesai: params.get("tgl_selesai") || (inputSelesai ? inputSelesai.value : ""),
            search: params.get("search") || (inputSearch ? inputSearch.value : ""),
            page: parseInt(params.get("page") || "1", 10),
        };
    }
    function updateUrl(params) {
        const newUrl = `${window.location.pathname}?${new URLSearchParams(params).toString()}`;
        window.history.pushState({}, "", newUrl);
    }
    async function loadLogData() {
        const params = getUrlParams();
        setLoadingState(true);
        const queryString = new URLSearchParams(params).toString();
        try {
            const response = await fetch(`/src/api/logs/get_serah_terima_nota_logs.php?${queryString}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            if (data.error) {
                throw new Error(data.error);
            }
            if (data.summary) {
                if (summaryTotalLogs) summaryTotalLogs.textContent = formatNumber(data.summary.total_logs);
                if (summaryInsert) summaryInsert.textContent = formatNumber(data.summary.total_insert);
                if (summaryUpdate) summaryUpdate.textContent = formatNumber(data.summary.total_update);
                if (summaryDelete) summaryDelete.textContent = formatNumber(data.summary.total_delete);
            }
            renderTable(data.tabel_data, data.pagination);
            renderPagination(data.pagination);
        } catch (error) {
            console.error("Error loading log data:", error);
            if (tableBody) {
                tableBody.innerHTML = `
          <tr>
            <td colspan="6" class="text-center p-8 text-red-600">
              <i class="fas fa-exclamation-triangle fa-lg mb-2"></i>
              <p>Gagal memuat data: ${error.message}</p>
            </td>
          </tr>`;
            }
        } finally {
            setLoadingState(false);
        }
    }
    function setLoadingState(isLoading) {
        if (isLoading) {
            if (filterSubmitButton) {
                filterSubmitButton.disabled = true;
                filterSubmitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span>Memuat...</span>`;
            }
            if (tableBody) {
                tableBody.innerHTML = `
          <tr>
            <td colspan="6" class="text-center p-8">
              <div class="spinner-simple"></div>
              <p class="mt-2 text-gray-500">Sedang mengambil data...</p>
            </td>
          </tr>`;
            }
            [summaryTotalLogs, summaryInsert, summaryUpdate, summaryDelete].forEach(el => {
                if (el) el.textContent = "-";
            });
        } else {
            if (filterSubmitButton) {
                filterSubmitButton.disabled = false;
                filterSubmitButton.innerHTML = `<i class="fas fa-filter"></i><span>Tampilkan</span>`;
            }
        }
    }
    function renderTable(data, pagination) {
        if (!tableBody) return;
        if (!data || data.length === 0) {
            tableBody.innerHTML = `
        <tr>
          <td colspan="6" class="text-center p-8 text-gray-500">
            <i class="fas fa-search fa-lg mb-2 text-gray-300"></i>
            <p>Tidak ada data ditemukan.</p>
          </td>
        </tr>`;
            return;
        }
        const offset = pagination.offset || 0;
        let htmlRows = "";
        data.forEach((row, index) => {
            let badgeClass = "bg-gray-100 text-gray-600 border-gray-200";
            let icon = "fa-info-circle";
            let label = row.action;
            switch (row.action) {
                case 'INSERT':
                    badgeClass = "bg-insert";
                    icon = "fa-plus";
                    label = "INPUT";
                    break;
                case 'UPDATE':
                    badgeClass = "bg-update";
                    icon = "fa-pen";
                    label = "EDIT";
                    break;
                case 'UPDATE_STATUS':
                    badgeClass = "bg-status";
                    icon = "fa-check-double";
                    label = "STATUS";
                    break;
                case 'SOFT_DELETE':
                    badgeClass = "bg-delete";
                    icon = "fa-trash";
                    label = "HAPUS";
                    break;
            }
            const badge = `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold border ${badgeClass}">
                      <i class="fas ${icon} mr-1"></i> ${label}
                     </span>`;
            const rowDataString = encodeURIComponent(JSON.stringify(row));
            htmlRows += `
        <tr class="hover:bg-gray-50 transition-colors">
            <td>${offset + index + 1}</td>
            <td class="text-gray-600 text-sm">
                ${formatDateIndo(row.tgl_log)}
            </td>
            <td>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 text-xs">
                        <i class="fas fa-user"></i>
                    </div>
                    <span class="font-medium text-gray-700 text-sm">${row.user_id || 'System'}</span>
                </div>
            </td>
            <td>${badge}</td>
            <td class="font-mono text-sm font-semibold text-gray-700">
                ${row.no_faktur}
            </td>
            <td class="text-center">
                <button class="btn-detail text-blue-600 hover:text-blue-800 hover:bg-blue-50 px-3 py-1 rounded transition-colors"
                    data-log="${rowDataString}">
                    <i class="fas fa-eye"></i> Detail
                </button>
            </td>
        </tr>
      `;
        });
        tableBody.innerHTML = htmlRows;
        document.querySelectorAll(".btn-detail").forEach(btn => {
            btn.addEventListener("click", function () {
                const raw = decodeURIComponent(this.getAttribute("data-log"));
                const logData = JSON.parse(raw);
                showDetailModal(logData);
            });
        });
    }
    function showDetailModal(log) {
        let oldData = log.old_data ? (typeof log.old_data === 'string' ? JSON.parse(log.old_data) : log.old_data) : {};
        let newData = log.new_data ? (typeof log.new_data === 'string' ? JSON.parse(log.new_data) : log.new_data) : {};
        let contentHtml = '';
        if (log.action === 'INSERT') {
            contentHtml = buildInsertTable(newData);
        } else if (log.action === 'SOFT_DELETE') {
            contentHtml = buildDeleteInfo(log, oldData, newData);
        } else {
            contentHtml = buildDiffTable(oldData, newData);
        }
        Swal.fire({
            title: `<strong>Detail Log: ${log.action}</strong>`,
            html: `<div class="text-left mt-2 max-h-96 overflow-y-auto">${contentHtml}</div>`,
            width: '600px',
            showCloseButton: true,
            showConfirmButton: false,
            footer: `<span class="text-xs text-gray-400">IP: ${log.ip_address} | ID: ${log.id}</span>`
        });
    }
    function buildInsertTable(data) {
        let rows = '';
        for (const [key, value] of Object.entries(data)) {
            if (value !== null && value !== "") {
                rows += `<tr><th width="40%">${key}</th><td><span class="val-new">${value}</span></td></tr>`;
            }
        }
        return `<table class="detail-table"><tbody>${rows}</tbody></table>`;
    }
    function buildDeleteInfo(log, oldData, newData) {
        let info = '';
        if (newData && newData.diotorisasi_oleh) {
            info = `<div class="bg-red-50 p-3 rounded mb-3 border border-red-100">
                    <p class="text-sm text-red-800"><strong>Dihapus Oleh:</strong> ${newData.dihapus_oleh_id || log.user_id}</p>
                    <p class="text-sm text-red-800"><strong>Otorisasi:</strong> ${newData.diotorisasi_oleh}</p>
                  </div>`;
        }
        return info + `<p class="mb-2 font-semibold text-gray-700">Data Sebelum Dihapus:</p>` + buildInsertTable(oldData);
    }
    function buildDiffTable(oldData, newData) {
        let rows = '';
        let allKeys = new Set([...Object.keys(oldData), ...Object.keys(newData)]);
        let hasChanges = false;
        const ignoredKeys = ['edit_pada', 'diedit_oleh', 'dibuat_pada', 'dibuat_oleh'];
        allKeys.forEach(key => {
            if (ignoredKeys.includes(key)) return;
            if (Object.prototype.hasOwnProperty.call(oldData, key) && !Object.prototype.hasOwnProperty.call(newData, key)) {
                return;
            }
            let valOld = oldData[key];
            let valNew = newData[key];
            if (!isNaN(valOld) && !isNaN(valNew) && valOld !== null && valNew !== null && valOld !== "" && valNew !== "") {
                if (parseFloat(valOld) == parseFloat(valNew)) return;
            } else {
                if (valOld == valNew) return;
            }
            let displayOld = valOld === undefined || valOld === null ? '(null)' : valOld;
            let displayNew = valNew === undefined || valNew === null ? '(null)' : valNew;
            rows += `<tr>
                    <th width="30%">${key}</th>
                    <td>
                        <div class="flex flex-col sm:flex-row sm:items-center gap-1">
                            <span class="val-old text-xs">${displayOld}</span>
                            <i class="fas fa-arrow-right text-gray-400 text-xs mx-1"></i>
                            <span class="val-new">${displayNew}</span>
                        </div>
                    </td>
                   </tr>`;
            hasChanges = true;
        });
        if (!hasChanges) return '<p class="text-center text-gray-500 italic">Tidak ada perubahan data yang signifikan.</p>';
        return `<table class="detail-table"><tbody>${rows}</tbody></table>`;
    }
    function renderPagination(pagination) {
        if (!paginationInfo || !paginationLinks) return;
        if (!pagination || pagination.total_rows === 0) {
            paginationInfo.textContent = "";
            paginationLinks.innerHTML = "";
            return;
        }
        const { current_page, total_pages, total_rows, limit, offset } = pagination;
        const start_row = offset + 1;
        const end_row = Math.min(offset + limit, total_rows);
        paginationInfo.textContent = `Menampilkan ${start_row} - ${end_row} dari ${total_rows} data`;
        let linksHtml = "";
        linksHtml += `
            <a href="${current_page > 1 ? "#" : "javascript:void(0)"}" 
               data-page="${current_page - 1}"
               class="pagination-link ${current_page === 1 ? "pagination-disabled" : ""}">
                <i class="fas fa-chevron-left"></i>
            </a>
        `;
        const max_pages_around = 2;
        let pages_to_show = [];
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
                <a href="#" data-page="${page_num}" 
                   class="pagination-link ${page_num === current_page ? "pagination-active" : ""}">
                    ${page_num}
                </a>
            `;
            last_page = page_num;
        }
        linksHtml += `
            <a href="${current_page < total_pages ? "#" : "javascript:void(0)"}" 
               data-page="${current_page + 1}"
               class="pagination-link ${current_page === total_pages ? "pagination-disabled" : ""}">
                <i class="fas fa-chevron-right"></i>
            </a>
        `;
        paginationLinks.innerHTML = linksHtml;
        document.querySelectorAll(".pagination-link").forEach((link) => {
            link.addEventListener("click", (e) => {
                e.preventDefault();
                if (!link.classList.contains("pagination-disabled")) {
                    const page = link.getAttribute("data-page");
                    const params = getUrlParams();
                    params.page = page;
                    updateUrl(params);
                    loadLogData();
                }
            });
        });
    }
    if (filterForm) {
        filterForm.addEventListener("submit", (e) => {
            e.preventDefault();
            const formData = new FormData(filterForm);
            const params = {};
            formData.forEach((value, key) => {
                params[key] = value;
            });
            params.page = 1;
            updateUrl(params);
            loadLogData();
        });
    }
    loadLogData();
});