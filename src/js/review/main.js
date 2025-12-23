import {
  getReviewData,
  submitReviewHandling,
  getReviewDetail,
  getReviewConversation,
  sendReviewMessage,
} from "./fetch.js";
import { renderPagination } from "./pagination.js";
import { renderTableReview } from "./table.js";
let currentFilter = {
  rating: "all",
  status: "all",
  page: 1,
  limit: 10,
};
let currentReviewId = null;
let isEditMode = false;
const updateHeaderStats = (stats) => {
  const totalReviewsEl = document.getElementById("totalReviews");
  const avgRatingEl = document.getElementById("avgRating");
  const pendingIssuesEl = document.getElementById("pendingIssues");
  if (stats) {
    totalReviewsEl.innerHTML = stats.total_reviews ?? "0";
    avgRatingEl.innerHTML = stats.avg_rating ?? "0.0";
    pendingIssuesEl.innerHTML = stats.pending_issues ?? "0";
  } else {
    totalReviewsEl.innerHTML = "-";
    avgRatingEl.innerHTML = "-";
    pendingIssuesEl.innerHTML = "-";
  }
};
const fetchAndRenderPage = async (page = 1) => {
  currentFilter.page = page;
  try {
    document.getElementById("tableLoading").classList.remove("hidden");
    document.querySelector("table").classList.add("hidden");
    document.getElementById(
      "paginationContainer"
    ).innerHTML = `<div class="animate-pulse h-8 bg-gray-200 rounded w-48"></div>`;
    const response = await getReviewData(
      currentFilter.page,
      currentFilter.limit,
      currentFilter.rating,
      currentFilter.status
    );
    if (response.stats) {
      updateHeaderStats(response.stats);
    }
    if (response.rating_counts) {
      updateRatingCardCounts(response.rating_counts);
    }
    const offset = (response.pagination.current_page - 1) * currentFilter.limit;
    renderTableReview(response.data, offset);
    renderPagination(response.pagination, fetchAndRenderPage);
  } catch (error) {
    console.error("Gagal memuat data review:", error);
    Toastify({
      text: "Gagal memuat data review",
      backgroundColor: "#EF4444",
    }).showToast();
  }
};
const setupRatingCardEvents = () => {
  const container = document.getElementById("ratingCardContainer");
  container.querySelectorAll(".rating-card").forEach((card) => {
    card.addEventListener("click", function () {
      container.querySelectorAll(".rating-card").forEach((c) => {
        c.classList.remove(
          "active",
          "bg-gradient-to-r",
          "from-gray-100",
          "to-gray-200",
          "border-yellow-400",
          "shadow-lg"
        );
        c.classList.add("bg-white", "hover:bg-yellow-50");
      });
      this.classList.remove("bg-white", "hover:bg-yellow-50");
      this.classList.add(
        "active",
        "bg-gradient-to-r",
        "from-gray-100",
        "to-gray-200",
        "border-yellow-400",
        "shadow-lg"
      );
      currentFilter.rating = this.dataset.rating;
      fetchAndRenderPage(1);
    });
  });
};
const setupStatusFilterEvent = () => {
  const statusFilter = document.getElementById("statusFilter");
  if (statusFilter) {
    statusFilter.addEventListener("change", function () {
      currentFilter.status = this.value;
      fetchAndRenderPage(1);
    });
  }
};
const updateRatingCardCounts = (counts) => {
  document.querySelectorAll(".rating-card").forEach((card) => {
    const rating = card.dataset.rating;
    const count = counts[rating] !== undefined ? counts[rating] : 0;
    const countSpan = card.querySelector(".ml-1.text-xs.text-gray-500");
    const pulseContainer = card.querySelector(".animate-pulse");
    if (countSpan) {
      countSpan.textContent = `(${count})`;
    }
    if (pulseContainer) {
      pulseContainer.classList.remove("animate-pulse");
    }
  });
};
window.openIssueHandlingModal = function (reviewId, reviewData) {
  isEditMode = false;
  currentReviewId = reviewId;
  document.getElementById("customerName").textContent = reviewData.nama || "-";
  document.getElementById("customerPhone").textContent =
    reviewData.handphone || "-";
  document.getElementById("reviewRating").textContent = reviewData.rating
    ? `${reviewData.rating} ⭐`
    : "-";
  document.getElementById("reviewDate").textContent = reviewData.tanggal || "-";
  document.getElementById("reviewComment").textContent =
    reviewData.komentar || "-";
  document.getElementById("issueHandlingForm").reset();
  const modalTitle = document.querySelector("#issueHandlingModal h2");
  if (modalTitle) {
    modalTitle.textContent = "Penanganan Masalah";
  }
  const issueModal = document.getElementById("issueHandlingModal");
  issueModal.classList.remove("hidden");
  document.body.style.overflow = "hidden";
};
window.editIssueHandling = async function (reviewId) {
  try {
    Swal.fire({
      title: "Memuat Data...",
      html: '<div class="flex justify-center items-center"><i class="fas fa-spinner fa-spin fa-2x text-orange-500"></i></div>',
      showConfirmButton: false,
      allowOutsideClick: false,
    });
    const result = await getReviewDetail(reviewId);
    if (result && result.success) {
      const data = result.data;
      isEditMode = true;
      currentReviewId = reviewId;
      document.getElementById("customerName").textContent =
        data.nama_lengkap || "-";
      document.getElementById("customerPhone").textContent = data.no_hp || "-";
      document.getElementById("reviewRating").textContent = data.rating
        ? `${data.rating} ⭐`
        : "-";
      document.getElementById("reviewDate").textContent = new Date(
        data.diperbarui_tgl
      ).toLocaleDateString("id-ID");
      document.getElementById("reviewComment").textContent =
        data.komentar || "-";
      document.getElementById("handlingStatus").value = data.status || "";
      document.getElementById("priority").value = data.prioritas || "";
      document.getElementById("issueCategory").value =
        data.kategori_masalah || "";
      document.getElementById("handlingDescription").value =
        data.deskripsi_penanganan || "";
      const modalTitle = document.querySelector("#issueHandlingModal h2");
      if (modalTitle) {
        modalTitle.textContent = "Edit Penanganan Masalah";
      }
      Swal.close();
      const issueModal = document.getElementById("issueHandlingModal");
      issueModal.classList.remove("hidden");
      document.body.style.overflow = "hidden";
    } else {
      Swal.fire({
        title: "Error",
        text: result.message || "Gagal memuat data penanganan",
        icon: "error",
        confirmButtonColor: "#EF4444",
      });
    }
  } catch (error) {
    console.error("Error loading edit data:", error);
    Swal.fire({
      title: "Terjadi Kesalahan",
      text: "Gagal memuat data untuk diedit. Silakan coba lagi.",
      icon: "error",
      confirmButtonColor: "#EF4444",
    });
  }
};
window.viewHandlingDetail = async function (reviewId) {
  try {
    Swal.fire({
      title: "Memuat Detail...",
      html: '<div class="flex justify-center items-center"><i class="fas fa-spinner fa-spin fa-2x text-orange-500"></i></div>',
      showConfirmButton: false,
      allowOutsideClick: false,
    });
    const result = await getReviewDetail(reviewId);
    if (result && result.success) {
      const data = result.data;
      const formattedDate = new Date(data.diperbarui_tgl).toLocaleString(
        "id-ID",
        {
          dateStyle: "long",
          timeStyle: "short",
        }
      );
      Swal.fire({
        title: `<div class="flex items-center text-2xl">
                            <div>
                                <i class="fas fa-file-alt mr-3 text-orange-500"></i>
                            </div>
                            <div>
                                Detail Penanganan Masalah
                            </div>
                        </div>`,
        html: `
                    <div class="text-left space-y-4 p-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                                <h4 class="font-semibold text-gray-800 text-md flex items-center mb-2">
                                    <i class="fas fa-user-circle text-orange-500 mr-3"></i>Informasi Pelanggan
                                </h4>
                                <p class="text-gray-600 text-sm"><strong>Nama:</strong> ${
                                  data.nama_lengkap || "-"
                                }</p>
                                <p class="text-gray-600 text-sm"><strong>No. HP:</strong> ${
                                  data.no_hp || "-"
                                }</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                                <h4 class="font-semibold text-gray-800 text-md flex items-center mb-2">
                                    <i class="fas fa-comment-alt text-orange-500 mr-3"></i>Detail Review
                                </h4>
                                <p class="text-gray-600 text-sm">
                                    <strong>Rating:</strong> 
                                    <span class="text-yellow-500 font-bold">${"⭐".repeat(
                                      data.rating
                                    )}</span> (${data.rating}/5)
                                </p>
                                <blockquote class="mt-2 border-l-4 border-gray-300 pl-4 italic text-gray-700 text-sm">
                                    ${data.komentar || "Tidak ada komentar."}
                                </blockquote>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-blue-50 p-4 rounded-xl text-center shadow-sm">
                                <p class="text-sm font-semibold text-blue-800">Status Penanganan</p>
                                <p class="text-lg font-bold text-blue-900 mt-1">${getStatusLabel(
                                  data.status
                                )}</p>
                            </div>
                            <div class="bg-orange-50 p-4 rounded-xl text-center shadow-sm">
                                <p class="text-sm font-semibold text-orange-800">Prioritas</p>
                                <p class="text-lg font-bold text-orange-900 mt-1">${getPriorityLabel(
                                  data.prioritas
                                )}</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                           <p class="text-sm font-semibold text-gray-700">Kategori Masalah</p>
                           <p class="text-md font-medium text-gray-900 mt-1">${getCategoryLabel(
                             data.kategori_masalah
                           )}</p>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-xl border-l-4 border-yellow-400">
                            <h4 class="font-semibold text-yellow-800 text-md flex items-center mb-2">
                                <i class="fas fa-clipboard-check text-yellow-600 mr-3"></i>Deskripsi Penanganan
                            </h4>
                            <p class="text-gray-800 mt-2 whitespace-pre-wrap text-sm">${
                              data.deskripsi_penanganan
                            }</p>
                        </div>
                        <div class="text-xs text-gray-500 text-center pt-2">
                             <i class="fas fa-clock mr-1"></i>Terakhir diperbarui: ${formattedDate}
                        </div>
                    </div>
                `,
        icon: false,
        confirmButtonText: '<i class="fas fa-times mr-2"></i>Tutup',
        confirmButtonColor: "#F97316",
        width: "650px",
        customClass: {
          title: "text-left",
        },
      });
    } else {
      Swal.fire({
        title: "Informasi",
        text: result.message || "Data penanganan tidak ditemukan.",
        icon: "info",
        confirmButtonColor: "#3B82F6",
      });
    }
  } catch (error) {
    console.error("Error fetching detail:", error);
    Swal.fire({
      title: "Terjadi Kesalahan",
      text: "Gagal memuat detail penanganan. Silakan coba lagi.",
      icon: "error",
      confirmButtonColor: "#EF4444",
    });
  }
};
let currentChatReviewId = null;
const renderChatConversation = (messages) => {
  const container = document.getElementById("chatConversationMessages");
  if (!messages || messages.length === 0) {
    container.innerHTML = `
            <div class="text-center text-gray-400 text-sm py-8">
                <i class="fas fa-comment-dots text-3xl mb-2"></i>
                <p>Belum ada percakapan</p>
            </div>
        `;
    return;
  }
  container.innerHTML = messages
    .map((msg) => {
      const isAdmin = msg.pengirim_type === "admin";
      const alignClass = isAdmin ? "justify-end" : "justify-start";
      const bgClass = isAdmin
        ? "bg-blue-500 text-white"
        : "bg-gray-200 text-gray-800";
      const time = new Date(msg.dibuat_tgl).toLocaleString("id-ID", {
        day: "2-digit",
        month: "short",
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
                      isAdmin ? "text-right" : "text-left"
                    }">
                        ${time}
                    </p>
                </div>
            </div>
        `;
    })
    .join("");
  scrollToBottomAdmin();
};
const loadChatConversation = async (reviewId) => {
  try {
    const result = await getReviewConversation(reviewId);
    if (result.success) {
      renderChatConversation(result.data);
    }
  } catch (error) {
    console.error("Error loading conversation:", error);
    Toastify({
      text: "Gagal memuat percakapan",
      duration: 2000,
      backgroundColor: "#EF4444",
    }).showToast();
  }
};
window.openChatModal = function (reviewId, reviewData) {
  currentChatReviewId = reviewId;
  document.getElementById("chatCustomerName").textContent =
    reviewData.nama || "-";
  document.getElementById("chatCustomerPhone").textContent =
    reviewData.handphone || "-";
  document.getElementById("chatCustomerRating").textContent = reviewData.rating
    ? `${reviewData.rating} ⭐`
    : "-";
  document.getElementById("chatCustomerComment").textContent =
    reviewData.komentar || "Tidak ada komentar";
  document.getElementById("chatMessageInput").value = "";
  const waButton = document.getElementById("sendWaButton");
  if (waButton) {
    let phone = (reviewData.handphone || "").replace(/[\s+-]/g, "");
    if (phone.startsWith("0")) {
      phone = "62" + phone.substring(1);
    }

    if (phone) {
      waButton.href = `https://wa.me/${phone}`;
      waButton.classList.remove("hidden");
    } else {
      waButton.href = "#";
      waButton.classList.add("hidden");
    }
  }
  const chatInputContainer = document.getElementById("chatInputContainer");
  const resolvedMessage = document.getElementById("chatResolvedMessage");
  if (reviewData.detail_status === "resolved") {
    chatInputContainer.classList.add("hidden");
    resolvedMessage.classList.remove("hidden");
  } else {
    chatInputContainer.classList.remove("hidden");
    resolvedMessage.classList.add("hidden");
  }
  loadChatConversation(reviewId);
  const chatModal = document.getElementById("chatModal");
  chatModal.classList.remove("hidden");
  document.body.style.overflow = "hidden";
};
function closeChatModal() {
  const chatModal = document.getElementById("chatModal");
  chatModal.classList.add("hidden");
  document.body.style.overflow = "auto";
  document.getElementById("chatMessageInput").value = "";
  currentChatReviewId = null;
}
let adminSelectedFile = null;
const attachBtnAdmin = document.getElementById("attachFileBtnAdmin");
const mediaInputAdmin = document.getElementById("mediaInputAdmin");
const previewContainerAdmin = document.getElementById(
  "imagePreviewContainerAdmin"
);
const previewImgAdmin = document.getElementById("imagePreviewAdmin");
const removePreviewBtnAdmin = document.getElementById(
  "removeImagePreviewAdmin"
);
if (attachBtnAdmin)
  attachBtnAdmin.addEventListener("click", () => mediaInputAdmin.click());
if (mediaInputAdmin)
  mediaInputAdmin.addEventListener("change", () => {
    const file = mediaInputAdmin.files[0];
    if (file && file.type.startsWith("image/")) {
      if (file.size > 5 * 1024 * 1024) {
        alert("Ukuran gambar maksimal adalah 5MB.");
        mediaInputAdmin.value = "";
        return;
      }
      adminSelectedFile = file;
      const reader = new FileReader();
      reader.onload = (e) => {
        previewImgAdmin.src = e.target.result;
        previewContainerAdmin.classList.remove("hidden");
      };
      reader.readAsDataURL(file);
    } else if (file) {
      alert("Hanya file gambar yang diizinkan.");
      mediaInputAdmin.value = "";
    }
  });
if (removePreviewBtnAdmin)
  removePreviewBtnAdmin.addEventListener("click", () => {
    adminSelectedFile = null;
    mediaInputAdmin.value = "";
    previewContainerAdmin.classList.add("hidden");
    previewImgAdmin.src = "";
  });

const sendChatMessageBtn = document.getElementById("sendChatMessageBtn");
const chatMessageInput = document.getElementById("chatMessageInput");
if (sendChatMessageBtn && chatMessageInput) {
  sendChatMessageBtn.addEventListener("click", async () => {
    const pesan = chatMessageInput.value.trim();
    if (!pesan && !adminSelectedFile) {
      Toastify({
        text: "Pesan atau gambar tidak boleh kosong!",
        duration: 2000,
        backgroundColor: "#EF4444",
      }).showToast();
      return;
    }
    if (!currentChatReviewId) {
      Toastify({
        text: "Review ID tidak ditemukan!",
        duration: 2000,
        backgroundColor: "#EF4444",
      }).showToast();
      return;
    }
    const originalText = sendChatMessageBtn.innerHTML;
    sendChatMessageBtn.innerHTML =
      '<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim...';
    sendChatMessageBtn.disabled = true;
    const formData = new FormData();
    formData.append("review_id", currentChatReviewId);
    formData.append("pesan", pesan);
    if (adminSelectedFile) {
      formData.append("media", adminSelectedFile);
    }
    try {
      const result = await sendReviewMessage(formData);
      if (result.success) {
        chatMessageInput.value = "";
        removePreviewBtnAdmin.click();
        await loadChatConversation(currentChatReviewId);
        scrollToBottomAdmin();
        Toastify({
          text: "Pesan berhasil dikirim!",
          duration: 2000,
          backgroundColor: "#10B981",
        }).showToast();
      } else {
        Toastify({
          text: result.message || "Gagal mengirim pesan",
          duration: 3000,
          backgroundColor: "#EF4444",
        }).showToast();
      }
    } catch (error) {
      Toastify({
        text: error.message || "Gagal mengirim pesan",
        duration: 3000,
        backgroundColor: "#EF4444",
      }).showToast();
    } finally {
      sendChatMessageBtn.innerHTML = originalText;
      sendChatMessageBtn.disabled = false;
    }
  });
  chatMessageInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      sendChatMessageBtn.click();
    }
  });
}
const closeChatModalBtn = document.getElementById("closeChatModal");
if (closeChatModalBtn) {
  closeChatModalBtn.addEventListener("click", closeChatModal);
}
const chatModal = document.getElementById("chatModal");
if (chatModal) {
  chatModal.addEventListener("click", (e) => {
    if (e.target === chatModal) {
      closeChatModal();
    }
  });
}
document.addEventListener("keydown", (e) => {
  if (
    e.key === "Escape" &&
    chatModal &&
    !chatModal.classList.contains("hidden")
  ) {
    closeChatModal();
  }
});
function getStatusLabel(status) {
  const labels = {
    pending: "⏳ Pending",
    in_progress: "🔄 Sedang Diproses",
    resolved: "✅ Selesai",
  };
  return labels[status] || status;
}
function getPriorityLabel(priority) {
  const labels = {
    low: "🟢 Rendah",
    medium: "🟡 Sedang",
    high: "🔴 Tinggi",
    urgent: "🚨 Urgent",
  };
  return labels[priority] || priority;
}
function getCategoryLabel(category) {
  const labels = {
    service: "👥 Pelayanan",
    product: "📦 Produk",
    payment: "💳 Pembayaran",
    delivery: "🚚 Pengiriman",
    technical: "🔧 Teknis",
    other: "📝 Lainnya",
  };
  return labels[category] || category;
}
const issueHandlingForm = document.getElementById("issueHandlingForm");
if (issueHandlingForm) {
  issueHandlingForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!currentReviewId) {
      Toastify({
        text: "Error: Review ID tidak ditemukan!",
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: "#EF4444",
      }).showToast();
      return;
    }
    const formData = new FormData(issueHandlingForm);
    const status = formData.get("status");
    const prioritas = formData.get("priority");
    const kategori_masalah = formData.get("category");
    const deskripsi_penanganan = formData.get("description");
    if (!status || !prioritas || !kategori_masalah || !deskripsi_penanganan) {
      Toastify({
        text: "Mohon lengkapi semua field yang diperlukan!",
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: "#EF4444",
      }).showToast();
      return;
    }
    const submitBtn = issueHandlingForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML =
      '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    submitBtn.disabled = true;
    try {
      const reviewData = {
        review_id: currentReviewId,
        status: status,
        prioritas: prioritas,
        kategori_masalah: kategori_masalah,
        deskripsi_penanganan: deskripsi_penanganan,
      };
      const result = await submitReviewHandling(reviewData);
      if (result.success) {
        const message = isEditMode
          ? "Data penanganan berhasil diperbarui!"
          : "Data penanganan berhasil disimpan!";
        Toastify({
          text: message,
          duration: 3000,
          gravity: "top",
          position: "right",
          backgroundColor: "#10B981",
        }).showToast();
        closeIssueHandlingModal();
        await fetchAndRenderPage(currentFilter.page);
      }
    } catch (error) {
      console.error("Error:", error);
      Toastify({
        text: error.message || "Gagal menyimpan data penanganan",
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: "#EF4444",
      }).showToast();
    } finally {
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    }
  });
}
function closeIssueHandlingModal() {
  const issueModal = document.getElementById("issueHandlingModal");
  issueModal.classList.add("hidden");
  document.body.style.overflow = "auto";
  issueHandlingForm.reset();
  currentReviewId = null;
  isEditMode = false;
}
const closeIssueModalBtn = document.getElementById("closeIssueModal");
const cancelIssueHandlingBtn = document.getElementById("cancelIssueHandling");
if (closeIssueModalBtn) {
  closeIssueModalBtn.addEventListener("click", closeIssueHandlingModal);
}
if (cancelIssueHandlingBtn) {
  cancelIssueHandlingBtn.addEventListener("click", closeIssueHandlingModal);
}
const issueModal = document.getElementById("issueHandlingModal");
if (issueModal) {
  issueModal.addEventListener("click", (e) => {
    if (e.target === issueModal) {
      closeIssueHandlingModal();
    }
  });
}
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape" && !issueModal.classList.contains("hidden")) {
    closeIssueHandlingModal();
  }
});
const scrollToBottomAdmin = () => {
  const container = document.getElementById("chatScrollContainer");
  if (container) {
    setTimeout(() => {
      container.scrollTop = container.scrollHeight;
    }, 100);
  }
};
const init = async () => {
  setupRatingCardEvents();
  setupStatusFilterEvent();
  await fetchAndRenderPage(1);
  Toastify({
    text: `Berhasil memuat halaman pertama`,
    duration: 2000,
    gravity: "top",
    position: "right",
    backgroundColor: "#10B981",
  }).showToast();
};
init();
