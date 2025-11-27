document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("receipt-table-body");
  const filterForm = document.getElementById("filter-form");
  const filterSubmitButton = document.getElementById("filter-submit-button");
  const filterInputSupplier = document.getElementById("search_supplier");
  const pageTitle = document.getElementById("page-title");
  const pageSubtitle = document.getElementById("page-subtitle");
  const paginationContainer = document.getElementById("pagination-container");
  const paginationInfo = document.getElementById("pagination-info");
  const paginationLinks = document.getElementById("pagination-links");

  // Helper Format Rupiah
  function formatRupiah(number) {
    if (isNaN(number) || number === null) return "0";
    return new Intl.NumberFormat("id-ID", {
      style: "decimal",
      currency: "IDR",
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(number);
  }

  // Helper Get URL Params
  function getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    const yesterdayString = yesterday.toISOString().split("T")[0];
    return {
      tgl_mulai: params.get("tgl_mulai") || yesterdayString,
      tgl_selesai: params.get("tgl_selesai") || yesterdayString,
      search_supplier: params.get("search_supplier") || "",
      page: parseInt(params.get("page") || "1", 10),
    };
  }

  function build_pagination_url(newPage) {
    const params = new URLSearchParams(window.location.search);
    params.set("page", newPage);
    return "?" + params.toString();
  }

  // --- FUNGSI BARU: Handle Konfirmasi ---
  window.handleConfirmCoretax = async function (id, candidateNsfp) {
    const result = await Swal.fire({
      title: "Konfirmasi Data?",
      html: `Data pembelian ini cocok dengan data Core Tax.<br>
             NSFP Core Tax: <b>${candidateNsfp}</b><br><br>
             Apakah Anda yakin ingin menghubungkan data ini?`,
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Ya, Konfirmasi",
      cancelButtonText: "Batal",
      confirmButtonColor: "#d63384",
    });

    if (result.isConfirmed) {
      try {
        Swal.fire({
          title: "Menyimpan...",
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading(),
        });

        const response = await fetch(
          "/src/api/coretax/konfirmasi_pembelian.php",
          {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: id, nsfp: candidateNsfp }),
          }
        );

        const resData = await response.json();

        if (!response.ok) throw new Error(resData.error || "Gagal konfirmasi");

        await Swal.fire("Berhasil!", "Data telah terkonfirmasi.", "success");
        loadData(); // Reload table
      } catch (error) {
        Swal.fire("Error", error.message, "error");
      }
    }
  };

  async function loadData() {
    const params = getUrlParams();
    const isPagination = params.page > 1;
    setLoadingState(true, isPagination);

    const queryString = new URLSearchParams({
      tgl_mulai: params.tgl_mulai,
      tgl_selesai: params.tgl_selesai,
      search_supplier: params.search_supplier,
      page: params.page,
    }).toString();

    try {
      const response = await fetch(
        `/src/api/coretax/get_laporan_pembelian.php?${queryString}`
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
      if (pageSubtitle)
        pageSubtitle.textContent = `Periode ${params.tgl_mulai} s/d ${params.tgl_selesai}`;

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
      if (filterSubmitButton)
        filterSubmitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span>Memuat...</span>`;
      if (tableBody)
        tableBody.innerHTML = `<tr><td colspan="9" class="text-center p-8"><div class="spinner-simple"></div><p class="mt-2 text-gray-500">Memuat data...</p></td></tr>`;
      if (paginationInfo) paginationInfo.textContent = "";
      if (paginationLinks) paginationLinks.innerHTML = "";
    } else {
      if (filterSubmitButton) {
        filterSubmitButton.disabled = false;
        filterSubmitButton.innerHTML = `<i class="fas fa-filter"></i><span>Tampilkan</span>`;
      }
    }
  }

  function showTableError(message) {
    tableBody.innerHTML = `
        <tr>
            <td colspan="9" class="text-center p-8 text-red-600">
                <i class="fas fa-exclamation-triangle fa-lg mb-2"></i>
                <p>Gagal memuat data: ${message}</p>
            </td>
        </tr>`;
  }

  function renderTable(tabel_data, offset) {
    // Sesuaikan colspan jadi 10 karena kolom nambah satu
    if (!tabel_data || tabel_data.length === 0) {
      tableBody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center p-8 text-gray-500">
                    <i class="fas fa-inbox fa-lg mb-2"></i>
                    <p>Tidak ada data ditemukan untuk filter ini.</p>
                </td>
            </tr>`;
      return;
    }

    let htmlRows = "";
    let item_counter = offset + 1;

    tabel_data.forEach((row) => {
      const dpp = parseFloat(row.dpp) || 0;
      const ppn = parseFloat(row.ppn) || 0;
      const total = parseFloat(row.total_terima_fp) || 0;

      const dateObj = new Date(row.tgl_nota);
      const dateFormatted = dateObj.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
      });

      // --- LOGIKA STATUS & NSFP ---
      let statusHtml = "";
      let nsfpHtml = "";

      if (row.ada_di_coretax == 1) {
        // KONDISI 1: SUDAH DIKONFIRMASI
        // Status: Badge Hijau
        statusHtml = `
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <i class="fas fa-check-circle mr-1"></i> Terkonfirmasi
            </span>
          `;
        // NSFP: Tampilkan nomor yang tersimpan di database pembelian (Tebal Hitam)
        nsfpHtml = `<span class="font-mono text-sm font-semibold text-gray-800">${
          row.nsfp || "-"
        }</span>`;
      } else if (row.candidate_nsfp) {
        // KONDISI 2: BELUM KONFIRMASI, TAPI ADA MATCH DI CORETAX
        // Status: Tombol Konfirmasi
        statusHtml = `
            <button onclick="handleConfirmCoretax(${row.id}, '${row.candidate_nsfp}')" 
                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 transition-colors"
                title="Klik untuk menghubungkan">
                <i class="fas fa-link mr-1"></i> Konfirmasi
            </button>
          `;
        // NSFP: Tampilkan kandidat nomor dari Coretax (Warna Abu/Italic untuk indikasi belum save)
        nsfpHtml = `
            <div class="flex flex-col items-center">
                <span class="font-mono text-sm text-gray-500 italic border-b border-dashed border-gray-300 cursor-help" title="Data ditemukan di Core Tax">
                    ${row.candidate_nsfp}
                </span>
            </div>
          `;
      } else {
        // KONDISI 3: TIDAK ADA DATA DI CORETAX
        statusHtml = `<span class="text-gray-300">-</span>`;
        nsfpHtml = `<span class="text-gray-300">-</span>`;
      }

      htmlRows += `
            <tr class="hover:bg-gray-50">
                <td class="text-center font-medium text-gray-500">${item_counter}</td>
                <td>${dateFormatted}</td>
                <td class="font-semibold text-gray-700">${row.no_faktur}</td>
                <td class="text-sm text-gray-600">${
                  row.kode_supplier || "-"
                }</td>
                <td class="text-sm font-medium text-gray-800">${
                  row.nama_supplier || "-"
                }</td>
                <td class="text-right font-mono text-gray-700">${formatRupiah(
                  dpp
                )}</td>
                <td class="text-right font-mono text-red-600">${formatRupiah(
                  ppn
                )}</td>
                <td class="text-right font-bold text-gray-800">${formatRupiah(
                  total
                )}</td>
                
                <td class="text-center align-middle whitespace-nowrap">
                    ${statusHtml}
                </td>
                
                <td class="text-center align-middle whitespace-nowrap">
                    ${nsfpHtml}
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

  loadData();
});
