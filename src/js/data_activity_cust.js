import { kodeCabang } from "./kode_cabang/kd.js";

let dataTable;
let dataTableDetail;
let selectedFiles = [];
function fetchData(range, cabang) {
    document.getElementById('loadingTable').style.display = 'flex';
    fetch(`../../api/customer/get_activity_customer?range=${range}&cabang=${cabang}`)
        .then(res => res.json())
        .then(data => {
            loadTable(data);
            document.getElementById('loadingTable').style.display = 'none';
        })
        .catch(error => {
            document.getElementById('loadingTable').style.display = 'none';
            console.log(error);
        })
}
function fetchDataRangeDate(range, selectId, cabang) {
    document.getElementById('loadingTable').style.display = 'flex';
    fetch(`../../api/customer/get_activity_customer?range=${selectId}&tanggal=${range}&cabang=${cabang}`)
        .then(res => res.json())
        .then(data => {
            loadTable(data);
            document.getElementById('loadingTable').style.display = 'none';
        })
        .catch(error => {
            document.getElementById('loadingTable').style.display = 'none';
            console.error('Error:', error)
        });
}
function fetchDetailsData(id, range, selectId, store) {
    document.getElementById('loadingTable').style.display = 'flex';
    fetch(`../../api/customer/get_activity_customer?range=${selectId}&tanggal=${range}&kd_cust=${id}&cabang=${store}`)
        .then(res => res.json())
        .then(details => {
            loadTableDetail(details);
            document.getElementById('loadingTable').style.display = 'none';
        })
        .catch(error => {
            document.getElementById('loadingTable').style.display = 'none';
            console.error('Error:', error)
        });
}
function loadTable(data) {
    const rows = data.data.map((cust, index) => {
        const fileIds = cust.folder ? cust.folder.split(",") : [];
        const statusCheck = cust.status_upload === null
            ? `<button class="btn-upload bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-xs md:px-4 md:py-2 " data-kd="${cust.kd_cust}">Upload</button>`
            : `<span class="text-green-600 font-medium text-xs">Checked ✅</span>`;
        const preview = fileIds && fileIds.length
            ? `<button class="btn-preview bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-md" 
            data-folder='${JSON.stringify(fileIds)}' data-hp = '${cust.kd_cust}' 
            title="Lihat Gambar">
            <i class="fas fa-images"></i>
        </button>`
            : "";

        return [
            index + 1,
            cust.kd_cust,
            cust.nama_cust,
            cust.total_poin_pk_pm,
            cust.poin_trans,
            cust.sisa_poin,
            `<span class="text-pink-600 font-semibold">${cust.T_Trans}</span>`,
            cust.store_alias_pk,
            '',
            statusCheck,
            preview,
            cust.store_kode
        ];
    });


    dataTable = $('#customerTable').DataTable({
        destroy: true,
        paging: true,
        retrieve: false,
        data: rows,
        dom: '<"top"lf>rt<"bottom"ip><"clear">',
        columns: [
            { title: "No" },
            { title: "No Hp" },
            { title: "Nama Pelanggan" },
            { title: "Total Poin" },
            { title: "Tukar Poin" },
            { title: "Sisa Poin" },
            { title: "Total Transaksi" },
            { title: "Cabang" },
            { title: "Action" },
            { title: "Periksa" },
            { title: "Lihat" },
            { title: "Kode" },
        ],
        createdRow: function (row) {
            $('td', row).each(function (colIndex) {
                // Tambahkan id dengan format: td-[kd_cust]-[namaKolom]
                const colNames = ["no", "kd_cust", "nama", "total_poin", "tukar_poin", "sisa_poin", "total_trans", "action"];
                $(this).attr('id', `td-${colNames[colIndex]}`);
            });
        },
        columnDefs: [
            {
                targets : 11,
                visible: false, 
                searchable: false 
            },
            {
                targets: 8,
                data: null,
                defaultContent: `
            <button id="details-poin" class="text-white bg-blue-500 hover:bg-blue-600 rounded-lg text-md md:text-xs md:px-4 md:py-2 cursor-pointer">
                Details
            </button>`
            },
            {
                targets: [3, 4, 5, 6, 9],
                className: 'text-center'
            }
        ],
        responsive: {
            details: {
                type: 'column',
                target: 'tr'
            }
        },
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            paginate: {
                first: "Awal",
                last: "Akhir",
                next: "›",
                previous: "‹"
            },
        },
        autoWidth: false,
        scrollX: false,
        initComplete: function () {
            customizeDataTableLayout("customerTable");
        },
        headerCallback: function (thead) {
            $(thead).find('th').each(function (index) {
                if (index === 3) {
                    $(this).addClass('th-total-poin');
                } else if (index === 4) {
                    $(this).addClass('th-tukar-poin');
                } else if (index === 5) {
                    $(this).addClass('th-sisa-poin');
                } else if (index === 6) {
                    $(this).addClass('th-transaksi');
                } else if (index === 9) {
                    $(this).addClass('th-periksa')
                }
            });
        },
        drawCallback: stylePaginationButtons,
    });

}

function loadTableDetail(details) {
    const rows = details.detail.map((cust, index) => ([
        index + 1,
        cust.kd_cust,
        cust.no_trans,
        cust.tanggal,
        cust.jam,
        cust.jumlah_point,
        `Rp ${cust.nominal.toLocaleString()}`,
        cust.kasir,
        cust.cabang,
        cust.keterangan_struk
    ]));

    dataTableDetail = $('#customerTableDetail').DataTable({
        destroy: true,
        retrieve: false,
        data: rows,
        columns: [
            { title: "No" },
            { title: "No Hp" },
            { title: "No Faktur" },
            { title: "Tanggal" },
            { title: "Jam" },
            { title: "Poin" },
            { title: "Total Belanja" },
            { title: "Kasir" },
            { title: "Cabang" },
            {
                title: "Item",
                render: (data, type, row) => {
                    if (row[9] === 'Detail') {
                        return `<a href="#" class="show-struk inline-block bg-green-300 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full" data-trans="${row[2]}" data-hp="${row[1]}" data-store="${row[11]}">Detail</a>`
                    } else if (row[9] === 'Manual') {
                        return '<span class="inline-block bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded-full"> Manul</span>';
                    } else {
                        return ''
                    }
                }
            }
        ],
        responsive: {
            details: {
                type: 'column',
                target: 'tr'
            }
        },
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            paginate: {
                first: "Awal",
                last: "Akhir",
                next: "›",
                previous: "‹"
            },
        },
        columnDefs: [
            {
                targets: [5, 6], className: 'text-center'
            }
        ],
        initComplete: function () {
            customizeDataTableLayout('customerTableDetail');
        },
        autoWidth: false,
        scrollX: false,
        drawCallback: function () {
            $('.dataTables_paginate a').addClass('px-3 py-1 border rounded-lg text-sm text-gray-700 hover:bg-pink-100 cursor-pointer');
            // Styling untuk table detail
            $('.dataTables_paginate .current')
                .removeClass('text-gray-700 hover:bg-pink-100')
                .addClass('bg-pink-500 text-white font-semibold border-pink-500');
            $('#customerTableDetail thead th').addClass('bg-pink-500 text-white');
            $('#customerTableDetail tbody td').addClass('text-sm text-gray-700 border-b');
            $('#customerTableDetail tbody tr').addClass('hover:bg-pink-50 transition-all duration-200');

        },
        headerCallback: function (thead) {
            $(thead).find('th').each(function (index) {
                if (index === 5) {
                    $(this).addClass('th-total-poin');
                } else if (index === 6) {
                    $(this).addClass('th-total-belanja');
                }
            });
        },
    });

}

$('#customerTableDetail').on('click', '.show-struk', function (e) {
    e.preventDefault();
    const noFaktur = $(this).data('trans');
    const kode = $(this).data('hp');
    fetch(`/src/api/customer/get_struk_belanja_customer?member=${kode}&kode=${noFaktur}`, {
        method: "GET",
        headers: { "Content-Type": "application/json" },
    })
        .then(res => res.json())
        .then(items => {
            let total = 0;
            let table = `
            <div class="rounded-lg overflow-hidden shadow border border-gray-200">
              <table class="w-full text-sm text-left">
                <thead class="bg-green-100 text-green-800 uppercase tracking-wide">
                  <tr>
                    <th class="px-4 py-2 text-center">No</th>
                    <th class="px-4 py-2">Nama Barang</th>
                    <th class="px-4 py-2 text-center">Qty</th>
                    <th class="px-4 py-2 text-center">Harga Normal</th>
                    <th class="px-4 py-2 text-center">Harga Promo</th>
                    <th class="px-4 py-2 text-center">Subtotal</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
          `;

            items.detail_transaction.forEach((item, i) => {
                const subtotal = item.qty * item.hrg_promo;
                total += subtotal;
                table += `
              <tr class="hover:bg-gray-50 transition duration-150 ease-in-out">
                <td class="px-4 py-2 text-center text-gray-600">${i + 1}</td>
                <td class="px-4 py-2 font-medium text-gray-700">${item.item}</td>
                <td class="px-4 py-2 text-center">${item.qty}</td>
                <td class="px-4 py-2 text-center text-gray-500">Rp${item.harga.toLocaleString()}</td>
                <td class="px-4 py-2 text-center text-green-600 font-semibold">Rp${item.hrg_promo.toLocaleString()}</td>
                <td class="px-4 py-2 text-center text-blue-600 font-semibold">Rp${subtotal.toLocaleString()}</td>
              </tr>
            `;
            });

            table += `
                </tbody>
              </table>
            </div>
            <div class="text-right text-base font-semibold mt-4 text-gray-800">
              Total: <span class="text-blue-700">Rp${total.toLocaleString()}</span>
            </div>
          `;
            // Menampilkan tabel di modal
            $('#strukContent').html(table);
        })
        .catch(error => console.error("Error fetching data:", error));
    $('#strukModal').removeClass('hidden').addClass('flex');
});

$('#strukModal').on('click', function (e) {
    if (e.target.id === 'strukModal' || e.target.id === 'closeModal1') {
        $(this).removeClass('flex').addClass('hidden');
    }
});

function customizeDataTableLayout(tableId) {
    const $wrapper = $(`#${tableId}`).closest('.dataTables_wrapper');
    const $length = $wrapper.find('.dataTables_length');
    const $search = $wrapper.find('.dataTables_filter');
    const $info = $wrapper.find('.dataTables_info');
    const $paginate = $wrapper.find('.dataTables_paginate');

    // Buat wrapper atas
    const $top = $('<div class="dt-detail-top grid grid-cols-1 md:grid-cols-2 gap-4 items-center mb-4"></div>');
    $top.append($length.addClass('order-1'));
    $top.append($search.addClass('order-3 justify-self-end'));
    $wrapper.prepend($top);

    // Buat wrapper bawah
    const $bottom = $('<div class="dt-detail-bottom grid grid-cols-1 md:grid-cols-2 items-center mt-4"></div>');
    $bottom.append($info.addClass('text-sm text-gray-600'));
    $bottom.append($paginate.addClass('flex justify-end gap-2'));
    $wrapper.append($bottom);

    // Style elemen-elemen input/select
    $length.find('select').addClass('ml-2 px-2 py-1 border rounded-lg');
    $search.find('input[type="search"]').addClass('ml-2 px-2 py-1 border rounded-lg');
}

function closeModal(btnId, modalId, contentId) {
    const closeModal = document.getElementById(btnId);
    const modalContent = document.getElementById(contentId);
    const modal = document.getElementById(modalId);
    closeModal.addEventListener("click", () => {
        gsap.to(modalContent, {
            opacity: 0,
            scale: 0.9,
            duration: 0.3,
            ease: "power2.in",
            onComplete: () => {
                modal.classList.add("hidden");
            },
        });
    });
}

function stylePaginationButtons() {
    $('.dataTables_paginate a').addClass('px-3 py-1 border rounded-lg text-sm text-gray-700 hover:bg-pink-100 cursor-pointer');
    $('.dataTables_paginate .current')
        .removeClass('text-gray-700 hover:bg-pink-100')
        .addClass('bg-pink-500 text-white font-semibold border-pink-500');

    $('#customerTable tbody tr').addClass('text-sm md:text-base');
    $('#customerTable tbody td').addClass('px-2 py-2');
    $('#customerTable thead th').addClass('text-center');
}

$(document).ready(function () {
    document.getElementById("kode").style.display = "none";

    // Fungsi untuk inisialisasi datepicker dan dropdown cabang
    async function initializeDatePicker(range) {
        // Hapus elemen datepicker dan dropdown cabang jika ada
        $('#searchContainer').html('');
        $('#kode').html('');

        // Menampilkan dropdown cabang untuk semua range
        document.getElementById("kode").style.display = "block";
        $('#kode').html(`
        <div class="flex flex-col gap-1 md:mr-4">
    <label for="selectCabang" class="text-sm text-gray-600 font-semibold flex items-center gap-2">
        <i class="fa fa-store text-pink-400"></i> Cabang
    </label>
    <select id="selectCabang" class="px-4 py-2 border border-pink-200 rounded-xl text-sm text-gray-700 shadow focus:outline-none focus:ring-2 focus:ring-pink-300 focus:border-pink-400 transition-all duration-200 bg-white/80">

    </select>
</div>
    `);
        const cabang = await kodeCabang('selectCabang');
        // Event listener untuk memilih cabang
        $('#selectCabang').on('change', function () {
            const selectedCabang = $(this).val();
            const selectedDate = $('#datepicker').val();
            const rangeSelected = $('#filterRange').val();

            if (selectedCabang && selectedDate) {
                fetchDataRangeDate(selectedDate, rangeSelected, selectedCabang);
            }
        });

        // Trigger otomatis jika sudah terisi
        $('#selectCabang').trigger('change');

        // Cek apakah range yang dipilih adalah 'week', 'month', atau 'day'
        const today = new Date();
        let yesterday = new Date();
        yesterday.setDate(yesterday.getDate() - 1);
        let maxDate = new Date(today);
        if (range === 'day' || range === 'week' || range === 'month') {
            // Tambahkan datepicker dengan batas tanggal
            $('#searchContainer').html('<input type="text" id="datepicker" class="px-4 py-2 border rounded-lg text-sm text-gray-600" autocomplete="off">');
            $('#datepicker').datepicker({
                maxDate: maxDate,
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                onSelect: function (selectedDate) {
                    const rangeSelected = $('#filterRange').val();
                    const selectedCabang = $('#selectCabang').val();
                    fetchDataRangeDate(selectedDate, rangeSelected, selectedCabang);
                },
                showButtonPanel: true
            });

            $('#datepicker').datepicker('setDate', yesterday);
        }
    }


    $('#customerTable tbody').on('click', 'button#details-poin', function () {
        const row = $(this).closest('tr');
        const kd_cust = row.find('td:eq(1)').text();
        const store = dataTable.row(row).data()[11];
        // Buka modal
        const modal = document.getElementById("modalTable");
        const modalContent = document.getElementById("modalContent");

        modal.classList.remove("hidden");

        gsap.fromTo(
            modalContent,
            { opacity: 0, scale: 0.9 },
            {
                opacity: 1,
                scale: 1,
                duration: 0.5,
                ease: "power2.out",
            }
        );
        const selectedDate = $("#datepicker").val();
        const selectTgl = $("#filterRange").val();
        // Panggil API detail
        fetchDetailsData(kd_cust, selectedDate, selectTgl, store);
    });

    $('#customerTable tbody').on('click', '.btn-upload', function (e) {
        e.stopPropagation();
        const row = $(this).closest('tr');
        const kd_cust = row.find('td:eq(1)').text();
        $('#uploadKdCust').val(kd_cust);
        $('#modalUpload').removeClass('hidden');
        gsap.fromTo('#modalUploadContent', { opacity: 0, scale: 0.9 }, { opacity: 1, scale: 1, duration: 0.3 });
    });

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-preview');
        if (!btn) return; // Cek dulu tombolnya ada

        const kd_cust = btn.getAttribute('data-hp') || '';
        $('#tambahFileKd').val(kd_cust);

        const folderAttr = btn.getAttribute('data-folder');
        if (!folderAttr || folderAttr === "null") {
            console.warn("Folder kosong atau null");
            return;
        }

        let fileIds;
        try {
            fileIds = JSON.parse(folderAttr);
        } catch (err) {
            console.error("Gagal parse data-folder:", err);
            return;
        }

        const container = document.getElementById('modalImageContainer');
        container.innerHTML = "";

        fileIds.forEach(fileId => {
            const img = document.createElement("img");
            img.src = `/preview?id=${fileId}`;
            img.className = "w-28 h-28 object-cover border rounded";
            img.addEventListener('click', () => {
                showLargeImage(fileId);
            });
            container.appendChild(img);
        });

        document.getElementById('imageModal').classList.remove('hidden');
    });


    function showLargeImage(fileId) {
        const modal = document.getElementById('largeImageModal'); // Modal kedua
        const img = document.getElementById('largeImage');
        img.src = `/preview?id=${fileId}`;
        modal.classList.remove('hidden');
    }

    // Tambahkan event untuk klik di luar gambar
    document.getElementById('largeImageModal').addEventListener('click', function (e) {
        const modal = document.getElementById('largeImageModal');
        const img = document.getElementById('largeImage');

        // Tutup hanya jika klik di luar gambar
        if (e.target === modal) {
            modal.classList.add('hidden');
            img.src = ''; // Kosongkan src untuk membebaskan memori
        }
    });
    document.getElementById('closeModal').addEventListener('click', () => {
        document.getElementById('imageModal').classList.add('hidden');
    });
    $('#cancelUpload').on('click', function () {
        $('#modalUpload').addClass('hidden');
        $('#uploadForm')[0].reset();
        $('#progressBar').css('width', '0%').text('0%');
        selectedFiles = [];
        document.getElementById('previewArea').innerHTML = '';
    });

    document.getElementById('uploadForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const overlay = document.getElementById('uploadOverlay');
        const kdCust = document.getElementById('uploadKdCust').value;

        if (selectedFiles.length === 0) {
            Toastify({
                text: "Pilih minimal 1 gambar.",
                duration: 1000,
                gravity: "top",
                position: "right",
                backgroundColor: "#f87171",
            }).showToast();
            return;
        }

        const formData = new FormData();
        formData.append('kd_cust', kdCust);
        selectedFiles.forEach(file => {
            formData.append('file_upload[]', file);
        });

        overlay.classList.remove('hidden');

        try {
            const res = await fetch('/upload_drive', {
                method: 'POST',
                body: formData
            });

            const result = await res.json();
            overlay.classList.add('hidden');

            if (res.status === 201 && result.success) {
                Toastify({
                    text: "Upload Berhasil",
                    duration: 1000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#22c55e",
                }).showToast();

                document.getElementById('modalUpload').classList.add('hidden');

                const row = [...document.querySelectorAll('#customerTable tbody tr')].find(r =>
                    r.children[1].textContent.trim() === kdCust
                );
                if (row) {
                    row.children[9].innerHTML = `<span class="text-green-600 font-medium text-xs">Checked ✅</span>`;
                }
                // Update tombol preview jika fileIds dikembalikan dari server
                if (result.data && result.data.length) {
                    const folderData = JSON.stringify(result.data.map(f => f.file_id));
                    const previewButton = `
                <button class="btn-preview bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-md" 
                    data-folder='${folderData}' data-hp='${kdCust.trim()}'
                    title="Lihat Gambar">
                    <i class="fas fa-images"></i>
                </button>`;
                    row.children[10].innerHTML = previewButton;
                }
                selectedFiles = [];
                document.getElementById('uploadForm').reset();
                document.getElementById('previewArea').innerHTML = '';
            } else {
                Toastify({
                    text: result.message || "Upload Gagal",
                    duration: 1000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#f87171",
                }).showToast();
            }
        } catch (err) {
            overlay.classList.add('hidden');
            Toastify({
                text: "Upload Gagal",
                duration: 1000,
                gravity: "top",
                position: "right",
                backgroundColor: "#f87171",
            }).showToast();
        }
    });
    document.getElementById('fileUpload').addEventListener('change', function () {
        const newFiles = Array.from(this.files);
        const previewArea = document.getElementById('previewArea');

        // Tambahkan file baru ke daftar jika belum mencapai 5
        for (const file of newFiles) {
            if (selectedFiles.length >= 5) {
                Toastify({
                    text: "Maksimal 5 gambar yang diperbolehkan.",
                    duration: 1500,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#facc15",
                }).showToast();
                break;
            }

            if (!file.type.startsWith('image/')) continue;

            // Cegah file duplikat berdasarkan nama
            const alreadyExists = selectedFiles.some(f => f.name === file.name && f.size === file.size);
            if (!alreadyExists) {
                selectedFiles.push(file);

                const fileItem = document.createElement('div');
                fileItem.className = "flex left-5 items-start text-sm text-gray-800";
                fileItem.innerHTML = `<i class="fas fa-image text-pink-500 mr-1"></i> ${file.name}`;
                previewArea.appendChild(fileItem);
            }
        }
        // Reset input agar bisa pilih file yang sama lagi nanti
        this.value = '';
    });

    document.getElementById('tambahFile').addEventListener('submit', async function (e) {
        e.preventDefault();
        const overlayT = document.getElementById('uploadOverlay+');
        overlayT.classList.remove('hidden');
        const fileUpload = document.querySelector('input[name="file_upload[]"]');
        if (fileUpload) fileUpload.remove();
        const overlay = document.getElementById('uploadOverlay');
        const kdCust = document.getElementById('tambahFileKd').value;
        if (selectedFiles.length === 0) {
            Toastify({
                text: "Pilih minimal 1 gambar.",
                duration: 1000,
                gravity: "top",
                position: "right",
                backgroundColor: "#f87171",
            }).showToast();
            return;
        }

        const formData = new FormData();
        formData.append('kd_cust', kdCust);
        selectedFiles.forEach(file => {
            formData.append('add_file[]', file);
        });

        overlay.classList.remove('hidden');

        try {
            const res = await fetch('/upload_drive', {
                method: 'POST',
                body: formData
            });

            const result = await res.json();
            overlay.classList.add('hidden');

            if (res.status === 201 && result.success) {
                Toastify({
                    text: "Upload Berhasil",
                    duration: 1000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#22c55e",
                }).showToast();

                document.getElementById('modalUpload').classList.add('hidden');

                const row = [...document.querySelectorAll('#customerTable tbody tr')].find(r =>
                    r.children[1].textContent.trim() === kdCust
                );
                // Update tombol preview jika fileIds dikembalikan dari server
                if (result.data && result.data.length) {
                    const folderData = JSON.stringify(result.data.map(f => f.file_id));
                    const previewButton = `
                <button class="btn-preview bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-md" 
                    data-folder='${folderData}' data-hp='${kdCust.trim()}'
                    title="Lihat Gambar">
                    <i class="fas fa-images"></i>
                </button>`;
                    row.children[10].innerHTML = previewButton;
                }
                selectedFiles = [];
                document.getElementById('tambahFile').reset();
                document.getElementById('previewAreaTambah').innerHTML = '';
                overlayT.classList.add('hidden');
            } else {
                overlayT.classList.add('hidden');
                Toastify({
                    text: result.message || "Upload Gagal",
                    duration: 1000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#f87171",
                }).showToast();
            }
        } catch (err) {
            overlayT.classList.add('hidden');
            Toastify({
                text: "Upload Gagal",
                duration: 1000,
                gravity: "top",
                position: "right",
                backgroundColor: "#f87171",
            }).showToast();
        }
    });

    document.getElementById('addFile').addEventListener('change', function () {
        const newFiles = Array.from(this.files);
        const previewArea = document.getElementById('previewAreaTambah');

        for (const file of newFiles) {
            if (selectedFiles.length >= 5) {
                Toastify({
                    text: "Maksimal 5 gambar yang diperbolehkan.",
                    duration: 1500,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#facc15",
                }).showToast();
                break;
            }

            if (!file.type.startsWith('image/')) continue;

            const alreadyExists = selectedFiles.some(f => f.name === file.name && f.size === file.size);
            if (!alreadyExists) {
                selectedFiles.push(file);

                const index = selectedFiles.length - 1;

                const fileItem = document.createElement('div');
                fileItem.className = "flex items-center justify-between bg-gray-100 px-3 py-2 rounded text-sm text-gray-800 mt-1 w-2xl";

                const fileName = document.createElement('span');
                fileName.innerHTML = `<i class="fas fa-image text-pink-500 mr-1"></i> ${file.name}`;
                fileName.className = "truncate";

                const deleteBtn = document.createElement('button');
                deleteBtn.innerHTML = '❌';
                deleteBtn.className = "ml-2 text-red-500 hover:text-red-700 text-sm";
                deleteBtn.addEventListener('click', () => {
                    selectedFiles.splice(index, 1);
                    fileItem.remove();
                });

                fileItem.appendChild(fileName);
                fileItem.appendChild(deleteBtn);
                previewArea.appendChild(fileItem);
            }
        }
        this.value = ''; // Reset input agar bisa pilih file yang sama lagi nanti
    });



    // Inisialisasi filter
    $('#filterRange').on('change', function () {
        const selectedRange = $(this).val();
        const selectedCabang = $("#selectCabang").val();
        initializeDatePicker(selectedRange);
        fetchData(selectedRange, selectedCabang);
    });

    // Inisialisasi dengan default filter 'month'
    fetchData('day', 'all');
    initializeDatePicker('day');

    closeModal("closeModal", "modalTable", "modalContent");
});



