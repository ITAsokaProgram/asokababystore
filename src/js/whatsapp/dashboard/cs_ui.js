function renderActiveChatLabels(labels) {
  const container = document.getElementById("active-chat-labels");
  if (!container) return;
  container.innerHTML = renderLabelTags(labels, "sm");
}
function renderLabelTags(labels, size = "xs") {
  if (!labels || labels.length === 0) return "";
  const sizeClasses =
    size === "xs" ? "text-[10px] px-1.5 py-0.5" : "text-xs px-2 py-0.5";
  return labels
    .map((label) => {
      const brightness = getBrightness(label.warna);
      const textColor = brightness > 128 ? "#000000" : "#FFFFFF";
      return `<span class="label-tag inline-block ${sizeClasses} font-medium rounded-full" style="background-color: ${label.warna}; color: ${textColor}; line-height: 1.2;">
                ${label.nama_label}
              </span>`;
    })
    .join(" ");
}
function renderMessages(messages) {
  const messageContainer = document.getElementById("message-container");
  messageContainer.innerHTML = "";
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
  messages.forEach((msg) => appendMessage(msg, false));
  messageContainer.scrollTop = messageContainer.scrollHeight;
}
function appendMessage(msg, scrollToBottom = true) {
  const messageContainer = document.getElementById("message-container");
  const placeholder = messageContainer.querySelector(".no-message-placeholder");
  if (placeholder) {
    placeholder.remove();
  }
  const lastBubble = messageContainer.querySelector(
    ".message-bubble:last-child"
  );
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
    const separator = document.createElement("div");
    separator.className = "date-separator";
    separator.textContent = formatDateSeparator(msg.timestamp);
    messageContainer.appendChild(separator);
  }
  const bubble = createMessageBubble(msg);
  messageContainer.appendChild(bubble);
  if (scrollToBottom) {
    messageContainer.scrollTop = messageContainer.scrollHeight;
  }
}
function prependMessages(messages) {
  const messageContainer = document.getElementById("message-container");
  const fragment = document.createDocumentFragment();
  const firstBubbleInContainer = messageContainer.querySelector(
    ".message-bubble:first-child"
  );
  let lastTimestamp = firstBubbleInContainer
    ? firstBubbleInContainer.dataset.timestamp
    : null;
  let lastDateString = lastTimestamp
    ? new Date(lastTimestamp).toDateString()
    : null;
  const existingFirstSeparator = messageContainer.querySelector(
    ".date-separator:first-of-type"
  );
  const existingFirstSeparatorText = existingFirstSeparator
    ? existingFirstSeparator.textContent
    : null;
  for (let i = messages.length - 1; i >= 0; i--) {
    const msg = messages[i];
    const msgDateString = new Date(msg.timestamp).toDateString();
    if (lastTimestamp && msgDateString !== lastDateString) {
      const separatorText = formatDateSeparator(lastTimestamp);
      if (separatorText === existingFirstSeparatorText) {
        if (existingFirstSeparator) {
          existingFirstSeparator.remove();
        }
      }
      const separator = document.createElement("div");
      separator.className = "date-separator";
      separator.textContent = separatorText;
      fragment.prepend(separator);
    }
    const bubble = createMessageBubble(msg);
    fragment.prepend(bubble);
    lastTimestamp = msg.timestamp;
    lastDateString = msgDateString;
  }
  if (messages.length > 0) {
    if (
      fragment.children.length > 0 &&
      fragment.children[0].classList.contains("message-bubble")
    ) {
      const oldestSeparator = document.createElement("div");
      oldestSeparator.className = "date-separator";
      oldestSeparator.textContent = formatDateSeparator(messages[0].timestamp);
      const firstSepInDomNow = messageContainer.querySelector(
        ".date-separator:first-of-type"
      );
      if (
        firstSepInDomNow &&
        firstSepInDomNow.textContent === oldestSeparator.textContent
      ) {
        firstSepInDomNow.remove();
      }
      fragment.prepend(oldestSeparator);
    }
  }
  messageContainer.prepend(fragment);
}
function updateChatUI(status) {
  const endChatButton = document.getElementById("end-chat-button");
  const startChatButton = document.getElementById("start-chat-button");
  const quickContactButton = document.getElementById("quick-contact-button");
  const messageInputArea = document.getElementById("message-input-area");
  const manageLabelsButton = document.getElementById("manage-labels-button");
  if (status === "live_chat") {
    endChatButton.classList.remove("hidden");
    startChatButton.classList.add("hidden");
    quickContactButton.classList.remove("hidden");
    messageInputArea.classList.remove("hidden");
    manageLabelsButton.classList.remove("hidden");
  } else {
    endChatButton.classList.add("hidden");
    quickContactButton.classList.add("hidden");
    if (status) {
      startChatButton.classList.remove("hidden");
      manageLabelsButton.classList.remove("hidden");
    } else {
      startChatButton.classList.add("hidden");
      manageLabelsButton.classList.add("hidden");
    }
    messageInputArea.classList.add("hidden");
  }
}
function clearActiveConversation() {
  if (!currentConversationId) {
    return;
  }
  document.getElementById("chat-with-name").textContent = "";
  currentConversationId = null;
  currentConversationStatus = null;
  const activeChat = document.getElementById("active-chat");
  const chatPlaceholder = document.getElementById("chat-placeholder");
  const chatHeader = document.getElementById("chat-header");
  const chatWithPhone = document.getElementById("chat-with-phone");
  activeChat.classList.add("hidden");
  activeChat.classList.remove("flex");
  if (window.innerWidth <= 768) {
    document
      .getElementById("conversation-list-container")
      .classList.add("mobile-show");
    chatPlaceholder.classList.add("hidden");
  } else {
    chatPlaceholder.classList.remove("hidden");
  }
  chatHeader.classList.remove("show");
  chatWithPhone.textContent = "";
  document.getElementById("edit-display-name-button").classList.add("hidden");
  document.getElementById("manage-labels-button").classList.add("hidden");
  currentDisplayName = null;
  currentConversationLabels = [];
  document.getElementById("active-chat-labels").innerHTML = "";
  updateChatUI(null);
  const activeItem = document.querySelector(".conversation-item.active");
  if (activeItem) {
    activeItem.classList.remove("active", "bg-blue-50");
  }
}
function updateTotalUnreadBadge(count) {
  const badge = document.getElementById("total-unread-badge");
  const title = document.querySelector("title");
  if (!badge) return;
  if (count > 0) {
    badge.textContent = count;
    badge.classList.remove("hidden");
    title.textContent = `(${count}) Dashboard CS WhatsApp`;
  } else {
    badge.textContent = "0";
    badge.classList.add("hidden");
    title.textContent = "Dashboard CS WhatsApp";
  }
}
function updateFilterUnreadBadges(counts) {
  const liveChatBadge = document.getElementById("unread-live_chat");
  const umumBadge = document.getElementById("unread-umum");
  const broadcastBadge = document.getElementById("unread-broadcast");
  const allBadge = document.getElementById("unread-all");
  const total = (counts.live_chat || 0) + (counts.umum || 0) + (counts.broadcast || 0);
  if (allBadge) {
    if (total > 0) {
      allBadge.textContent = total;
      allBadge.classList.remove("hidden");
    } else {
      allBadge.textContent = "0";
      allBadge.classList.add("hidden");
    }
  }
  if (liveChatBadge) {
    if (counts.live_chat > 0) {
      liveChatBadge.textContent = counts.live_chat;
      liveChatBadge.classList.remove("hidden");
    } else {
      liveChatBadge.textContent = "0";
      liveChatBadge.classList.add("hidden");
    }
  }
  if (umumBadge) {
    if (counts.umum > 0) {
      umumBadge.textContent = counts.umum;
      umumBadge.classList.remove("hidden");
    } else {
      umumBadge.textContent = "0";
      umumBadge.classList.add("hidden");
    }
  }
  if (broadcastBadge) {
    if (counts.broadcast > 0) {
      broadcastBadge.textContent = counts.broadcast;
      broadcastBadge.classList.remove("hidden");
    } else {
      broadcastBadge.textContent = "0";
      broadcastBadge.classList.add("hidden");
    }
  }
}
function getStatusIcon(status) {
  let iconClass = "fa-check";
  let iconColor = "text-gray-400";
  if (status === "delivered") {
    iconClass = "fa-check-double";
  } else if (status === "read") {
    iconClass = "fa-check-double";
    iconColor = "text-teal-500";
  }
  return `<i class="fas ${iconClass} ${iconColor} message-status-icon"></i>`;
}
function createMessageBubble(msg) {
  const bubble = document.createElement("div");
  const isUser = msg.pengirim === "user";
  let bubbleTypeClass = "";
  if (isUser) {
    bubbleTypeClass = "user-bubble";
  } else {
    if (msg.dikirim_oleh_bot == 1) {
      bubbleTypeClass = "admin-bubble-bot";
    } else {
      bubbleTypeClass = "admin-bubble";
    }
  }
  bubble.className = `message-bubble ${bubbleTypeClass}`;
  bubble.dataset.timestamp = msg.timestamp;
  if (!isUser && msg.wamid) {
    bubble.dataset.wamid = msg.wamid;
  }
  const messageType = msg.tipe_pesan || "text";
  let contentHTML = "";
  switch (messageType) {
    case "broadcast":
      let templateName = "-";
      let variables = [];
      let attachmentUrl = null;
      const lines = msg.isi_pesan.split("\n");
      lines.forEach((line) => {
        const trimmedLine = line.trim();
        if (trimmedLine.startsWith("Template:")) {
          templateName = trimmedLine.substring(9).trim();
        } else if (trimmedLine.startsWith("Isi/Vars:")) {
          const varsString = trimmedLine.substring(9).trim();
          if (varsString) {
            variables = varsString.split(",").map((v) => v.trim());
          }
        } else if (trimmedLine.startsWith("Lampiran:")) {
          attachmentUrl = trimmedLine.substring(9).trim();
        }
      });
      let mediaHtml = "";
      if (attachmentUrl && attachmentUrl !== "null" && attachmentUrl !== "") {
        mediaHtml = `
            <div class="mb-3 rounded-lg overflow-hidden border border-gray-100 shadow-sm group relative">
                <a href="${attachmentUrl}" target="_blank" rel="noopener noreferrer">
                    <img src="${attachmentUrl}" alt="Broadcast Media" class="w-full h-auto object-cover max-h-[200px] transition-transform duration-300 group-hover:scale-105">
                     <div class="absolute inset-0 opacity-50  group-hover:bg-opacity-10 transition-all flex items-center justify-center">
                        <i class="fas fa-external-link-alt text-white opacity-0 group-hover:opacity-100 drop-shadow-md"></i>
                    </div>
                </a>
            </div>`;
      }
      let varsHtml = "";
      if (variables.length > 0 && variables[0] !== "") {
        const badges = variables
          .map(
            (v) =>
              `<span class="inline-block bg-blue-50 text-blue-700 text-[10px] px-2 py-1 rounded border border-blue-100 font-medium font-mono">${v}</span>`
          )
          .join("");
        varsHtml = `
            <div class="mt-2 pt-2 border-t border-dashed border-gray-200">
                <p class="text-[10px] text-gray-400 mb-1 uppercase tracking-wider font-semibold">Variabel Data</p>
                <div class="flex flex-wrap gap-1.5">
                    ${badges}
                </div>
            </div>`;
      }
      contentHTML = `
        <div class="message-content broadcast-content bg-white rounded-lg p-0 overflow-hidden" style="min-width: 260px; max-width: 320px;">
            <div class="flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-purple-50 to-white border-b border-purple-100">
                <div class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-bullhorn text-purple-600 text-xs"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-purple-700 uppercase tracking-wide">Broadcast</p>
                    <p class="text-[10px] text-gray-400 leading-none">Pesan Otomatis</p>
                </div>
            </div>
            <div class="p-3">
                ${mediaHtml}
                <div class="mb-1">
                    <p class="text-[10px] text-gray-400 mb-0.5 uppercase tracking-wider font-semibold">Nama Template</p>
                    <div class="bg-gray-50 text-gray-700 text-xs px-2 py-1.5 rounded border border-gray-200 font-mono break-all flex items-center gap-2">
                        <i class="fas fa-code text-gray-400 text-[10px]"></i>
                        ${templateName}
                    </div>
                </div>
                ${varsHtml}
            </div>
        </div>`;
      break;
    case "image":
      bubble.classList.add("media-bubble");
      contentHTML = `
                <div class="message-content media-content">
                    <a href="${msg.isi_pesan}" target="_blank" rel="noopener noreferrer">
                        <img src="${msg.isi_pesan}" alt="Gambar" class="media-item">
                    </a>
                </div>`;
      break;
    case "video":
      bubble.classList.add("media-bubble");
      contentHTML = `
                <div class="message-content media-content">
                    <video src="${msg.isi_pesan}" controls class="media-item"></video>
                </div>`;
      break;
    case "audio":
      contentHTML = `
                <div class="message-content audio-content">
                    <audio src="${msg.isi_pesan}" controls class="audio-player"></audio>
                </div>`;
      break;
    case "document":
      let url, filename;
      try {
        const docInfo = JSON.parse(msg.isi_pesan);
        url = docInfo.url;
        filename = docInfo.filename || "Dokumen";
      } catch (e) {
        url = msg.isi_pesan;
        try {
          filename = url.split("/").pop();
          if (filename.includes("?")) {
            filename = filename.split("?")[0];
          }
          filename = decodeURIComponent(filename);
        } catch (err) {
          filename = "Dokumen";
        }
      }
      const filenameLower = (filename || "").toLowerCase();
      let iconClass = "fas fa-file-alt";
      let iconColor = "#4B5563";
      if (filenameLower.endsWith(".pdf")) {
        iconClass = "fas fa-file-pdf";
        iconColor = "#EF4444";
      } else if (
        filenameLower.endsWith(".doc") || filenameLower.endsWith(".docx")
      ) {
        iconClass = "fas fa-file-word";
        iconColor = "#3B82F6";
      } else if (
        filenameLower.endsWith(".xls") ||
        filenameLower.endsWith(".xlsx") ||
        filenameLower.endsWith(".csv")
      ) {
        iconClass = "fas fa-file-excel";
        iconColor = "#10B981";
      }
      let bgColor = "#F3F4F6";
      if (isUser) {
        bgColor = "#EBF5FF";
      } else if (msg.dikirim_oleh_bot == 1) {
        bgColor = "#F0FFF4";
      }
      contentHTML = `
            <div class="message-content document-content" style="display: flex; align-items: center; background-color: ${bgColor}; border-radius: 8px; padding: 10px 14px; max-width: 280px; word-break: break-all;">
                <a href="${url}" target="_blank" rel="noopener noreferrer" download="${filename}" style="display: flex; align-items: center; text-decoration: none; color: #333; width: 100%;">
                    <i class="${iconClass}" style="font-size: 1.6em; color: ${iconColor}; margin-right: 12px; flex-shrink: 0;"></i>
                    <span style="flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 500; color: #1F2937;">${filename}</span>
                    <i class="fas fa-download" style="font-size: 1em; color: #6B7280; margin-left: 10px; flex-shrink: 0;"></i>
                </a>
            </div>`;
      bubble.classList.add("file-bubble");
      break;
    case "contacts":
      try {
        const contactInfo = JSON.parse(msg.isi_pesan);
        const contactName = contactInfo.name || "Kontak";
        const contactPhone = contactInfo.phone || "Tidak ada nomor";
        let iconClass = "fas fa-user-circle";
        let iconColor = "#3B82F6";
        let bgColor = "#F3F4F6";
        if (isUser) {
          bgColor = "#EBF5FF";
        } else if (msg.dikirim_oleh_bot == 1) {
          bgColor = "#F0FFF4";
        }
        contentHTML = `
                                <div class="message-content contact-content" style="display: flex; align-items: center; background-color: ${bgColor}; border-radius: 8px; padding: 4px; max-width: 280px; word-break: break-all;">
                                    <div  style="display: flex; align-items: center; text-decoration: none; color: #333; width: 100%;">
                                        <i class="${iconClass}" style="font-size: 1.6em; color: ${iconColor}; margin-right: 12px; flex-shrink: 0;"></i>
                                        <div style="flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: normal;">
                                            <span style="font-weight: 500; color: #1F2937; display: block;">${contactName}</span>
                                            <span style="font-size: 0.9em; color: #6B7280; display: block;">${contactPhone}</span>
                                        </div>
                                    </div>
                                </div>`;
        bubble.classList.add("file-bubble");
      } catch (e) {
        console.error("Gagal parse JSON kontak:", e, msg.isi_pesan);
        const p = document.createElement("p");
        p.style.whiteSpace = "pre-wrap";
        p.style.marginBottom = "0";
        p.appendChild(
          document.createTextNode("[Kontak] " + msg.isi_pesan)
        );
        contentHTML = `<div class="message-content text-content">${p.outerHTML}</div>`;
      }
      break;
    default:
      const p = document.createElement("p");
      p.style.whiteSpace = "pre-wrap";
      p.style.marginBottom = "0";
      p.appendChild(document.createTextNode(msg.isi_pesan));
      contentHTML = `<div class="message-content text-content">${p.outerHTML}</div>`;
      break;
  }
  let statusIconHTML = "";
  if (!isUser && msg.status_pengiriman) {
    statusIconHTML = getStatusIcon(msg.status_pengiriman);
  }
  bubble.innerHTML = `
        ${contentHTML}
        <span class="message-time">
            ${formatTimestamp(msg.timestamp)}
            ${statusIconHTML} </span>
    `;
  return bubble;
}
function updateAllTimeAgoStrings() {
  const timeElements = document.querySelectorAll(".conversation-time-ago");
  if (timeElements.length === 0) {
    return;
  }
  timeElements.forEach((el) => {
    const timestamp = el.dataset.timestamp;
    if (timestamp) {
      try {
        const date = new Date(timestamp);
        const newTimeAgo = getTimeAgo(date);
        if (el.textContent !== newTimeAgo) {
          el.textContent = newTimeAgo;
        }
      } catch (e) {
        console.error("Error parsing timestamp for timeAgo:", e, timestamp);
      }
    }
  });
}
function formatLatestMessage(convo) {
  if (!convo.latest_message_type || !convo.latest_message_content) {
    return '<p class="text-xs text-gray-500 italic truncate">Belum ada pesan</p>';
  }
  let icon = "";
  let text = "";
  switch (convo.latest_message_type) {
    case "text":
      text = convo.latest_message_content;
      break;
    case "image":
      icon = '<i class="fas fa-image text-gray-400 mr-2"></i>';
      text = " Gambar";
      break;
    case "video":
      icon = '<i class="fas fa-video text-gray-400 mr-2"></i>';
      text = " Video";
      break;
    case "audio":
      icon = '<i class="fas fa-microphone text-gray-400 mr-2"></i>';
      text = " Pesan suara";
      break;
    case "document":
      icon = '<i class="fas fa-file-alt text-gray-400 mr-2"></i>';
      try {
        const docInfo = JSON.parse(convo.latest_message_content);
        text = docInfo.filename || "Dokumen";
      } catch (e) {
        text = " Dokumen";
      }
      break;
    case "contacts":
      icon = '<i class="fas fa-user-circle text-gray-400 mr-2"></i>';
      try {
        const contactInfo = JSON.parse(convo.latest_message_content);
        text = contactInfo.name || " Kontak";
      } catch (e) {
        text = " Kontak";
      }
      break;
    case "broadcast":
      icon = '<i class="fas fa-bullhorn text-purple-500 mr-2"></i>';
      const lines = convo.latest_message_content.split('\n');
      const templateLine = lines.find(line => line.trim().startsWith('Template:'));
      if (templateLine) {
        text = templateLine.substring(9).trim();
      } else {
        text = "Pesan Broadcast";
      }
      break;
    default:
      text = ` [${convo.latest_message_type}]`;
      break;
  }
  return `<div class="latest-message-preview text-xs text-gray-500 flex items-center truncate">
                ${icon}
                <span class="truncate"> ${text}</span>
            </div>`;
}
