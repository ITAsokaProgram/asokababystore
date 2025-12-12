let selectedMediaFile = null;
let currentConversationLabels = [];
let currentSearchTerm = "";
const wa_token = getToken();
let ws;
let currentConversationId = null;
let currentDisplayName = null;
let currentConversationStatus = null;
let currentFilter = "semua";
let isConversationLoading = false;
let currentMessagePage = 1;
let hasMoreMessages = true;
let isLoadingMoreMessages = false;
let currentConvoPage = 1;
let hasMoreConvos = true;
let isLoadingMoreConvos = false;
const BRANCH_CONTACTS = {
  Jabodetabek: {
    "Daan Mogot": "6281808174105",
    Poris: "6281806683401",
    "Harapan Indah": "6287889552647",
    Bintaro: "6287775692431",
    Cinere: "6287787987127",
    Pamulang: "6285947461478",
    Ciledug: "6287849816901",
    Kartini: "6287849816904",
    Parung: "6287887689802",
    Condet: "6287739974652",
    "Duren Sawit": "6285951449821",
    Rawamangun: "6287773844521",
    Cibubur: "6287863814646",
    Ceger: "6285965847263",
    Jatiwaringin: "6281998482529",
    "Graha Raya": "6287846959785",
    Galaxy: "6287852415221",
    Jatiasih: "6287856599869",
    "PIK 2": "6287772562015",
  },
  "Bangka & Belitung": {
    "Pangkal Pinang": "6287896370431",
    Merapin: "6287797561846",
    Toboali: "6281995651279",
    Semabung: "6281908239741",
    Koba: "6285933237653",
    Sungailiat: "6285933237651",
    "Tanjung Pandan": "6281929765780",
    "Air Raya": "6281929746487",
    Manggar: "6287866839246",
  },
};
document.addEventListener("DOMContentLoaded", () => {
  if ("Notification" in window && Notification.permission === "default") {
    Notification.requestPermission();
  }
  if (window.innerWidth <= 768) {
    document
      .getElementById("conversation-list-container")
      .classList.add("mobile-show");
  }
  if (!wa_token) {
    console.error("Token admin tidak ditemukan. Harap login kembali.");
    Swal.fire("Error", "Token tidak ditemukan, harap login kembali.", "error");
    return;
  }
  const messageContainer = document.getElementById("message-container");
  messageContainer.addEventListener("scroll", () => {
    if (
      messageContainer.scrollTop === 0 &&
      hasMoreMessages &&
      !isLoadingMoreMessages &&
      !isConversationLoading &&
      currentConversationId
    ) {
      loadMoreMessages();
    }
  });
  const conversationList = document.getElementById("conversation-list");
  if (conversationList) {
    conversationList.addEventListener("scroll", () => {
      const { scrollTop, scrollHeight, clientHeight } = conversationList;
      if (
        scrollTop + clientHeight >= scrollHeight - 50 &&
        hasMoreConvos &&
        !isLoadingMoreConvos
      ) {
        loadMoreConversations();
      }
    });
  }
  const quickContactButton = document.getElementById("quick-contact-button");
  if (quickContactButton) {
    quickContactButton.addEventListener("click", showQuickContactMenu);
  }
  const mobileBackButton = document.getElementById("mobile-back-button");
  const conversationListContainer = document.getElementById(
    "conversation-list-container"
  );
  const activeChat = document.getElementById("active-chat");
  const chatPlaceholder = document.getElementById("chat-placeholder");
  const mediaInput = document.getElementById("media-input");
  const chatLayout = document.getElementById("chat-layout");
  const mediaPreviewContainer = document.getElementById(
    "media-preview-container"
  );
  const mediaPreviewImage = document.getElementById("media-preview-image");
  const mediaPreviewVideo = document.getElementById("media-preview-video");
  const removeMediaButton = document.getElementById("remove-media-button");
  mediaInput.addEventListener("change", () => {
    const file = mediaInput.files[0];
    if (!file) return;
    selectedMediaFile = file;
    const fileURL = URL.createObjectURL(file);
    mediaPreviewImage.classList.add("hidden");
    mediaPreviewVideo.classList.add("hidden");
    let docPreview = document.getElementById("media-preview-doc");
    if (!docPreview) {
      docPreview = document.createElement("div");
      docPreview.id = "media-preview-doc";
      docPreview.className =
        "hidden p-2 bg-gray-100 rounded text-sm font-medium text-gray-700 flex items-center gap-2";
      mediaPreviewContainer.insertBefore(
        docPreview,
        removeMediaButton.nextSibling
      );
    }
    docPreview.classList.add("hidden");
    if (file.type.startsWith("image/")) {
      mediaPreviewImage.src = fileURL;
      mediaPreviewImage.classList.remove("hidden");
    } else if (file.type.startsWith("video/")) {
      mediaPreviewVideo.src = fileURL;
      mediaPreviewVideo.classList.remove("hidden");
    } else {
      docPreview.innerHTML = `<i class="fas fa-file-alt text-blue-500"></i> ${file.name}`;
      docPreview.classList.remove("hidden");
    }
    mediaPreviewContainer.classList.remove("hidden");
  });
  removeMediaButton.addEventListener("click", () => {
    mediaInput.value = "";
    selectedMediaFile = null;
    mediaPreviewContainer.classList.add("hidden");
    mediaPreviewImage.src = "";
    mediaPreviewVideo.src = "";
    const docPreview = document.getElementById("media-preview-doc");
    if (docPreview) docPreview.classList.add("hidden");
  });
  const mobileListToggle = document.getElementById("mobile-list-toggle");
  if (mobileListToggle) {
    mobileListToggle.addEventListener("click", () => {
      conversationListContainer.classList.toggle("mobile-show");
    });
  }
  const fullscreenButton = document.getElementById("mobile-fullscreen-toggle");
  const fullscreenIcon = document.getElementById("fullscreen-icon");
  if (fullscreenButton && chatLayout) {
    fullscreenButton.addEventListener("click", () => {
      if (!document.fullscreenElement) {
        chatLayout.requestFullscreen().catch((err) => {
          console.error(
            `Gagal masuk mode layar penuh: ${err.message} (${err.name})`
          );
        });
      } else {
        if (document.exitFullscreen) {
          document.exitFullscreen();
        }
      }
    });
    document.addEventListener("fullscreenchange", () => {
      if (document.fullscreenElement) {
        fullscreenIcon.classList.remove("fa-expand");
        fullscreenIcon.classList.add("fa-compress");
        fullscreenButton.title = "Keluar Layar Penuh";
      } else {
        fullscreenIcon.classList.remove("fa-compress");
        fullscreenIcon.classList.add("fa-expand");
        fullscreenButton.title = "Layar Penuh";
      }
    });
  }
  const mobileCloseListButton = document.getElementById("mobile-close-list");
  if (mobileCloseListButton) {
    mobileCloseListButton.addEventListener("click", () => {
      conversationListContainer.classList.remove("mobile-show");
    });
  }
  if (mobileBackButton) {
    mobileBackButton.addEventListener("click", () => {
      if (window.innerWidth <= 768) {
        activeChat.classList.add("hidden");
        chatPlaceholder.classList.remove("hidden");
        conversationListContainer.classList.add("mobile-show");
      }
    });
  }
  const toggleButton = document.getElementById("toggle-conversation-list");
  if (toggleButton) {
    const isCollapsed =
      sessionStorage.getItem("conversationListCollapsed") === "true";
    if (isCollapsed) {
      conversationListContainer.classList.add("collapsed");
      chatLayout.classList.add("list-collapsed");
    }
    toggleButton.addEventListener("click", () => {
      const isCurrentlyCollapsed =
        conversationListContainer.classList.contains("collapsed");
      if (isCurrentlyCollapsed) {
        conversationListContainer.classList.remove("collapsed");
        chatLayout.classList.remove("list-collapsed");
        sessionStorage.setItem("conversationListCollapsed", "false");
      } else {
        conversationListContainer.classList.add("collapsed");
        chatLayout.classList.add("list-collapsed");
        sessionStorage.setItem("conversationListCollapsed", "true");
      }
    });
  }
  const filterButtonsContainer = document.getElementById(
    "status-filter-buttons"
  );
  if (filterButtonsContainer) {
    filterButtonsContainer.addEventListener("click", (e) => {
      const button = e.target.closest(".filter-button");
      if (!button) return;
      currentFilter = button.dataset.filter;
      filterButtonsContainer
        .querySelectorAll(".filter-button")
        .forEach((btn) => {
          btn.classList.remove(
            "active",
            "bg-blue-500",
            "text-white",
            "shadow-sm"
          );
          btn.classList.add(
            "bg-gray-100",
            "text-gray-600",
            "hover:bg-gray-200"
          );
        });
      button.classList.add("active", "bg-blue-500", "text-white", "shadow-sm");
      button.classList.remove(
        "bg-gray-100",
        "text-gray-600",
        "hover:bg-gray-200"
      );
      currentConvoPage = 1;
      hasMoreConvos = true;
      fetchAndRenderConversations();
    });
  }
  const searchInput = document.getElementById("search-input");
  if (searchInput) {
    const debouncedSearch = debounce(() => {
      currentSearchTerm = searchInput.value;
      currentConvoPage = 1;
      hasMoreConvos = true;
      fetchAndRenderConversations();
    }, 300);
    searchInput.addEventListener("input", debouncedSearch);
  }
  initWebSocket();
  fetchAndRenderConversations();
  const sendButton = document.getElementById("send-button");
  const messageInput = document.getElementById("message-input");
  const endChatButton = document.getElementById("end-chat-button");
  const manageLabelsButton = document.getElementById("manage-labels-button");
  const editDisplayNameButton = document.getElementById(
    "edit-display-name-button"
  );
  const startChatButton = document.getElementById("start-chat-button");
  sendButton.addEventListener("click", sendMessage);
  editDisplayNameButton.addEventListener("click", handleEditDisplayName);
  manageLabelsButton.addEventListener("click", handleManageLabels);
  endChatButton.addEventListener("click", endConversation);
  startChatButton.addEventListener("click", startConversation);
  messageInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });
  messageInput.addEventListener("paste", (e) => {
    const items = (e.clipboardData || e.originalEvent.clipboardData).items;
    let foundImage = false;
    for (let i = 0; i < items.length; i++) {
      if (items[i].type.indexOf("image") !== -1) {
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
          mediaPreviewImage.classList.remove("hidden");
          mediaPreviewVideo.classList.add("hidden");
          mediaPreviewContainer.classList.remove("hidden");
          break;
        }
      }
    }
  });
  messageInput.addEventListener("input", () => {
    messageInput.style.height = "auto";
    messageInput.style.height = Math.min(messageInput.scrollHeight, 120) + "px";
  });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" || e.key === "Esc") {
      e.preventDefault();
      clearActiveConversation();
    }
  });
  setInterval(() => {
    if (typeof updateAllTimeAgoStrings === "function") {
      updateAllTimeAgoStrings();
    }
  }, 30000);
});
async function showQuickContactMenu() {
  if (!currentConversationId || currentConversationStatus !== "live_chat") {
    Swal.fire({
      icon: "warning",
      title: "Tidak Tersedia",
      text: "Fitur ini hanya tersedia saat live chat aktif.",
      confirmButtonColor: "#3b82f6",
    });
    return;
  }
  const regionsHtml = Object.keys(BRANCH_CONTACTS)
    .map(
      (region) =>
        `<optgroup label="${region}">
            ${Object.entries(BRANCH_CONTACTS[region])
              .map(
                ([branch, phone]) =>
                  `<option value="${phone}" data-branch="${branch}">${branch}</option>`
              )
              .join("")}
        </optgroup>`
    )
    .join("");
  const { value: selectedPhone } = await Swal.fire({
    title: "Kirim Kontak Cabang",
    html: `
            <div class="text-left mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Cabang:</label>
                <select id="branch-selector" class="swal2-input w-full p-2 border rounded-lg">
                    ${regionsHtml}
                </select>
            </div>
            <div id="selected-info" class="hidden mt-3 p-3 bg-blue-50 rounded-lg text-sm">
                <p class="font-semibold text-blue-900" id="selected-branch"></p>
                <p class="text-blue-700" id="selected-phone"></p>
            </div>
        `,
    showCancelButton: true,
    confirmButtonText: "Kirim Kontak",
    cancelButtonText: "Batal",
    confirmButtonColor: "#10b981",
    cancelButtonColor: "#6b7280",
    width: "400px",
    didOpen: () => {
      const selector = document.getElementById("branch-selector");
      const info = document.getElementById("selected-info");
      const branchName = document.getElementById("selected-branch");
      const phoneNumber = document.getElementById("selected-phone");
      selector.addEventListener("change", (e) => {
        if (e.target.value) {
          const selectedOption = e.target.options[e.target.selectedIndex];
          const branch = selectedOption.dataset.branch;
          branchName.textContent = `Cabang: ${branch}`;
          phoneNumber.textContent = `Nomor: ${e.target.value}`;
          info.classList.remove("hidden");
        } else {
          info.classList.add("hidden");
        }
      });
    },
    preConfirm: () => {
      const selector = document.getElementById("branch-selector");
      if (!selector.value) {
        Swal.showValidationMessage("Silakan pilih cabang terlebih dahulu");
        return false;
      }
      return {
        phone: selector.value,
        branch: selector.options[selector.selectedIndex].dataset.branch,
      };
    },
  });
  if (selectedPhone) {
    await sendContactToCustomer(selectedPhone.branch, selectedPhone.phone);
  }
}
async function sendContactToCustomer(branchName, phoneNumber) {
  try {
    const response = await fetch("/src/api/whatsapp/send_branch_contact.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${wa_token}`,
      },
      body: JSON.stringify({
        conversation_id: currentConversationId,
        branch_name: branchName,
        phone_number: phoneNumber,
      }),
    });
    const result = await response.json();
    if (!response.ok || !result.success) {
      throw new Error(result.message || "Gagal mengirim kontak.");
    }
    Swal.fire({
      toast: true,
      position: "top-end",
      icon: "success",
      title: "Kontak berhasil dikirim!",
      showConfirmButton: false,
      timer: 2000,
    });
  } catch (error) {
    console.error("Error sending contact:", error);
    Swal.fire({
      icon: "error",
      title: "Gagal",
      text: error.message,
      confirmButtonColor: "#ef4444",
    });
  }
}
