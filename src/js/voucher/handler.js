document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("voucher-table-body");
  const filterForm = document.getElementById("filter-form");
  const filterSubmitButton = document.getElementById("filter-submit-button");
  const filterSelectStore = document.getElementById("kd_store");
  const pageTitle = document.getElementById("page-title");
  const pageSubtitle = document.getElementById("page-subtitle");
  const paginationContainer = document.getElementById("pagination-container");
  const paginationInfo = document.getElementById("pagination-info");
  const paginationLinks = document.getElementById("pagination-links");

  function formatRupiah(number) {
    if (isNaN(number) || number === null) return "0";
    return new Intl.NumberFormat("id-ID", {
      style: "decimal",
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(number);
  }

  function formatDateTime(dateString) {
    if (!dateString) return "-";
    return dateString.substring(0, 19);
  }

  function formatJustDate(dateString) {
    if (!dateString) return "-";
    return dateString.substring(0, 10);
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

  function getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    const today = new Date();
    const todayString = today.toLocaleDateString("en-CA");
    const lastMonth = new Date();
    lastMonth.setMonth(lastMonth.getMonth() - 1);
    const lastMonthString = lastMonth.toLocaleDateString("en-CA");

    return {
      tgl_mulai: params.get("tgl_mulai") || lastMonthString,
      tgl_selesai: params.get("tgl_selesai") || todayString,
      kd_store: params.get("kd_store") || "all",
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
    const token = getCookie("admin_token");

    setLoadingState(true, isPagination);

    const queryString = new URLSearchParams({
      tgl_mulai: params.tgl_mulai,
      tgl_selesai: params.tgl_selesai,
      kd_store: params.kd_store,
      page: params.page,
    }).toString();

    try {
      const response = await fetch(
        `/src/api/voucher/get_vouchers.php?${queryString}`,
        {
          headers: {
            Accept: "application/json",
            Authorization: "Bearer " + token,
          },
        }
      );

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(
          errorData.error || `HTTP error! status: ${response.status}`
        );
      }

      const data = await response.json();
      if (data.error) throw new Error(data.error);

      // ISI FILTER CABANG DARI DATA API
      if (data.stores) {
        populateStoreFilter(data.stores, params.kd_store);
      }

      if (pageSubtitle) {
        let storeName = "Seluruh Cabang";
        if (filterSelectStore && filterSelectStore.selectedIndex > -1) {
          storeName =
            filterSelectStore.options[filterSelectStore.selectedIndex].text;
        }
        pageSubtitle.textContent = `Periode Tgl Awal ${params.tgl_mulai} s/d ${params.tgl_selesai} - ${storeName}`;
      }

      renderTable(
        data.tabel_data,
        data.pagination ? data.pagination.offset : 0,
        data.date_subtotals,
        data.pagination
      );
      renderPagination(data.pagination);
    } catch (error) {
      console.error("Error loading data:", error);
      showTableError(error.message);
      if (pageSubtitle) pageSubtitle.textContent = "Gagal memuat data";
    } finally {
      setLoadingState(false);
    }
  }

  function setLoadingState(isLoading, isPagination = false) {
    if (isLoading) {
      if (filterSubmitButton) {
        filterSubmitButton.disabled = true;
        filterSubmitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span> Memuat...</span>`;
      }
      if (!isPagination && tableBody) {
        tableBody.innerHTML = `
          <tr>
            <td colspan="11" class="text-center p-8">
              <div class="spinner-simple"></div>
              <p class="mt-2 text-gray-500">Memuat data...</p>
            </td>
          </tr>`;
      }
    } else {
      if (filterSubmitButton) {
        filterSubmitButton.disabled = false;
        filterSubmitButton.innerHTML = `<i class="fas fa-filter"></i><span> Tampilkan</span>`;
      }
    }
  }

  function showTableError(message) {
    tableBody.innerHTML = `
      <tr>
        <td colspan="11" class="text-center p-8 text-red-600">
          <i class="fas fa-exclamation-triangle fa-lg mb-2"></i>
          <p>Gagal memuat data: ${message}</p>
        </td>
      </tr>`;
  }

  function populateStoreFilter(stores, selectedStore) {
    if (!filterSelectStore) return;

    // Simpan nilai lama sebelum dikosongkan
    const currentVal = selectedStore || filterSelectStore.value;

    filterSelectStore.innerHTML = "";

    // Tambahkan opsi default
    const defaultOption = new Option("Seluruh Store", "all");
    filterSelectStore.add(defaultOption);

    const allOption = new Option("SEMUA CABANG", "SEMUA CABANG");
    filterSelectStore.add(allOption);

    if (stores && stores.length > 0) {
      stores.forEach((store) => {
        const option = new Option(
          `${store.kd_store} - ${store.nm_alias}`,
          store.kd_store
        );
        filterSelectStore.add(option);
      });
    }

    // Set kembali nilai yang terpilih
    filterSelectStore.value = currentVal;

    // Jika value yang di set tidak ada di list (misal 'all' tapi list hanya cabang tertentu),
    // maka fallback ke option pertama yang tersedia
    if (filterSelectStore.selectedIndex === -1) {
      filterSelectStore.selectedIndex = 0;
    }
  }

  function renderTable(tabel_data, offset, date_subtotals, pagination) {
    if (!tabel_data || tabel_data.length === 0) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="11" class="text-center p-8 text-gray-500">
            <i class="fas fa-inbox fa-lg mb-2"></i>
            <p>Tidak ada voucher ditemukan.</p>
          </td>
        </tr>`;
      return;
    }

    let htmlRows = "";
    let current_tanggal = null;
    let item_counter = offset + 1;

    tabel_data.forEach((row) => {
      const rowDateOnly = row.tgl_awal ? row.tgl_awal.split(" ")[0] : "unknown";
      if (rowDateOnly !== current_tanggal) {
        current_tanggal = rowDateOnly;
        htmlRows += `
          <tr class="header-tanggal-row bg-gray-100">
            <td colspan="11" class="px-4 py-2 font-bold text-pink-600">
              Tgl Awal: ${formatJustDate(row.tgl_awal)}
            </td>
          </tr>`;
      }

      const isExpired = new Date(row.tgl_akhir) < new Date();
      const isHabis = parseFloat(row.sisa) <= 0;
      let statusBadge = isHabis
        ? `<span class="px-2 py-1 bg-gray-200 text-gray-600 rounded text-xs">Habis</span>`
        : isExpired
        ? `<span class="px-2 py-1 bg-red-100 text-red-600 rounded text-xs">Expired</span>`
        : `<span class="px-2 py-1 bg-green-100 text-green-600 rounded text-xs">Aktif</span>`;

      htmlRows += `
        <tr class="hover:bg-gray-50 border-b">
          <td class="text-center text-gray-500 text-sm">${item_counter}</td>
          <td class="font-medium text-gray-900">${row.kd_voucher}</td>
          <td>
            <div class="text-sm font-semibold">${row.pemilik || "-"}</div>
            <div class="text-xs text-gray-500">${row.kd_cust || ""}</div>
          </td>
          <td class="text-center">${row.kd_store}</td>
          <td>${formatRupiah(row.nilai)}</td>
          <td>${formatRupiah(row.pakai)}</td>
          <td class="font-bold text-gray-700">${formatRupiah(row.sisa)}</td>
          <td class="text-sm">${formatDateTime(row.tgl_awal)}</td>
          <td class="text-sm">${formatDateTime(row.tgl_akhir)}</td>
          <td class="text-sm">${formatJustDate(row.tgl_buat)}</td>
          <td class="text-center">${statusBadge}</td>
        </tr>`;
      item_counter++;
    });
    tableBody.innerHTML = htmlRows;
  }

  function renderPagination(pagination) {
    if (!pagination || pagination.total_rows === 0) {
      paginationInfo.textContent = "";
      paginationLinks.innerHTML = "";
      return;
    }

    const { current_page, total_pages, total_rows, limit, offset } = pagination;
    const start_row = offset + 1;
    const end_row = Math.min(offset + limit, total_rows);
    paginationInfo.textContent = `Menampilkan ${start_row} - ${end_row} dari ${total_rows} data`;

    let linksHtml = `
      <a href="${
        current_page > 1 ? build_pagination_url(current_page - 1) : "#"
      }" 
         class="pagination-link ${
           current_page === 1 ? "opacity-50 pointer-events-none" : ""
         }">
        <i class="fas fa-chevron-left"></i>
      </a>`;

    for (let i = 1; i <= total_pages; i++) {
      if (
        i === 1 ||
        i === total_pages ||
        (i >= current_page - 2 && i <= current_page + 2)
      ) {
        linksHtml += `
              <a href="${build_pagination_url(i)}" 
                 class="pagination-link ${
                   i === current_page ? "bg-pink-600 text-white" : ""
                 }">
                ${i}
              </a>`;
      } else if (i === current_page - 3 || i === current_page + 3) {
        linksHtml += `<span class="px-2">...</span>`;
      }
    }

    linksHtml += `
      <a href="${
        current_page < total_pages
          ? build_pagination_url(current_page + 1)
          : "#"
      }" 
         class="pagination-link ${
           current_page === total_pages ? "opacity-50 pointer-events-none" : ""
         }">
        <i class="fas fa-chevron-right"></i>
      </a>`;

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

  // Jalankan load data pertama kali
  loadData();
});
