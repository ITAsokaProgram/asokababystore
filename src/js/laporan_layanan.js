let dataTableInstance;
let laporanTerpilih = null; 
let currentChatContactId = null; 

function fetchData() {
    const token = localStorage.getItem('token');
    fetch("/src/api/customer/laporan_layanan", {
        method: "GET",
        headers: { "Content-Type": "application/json", "Authorization": `Bearer ${token}` }
    })
    .then(res => {
        if (!res.ok) {
            throw new Error('Gagal mengambil data dari server');
        }
        return res.json();
    })
    .then(responseObject => {
        
        if (responseObject && responseObject.data) {
            renderLaporan(responseObject.data); 
            updateCountStatus(responseObject.data); 
        } else {
            console.error("Format data tidak sesuai:", responseObject);
        }
    })
    .catch(error => {
        console.error("Message Error:", error);
        
        Swal.fire('Error', 'Gagal memuat data laporan: ' + error.message, 'error');
    });
}


function customizeDataTableLayout(tableId) {
    const $wrapper = $(`#${tableId}`).closest('.dataTables_wrapper');

    
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

    
    styleInputElements($wrapper.find('.dataTables_length'), $wrapper.find('.dataTables_filter'));
    stylePaginationButtons();
}

function createTopWrapper($wrapper, $length, $search) {
    const $top = $('<div class="dt-detail-top flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4 mt-4 pl-3 pr-3"></div>');


    
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
    if (!tanggalStr) return ''; 
    const date = new Date(tanggalStr);
    if (isNaN(date.getTime())) return '-'; 
    const day = date.getDate();
    const month = date.getMonth() + 1;
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
}
function renderLaporan(data) { 
    dataTableInstance.clear();

    
    data.forEach(laporan => {
        const dikirim = formatTanggal(laporan.dikirim);
        const no_hp = laporan.no_hp || '-';
        const nama_lengkap = laporan.nama_lengkap || '-';
        const subject = laporan.subject || '-';
        const status = laporan.status || 'open';
        const message = laporan.message || '-';
        const shortMessage = message.length > 50 ? message.substring(0, 50) + '...' : message;
        const statusHTML = `<span class="px-3 py-1 rounded-full text-xs font-semibold ${getStatusClass(status)}">${mapStatusToText(status)}</span>`;

        const detailButton = `<button class="btn-action" onclick='openModal(${JSON.stringify(laporan)})'>Detail</button>`;

        let chatButton = '';
        
        if (laporan.is_user_registered) {
            const unreadBadge = laporan.unread_count > 0 ? `<span class="absolute -top-1.5 -right-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white ring-2 ring-white">${laporan.unread_count}</span>` : '';
            chatButton = `
                <button 
                    onclick='openChatModal(${laporan.id}, ${JSON.stringify({nama: laporan.nama_lengkap, handphone: laporan.no_hp, subject: laporan.subject, message: laporan.message}).replace(/"/g, '&quot;')})'
                    class="relative btn-action bg-indigo-500 hover:bg-indigo-600"
                    title="Chat dengan Customer">
                    <i class="fas fa-comments"></i> Chat
                    ${unreadBadge}
                </button>
            `;
        }

        dataTableInstance.row.add([
            dikirim,
            `<span class="font-mono">${no_hp}</span>`,
            `<span class="font-semibold">${nama_lengkap}</span>`,
            subject,
            `<span class="max-w-lg truncate cursor-pointer" data-tippy-content="${message}">${shortMessage}</span>`,
            statusHTML,
            `<div class="flex items-center justify-center gap-2">${detailButton} ${chatButton}</div>`
        ]);
    });
    
    dataTableInstance.on("draw", () => {
        tippy('[data-tippy-content]');
        stylePaginationButtons();
    });
    dataTableInstance.order([]).draw();
}

function openChatModal(contactId, contactData) {
    currentChatContactId = contactId;
    document.getElementById('chatCustomerName').textContent = contactData.nama || '-';
    document.getElementById('chatCustomerPhone').textContent = contactData.handphone || '-';
    document.getElementById('chatCustomerSubject').textContent = contactData.subject || '-';
    document.getElementById('chatCustomerMessage').textContent = contactData.message || '-';
    
    document.getElementById('chatMessageInput').value = '';
    
    loadChatConversation(contactId);
    
    document.getElementById('chatModal').classList.remove('hidden');
}

function closeChatModal() {
    document.getElementById('chatModal').classList.add('hidden');
    currentChatContactId = null;
}


async function loadChatConversation(contactId) {
    const container = document.getElementById('chatConversationMessages');
    container.innerHTML = `<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-blue-500"></i></div>`;

    try {
        const token = localStorage.getItem('token');
        const response = await fetch(`/src/api/customer/contact_us/get_contact_us_conversation.php?contact_us_id=${contactId}`, {
             headers: { "Authorization": `Bearer ${token}` }
        });
        const result = await response.json();

        if (result.success) {
            renderChatConversation(result.data);
            fetchData(); 
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        container.innerHTML = `<div class="text-center text-red-500 py-8">${error.message}</div>`;
    }
}


function renderChatConversation(messages) {
    const container = document.getElementById('chatConversationMessages');
    if (!messages || messages.length === 0) {
        container.innerHTML = `<div class="text-center text-gray-400 py-8"><p>Belum ada percakapan.</p></div>`;
        return;
    }
    container.innerHTML = messages.map(msg => {
        const isAdmin = msg.pengirim_type === 'admin';
        const align = isAdmin ? 'justify-end' : 'justify-start';
        const bubbleColor = isAdmin ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800';
        const time = new Date(msg.dibuat_tgl).toLocaleString('id-ID', { hour: '2-digit', minute: '2-digit' });

        return `
            <div class="flex ${align}">
                <div class="max-w-xs md:max-w-md">
                    <div class="${bubbleColor} rounded-lg px-3 py-2 shadow-sm">
                        <p class="text-sm whitespace-pre-wrap break-words">${msg.pesan}</p>
                    </div>
                    <p class="text-xs text-gray-400 mt-1 ${isAdmin ? 'text-right' : 'text-left'}">${time}</p>
                </div>
            </div>
        `;
    }).join('');
    
    
    const scrollContainer = document.getElementById('chatScrollContainer');
    if(scrollContainer) {
        scrollContainer.scrollTop = scrollContainer.scrollHeight;
    }
}


async function sendChatMessage() {
    const input = document.getElementById('chatMessageInput');
    const message = input.value.trim();
    if (!message || !currentChatContactId) return;

    const sendBtn = document.getElementById('sendChatMessageBtn');
    sendBtn.disabled = true;
    sendBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i>`;

    try {
        const token = localStorage.getItem('token');
        const response = await fetch(`/src/api/customer/contact_us/send_contact_us_message.php`, {
            method: 'POST',
            headers: { 
                "Content-Type": "application/json",
                "Authorization": `Bearer ${token}`
            },
            body: JSON.stringify({
                contact_us_id: currentChatContactId,
                pesan: message
            })
        });
        const result = await response.json();

        if (result.success) {
            input.value = '';
            loadChatConversation(currentChatContactId); 
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        Swal.fire('Error', error.message, 'error');
    } finally {
        sendBtn.disabled = false;
        sendBtn.innerHTML = `<i class="fas fa-paper-plane mr-2"></i> Kirim`;
    }
}


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
function mapStatusToText(status) {
    const statusMap = {
        'open': 'Dibuka',
        'in_progress': 'Diproses',
        'selesai': 'Selesai'
    };
    return statusMap[status] || status;
}

function openModal(laporan) {
    laporanTerpilih = laporan; 

    
    document.getElementById('modalKode').textContent = laporan.no_hp || '-';
    document.getElementById('modalNama').textContent = laporan.nama_lengkap || '-';
    document.getElementById('modalKategori').textContent = laporan.subject || '-';
    document.getElementById('modalDeskripsi').textContent = laporan.message || '-';
    document.getElementById('modalTanggal').textContent = formatTanggal(laporan.dikirim);
    document.getElementById('modalStatus').textContent = laporan.status || 'open';
    document.getElementById('modalEmail').textContent = laporan.email || '-';
    document.getElementById('pesan_balasan').value = ''; 

    
    const kirimBtn = document.getElementById('kirimEmailButton');
    const selesaiBtn = document.getElementById('selesaiButton');
    const balasanTextarea = document.getElementById('pesan_balasan');

    
    if (laporan.status === 'selesai') {
        kirimBtn.classList.add('hidden');
        selesaiBtn.classList.add('hidden');
        balasanTextarea.disabled = true;
    } else if (laporan.status === 'in_progress') {
        kirimBtn.classList.add('hidden');
        selesaiBtn.classList.remove('hidden');
        balasanTextarea.disabled = false;
    } else { 
        kirimBtn.classList.remove('hidden');
        selesaiBtn.classList.add('hidden');
        balasanTextarea.disabled = false;
    }

    
    document.getElementById('modalDetail').classList.remove('hidden');
}

function selesaiLaporan() {
    if (!laporanTerpilih) return;

    fetch(`/src/api/customer/laporan_layanan.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            kode: laporanTerpilih.no_hp, 
            status: 'selesai' 
        })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            Swal.fire('Berhasil', 'Laporan telah ditandai sebagai selesai.', 'success');
            closeModal();
            fetchData();
        } else {
            Swal.fire('Gagal', 'Gagal memperbarui status laporan.', 'error');
        }
    }).catch(err => console.error(err));
}


function closeModal() {
    document.getElementById('modalDetail').classList.add('hidden');
    laporanTerpilih = null; 
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
    
    updateStatusInProgress(nomor);
}
function updateCountStatus(data) { 
    const countOpen = data.filter(laporan => laporan.status === 'open').length;
    const countInProgress = data.filter(laporan => laporan.status === 'in_progress').length;
    const countSelesai = data.filter(laporan => laporan.status === 'selesai').length;

    document.getElementById('countOpen').textContent = countOpen;
    document.getElementById('countInProgress').textContent = countInProgress;
    document.getElementById('countSelesai').textContent = countSelesai;
}

function updateStatusInProgress(noHp) {
    const laporan = window.laporanTerpilih; 
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
    document.getElementById('closeChatModal').addEventListener('click', closeChatModal);
    document.getElementById('sendChatMessageBtn').addEventListener('click', sendChatMessage);
    document.getElementById('chatMessageInput').addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendChatMessage();
        }
    });

});
document.getElementById("toggle-sidebar").addEventListener("click", function () {
    document.getElementById("sidebar").classList.toggle("open");
});
document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("sidebar");
    const closeBtn = document.getElementById("closeSidebar");

    closeBtn.addEventListener("click", function () {
        sidebar.classList.remove("open"); 
    });
});
document.getElementById("toggle-hide").addEventListener("click", function () {
    var sidebarTexts = document.querySelectorAll(".sidebar-text");
    let mainContent = document.getElementById("main-content");
    let sidebar = document.getElementById("sidebar");
    var toggleButton = document.getElementById("toggle-hide");
    var icon = toggleButton.querySelector("i");

    if (sidebar.classList.contains("w-64")) {
        
        sidebar.classList.remove("w-64", "px-5");
        sidebar.classList.add("w-16", "px-2");
        sidebarTexts.forEach((text) => text.classList.add("hidden")); 
        mainContent.classList.remove("ml-64");
        mainContent.classList.add("ml-16"); 
        toggleButton.classList.add("left-20"); 
        toggleButton.classList.remove("left-64");
        icon.classList.remove("fa-angle-left"); 
        icon.classList.add("fa-angle-right");
    } else {
        
        sidebar.classList.remove("w-16", "px-2");
        sidebar.classList.add("w-64", "px-5");
        sidebarTexts.forEach((text) => text.classList.remove("hidden")); 
        mainContent.classList.remove("ml-16");
        mainContent.classList.add("ml-64");
        toggleButton.classList.add("left-64"); 
        toggleButton.classList.remove("left-20");
        icon.classList.remove("fa-angle-right"); 
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

    
    document.addEventListener("click", function (event) {
        if (!profileCard.contains(event.target) && !profileImg.contains(event.target)) {
            profileCard.classList.remove("show");
        }
    });
});
async function kirimBalasanEmail() {
    if (!laporanTerpilih) return;

    const balasan = document.getElementById("pesan_balasan").value.trim();
    if (balasan === "") {
        Swal.fire('Peringatan', 'Mohon isi pesan balasan terlebih dahulu.', 'warning');
        return;
    }

    const button = document.getElementById('kirimEmailButton');
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';

    let responseText = ''; 
    try {
        const response = await fetch('/src/api/customer/laporan_layanan.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                kode: laporanTerpilih.no_hp,
                status: 'in_progress',
                balasan: balasan,
                email: laporanTerpilih.email,
                nama: laporanTerpilih.nama_lengkap,
                subject: laporanTerpilih.subject,
                laporan_awal: laporanTerpilih.message
            }),
        });
        
        responseText = await response.text(); 
        const result = JSON.parse(responseText); 

        if (response.ok && result.success) {
            await Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: result.message,
                timer: 2000,
                showConfirmButton: false
            });
            closeModal();
            fetchData();
        } else {
            throw new Error(result.message || 'Gagal mengirim balasan.');
        }

    } catch (error) {
        console.error('Error:', error);
        
        if (error instanceof SyntaxError) {
             console.error("Respons Server (bukan JSON):", responseText);
             await Swal.fire('Error Kritis', 'Server memberikan respons yang tidak valid. Cek console untuk detail.', 'error');
        } else {
             
             await Swal.fire('Oops...', error.message, 'error');
        }
    } finally {
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Balasan';
    }
}

