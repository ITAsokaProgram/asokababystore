document.addEventListener("DOMContentLoaded", () => {
  const filterStore = document.getElementById("kode_store_filter");
  const tableBody = document.getElementById("koreksi-table-body");
  const filterForm = document.getElementById("filter-form");
  const paginationInfo = document.getElementById("pagination-info");
  const paginationLinks = document.getElementById("pagination-links");

  // Elements for Summary Cards
  const elTotalSelisih = document.getElementById("summary-total-selisih");
  const elTotalRupiahSelisih = document.getElementById("summary-total-rupiah-selisih");
  const elTotalMissing = document.getElementById("summary-total-missing");
  const elTotalNotFound = document.getElementById("summary-total-notfound");

  const btnShowSelisih = document.getElementById("btn-show-selisih");
  const btnShowRupiahSelisih = document.getElementById("btn-show-rupiah-selisih");
  const btnShowMissing = document.getElementById("btn-show-missing");
  const btnShowNotFound = document.getElementById("btn-show-notfound");

  let summaryData = {
    list_selisih: [],
    list_belum_ada: [],
    list_tidak_ditemukan: [],
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

  // --- Handling Modals ---
  window.openDetailRow = function (index) {
    const rowData = currentTableData[index];
    if (rowData) {
      window.dispatchEvent(
        new CustomEvent("open-detail-modal", { detail: rowData })
      );
    }
  };

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
  if (btnShowRupiahSelisih) {
    btnShowRupiahSelisih.addEventListener("click", () => {
      window.dispatchEvent(
        new CustomEvent("open-summary-modal", {
          detail: {
            title: "Detail Nominal Selisih",
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
            title: "Belum Ada (Ada di ERP, Belum Scan)",
            list: summaryData.list_belum_ada,
          },
        })
      );
    });
  }
  if (btnShowNotFound) {
    btnShowNotFound.addEventListener("click", () => {
      window.dispatchEvent(
        new CustomEvent("open-summary-modal", {
          detail: {
            title: "Data Tidak Ditemukan (Ada Scan, Tdk Ada ERP)",
            list: summaryData.list_tidak_ditemukan,
          },
        })
      );
    });
  }

  function getCookie(name) {
    let nameEQ = name + "=";
    let ca = document.cookie.split(";");
    for (let i = 0; i < ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) == " ") c = c.substring(1, c.length);
      if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
  }

  async function loadStores() {
    const token = getCookie("admin_token");
    const url = "/src/api/cabang/get_kode.php";
    try {
      const response = await fetch(url, {
        headers: {
          Accept: "application/json",
          Authorization: "Bearer " + token,
        },
      });
      const result = await response.json();
      const select = filterStore;
      if (select) {
        select.innerHTML = "";
        const defaultOption = new Option("Pilih Cabang", "", true, true);
        defaultOption.disabled = true;
        select.add(defaultOption);
        const urlParams = new URLSearchParams(window.location.search);
        const currentStore = urlParams.get('kode_store');

        if (result.data && result.data.length > 0) {
          result.data.forEach((store) => {
            const option = new Option(
              `${store.nama_cabang} (${store.store})`,
              store.store
            );
            select.add(option);
          });
          if (currentStore) {
            select.value = currentStore;
          }
        } else {
          select.innerHTML = '<option value="">Gagal memuat data cabang</option>';
        }
      }
    } catch (error) {
      console.error("Error fetching stores:", error);
      if (filterStore) {
        filterStore.innerHTML = '<option value="">Error koneksi</option>';
      }
    }
  }

  async function loadData() {
    const formData = new FormData(filterForm);
    const kodeStore = formData.get("kode_store");
    if (!kodeStore) {
      tableBody.innerHTML = `<tr>
            <td colspan="8" class="text-center p-8">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-3">
                    <i class="fas fa-store text-gray-400 text-xl"></i>
                </div>
                <p class="text-gray-500 font-medium">Silahkan pilih cabang terlebih dahulu</p>
            </td>
        </tr>`;
      paginationInfo.textContent = "";
      paginationLinks.innerHTML = "";
      resetSummary();
      return;
    }
    setLoading(true);
    const params = new URLSearchParams(formData);
    const urlParams = new URLSearchParams(window.location.search);
    const currentPageFromUrl = urlParams.get('page') || '1';
    params.set('page', currentPageFromUrl);

    try {
      const response = await fetch(
        `/src/api/koreksi/get_koreksi.php?${params.toString()}`
      );
      const data = await response.json();
      if (data.error) throw new Error(data.error);

      // Handle Summary Data
      if (data.summary) {
        elTotalSelisih.textContent = data.summary.total_selisih;
        elTotalRupiahSelisih.textContent = "Rp " + formatRupiah(data.summary.total_selisih_rupiah);
        elTotalMissing.textContent = "Rp " + formatRupiah(data.summary.total_belum_ada);
        elTotalNotFound.textContent = data.summary.total_tidak_ditemukan;

        summaryData.list_selisih = data.summary.list_selisih;
        summaryData.list_belum_ada = data.summary.list_belum_ada;
        summaryData.list_tidak_ditemukan = data.summary.list_tidak_ditemukan;
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

  function resetSummary() {
    elTotalSelisih.textContent = "0";
    elTotalRupiahSelisih.textContent = "Rp 0";
    elTotalMissing.textContent = "Rp 0";
    elTotalNotFound.textContent = "0";
    summaryData = { list_selisih: [], list_belum_ada: [], list_tidak_ditemukan: [] };
  }

  function setLoading(isLoading) {
    if (isLoading) {
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

      // Status Badge Logic
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
                           <i class="fas fa-question-circle mr-1"></i> Tdk Ditemukan
                         </span>`;
      }

      html += `
        <tr class="hover:bg-gray-50 border-b border-gray-100 cursor-pointer transition" onclick="openDetailRow(${index})">
            <td class="text-center text-gray-500 text-sm py-3">${offset + index + 1}</td>
            <td class="text-sm">${statusBadge}</td>
            <td class="text-sm text-gray-700">${formatJustDate(row.tgl_koreksi)}</td>
            <td class="text-sm font-semibold text-gray-600">${storeLabel}</td> 
            <td class="font-medium text-pink-600">${row.kode_supp || '-'}</td>
            <td class="text-sm font-bold text-gray-800 font-mono">${row.no_faktur}</td>
            <td class="text-right font-mono text-gray-700">${formatRupiah(row.total_koreksi)}</td>
            <td class="text-sm text-gray-500 italic truncate max-w-xs">${row.keterangan || "-"}</td>
        </tr>
      `;
    });
    tableBody.innerHTML = html;
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
            <a href="${current_page > 1 ? build_pagination_url(current_page - 1) : "#"}" 
               class="pagination-link ${current_page === 1 ? "pagination-disabled" : ""}">
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
                   class="pagination-link ${page_num === Number(current_page) ? "pagination-active" : ""}">
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

    // Add Event Listeners for new links
    const newLinks = paginationLinks.querySelectorAll('a.pagination-link');
    newLinks.forEach(link => {
      link.addEventListener("click", (e) => {
        if (!link || link.classList.contains("pagination-disabled")) return;
        e.preventDefault();
        const url = link.getAttribute("href");
        window.history.pushState({}, "", url);
        loadData();
      });
    });
  }

  function build_pagination_url(newPage) {
    const params = new URLSearchParams(window.location.search);
    params.set("page", newPage);
    return "?" + params.toString();
  }

  if (filterForm) {
    filterForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const select = document.getElementById("kode_store_filter");
      if (!select.value) {
        alert("Mohon pilih cabang terlebih dahulu!");
        return;
      }
      const formData = new FormData(filterForm);
      const params = new URLSearchParams(formData);
      params.set("page", "1");
      window.history.pushState({}, "", `?${params.toString()}`);
      loadData();
    });
  }

  loadStores().then(() => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('kode_store') && urlParams.get('kode_store') !== "") {
      loadData();
    } else {
      // Optional: Don't auto load if no store selected, just wait for user
    }
  });
});