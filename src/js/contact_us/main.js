import { submitContactUs, getContactHistory, getConversation, sendMessage } from './fetch.js';

let currentContactId = null;
function mapStatusToText(status) {
    const statusMap = {
        'open': 'Dibuka',
        'in_progress': 'Diproses',
        'selesai': 'Selesai',
    };
    return statusMap[status] || status;
}
const showToast = (message, isSuccess = true) => {
    Toastify({
        text: message,
        duration: 3000,
        gravity: "top",
        position: "center",
        backgroundColor: isSuccess ? "#10B981" : "#EF4444",
    }).showToast();
};
const renderHistory = (historyItems) => {
    const container = document.getElementById('contact-history-container');
    const loader = document.getElementById('history-loader');
    if (loader) {
        loader.classList.add('hidden');
    }

    if (!historyItems || historyItems.length === 0) {
        container.innerHTML = `<div class="text-center py-8">
            <i class="fas fa-envelope-open-text text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-500">Anda belum pernah mengirim pesan.</p>
        </div>`;
        return;
    }

    container.innerHTML = historyItems.map(item => {
        const unreadBadge = item.unread_count > 0 ? `<div class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">${item.unread_count}</div>` : '';
        const statusColor = item.status === 'ditutup' ? 'bg-gray-100 text-gray-500' : 'bg-green-100 text-green-700';
        
        
        return `
            <div class="bg-white p-4 rounded-xl shadow-sm border hover:shadow-md transition-shadow cursor-pointer relative" 
                 data-item='${JSON.stringify(item).replace(/'/g, "&apos;")}'>
                ${unreadBadge}
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-semibold text-gray-800">${item.subject}</p>
                        <p class="text-xs text-gray-500">Dikirim: ${new Date(item.dikirim).toLocaleString('id-ID')}</p>
                    </div>
                    <span class="text-xs font-medium px-2 py-1 rounded-full ${statusColor}">${mapStatusToText(item.status)}</span>
                </div>
            </div>
        `;
    }).join('');

    container.querySelectorAll('[data-item]').forEach(item => {
        item.addEventListener('click', () => {
            
            openChatModal(JSON.parse(item.dataset.item.replace(/&apos;/g, "'")));
        });
    });
};

const renderConversation = (messages) => {
    const container = document.getElementById('chatConversationMessages');
    if (!messages || messages.length === 0) {
        container.innerHTML = `<div class="text-center text-gray-400 py-8"><i class="fas fa-comment-dots text-3xl mb-2"></i><p>Belum ada percakapan.</p></div>`;
        return;
    }
    container.innerHTML = messages.map(msg => {
        const isCustomer = msg.pengirim_type === 'customer';
        const align = isCustomer ? 'justify-end' : 'justify-start';
        const bubbleColor = isCustomer ? 'bg-pink-500 text-white' : 'bg-gray-200 text-gray-800';
        const time = new Date(msg.dibuat_tgl.replace(' ', 'T')).toLocaleString('id-ID', { hour: '2-digit', minute: '2-digit' });

        return `
            <div class="flex ${align} animate-fade-in-up">
                <div class="max-w-xs md:max-w-md">
                    <div class="${bubbleColor} rounded-lg px-3 py-2 shadow-sm">
                        <p class="text-sm whitespace-pre-wrap break-words">${msg.pesan}</p>
                    </div>
                    <p class="text-xs text-gray-400 mt-1 ${isCustomer ? 'text-right' : 'text-left'}">${time}</p>
                </div>
            </div>
        `;
    }).join('');
    scrollToBottom();
};


const scrollToBottom = () => {
    const container = document.getElementById('chatScrollContainer');
    if (container) {
        setTimeout(() => container.scrollTop = container.scrollHeight, 100);
    }
};
const openChatModal = async (item) => {
    currentContactId = item.id;
    
    
    document.getElementById('chatSubject').textContent = item.subject;
    
    
    
    document.getElementById('chatModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    document.getElementById('chatConversationMessages').innerHTML = `<div class="text-center py-8"><div class="w-6 h-6 border-2 border-pink-200 border-t-pink-500 rounded-full animate-spin mx-auto"></div></div>`;
    
    try {
        const result = await getConversation(item.id);
        if (result.success || result.status === 'success') {
            renderConversation(result.data);
            loadHistory(); 
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        showToast(error.message, false);
        closeChatModal();
    }
};



const closeChatModal = () => {
    document.getElementById('chatModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    currentContactId = null;
};

const handleFormSubmit = async (e) => {
    e.preventDefault();
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('contactUsForm');
    const formData = {
        subject: form.subject.value.trim(),
        message: form.message.value.trim()
    };
    if (!formData.subject || !formData.message) {
        showToast('Subjek dan Pesan tidak boleh kosong.', false);
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i><span class="ml-2">Mengirim...</span>`;

    try {
        const result = await submitContactUs(formData);
        if (result.success || result.status === 'success') {
            showToast('Pesan berhasil dikirim!');
            form.reset();
            loadHistory();
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        showToast(error.message, false);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = `<i class="fas fa-paper-plane"></i><span class="ml-2">Kirim Pesan</span>`;
    }
};

const handleSendMessage = async () => {
    const input = document.getElementById('chatMessageInput');
    const sendBtn = document.getElementById('sendChatMessageBtn');
    const message = input.value.trim();
    if (!message || !currentContactId) return;

    sendBtn.disabled = true;

    try {
        const result = await sendMessage(currentContactId, message);
        if (result.success || result.status === 'success') {
            input.value = '';
            const convResult = await getConversation(currentContactId);
            renderConversation(convResult.data);
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        showToast(error.message, false);
    } finally {
        sendBtn.disabled = false;
    }
};

const loadHistory = async () => {
    try {
        const result = await getContactHistory();
        if (result.status === 'success') {
            renderHistory(result.data);
        } else {
            throw new Error(result.message || 'Gagal memuat riwayat.');
        }
    } catch (error) {
        console.error("Error di loadHistory:", error); 
        document.getElementById('contact-history-container').innerHTML = `<div class="text-center py-8 text-red-500">${error.message}</div>`;
    }
};


document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('contactUsForm').addEventListener('submit', handleFormSubmit);
    document.getElementById('sendChatMessageBtn').addEventListener('click', handleSendMessage);
    document.getElementById('closeChatModal').addEventListener('click', closeChatModal);
    
    document.getElementById('chatMessageInput').addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleSendMessage();
        }
    });

    loadHistory();
});