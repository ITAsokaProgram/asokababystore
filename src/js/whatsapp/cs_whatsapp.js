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
let currentFilter = 'semua';

document.addEventListener('DOMContentLoaded', () => {
    if (window.innerWidth <= 768) {
        document.getElementById('conversation-list-container').classList.add('mobile-show');
    }
    if (!token) {
        console.error("Token admin tidak ditemukan. Harap login kembali.");
        Swal.fire('Error', 'Token tidak ditemukan, harap login kembali.', 'error');
        return;
    }

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

    const mobileListToggle = document.getElementById('mobile-list-toggle');
    if (mobileListToggle) {
        mobileListToggle.addEventListener('click', () => {
            conversationListContainer.classList.toggle('mobile-show');
        });
    }

    const mobileCloseListButton = document.getElementById('mobile-close-list');
        if (mobileCloseListButton) {
            mobileCloseListButton.addEventListener('click', () => {
            conversationListContainer.classList.remove('mobile-show');
            });
        }   
    
    if (mobileBackButton) {
        mobileBackButton.addEventListener('click', () => {
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
        const isCollapsed = sessionStorage.getItem('conversationListCollapsed') === 'true';
        if (isCollapsed) {
            conversationListContainer.classList.add('collapsed');
            chatLayout.classList.add('list-collapsed');
        }
        
        toggleButton.addEventListener('click', () => {
            const isCurrentlyCollapsed = conversationListContainer.classList.contains('collapsed');
            
            if (isCurrentlyCollapsed) {
                conversationListContainer.classList.remove('collapsed');
                chatLayout.classList.remove('list-collapsed');
                sessionStorage.setItem('conversationListCollapsed', 'false');
            } else {
                conversationListContainer.classList.add('collapsed');
                chatLayout.classList.add('list-collapsed');
                sessionStorage.setItem('conversationListCollapsed', 'true');
            }
        });
    }

    const filterButtonsContainer = document.getElementById('status-filter-buttons');
    if (filterButtonsContainer) {
        filterButtonsContainer.addEventListener('click', (e) => {
            const button = e.target.closest('.filter-button');
            if (!button) return;

            currentFilter = button.dataset.filter;
            
            filterButtonsContainer.querySelectorAll('.filter-button').forEach(btn => {
                btn.classList.remove('active', 'bg-blue-500', 'text-white', 'shadow-sm');
                btn.classList.add('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
            });
            
            button.classList.add('active', 'bg-blue-500', 'text-white', 'shadow-sm');
            button.classList.remove('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');

            fetchAndRenderConversations();
        });
    }

    initWebSocket();
    fetchAndRenderConversations(true);

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
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' || e.key === 'Esc') { 
            e.preventDefault(); 
            clearActiveConversation();
        }
    });
});
function initWebSocket() {
    ws = new WebSocket('wss://asokababystore.com/ws');

    ws.onopen = () => {
        console.log('WebSocket connected');
    }

    ws.onmessage = (event) => {
        try {
            const data = JSON.parse(event.data);

            if ((data.event === 'new_live_chat' || data.event === 'new_message') && data.conversation_id !== currentConversationId) {
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
                
                if (data.total_unread_count !== undefined) {
                    updateTotalUnreadBadge(data.total_unread_count);
                }

            } else if (data.event === 'new_message') {
                
                if (data.conversation_id === currentConversationId) {
                    appendMessage(data.message);
                    
                    fetch(`/src/api/whatsapp/get_cs_data.php?conversation_id=${data.conversation_id}`, {
                        headers: { 'Authorization': `Bearer ${token}` }
                    }).catch(err => console.error("Gagal menandai pesan sebagai terbaca:", err));

                } else {
                    fetchAndRenderConversations();
                    
                    if (data.total_unread_count !== undefined) {
                        updateTotalUnreadBadge(data.total_unread_count);
                    }
                }
            
            } else if (data.event === 'unread_count_update') {
                updateTotalUnreadBadge(data.total_unread_count);
                fetchAndRenderConversations(); 
            }

        } catch (e) {
        }
    };

    ws.onclose = () => {
        console.log('WebSocket closed. Reconnecting...');
        setTimeout(initWebSocket, 5000);
    };

    ws.onerror = (error) => console.error('WebSocket error:', error);
}
async function fetchAndRenderConversations(isInitialLoad = false) {
    try {
        const response = await fetch(`/src/api/whatsapp/get_cs_data.php?filter=${currentFilter}`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });

        if (!response.ok) throw new Error('Gagal mengambil data percakapan.');
        
        // Modifikasi 1: Dapatkan data sebagai objek
        const data = await response.json();
        // Modifikasi 2: Ambil array percakapan dari objek
        const conversations = data.conversations; 
        
        const listElement = document.getElementById('conversation-list');
        listElement.innerHTML = ''; 

        // Modifikasi 3: Perbarui filter badge DAN total badge
        if (data.unread_counts) {
            updateFilterUnreadBadges(data.unread_counts);
            
            // Hitung total dari data count yang baru (lebih akurat)
            const totalUnread = (data.unread_counts.live_chat || 0) + (data.unread_counts.umum || 0);
            updateTotalUnreadBadge(totalUnread);
        }
        
        // Modifikasi 4: Hapus blok 'if (isInitialLoad)' yang lama karena sudah digantikan oleh logika di atas

        if (conversations.length === 0) {
            listElement.innerHTML = `
                <div class="flex flex-col items-center justify-center p-12 text-gray-400 text-center">
                    <i class="fas fa-inbox text-5xl mb-4 opacity-40"></i>
                    <p class="text-sm font-medium">Tidak ada percakapan</p>
                    <p class="text-xs mt-1 opacity-75">Percakapan baru akan muncul di sini</p>
                </div>`;
            return;
        }

        conversations.forEach(convo => {
            const item = document.createElement('div');
            item.className = 'conversation-item p-4 border-b border-gray-100 cursor-pointer hover:bg-blue-50 transition-all duration-200 relative';
            if (convo.id === currentConversationId) {
                item.classList.add('active', 'bg-blue-50');
            }
            
            const lastInteraction = new Date(convo.terakhir_interaksi_pada);
            const timeAgo = getTimeAgo(lastInteraction);
            
            item.innerHTML = `
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold shadow-sm">
                        <i class="fas fa-user text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start mb-1">
                            <p class="font-semibold text-gray-900 text-sm truncate pr-2">${convo.nomor_telepon}</p>
                            ${convo.status_percakapan === 'live_chat' ? 
                                '<span class="live-badge px-2 py-0.5 text-xs font-semibold text-red-700 bg-red-100 rounded-full flex-shrink-0 shadow-sm">Live</span>' : 
                                ''}
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <p class="text-xs text-gray-500">${timeAgo}</p>
                            ${convo.jumlah_belum_terbaca > 0 ? 
                                `<span class="unread-badge bg-blue-500 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center shadow-md">
                                    ${convo.jumlah_belum_terbaca}
                                </span>` : 
                                ''}
                        </div>
                    </div>
                </div>
            `;
            
            item.addEventListener('click', () => {
                selectConversation(convo.id);
                
                if (window.innerWidth <= 768) {
                    const conversationListContainer = document.getElementById('conversation-list-container');
                    conversationListContainer.classList.remove('mobile-show');
                }
            });
            listElement.appendChild(item);
        });

    } catch (error) {
        console.error(error);
        const listElement = document.getElementById('conversation-list');
        listElement.innerHTML = `
            <div class="flex flex-col items-center justify-center p-12 text-red-500">
                <i class="fas fa-exclamation-circle text-4xl mb-3"></i>
                <p class="text-sm font-medium">Gagal memuat percakapan</p>
                <button onclick="fetchAndRenderConversations()" class="mt-3 px-4 py-2 bg-blue-500 text-white text-xs rounded-lg hover:bg-blue-600 transition-colors">
                    <i class="fas fa-sync-alt mr-1"></i> Coba Lagi
                </button>
            </div>`;
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
    document.getElementById('chat-header').classList.add('show'); 

    document.getElementById('active-chat').classList.remove('hidden');
    document.getElementById('active-chat').classList.add('flex');
    
    const messageContainer = document.getElementById('message-container');
    messageContainer.innerHTML = `
        <div class="flex items-center justify-center h-full">
            <div class="text-center">
                <div class="loading-spinner mx-auto" style="border-color: #e0e7ff; border-top-color: #3b82f6; width: 40px; height: 40px;"></div>
                <p class="mt-3 text-sm text-gray-500">Memuat pesan...</p>
            </div>
        </div>`;
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
        document.getElementById('chat-with-name').textContent = details.nama_profil ?? '-';
        
        renderMessages(messages);
        updateChatUI(currentConversationStatus);
        fetchAndRenderConversations();

    } catch (error) {
        console.error(error);
        messageContainer.innerHTML = `
            <div class="flex items-center justify-center h-full text-center p-4">
                <div>
                    <i class="fas fa-exclamation-circle text-red-400 text-4xl mb-3"></i>
                    <p class="text-red-500 text-sm font-medium">${error.message}</p>
                    <button onclick="selectConversation(${conversationId})" class="mt-3 px-4 py-2 bg-blue-500 text-white text-xs rounded-lg hover:bg-blue-600 transition-colors">
                        <i class="fas fa-sync-alt mr-1"></i> Coba Lagi
                    </button>
                </div>
            </div>`;
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

function clearActiveConversation() {
    if (!currentConversationId) {
        return;
    }

    currentConversationId = null;
    currentConversationStatus = null;

    const activeChat = document.getElementById('active-chat');
    const chatPlaceholder = document.getElementById('chat-placeholder');
    const chatHeader = document.getElementById('chat-header');
    const chatWithPhone = document.getElementById('chat-with-phone');

    activeChat.classList.add('hidden');
    activeChat.classList.remove('flex');
    
    if (window.innerWidth <= 768) {
        document.getElementById('conversation-list-container').classList.add('mobile-show');
        chatPlaceholder.classList.add('hidden');
    } else {
        chatPlaceholder.classList.remove('hidden');
    }

    chatHeader.classList.remove('show');
    chatWithPhone.textContent = ''; 

    updateChatUI(null);

    const activeItem = document.querySelector('.conversation-item.active');
    if (activeItem) {
        activeItem.classList.remove('active', 'bg-blue-50');
    }
}

function renderMessages(messages) {
    const messageContainer = document.getElementById('message-container');
    messageContainer.innerHTML = '';
    
    if (messages.length === 0) {
        messageContainer.innerHTML = `
            <div class="no-message-placeholder flex items-center justify-center h-full text-center text-gray-400">
                <div>
                    <i class="fas fa-comment-dots text-5xl mb-3 opacity-30"></i>
                    <p class="text-sm font-medium">Belum ada pesan</p>
                    <p class="text-xs mt-1 opacity-75">Mulai percakapan dengan mengirim pesan</p>
                </div>
            </div>`;
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

    const lastBubble = messageContainer.querySelector('.message-bubble:last-child');
    const lastTimestamp = lastBubble ? lastBubble.dataset.timestamp : null;

    let needsSeparator = false;
    if (!lastTimestamp) {
        needsSeparator = true;
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
    
    if (!message && !selectedMediaFile) return;
    if (!currentConversationId) return;

    if (selectedMediaFile) {
        const fileURL = URL.createObjectURL(selectedMediaFile);
        const mediaType = selectedMediaFile.type.startsWith('image/') ? 'image' : 'video';
        appendMessage({
            pengirim: 'admin',
            isi_pesan: fileURL,
            tipe_pesan: mediaType,
            timestamp: new Date().toISOString(),
            status_baca: 0 
        });
        if (message) {
             appendMessage({ 
                 pengirim: 'admin', 
                 isi_pesan: message, 
                 tipe_pesan: 'text', 
                 timestamp: new Date().toISOString(), 
                 status_baca: 0 
             });
        }
    } else if (message) {
        appendMessage({
            pengirim: 'admin',
            isi_pesan: message,
            tipe_pesan: 'text',
            timestamp: new Date().toISOString(),
            status_baca: 0
        });
    }

    sendButton.disabled = true;
    sendButton.innerHTML = '<div class="loading-spinner"></div>';

    const formData = new FormData();
    formData.append('conversation_id', currentConversationId);
    if (message) {
        formData.append('message', message);
    }
    if (selectedMediaFile) {
        formData.append('media', selectedMediaFile);
    }

    messageInput.value = '';
    messageInput.style.height = 'auto';
    document.getElementById('remove-media-button').click();

    try {
        const response = await fetch('/src/api/whatsapp/send_admin_reply.php', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`
            },
            body: formData
        });

        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal mengirim balasan.');
        }

        fetchAndRenderConversations();

    } catch (error) {
        console.error(error);
        Swal.fire('Error', error.message, 'error');
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
function updateTotalUnreadBadge(count) {
    const badge = document.getElementById('total-unread-badge');
    const title = document.querySelector('title');
    
    if (!badge) return;

    if (count > 0) {
        badge.textContent = count;
        badge.classList.remove('hidden');
        title.textContent = `(${count}) Dashboard CS WhatsApp`;
    } else {
        badge.textContent = '0';
        badge.classList.add('hidden');
        title.textContent = 'Dashboard CS WhatsApp';
    }
}
function updateFilterUnreadBadges(counts) {
    const liveChatBadge = document.getElementById('unread-live_chat');
    const umumBadge = document.getElementById('unread-umum');
    const allBadge = document.getElementById('unread-all'); 

    const total = (counts.live_chat || 0) + (counts.umum || 0);

    if (allBadge) {
        if (total > 0) {
            allBadge.textContent = total;
            allBadge.classList.remove('hidden');
        } else {
            allBadge.textContent = '0';
            allBadge.classList.add('hidden');
        }
    }

    if (liveChatBadge) {
        if (counts.live_chat > 0) {
            liveChatBadge.textContent = counts.live_chat;
            liveChatBadge.classList.remove('hidden');
        } else {
            liveChatBadge.textContent = '0';
            liveChatBadge.classList.add('hidden');
        }
    }

    if (umumBadge) {
        if (counts.umum > 0) {
            umumBadge.textContent = counts.umum;
            umumBadge.classList.remove('hidden');
        } else {
            umumBadge.textContent = '0';
            umumBadge.classList.add('hidden');
        }
    }
}