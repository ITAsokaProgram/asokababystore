let selectedMediaFile = null;
let currentConversationLabels = [];
let currentSearchTerm = '';
const wa_token = getToken();
let ws;
let currentConversationId = null;
let currentDisplayName = null;
let currentConversationStatus = null;
let currentFilter = 'semua';
let isConversationLoading = false;

let currentMessagePage = 1;
let hasMoreMessages = true;
let isLoadingMoreMessages = false;

let currentConvoPage = 1;
let hasMoreConvos = true;
let isLoadingMoreConvos = false;

document.addEventListener('DOMContentLoaded', () => {
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
    if (window.innerWidth <= 768) {
        document.getElementById('conversation-list-container').classList.add('mobile-show');
    }

    if (!wa_token) {
        console.error("Token admin tidak ditemukan. Harap login kembali.");
        Swal.fire('Error', 'Token tidak ditemukan, harap login kembali.', 'error');
        return;
    }

    const messageContainer = document.getElementById('message-container');
    
    messageContainer.addEventListener('scroll', () => {
        if (messageContainer.scrollTop === 0 && hasMoreMessages && !isLoadingMoreMessages && currentConversationId) {
            loadMoreMessages();
        }
    });

    const conversationList = document.getElementById('conversation-list');
    if (conversationList) {
        conversationList.addEventListener('scroll', () => {
            const { scrollTop, scrollHeight, clientHeight } = conversationList;
            if (scrollTop + clientHeight >= scrollHeight - 50 && hasMoreConvos && !isLoadingMoreConvos) {
                loadMoreConversations();
            }
        });
    }

    const mobileBackButton = document.getElementById('mobile-back-button');
    const conversationListContainer = document.getElementById('conversation-list-container');
    const activeChat = document.getElementById('active-chat');
    const chatPlaceholder = document.getElementById('chat-placeholder');
    const mediaInput = document.getElementById('media-input');
    const chatLayout = document.getElementById('chat-layout');
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

    const fullscreenButton = document.getElementById('mobile-fullscreen-toggle');
    const fullscreenIcon = document.getElementById('fullscreen-icon');

    if (fullscreenButton && chatLayout) {
        fullscreenButton.addEventListener('click', () => {
        if (!document.fullscreenElement) {
            // Jika belum fullscreen, minta masuk ke mode fullscreen
            chatLayout.requestFullscreen().catch(err => {
            console.error(`Gagal masuk mode layar penuh: ${err.message} (${err.name})`);
            });
        } else {
            // Jika sedang fullscreen, keluar dari mode fullscreen
            if (document.exitFullscreen) {
            document.exitFullscreen();
            }
        }
        });

        // Listener ini penting untuk mengubah ikon jika pengguna keluar fullscreen (misal: pakai tombol 'Esc')
        document.addEventListener('fullscreenchange', () => {
        if (document.fullscreenElement) {
            // Sedang dalam mode fullscreen
            fullscreenIcon.classList.remove('fa-expand');
            fullscreenIcon.classList.add('fa-compress');
            fullscreenButton.title = "Keluar Layar Penuh";
        } else {
            // Keluar dari mode fullscreen
            fullscreenIcon.classList.remove('fa-compress');
            fullscreenIcon.classList.add('fa-expand');
            fullscreenButton.title = "Layar Penuh";
        }
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
            currentConvoPage = 1; 
            hasMoreConvos = true;
            fetchAndRenderConversations();
        });
    }

    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        const debouncedSearch = debounce(() => {
            currentSearchTerm = searchInput.value;
            currentConvoPage = 1; 
            hasMoreConvos = true; 
            fetchAndRenderConversations();
        }, 300);

        searchInput.addEventListener('input', debouncedSearch);
    }

    initWebSocket();
    fetchAndRenderConversations();

    const sendButton = document.getElementById('send-button');
    const messageInput = document.getElementById('message-input');
    const endChatButton = document.getElementById('end-chat-button');
    const manageLabelsButton = document.getElementById('manage-labels-button');
    const editDisplayNameButton = document.getElementById('edit-display-name-button');
    const startChatButton = document.getElementById('start-chat-button'); 

    sendButton.addEventListener('click', sendMessage);
    editDisplayNameButton.addEventListener('click', handleEditDisplayName);
    manageLabelsButton.addEventListener('click', handleManageLabels);
    endChatButton.addEventListener('click', endConversation);
    startChatButton.addEventListener('click', startConversation);


    messageInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    messageInput.addEventListener('paste', (e) => {
        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        let foundImage = false;

        for (let i = 0; i < items.length; i++) {
            if (items[i].type.indexOf('image') !== -1) {
                const file = items[i].getAsFile();
                if (file) {
                    e.preventDefault();
                    foundImage = true;

                    if (selectedMediaFile) {
                        removeMediaButton.click();
                    }

                    selectedMediaFile = file;
                    const fileURL = URL.createObjectURL(file);

                    mediaPreviewImage.src = fileURL;
                    mediaPreviewImage.classList.remove('hidden');
                    mediaPreviewVideo.classList.add('hidden');
                    mediaPreviewContainer.classList.remove('hidden');
                    
                    break; 
                }
            }
        }
    });


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
    setInterval(() => {
        if (typeof updateAllTimeAgoStrings === 'function') {
            updateAllTimeAgoStrings();
        }
    }, 30000);
});
