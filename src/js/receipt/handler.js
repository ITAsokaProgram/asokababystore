document.addEventListener("DOMContentLoaded", () => {
  const filterStore = document.getElementById("kode_store_filter");
  const tableBody = document.getElementById("receipt-table-body");
  const filterForm = document.getElementById("filter-form");
  const paginationInfo = document.getElementById("pagination-info");
  const paginationLinks = document.getElementById("pagination-links");
  const elTotalSelisih = document.getElementById("summary-total-selisih");
  const elTotalRupiahSelisih = document.getElementById(
    "summary-total-rupiah-selisih"
  );
  const elTotalMissing = document.getElementById("summary-total-missing");
  const elTotalNotFound = document.getElementById("summary-total-notfound");
  const btnShowSelisih = document.getElementById("btn-show-selisih");
  const btnShowRupiahSelisih = document.getElementById(
    "btn-show-rupiah-selisih"
  );
  const btnShowMissing = document.getElementById("btn-show-missing");
  const btnShowNotFound = document.getElementById("btn-show-notfound");
  let summaryData = {
    list_selisih: [],
    list_belum_ada: [],
    list_tidak_ditemukan: [],
  };
  let currentTableData = [];
  if (paginationLinks) {
    paginationLinks.addEventListener("click", (e) => {
      const link = e.target.closest("a.pagination-link");
      
      if (!link || link.classList.contains("pagination-disabled")) return;

      e.preventDefault();

      const url = link.getAttribute("href");

      window.history.pushState({}, "", url);

      loadData();
    });
  }
  window.copyToClipboard = function (text, btnElement) {
    if (!text) return;
    navigator.clipboard.writeText(text).then(
      () => {
        const icon = btnElement.querySelector("i");
        const originalClass = icon.className;
        icon.className = "fas fa-check text-green-500";
        setTimeout(() => {
          icon.className = "fas fa-copy";
        }, 1500);
      },
      (err) => {
        console.error("Gagal copy: ", err);
      }
    );
  };
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
            title: "Faktur Belum Ada (Checking)",
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
            title: "Data Tidak Ditemukan",
            list: summaryData.list_tidak_ditemukan,
          },
        })
      );
    });
  }
  async function loadStores() {
    try {
      const token = getCookie("admin_token");
      const response = await fetch("/src/api/cabang/get_kode.php", {
        headers: {
          Accept: "application/json",
          Authorization: "Bearer " + token,
        },
      });
      const result = await response.json();
      const select = filterStore || document.getElementById("cabang");
      if (!select) return;
      select.innerHTML = ""; 
      const urlParams = new URLSearchParams(window.location.search);
      const urlKodeStore = urlParams.get('kode_store');
      if (result.data && result.data.length > 0) {
        const defaultOption = new Option("Pilih Cabang", "");
        defaultOption.disabled = true;
        select.add(defaultOption);
        const allOption = new Option("Semua Cabang", "");
        select.add(allOption);
        result.data.forEach((store) => {
          const option = new Option(
            `${store.nama_cabang} (${store.store})`,
            store.store
          );
          select.add(option);
        });
        if (urlKodeStore !== null) {
            if (urlKodeStore === "") {
                select.selectedIndex = 1; 
            } else {
                select.value = urlKodeStore;
            }
        } else {
            select.selectedIndex = 1; 
        }
      } else {
        select.innerHTML = '<option value="">Gagal memuat data cabang</option>';
      }
      if (urlParams.has('page') || urlParams.has('kode_store')) {
          loadData();
      }
    } catch (error) {
      console.error("Gagal load store:", error);
      const select = filterStore || document.getElementById("cabang");
      if (select) {
        select.innerHTML = '<option value="">Error koneksi</option>';
      }
    }
  }
  async function loadData() {
    setLoading(true);
    const formData = new FormData(filterForm);
    const params = new URLSearchParams(formData);
    const urlParams = new URLSearchParams(window.location.search);
    const currentPageFromUrl = urlParams.get('page') || '1';
    params.set('page', currentPageFromUrl);
    try {
      const response = await fetch(
        `/src/api/receipt/get_receipts.php?${params.toString()}`
      );
      const data = await response.json();
      if (data.error) throw new Error(data.error);
      if (data.summary) {
        elTotalSelisih.textContent = data.summary.total_selisih;
        elTotalMissing.textContent = "Rp " + formatRupiah(data.summary.total_belum_ada);
        if (elTotalRupiahSelisih) {
          elTotalRupiahSelisih.textContent = "Rp " + formatRupiah(data.summary.total_selisih_rupiah);
        }
        if (elTotalNotFound) {
          elTotalNotFound.textContent = data.summary.total_tidak_ditemukan;
        }
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
      let rowClass = "hover:bg-gray-50";
      html += `
         <tr class="${rowClass} border-b border-gray-100 transition cursor-pointer" onclick="openDetailRow(${index})">
             <td class="text-center text-gray-500 text-sm py-3">${
               offset + index + 1
             }</td>
             <td class="text-sm" style="max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                 ${statusBadge}
             </td> 
             <td class="text-sm text-gray-700">${formatJustDate(
               row.tgl_tiba
             )}</td>
             <td class="text-sm font-semibold text-gray-600">${storeLabel}</td>
             <td class="text-sm text-gray-600">
                 <div class="font-medium text-pink-600">${row.kode_supp}</div>
             </td>
             <td class="text-sm font-bold text-gray-800 font-mono">
                 <div class="flex items-center justify-between gap-2 group">
                     <span>${row.no_faktur}</span>
                     <button 
                        type="button"
                        onclick="event.stopPropagation(); copyToClipboard('${
                          row.no_faktur
                        }', this)" 
                        class="p-1 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-all opacity-0 group-hover:opacity-100 focus:opacity-100"
                        title="Copy No Faktur">
                        <i class="fas fa-copy"></i>
                     </button>
                 </div>
             </td>
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
    console.log(current_page)
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
                      page_num === Number(current_page) ? "pagination-active" : ""
                    }">
                     ${page_num}
                 </a>
             `;
      last_page = page_num;
    }
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
});
function build_pagination_url(newPage) {
  const params = new URLSearchParams(window.location.search);
  params.set("page", newPage);
  return "?" + params.toString();
}
