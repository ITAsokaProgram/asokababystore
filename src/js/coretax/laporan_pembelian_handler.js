document.addEventListener("DOMContentLoaded", () => {
  const tableBody = document.getElementById("receipt-table-body");
  const filterForm = document.getElementById("filter-form");
  const filterSubmitButton = document.getElementById("filter-submit-button");
  const filterSelectStore = document.getElementById("kd_store");
  const filterSelectStatus = document.getElementById("status_data");
  const filterSelectTipePembelian = document.getElementById(
    "filter_tipe_pembelian"
  );
  const filterInputSupplier = document.getElementById("search_supplier");
  const pageTitle = document.getElementById("page-title");
  const pageSubtitle = document.getElementById("page-subtitle");
  const paginationContainer = document.getElementById("pagination-container");
  const paginationInfo = document.getElementById("pagination-info");
  const paginationLinks = document.getElementById("pagination-links");
  const filterTypeSelect = document.getElementById("filter_type");
  const containerMonth = document.getElementById("container-month");
  const containerDateRange = document.getElementById("container-date-range");
  const filterBulan = document.getElementById("bulan");
  const filterTahun = document.getElementById("tahun");
  const filterTglMulai = document.getElementById("tgl_mulai");
  const filterTglSelesai = document.getElementById("tgl_selesai");
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
    const now = new Date();
    const currentMonth = String(now.getMonth() + 1).padStart(2, "0");
    const currentYear = now.getFullYear();
    return {
      filter_type: params.get("filter_type") || "month",
      bulan: params.get("bulan") || currentMonth,
      tahun: params.get("tahun") || currentYear,
      tgl_mulai: params.get("tgl_mulai") || yesterdayString,
      tgl_selesai: params.get("tgl_selesai") || yesterdayString,
      kd_store: params.get("kd_store") || "all",
      status_data: params.get("status_data") || "all",
      filter_tipe_pembelian: params.get("filter_tipe_pembelian") || "semua",
      search_supplier: params.get("search_supplier") || "",
      page: parseInt(params.get("page") || "1", 10),
    };
  }
  function build_pagination_url(newPage) {
    const params = new URLSearchParams(window.location.search);
    params.set("page", newPage);
    return "?" + params.toString();
  }
  function populateStoreFilter(stores, selectedStore) {
    if (!filterSelectStore || filterSelectStore.options.length > 1) {
      filterSelectStore.value = selectedStore;
      return;
    }
    stores.forEach((store) => {
      const option = document.createElement("option");
      option.value = store.kd_store;
      option.textContent = `${store.kd_store} - ${store.nm_alias}`;
      if (store.kd_store === selectedStore) {
        option.selected = true;
      }
      filterSelectStore.appendChild(option);
    });
    filterSelectStore.value = selectedStore;
  }
  async function loadData() {
    const params = getUrlParams();
    const isPagination = params.page > 1;
    setLoadingState(true, isPagination);
    const queryString = new URLSearchParams({
      filter_type: params.filter_type,
      bulan: params.bulan,
      tahun: params.tahun,
      tgl_mulai: params.tgl_mulai,
      tgl_selesai: params.tgl_selesai,
      kd_store: params.kd_store,
      status_data: params.status_data,
      filter_tipe_pembelian: params.filter_tipe_pembelian,
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
      if (data.stores) {
        populateStoreFilter(data.stores, params.kd_store);
      }
      if (filterInputSupplier)
        filterInputSupplier.value = params.search_supplier;
      if (filterSelectStatus) filterSelectStatus.value = params.status_data;
      if (filterSelectTipePembelian)
        filterSelectTipePembelian.value = params.filter_tipe_pembelian;
      if (filterTypeSelect) {
        filterTypeSelect.value = params.filter_type;
        toggleFilterMode();
      }
      if (filterBulan) filterBulan.value = params.bulan;
      if (filterTahun) filterTahun.value = params.tahun;
      if (filterTglMulai) filterTglMulai.value = params.tgl_mulai;
      if (filterTglSelesai) filterTglSelesai.value = params.tgl_selesai;
      if (pageSubtitle) {
        let storeName = "";
        if (
          filterSelectStore.options.length > 0 &&
          filterSelectStore.selectedIndex > -1 &&
          filterSelectStore.value !== "all"
        ) {
          storeName =
            " - " +
            filterSelectStore.options[filterSelectStore.selectedIndex].text;
        }
        let periodText = "";
        if (params.filter_type === "month") {
          const monthNames = [
            "Januari",
            "Februari",
            "Maret",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Agustus",
            "September",
            "Oktober",
            "November",
            "Desember",
          ];
          const monthIndex = parseInt(params.bulan) - 1;
          const monthName = monthNames[monthIndex] || params.bulan;
          periodText = `Periode Bulan ${monthName} ${params.tahun}`;
        } else {
          periodText = `Periode ${params.tgl_mulai} s/d ${params.tgl_selesai}`;
        }
        pageSubtitle.textContent = `${periodText}${storeName}`;
      }
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
        tableBody.innerHTML = `<tr><td colspan="12" class="text-center p-8"><div class="spinner-simple"></div><p class="mt-2 text-gray-500">Memuat data...</p></td></tr>`;
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
    tableBody.innerHTML = `<tr><td colspan="12" class="text-center p-8 text-red-600"><p>Gagal: ${message}</p></td></tr>`;
  }
  function renderTable(tabel_data, offset) {
    if (!tabel_data || tabel_data.length === 0) {
      tableBody.innerHTML = `
              <tr>
                  <td colspan="12" class="text-center p-8 text-gray-500">
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
      const isBtkp = row.is_btkp == 1;
      const btkpBadge = isBtkp
        ? `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800 border border-green-200">OK</span>`
        : `<span class="text-gray-300 text-xs">-</span>`;
      let mergedCandidatesMap = new Map();
      if (row.candidate_nsfps) {
        const candidatesRaw = row.candidate_nsfps.split(",");
        candidatesRaw.forEach((raw) => {
          const parts = raw.split("|");
          if (parts.length >= 2) {
            const nsfp = parts[0];
            const status = parts[1];
            const usedBy = parts[2];
            const source = parts[3] || "CORETAX";
            const matchType = parts[4] || "VALUE";
            const supplierName = parts[5] || "";
            if (!mergedCandidatesMap.has(nsfp)) {
              mergedCandidatesMap.set(nsfp, {
                nsfp: nsfp,
                status: status,
                sources: [source],
                matchType: matchType,
                usedBy: usedBy,
                supplierName: supplierName,
              });
            } else {
              const existing = mergedCandidatesMap.get(nsfp);
              if (!existing.sources.includes(source)) {
                existing.sources.push(source);
              }
              if (matchType === "INVOICE") {
                existing.matchType = "INVOICE";
              }
              if (status === "AVAILABLE") {
                existing.status = "AVAILABLE";
              }
              if (!existing.supplierName && supplierName) {
                existing.supplierName = supplierName;
              }
            }
          }
        });
      }
      let parsedCandidates = Array.from(mergedCandidatesMap.values());
      let availableCandidates = parsedCandidates.filter(
        (c) => c.status === "AVAILABLE"
      );
      let usedCandidates = parsedCandidates.filter(
        (c) => c.status !== "AVAILABLE"
      );
      availableCandidates.sort((a, b) => {
        if (a.matchType === "INVOICE" && b.matchType !== "INVOICE") return -1;
        if (a.matchType !== "INVOICE" && b.matchType === "INVOICE") return 1;
        return 0;
      });
      let htmlFisik = '<span class="text-gray-300 text-xs">-</span>';
      let htmlCoretax = '<span class="text-gray-300 text-xs">-</span>';
      if (row.ada_di_coretax == 1) {
        const badgeConfirmed = `
                  <div class="flex flex-col items-center justify-center gap-1">
                      <span class="font-mono text-xs font-semibold text-gray-800">${
                        row.nsfp || "-"
                      }</span>
                  </div>`;
        const tipe = row.tipe_nsfp ? row.tipe_nsfp.toLowerCase() : "";
        if (tipe === "all" || tipe.includes("fisik")) {
          htmlFisik = badgeConfirmed;
        }
        if (tipe === "all" || tipe.includes("coretax")) {
          htmlCoretax = badgeConfirmed;
        }
      } else if (availableCandidates.length > 0) {
        const bestCandidate = availableCandidates[0];
        const isDualMatch =
          bestCandidate.sources.includes("FISIK") &&
          bestCandidate.sources.includes("CORETAX");
        const candidateString = availableCandidates
          .map((c) => `${c.nsfp}###${c.supplierName}`)
          .join(",");
        const count = availableCandidates.length;
        const textClass =
          "text-gray-500 font-medium group-hover:text-gray-800 border-gray-300 group-hover:border-gray-500";
        let multiIndicator = "";
        if (count > 1) {
          multiIndicator = `<span class="text-[10px] text-gray-400 mt-0.5 block italic group-hover:text-gray-600">
                                     (Pilih dr ${count} opsi)
                                   </span>`;
        }
        const actionHtml = `
                  <div class="flex flex-col items-center justify-center cursor-pointer group py-1 select-none"
                      onclick="handleConfirmCoretax(${row.id}, '${candidateString}', ${isDualMatch})"
                      title="Klik untuk memilih NSFP ini">
                     <span class="font-mono text-xs ${textClass} border-b border-dashed group-hover:border-solid transition-colors duration-200">
                         ${bestCandidate.nsfp}
                     </span>
                     ${multiIndicator}
                  </div>`;
        if (isDualMatch) {
          htmlFisik = actionHtml;
          htmlCoretax = actionHtml;
        } else {
          if (bestCandidate.sources.includes("FISIK")) {
            htmlFisik = actionHtml;
          }
          if (bestCandidate.sources.includes("CORETAX")) {
            htmlCoretax = actionHtml;
          }
        }
      } else if (usedCandidates.length > 0) {
        const firstMatch = usedCandidates[0];
        const errorHtml = `
                  <div class="flex flex-col items-center">
                      <span class="text-[10px] font-bold text-red-500">Ganda/Terpakai</span>
                      <span class="text-[10px] text-gray-400 font-mono decoration-line-through">${firstMatch.nsfp}</span>
                  </div>`;
        if (firstMatch.sources.includes("FISIK")) htmlFisik = errorHtml;
        if (firstMatch.sources.includes("CORETAX")) htmlCoretax = errorHtml;
        if (
          firstMatch.sources.includes("FISIK") &&
          firstMatch.sources.includes("CORETAX")
        ) {
          htmlFisik = errorHtml;
          htmlCoretax = errorHtml;
        }
      }
      htmlRows += `
                  <tr class="hover:bg-gray-50">
                      <td class="text-center font-medium text-gray-500">${item_counter}</td>
                      <td>${dateFormatted}</td>
                      <td class="font-semibold text-gray-700">${
                        row.no_faktur
                      }</td>
                      <td class="text-center">${btkpBadge}</td> 
                      <td class="">
                          <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-800">
                              ${row.Nm_Alias || "-"}
                          </span>
                      </td>
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
                      <td class="text-center align-middle border-l border-gray-100 bg-blue-50/30">
                          ${htmlFisik}
                      </td>
                      <td class="text-center align-middle border-l border-gray-100 bg-yellow-50/30">
                          ${htmlCoretax}
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
