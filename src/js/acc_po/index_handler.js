document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("filter-form");
    const inputSupp = document.getElementById("input-supplier");
    const hiddenSupp = document.getElementById("kode_supp_val");
    const suppList = document.getElementById("supplier-list");
    const selectArea = document.getElementById("select-area");
    const sectionCabang = document.getElementById("section-cabang");
    const branchTrigger = document.getElementById("branch-dropdown-trigger");
    const branchMenu = document.getElementById("branch-dropdown-menu");
    const branchLabel = document.getElementById("branch-selected-label");
    const branchIcon = document.getElementById("branch-dropdown-icon");
    const branchSearch = document.getElementById("branch-search");
    const branchContainer = document.getElementById("branch-container");
    const btnSelectAll = document.getElementById("btn-select-all");
    const btnDeselectAll = document.getElementById("btn-deselect-all");
    const btnResetForm = document.getElementById("btn-reset-form");
    const btnSubmit = document.getElementById("btn-submit");
    const tableHead = document.getElementById("table-head");
    const tableBody = document.getElementById("table-body");
    const resultContainer = document.getElementById("result-container");
    const pageTitle = document.getElementById("dynamic-subtitle");
    const totalBadge = document.getElementById("total-badge");
    const scrollContainer = document.querySelector(".overflow-auto");
    const btnColToggle = document.getElementById("btn-col-toggle");
    const colMenu = document.getElementById("col-menu");
    const colListContainer = document.getElementById("col-list-container");
    const styleElement = document.getElementById("dynamic-column-styles");
    let debounceTimer;
    let accumulatedData = [];
    let currentHeaders = [];
    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;
    let allBranches = [];
    const columnConfig = [
    { id: 'no', label: 'No', class: 'c-no', default: true, isSticky: true },
    { id: 'plu', label: 'PLU', class: 'c-plu', default: true, isSticky: true },
    { id: 'barcode', label: 'Barcode', class: 'c-barcode', default: true, isSticky: true },
    { id: 'nama', label: 'Nama Barang', class: 'c-nama', default: true, isSticky: true },
    { id: 'hbeli', label: 'H. Beli', class: 'c-hbeli', default: true },
    { id: 'hjual', label: 'H. Jual', class: 'c-hjual', default: true },
    { id: 's_jual', label: 'Toko: Jual', class: 'c-s-jual', default: true, isStoreCol: true },
    { id: 's_stok', label: 'Toko: Stok', class: 'c-s-stok', default: true, isStoreCol: true },
    { id: 's_posys', label: 'Toko: PO Sys', class: 'c-s-posys', default: true, isStoreCol: true },
    { id: 's_pomd', label: 'Toko: PO MD', class: 'c-s-pomd', default: true, isStoreCol: true },
    { id: 's_seas', label: 'Toko: Pj Season', class: 'c-s-seas', default: false, isStoreCol: true },
    { id: 's_mutasi', label: 'Toko: Mutasi', class: 'c-s-mutasi', default: false, isStoreCol: true },
    { id: 's_sdhpo', label: 'Toko: Sdh PO', class: 'c-s-sdhpo', default: false, isStoreCol: true },
    { id: 't_po', label: 'Total PO', class: 'c-t-po', default: true },
    { id: 't_pj', label: 'Total PJ', class: 'c-t-pj', default: true },
    { id: 't_ss', label: 'Total SS', class: 'c-t-ss', default: true },
    { id: 't_rasio', label: 'Rasio', class: 'c-t-rasio', default: true },
    { id: 't_rp', label: 'Total Rp', class: 'c-t-rp', default: true },
];
    init();
    async function init() {
        if (!branchTrigger || !branchMenu) {
            console.error("CRITICAL: Elemen Dropdown tidak ditemukan di HTML. Pastikan index.php sudah diupdate.");
            return;
        }
        await Promise.all([loadAreas(), loadBranches()]);
        checkFormValidity();
        await loadUrlParams();
        initColumnToggler();
    }
    if(inputSupp) {
        inputSupp.addEventListener("input", function() {
            const val = this.value;
            if (hiddenSupp.value !== "") {
                hiddenSupp.value = "";
                checkFormValidity();
            }
            clearTimeout(debounceTimer);
            if (!val || val.length < 1) {
                suppList.classList.add("hidden");
                return;
            }
            debounceTimer = setTimeout(async () => {
                try {
                    suppList.classList.remove("hidden");
                    suppList.innerHTML = `<div class="p-2 text-gray-400 text-xs"><i class="fas fa-spinner fa-spin"></i> Mencari...</div>`;
                    const res = await fetch(`/src/api/acc_po/get_suppliers.php?q=${encodeURIComponent(val)}`);
                    const data = await res.json();
                    renderSupplierList(data);
                } catch (e) {
                    suppList.innerHTML = `<div class="p-2 text-red-400 text-xs">Gagal memuat data</div>`;
                }
            }, 300);
        });
        document.addEventListener("click", (e) => {
            if (!inputSupp.contains(e.target) && !suppList.contains(e.target)) {
                suppList.classList.add("hidden");
            }
        });
    }
    function formatValueDisplay(val, isNumber = true) {
        if (val === null || val === undefined || val === '') {
            return '<span class="empty-val">-</span>';
        }
        if (val == 0) return '<span class="empty-val">0</span>';
        return isNumber ? formatNumber(val) : val;
    }
    async function loadUrlParams() {
        const params = new URLSearchParams(window.location.search);
        const kodeSupp = params.get('kode_supp');
        const namaSupp = params.get('supp_name'); 
        if (kodeSupp && namaSupp) {
            hiddenSupp.value = kodeSupp;
            inputSupp.value = namaSupp;
        }
        const area = params.get('kode_area');
        if (area) {
            selectArea.value = area;
            selectArea.dispatchEvent(new Event('change'));
        }
        const branches = params.get('kd_store');
        if (branches && !area) {
            const branchList = branches.split(',');
            branchList.forEach(alias => {
                const cb = document.querySelector(`.branch-checkbox[value="${alias}"]`);
                if (cb) cb.checked = true;
            });
            handleBranchChange(); 
        }
        checkFormValidity();
        if (!btnSubmit.disabled) {
            form.dispatchEvent(new Event('submit'));
        }
    }
    function syncUrlParams() {
        const params = new URLSearchParams();
        if (hiddenSupp.value) {
            params.set('kode_supp', hiddenSupp.value);
            params.set('supp_name', inputSupp.value); 
        }
        if (selectArea.value) {
            params.set('kode_area', selectArea.value);
        } else {
            const checked = Array.from(document.querySelectorAll(".branch-checkbox:checked"))
                .map(cb => cb.value);
            if (checked.length > 0) {
                params.set('kd_store', checked.join(','));
            }
        }
        const newUrl = `${window.location.pathname}?${params.toString()}`;
        window.history.replaceState(null, '', newUrl);
    }
    function updateHeaderTitle(firstRowData) {
        if (!pageTitle) return;
        const suppName = inputSupp.value || "Semua Supplier";
        let locationText = "";
        if (selectArea.value) {
            const areaText = selectArea.options[selectArea.selectedIndex].text;
            locationText = `AREA ${areaText}`;
        } else {
            const checkedBoxes = Array.from(document.querySelectorAll(".branch-checkbox:checked"));
            if (checkedBoxes.length === 0) {
                locationText = "Semua Cabang";
            } else {
                const names = checkedBoxes.map(cb => cb.value); 
                if (names.length <= 2) {
                    locationText = names.join(", ");
                } else {
                    locationText = `${names[0]}, ${names[1]}, ...`;
                }
            }
        }
        let dateText = "";
        if (firstRowData && firstRowData.tgl_awal && firstRowData.tgl_akhir) {
            dateText = `${firstRowData.tgl_awal} s/d ${firstRowData.tgl_akhir}`;
        }
        pageTitle.innerText = `Sisa Stok Supplier ${suppName}, ${locationText}, ${dateText}`;
    }
    function renderSupplierList(data) {
        suppList.innerHTML = "";
        if (data.length === 0) {
            suppList.innerHTML = `<div class="p-2 text-gray-400 text-xs">Tidak ditemukan</div>`;
            return;
        }
        data.forEach(item => {
            const div = document.createElement("div");
            div.className = "autocomplete-item flex items-center gap-2 p-2 hover:bg-pink-50 cursor-pointer border-b border-gray-100 last:border-0 transition-colors";
            div.innerHTML = `<i class="fas fa-truck text-pink-400 text-xs"></i> <span class="text-xs text-gray-700">${item.text}</span>`;
            div.addEventListener("click", () => {
                inputSupp.value = item.text;
                hiddenSupp.value = item.id;
                suppList.classList.add("hidden");
                checkFormValidity();
            });
            suppList.appendChild(div);
        });
    }
    if (branchTrigger && branchMenu && branchSearch) {
        branchTrigger.addEventListener("click", (e) => {
            if (sectionCabang.classList.contains("opacity-50")) return; 
            e.stopPropagation();
            toggleBranchMenu();
        });
        document.addEventListener("click", (e) => {
            if (branchMenu && !branchMenu.classList.contains("hidden")) {
                if (!branchTrigger.contains(e.target) && !branchMenu.contains(e.target)) {
                    closeBranchMenu();
                }
            }
        });
        branchSearch.addEventListener("input", (e) => {
            const keyword = e.target.value.toLowerCase();
            const filtered = allBranches.filter(b => {
                const name = b.Nm_Alias || b.Nm_Store || "";
                const code = b.Kd_Store || "";
                return code.toLowerCase().includes(keyword) || name.toLowerCase().includes(keyword);
            });
            renderBranches(filtered);
        });
    }
    function toggleBranchMenu() {
        const isHidden = branchMenu.classList.contains("hidden");
        if (isHidden) {
            branchMenu.classList.remove("hidden");
            if(branchIcon) branchIcon.style.transform = "rotate(180deg)";
            branchTrigger.classList.add("ring-2", "ring-pink-500", "border-pink-500");
            if(branchSearch) branchSearch.focus();
        } else {
            closeBranchMenu();
        }
    }
    function closeBranchMenu() {
        branchMenu.classList.add("hidden");
        if(branchIcon) branchIcon.style.transform = "rotate(0deg)";
        branchTrigger.classList.remove("ring-2", "ring-pink-500", "border-pink-500");
    }
    async function loadAreas() {
        try {
            const res = await fetch("/src/api/option/get_area.php");
            const json = await res.json();
            if (json.success) {
                selectArea.innerHTML = '<option value="">-- Pilih Area --</option>';
                json.data.forEach(area => {
                    const opt = document.createElement("option");
                    opt.value = area.id;
                    opt.textContent = area.text;
                    selectArea.appendChild(opt);
                });
            }
        } catch (e) {
            console.error("Error load areas", e);
        }
    }
    async function loadBranches() {
        try {
            const res = await fetch("/src/api/option/get_cabang.php");
            const json = await res.json();
            if (json.success) {
                allBranches = json.data; 
                renderBranches(allBranches);
            } else {
                throw new Error(json.message || "Gagal mengambil data cabang");
            }
        } catch (e) {
            console.error("LOAD BRANCH ERROR:", e); 
            if (branchContainer) {
                branchContainer.innerHTML = `<p class="text-red-500 text-xs p-2 text-center">Gagal memuat: ${e.message}</p>`;
            }
        }
    }
    function renderBranches(data) {
        if (!branchContainer) return;
        branchContainer.innerHTML = "";
        if(data.length === 0) {
            branchContainer.innerHTML = '<p class="text-gray-400 text-xs p-2 text-center">Tidak ditemukan</p>';
            return;
        }
        data.forEach(store => {
            const storeCode = store.Kd_Store;
            const storeName = store.Nm_Alias || store.Nm_Store || "No Name";
            const label = document.createElement("label");
            label.className = "flex items-center gap-3 p-2 hover:bg-pink-50 rounded cursor-pointer transition-colors border-b border-gray-50 last:border-0";
            const isChecked = document.querySelector(`.branch-checkbox[value="${store.Nm_Alias}"]`)?.checked || false;
            label.innerHTML = `
                <div class="relative flex items-center">
                    <input type="checkbox" name="kd_store[]" value="${store.Nm_Alias}" 
                        class="branch-checkbox peer h-4 w-4 cursor-pointer appearance-none rounded border border-gray-300 shadow-sm checked:border-pink-500 checked:bg-pink-500 hover:border-pink-400 focus:ring-1 focus:ring-pink-200"
                        ${isChecked ? 'checked' : ''}>
                    <i class="fas fa-check absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-[10px] text-white opacity-0 peer-checked:opacity-100 pointer-events-none"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-[11px] font-bold text-gray-700 leading-tight">${storeCode}</span>
                    <span class="text-[10px] text-gray-500 leading-tight">${storeName}</span>
                </div>
            `;
            label.querySelector("input").addEventListener("change", handleBranchChange);
            branchContainer.appendChild(label);
        });
    }
    if (selectArea) {
        selectArea.addEventListener("change", () => {
            const isAreaSelected = selectArea.value !== "";
            if (isAreaSelected) {
                sectionCabang.classList.add("opacity-50", "pointer-events-none", "grayscale");
                document.querySelectorAll(".branch-checkbox").forEach(cb => cb.checked = false);
                updateBranchLabel();
            } else {
                sectionCabang.classList.remove("opacity-50", "pointer-events-none", "grayscale");
            }
            checkFormValidity();
        });
    }
    function handleBranchChange() {
        const checkedBoxes = document.querySelectorAll(".branch-checkbox:checked");
        const checkedCount = checkedBoxes.length;
        if (checkedCount > 0) {
            selectArea.value = ""; 
            selectArea.disabled = true;
            selectArea.classList.add("bg-gray-100", "cursor-not-allowed");
        } else {
            selectArea.disabled = false;
            selectArea.classList.remove("bg-gray-100", "cursor-not-allowed");
        }
        updateBranchLabel(checkedCount, checkedBoxes);
        checkFormValidity();
    }
    function updateBranchLabel(count = 0, checkedNodes = []) {
        if (!branchLabel) return;
        if (count === 0) {
            branchLabel.textContent = "-- Pilih Cabang --";
            branchLabel.classList.remove("text-pink-600", "font-bold");
            branchLabel.classList.add("text-gray-500");
        } else if (count === 1) {
            const name = checkedNodes[0].closest('label').querySelector('span:last-child').textContent;
            branchLabel.textContent = name;
            branchLabel.classList.add("text-pink-600", "font-bold");
        } else if (count === allBranches.length && allBranches.length > 0) {
            branchLabel.textContent = "Semua Cabang Terpilih";
            branchLabel.classList.add("text-pink-600", "font-bold");
        } else {
            branchLabel.textContent = `${count} Cabang Terpilih`;
            branchLabel.classList.add("text-pink-600", "font-bold");
        }
    }
    if (btnSelectAll) {
        btnSelectAll.addEventListener("click", () => {
            const visibleCheckboxes = branchContainer.querySelectorAll(".branch-checkbox");
            visibleCheckboxes.forEach(cb => cb.checked = true);
            handleBranchChange();
        });
    }
    if (btnDeselectAll) {
        btnDeselectAll.addEventListener("click", () => {
            document.querySelectorAll(".branch-checkbox").forEach(cb => cb.checked = false);
            handleBranchChange();
            if (branchSearch) {
                branchSearch.value = "";
                renderBranches(allBranches);
                branchSearch.focus();
            }
        });
    }
    if (btnResetForm) {
        btnResetForm.addEventListener("click", () => {
            form.reset();
            hiddenSupp.value = "";
            sectionCabang.classList.remove("opacity-50", "pointer-events-none", "grayscale");
            selectArea.disabled = false;
            selectArea.classList.remove("bg-gray-100", "cursor-not-allowed");
            document.querySelectorAll(".branch-checkbox").forEach(cb => cb.checked = false);
            updateBranchLabel();
            if(branchSearch) branchSearch.value = "";
            renderBranches(allBranches);
            resultContainer.classList.add("hidden");
            checkFormValidity();
        });
    }
    function checkFormValidity() {
        const hasSupp = hiddenSupp.value !== "";
        const hasArea = selectArea.value !== "";
        const hasBranch = document.querySelectorAll(".branch-checkbox:checked").length > 0;
        const isValid = hasSupp && (hasArea || hasBranch);
        btnSubmit.disabled = !isValid;
        if (isValid) {
            btnSubmit.classList.remove("opacity-50", "cursor-not-allowed");
        } else {
            btnSubmit.classList.add("opacity-50", "cursor-not-allowed");
        }
    }
    if (form) {
        form.addEventListener("submit", (e) => {
            e.preventDefault();
            syncUrlParams();
            accumulatedData = [];
            tableBody.innerHTML = "";
            currentPage = 1;
            hasMore = true;
            currentHeaders = [];
            resultContainer.classList.remove("hidden");
            if (scrollContainer) scrollContainer.scrollTop = 0;
            fetchData(1);
        });
    }
    async function fetchData(page) {
        if (isLoading) return;
        isLoading = true;
        if (page === 1) {
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            tableBody.innerHTML = `<tr><td colspan="100%" class="text-center py-10"><i class="fas fa-spinner fa-spin text-pink-500 text-2xl"></i><p class="text-gray-500 text-xs mt-2">Memuat data...</p></td></tr>`;
        } else {
            const loadingRow = document.createElement("tr");
            loadingRow.id = "loading-row";
            loadingRow.innerHTML = `<td colspan="100%" class="text-center py-4 bg-gray-50"><i class="fas fa-spinner fa-spin text-pink-500"></i></td>`;
            tableBody.appendChild(loadingRow);
        }
        const formData = new FormData(form);
        const payload = {
            kode_supp: hiddenSupp.value,
            page: page
        };
        if (selectArea.value) {
            payload.kode_area = selectArea.value;
        } else {
            payload.kd_store = formData.getAll("kd_store[]");
        }
        try {
            const res = await fetch("/src/api/acc_po/get_data.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (!result.success) {
                if (page === 1) tableBody.innerHTML = `<tr><td colspan="100%" class="text-center py-4 text-red-500 text-xs">${result.error}</td></tr>`;
                Swal.fire("Peringatan", result.error || "Gagal mengambil data", "warning");
                return;
            }
            const lr = document.getElementById("loading-row");
            if (lr) lr.remove();
            if (page === 1) {
                currentHeaders = result.headers; 
                renderTableHeader(result.headers);
                const count = result.data.length; 
                totalBadge.innerText = `${count}+ Data Ditampilkan`; 
                tableBody.innerHTML = ""; 
                const firstItem = result.data.length > 0 ? result.data[0] : null;
                updateHeaderTitle(firstItem);
            }
            if (result.data.length > 0) {
                accumulatedData = [...accumulatedData, ...result.data];
                renderTableBody(result.data, result.headers);
            } else if (page === 1) {
                tableBody.innerHTML = `<tr><td colspan="100%" class="text-center py-8 text-gray-400 text-xs">
                    <i class="fas fa-box-open text-2xl mb-2 opacity-50"></i><br>Tidak ada data ditemukan
                </td></tr>`;
            }
            hasMore = result.has_more;
            currentPage = page;
        } catch (error) {
            console.error(error);
            Swal.fire("Error", "Terjadi kesalahan sistem", "error");
            if (page === 1) tableBody.innerHTML = `<tr><td colspan="100%" class="text-center text-red-500 py-4">Error koneksi</td></tr>`;
        } finally {
            isLoading = false;
            btnSubmit.innerHTML = '<i class="fas fa-search mr-1"></i> Tampilkan Data';
        }
    }
    function initColumnToggler() {
        if (!colListContainer) return;
        colListContainer.innerHTML = '';
        const stickyDiv = document.createElement('div');
        stickyDiv.className = 'p-2 mb-2 bg-pink-50 rounded border border-pink-100';
        stickyDiv.innerHTML = `
            <div class="flex items-center gap-2">
                <input type="checkbox" id="chk-sticky-main" class="rounded text-pink-500 focus:ring-pink-400" checked>
                <label for="chk-sticky-main" class="text-xs font-bold text-pink-700 cursor-pointer">Sticky Info Barang</label>
            </div>
        `;
        colListContainer.appendChild(stickyDiv);
        document.getElementById('chk-sticky-main').addEventListener('change', function() {
            const isSticky = this.checked;
            const cols = document.querySelectorAll('.sticky-col');
            cols.forEach(el => {
                if(isSticky) {
                    el.style.position = 'sticky';
                    el.style.zIndex = el.tagName === 'TH' ? '30' : '10';
                } else {
                    el.style.position = 'static';
                }
            });
        });
        columnConfig.forEach(col => {
            const div = document.createElement('div');
            div.className = 'flex items-center gap-2 mb-1 pl-2';
            div.innerHTML = `
                <input type="checkbox" id="chk-${col.id}" class="col-checkbox rounded text-pink-500 focus:ring-pink-400" ${col.default ? 'checked' : ''} data-class="${col.class}">
                <label for="chk-${col.id}" class="text-xs text-gray-700 cursor-pointer select-none">${col.label}</label>
            `;
            colListContainer.appendChild(div);
        });
        const checkboxes = document.querySelectorAll('.col-checkbox');
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateTableVisibility);
        });
        btnColToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            colMenu.classList.toggle('hidden');
        });
        document.addEventListener('click', (e) => {
            if (!colMenu.contains(e.target) && !btnColToggle.contains(e.target)) {
                colMenu.classList.add('hidden');
            }
        });
    }
    function updateTableVisibility() {
        if (!styleElement) return;
        let cssRules = "";
        const checkboxes = document.querySelectorAll('.col-checkbox');
        checkboxes.forEach(cb => {
            if (!cb.checked) {
                const className = cb.getAttribute('data-class');
                cssRules += `.${className} { display: none !important; } \n`;
            }
        });
        styleElement.textContent = cssRules;
        let activeStoreCols = 0;
        columnConfig.forEach(col => {
            if (col.isStoreCol) {
                const cb = document.getElementById(`chk-${col.id}`);
                if (cb && cb.checked) {
                    activeStoreCols++;
                }
            }
        });
        const storeHeaders = document.querySelectorAll('.store-header-dynamic');
        storeHeaders.forEach(th => {
            if (activeStoreCols > 0) {
                th.style.display = 'table-cell'; 
                th.colSpan = activeStoreCols;    
            } else {
                th.style.display = 'none';       
            }
        });
    }
    function renderTableHeader(stores) {
        let row1 = `
            <tr class="divide-x divide-pink-200">
                <th rowspan="2" class="c-no p-2 bg-pink-50 w-10 border-b border-pink-200 sticky-col" style="left:0;">No</th>
                <th rowspan="2" class="c-plu p-2 bg-pink-50  border-b border-pink-200 sticky-col" style="left:40px;">PLU</th>
                <th rowspan="2" class="c-barcode p-2 bg-pink-50  border-b border-pink-200 sticky-col shadow-lg" style="left:120px;">Barcode</th>
                <th rowspan="2" class="c-nama p-2  bg-pink-50 border-b border-pink-200 sticky-col" style="left:220px;">Nama Barang</th>
                <th rowspan="2" class="c-hbeli p-2  bg-pink-50 border-b border-pink-200">H. Beli</th>
                <th rowspan="2" class="c-hjual p-2  bg-pink-50 border-b border-pink-200">H. Jual</th>
        `;
        stores.forEach(store => {
            row1 += `<th class="store-header-dynamic p-1 text-center bg-pink-100 text-pink-800 font-bold border-b border-pink-300 text-[10px] uppercase tracking-wider">${store.name}</th>`;
        });
        row1 += `
                <th rowspan="2" class="c-t-po p-2  bg-yellow-50 border-b border-yellow-200 text-yellow-800">Total PO</th>
                <th rowspan="2" class="c-t-pj p-2  bg-yellow-50 border-b border-yellow-200 text-yellow-800">Total PJ</th>
                <th rowspan="2" class="c-t-ss p-2  bg-yellow-50 border-b border-yellow-200 text-yellow-800">Total SS</th>
                <th rowspan="2" class="c-t-rasio p-2  bg-yellow-50 border-b border-yellow-200 text-yellow-800">Rasio</th>
                <th rowspan="2" class="c-t-rp p-2  bg-yellow-50 border-b border-yellow-200 text-yellow-800">Total Rp</th>
            </tr>
        `;
        let row2 = `<tr class="divide-x divide-pink-200">`;
        stores.forEach(() => {
            row2 += `
                <th class="c-s-jual p-1  text-[9px] bg-white text-gray-500 font-normal">Jual</th>
                <th class="c-s-stok p-1  text-[9px] bg-white text-gray-500 font-normal">Stok</th>
                <th class="c-s-posys p-1  text-[9px] bg-blue-50 text-blue-600 font-bold">PO Sys</th>
                <th class="c-s-pomd p-1  text-[9px] bg-pink-50 text-pink-600 font-bold">PO MD</th>
                <th class="c-s-seas p-1  text-[9px] bg-purple-50 text-purple-600 font-normal">Pj Season</th>
                <th class="c-s-mutasi p-1  text-[9px] bg-gray-50 text-gray-600 font-normal">Mutasi</th>
                <th class="c-s-sdhpo p-1  text-[9px] bg-green-50 text-green-600 font-normal">Sdh PO</th>
            `;
        });
        row2 += `</tr>`;
        tableHead.innerHTML = row1 + row2;
        updateTableVisibility();
    }
    function renderTableBody(data, stores) {
        const fragment = document.createDocumentFragment();
        let startNum = (currentPage - 1) * 20 + 1;
        data.forEach((item, idx) => {
            const tr = document.createElement("tr");
            tr.className = "hover:bg-pink-50 transition-colors border-b border-gray-100 text-xs group";
            let html = `
                <td class="c-no p-2 text-center bg-white group-hover:bg-pink-50 font-bold text-gray-400 border-r border-gray-100 sticky-col" style="left:0;">${startNum + idx}</td>
                <td class="c-plu p-2 bg-white group-hover:bg-pink-50 font-mono text-pink-600 font-bold border-r border-gray-100 sticky-col" style="left:40px;">${item.plu}</td>
                <td class="c-barcode p-2 bg-white group-hover:bg-pink-50 font-mono text-gray-500 border-r border-gray-100 shadow-md sticky-col" style="left:120px;">${item.barcode || '-'}</td>
                <td class="c-nama p-2 text-gray-800 font-medium truncate max-w-[200px] bg-white sticky-col" style="left:220px;" title="${item.descp}">${item.descp}</td>
                <td class="c-hbeli p-2 text-right font-mono text-gray-600">${formatNumber(item.h_beli)}</td>
                <td class="c-hjual p-2 text-right font-mono text-gray-600">${formatNumber(item.h_jual)}</td>
            `;
            stores.forEach(store => {
                const branchData = item.branches[store.code] || { 
                    penjualan: null, stok_akhir: null, po_by_system: null, 
                    po_by_md: null, penjualan_s: null, mutasi: null, sudah_po: null 
                };
                const valPOMD = branchData.po_by_md !== null ? branchData.po_by_md : '';
                html += `
                    <td class="c-s-jual p-1 text-right border-r border-pink-50">${formatValueDisplay(branchData.penjualan)}</td>
                    <td class="c-s-stok p-1 text-right border-r border-pink-50">${formatValueDisplay(branchData.stok_akhir)}</td>
                    <td class="c-s-posys p-1 text-right border-r border-pink-50 text-blue-600 font-bold">${formatValueDisplay(branchData.po_by_system)}</td>
                    <td class="c-s-pomd p-1 border-r border-pink-50">
                        <input type="number" class="input-po-md table-input bg-pink-50 focus:bg-white border-pink-200 font-bold text-pink-700" 
                            value="${valPOMD}" 
                            placeholder="0"
                            data-original="${valPOMD}">
                    </td>
                    <td class="c-s-seas p-1 text-right border-r border-pink-50 text-purple-600">${formatValueDisplay(branchData.penjualan_s)}</td>
                    <td class="c-s-mutasi p-1 text-right border-r border-pink-50 text-gray-600">${formatValueDisplay(branchData.mutasi)}</td>
                    <td class="c-s-sdhpo p-1 text-right border-r border-pink-50 text-green-600">${formatValueDisplay(branchData.sudah_po)}</td>
                `;
            });
            const sum = item.summary;
            html += `
                <td class="c-t-po p-1 bg-yellow-50/30 border-l border-yellow-100 text-right font-bold">${formatNumber(sum.Total_PO)}</td>
                <td class="c-t-pj p-1 bg-yellow-50/30 text-right">${formatNumber(sum.Total_PJ)}</td>
                <td class="c-t-ss p-1 bg-yellow-50/30 text-right">${formatNumber(sum.Total_SS)}</td>
                <td class="c-t-rasio p-1 bg-yellow-50/30 text-right">${sum.Rasio}</td>
                <td class="c-t-rp p-1 bg-yellow-50/30 text-right font-bold">${formatNumber(sum.Total_Rp)}</td>
            `;
            tr.innerHTML = html;
            fragment.appendChild(tr);
        });
        tableBody.appendChild(fragment);
        attachInputListeners();
    }
    function attachInputListeners() {
        const inputs = document.querySelectorAll('.input-po-md');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                const tr = this.closest('tr');
                const originalVal = this.dataset.original;
                const currentVal = this.value;
                if (currentVal !== originalVal) {
                    tr.classList.add('row-modified');
                } else {
                    const allInputsInRow = tr.querySelectorAll('.input-po-md');
                    let isAnyChanged = false;
                    allInputsInRow.forEach(inp => {
                        if (inp.value !== inp.dataset.original) isAnyChanged = true;
                    });
                    if (!isAnyChanged) {
                        tr.classList.remove('row-modified');
                    }
                }
            });
        });
    }
    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }
    if (scrollContainer) {
        scrollContainer.addEventListener("scroll", () => {
            const { scrollTop, scrollHeight, clientHeight } = scrollContainer;
            if (scrollTop + clientHeight >= scrollHeight - 50) {
                if (!isLoading && hasMore) {
                    fetchData(currentPage + 1);
                }
            }
        });
    }
});