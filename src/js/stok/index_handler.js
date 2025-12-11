document.addEventListener("DOMContentLoaded", () => {
  const branchContainer = document.getElementById("branch-container");
  const branchSearchInput = document.getElementById("branch-search");
  const btnSelectAll = document.getElementById("btn-select-all");
  const btnDeselectAll = document.getElementById("btn-deselect-all");
  const selectedCountLabel = document.getElementById("selected-count");
  const selectSupp = document.getElementById("kode_supp");
  const btnSubmit = document.getElementById("filter-submit-button");
  const btnReset = document.getElementById("reset-button");
  const form = document.getElementById("filter-form");
  const suppLoadingText = document.getElementById("supp-loading-text");
  const resultContainer = document.getElementById("result-container");
  const tableHeadersRow = document.getElementById("table-headers");
  const tableBody = document.getElementById("table-body");
  const tableScrollContainer = document.querySelector("#table-scroll-area");
  const totalBadge = document.getElementById("total-records-badge");
  let debounceTimer;
  let allBranchesData = [];
  let currentPage = 1;
  let isLoadingMore = false;
  let hasMoreData = true;
  let currentHeaders = [];
  init();
  async function init() {
    showEmptyState("Silahkan pilih cabang dan supplier terlebih dahulu.");
    await loadBranches();
    checkUrlAndLoad();
  }
  function showEmptyState(message) {
    resultContainer.classList.remove("hidden");
    tableHeadersRow.innerHTML = `
      <th class="px-2 py-2 border-b-2 border-pink-200 text-center w-10 font-bold text-pink-700">#</th>
      <th class="px-2 py-2 border-b-2 border-pink-200 font-bold min-w-[80px]">PLU</th>
      <th class="px-2 py-2 border-b-2 border-pink-200 font-bold min-w-[100px]">Barcode</th>
      <th class="px-2 py-2 border-b-2 border-pink-200 font-bold min-w-[200px]">Nama Barang</th>
      <th class="px-2 py-2 border-b-2 border-pink-200 font-bold text-center">Total</th>
    `;
    tableBody.innerHTML = `
      <tr>
        <td colspan="100%" class="text-center align-middle p-0" style="height: 400px;">
          <div class="flex flex-col items-center justify-center h-full text-gray-400 opacity-60">
            <i class="fas fa-search text-5xl mb-4 text-pink-200"></i>
            <p class="text-sm font-medium">${message}</p>
          </div>
        </td>
      </tr>
    `;
  }
  async function loadBranches() {
    try {
      const response = await fetch("/src/api/option/get_cabang.php");
      const result = await response.json();
      if (result.success && result.data) {
        allBranchesData = result.data;
        renderBranches(allBranchesData);
      } else {
        branchContainer.innerHTML = `
          <div class="col-span-full text-center p-8">
            <i class="fas fa-exclamation-triangle text-red-400 text-2xl mb-2"></i>
            <p class="text-red-500 text-sm font-medium">Gagal memuat cabang</p>
          </div>`;
      }
    } catch (error) {
      console.error(error);
      branchContainer.innerHTML = `
        <div class="col-span-full text-center p-8">
          <i class="fas fa-wifi-slash text-red-400 text-2xl mb-2"></i>
          <p class="text-red-500 text-sm font-medium">Error koneksi</p>
        </div>`;
    }
  }
  function renderBranches(data) {
    branchContainer.innerHTML = "";
    if (data.length === 0) {
      branchContainer.innerHTML = `
        <div class="col-span-full text-center py-4">
          <i class="fas fa-search text-gray-300 text-lg mb-1"></i>
          <p class="text-gray-400 text-xs">Tidak ada cabang</p>
        </div>`;
      return;
    }
    data.forEach((store) => {
      const div = document.createElement("div");
      div.className = "relative";
      div.innerHTML = `
        <label class="cursor-pointer group block h-full">
          <input type="checkbox" name="kd_store[]" value="${store.Kd_Store}" 
            class="branch-checkbox peer sr-only">
          <div class="branch-card border border-gray-200 rounded-lg p-2 h-full flex items-center gap-2 bg-white hover:bg-pink-50">
            <div class="branch-icon w-6 h-6 bg-gray-100 rounded-md flex items-center justify-center transition-all flex-shrink-0">
              <i class="fas fa-store text-gray-400 text-xs"></i>
            </div>
            <div class="flex flex-col min-w-0 flex-1">
              <span class="text-[11px] font-bold text-gray-800 group-hover:text-pink-600 transition-colors truncate leading-tight">${
                store.Kd_Store
              }</span>
              <span class="text-[10px] text-gray-500 truncate leading-tight">${
                store.Nm_Alias || store.Nm_Store
              }</span>
            </div>
            <i class="fas fa-check-circle text-pink-500 text-xs opacity-0 peer-checked:opacity-100 transition-opacity"></i>
          </div>
        </label>
      `;
      branchContainer.appendChild(div);
      const input = div.querySelector("input");
      input.addEventListener("change", () => {
        handleBranchSelectionChange();
      });
    });
  }
  branchSearchInput.addEventListener("input", (e) => {
    const keyword = e.target.value.toLowerCase();
    const filtered = allBranchesData.filter(
      (store) =>
        store.Kd_Store.toLowerCase().includes(keyword) ||
        (store.Nm_Alias && store.Nm_Alias.toLowerCase().includes(keyword)) ||
        (store.Nm_Store && store.Nm_Store.toLowerCase().includes(keyword))
    );
    renderBranches(filtered);
  });
  btnSelectAll.addEventListener("click", () => {
    const checkboxes = branchContainer.querySelectorAll(
      "input[type='checkbox']"
    );
    checkboxes.forEach((cb) => {
      cb.checked = true;
    });
    handleBranchSelectionChange();
  });
  btnDeselectAll.addEventListener("click", () => {
    const checkboxes = branchContainer.querySelectorAll(
      "input[type='checkbox']"
    );
    checkboxes.forEach((cb) => {
      cb.checked = false;
    });
    handleBranchSelectionChange();
  });
  function getSelectedStores() {
    const checkboxes = branchContainer.querySelectorAll(
      "input[type='checkbox']:checked"
    );
    return Array.from(checkboxes).map((cb) => cb.value);
  }
  function handleBranchSelectionChange() {
    const selectedOptions = getSelectedStores();
    selectedCountLabel.textContent = `${selectedOptions.length} Dipilih`;
    if (selectedOptions.length > 0) {
      document
        .getElementById("supplier-section")
        .classList.remove("opacity-50");
    } else {
      document.getElementById("supplier-section").classList.add("opacity-50");
    }
    selectSupp.disabled = true;
    selectSupp.classList.add("bg-gray-50", "cursor-not-allowed");
    btnSubmit.disabled = true;
    clearTimeout(debounceTimer);
    if (selectedOptions.length > 0) {
      selectSupp.innerHTML = '<option value="">⏳ Menunggu...</option>';
      suppLoadingText.innerHTML =
        '<i class="fas fa-spinner fa-spin text-pink-500"></i> Mengambil data...';
      debounceTimer = setTimeout(() => {
        loadSuppliers(selectedOptions);
      }, 300);
    } else {
      selectSupp.innerHTML =
        '<option value="">-- Pilih Cabang Dulu --</option>';
      suppLoadingText.innerHTML =
        '<i class="fas fa-arrow-up text-pink-400"></i> Pilih cabang untuk memuat supplier';
    }
  }
  async function loadSuppliers(storeCodes) {
    suppLoadingText.innerHTML =
      '<i class="fas fa-spinner fa-spin text-pink-500"></i> Sedang mengambil data supplier...';
    try {
      const response = await fetch(
        "/src/api/option/get_supplier_by_cabang.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ kd_store: storeCodes }),
        }
      );
      const result = await response.json();
      selectSupp.innerHTML = '<option value="">-- Pilih Supplier --</option>';
      if (result.success && result.data.length > 0) {
        result.data.forEach((supp) => {
          const option = document.createElement("option");
          option.value = supp.kode;
          option.textContent = `${supp.kode} - ${supp.nama}`;
          selectSupp.appendChild(option);
        });
        selectSupp.disabled = false;
        selectSupp.classList.remove("bg-gray-50", "cursor-not-allowed");
        selectSupp.classList.add("bg-white");
        suppLoadingText.innerHTML = `<span class="text-green-600 font-bold"><i class="fas fa-check-circle"></i> ${result.data.length} Supplier tersedia</span>`;
        if (window.innerWidth > 768) selectSupp.focus();
      } else {
        suppLoadingText.innerHTML = `<span class="text-red-500"><i class="fas fa-times-circle"></i> Tidak ada supplier ditemukan</span>`;
        selectSupp.disabled = true;
        selectSupp.classList.add("bg-gray-50", "cursor-not-allowed");
      }
    } catch (e) {
      console.error(e);
      suppLoadingText.innerHTML =
        '<span class="text-red-500"><i class="fas fa-exclamation-triangle"></i> Gagal memuat supplier</span>';
      selectSupp.innerHTML = '<option value="">Error Koneksi</option>';
      selectSupp.disabled = true;
    }
  }
  async function checkUrlAndLoad() {
    const params = new URLSearchParams(window.location.search);
    const storesParam = params.get("stores");
    const suppParam = params.get("supp");
    if (storesParam) {
      const stores = storesParam.split(",");
      const checkboxes = branchContainer.querySelectorAll(
        "input[type='checkbox']"
      );
      checkboxes.forEach((cb) => {
        if (stores.includes(cb.value)) {
          cb.checked = true;
        }
      });
      selectedCountLabel.textContent = `${stores.length} Dipilih`;
      if (stores.length > 0) {
        document
          .getElementById("supplier-section")
          .classList.remove("opacity-50");
        await loadSuppliers(stores);
        if (suppParam) {
          selectSupp.value = suppParam;
          if (selectSupp.value) {
            btnSubmit.disabled = false;
            resetAndFetchData();
          }
        }
      }
    }
  }
  function updateUrlState(stores, supp) {
    const url = new URL(window.location);
    if (stores.length > 0) url.searchParams.set("stores", stores.join(","));
    if (supp) url.searchParams.set("supp", supp);
    window.history.pushState({}, "", url);
  }
  selectSupp.addEventListener("change", () => {
    btnSubmit.disabled = !selectSupp.value;
    if (selectSupp.value) {
      resetAndFetchData();
    } else {
      showEmptyState("Silahkan pilih supplier.");
      if (totalBadge) totalBadge.classList.add("hidden");
    }
  });
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    resetAndFetchData();
  });
  if (btnReset) {
    btnReset.addEventListener("click", () => {
      const checkboxes = branchContainer.querySelectorAll(
        "input[type='checkbox']"
      );
      checkboxes.forEach((cb) => {
        cb.checked = false;
      });
      handleBranchSelectionChange();
      branchSearchInput.value = "";
      renderBranches(allBranchesData);
      showEmptyState(
        "Silahkan pilih cabang dan supplier untuk menampilkan data."
      );
      if (totalBadge) totalBadge.classList.add("hidden");
      currentPage = 1;
      const url = new URL(window.location);
      url.searchParams.delete("stores");
      url.searchParams.delete("supp");
      window.history.pushState({}, "", url);
    });
  }
  if (tableScrollContainer) {
    tableScrollContainer.addEventListener("scroll", () => {
      const scrollTop = Math.ceil(tableScrollContainer.scrollTop);
      const scrollHeight = tableScrollContainer.scrollHeight;
      const clientHeight = tableScrollContainer.clientHeight;
      if (scrollTop + clientHeight >= scrollHeight - 50) {
        if (!isLoadingMore && hasMoreData) {
          loadMoreData();
        }
      }
    });
  }
  function resetAndFetchData() {
    const stores = getSelectedStores();
    const supp = selectSupp.value;
    if (stores.length === 0 || !supp) return;
    updateUrlState(stores, supp);
    currentPage = 1;
    hasMoreData = true;
    currentHeaders = [];
    tableHeadersRow.innerHTML = "";
    tableBody.innerHTML = "";
    if (totalBadge) totalBadge.classList.add("hidden");
    resultContainer.classList.remove("hidden");
    resultContainer.classList.add("show");
    /* Opsional: scroll ke result. 
      Jika ini menyebabkan jump pada mobile, bisa di comment.
    */
    if (tableScrollContainer) {
      tableScrollContainer.scrollTop = 0;
    }
    fetchStockData(stores, supp, 1);
  }
  function loadMoreData() {
    const stores = getSelectedStores();
    const supp = selectSupp.value;
    if (!supp || stores.length === 0) {
      console.warn("Mencegah request: Parameter tidak lengkap saat scroll");
      return;
    }
    currentPage++;
    fetchStockData(stores, supp, currentPage);
  }
  async function fetchStockData(stores, supplier, page) {
    if (!supplier || stores.length === 0) {
      return;
    }
    isLoadingMore = true;
    if (page === 1) {
      btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mencari...';
      btnSubmit.disabled = true;
      tableBody.innerHTML = `
        <tr>
          <td colspan="100%" class="text-center p-0" style="height: 400px;">
             <div class="flex flex-col items-center justify-center h-full">
                <div class="inline-block shimmer rounded-xl px-8 py-6 bg-pink-50 border border-pink-100">
                    <i class="fas fa-spinner fa-spin text-pink-500 text-3xl mb-3"></i>
                    <p class="text-gray-600 font-bold animate-pulse">Memuat data stock...</p>
                </div>
            </div>
          </td>
        </tr>`;
    } else {
      const loadingRow = document.createElement("tr");
      loadingRow.id = "loading-row";
      loadingRow.innerHTML = `
        <td colspan="100%" class="text-center py-3 bg-pink-50">
          <i class="fas fa-spinner fa-spin text-pink-500"></i> 
        </td>`;
      tableBody.appendChild(loadingRow);
    }
    try {
      const response = await fetch("/src/api/stok/get_data.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          kd_store: stores,
          kode_supp: supplier,
          page: page,
        }),
      });
      const result = await response.json();
      if (page === 1) {
        tableBody.innerHTML = "";
        if (totalBadge && result.total_items !== undefined) {
          totalBadge.textContent = `${formatNumber(
            result.total_items
          )} Data Ditemukan`;
          totalBadge.classList.remove("hidden");
        }
      } else {
        const lr = document.getElementById("loading-row");
        if (lr) lr.remove();
      }
      if (result.error) throw new Error(result.error);
      hasMoreData = result.has_more;
      if (page === 1) {
        currentHeaders = result.headers;
        renderTableHeader(result.headers);
      }
      if (result.data.length === 0 && page === 1) {
        tableBody.innerHTML = `
          <tr>
            <td colspan="100%" class="text-center p-0" style="height: 400px;">
                <div class="flex flex-col items-center justify-center h-full text-gray-400">
                    <i class="fas fa-box-open text-gray-300 text-5xl mb-3"></i>
                    <p class="text-gray-500 font-medium">Data tidak ditemukan</p>
                </div>
            </td>
          </tr>`;
        if (totalBadge) {
          totalBadge.textContent = "0 Data Ditemukan";
          totalBadge.classList.remove("hidden");
        }
      } else {
        const startNumber = (page - 1) * 20 + 1;
        appendTableRows(result.data, currentHeaders, startNumber);
      }
    } catch (error) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: error.message,
        confirmButtonColor: "#ec4899",
      });
      if (page === 1)
        tableBody.innerHTML = `
          <tr>
            <td colspan="100%" class="text-center p-0" style="height: 400px;">
                <div class="flex flex-col items-center justify-center h-full text-red-400">
                    <i class="fas fa-exclamation-circle text-4xl mb-2"></i>
                    <p class="font-medium">Terjadi kesalahan</p>
                </div>
            </td>
          </tr>`;
      if (totalBadge) totalBadge.classList.add("hidden");
    } finally {
      isLoadingMore = false;
      btnSubmit.innerHTML = '<i class="fas fa-search"></i> Cari Stock';
      btnSubmit.disabled = false;
    }
  }
  function renderTableHeader(storeHeaders) {
    tableHeadersRow.innerHTML = `
      <th class="px-2 py-2 border-b-2 border-pink-200 text-center w-10 font-bold text-pink-700 text-xs">
        <i class="fas fa-hashtag"></i>
      </th>
      <th class="px-2 py-2 border-b-2 border-pink-200 sticky left-0 z-30 shadow-[2px_0_8px_-2px_rgba(236,72,153,0.2)] font-bold text-xs bg-white">
        PLU
      </th>
      <th class="px-2 py-2 border-b-2 border-pink-200 font-bold text-xs">Barcode</th>
      <th class="px-2 py-2 border-b-2 border-pink-200 min-w-[150px] font-bold text-xs">Nama Barang</th>
      <th class="px-2 py-2 border-b-2 border-pink-200 text-center font-bold text-pink-700 text-xs">
        Total
      </th>
    `;
    storeHeaders.forEach((store) => {
      const th = document.createElement("th");
      th.className =
        "px-2 py-2 border-b-2 border-l border-pink-200 text-center bg-gradient-to-br from-pink-50 to-pink-100 text-pink-800 whitespace-nowrap sticky top-0 z-20 font-bold text-xs";
      th.innerHTML = `
        <div class="flex flex-col items-center gap-0.5">
          <i class="fas fa-store text-[10px] text-pink-500"></i>
          <span class="text-[10px]">${store.name}</span>
        </div>`;
      tableHeadersRow.appendChild(th);
    });
  }
  function appendTableRows(data, storeHeaders, startNumber = 1) {
    const fragment = document.createDocumentFragment();
    data.forEach((item, index) => {
      const tr = document.createElement("tr");
      tr.className =
        "hover:bg-gradient-to-r hover:from-pink-50 hover:to-transparent border-b border-gray-100 transition-all group";
      let totalRowQty = 0;
      storeHeaders.forEach((store) => {
        totalRowQty +=
          item.stok_per_store[store.code] !== undefined
            ? parseFloat(item.stok_per_store[store.code])
            : 0;
      });
      const currentNumber = startNumber + index;
      let html = `
        <td class="px-2 py-1.5 text-center text-[11px] text-gray-400 font-bold border-r border-gray-100">
          ${currentNumber}
        </td>
        <td class="px-2 py-1.5 font-mono text-[11px] font-bold text-pink-700 sticky left-0 bg-white group-hover:bg-pink-50 z-10 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)] border-r border-gray-100">
          ${item.plu}
        </td>
        <td class="px-2 py-1.5 font-mono text-[11px] text-gray-600">${
          item.barcode || "-"
        }</td>
        <td class="px-2 py-1.5 text-[11px] font-medium text-gray-800 leading-tight">${
          item.nama_barang
        }</td>
        <td class="px-2 py-1.5 text-center text-[11px] font-bold bg-gradient-to-r from-pink-50 to-pink-100 text-pink-700 border-x border-pink-200">
          ${formatNumber(totalRowQty)}
        </td>
      `;
      storeHeaders.forEach((store) => {
        const qty =
          item.stok_per_store[store.code] !== undefined
            ? parseFloat(item.stok_per_store[store.code])
            : 0;
        let colorClass = "text-gray-700";
        let bgClass = "";
        if (qty <= 0) {
          colorClass = "text-red-600 font-bold";
          bgClass = "bg-red-50";
        } else if (qty > 100) {
          colorClass = "text-green-600 font-bold";
          bgClass = "bg-green-50";
        }
        html += `<td class="px-2 py-1.5 text-center text-[11px] ${colorClass} ${bgClass} border-l border-gray-100">${formatNumber(
          qty
        )}</td>`;
      });
      tr.innerHTML = html;
      fragment.appendChild(tr);
    });
    tableBody.appendChild(fragment);
  }
  function formatNumber(num) {
    return new Intl.NumberFormat("id-ID").format(num);
  }
  window.exportToExcel = function () {
    const table = document.getElementById("stock-table");
    if (!table) return;
    const wb = XLSX.utils.table_to_book(table, { sheet: "Stock" });
    XLSX.writeFile(wb, "Laporan_Stock_Harian.xlsx");
  };
});
