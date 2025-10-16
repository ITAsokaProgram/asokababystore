let selectedMediaFile = null; 

const getToken = () => {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; admin_token=`);
    if (parts.length === 2) return parts.pop().split(";").shift();
    return null;
}

const token = getToken();
let ws;
let currentConversationId = null;
let currentConversationStatus = null;

document.addEventListener('DOMContentLoaded', () => {
    if (!token) {
        console.error("Token admin tidak ditemukan. Harap login kembali.");
        Swal.fire('Error', 'Token tidak ditemukan, harap login kembali.', 'error');
        return;
    }

    const mobileShowList = document.getElementById('mobile-show-list');
    const mobileCloseList = document.getElementById('mobile-close-list');
    const mobileBackButton = document.getElementById('mobile-back-button');
    const conversationListContainer = document.getElementById('conversation-list-container');
    const activeChat = document.getElementById('active-chat');
    const chatPlaceholder = document.getElementById('chat-placeholder');
    const mediaInput = document.getElementById('media-input');
    const mediaPreviewContainer = document.getElementById('media-preview-container');
    const mediaPreviewImage = document.getElementById('media-preview-image');
    const mediaPreviewVideo = document.getElementById('media-preview-video');
    const removeMediaButton = document.getElementById('remove-media-button');

    mediaInput.addEventListener('change', () => {
        const file = mediaInput.files[0];
        if (!file) return;
        
        selectedMediaFile = file;
        const fileURL = URL.createObjectURL(file);

        mediaPreviewImage.classList.add('hidden');
        mediaPreviewVideo.classList.add('hidden');

        if (file.type.startsWith('image/')) {
            mediaPreviewImage.src = fileURL;
            mediaPreviewImage.classList.remove('hidden');
        } else if (file.type.startsWith('video/')) {
            mediaPreviewVideo.src = fileURL;
            mediaPreviewVideo.classList.remove('hidden');
        }
        
        mediaPreviewContainer.classList.remove('hidden');
    });

    removeMediaButton.addEventListener('click', () => {
        mediaInput.value = ''; 
        selectedMediaFile = null;
        mediaPreviewContainer.classList.add('hidden');
        mediaPreviewImage.src = '';
        mediaPreviewVideo.src = '';
    });
    
 

    if (mobileCloseList) {
        mobileCloseList.addEventListener('click', () => {
            conversationListContainer.classList.remove('mobile-show');
            mobileCloseList.classList.add('hidden');
        });
    }
    
    if (mobileBackButton) {
        mobileBackButton.addEventListener('click', () => {
            console.log('Mobile back button clicked');
            if (window.innerWidth <= 768) {
                activeChat.classList.add('hidden');
                chatPlaceholder.classList.remove('hidden');
                conversationListContainer.classList.add('mobile-show');
            }
        });
    }

    const toggleButton = document.getElementById('toggle-conversation-list');
    const chatLayout = document.getElementById('chat-layout');

    if (toggleButton) {
        const isCollapsed = localStorage.getItem('conversationListCollapsed') === 'true';
        if (isCollapsed) {
            conversationListContainer.classList.add('collapsed');
            chatLayout.classList.add('list-collapsed');
        }

        toggleButton.addEventListener('click', () => {
            const isCurrentlyCollapsed = conversationListContainer.classList.contains('collapsed');
            
            if (isCurrentlyCollapsed) {
                conversationListContainer.classList.remove('collapsed');
                chatLayout.classList.remove('list-collapsed');
                localStorage.setItem('conversationListCollapsed', 'false');
            } else {
                conversationListContainer.classList.add('collapsed');
                chatLayout.classList.add('list-collapsed');
                localStorage.setItem('conversationListCollapsed', 'true');
            }
        });
    }



    initWebSocket();
    fetchAndRenderConversations();

    const sendButton = document.getElementById('send-button');
    const messageInput = document.getElementById('message-input');
    const endChatButton = document.getElementById('end-chat-button');

    sendButton.addEventListener('click', sendMessage);
    messageInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    
    endChatButton.addEventListener('click', endConversation);
    
    messageInput.addEventListener('input', () => {
        messageInput.style.height = 'auto';
        messageInput.style.height = Math.min(messageInput.scrollHeight, 120) + 'px';
    });
});

function initWebSocket() {
    ws = new WebSocket('wss://asokababystore.com/ws');

    ws.onopen = () => {
        // console.log('WebSocket connection established.')
    }

    ws.onmessage = (event) => {
        console.log('WebSocket message received:', event.data);
        try {
            const data = JSON.parse(event.data);

            if (data.event === 'new_live_chat' || data.event === 'new_message') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: data.event === 'new_live_chat' ? `Live chat baru dari ${data.phone}` : `Pesan baru dari ${data.phone}`,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            }

            if (data.event === 'new_live_chat') {
                fetchAndRenderConversations();
                
                if (data.conversation_id) {
                    selectConversation(data.conversation_id);
                }

            } else if (data.event === 'new_message') {
                fetchAndRenderConversations();

                if (data.conversation_id === currentConversationId) {
                    appendMessage(data.message);
                }
            }


        } catch (e) {
            // console.log('Received a non-JSON message, likely a welcome message:', event.data);
        }
    };

    ws.onclose = () => {
        console.log('WebSocket connection closed. Attempting to reconnect...');
        setTimeout(initWebSocket, 5000);
    };

    ws.onerror = (error) => console.error('WebSocket error:', error);
}

async function fetchAndRenderConversations() {
    try {
        const response = await fetch('/src/api/whatsapp/get_cs_data.php', {
            headers: { 'Authorization': `Bearer ${token}` }
        });

        if (!response.ok) throw new Error('Gagal mengambil data percakapan.');
        
        const conversations = await response.json();
        const listElement = document.getElementById('conversation-list');
        listElement.innerHTML = ''; 

        if (conversations.length === 0) {
            listElement.innerHTML = '<div class="p-8 text-center text-gray-500"><i class="fas fa-inbox text-4xl mb-3 opacity-50"></i><p class="text-sm">Tidak ada percakapan aktif</p></div>';
            return;
        }

        conversations.forEach(convo => {
            const item = document.createElement('div');
            item.className = 'conversation-item p-3 md:p-4 border-b border-gray-200 cursor-pointer hover:bg-gray-100 transition-all duration-200';
            if (convo.id === currentConversationId) {
                item.classList.add('active');
            }
            
            const lastInteraction = new Date(convo.terakhir_interaksi_pada);
            const timeAgo = getTimeAgo(lastInteraction);
            
            item.innerHTML = `
                <div class="flex justify-between items-start mb-1">
                    <div class="flex items-center gap-2 flex-1 min-w-0">
                        <i class="fas fa-user-circle text-gray-400 text-lg flex-shrink-0"></i>
                        <p class="font-semibold text-gray-800 text-sm md:text-base truncate">${convo.nomor_telepon}</p>
                    </div>
                    ${convo.status_percakapan === 'live_chat' ? '<span class="live-badge px-2 py-0.5 text-xs font-semibold text-red-800 bg-red-200 rounded-full flex-shrink-0">Live</span>' : ''}
                </div>
                <p class="text-xs text-gray-500 ml-7">${timeAgo}</p>
            `;
            item.addEventListener('click', () => {
                selectConversation(convo.id);
                // Close mobile list view when conversation selected
                if (window.innerWidth <= 768) {
                    document.getElementById('conversation-list-container').classList.remove('mobile-show');
                }
            });
            listElement.appendChild(item);
        });

    } catch (error) {
        console.error(error);
        const listElement = document.getElementById('conversation-list');
        listElement.innerHTML = '<div class="p-8 text-center text-red-500"><i class="fas fa-exclamation-circle text-3xl mb-2"></i><p class="text-sm">Gagal memuat percakapan</p></div>';
        Swal.fire('Error', error.message, 'error');
    }
}

function getTimeAgo(date) {
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return 'Baru saja';
    if (diffMins < 60) return `${diffMins} menit lalu`;
    if (diffHours < 24) return `${diffHours} jam lalu`;
    if (diffDays < 7) return `${diffDays} hari lalu`;
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}

async function selectConversation(conversationId) {
    currentConversationId = conversationId;

    document.getElementById('chat-placeholder').classList.add('hidden');
    document.getElementById('active-chat').classList.remove('hidden');
    document.getElementById('active-chat').classList.add('flex');
    
    const messageContainer = document.getElementById('message-container');
    messageContainer.innerHTML = '<div class="flex items-center justify-center h-full"><div class="loading-spinner" style="border-color: #cbd5e1; border-top-color: #3b82f6; width: 40px; height: 40px;"></div></div>';
    updateChatUI(null);

    try {
        const response = await fetch(`/src/api/whatsapp/get_cs_data.php?conversation_id=${conversationId}`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });

        if (!response.ok) throw new Error('Gagal memuat riwayat pesan.');

        const data = await response.json();
        const { details, messages } = data;
        
        currentConversationStatus = details.status_percakapan;
        document.getElementById('chat-with-phone').textContent = details.nomor_telepon;
        
        renderMessages(messages);
        updateChatUI(currentConversationStatus);
        fetchAndRenderConversations();

    } catch (error) {
        console.error(error);
        messageContainer.innerHTML = `<div class="flex items-center justify-center h-full text-center p-4"><div><i class="fas fa-exclamation-circle text-red-500 text-3xl mb-2"></i><p class="text-red-500 text-sm">${error.message}</p></div></div>`;
        updateChatUI(null);
    }
}

function updateChatUI(status) {
    const endChatButton = document.getElementById('end-chat-button');
    const messageInputArea = document.getElementById('message-input-area');

    if (status === 'live_chat') {
        endChatButton.classList.remove('hidden');
        messageInputArea.classList.remove('hidden');
    } else {
        endChatButton.classList.add('hidden');
        messageInputArea.classList.add('hidden');
    }
}

function renderMessages(messages) {
    const messageContainer = document.getElementById('message-container');
    messageContainer.innerHTML = '';
    
    if (messages.length === 0) {
        messageContainer.innerHTML = '<div class="no-message-placeholder flex items-center justify-center h-full text-center text-gray-400"><div><i class="fas fa-comment-slash text-4xl mb-2"></i><p class="text-sm">Belum ada pesan</p></div></div>';
        return;
    }
    
    messages.forEach(msg => appendMessage(msg));
    messageContainer.scrollTop = messageContainer.scrollHeight;
}
function appendMessage(msg) {
    const messageContainer = document.getElementById('message-container');
    const placeholder = messageContainer.querySelector('.no-message-placeholder');
    if (placeholder) {
        placeholder.remove();
    }

    // --- LOGIKA PENAMBAHAN PEMISAH TANGGAL ---
    const lastBubble = messageContainer.querySelector('.message-bubble:last-child');
    const lastTimestamp = lastBubble ? lastBubble.dataset.timestamp : null;

    let needsSeparator = false;
    if (!lastTimestamp) {
        needsSeparator = true; // Selalu tambahkan untuk pesan pertama
    } else {
        const lastDate = new Date(lastTimestamp).toDateString();
        const newDate = new Date(msg.timestamp).toDateString();
        if (newDate !== lastDate) {
            needsSeparator = true;
        }
    }

    if (needsSeparator) {
        const separator = document.createElement('div');
        separator.className = 'date-separator';
        separator.textContent = formatDateSeparator(msg.timestamp);
        messageContainer.appendChild(separator);
    }

    const bubble = document.createElement('div');
    const isUser = msg.pengirim === 'user';
    
    bubble.className = `message-bubble ${isUser ? 'user-bubble' : 'admin-bubble'}`;
    bubble.dataset.timestamp = msg.timestamp; 

    const messageType = msg.tipe_pesan || 'text';
    let contentHTML = '';

    switch (messageType) {
        case 'image':
            bubble.classList.add('media-bubble');
            contentHTML = `
                <div class="message-content media-content">
                    <a href="${msg.isi_pesan}" target="_blank" rel="noopener noreferrer">
                        <img src="${msg.isi_pesan}" alt="Gambar" class="media-item">
                    </a>
                </div>`;
            break;
        case 'video':
            bubble.classList.add('media-bubble');
            contentHTML = `
                <div class="message-content media-content">
                    <video src="${msg.isi_pesan}" controls class="media-item"></video>
                </div>`;
            break;
        case 'audio':
            contentHTML = `
                <div class="message-content audio-content">
                    <audio src="${msg.isi_pesan}" controls class="audio-player"></audio>
                </div>`;
            break;
        default:
            const p = document.createElement('p');
            p.style.whiteSpace = 'pre-wrap';
            p.style.marginBottom = '0';
            p.appendChild(document.createTextNode(msg.isi_pesan));
            contentHTML = `<div class="message-content text-content">${p.outerHTML}</div>`;
            break;
    }

    bubble.innerHTML = `
        ${contentHTML}
        <span class="message-time">${formatTimestamp(msg.timestamp)}</span>
    `;
    
    messageContainer.appendChild(bubble);
    
    requestAnimationFrame(() => {
        messageContainer.scrollTop = messageContainer.scrollHeight;
    });
}
async function sendMessage() {
    if (currentConversationStatus !== 'live_chat') {
        Swal.fire({
            icon: 'info',
            title: 'Tidak dapat mengirim',
            text: 'Anda hanya bisa mengirim pesan pada percakapan live chat.',
            confirmButtonColor: '#3b82f6'
        });
        return;
    }

    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    const message = messageInput.value.trim();
    
    // Validasi: harus ada pesan teks atau file media
    if (!message && !selectedMediaFile) return;
    if (!currentConversationId) return;

    // Tampilkan pesan/media di UI secara langsung
    if (selectedMediaFile) {
        const fileURL = URL.createObjectURL(selectedMediaFile);
        const mediaType = selectedMediaFile.type.startsWith('image/') ? 'image' : 'video';
        appendMessage({
            pengirim: 'admin',
            isi_pesan: fileURL, // Gunakan URL lokal untuk pratinjau
            tipe_pesan: mediaType,
            timestamp: new Date().toISOString()
        });
        if (message) { // Jika ada caption, tampilkan juga sebagai pesan terpisah (opsional) atau gabungkan
             appendMessage({ pengirim: 'admin', isi_pesan: message, tipe_pesan: 'text', timestamp: new Date().toISOString() });
        }
    } else if (message) {
        appendMessage({
            pengirim: 'admin',
            isi_pesan: message,
            tipe_pesan: 'text',
            timestamp: new Date().toISOString()
        });
    }

    sendButton.disabled = true;
    sendButton.innerHTML = '<div class="loading-spinner"></div>';

    // Persiapkan data untuk dikirim
    const formData = new FormData();
    formData.append('conversation_id', currentConversationId);
    if (message) {
        formData.append('message', message);
    }
    if (selectedMediaFile) {
        formData.append('media', selectedMediaFile);
    }

    // Reset input setelah data disiapkan
    messageInput.value = '';
    messageInput.style.height = 'auto';
    document.getElementById('remove-media-button').click(); // Panggil klik untuk mereset UI media

    try {
        const response = await fetch('/src/api/whatsapp/send_admin_reply.php', {
            method: 'POST',
            headers: {
                // Hapus 'Content-Type', browser akan set otomatis untuk FormData
                'Authorization': `Bearer ${token}`
            },
            body: formData
        });

        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal mengirim balasan.');
        }

        // Muat ulang daftar percakapan untuk update 'terakhir interaksi'
        fetchAndRenderConversations();

    } catch (error) {
        console.error(error);
        Swal.fire('Error', error.message, 'error');
    // TODO: Tambahkan logika untuk menandai pesan yang gagal terkirim di UI
    } finally {
        sendButton.disabled = false;
        sendButton.innerHTML = '<i class="fas fa-paper-plane"></i>';
    }
}

async function endConversation() {
    if (!currentConversationId) return;

    const confirmation = await Swal.fire({
        title: 'Akhiri Percakapan?',
        text: "Anda yakin ingin mengakhiri sesi live chat ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, akhiri!',
        cancelButtonText: 'Batal'
    });

    if (confirmation.isConfirmed) {
        try {
            const response = await fetch('/src/api/whatsapp/end_conversation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ conversation_id: currentConversationId })
            });

            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Gagal mengakhiri percakapan.');
            }
            
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Percakapan telah diakhiri.',
                confirmButtonColor: '#10b981'
            });
            
            currentConversationStatus = 'open';
            updateChatUI(currentConversationStatus);
            fetchAndRenderConversations();

        } catch (error) {
            console.error(error);
            Swal.fire('Error', error.message, 'error');
        }
    }
}


function formatTimestamp(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    const hours = date.getHours().toString().padStart(2, '0');
    const minutes = date.getMinutes().toString().padStart(2, '0');
    return `${hours}:${minutes}`;
}

function formatDateSeparator(dateString) {
    const date = new Date(dateString);
    const today = new Date();
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);

    const options = {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    };

    if (date.toDateString() === today.toDateString()) {
        return 'Hari ini';
    }
    if (date.toDateString() === yesterday.toDateString()) {
        return 'Kemarin';
    }
    return date.toLocaleDateString('id-ID', options);
}
