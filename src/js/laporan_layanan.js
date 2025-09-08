let dataTableInstance;

function fetchData() {
    const token = localStorage.getItem('token');
    fetch("/src/api/customer/laporan_layanan", {
        method: "GET",
        headers: { "Content-Type": "application/json", "Authorization": `Bearer ${token}` }
    }).then(res => res.json())
        .then(data => {
            renderLaporan(data)
            updateCountStatus(data)
        }).catch(error => {
            console.log("Message Error", error)
        })
}

function customizeDataTableLayout(tableId) {
    const $wrapper = $(`#${tableId}`).closest('.dataTables_wrapper');

    // Hanya tambahkan jika belum ada
    if ($wrapper.find('.dt-detail-top').length === 0) {
        const $length = $wrapper.find('.dataTables_length');
        const $search = $wrapper.find('.dataTables_filter');
        createTopWrapper($wrapper, $length, $search);
    }

    if ($wrapper.find('.dt-detail-bottom').length === 0) {
        const $info = $wrapper.find('.dataTables_info');
        const $paginate = $wrapper.find('.dataTables_paginate');
        createBottomWrapper($wrapper, $info, $paginate);
    }

    // Styling tetap dijalankan setiap draw
    styleInputElements($wrapper.find('.dataTables_length'), $wrapper.find('.dataTables_filter'));
    stylePaginationButtons();
}

function createTopWrapper($wrapper, $length, $search) {
    const $top = $('<div class="dt-detail-top flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4 mt-4 pl-3 pr-3"></div>');


    // Menambahkan komponen length dan search di bagian atas
    $top.append($length.addClass('order-1 font-medium text-sm'));
    $top.append($search.addClass('order-2 justify-self-end mt-2 md:mt-0'));

    $wrapper.prepend($top);
}

function createBottomWrapper($wrapper, $info, $paginate) {
    const $bottom = $('<div class="dt-detail-bottom flex justify-between items-center mt-4 pl-4 pr-4 text-sm"></div>');
    $bottom.append($info.addClass('text-gray-600'));
    $bottom.append($paginate.addClass('flex justify-end gap-2'));
    $wrapper.append($bottom);
}

function styleInputElements($length, $search) {
    // Styling input select dan search box
    $length.find('select').addClass('ml-2 px-2 py-1 border rounded-md focus:ring-2 focus:ring-blue-500');
    $search.find('input[type="search"]').addClass('ml-2 px-2 py-1 border rounded-md focus:ring-2 focus:ring-blue-500');
}

function stylePaginationButtons() {
    $('.dataTables_paginate a').addClass('px-3 py-1 border rounded-lg text-sm text-gray-700 hover:bg-pink-100 cursor-pointer p-4 mb-3');
    $('.dataTables_paginate .current')
        .removeClass('text-gray-700 hover:bg-pink-100')
        .addClass('bg-pink-500 text-white font-semibold border-pink-500');

    $('#tableLaporan tbody tr').addClass('text-sm md:text-base');
    $('#tableLaporan tbody td').addClass('px-2 py-2');
    $('#tableLaporan thead th').addClass('text-center');
}
function formatTanggal(tanggalStr) {
    if (!tanggalStr) return ''; // Cek null, undefined, atau string kosong
    const date = new Date(tanggalStr);
    if (isNaN(date.getTime())) return '-'; // Cek jika tanggal invalid
    const day = date.getDate();
    const month = date.getMonth() + 1;
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
}
// Render data laporan
function renderLaporan(data) {
    // Clear dulu datanya
    dataTableInstance.clear();

    // Loop dan masukkan ke DataTables
    data.data.forEach(laporan => {
        const dikirim = formatTanggal(laporan.dikirim);
        const no_hp = laporan.no_hp || '-';
        const nama_lengkap = laporan.nama_lengkap || '-';
        const subject = laporan.subject || '-';
        const status = laporan.status || 'open';
        const message = laporan.message || '-';
        const shortMessage = message ? message.substring(0, 50) + '...' : '-';
        const statusHTML = `
            <span class="px-3 py-1 rounded-full text-xs font-semibold ${getStatusClass(status)}">
                ${status.replace('_', ' ')}
            </span>`;

        const actionButton = `<span class="inline-block bg-blue-100 text-blue-700 text-md rounded-full font-semibold mr-2 px-2.5 py-0.5 cursor-pointer hover:bg-blue-300 hover:text-blue-900"
      onclick='openModal(${JSON.stringify(laporan)})'>
  Detail
</span>`;

        dataTableInstance.row.add([
            dikirim,
            `<span class="font-mono">${no_hp}</span>`,
            `<span class="font-semibold">${nama_lengkap}</span>`,
            subject,
            `<span class="max-w-lg truncate cursor-pointer" data-tippy-content="${message}">${shortMessage}</span>`,
            statusHTML,
            `<div class="text-center">${actionButton}</div>`
        ]);
    });

    // Apply perubahan

    dataTableInstance.on("draw", () => {
        tippy('[data-tippy-content]', {
            placement: 'top',
            animation: 'fade',
            duration: [200, 150],
            theme: 'light-border'
        });
        stylePaginationButtons()
    })
    dataTableInstance.order([]).draw();
}

// Fungsi status dengan kelas dinamis
function getStatusClass(status) {
    switch (status) {
        case 'selesai':
            return 'bg-green-100 text-green-700';
        case 'in_progress':
            return 'bg-yellow-100 text-yellow-700';
        default:
            return 'bg-red-100 text-red-700';
    }
}
function openModal(laporan) {
    // Isi modal dengan data laporan
    document.getElementById('modalKode').textContent = laporan.no_hp || '-';
    document.getElementById('modalNama').textContent = laporan.nama_lengkap || '-';
    document.getElementById('modalKategori').textContent = laporan.subject || '-';
    document.getElementById('modalDeskripsi').textContent = laporan.message || '-';
    document.getElementById('modalTanggal').textContent = formatTanggal(laporan.dikirim);
    document.getElementById('modalStatus').textContent = laporan.status || 'open';

    // Tombol Kirim dan Selesai
    const sendBtn = document.getElementById('send');  
    const selesaiBtn = document.getElementById('selesaiButton'); 

    // Sembunyikan kontrol jika tidak dalam status "in_progress"
    sendBtn.classList.remove('hidden');
    selesaiBtn.classList.add('hidden'); 

    // Jika status in_progress, sembunyikan tombol Kirim dan tampilkan tombol Selesai
    if (laporan.status === 'in_progress') {
        sendBtn.classList.add('hidden');  
        selesaiBtn.classList.remove('hidden');  
    }

    // Tampilkan modal detail
    document.getElementById('modalDetail').classList.remove('hidden');
}

// Fungsi untuk menandai laporan selesai
function selesaiLaporan() {
    const laporanId = document.getElementById('modalKode').textContent;
    fetch(`../../api/customer/laporan_layanan`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ kode: laporanId, status: 'selesai' })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            document.getElementById('modalStatus').textContent = 'done';
            document.getElementById('selesaiButton').classList.add('hidden');
        }
    });
}


function closeModal() {
    document.getElementById('modalDetail').classList.add('hidden');
}

function kirimWhatsApp() {
    const kategori = document.getElementById('modalKategori').innerText.trim();
    const nama = document.getElementById('modalNama').innerText.trim();
    const kode = document.getElementById('modalKode').innerText.trim();
    const deskripsi = document.getElementById('modalDeskripsi').innerText.trim();
    const balasPesan = document.getElementById("pesan_balasan").value;
    const nomor = document.getElementById("modalKode").innerText.trim();
    document.getElementById("send").disabled = false;
    const pesan = `Membalas pesan terkait *${kategori}* dari customer *${nama}* no hp *${kode}* pesannya: *${deskripsi}* \n \n ${balasPesan}`;
    const encoded = encodeURIComponent(pesan);
    window.open(`https://wa.me/+62${nomor}?text=${encoded}`, '_blank');
    // Update status to 'in progress'
    updateStatusInProgress(nomor);
}
function updateCountStatus(data) {
    const countOpen = data.data.filter(laporan => laporan.status === 'open').length;
    const countInProgress = data.data.filter(laporan => laporan.status === 'in_progress').length;
    const countSelesai = data.data.filter(laporan => laporan.status === 'selesai').length;

    // Update angka di elemen span
    document.getElementById('countOpen').textContent = countOpen;
    document.getElementById('countInProgress').textContent = countInProgress;
    document.getElementById('countSelesai').textContent = countSelesai;
}
function updateStatusInProgress(noHp) {
    const laporan = window.laporanTerpilih; // Pastikan laporan ini diset saat openModal
    fetch(`../../api/customer/laporan_layanan`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            kode: noHp,
            status: 'in_progress'
        })
    })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                document.getElementById('modalStatus').textContent = 'in_progress';
                const statusCell = document.querySelector(`[data-id="${laporan.id}"] .cell-status`);
                if (statusCell) statusCell.textContent = 'in_progress';
            }
        });
}
// Setup DataTable dengan custom styling
document.addEventListener('DOMContentLoaded', function () {
    dataTableInstance = $('#tabelLaporan').DataTable({
        responsive: true,
        dom: 'lfrtip',
        language: {
            search: '🔍 Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            paginate: {
                previous: '⬅️',
                next: '➡️'
            }
        },
        order: [[0, 'desc']],
        columnDefs: [
            {
                targets: 0, 
                type: 'date', 
                render: function (data, type, row) {
                    return data; 
                }
            },
            { orderable: false, targets: [6] } 
        ],
        initComplete: function () {
            customizeDataTableLayout('tabelLaporan');
        }
    });

    fetchData();
});
document.getElementById("toggle-sidebar").addEventListener("click", function () {
    document.getElementById("sidebar").classList.toggle("open");
});
document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("sidebar");
    const closeBtn = document.getElementById("closeSidebar");

    closeBtn.addEventListener("click", function () {
        sidebar.classList.remove("open"); // Hilangkan class .open agar sidebar tertutup
    });
});
document.getElementById("toggle-hide").addEventListener("click", function () {
    var sidebarTexts = document.querySelectorAll(".sidebar-text");
    let mainContent = document.getElementById("main-content");
    let sidebar = document.getElementById("sidebar");
    var toggleButton = document.getElementById("toggle-hide");
    var icon = toggleButton.querySelector("i");

    if (sidebar.classList.contains("w-64")) {
        // Sidebar mengecil
        sidebar.classList.remove("w-64", "px-5");
        sidebar.classList.add("w-16", "px-2");
        sidebarTexts.forEach((text) => text.classList.add("hidden")); // Sembunyikan teks
        mainContent.classList.remove("ml-64");
        mainContent.classList.add("ml-16"); // Main ikut mundur
        toggleButton.classList.add("left-20"); // Geser tombol lebih dekat
        toggleButton.classList.remove("left-64");
        icon.classList.remove("fa-angle-left"); // Ubah ikon
        icon.classList.add("fa-angle-right");
    } else {
        // Sidebar membesar
        sidebar.classList.remove("w-16", "px-2");
        sidebar.classList.add("w-64", "px-5");
        sidebarTexts.forEach((text) => text.classList.remove("hidden")); // Tampilkan teks kembali
        mainContent.classList.remove("ml-16");
        mainContent.classList.add("ml-64");
        toggleButton.classList.add("left-64"); // Geser tombol ke posisi awal
        toggleButton.classList.remove("left-20");
        icon.classList.remove("fa-angle-right"); // Ubah ikon
        icon.classList.add("fa-angle-left");
    }
});
document.addEventListener("DOMContentLoaded", function () {
    const profileImg = document.getElementById("profile-img");
    const profileCard = document.getElementById("profile-card");

    profileImg.addEventListener("click", function (event) {
        event.preventDefault();
        profileCard.classList.toggle("show");
    });

    // Tutup profile-card jika klik di luar
    document.addEventListener("click", function (event) {
        if (!profileCard.contains(event.target) && !profileImg.contains(event.target)) {
            profileCard.classList.remove("show");
        }
    });
});