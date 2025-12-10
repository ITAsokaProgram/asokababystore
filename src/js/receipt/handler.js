document.addEventListener("DOMContentLoaded", () => {
  const filterStore = document.getElementById("kode_store_filter");
  const tableBody = document.getElementById("receipt-table-body");
  const filterForm = document.getElementById("filter-form");
  const paginationInfo = document.getElementById("pagination-info");
  const paginationLinks = document.getElementById("pagination-links");

  // Elements Summary
  const elTotalSelisih = document.getElementById("summary-total-selisih");
  const elTotalRupiahSelisih = document.getElementById(
    "summary-total-rupiah-selisih"
  ); // New Element
  const elTotalMissing = document.getElementById("summary-total-missing");
  const btnShowSelisih = document.getElementById("btn-show-selisih");
  const btnShowMissing = document.getElementById("btn-show-missing");

  let summaryData = {
    list_selisih: [],
    list_belum_ada: [],
  };

  let currentTableData = [];

  function formatRupiah(number) {
    if (isNaN(number) || number === null) return "0";
    return new Intl.NumberFormat("id-ID", {
      style: "decimal",
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(number);
  }

  function formatJustDate(dateString) {
    if (!dateString) return "-";
    return dateString.substring(0, 10);
  }

  window.openDetailRow = function (index) {
    const rowData = currentTableData[index];
    if (rowData) {
      window.dispatchEvent(
        new CustomEvent("open-detail-modal", { detail: rowData })
      );
    }
  };

  // ... (Event Listeners Modal Summary tetap sama) ...
  if (btnShowSelisih) {
    btnShowSelisih.addEventListener("click", () => {
      window.dispatchEvent(
        new CustomEvent("open-summary-modal", {
          detail: {
            title: "Faktur Selisih",
            list: summaryData.list_selisih,
          },
        })
      );
    });
  }

  if (btnShowMissing) {
    btnShowMissing.addEventListener("click", () => {
      window.dispatchEvent(
        new CustomEvent("open-summary-modal", {
          detail: {
            title: "Faktur Belum Ada (Checking)",
            list: summaryData.list_belum_ada,
          },
        })
      );
    });
  }

  // ... (loadStores tetap sama) ...
  async function loadStores() {
    try {
      const response = await fetch("/src/api/shared/get_all_store.php");
      const result = await response.json();
      if (result.success) {
        let options = '<option value="">Semua Cabang</option>';
        result.data.forEach((store) => {
          options += `<option value="${store.Kd_Store}">${store.Nm_Alias} (${store.Kd_Store})</option>`;
        });
        if (filterStore) filterStore.innerHTML = options;
      }
    } catch (error) {
      console.error("Gagal load store:", error);
    }
  }

  async function loadData() {
    setLoading(true);
    const formData = new FormData(filterForm);
    const params = new URLSearchParams(formData);

    try {
      const response = await fetch(
        `/src/api/receipt/get_receipts.php?${params.toString()}`
      );
      const data = await response.json();

      if (data.error) throw new Error(data.error);

      if (data.summary) {
        elTotalSelisih.textContent = formatRupiah(data.summary.total_selisih);
        elTotalMissing.textContent = formatRupiah(data.summary.total_belum_ada);
        // Update Total Nominal Selisih
        if (elTotalRupiahSelisih) {
          elTotalRupiahSelisih.textContent =
            "Rp " + formatRupiah(data.summary.total_selisih_rupiah);
        }

        summaryData.list_selisih = data.summary.list_selisih;
        summaryData.list_belum_ada = data.summary.list_belum_ada;
      }

      currentTableData = data.tabel_data || [];
      renderTable(data.tabel_data, data.pagination.offset);
      renderPagination(data.pagination);
    } catch (error) {
      console.error(error);
      tableBody.innerHTML = `<tr><td colspan="8" class="text-center text-red-500 p-4">Error: ${error.message}</td></tr>`;
    } finally {
      setLoading(false);
    }
  }

  function setLoading(isLoading) {
    if (isLoading) {
      // Colspan jadi 8 karena ada tambahan kolom Status
      tableBody.innerHTML = `<tr><td colspan="8" class="text-center p-8"><div class="spinner-simple"></div></td></tr>`;
    }
  }

  function renderTable(rows, offset) {
    if (!rows || rows.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="8" class="text-center p-8 text-gray-500">Tidak ada data ditemukan.</td></tr>`;
      return;
    }

    let html = "";
    rows.forEach((row, index) => {
      const storeLabel = row.kode_store
        ? `<span class="badge-pink">${row.Nm_Alias || row.kode_store}</span>`
        : "-";

      // LOGIC STATUS BADGE
      let statusBadge = "";
      if (row.status_data === "MATCH") {
        statusBadge = `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                           <i class="fas fa-check-circle mr-1"></i> Sesuai
                         </span>`;
      } else if (row.status_data === "DIFF") {
        statusBadge = `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                           <i class="fas fa-times-circle mr-1"></i> Selisih
                         </span>`;
      } else if (row.status_data === "NOT_FOUND_IN_ERP") {
        statusBadge = `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                           <i class="fas fa-question-circle mr-1"></i> Tdk Ada di Pusat
                         </span>`;
      }

      let rowClass = "hover:bg-gray-50";

      html += `
        <tr class="${rowClass} border-b border-gray-100 transition cursor-pointer" onclick="openDetailRow(${index})">
            <td class="text-center text-gray-500 text-sm py-3">${
              offset + index + 1
            }</td>
            <td class="text-sm">${statusBadge}</td> <td class="text-sm text-gray-700">${formatJustDate(
        row.tgl_tiba
      )}</td>
            <td class="text-sm font-semibold text-gray-600">${storeLabel}</td>
            <td class="text-sm text-gray-600">
                <div class="font-medium text-pink-600">${row.kode_supp}</div>
            </td>
            <td class="text-sm font-bold text-gray-800 font-mono">${
              row.no_faktur
            }</td>
            <td class="text-right font-mono text-blue-600 font-semibold">${formatRupiah(
              row.total_check
            )}</td>
            <td class="text-sm text-gray-500 italic truncate" style="max-width: 5rem;">${
              row.keterangan || "-"
            }</td>
        </tr>
      `;
    });
    tableBody.innerHTML = html;
  }

  // ... (renderPagination dan Logic lainnya tetap sama) ...
  function renderPagination(pagination) {
    // Copy paste logika pagination sebelumnya disini, tidak ada perubahan logic
    // ...
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

    // Prev Button
    linksHtml += `
            <a href="${
              current_page > 1 ? build_pagination_url(current_page - 1) : "#"
            }" 
               class="pagination-link ${
                 current_page === 1 ? "pagination-disabled" : ""
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
                   class="pagination-link ${
                     page_num === current_page ? "pagination-active" : ""
                   }">
                    ${page_num}
                </a>
            `;
      last_page = page_num;
    }

    // Next Button
    linksHtml += `
            <a href="${
              current_page < total_pages
                ? build_pagination_url(current_page + 1)
                : "#"
            }" 
               class="pagination-link ${
                 current_page === total_pages ? "pagination-disabled" : ""
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

  loadStores();
  loadData();
});

function build_pagination_url(newPage) {
  const params = new URLSearchParams(window.location.search);
  params.set("page", newPage);
  return "?" + params.toString();
}
