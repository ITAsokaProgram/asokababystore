import {
  closeModalOutside,
  closeModalReview,
  postFormReview,
  handleReviewClick,
} from "../../customer_pubs/review.js";
import fetchTransaksi from "../fetch/fetch_trans.js";
import getCookie from "../utils/cookies.js";
import {
  getReviewConversation,
  sendReviewMessage,
} from "../fetch/fetch_review_conv.js";

const rupiah = (value) =>
  new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR" }).format(
    value
  );

const formatTanggal = (tgl) => {
  const options = { day: "2-digit", month: "long", year: "numeric" };
  return new Date(tgl).toLocaleDateString("id-ID", options);
};
const token = getCookie("customer_token");
export const renderTransaksi = (data) => {
  const container = document.getElementById("transaksi-container");
  if (!container) return;

  container.innerHTML = data
    .slice(0, 3)
    .map((item) => {
      const stars = item.rating
        ? `<div class="flex items-center gap-1 text-yellow-400">
                    ${generateStars(item.rating)}
               </div>`
        : `<button class="btn-review text-pink-500 hover:underline text-md" data-bon="${
            item.no_faktur
          }"        data-kasir="${item.nama_kasir || ""}">
                Beri review
            </button>`;
      const unreadBadge =
        item.unread_count > 0
          ? `<span class="absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white">${item.unread_count}</span>`
          : "";

      // UPDATE DISINI: Menggunakan item.total_bayar
      return `
        <h3 class="text-sm font-semibold text-gray-600 mb-0">
            ${formatTanggal(item.tanggal)}
        </h3>
        <a href="/customer/history" class="block">
        <div class="relative bg-pink-50 border border-pink-100 rounded-xl p-4 shadow-sm hover:shadow-md transition">
            ${unreadBadge}
            <div class="flex justify-between items-center mb-2">
                <div>
                    <p class="font-medium text-gray-800">${item.no_faktur}</p>
                    <p class="text-xs text-gray-500">${
                      item.Nm_Store || "Toko Asoka"
                    }</p>
                </div>
                <span class="text-pink-600 font-bold text-sm">
                    ${rupiah(item.total_bayar)}
                </span>
            </div>
            <div class="flex justify-between items-center text-sm">
                ${stars}
            </div>
            </div>
            </a>
        `;
    })
    .join("");

  document.querySelectorAll(".btn-review").forEach((button) => {
    button.addEventListener("click", () => {
      const bon = button.dataset.bon;
      const namaKasir = button.dataset.kasir;
      handleReviewClick(bon, namaKasir);
    });
  });
  closeModalOutside("reviewModal");
  closeModalReview("reviewModal", "closeModal");
  postFormReview();
};

export const displayTransaksi = async () => {
  const kode = localStorage.getItem("kode");
  const data = await fetchTransaksi(token, kode);
  const countTrans = document.getElementById("transaction-count");
  if (countTrans) countTrans.textContent = (data.data && data.data.length) || 0;

  const loader = document.getElementById("transaksi-loader");
  if (loader) loader.style.display = "none";

  const container = document.getElementById("transaksi-container");
  if (!container) return;

  container.innerHTML = data.data
    .map((item) => {
      const stars = item.rating
        ? `<div class="flex items-center gap-1.5 text-yellow-400 mb-1">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                ${generateStars(item.rating)}
                <span class="text-xs text-gray-500 ml-1">(${item.rating})</span>
            </div>`
        : `<button class="btn-review font-bold group flex items-center gap-2 text-pink-500 text-lg font-medium transition-all duration-200 hover:scale-105" data-bon="${
            item.no_faktur
          }" data-kasir="${item.nama_kasir || ""}">
                <svg class="w-4 h-4 group-hover:animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                Beri Rating
            </button>`;

      const unreadBadge =
        item.unread_count > 0
          ? `<span class="absolute -top-1.5 -right-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white ring-2 ring-white/50">${item.unread_count}</span>`
          : "";
      const chatButton =
        (item.review_id && item.conversation_started) || item.detail_review_id
          ? `
      <button 
          onclick="window.openChatModal(${item.review_id}, '${item.no_faktur}', '${item.detail_status}')"
          data-review-id="${item.review_id}"
          class="relative group/btn flex items-center gap-2 bg-gradient-to-r from-blue-500 to-indigo-600
                   text-white px-4 py-2 rounded-lg font-medium text-sm
                   shadow-sm hover:shadow-md transition-all duration-200 hover:scale-105">
          <i class="fas fa-comments group-hover/btn:animate-tada"></i>
          Chat
          ${unreadBadge}
      </button>
      `
          : "";

      const commentSection = item.komentar
        ? `<div class="mt-4 p-4 bg-pink-50/70 border-l-4 border-pink-200 rounded-r-lg">
                  <div class="flex items-start gap-3">
                  <svg class="w-6 h-6 text-pink-300 flex-shrink-0 -mt-1" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M6 17h3l2-4V7H5v6h3l-2 4zm8 0h3l2-4V7h-6v6h3l-2 4z"/>
                  </svg>
                  <p class="text-sm text-gray-700 italic">
                      ${item.komentar}
                       
                  </p>
                  </div>
              </div>`
        : "";

      const saranButton =
        item.rating &&
        item.rating <= 3 &&
        (!item.detail_status ||
          (item.detail_status !== "resolved" &&
            item.detail_status !== "closed"))
          ? `
            <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
                <i class="fab fa-whatsapp text-2xl text-green-500 flex-shrink-0"></i>
                <div>
                    <p class="text-sm text-gray-700">
                        Jika ingin ditanggapi dengan cepat,
                        <a href="https://wa.me/62817171212" target="_blank" class="font-bold text-green-600 hover:underline">
                            klik disini untuk menghubungi Customer Service kami.
                        </a>
                    </p>
                </div>
            </div>
            `
          : "";

      // UPDATE DISINI: Menggunakan item.total_bayar
      return `
    <div class="mb-6 group">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-2 h-2 bg-pink-400 rounded-full animate-pulse"></div>
            <h3 class="text-sm font-semibold text-gray-600 tracking-wide">
                ${formatTanggal(item.tanggal)}
            </h3>
            <div class="flex-1 h-px bg-gradient-to-r from-pink-200 to-transparent"></div>
        </div>
        
        <div class="bg-white border border-pink-100 rounded-2xl p-5 shadow-sm hover:shadow-lg transition-all duration-300 group-hover:scale-[1.02]">
            
            <div class="flex justify-between items-start mb-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="font-bold text-gray-800 text-sm tracking-tight">
                            ${item.no_faktur}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p class="text-xs text-gray-500 font-medium">
                            ${item.Nm_Store}
                        </p>
                    </div>
                    <div class="flex items-end">
                    <div class="bg-gradient-to-r from-blue-500 to-emerald-600 text-white px-3 py-1.5 rounded-lg shadow-sm">
                        <span class="font-bold text-sm">
                            ${rupiah(item.total_bayar)}
                        </span>
                    </div>
                </div>
                </div>
                
                
            </div>
            
            <div class="mb-4">
                ${stars}
                ${commentSection}
                ${saranButton} 
            </div>

            
            <div class="flex justify-between items-center pt-3 border-t border-pink-100">
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Transaksi berhasil</span>
                </div>
                
                <div class="flex items-center gap-2"> 
                    ${chatButton}
                    <a href="/transaksi?kode=${item.no_faktur}&member=${kode}"
                    id="struk"
                    data-bon="${item.no_faktur}"
                    target="_blank"
                    class="group/btn flex items-center gap-2 bg-gradient-to-r from-pink-500 to-pink-600 hover:from-pink-600 hover:to-pink-700
                            text-white px-4 py-2 rounded-lg font-medium text-sm
                            shadow-sm hover:shadow-md transition-all duration-200 hover:scale-105">
                    <svg class="w-4 h-4 group-hover/btn:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Lihat Struk
                </a>
              </div>
            </div>
        </div>
    </div>
`;
    })
    .join("");

  document.querySelectorAll(".btn-review").forEach((button) => {
    const bon = button.dataset.bon;
    const namaKasir = button.dataset.kasir;
    button.addEventListener("click", () => {
      handleReviewClick(bon, namaKasir);
    });
  });

  closeModalOutside("reviewModal");
  closeModalReview("reviewModal", "closeModal");
  postFormReview();
};

const generateStars = (count) => {
  const maxStars = 5;
  let html = "";
  for (let i = 1; i <= maxStars; i++) {
    if (i <= count) {
      html += `<i class="fas fa-star text-yellow-400"></i>`;
    } else {
      html += `<i class="far fa-star text-yellow-300"></i>`;
    }
  }
  return html;
};

let currentChatReviewId = null;

const renderChatConversation = (messages) => {
  const container = document.getElementById("chatConversationMessagesCust");
  if (!messages || messages.length === 0) {
    container.innerHTML = `<div class="text-center text-gray-400 py-8"><i class="fas fa-comment-slash text-3xl mb-2"></i><p>Belum ada percakapan.</p></div>`;
    return;
  }

  container.innerHTML = messages
    .map((msg) => {
      const isCustomer = msg.pengirim_type === "customer";
      const alignClass = isCustomer ? "justify-end" : "justify-start";
      const bgClass = isCustomer
        ? "bg-pink-500 text-white"
        : "bg-gray-200 text-gray-800";
      const time = new Date(msg.dibuat_tgl).toLocaleString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
      });

      let bubbleContent = "";
      let bubblePadding = "";

      if (msg.tipe_pesan === "image") {
        bubblePadding = "p-2";
        bubbleContent = `
                <a href="${
                  msg.media_url
                }" target="_blank" rel="noopener noreferrer" class="cursor-pointer">
                    <img src="${
                      msg.media_url
                    }" alt="Lampiran" class="rounded-lg mb-2 max-h-48 w-full object-cover">
                </a>
                ${
                  msg.pesan
                    ? `<p class="text-sm whitespace-pre-wrap break-words px-1">${msg.pesan}</p>`
                    : ""
                }
            `;
      } else {
        bubblePadding = "px-4 py-2";
        bubbleContent = `
                <p class="text-sm whitespace-pre-wrap break-words">${msg.pesan}</p>
            `;
      }

      return `
            <div class="flex ${alignClass} animate-fade-in-up">
                <div class="max-w-xs md:max-w-md lg:max-w-lg mb-4">
                    <div class="${bgClass} ${bubblePadding} rounded-lg shadow-sm">
                        ${bubbleContent}
                    </div>
                    <p class="text-xs text-gray-400 mt-1 ${
                      isCustomer ? "text-right" : "text-left"
                    }">
                        ${time}
                    </p>
                </div>
            </div>
        `;
    })
    .join("");

  scrollToBottomCust();
};

const loadChatConversation = async (reviewId) => {
  try {
    const result = await getReviewConversation(reviewId);
    if (result.success) {
      renderChatConversation(result.data);
    } else {
      document.getElementById(
        "chatConversationMessagesCust"
      ).innerHTML = `<p class="text-red-500 text-center">${result.message}</p>`;
    }
  } catch (error) {
    console.error("Gagal memuat percakapan:", error);
  }
};

window.openChatModal = function (reviewId, noBon, detailStatus) {
  currentChatReviewId = reviewId;
  document.getElementById("chatBonCust").textContent = `No. Bon: ${noBon}`;
  document.getElementById("chatMessageInputCust").value = "";

  const chatInputContainer = document.getElementById("chatInputContainerCust");
  const resolvedMessage = document.getElementById("chatResolvedMessageCust");

  if (detailStatus === "resolved") {
    chatInputContainer.classList.add("hidden");
    resolvedMessage.classList.remove("hidden");
  } else {
    chatInputContainer.classList.remove("hidden");
    resolvedMessage.classList.add("hidden");
  }

  loadChatConversation(reviewId);
  document.getElementById("chatModalCust").classList.remove("hidden");
};

function closeChatModal() {
  document.getElementById("chatModalCust").classList.add("hidden");
  if (currentChatReviewId) {
    const chatButton = document.querySelector(
      `button[data-review-id='${currentChatReviewId}']`
    );
    if (chatButton) {
      const badge = chatButton.querySelector("span.absolute");
      if (badge) {
        badge.remove();
      }
    }
  }
  currentChatReviewId = null;
}

document.addEventListener("DOMContentLoaded", () => {
  const chatModal = document.getElementById("chatModalCust");
  const closeBtn = document.getElementById("closeChatModalCust");
  const sendBtn = document.getElementById("sendChatMessageBtnCust");
  const input = document.getElementById("chatMessageInputCust");

  // Elemen baru
  const attachBtn = document.getElementById("attachFileBtnCust");
  const mediaInput = document.getElementById("mediaInputCust");
  const previewContainer = document.getElementById("imagePreviewContainerCust");
  const previewImg = document.getElementById("imagePreviewCust");
  const removePreviewBtn = document.getElementById("removeImagePreviewCust");

  let selectedFile = null;

  if (closeBtn) closeBtn.addEventListener("click", closeChatModal);
  if (chatModal)
    chatModal.addEventListener("click", (e) => {
      if (e.target === chatModal) closeChatModal();
    });

  // --- LOGIKA BARU UNTUK FILE ---
  if (attachBtn) attachBtn.addEventListener("click", () => mediaInput.click());

  if (mediaInput)
    mediaInput.addEventListener("change", () => {
      const file = mediaInput.files[0];
      if (file && file.type.startsWith("image/")) {
        if (file.size > 5 * 1024 * 1024) {
          // 5MB
          alert("Ukuran gambar maksimal adalah 5MB.");
          mediaInput.value = ""; // Reset input
          return;
        }
        selectedFile = file;
        const reader = new FileReader();
        reader.onload = (e) => {
          previewImg.src = e.target.result;
          previewContainer.classList.remove("hidden");
        };
        reader.readAsDataURL(file);
      } else if (file) {
        alert("Hanya file gambar yang diizinkan.");
        mediaInput.value = ""; // Reset input
      }
    });

  if (removePreviewBtn)
    removePreviewBtn.addEventListener("click", () => {
      selectedFile = null;
      mediaInput.value = ""; // Reset input file
      previewContainer.classList.add("hidden");
      previewImg.src = "";
    });
  // --- AKHIR LOGIKA BARU ---

  // Modifikasi fungsi sendMessage
  const sendMessage = async () => {
    const pesan = input.value.trim();

    // Cek jika pesan dan file kosong
    if (!pesan && !selectedFile) {
      alert("Silakan tulis pesan atau pilih gambar.");
      return;
    }
    if (!currentChatReviewId) return;

    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    // Buat FormData
    const formData = new FormData();
    formData.append("review_id", currentChatReviewId);
    formData.append("pesan", pesan);
    if (selectedFile) {
      formData.append("media", selectedFile);
    }

    try {
      // Kirim FormData
      const result = await sendReviewMessage(formData);
      if (result.success) {
        input.value = "";
        // Reset file preview
        removePreviewBtn.click();
        await loadChatConversation(currentChatReviewId);
      } else {
        alert("Gagal mengirim pesan: " + result.message);
      }
    } catch (error) {
      console.error(error);
      alert("Terjadi kesalahan saat mengirim pesan.");
    } finally {
      sendBtn.disabled = false;
      sendBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i> Kirim';
    }
  };

  if (sendBtn) sendBtn.addEventListener("click", sendMessage);
  if (input)
    input.addEventListener("keydown", (e) => {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    });
});

const scrollToBottomCust = () => {
  const container = document.getElementById("chatScrollContainerCust");
  if (container) {
    setTimeout(() => {
      container.scrollTop = container.scrollHeight;
    }, 100);
  }
};

export default renderTransaksi;
