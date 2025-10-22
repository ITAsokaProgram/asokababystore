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

document.addEventListener('DOMContentLoaded', () => {
    if (window.innerWidth <= 768) {
        document.getElementById('conversation-list-container').classList.add('mobile-show');
    }

    if (!wa_token) {
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

    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        const debouncedSearch = debounce(() => {
            currentSearchTerm = searchInput.value;
            fetchAndRenderConversations();
        }, 300);

        searchInput.addEventListener('input', debouncedSearch);
    }

    initWebSocket();
    fetchAndRenderConversations(true);

    const sendButton = document.getElementById('send-button');
    const messageInput = document.getElementById('message-input');
    const endChatButton = document.getElementById('end-chat-button');
    const manageLabelsButton = document.getElementById('manage-labels-button');
    const editDisplayNameButton = document.getElementById('edit-display-name-button');

    sendButton.addEventListener('click', sendMessage);
    editDisplayNameButton.addEventListener('click', handleEditDisplayName);
    manageLabelsButton.addEventListener('click', handleManageLabels);

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