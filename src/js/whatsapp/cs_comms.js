function initWebSocket() {
    ws = new WebSocket('wss://asokababystore.com/ws');

    ws.onopen = () => {
        console.log('WebSocket connected');
    }

    ws.onmessage = (event) => {
        try {
            if (typeof event.data === 'string' && (event.data.startsWith('{') || event.data.startsWith('['))) {
                
                const data = JSON.parse(event.data);
                
                const currentSwal = Swal.getPopup();
                const isModalVisible = currentSwal && currentSwal.style.display !== 'none' && !currentSwal.classList.contains('swal2-toast');
                
                if ((data.event === 'new_live_chat' || data.event === 'new_message') && 
                    data.conversation_id !== currentConversationId && 
                    !isModalVisible) {
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
                            headers: { 'Authorization': `Bearer ${wa_token}` }
                        }).catch(err => console.error("Gagal menandai pesan sebagai terbaca:", err));
                    } else {
                        if (data.total_unread_count !== undefined) {
                            updateTotalUnreadBadge(data.total_unread_count);
                        }
                        fetchAndUpdateBadges();
                        const listElement = document.getElementById('conversation-list');
                        let existingItem = listElement.querySelector(`.conversation-item[data-id="${data.conversation_id}"]`);
                        
                        if (existingItem) {
                            
                            let unreadBadge = existingItem.querySelector('.unread-badge');
                            if (!unreadBadge) {
                                unreadBadge = document.createElement('span');
                                unreadBadge.className = 'unread-badge bg-blue-500 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center shadow-md';
                                const timeElement = existingItem.querySelector('.text-xs.text-gray-500');
                                if (timeElement && timeElement.parentElement) {
                                    timeElement.parentElement.appendChild(unreadBadge);
                                }
                            }
                            let currentItemCount = parseInt(unreadBadge.textContent) || 0;
                            unreadBadge.textContent = currentItemCount + 1;
                            
                            const timeElement = existingItem.querySelector('.text-xs.text-gray-500');
                            if (timeElement) {
                                timeElement.textContent = 'Baru saja';
                            }
                            
                            if (currentConvoPage === 1 && currentFilter === 'semua' && currentSearchTerm === '') {
                                listElement.prepend(existingItem);
                            }
                        } else {
                            if (currentConvoPage === 1 && currentSearchTerm === '') {
                                fetchAndRenderConversations();
                            }
                        }
                    }
                

                } else if (data.event === 'new_admin_reply') {
                    
                    if (data.conversation_id === currentConversationId) {
                        appendMessage(data.message);
                    }
                    
                    const listElement = document.getElementById('conversation-list');
                    let existingItem = listElement.querySelector(`.conversation-item[data-id="${data.conversation_id}"]`);
                    
                    if (existingItem) {
                        const timeElement = existingItem.querySelector('.text-xs.text-gray-500');
                        if (timeElement) {
                            timeElement.textContent = 'Baru saja'; 
                        }
                        
                        if (currentConvoPage === 1 && (currentFilter === 'semua' || currentFilter === 'live_chat') && currentSearchTerm === '') {
                            listElement.prepend(existingItem);
                        }
                    } else {
                        if (currentConvoPage === 1 && (currentFilter === 'semua' || currentFilter === 'live_chat') && currentSearchTerm === '') {
                            fetchAndRenderConversations(); 
                        }
                    }

                } else if (data.event === 'unread_count_update') {
                    updateTotalUnreadBadge(data.total_unread_count);
                    
                    if (data.unread_counts) {
                        updateFilterUnreadBadges(data.unread_counts);
                    }
                }
            } else {
                console.log("WebSocket received non-JSON message:", event.data);
            }
        } catch (e) {
            console.error("WebSocket onmessage error:", e, "Data:", event.data);
        }
    };

    ws.onclose = () => {
        console.log('WebSocket closed. Reconnecting...');
        setTimeout(initWebSocket, 5000);
    };

    ws.onerror = (error) => console.error('WebSocket error:', error);
}

async function fetchAndRenderConversations(isLoadMore = false) {
    try {
        const params = new URLSearchParams({
            filter: currentFilter,
            search: currentSearchTerm,
            page: currentConvoPage
        });
        const response = await fetch(`/src/api/whatsapp/get_cs_data.php?${params.toString()}`, {
            headers: { 'Authorization': `Bearer ${wa_token}` }
        });
        if (!response.ok) throw new Error('Gagal mengambil data percakapan.');
        const data = await response.json();
        const conversations = data.conversations;
        const listElement = document.getElementById('conversation-list');
        if (!isLoadMore) {
            listElement.innerHTML = '';
        }

        if (data.unread_counts) {
            updateFilterUnreadBadges(data.unread_counts);
            const totalUnread = (data.unread_counts.live_chat || 0) + (data.unread_counts.umum || 0);
            updateTotalUnreadBadge(totalUnread);
        }

        if (data.pagination) {
            currentConvoPage = data.pagination.current_page;
            hasMoreConvos = data.pagination.has_more;
        }

        if (conversations.length === 0 && !isLoadMore) {
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
            
            item.dataset.id = convo.id;

            if (convo.id === currentConversationId) {
                item.classList.add('active', 'bg-blue-50');
            }
            const lastInteraction = new Date(convo.urutan_interaksi);
            const timeAgo = getTimeAgo(lastInteraction);

            item.innerHTML = `
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold shadow-sm">
                        <i class="fas fa-user text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0 space-y-0.5">
                        <div class="flex justify-between items-start mb-1">
                            <p class="font-semibold text-gray-900 text-sm truncate pr-2">${convo.nama_display || convo.nomor_telepon}</p>
                            ${convo.status_percakapan === 'live_chat' ? 
                                '<span class="live-badge px-2 py-0.5 text-xs font-semibold text-red-700 bg-red-100 rounded-full flex-shrink-0 shadow-sm">Live</span>' : 
                                ''}
                        </div>
                        <div class="flex flex-wrap gap-1 mb-1.5 min-h-[14px]">
                            ${renderLabelTags(convo.labels, 'xs')}
                        </div>
                        <div class="flex justify-between items-center">
                            <p class="conversation-time-ago text-xs text-gray-500" data-timestamp="${convo.urutan_interaksi}">${timeAgo}</p>
                            ${convo.jumlah_belum_terbaca > 0 ? 
                                `<span class="unread-badge bg-blue-500 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center shadow-md">
                                    ${convo.jumlah_belum_terbaca}
                                </span>` : 
                                ''}
                        </div>
                    </div>
                </div>
            `;
            
            item.addEventListener('click', (e) => { 
                selectConversation(convo.id, e.currentTarget); 
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

async function fetchAndUpdateBadges() {
    try {
        const response = await fetch(`/src/api/whatsapp/get_cs_data.php?counts_only=true`, {
            headers: { 'Authorization': `Bearer ${wa_token}` }
        });
        if (!response.ok) return;
        const data = await response.json();
        if (data.unread_counts) {
            updateFilterUnreadBadges(data.unread_counts);
            const totalUnread = (data.unread_counts.live_chat || 0) + (data.unread_counts.umum || 0);
            updateTotalUnreadBadge(totalUnread);
        }
    } catch (error) {
        console.error("Gagal update badge counts:", error);
    }
}
async function selectConversation(conversationId, clickedItemElement = null) {
    if (isConversationLoading && conversationId === currentConversationId) {
        console.warn('Masih memuat percakapan, harap tunggu...');
        return; 
    }

    const oldActiveItem = document.querySelector('.conversation-item.active');
    if (oldActiveItem) {
        oldActiveItem.classList.remove('active', 'bg-blue-50');
    }

    let currentItemElement = clickedItemElement;
    if (!currentItemElement && conversationId) {
        currentItemElement = document.querySelector(`.conversation-item[data-id="${conversationId}"]`);
    }
    
    if (currentItemElement) {
        currentItemElement.classList.add('active', 'bg-blue-50');
    }

    isConversationLoading = true;
    const conversationList = document.getElementById('conversation-list');
    if (conversationList) {
        conversationList.classList.add('loading-disabled');
    }

    if (conversationId !== currentConversationId) {
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
        
        
        currentMessagePage = 1;
        hasMoreMessages = true;
        isLoadingMoreMessages = false;
    }
    
    currentConversationId = conversationId;

    try {
        
        const response = await fetch(`/src/api/whatsapp/get_cs_data.php?conversation_id=${conversationId}&page=1`, {
            headers: { 'Authorization': `Bearer ${wa_token}` }
        });
        if (!response.ok) throw new Error('Gagal memuat riwayat pesan.');
        
        
        const { details, messages, labels, pagination } = await response.json();

        
        currentMessagePage = pagination.current_page;
        hasMoreMessages = pagination.has_more;

        currentConversationStatus = details.status_percakapan;
        currentDisplayName = details.nama_display;
        currentConversationLabels = labels;

        document.getElementById('chat-with-phone').textContent = details.nomor_telepon;
        document.getElementById('chat-with-name').textContent = details.nama_profil ?? '-';
        document.getElementById('edit-display-name-button').classList.remove('hidden');
        document.getElementById('manage-labels-button').classList.remove('hidden');

        if (currentItemElement) {
            const unreadBadge = currentItemElement.querySelector('.unread-badge');
            if (unreadBadge) {
                const totalBadge = document.getElementById('total-unread-badge');
                if (totalBadge) {
                    let currentTotal = parseInt(totalBadge.textContent) || 0;
                    let itemUnreadCount = parseInt(unreadBadge.textContent) || 0;
                    let newTotal = Math.max(0, currentTotal - itemUnreadCount);
                    updateTotalUnreadBadge(newTotal); 
                }

                unreadBadge.remove();
            }
        }

        renderActiveChatLabels(labels);
        renderMessages(messages);
        updateChatUI(currentConversationStatus);


    } catch (error) {
        console.error(error);
        const messageContainer = document.getElementById('message-container'); 
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
    } finally {
        isConversationLoading = false;
        if (conversationList) {
            conversationList.classList.remove('loading-disabled');
        }
    }
}

async function loadMoreMessages() {
    if (isLoadingMoreMessages || !hasMoreMessages) return;

    isLoadingMoreMessages = true;
    currentMessagePage++; 

    const messageContainer = document.getElementById('message-container');
    const oldScrollHeight = messageContainer.scrollHeight;

    
    const spinner = document.createElement('div');
    spinner.id = 'message-loader-spinner';
    spinner.innerHTML = `<div class="flex justify-center p-3"><div class="loading-spinner" style="border-color: #e0e7ff; border-top-color: #3b82f6; width: 32px; height: 32px;"></div></div>`;
    messageContainer.prepend(spinner);

    try {
        const response = await fetch(`/src/api/whatsapp/get_cs_data.php?conversation_id=${currentConversationId}&page=${currentMessagePage}`, {
            headers: { 'Authorization': `Bearer ${wa_token}` }
        });

        if (!response.ok) throw new Error('Gagal memuat pesan lama.');
        
        const { messages, pagination } = await response.json();

        hasMoreMessages = pagination.has_more;
        currentMessagePage = pagination.current_page;

        prependMessages(messages); 

    } catch (error) {
        console.error("Gagal memuat pesan lama:", error);
        currentMessagePage--; 
    } finally {
        
        const loaderSpinner = document.getElementById('message-loader-spinner');
        if (loaderSpinner) {
            loaderSpinner.remove();
        }

        
        const newScrollHeight = messageContainer.scrollHeight;
        messageContainer.scrollTop = newScrollHeight - oldScrollHeight;

        isLoadingMoreMessages = false;
    }
}

async function loadMoreConversations() {
    if (isLoadingMoreConvos || !hasMoreConvos) return;

    isLoadingMoreConvos = true;
    currentConvoPage++;

    const listElement = document.getElementById('conversation-list');
    
    const spinner = document.createElement('div');
    spinner.id = 'convo-loader-spinner';
    spinner.innerHTML = `<div class="p-4 text-center text-gray-500">
                           <div class="loading-spinner mx-auto" style="border-color: #cbd5e1; border-top-color: #3b82f6; width: 32px; height: 32px;"></div>
                         </div>`;
    listElement.appendChild(spinner);

    try {
        await fetchAndRenderConversations(true);
    } catch (error) {
        console.error("Gagal memuat percakapan lama:", error);
        currentConvoPage--; 
    } finally {
        const loaderSpinner = document.getElementById('convo-loader-spinner');
        if (loaderSpinner) {
            loaderSpinner.remove();
        }
        isLoadingMoreConvos = false;
    }
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
                'Authorization': `Bearer ${wa_token}`
            },
            body: formData
        });
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal mengirim balasan.');
        }
        
        currentConvoPage = 1;
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
                    'Authorization': `Bearer ${wa_token}`
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
            currentConvoPage = 1;
            fetchAndRenderConversations();
        } catch (error) {
            console.error(error);
            Swal.fire('Error', error.message, 'error');
        }
    }
}

function handleEditDisplayName() {
    if (!currentConversationId) return;
    Swal.fire({
        title: 'Ubah Nama Tampilan',
        input: 'text',
        inputValue: currentDisplayName || '',
        inputPlaceholder: 'Masukkan nama tampilan...',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Simpan',
        denyButtonText: 'Hapus Nama',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#3b82f6',
        denyButtonColor: '#ef4444',
    }).then((result) => {
        if (result.isConfirmed) {
            const newName = result.value.trim();
            updateDisplayName(currentConversationId, newName);
        } else if (result.isDenied) {
            updateDisplayName(currentConversationId, null);
        }
    });
}

async function updateDisplayName(conversationId, newName) {
    try {
        const response = await fetch('/src/api/whatsapp/update_display_name.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${wa_token}`
            },
            body: JSON.stringify({
                conversation_id: conversationId,
                nama_display: newName
            })
        });
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal memperbarui nama.');
        }
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Nama berhasil diperbarui!',
            showConfirmButton: false,
            timer: 2000
        });
        currentDisplayName = newName;
        document.getElementById('chat-with-name').textContent = newName || document.getElementById('chat-with-phone').textContent;
        currentConvoPage = 1;
        fetchAndRenderConversations();
    } catch (error) {
        console.error('Gagal update nama display:', error);
        Swal.fire('Error', error.message, 'error');
    }
}

async function handleManageLabels() {
    if (!currentConversationId) return;

    try {
        const response = await fetch('/src/api/whatsapp/get_all_labels.php', {
            headers: { 'Authorization': `Bearer ${wa_token}` }
        });
        if (!response.ok) throw new Error('Gagal mengambil daftar label.');
        const allLabels = await response.json();

        const preselectedLabelIds = new Set(currentConversationLabels.map(label => label.id.toString()));

        let labelsHtml = '<div id="swal-label-list" class="text-left swal2-checkbox-list" style="display: flex; flex-direction: column; align-items: flex-start; max-height: 250px; overflow-y: auto;">';
        allLabels.forEach(label => {
            const isChecked = preselectedLabelIds.has(label.id.toString()) ? 'checked' : '';
            const textColor = getBrightness(label.warna) > 128 ? '#000' : '#FFF';
            labelsHtml += `
                <label class="swal2-checkbox" style="display: inline-flex; align-items: center; margin: 0.3em 0; cursor: pointer;">
                    <input type="checkbox" value="${label.id}" ${isChecked} style="margin-right: 0.75em;">
                    <span class="swal2-label">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium" style="background-color: ${label.warna}; color: ${textColor}; line-height: 1.4;">
                            ${label.nama_label}
                        </span>
                    </span>
                </label>
            `;
        });
        labelsHtml += '</div>';

        const fullHtml = '<div class="text-sm text-left mb-2">Pilih label untuk percakapan ini:</div>' + labelsHtml;

        const { value: selectedLabelIds } = await Swal.fire({
            title: 'Kelola Label',
            html: fullHtml,
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#3b82f6',
            width: '400px',
            preConfirm: () => {
                const checkboxes = document.querySelectorAll('#swal-label-list input[type="checkbox"]:checked');
                return Array.from(checkboxes).map(cb => cb.value);
            }
        });

        if (Array.isArray(selectedLabelIds)) {
            await updateConversationLabels(currentConversationId, selectedLabelIds);
        }
    } catch (error) {
        console.error('Gagal kelola label:', error);
        Swal.fire('Error', error.message, 'error');
    }
}

async function updateConversationLabels(conversationId, labelIds) {
    try {
        console.log("label: ", labelIds)
        const response = await fetch('/src/api/whatsapp/update_conversation_labels.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${wa_token}`
            },
            body: JSON.stringify({
                conversation_id: conversationId,
                label_ids: labelIds
            })
        });
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Gagal memperbarui label.');
        }

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Label berhasil diperbarui!',
            showConfirmButton: false,
            timer: 2000
        });

        currentConversationLabels = result.new_labels || [];
        renderActiveChatLabels(currentConversationLabels);
        currentConvoPage = 1;
        fetchAndRenderConversations(); 
    } catch (error) {
        console.error('Gagal update label percakapan:', error);
        Swal.fire('Error', error.message, 'error');
    }
}
async function startConversation() {
  if (!currentConversationId) return;

  const confirmation = await Swal.fire({
      title: 'Mulai Live Chat?',
      text: "Anda akan mengirim undangan untuk memulai live chat ke pelanggan ini. Lanjutkan?",
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#28a745', 
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Ya, Kirim Undangan!',
      cancelButtonText: 'Batal'
  });

  if (confirmation.isConfirmed) {
      const startButton = document.getElementById('start-chat-button');
      if(startButton) {
          startButton.disabled = true;
          startButton.innerHTML = '<div class="loading-spinner"></div>';
      }

      try {
          const response = await fetch('/src/api/whatsapp/send_live_chat_invitation.php', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'Authorization': `Bearer ${wa_token}`
              },
              body: JSON.stringify({ conversation_id: currentConversationId })
          });
          const result = await response.json();
          if (!response.ok || !result.success) {
              throw new Error(result.message || 'Gagal mengirim undangan.');
          }
          
          Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: 'Undangan terkirim!',
              text: 'Menunggu balasan pelanggan.',
              showConfirmButton: false,
              timer: 3000
          });
          
          currentConvoPage = 1;
          fetchAndRenderConversations();

      } catch (error) {
          console.error(error);
          Swal.fire('Error', error.message, 'error');
      } finally {
            if(startButton) {
                startButton.disabled = false;
                startButton.innerHTML = '<i class="fas fa-play-circle"></i><span class="hidden sm:inline">Mulai Chat</span>';
            }
      }
  }
}