document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("logDetailModal");
  const closeButton = document.getElementById("closeLogModal");
  const modalCabangName = document.getElementById("modalCabangName");
  const modalBody = document.getElementById("modalBodyContent");
  const tableBody = document.getElementById("log-table-body");
  const filterForm = document.getElementById("filter-form");
  const filterSelectTanggal = document.getElementById("tanggal");
  const filterSubmitButton = document.getElementById("filter-submit-button");
  const tanggalDipilihTeks = document.getElementById("tanggal-dipilih-teks");
  const summaryTotal = document.getElementById("summary-total-cabang");
  const summarySinkron = document.getElementById("summary-sudah-sinkron");
  const summaryBelum = document.getElementById("summary-belum-sinkron");
  const showAllLogsButton = document.getElementById("show-all-logs-button");

  async function loadLogSummary() {
    const selectedTanggal = filterSelectTanggal.value;
    setLoadingState(true);
    try {
      const response = await fetch(
        `/src/api/log_backup/get_log_summary.php?tanggal=${encodeURIComponent(
          selectedTanggal
        )}`
      );
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(
          errorData.error || `HTTP error! status: ${response.status}`
        );
      }
      const data = await response.json();
      if (data.error) {
        throw new Error(data.error);
      }
      updateSummaryCards(data.summary);
      renderTable(data.tabel_data, selectedTanggal);
      tanggalDipilihTeks.textContent = selectedTanggal;
    } catch (error) {
      console.error("Error loading log summary:", error);
      showTableError(error.message);
    } finally {
      setLoadingState(false);
    }
  }

  function setLoadingState(isLoading) {
    if (isLoading) {
      filterSubmitButton.disabled = true;
      filterSubmitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span>Memuat...</span>`;
      if (showAllLogsButton) showAllLogsButton.disabled = true;
      tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center p-8">
                        <div class="spinner-simple"></div>
                        <p class="mt-2 text-gray-500">Memuat data...</p>
                    </td>
                </tr>`;
      summaryTotal.textContent = "-";
      summarySinkron.textContent = "-";
      summaryBelum.textContent = "-";
    } else {
      filterSubmitButton.disabled = false;
      filterSubmitButton.innerHTML = `<i class="fas fa-filter"></i><span>Tampilkan</span>`;
      if (showAllLogsButton) showAllLogsButton.disabled = false;
    }
  }

  function showTableError(message) {
    tableBody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center p-8 text-red-600">
                    <i class="fas fa-exclamation-triangle fa-lg mb-2"></i>
                    <p>Gagal memuat data: ${message}</p>
                </td>
            </tr>`;
  }

  function updateSummaryCards(summary) {
    summaryTotal.textContent = summary.total_cabang || 0;
    summarySinkron.textContent = summary.total_sudah_sinkron || 0;
    summaryBelum.textContent = summary.total_belum_sinkron || 0;
  }
  function renderTable(tabel_data, selectedTanggal) {
    if (!tabel_data || tabel_data.length === 0) {
      tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center p-8 text-gray-500">
                        <i class="fas fa-inbox fa-lg mb-2"></i>
                        <p>Tidak ada data ditemukan untuk tanggal ini.</p>
                    </td>
                </tr>`;
      return;
    }
    let htmlRows = "";
    tabel_data.forEach((row) => {
      htmlRows += `
                <tr class="clickable-row" 
                    data-alias="${row.nama_cabang}" 
                    data-tanggal="${selectedTanggal}">
                    <td class="font-semibold text-gray-900">${
                      row.nama_cabang
                    }</td>
                    <td>
                        <span class="badge ${row.status_class}">
                            ${row.status}
                        </span>
                    </td>
                    <td>${row.total_sinkron}</td>
                    <td>
                        ${
                          row.total_error > 0
                            ? `<span class="font-bold text-red-600">${row.total_error}</span>`
                            : row.total_error
                        }
                    </td>
                </tr>
            `;
    });
    tableBody.innerHTML = htmlRows;
  }
  if (tableBody) {
    tableBody.addEventListener("click", (e) => {
      const row = e.target.closest("tr.clickable-row");
      if (row) {
        // TAMBAHKAN .trim() DI SINI
        const alias = row.dataset.alias.trim();
        const tanggal = row.dataset.tanggal;
        if (alias && tanggal) {
          openModal(alias, tanggal);
        }
      }
    });
  }
  function openModal(alias, tanggal) {
    if (!modal || !modalCabangName || !modalBody) return;
    modal.dataset.modalType = "branch";
    // TAMBAHKAN .trim() DI SINI
    modalCabangName.textContent = alias.trim();
    modalBody.innerHTML =
      '<p class="text-center text-slate-400">Memuat data...</p>';
    modal.style.display = "flex";
    // DAN DI SINI
    fetchLogDetail(alias.trim(), tanggal);
  }
  function closeModal() {
    if (!modal) return;
    modal.style.display = "none";
    modalCabangName.textContent = "";
    modalBody.innerHTML = "";
    modal.dataset.modalType = "";
  }

  function openModalAllLogs(tanggal) {
    if (!modal || !modalCabangName || !modalBody) return;
    modal.dataset.modalType = "all";
    modalCabangName.textContent = `Semua Log - ${tanggal}`;
    modalBody.innerHTML =
      '<p class="text-center text-slate-400">Memuat data...</p>';
    modal.style.display = "flex";
    fetchLogAll(tanggal);
  }
  if (closeButton) {
    closeButton.addEventListener("click", closeModal);
  }
  if (modal) {
    modal.addEventListener("click", (e) => {
      if (e.target === modal) {
        closeModal();
      }
    });
  }
  async function fetchLogDetail(alias, tanggal) {
    try {
      const response = await fetch(
        `/src/api/log_backup/get_log_detail.php?alias=${encodeURIComponent(
          alias
        )}&tanggal=${encodeURIComponent(tanggal)}`
      );
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();
      if (data.success) {
        const formattedLog = formatLogContent(data.log_content);
        modalBody.innerHTML = "<pre>" + formattedLog + "</pre>";
      } else {
        throw new Error(data.message || "Gagal mengambil data log.");
      }
    } catch (error) {
      console.error("Error fetching log detail:", error);
      modalBody.innerHTML = `<pre><span class="log-error">Gagal memuat detail log: ${error.message}</span></pre>`;
    }
  }

  async function fetchLogAll(tanggal) {
    try {
      const response = await fetch(
        `/src/api/log_backup/get_log_all.php?tanggal=${encodeURIComponent(
          tanggal
        )}`
      );
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();
      if (data.success) {
        const formattedLog = formatLogContent(data.log_content);
        modalBody.innerHTML = "<pre>" + formattedLog + "</pre>";
      } else {
        throw new Error(data.message || "Gagal mengambil data log.");
      }
    } catch (error) {
      console.error("Error fetching all logs:", error);
      modalBody.innerHTML = `<pre><span class="log-error">Gagal memuat detail log: ${error.message}</span></pre>`;
    }
  }

  function formatLogContent(content) {
    let escapedContent = content
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;");
    const lines = escapedContent.split("\n");
    const formattedLines = lines.map((line) => {
      if (line.includes("SUCCESS:")) {
        return `<span class="log-success">${line}</span>`;
      }
      if (line.includes("ERROR:")) {
        return `<span class="log-error">${line}</span>`;
      }
      if (line.toUpperCase().includes("ERROR") && !line.includes("ERROR:")) {
        return `<span class="log-error">${line}</span>`;
      }
      if (
        modal.dataset.modalType === "branch" &&
        modalCabangName &&
        line.trim() === modalCabangName.textContent
      ) {
        return `<span style="color: #60a5fa; font-weight: bold;">${line}</span>`;
      }
      return line;
    });
    return formattedLines.join("\n");
  }
  if (filterForm) {
    filterForm.addEventListener("submit", (e) => {
      e.preventDefault();
      loadLogSummary();
    });
  }
  if (showAllLogsButton) {
    showAllLogsButton.addEventListener("click", () => {
      const selectedTanggal = filterSelectTanggal.value;
      openModalAllLogs(selectedTanggal);
    });
  }
  loadLogSummary();
});
