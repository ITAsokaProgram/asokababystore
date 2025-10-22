function renderActiveChatLabels(labels) {
    const container = document.getElementById('active-chat-labels');
    if (!container) return;
    container.innerHTML = renderLabelTags(labels, 'sm');
}

function renderLabelTags(labels, size = 'xs') {
    if (!labels || labels.length === 0) return '';
    const sizeClasses = size === 'xs' 
        ? 'text-[10px] px-1.5 py-0.5' 
        : 'text-xs px-2 py-0.5';
    return labels.map(label => {
        const brightness = getBrightness(label.warna);
        const textColor = brightness > 128 ? '#000000' : '#FFFFFF';
        return `<span class="label-tag inline-block ${sizeClasses} font-medium rounded-full" style="background-color: ${label.warna}; color: ${textColor}; line-height: 1.2;">
                    ${label.nama_label}
                </span>`;
    }).join(' ');
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

function updateChatUI(status) {
    const endChatButton = document.getElementById('end-chat-button');
    const messageInputArea = document.getElementById('message-input-area');
    const manageLabelsButton = document.getElementById('manage-labels-button');

    if (status === 'live_chat') {
        endChatButton.classList.remove('hidden');
        messageInputArea.classList.remove('hidden');
        manageLabelsButton.classList.remove('hidden');
    } else {
        endChatButton.classList.add('hidden');
        messageInputArea.classList.add('hidden');
        if (status) {
            manageLabelsButton.classList.remove('hidden');
        } else {
            manageLabelsButton.classList.add('hidden');
        }
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
    document.getElementById('edit-display-name-button').classList.add('hidden');
    document.getElementById('manage-labels-button').classList.add('hidden');
    currentDisplayName = null;
    currentConversationLabels = [];
    document.getElementById('active-chat-labels').innerHTML = '';
    updateChatUI(null);

    const activeItem = document.querySelector('.conversation-item.active');
    if (activeItem) {
        activeItem.classList.remove('active', 'bg-blue-50');
    }
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