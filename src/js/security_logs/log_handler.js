document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.getElementById("log-table-body");
    const filterForm = document.getElementById("filter-form");
    const inputStartDate = document.getElementById("start_date");
    const inputEndDate = document.getElementById("end_date");
    const filterSubmitButton = document.getElementById("filter-submit-button");
    const periodeTeks = document.getElementById("periode-teks");

    // Summary Elements
    const summaryTotal = document.getElementById("summary-total-events");
    const summaryIps = document.getElementById("summary-unique-ips");
    const summaryService = document.getElementById("summary-top-service");

    // 1. Fungsi untuk mengubah URL Browser tanpa reload (PushState)
    function updateUrlParams(start, end) {
        const url = new URL(window.location);
        url.searchParams.set("start_date", start);
        url.searchParams.set("end_date", end);
        window.history.pushState({}, "", url);
    }

    // 2. Fungsi memuat filter dari URL (Backup jika PHP tidak merender value)
    function syncInputsFromUrl() {
        const params = new URLSearchParams(window.location.search);
        if (params.has("start_date")) {
            inputStartDate.value = params.get("start_date");
        }
        if (params.has("end_date")) {
            inputEndDate.value = params.get("end_date");
        }
    }

    async function loadLogs() {
        const start = inputStartDate.value;
        const end = inputEndDate.value;

        // Panggil fungsi update URL setiap kali load data
        updateUrlParams(start, end);

        setLoadingState(true);

        try {
            const response = await fetch(
                `/src/api/security_logs/get_logs.php?start_date=${encodeURIComponent(start)}&end_date=${encodeURIComponent(end)}`
            );

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.error) {
                throw new Error(data.error);
            }

            updateSummaryCards(data.summary);
            renderTable(data.logs);

            periodeTeks.textContent = `${formatDateID(start)} - ${formatDateID(end)}`;

        } catch (error) {
            console.error("Error loading logs:", error);
            showTableError(error.message);
        } finally {
            setLoadingState(false);
        }
    }

    function formatDateID(dateString) {
        const options = { day: 'numeric', month: 'long', year: 'numeric' };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    }

    function setLoadingState(isLoading) {
        if (isLoading) {
            filterSubmitButton.disabled = true;
            filterSubmitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span>Memuat...</span>`;
            tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-8"><div class="spinner-simple"></div><p class="mt-2 text-gray-500">Memuat data...</p></td></tr>`;
            summaryTotal.textContent = "-";
            summaryIps.textContent = "-";
            summaryService.textContent = "-";
        } else {
            filterSubmitButton.disabled = false;
            filterSubmitButton.innerHTML = `<i class="fas fa-filter"></i><span>Tampilkan</span>`;
        }
    }

    function showTableError(message) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-8 text-red-600"><i class="fas fa-exclamation-triangle fa-lg mb-2"></i><p>Gagal memuat data: ${message}</p></td></tr>`;
    }

    function updateSummaryCards(summary) {
        summaryTotal.textContent = summary.total_events || 0;
        summaryIps.textContent = summary.unique_ips || 0;
        summaryService.textContent = summary.top_service || "-";
    }

    function renderTable(logs) {
        if (!logs || logs.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-8 text-gray-500"><i class="fas fa-check-circle fa-lg mb-2 text-green-500"></i><p>Tidak ada log keamanan pada periode ini.</p></td></tr>`;
            return;
        }

        let htmlRows = "";
        logs.forEach((row) => {
            let badgeClass = "bg-gray-100 text-gray-800";
            let icon = "fa-cog";

            if (row.service_name === 'sshd') {
                badgeClass = "bg-red-100 text-red-800";
                icon = "fa-terminal";
            } else if (row.service_name === 'mysqld-auth') {
                badgeClass = "bg-blue-100 text-blue-800";
                icon = "fa-database";
            }

            const dateTime = new Date(row.log_date).toLocaleString('id-ID', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit', second: '2-digit'
            });

            htmlRows += `
                <tr class="hover:bg-gray-50">
                    <td class="font-mono text-gray-600 text-sm">${dateTime}</td>
                    <td class="font-semibold text-gray-900">${row.ip_address}</td>
                    <td><span class="text-gray-700">${row.country_code || '<span class="text-gray-400 text-xs italic">N/A</span>'}</span></td>
                    <td>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass}">
                            <i class="fas ${icon} mr-1.5"></i>${row.service_name}
                        </span>
                    </td>
                </tr>`;
        });
        tableBody.innerHTML = htmlRows;
    }

    if (filterForm) {
        filterForm.addEventListener("submit", (e) => {
            e.preventDefault();
            loadLogs();
        });
    }

    // 3. Inisialisasi: Sync input dari URL dulu (jika ada), baru load data
    syncInputsFromUrl();
    loadLogs();
});