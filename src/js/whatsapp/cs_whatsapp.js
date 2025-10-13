
const getToken = () => {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; token=`);
    if (parts.length === 2) return parts.pop().split(";").shift();
    return null;
}

const token = getToken();
let ws;
let currentConversationId = null;

document.addEventListener('DOMContentLoaded', () => {
    if (!token) {
        console.error("Token admin tidak ditemukan. Harap login kembali.");
        Swal.fire('Error', 'Token tidak ditemukan, harap login kembali.', 'error');
        return;
    }

    initWebSocket();
    fetchAndRenderConversations();

    const sendButton = document.getElementById('send-button');
    const messageInput = document.getElementById('message-input');

    sendButton.addEventListener('click', sendMessage);
    messageInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    
    messageInput.addEventListener('input', () => {
        messageInput.style.height = 'auto';
        messageInput.style.height = (messageInput.scrollHeight) + 'px';
    });
});

function initWebSocket() {
    
    ws = new WebSocket('wss://asokababystore.com/ws');

    ws.onopen = () => {
        console.log('WebSocket connection established.');
        
        
    };

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
                fetchAndRenderConversations();
                
                
                if (data.event === 'new_message' && data.conversation_id === currentConversationId) {
                    appendMessage({ pengirim: 'user', isi_pesan: data.message });
                }
            }
        } catch (e) {
            
            
            console.log('Received a non-JSON message, likely a welcome message:', event.data);
        }
    };

    ws.onclose = () => {
        console.log('WebSocket connection closed. Attempting to reconnect...');
        
        setTimeout(initWebSocket, 5000);
    };

    ws.onerror = (error) => {
        console.error('WebSocket error:', error);
    };
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
            listElement.innerHTML = '<p class="text-center text-gray-500 p-4">Tidak ada percakapan.</p>';
            return;
        }

        conversations.forEach(convo => {
            const item = document.createElement('div');
            item.className = 'conversation-item p-4 border-b border-gray-200 cursor-pointer hover:bg-gray-100';
            if (convo.id === currentConversationId) {
                item.classList.add('active');
            }
            
            item.innerHTML = `
                <div class="flex justify-between items-center">
                    <p class="font-semibold text-gray-800">${convo.nomor_telepon}</p>
                    ${convo.status_percakapan === 'live_chat' ? '<span class="px-2 py-0.5 text-xs font-semibold text-red-800 bg-red-200 rounded-full">Live</span>' : ''}
                </div>
                <p class="text-sm text-gray-500 truncate">Terakhir interaksi: ${new Date(convo.terakhir_interaksi_pada).toLocaleString('id-ID')}</p>
            `;
            item.addEventListener('click', () => selectConversation(convo.id, convo.nomor_telepon));
            listElement.appendChild(item);
        });

    } catch (error) {
        console.error(error);
        Swal.fire('Error', error.message, 'error');
    }
}

async function selectConversation(conversationId, phoneNumber) {
    currentConversationId = conversationId;

    
    document.getElementById('chat-placeholder').classList.add('hidden');
    document.getElementById('active-chat').classList.remove('hidden');
    document.getElementById('active-chat').classList.add('flex');

    
    document.getElementById('chat-with-phone').textContent = phoneNumber;
    
    
    document.querySelectorAll('.conversation-item').forEach(el => el.classList.remove('active'));
    document.querySelector(`div[onclick="selectConversation(${conversationId}, '${phoneNumber}')"]`)?.classList.add('active');


    const messageContainer = document.getElementById('message-container');
    messageContainer.innerHTML = '<p class="text-center text-gray-500">Memuat pesan...</p>';

    try {
        const response = await fetch(`/src/api/whatsapp/get_cs_data.php?conversation_id=${conversationId}`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });

        if (!response.ok) throw new Error('Gagal memuat riwayat pesan.');

        const messages = await response.json();
        renderMessages(messages);

    } catch (error) {
        console.error(error);
        messageContainer.innerHTML = `<p class="text-center text-red-500">${error.message}</p>`;
    }
}

function renderMessages(messages) {
    const messageContainer = document.getElementById('message-container');
    messageContainer.innerHTML = '';
    messages.forEach(msg => appendMessage(msg));
}

function appendMessage(msg) {
    const messageContainer = document.getElementById('message-container');
    const bubble = document.createElement('div');
    const isUser = msg.pengirim === 'user';
    
    bubble.className = `message-bubble p-3 rounded-lg ${isUser ? 'user-bubble' : 'admin-bubble'}`;
    bubble.textContent = msg.isi_pesan;
    
    messageContainer.appendChild(bubble);
    
    messageContainer.scrollTop = messageContainer.scrollHeight;
}

async function sendMessage() {
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    const message = messageInput.value.trim();

    if (!message || !currentConversationId) return;

    
    appendMessage({ pengirim: 'admin', isi_pesan: message });
    messageInput.value = '';
    messageInput.style.height = 'auto'; 

    sendButton.disabled = true;

    try {
        const response = await fetch('/src/api/whatsapp/send_admin_reply.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
                conversation_id: currentConversationId,
                message: message
            })
        });

        const result = await response.json();
        if (!result.success) {
            throw new Error('Gagal mengirim balasan.');
        }
        
        
        fetchAndRenderConversations();

    } catch (error) {
        console.error(error);
        Swal.fire('Error', error.message, 'error');
        
    } finally {
        sendButton.disabled = false;
    }
}