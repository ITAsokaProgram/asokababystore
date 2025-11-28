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
  function getCookie(name) {
    const value = document.cookie.match(
      "(^|;)\\s*" + name + "\\s*=\\s*([^;]+)"
    );
    if (value) return value[2];
    return null;
  }
  function getToken() {
    return getCookie("admin_token");
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
  window.handleConfirmCoretax = async function (id, candidateString) {
    const candidates = candidateString.split(",");
    let selectedNsfp = candidates[0];
    if (candidates.length > 1) {
      const inputOptions = {};
      candidates.forEach((nsfp) => {
        inputOptions[nsfp] = nsfp;
      });
      const { value: userSelection } = await Swal.fire({
        title: "Pilih NSFP",
        text: "Terdapat beberapa data Coretax yang cocok. Silakan pilih NSFP yang benar:",
        input: "select",
        inputOptions: inputOptions,
        inputValue: candidates[0],
        inputPlaceholder: "Pilih NSFP...",
        width: "600px",
        showCancelButton: true,
        confirmButtonText: "Pilih & Konfirmasi",
        cancelButtonText: "Batal",
        confirmButtonColor: "#d63384",
        inputValidator: (value) => {
          if (!value) {
            return "Anda harus memilih salah satu NSFP!";
          }
        },
      });
      if (userSelection) {
        selectedNsfp = userSelection;
      } else {
        return;
      }
    } else {
      const result = await Swal.fire({
        title: "Konfirmasi Data?",
        html: `Data pembelian ini cocok dengan data Coretax.<br>
               NSFP: <b class="text-lg">${selectedNsfp}</b><br><br>
               Hubungkan data ini?`,
        icon: "question",
        width: "500px",
        showCancelButton: true,
        confirmButtonText: "Ya, Konfirmasi",
        cancelButtonText: "Batal",
        confirmButtonColor: "#d63384",
      });
      if (!result.isConfirmed) return;
    }
    try {
      const token = getToken();
      if (!token) {
        throw new Error("Sesi habis. Silakan login kembali.");
      }
      Swal.fire({
        title: "Menyimpan...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
      });
      const response = await fetch(
        "/src/api/coretax/konfirmasi_pembelian.php",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
          },
          body: JSON.stringify({ id: id, nsfp: selectedNsfp }),
        }
      );
      const resData = await response.json();
      if (response.status === 401) {
        throw new Error(
          "Sesi tidak valid atau kadaluarsa. Silakan login ulang."
        );
      }
      if (!response.ok) throw new Error(resData.error || "Gagal konfirmasi");
      await Swal.fire("Berhasil!", "Data telah terkonfirmasi.", "success");
      loadData();
    } catch (error) {
      Swal.fire("Error", error.message, "error");
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
      const dppNilaiLain = parseFloat(row.dpp_nilai_lain) || 0;
      const dateFormatted = dateObj.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
      });
      let availableCandidates = [];
      let usedCandidates = [];
      if (row.candidate_nsfps) {
        const candidatesRaw = row.candidate_nsfps.split(",");
        candidatesRaw.forEach((raw) => {
          const parts = raw.split("|");
          if (parts.length >= 2) {
            const nsfpCode = parts[0];
            const status = parts[1];
            const usedBy = parts[2];
            if (status === "AVAILABLE") {
              availableCandidates.push(nsfpCode);
            } else {
              usedCandidates.push({ nsfp: nsfpCode, usedBy: usedBy });
            }
          } else {
            availableCandidates.push(parts[0]);
          }
        });
      }
      let statusHtml = "";
      let nsfpHtml = "";
      if (row.ada_di_coretax == 1) {
        statusHtml = `
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <i class="fas fa-check-circle mr-1"></i> Terkonfirmasi
            </span>`;
        nsfpHtml = `<span class="font-mono text-sm font-semibold text-gray-800">${
          row.nsfp || "-"
        }</span>`;
      } else if (availableCandidates.length > 0) {
        const candidateString = availableCandidates.join(",");
        const count = availableCandidates.length;
        if (count > 1) {
          statusHtml = `
            <button onclick="handleConfirmCoretax(${row.id}, '${candidateString}')" 
                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none transition-colors"
                title="Terdapat ${count} NSFP yang cocok">
                <i class="fas fa-list-ul mr-1"></i> Pilih NSFP (${count})
            </button>`;
          nsfpHtml = `
            <div class="flex flex-col items-center">
                <span class="text-xs text-purple-600 font-semibold italic">
                   ${count} Opsi Ditemukan
                </span>
            </div>`;
        } else {
          statusHtml = `
            <button onclick="handleConfirmCoretax(${row.id}, '${candidateString}')" 
                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-pink-600 hover:bg-pink-700 focus:outline-none transition-colors"
                title="Klik untuk menghubungkan">
                <i class="fas fa-link mr-1"></i> Konfirmasi
            </button>`;
          nsfpHtml = `
            <div class="flex flex-col items-center">
                <span class="font-mono text-sm text-gray-500 italic border-b border-dashed border-gray-300 cursor-help" title="Data ditemukan di Coretax">
                    ${availableCandidates[0]}
                </span>
            </div>`;
        }
      } else if (usedCandidates.length > 0) {
        const firstMatch = usedCandidates[0];
        statusHtml = `
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 cursor-help" 
                  title="Nominal DPP & PPN cocok dengan NSFP: ${firstMatch.nsfp}, namun NSFP tersebut sudah dipakai oleh invoice: ${firstMatch.usedBy}">
                <i class="fas fa-exclamation-circle mr-1"></i> Nominal Kembar
            </span>`;
        nsfpHtml = `
            <div class="flex flex-col items-center">
                <span class="text-xs text-orange-600 italic">
                    (Sudah Terpakai)
                </span>
                <span class="text-[10px] text-gray-400">
                    ${firstMatch.nsfp}
                </span>
            </div>`;
      } else {
        statusHtml = `<span class="text-gray-300">-</span>`;
        nsfpHtml = `<span class="text-gray-300">-</span>`;
      }
      htmlRows += `
            <tr class="hover:bg-gray-50">
                <td class="text-center font-medium text-gray-500">${item_counter}</td>
                <td>${dateFormatted}</td>
                <td class="font-semibold text-gray-700">${row.no_faktur}</td>
                <td class="text-sm font-medium text-gray-800">${
                  row.nama_supplier || "-"
                }</td>
                <td class="text-right font-mono text-gray-700">${formatRupiah(
                  dpp
                )}</td>
                <td class="text-right font-mono text-gray-700">${formatRupiah(
                  dppNilaiLain
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
