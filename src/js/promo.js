// State For Data
let promoData = [];
let dataBarang = [];
let dataBarangEdit = [];
let kodeSupp = [];
let filteredData = [];
let allDiskonCells = [];
let allPotonganCells = [];
let allDiskonCellsEdit = [];
let allPotonganCellsEdit = [];
let diskonCache = {};
let potonganCache = {};
let diskonCacheEdit = {};
let potonganCacheEdit = {};
// Fetch Data
fetch('/src/api/transaction/promo/get_data_promo')
    .then(res => res.json())
    .then(data => {
        promoData = data.promo;
        filteredData = promoData.filter((item, index, self) =>
            index === self.findIndex((t) => t.kode_promo === item.kode_promo))
        checkAndUpdatePromoStatus();
        // console.log("filter data", filteredData)
        updateDataInfo()
        renderTable()
    }).catch(error => {
        console.log("Gagal Fetch Data", error)
    })

// State management
let currentPage = 1;
let pageSize = 10;

// DOM Elements
const tableBody = document.getElementById('tableBody');
const searchInput = document.getElementById('searchInput');
const pageSizeSelect = document.getElementById('pageSize');
const paginationContainer = document.getElementById('pagination');
const loadingTable = document.getElementById('loadTable');
loadingTable.classList.add("hidden")
// Render table row
function renderRow(item, index) {
  const globalIndex = (currentPage - 1) * pageSize + index + 1;

  // Format tanggal: "12 Agu 2025" (fallback ke YYYY-MM-DD jika invalid)
  const fmtID = (s) => {
    try {
      const d = new Date((s || '').split(' ')[0] + 'T00:00:00');
      return isNaN(d) ? (s || '').split(' ')[0] : d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    } catch { return (s || '').split(' ')[0]; }
  };
  const dateRange = `${fmtID(item.tgl_mulai)} – ${fmtID(item.tgl_selesai)}`;

  const isAktif = String(item.status).toLowerCase() === 'aktif';
  const isDipakai = String(item.status_digunakan).toLowerCase() === 'sedang_dipakai';

  const statusChip   = isAktif   ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-slate-100 text-slate-700 ring-1 ring-slate-200';
  const statusDot    = isAktif   ? 'bg-emerald-500'                                       : 'bg-slate-400';
  const gunakanChip  = isDipakai ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200'   : 'bg-slate-100 text-slate-700 ring-1 ring-slate-200';
  const gunakanDot   = isDipakai ? 'bg-indigo-500'                                         : 'bg-slate-400';

  const storeFull = item.nama_store || '';
  const storeShort = storeFull.length > 18 ? `${storeFull.slice(0, 18)}…` : storeFull;

  return `
    <tr class="group even:bg-white odd:bg-slate-50/40 hover:bg-slate-50 transition-colors duration-150">
      <td class="px-4 py-3 text-slate-600 font-medium tabular-nums">${globalIndex}</td>
      <td class="px-4 py-3 text-slate-800 font-semibold font-mono">${item.kode_promo}</td>
      <td class="px-4 py-3 text-slate-700">${item.nama_supplier}</td>

      <td class="px-4 py-3 text-slate-700">
        <span class="inline-block max-w-[12rem] truncate align-middle cursor-help"
              title="${storeFull}" data-tippy-content="${storeFull}">
          ${storeShort}
        </span>
      </td>

      <td class="px-4 py-3 text-slate-600 tabular-nums">${dateRange}</td>

      <td class="px-4 py-3">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ${statusChip}">
          <span class="h-1.5 w-1.5 rounded-full ${statusDot}"></span>
          ${item.status}
        </span>
      </td>

      <td class="px-4 py-3">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ${gunakanChip}">
          <span class="h-1.5 w-1.5 rounded-full ${gunakanDot}"></span>
          ${item.status_digunakan}
        </span>
      </td>

      <td class="px-4 py-3 text-slate-700">${item.keterangan ?? ''}</td>

      <td class="px-4 py-3">
        <div class="flex items-center justify-center gap-2 whitespace-nowrap">
          <button
            onclick="editPromo('${item.kode_promo}','${item.kode_supp}')"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border border-indigo-200 bg-white text-indigo-700 hover:bg-indigo-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-200 transition"
            aria-label="Edit promo ${item.kode_promo}" data-tippy-content="Edit">
            <i class="fas fa-edit text-[12px]"></i>
            <span class="hidden md:inline">Edit</span>
          </button>

          <button
            onclick="detailPromo('${item.kode_promo}','${item.kode_supp}')"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-slate-200 transition"
            aria-label="Lihat detail ${item.kode_promo}" data-tippy-content="Detail">
            <i class="fa-solid fa-eye text-[12px]"></i>
            <span class="hidden md:inline">Detail</span>
          </button>
        </div>
      </td>
    </tr>
  `;
}


// Render pagination (UI enhanced only)
function renderPagination() {
  const totalPages = Math.ceil(filteredData.length / pageSize);
  let paginationHTML = '';

  // Previous
  paginationHTML += `
    <button onclick="changePage(${currentPage - 1})"
      class="inline-flex items-center justify-center h-9 px-3 rounded-lg border bg-white
             border-slate-300 text-slate-700 text-sm font-medium
             hover:bg-slate-50 disabled:opacity-60 disabled:cursor-not-allowed
             focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-200 transition"
      ${currentPage === 1 ? 'disabled' : ''}>
      <i class="fa-solid fa-angle-left mr-1 text-xs"></i>
      <span class="hidden sm:inline">Previous</span>
    </button>
  `;

  // Page numbers
  for (let i = 1; i <= totalPages; i++) {
    const isActive = currentPage === i;
    paginationHTML += `
      <button onclick="changePage(${i})"
        class="inline-flex items-center justify-center h-9 px-3 rounded-lg border text-sm font-medium transition
               ${isActive
                 ? 'bg-indigo-600 text-blue-200 border-indigo-600'
                 : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-200'}">
        ${i}
      </button>
    `;
  }

  // Next
  paginationHTML += `
    <button onclick="changePage(${currentPage + 1})"
      class="inline-flex items-center justify-center h-9 px-3 rounded-lg border bg-white
             border-slate-300 text-slate-700 text-sm font-medium
             hover:bg-slate-50 disabled:opacity-60 disabled:cursor-not-allowed
             focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-200 transition"
      ${currentPage === totalPages ? 'disabled' : ''}>
      <span class="hidden sm:inline">Next</span>
      <i class="fa-solid fa-angle-right ml-1 text-xs"></i>
    </button>
  `;

  paginationContainer.innerHTML = paginationHTML;
}


// Change page
function changePage(page) {
    currentPage = page;
    renderTable();
}

function updateDataInfo() {
    const start = (currentPage - 1) * pageSize + 1;
    const end = Math.min(currentPage * pageSize, filteredData.length);
    const total = filteredData.length;

    const infoText = `Menampilkan ${start}–${end} dari ${total} data`;
    document.getElementById('dataInfo').textContent = infoText;
}

// Filter data
function filterData(searchTerm) {
    // First get the deduplicated data
    const uniquePromoData = promoData.filter((item, index, self) =>
        index === self.findIndex((t) => t.kode_promo === item.kode_promo)
    );

    // Then filter the unique data based on search term
    filteredData = uniquePromoData.filter(item =>
        Object.values(item).some(value =>
            String(value).toLowerCase().includes(searchTerm.toLowerCase())
        )
    );

    currentPage = 1;
    renderTable();
}

// Render table
function renderTable() {
    const start = (currentPage - 1) * pageSize;
    const end = start + pageSize;
    const paginatedData = filteredData.slice(start, end);

    tableBody.innerHTML = paginatedData.map((item, index) => renderRow(item, index)).join('');
    renderPagination();
    tippy('[data-tippy-content]');

}

// Event listeners
searchInput.addEventListener('input', (e) => filterData(e.target.value));
pageSizeSelect.addEventListener('change', (e) => {
    pageSize = parseInt(e.target.value);
    currentPage = 1;
    renderTable();
});

// Initial render
renderTable();


// Fetch store data
function fetchStoreData(divList, isReset = false) {
    const dropdownList = document.getElementById(divList);
    if (isReset) dropdownList.innerHTML = '';
    return fetch('/src/api/transaction/promo/get_store')
        .then(res => res.json())
        .then(data => {
            // console.log('Store data:', data);
            if (data.data_cabang) {
                // Add "SEMUA CABANG" option
                const allStoresOption = document.createElement('div');
                allStoresOption.className = 'p-3';
                allStoresOption.innerHTML = `
                    <label class="flex items-center px-5 py-3 hover:bg-pink-50 rounded-lg cursor-pointer transition-colors duration-200">
                        <input type="checkbox" value="all" class="w-5 h-5 mr-3 cabangCheckbox rounded text-pink-500 focus:ring-pink-300">
                        <span class="text-base text-pink-600">SEMUA CABANG</span>
                    </label>
                `;
                dropdownList.appendChild(allStoresOption);

                // Add store options
                data.data_cabang.forEach(store => {
                    const storeOption = document.createElement('div');
                    storeOption.className = 'p-3';
                    storeOption.innerHTML = `
                        <label class="flex items-center px-5 py-3 hover:bg-pink-50 rounded-lg cursor-pointer transition-colors duration-200">
                            <input type="checkbox" value="${store.Kd_Store}" class="w-5 h-5 mr-3 cabangCheckbox rounded text-pink-500 focus:ring-pink-300">
                            <span class="text-base text-pink-600">${store.Nm_Alias} - ${store.Kd_Store} </span>
                        </label>
                    `;
                    dropdownList.appendChild(storeOption);
                });
                // Reattach event listeners
                attachCheckboxListeners('dropdownCabangList', 'cabangTerpilih', 'dropdownCabangButton');
            }
        })
        .catch(error => {
            console.error("Error fetching store data:", error);
        });
}
function fetchStoreDataEdit(divList, isReset = false, selectedCabang = []) {
    const dropdownList = document.getElementById(divList);
    if (isReset) dropdownList.innerHTML = '';

    return fetch('/src/api/transaction/promo/get_store')
        .then(res => res.json())
        .then(data => {
            if (data.data_cabang) {
                // Tambah opsi "SEMUA CABANG"
                const allStoresOption = document.createElement('div');
                const isAllCabang = selectedCabang.length === data.data_cabang.length
                allStoresOption.className = 'p-3';
                allStoresOption.innerHTML = `
                    <label class="flex items-center px-5 py-3 hover:bg-pink-50 rounded-lg cursor-pointer transition-colors duration-200">
                        <input type="checkbox" value="all" class="w-5 h-5 mr-3 cabangCheckbox rounded text-pink-500 focus:ring-pink-300" ${isAllCabang ? 'checked' : ''}>
                        <span class="text-base text-pink-600">SEMUA CABANG</span>
                    </label>
                `;
                dropdownList.appendChild(allStoresOption);

                // Tambah opsi cabang
                data.data_cabang.forEach(store => {
                    const isChecked = selectedCabang.includes(store.Kd_Store);
                    const storeOption = document.createElement('div');
                    storeOption.className = 'p-3';
                    storeOption.innerHTML = `
                        <label class="flex items-center px-5 py-3 hover:bg-pink-50 rounded-lg cursor-pointer transition-colors duration-200">
                            <input type="checkbox" value="${store.Kd_Store}" ${isChecked ? 'checked' : ''} class="w-5 h-5 mr-3 cabangCheckbox rounded text-pink-500 focus:ring-pink-300">
                            <span class="text-base text-pink-600">${store.Nm_Alias} - ${store.Kd_Store}</span>
                        </label>
                    `;
                    dropdownList.appendChild(storeOption);
                });

                // Update tombol dropdown jika ada cabang terpilih
                const dropdownCabangButtonEdit = document.getElementById('dropdownCabangButtonEdit');
                if (dropdownCabangButtonEdit) {
                    if (isAllCabang) {
                        const allStoreCodes = data.data_cabang.map(store => store.Kd_Store).join(',');
                        document.getElementById('cabangTerpilihEdit').value = allStoreCodes;
                        dropdownCabangButtonEdit.textContent = "SEMUA CABANG";
                    } else if (selectedCabang.length > 0) {
                        const selectedText = selectedCabang.map(kodeCabang => {
                            const matched = data.data_cabang.find(store => store.Kd_Store === kodeCabang);
                            return matched ? `${matched.Kd_Store}` : kodeCabang;
                        }).join(', ');
                        dropdownCabangButtonEdit.textContent = selectedText;
                    } else {
                        dropdownCabangButtonEdit.textContent = "Pilih Cabang";
                    }
                }

                // Pasang event listener checkbox
                attachCheckboxListeners('dropdownCabangListEdit', 'cabangTerpilihEdit', 'dropdownCabangButtonEdit');
                if (isAllCabang) {
                    const semuaCabangCheckbox = dropdownList.querySelector('.cabangCheckbox[value="all"]');
                    if (semuaCabangCheckbox) {
                        semuaCabangCheckbox.dispatchEvent(new Event('change'));
                    }
                }
            }
        })
        .catch(error => {
            console.error("Error fetching store data:", error);
        });
}
// Toggle dropdown Cabang
function toggleCabangDropdown(listId) {
    const dropdownList = document.getElementById(listId);
    if (!dropdownList) return;
    dropdownList.classList.toggle('hidden');
    if (!dropdownList.classList.contains('hidden') && dropdownList.children.length === 0) {
        if (listId === 'dropdownCabangList') {
            fetchStoreData(listId, true);
        } else {
            fetchStoreDataEdit(listId, true);
        }
    }

}

function wrapperClick(divId, divList, e) {
    const wrapper = document.getElementById(divId);
    if (!wrapper.contains(e.target)) {
        document.getElementById(divList).classList.add('hidden');
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function (e) {
    wrapperClick('dropdownCabangWrapperEdit', 'dropdownCabangListEdit', e);
    wrapperClick('dropdownCabangWrapper', 'dropdownCabangList', e);
});

// Attach event listeners to checkboxes
function attachCheckboxListeners(containerId, cbId, btnId) {
    const container = document.getElementById(containerId);
    const checkboxes = container.querySelectorAll('.cabangCheckbox');
    const semuaCabangCheckbox = container.querySelector('.cabangCheckbox[value="all"]');
    const storeCheckboxes = Array.from(checkboxes).filter(cb => cb.value !== "all");

    console.log('Initial checkboxes:', {
        semuaCabang: semuaCabangCheckbox,
        storeCheckboxes: storeCheckboxes.map(cb => ({ value: cb.value, checked: cb.checked }))
    });

    // Handle "SEMUA CABANG" checkbox
    semuaCabangCheckbox.addEventListener('change', () => {
        console.log('SEMUA CABANG checkbox changed:', {
            checked: semuaCabangCheckbox.checked,
            value: semuaCabangCheckbox.value
        });

        if (semuaCabangCheckbox.checked) {
            // Uncheck and disable all other checkboxes
            storeCheckboxes.forEach(cb => {
                cb.checked = true;
                cb.disabled = true;
            });
            // Get all Kd_Store values
            const allStoreValues = storeCheckboxes.map(cb => cb.value).join(',');
            // console.log('All store values:', allStoreValues);
            document.getElementById(cbId).value = allStoreValues;
            document.getElementById(btnId).textContent = "SEMUA CABANG";
        } else {
            // Enable all other checkboxes
            storeCheckboxes.forEach(cb => {
                cb.checked = false
                cb.disabled = false;
            });
            document.getElementById(cbId).value = "";
            document.getElementById(btnId).textContent = "Pilih Cabang";
        }
    });

    // Handle individual store checkboxes
    storeCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            console.log('Store checkbox changed:', {
                value: checkbox.value,
                checked: checkbox.checked
            });

            // If any store is checked, uncheck "SEMUA CABANG"
            if (checkbox.checked) {
                semuaCabangCheckbox.checked = false;
                semuaCabangCheckbox.disabled = false;
            }

            const selected = Array.from(storeCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);

            // console.log('Selected stores:', selected);
            document.getElementById(cbId).value = selected.join(',');
            document.getElementById(btnId).textContent =
                selected.length > 0 ? selected.join(', ') : 'Pilih Cabang';
        });
    });
}

// Handle form submission Tambah Promo
$(document).ready(function () {
    $("#formTambahPromo").on("submit", function (e) {
        e.preventDefault();
        const bodyTable = document.querySelectorAll("#tbody-barang tr");
        const dataTable = [];

        bodyTable.forEach(row => {
            const cells = row.querySelectorAll('td');
            let barcode = cells[0].textContent.trim();
            let namaBarang = cells[1].textContent.trim();
            let hargaJual = cells[2].textContent.trim();
            let diskon = cells[3].textContent.trim();
            let potongan = cells[4].textContent.trim();
            let kode_supp = cells[5].textContent.trim();
            if (barcode && barcode !== '0' && namaBarang && (hargaJual !== '' || diskon !== '' || potongan !== '')) {
                dataTable.push({ barcode, namaBarang, hargaJual, diskon, potongan, kode_supp });
            }
        });

        let barangToSend = [];

        // Ambil semua nilai diskon yang bukan nol
        let diskonValues = dataTable.map(item => Number(item.diskon));
        let potonganValues = dataTable.map(item => Number(item.potongan));

        // Cek apakah SEMUA item punya diskon yang sama dan BUKAN 0
        let allDiskonSame =
            dataTable.length > 1 &&
            new Set(diskonValues).size === 1 &&
            diskonValues[0] !== 0;

        // Cek apakah SEMUA item punya potongan yang sama dan BUKAN 0
        let allPotonganSame =
            dataTable.length > 1 &&
            new Set(potonganValues).size === 1 &&
            potonganValues[0] !== 0;

        if (allDiskonSame) {
            barangToSend.push({
                barcode: 0,
                namaBarang: "All Master",
                hargaJual: Number(dataTable[0].hargaJual),
                diskon: diskonValues[0],
                potongan: 0,
                kode_supp: String(dataTable[0].kode_supp)
            });
        } else if (allPotonganSame) {
            barangToSend.push({
                barcode: 0,
                namaBarang: "All Master",
                hargaJual: Number(dataTable[0].hargaJual),
                diskon: 0,
                potongan: potonganValues[0],
                kode_supp: String(dataTable[0].kode_supp)

            });
        } else {
            // Tidak sama semua, kirim satu per satu
            barangToSend = dataTable.map(item => ({ ...item }));
        }

        // console.log("diskonValues:", diskonValues);
        // console.log("unique diskon:", new Set(diskonValues));
        // console.log("allDiskonSame:", allDiskonSame);
        // // --- END tambahan cek ---

        // console.log("Data barang yang akan dikirim:", barangToSend);

        // Get form data
        const formData = {
            kode_promo: $("input[name='kode_promo']").val(),
            nama_supplier: $("input[name='supplier']").val(),
            kd_store: $("#cabangTerpilih").val(),
            start: $("input[name='tanggal_mulai']").val(),
            end: $("input[name='tanggal_selesai']").val(),
            ket: $("textarea[name='keterangan']").val(),
            status: "aktif",
            status_digunakan: "tidak_dipakai",
            barang: barangToSend
        };
        // console.log("request data", formData);

        // Validate form
        if (!formData.kode_promo || !formData.nama_supplier || !formData.kd_store || !formData.start || !formData.end || dataTable.length === 0) {
            Swal.fire({
                title: 'Peringatan!',
                text: 'Mohon lengkapi semua field yang diperlukan!',
                icon: 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#ec4899'
            });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah kamu yakin ingin menyimpan data ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading setelah user konfirmasi
                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                // Send data to server using fetch
                fetch('/src/api/transaction/promo/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                })
                    .then(response => response.json())
                    .then(response => {
                        if (response.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message || 'Promo berhasil disimpan!',
                                icon: 'success',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#ec4899'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    closeModal('modalTambahPromo', 'modalContent');
                                    $("#formTambahPromo")[0].reset();
                                    document.getElementById("dropdownCabangButton").textContent = "Pilih Cabang";
                                    document.getElementById("cabangTerpilih").value = "";

                                    // Refresh table data
                                    return fetch('/src/api/transaction/promo/get_data_promo');
                                }
                            })
                                .then(res => res.json())
                                .then(data => {
                                    promoData = data.promo;
                                    filteredData = promoData.filter((item, index, self) =>
                                        index === self.findIndex((t) => t.kode_promo === item.kode_promo)
                                    );
                                    updateDataInfo()
                                    renderTable();
                                })
                                .catch(error => {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: `Gagal memuat ulang data promo ${error}`,
                                        icon: 'error',
                                        confirmButtonText: 'OK',
                                        confirmButtonColor: '#ec4899'
                                    });
                                });
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: response.message || 'Gagal menyimpan promo',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#ec4899'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error!',
                            text: `Terjadi kesalahan saat menyimpan promo!${error}`,
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#ec4899'
                        });
                    });
            } else {
                // User membatalkan
                console.log('User membatalkan penyimpanan.');
            }
        });
    });
});

//Hanlde Form submission Edit Promo
$(document).ready(function () {
    $("#formEditPromo").on("submit", function (e) {
        e.preventDefault();
        const bodyTable = document.querySelectorAll("#tbody-barang-edit tr");
        const dataTable = [];

        bodyTable.forEach(row => {
            const cells = row.querySelectorAll('td');
            let barcode = cells[0].textContent.trim();
            let namaBarang = cells[1].textContent.trim();
            let hargaJual = cells[2].textContent.trim();
            let diskon = cells[3].textContent.trim();
            let potongan = cells[4].textContent.trim();
            let kode_supp = cells[5].textContent.trim();
            let idPromo = cells[6].textContent.trim();
            dataTable.push({ barcode, namaBarang, hargaJual, diskon, potongan, kode_supp, idPromo });
        });
        let barangToSend = [];
        barangToSend = dataTable.map(item => ({ ...item }));


        // console.log("diskonValues:", diskonValues);
        // console.log("unique diskon:", new Set(diskonValues));
        // console.log("allDiskonSame:", allDiskonSame);
        // // --- END tambahan cek ---

        // console.log("Data barang yang akan dikirim:", barangToSend);

        // Get form data
        const formData = {
            kode_promo: $("input[name='kode_promo_edit']").val(),
            nama_supplier: $("input[name='supplier-edit']").val(),
            kd_store: $("#cabangTerpilihEdit").val(),
            start: $("#start-edit").val(),
            end: $("#end-edit").val(),
            ket: $("textarea[name='keterangan-edit']").val(),
            status: $("#status_edit").val(),
            status_digunakan: $("#status_edit_penggunaan").val(),
            barang: barangToSend
        };
        console.log("request data", formData);

        // Validate form
        if (!formData.kode_promo || !formData.nama_supplier || !formData.kd_store || !formData.start || !formData.end || dataTable.length === 0) {
            Swal.fire({
                title: 'Peringatan!',
                text: 'Mohon lengkapi semua field yang diperlukan!',
                icon: 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#ec4899'
            });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah kamu yakin ingin menyimpan data ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading setelah user konfirmasi
                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                // Send data to server using fetch
                fetch('/src/api/transaction/promo/update_promo', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                })
                    .then(response => response.json())
                    .then(response => {
                        if (response.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message || 'Promo berhasil disimpan!',
                                icon: 'success',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#ec4899'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    closeModal('modalEditPromo', 'modalContentEdit');
                                    // Refresh table data
                                    return fetch('/src/api/transaction/promo/get_data_promo');
                                }
                            })
                                .then(res => res.json())
                                .then(data => {
                                    promoData = data.promo;
                                    filteredData = promoData.filter((item, index, self) =>
                                        index === self.findIndex((t) => t.kode_promo === item.kode_promo)
                                    );
                                    updateDataInfo()
                                    checkAndUpdatePromoStatus();
                                    renderTable();
                                })
                                .catch(error => {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: `Gagal memuat ulang data promo ${error}`,
                                        icon: 'error',
                                        confirmButtonText: 'OK',
                                        confirmButtonColor: '#ec4899'
                                    });
                                });
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: response.message || 'Gagal menyimpan promo',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#ec4899'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error!',
                            text: `Terjadi kesalahan saat menyimpan promo!${error}`,
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#ec4899'
                        });
                    });
            } else {
                // User membatalkan
                console.log('User membatalkan penyimpanan.');
            }
        });
    });
})

// Initialize autocomplete for barang / supplier
function initFlexibleAutocomplete({ selector, url, key = 'descp', onSelect = null }) {
    // console.log('Initializing autocomplete for:', selector);
    let loadedData = [];
    // Initialize autocomplete when document is ready
    $(document).ready(function () {
        const inputField = $(selector);

        // Load data immediately when document is ready
        if (!url) {
            inputField.on('focus input', function () {
                showSuggestions($(this), dataBarang, key, onSelect);
            });
        } else {
            fetch(url)
                .then(res => res.json())
                .then(res => {
                    // console.log('Raw response:', res);
                    // Check if data is in the expected format
                    if (res && res.data_supplier) {
                        loadedData = res.data_supplier;
                    } else if (Array.isArray(res)) {
                        loadedData = res;
                    } else {
                        loadedData = [];
                    }
                })
                .catch(error => {
                    console.error("Failed to fetch data:", error);
                    loadedData = [];
                });

            // Event untuk input field focus
            inputField.on('focus', function () {
                showSuggestions($(this), loadedData, key, onSelect);
            });

            // Event untuk input field saat ada perubahan
            inputField.on('input', function () {
                showSuggestions($(this), loadedData, key, onSelect);
            });

        }
        // Event untuk menutup suggestion ketika klik di luar
        $(document).on('click', function (e) {
            if (!$(e.target).closest(selector + ', .barang-suggestions').length) {
                $('.barang-suggestions').remove();
            }
        });
    });
}

// Fungsi untuk menampilkan suggestions
function showSuggestions(inputField, dataList, key, onSelect) {
    const inputVal = inputField.val().toLowerCase();

    let filtered = dataList.filter(item => {
        const value = item[key] ?? '';
        return value.toLowerCase().includes(inputVal);
    });

    // Hapus suggestions sebelumnya
    $('.barang-suggestions').remove();

    // Jika ada suggestions
    if (filtered.length > 0) {
        const div = $('<div class="barang-suggestions absolute z-10 bg-white border border-pink-200 rounded-lg mt-1 max-h-60 overflow-y-auto shadow-md"></div>');
        div.css({
            'min-width': '200px',
            'max-width': '300px',
            'width': 'auto'
        });

        filtered.forEach(item => {
            const suggestionText = item[key] ?? '';
            $('<div class="px-4 py-2 hover:bg-pink-50 cursor-pointer whitespace-nowrap"></div>')
                .text(suggestionText)
                .on('click', () => {
                    inputField.val(suggestionText);
                    $('.barang-suggestions').remove();

                    if (typeof onSelect === 'function') {
                        onSelect(item, inputField);
                    }
                })
                .appendTo(div);
        });

        inputField.after(div);
    }
}

// Initialize autocomplete for supplier
$(document).ready(function () {
    initFlexibleAutocomplete({
        selector: "#supplier-view",
        url: "/src/api/transaction/promo/get_name_supplier",
        key: "nama_supp",
        onSelect: (item) => {
            loadingTable.classList.remove("hidden")
            let kdSupp = item.kode_supp;
            fetch("/src/api/transaction/promo/get_name_barang?supplier=" + encodeURIComponent(kdSupp), {
                method: 'GET'
            })
                .then(res => res.json())
                .then(res => {
                    loadingTable.classList.add("hidden")
                    dataBarang = res.data_barang.map(item => ({
                        ...item,
                        kode_supp: kdSupp
                    }))
                    // console.log("Data", dataBarang)
                    const tbody = document.getElementById("tbody-barang");
                    tbody.innerHTML = '';


                    dataBarang.forEach(barang => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                                <td contenteditable="false" class="w-1/4 border border-pink-200 p-1 text-sm">${barang.plu}</td>
                                <td contenteditable="false" class="w-2/5 border border-pink-200 p-1 text-sm">${barang.descp}</td>
                                <td contenteditable="false" class="w-1/4 border border-pink-200 p-1 text-sm">${barang.harga_jual ?? ''}</td>
                                <td contenteditable="true" class="w-1/4 border border-pink-200 p-1 diskon text-sm" data-plu="${barang.plu}"></td>
                                <td contenteditable="true" class="w-1/4 border border-pink-200 p-1 potongan text-sm" data-plu="${barang.plu}"></td>
                                <td class="hidden">${barang.kode_supp}</td>
    `;
                        tbody.appendChild(tr);

                        const diskonCell = tr.querySelector(".diskon");
                        const potonganCell = tr.querySelector(".potongan");

                        allDiskonCells.push(diskonCell);
                        allPotonganCells.push(potonganCell);

                        restrictToNumbers(diskonCell);
                        restrictToNumbers(potonganCell);

                        diskonCell.addEventListener('input', handleGlobalInputCheck);
                        potonganCell.addEventListener('input', handleGlobalInputCheck);
                    });

                })
                .catch(error => {
                    console.log("Gagal Fetch", error);
                })
        }
    });
    document.getElementById('tbody-barang').addEventListener('input', function (e) {
        if (e.target && e.target.classList.contains('diskon')) {
            const plu = e.target.dataset.plu;
            diskonCache[plu] = e.target.textContent.trim();
        }
        if (e.target && e.target.classList.contains('potongan')) {
            const plu = e.target.dataset.plu;
            potonganCache[plu] = e.target.textContent.trim();
        }
    });
    const inputSearch = document.getElementById('search-barang');
    inputSearch.addEventListener("input", function () {
        const keyword = this.value.toLowerCase();
        const hasilFilter = dataBarang.filter(b => {
            return String(b.plu).toLowerCase().includes(keyword) || String(b.descp).toLowerCase().includes(keyword);
        })
        renderTableDisOrPot(hasilFilter, 'tbody-barang')
    })
    // Set tanggal hari ini untuk start dan end
    setTodayDate('start');
    setTodayDate('end');
});

function handleGlobalInputCheck() {
    let isDiskonUsed = false;
    let isPotonganUsed = false;

    allDiskonCells.forEach(cell => {
        if (cell.textContent.trim() !== '') {
            isDiskonUsed = true;
        }
    });

    allPotonganCells.forEach(cell => {
        if (cell.textContent.trim() !== '') {
            isPotonganUsed = true;
        }
    });

    if (!isDiskonUsed && !isPotonganUsed) {
        allDiskonCells.forEach(cell => {
            cell.setAttribute("contenteditable", "true");
        });
        allPotonganCells.forEach(cell => {
            cell.setAttribute("contenteditable", "true");
        });
        return;
    }

    if (isDiskonUsed && isPotonganUsed) {
        console.warn("Hanya boleh mengisi salah satu: diskon atau potongan.");
        return;
    }


    allDiskonCells.forEach(cell => {
        cell.setAttribute("contenteditable", isPotonganUsed ? "false" : "true");
    });

    allPotonganCells.forEach(cell => {
        cell.setAttribute("contenteditable", isDiskonUsed ? "false" : "true");
    });
}


const allDiskonInput = document.getElementById("allDiskon");
const allPotonganInput = document.getElementById("allPotongan");
const allDiskonInputEdit = document.getElementById("allDiskonEdit");
const allPotonganInputEdit = document.getElementById("allPotonganEdit");
// Untuk diskon
allDiskonInput.addEventListener("blur", function () {
    const val = allDiskonInput.value.trim();
    if (val !== "") {
        confirmApplyAll("diskon", () => {
            applyValueToCells({
                inputEl: allDiskonInput,
                targetSelector: "td.diskon",
                type: "diskon"
            });
        });
    } else {
        // Jika kosong, hapus semua nilai diskon
        document.querySelectorAll("td.diskon").forEach(cell => {
            cell.textContent = "";
        });
        allPotonganInput.disabled = false;
    }
});
allPotonganInput.addEventListener("blur", function () {
    const val = allPotonganInput.value.trim();
    if (val !== "") {
        confirmApplyAll("potongan", () => {
            applyValueToCells({
                inputEl: allPotonganInput,
                targetSelector: "td.potongan",
                type: "potongan"
            });
        });
    } else {
        // Jika kosong, hapus semua nilai potongan
        document.querySelectorAll("td.potongan").forEach(cell => {
            cell.textContent = "";
        });
        allDiskonInput.disabled = false;

    }
});
allDiskonInputEdit.addEventListener("blur", function () {
    const val = allDiskonInputEdit.value.trim();
    if (val !== "") {
        confirmApplyAll("diskon", () => {
            applyValueToCells({
                inputEl: allDiskonInputEdit,
                targetSelector: "td.diskon",
                type: "diskon"
            });
        });
    } else {
        // Jika kosong, hapus semua nilai diskon
        document.querySelectorAll("td.diskon").forEach(cell => {
            cell.textContent = "";
        });
        allPotonganInputEdit.disabled = false;
    }
});
allPotonganInputEdit.addEventListener("blur", function () {
    const val = allPotonganInputEdit.value.trim();
    if (val !== "") {
        confirmApplyAll("potongan", () => {
            applyValueToCells({
                inputEl: allPotonganInputEdit,
                targetSelector: "td.potongan",
                type: "potongan"
            });
        });
    } else {
        // Jika kosong, hapus semua nilai potongan
        document.querySelectorAll("td.potongan").forEach(cell => {
            cell.textContent = "";
        });
        allDiskonInputEdit.disabled = false;

    }
});

function confirmApplyAll(type, callback) {
    Swal.fire({
        title: 'Konfirmasi',
        text: `Apakah Anda ingin mengisi semua kolom ${type}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, isi semua',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            callback();
        }
    });
}
function applyValueToCells({ inputEl, targetSelector, type = 'diskon' }) {
    const value = parseInt(inputEl.value);
    const targetCells = document.querySelectorAll(`td.${type}`);

    // Reset semua jika kosong
    if (isNaN(value)) {
        if (type === 'diskon') {
            allPotonganInput.disabled = false;
            allPotonganInputEdit.disabled = false;
        } else if (type === 'potongan') {
            allDiskonInput.disabled = false;
            allDiskonInputEdit.disabled = false;
        }
        inputEl.setCustomValidity("");
        targetCells.forEach(cell => cell.textContent = "");
        return;
    }

    // Khusus diskon, batasi 1-100
    if (type === 'diskon' && (value < 1 || value > 100)) {
        allPotonganInput.disabled = true;
        allPotonganInputEdit.disabled = true;
        inputEl.setCustomValidity("Diskon harus antara 1 sampai 100");
        inputEl.reportValidity();
        return;
    }

    // Valid input, clear custom validity
    inputEl.setCustomValidity("");

    // Set konten sel sesuai input
    targetCells.forEach(cell => {
        cell.textContent = value;
    });

    // Disable input lawan jenis saat ada isi
    if (type === 'diskon') {
        allPotonganInput.disabled = true;
        allPotonganInputEdit.disabled = true;
    } else if (type === 'potongan') {
        allDiskonInput.disabled = true;
        allDiskonInputEdit.disabled = true;
    }

    console.log(`${type}`, targetCells);
}
function renderTableDisOrPot(dataBarang, tbodyId) {
    if (!Array.isArray(dataBarang)) {
        console.error("Data bukan array:", dataBarang);
        return;
    }

    const tbody = document.getElementById(tbodyId);
    tbody.innerHTML = '';
    dataBarang.forEach(barang => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
                        <td contenteditable="false" class="w-1/4 border border-pink-200 p-1 text-sm">${barang.plu}</td>
                        <td contenteditable="false" class="w-2/5 border border-pink-200 p-1 text-sm">${barang.descp}</td>
                        <td contenteditable="false" class="w-1/4 border border-pink-200 p-1 text-sm">${barang.harga_jual ?? ''}</td>
                         <td contenteditable="true" class="w-1/4 border border-pink-200 p-1 diskon text-sm" data-plu="${barang.plu}">
                            ${diskonCache[barang.plu] ?? ''}
                        </td>
                        <td contenteditable="true" class="w-1/4 border border-pink-200 p-1 potongan text-sm" data-plu="${barang.plu}">
                            ${potonganCache[barang.plu] ?? ''}
                        </td>
                        `;
        tbody.appendChild(tr);
        const diskonCell = tr.querySelector(".diskon");
        const potonganCell = tr.querySelector(".potongan");

        allDiskonCells.push(diskonCell);
        allPotonganCells.push(potonganCell);

        restrictToNumbers(diskonCell);
        restrictToNumbers(potonganCell);

        diskonCell.addEventListener('input', handleGlobalInputCheck);
        potonganCell.addEventListener('input', handleGlobalInputCheck);
    });
}
function renderTableDisOrPotEd(dataBarang, tbodyId) {
    if (!Array.isArray(dataBarang)) {
        console.error("Data bukan array:", dataBarang);
        return;
    }

    const tbody = document.getElementById(tbodyId);
    tbody.innerHTML = '';
    dataBarang.forEach(barang => {
        console.log(barang.diskon)
        const tr = document.createElement('tr');
        tr.innerHTML = `
                        <td contenteditable="false" class="w-1/4 border border-pink-200 p-1 text-sm">${barang.plu}</td>
                        <td contenteditable="false" class="w-2/5 border border-pink-200 p-1 text-sm">${barang.descp}</td>
                        <td contenteditable="false" class="w-1/4 border border-pink-200 p-1 text-sm">${barang.harga_jual ?? ''}</td>
                         <td contenteditable="true" class="w-1/4 border border-pink-200 p-1 diskon text-sm" data-plu="${barang.plu}">
                            ${(barang.plu in diskonCacheEdit)
                                ? diskonCacheEdit[barang.plu]
                                : (barang.diskon !== "0" ? barang.diskon : '')}
                        </td>

                        <td contenteditable="true" class="w-1/4 border border-pink-200 p-1 potongan text-sm" data-plu="${barang.plu}">
                            ${(barang.plu in potonganCacheEdit)
                                ? potonganCacheEdit[barang.plu]
                                : (barang.potongan_harga !== "0" ? barang.potongan_harga : '')}
                        </td>
                        `;
        tbody.appendChild(tr);
        const diskonCell = tr.querySelector(".diskon");
        const potonganCell = tr.querySelector(".potongan");

        allDiskonCellsEdit.push(diskonCell);
        allPotonganCellsEdit.push(potonganCell);

        restrictToNumbers(diskonCell);
        restrictToNumbers(potonganCell);

        diskonCell.addEventListener('input', handleGlobalInputCheck);
        potonganCell.addEventListener('input', handleGlobalInputCheck);
    });
}
function restrictToNumbers(cell) {
    cell.addEventListener('keypress', (e) => {
        if (!/[0-9]/.test(e.key)) {
            e.preventDefault();
        }
    });
}
function setTodayDate(id) {
    const input = document.getElementById(id);
    if (!input) return;

    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');

    return input.value = `${yyyy}-${mm}-${dd}`;
}

// Add animation for modal
function tambahPromo() {
    const modal = document.getElementById("modalTambahPromo");
    const modalContent = document.getElementById("modalContent");
    const form = document.getElementById("formTambahPromo");
    form.reset();
    document.getElementById("dropdownCabangButton").textContent = "Pilih Cabang";
    document.getElementById("dropdownCabangList").disabled = false;
    document.getElementById("cabangTerpilih").value = "";
    document.getElementById("allDiskon").disabled = false;
    document.getElementById("allPotongan").disabled = false;
    document.getElementById("tbody-barang").innerHTML = '';
    setTodayDate('start')
    setTodayDate('end')
    modal.classList.remove("hidden");
    // Trigger animation
    setTimeout(() => {
        modalContent.classList.remove("scale-95", "opacity-0");
        modalContent.classList.add("scale-100", "opacity-100");
    }, 10);
}
function editPromo(kode_promo, kode_supplier) {
    // Show loading state
    loadingTable.classList.remove("hidden");

    // Fetch promo data
    fetch(`/src/api/transaction/promo/get_edit_promo?kode_promo=${kode_promo}&kode_supplier=${kode_supplier}`)
        .then(res => res.json())
        .then(data => {
            loadingTable.classList.add("hidden");

            // Validate response data
            if (!data || !data.data_promo || !Array.isArray(data.data_promo) || data.data_promo.length === 0) {
                throw new Error("Invalid response data format");
            }

            // Get the first promo item for basic info
            const promoData = data.data_promo[0];
            dataBarangEdit = promoData;
            const cabangPromo = promoData.kd_store ? promoData.kd_store.split(",").map(c => c.trim()) : [];
            fetchStoreDataEdit('dropdownCabangListEdit', true, cabangPromo);


            // Get form elements
            const editKodePromo = document.querySelector("#modalEditPromo input[name='kode_promo_edit']");
            const editSupplier = document.querySelector("#modalEditPromo input[name='supplier-edit']");
            const startEdit = document.getElementById("start-edit");
            const endEdit = document.getElementById("end-edit");
            const editKeterangan = document.querySelector("#modalEditPromo textarea[name='keterangan-edit']");
            const cabangTerpilihEdit = document.getElementById("cabangTerpilihEdit");
            const dropdownCabangButtonEdit = document.getElementById("dropdownCabangButtonEdit");
            const dropdownCabangListEdit = document.getElementById("dropdownCabangListEdit");
            const tbody = document.getElementById("tbody-barang-edit");
            // Check if all elements exist
            if (!editKodePromo || !editSupplier || !startEdit || !endEdit ||
                !editKeterangan || !cabangTerpilihEdit || !dropdownCabangButtonEdit || !tbody) {
                throw new Error("Required form elements not found");
            }
            cabangTerpilihEdit.innerHTML = "";
            if (cabangPromo.length > 0) {
                cabangPromo.forEach(kodeCabang => {
                    cabangTerpilihEdit.value = kodeCabang;
                });

                // Jika kamu juga ingin menandai checkbox di list dropdown
                const checkboxes = dropdownCabangListEdit.querySelectorAll("input[type='checkbox']");
                checkboxes.forEach(cb => {
                    cb.checked = cabangPromo.includes(cb.value);
                });
            }

            const statusEditSelect = document.getElementById("status_edit");
            const statusPenggunaanSelect = document.getElementById("status_edit_penggunaan");


            if (statusEditSelect && promoData.status) {
                const optionExists = Array.from(statusEditSelect.options).some(opt => opt.value === promoData.status);
                if (optionExists) {
                    statusEditSelect.value = promoData.status;
                } else {
                    console.warn("Option status tidak ditemukan di select");
                }
            }

            if (statusPenggunaanSelect && promoData.status_digunakan) {
                const optionExists = Array.from(statusPenggunaanSelect.options).some(opt => opt.value === promoData.status_digunakan);
                if (optionExists) {
                    statusPenggunaanSelect.value = promoData.status_digunakan;
                } else {
                    console.warn("Option status_digunakan tidak ditemukan di select");
                }
            }

            // Populate form fields with safe data access
            editKodePromo.value = promoData.kode_promo || '';
            editSupplier.value = promoData.nama_supplier || '';
            // Safely handle date splitting
            const tglMulai = promoData.tgl_mulai ? promoData.tgl_mulai.split(' ')[0] : '';
            const tglSelesai = promoData.tgl_selesai ? promoData.tgl_selesai.split(' ')[0] : '';
            startEdit.value = tglMulai;
            endEdit.value = tglSelesai;

            editKeterangan.value = promoData.keterangan || '';

            // Clear and populate items table
            tbody.innerHTML = '';

            // Add all promo items to the table
            data.data_promo.forEach(item => {
                if (!item) return;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td contenteditable="false" class="w-1/4 border border-pink-200 p-1 text-sm">${item.plu || ''}</td>
                    <td contenteditable="false" class="w-2/5 border border-pink-200 p-1 text-sm">${item.descp || ''}</td>
                    <td contenteditable="false" class="w-1/4 border border-pink-200 p-1 text-sm">${item.harga_jual || ''}</td>
                    <td contenteditable="${item.diskon == 0 ? 'false' : 'true'}" class="w-1/4 border border-pink-200 p-1 diskon text-sm" data-plu="${item.plu}">${item.diskon == 0 ? '' : item.diskon}</td>
                    <td contenteditable="${item.potongan_harga == 0 ? 'false' : 'true'}" class="w-1/4 border border-pink-200 p-1 potongan text-sm" data-plu="${item.plu}">${item.potongan_harga == 0 ? '' : item.potongan_harga}</td>
                    <td class="hidden">${kode_supplier || ''}</td>
                    <td class="hidden">${item.id_promo || ''}</td>
                `;
                tbody.appendChild(tr);

                // Add event listeners for diskon and potongan cells
                const diskonCell = tr.querySelector(".diskon");
                const potonganCell = tr.querySelector(".potongan");

                allDiskonCellsEdit.push(diskonCell);
                allPotonganCellsEdit.push(potonganCell);

                restrictToNumbers(diskonCell);
                restrictToNumbers(potonganCell);

                diskonCell.addEventListener('input', handleGlobalInputCheck);
                potonganCell.addEventListener('input', handleGlobalInputCheck);
            });

            // Show modal
            const modal = document.getElementById("modalEditPromo");
            const modalContent = document.getElementById("modalContentEdit");

            if (!modal || !modalContent) {
                throw new Error("Modal elements not found");
            }

            modal.classList.remove("hidden");

            // Trigger animation
            setTimeout(() => {
                modalContent.classList.remove("scale-95", "opacity-0");
                modalContent.classList.add("scale-100", "opacity-100");
            }, 10);
            document.getElementById('tbody-barang-edit').addEventListener('input', function (e) {
                if (e.target && e.target.classList.contains('diskon')) {
                    const plu = e.target.dataset.plu;
                    diskonCacheEdit[plu] = e.target.textContent.trim();
                }
                if (e.target && e.target.classList.contains('potongan')) {
                    const plu = e.target.dataset.plu;
                    potonganCacheEdit[plu] = e.target.textContent.trim();
                }
            });
            const inputSearchEdit = document.getElementById('search-barang-edit');
            inputSearchEdit.addEventListener("input", function () {
                const keyword = this.value.toLowerCase();
                const hasilFilter = data.data_promo.filter(b => {
                    return String(b.plu).toLowerCase().includes(keyword) || String(b.descp).toLowerCase().includes(keyword);
                })
                console.log(hasilFilter)
                renderTableDisOrPotEd(hasilFilter, 'tbody-barang-edit');
            })
        })
        .catch(error => {
            loadingTable.classList.add("hidden");
            Swal.fire({
                title: 'Error!',
                text: 'Gagal memuat data promo: ' + error.message,
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#ec4899'
            });
        });
}

function detailPromo(kode_promo, kode_supplier) {
    const modal = document.getElementById("modalDetailPromo");
    const modalContent = document.getElementById("modalContentDetail");
    const tbody = document.getElementById("tbody-detail-barang");

    // Tampilkan modal
    modal.classList.remove("hidden");

    // Kosongkan tbody sebelum isi ulang
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-gray-400 py-4">Loading...</td></tr>`;

    // Fetch data
    fetch(`/src/api/transaction/promo/get_edit_promo?kode_promo=${kode_promo}&kode_supplier=${kode_supplier}`)
        .then(res => res.json())
        .then(data => {
            // Kosongkan isi tabel setelah data masuk
            tbody.innerHTML = "";

            // Tangani jika datanya kosong
            if (!data || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center text-gray-400 py-4">Tidak ada data promo ditemukan.</td></tr>`;
                return;
            }
            // Loop data dan buat baris
            data.data_promo.forEach(item => {
                const row = document.createElement("tr");
                row.className = "hover:bg-rose-50 transition";

                row.innerHTML = `
          <td class="px-4 py-2 border border-rose-200 font-mono text-sm text-gray-700">${item.kode_promo}</td>
          <td class="px-4 py-2 border border-rose-200 font-mono text-sm text-gray-700">${item.plu}</td>
          <td class="px-4 py-2 border border-rose-200 text-gray-700">${item.descp}</td>
          <td class="px-4 py-2 border border-rose-200 text-right text-gray-700">Rp ${Number(item.harga_jual).toLocaleString()}</td>
          <td class="px-4 py-2 border border-rose-200 text-center text-gray-700">${item.diskon || 0}%</td>
          <td class="px-4 py-2 border border-rose-200 text-center text-gray-700">Rp ${Number(item.potongan_harga || 0).toLocaleString()}</td>
        `;
                tbody.appendChild(row);
            });
        })
        .catch(err => {
            console.error("Gagal ambil data promo:", err);
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-red-500 py-4">Terjadi kesalahan saat memuat data.</td></tr>`;
        });

    // Trigger animasi buka modal
    setTimeout(() => {
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 50);
}

function closeModal(modalId, modalBody) {
    const modal = document.getElementById(modalId);
    const modalContent = document.getElementById(modalBody);
    modalContent.classList.remove("scale-100", "opacity-100");
    modalContent.classList.add("scale-95", "opacity-0");
    setTimeout(() => {
        modal.classList.add("hidden");
    }, 300);
}
// Add this function after the state declarations
function checkAndUpdatePromoStatus() {
    const todayStr = new Date().toISOString().split('T')[0]; // contoh: "2025-05-19"

    promoData.forEach(promo => {
        const endDateStr = promo.tgl_selesai.split(' ')[0]; // ambil bagian tanggal saja

        if (todayStr > endDateStr && promo.status === 'aktif') {
            // Update status promo jadi nonaktif
            fetch('/src/api/transaction/promo/update_status_promo', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_promo: promo.id_promo,
                    status: 'nonaktif',
                    keterangan: promo.keterangan
                })
            })
                .then(res => res.json())
                .then(response => {
                    if (response.message === 'Berhasil mengubah data') {
                        promo.status = 'nonaktif';
                        filteredData = promoData.filter((item, index, self) =>
                            index === self.findIndex(t => t.kode_promo === item.kode_promo)
                        );
                        renderTable();
                    }
                })
                .catch(error => {
                    console.error('Error updating promo status:', error);
                });
        }
    });
}






