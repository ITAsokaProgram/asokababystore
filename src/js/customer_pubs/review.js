// Get Cookie
import { getCookie } from "/src/js/index/utils/cookies.js";

// // Checking Direct Link Access
const userObject = [];
let currentBon = null;
let currentKasir = null; 
export const handleReviewClick = (bon, namaKasir) => {
  currentBon = bon;
  currentKasir = namaKasir; 
  openReviewModal(bon, "reviewModal");
};
// // Checking Direct Link Access
const token = getCookie("customer_token");
if (!token) {
  Swal.fire({
    icon: "error",
    title: "Akses Ditolak",
    text: "Anda harus login untuk mengakses halaman ini.",
    confirmButtonText: "Login",
    customClass: {
      popup: "rounded-xl shadow-xl border-2 border-pink-100",
      title: "text-pink-600 font-bold",
      content: "text-gray-600",
    },
  }).then(() => {
    window.location.href = "/log_in";
  });
} else {
  fetch(`/src/auth/verify_token_pubs?token=${token}`, {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
    },
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Sesi tidak valid atau telah kedaluwarsa.");
      }

      return response.json();
    })
    .then((data) => {
      if (data.status !== "success") {
        Swal.fire({
          icon: "error",
          title: "Akses Ditolak",
          text: data.message || "Anda harus login untuk mengakses halaman ini.",
          confirmButtonText: "Login",
          customClass: {
            popup: "rounded-xl shadow-xl border-2 border-pink-100",
            title: "text-pink-600 font-bold",
            content: "text-gray-600",
          },
        }).then(() => {
          window.location.href = "/log_in.php";
        });
      } else {
        userObject.push(data.data || data.user);
        const hp = userObject[0].no_hp;
        let qris = document.getElementById("qris");
        if(!qris) {
          qris = "";
        } else {
          qris.setAttribute("href", `/customer/qris?number=${hp}`);
        }
      }
    });
}

// ============ MODAL CONTROL ============

// Fungsi buka modal (panggil ini saat ingin buka modal dari tombol luar)
export function openReviewModal(bon, id) {
  const modal = document.getElementById(id);
  if (!bon) {
    modal.classList.add("hidden");
  } else {
    modal.classList.remove("hidden");
  }
}

// Tombol close modal
export const closeModalReview = (idModal, idBtn) => {
  const closeModalBtn = document.getElementById(idBtn);
  const modal = document.getElementById(idModal);
  closeModalBtn.addEventListener("click", () => {
    modal.classList.add("hidden");
    resetReviewForm(idModal);
  });
};

// Klik luar modal = tutup
export const closeModalOutside = (id) => {
  const modal = document.getElementById(id);
  window.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.classList.add("hidden");
      resetReviewForm(id);
    }
  });
};

// ============ RATING BINTANG ============
const starContainer = document.getElementById("starRating");
const reviewDetails = document.getElementById("reviewDetails");
let selectedRating = 0;

for (let i = 1; i <= 5; i++) {
  const star = document.createElement("span");
  star.innerHTML = "★";
  star.classList.add("text-gray-400", "text-4xl");
  star.dataset.value = i;

  star.addEventListener("click", function () {
    selectedRating = i;
    updateStars();
    reviewDetails.classList.remove("hidden"); // Tampilkan bagian komentar & foto
  });

  starContainer.appendChild(star);
}

function resetReviewForm(id) {
  const modal = document.getElementById(id);
  reviewForm.reset();
  currentBon = null;
  currentKasir = null;
  selectedRating = 0;
  selectedFiles = [];
  updateStars();
  updatePreview();
  reviewDetails.classList.add("hidden");
  photoInput.value = "";
  modal.classList.add("hidden");
}

function updateStars() {
  const stars = document.querySelectorAll("#starRating span");
  stars.forEach((star) => {
    const val = parseInt(star.dataset.value);
    star.classList.toggle("text-yellow-400", val <= selectedRating);
    star.classList.toggle("text-gray-400", val > selectedRating);
  });
}

// ============ FOTO CAMERA & VALIDASI (1 FOTO SAJA) ============
const photoInput = document.getElementById("photo");
const photoPreview = document.getElementById("photoPreview");
let selectedFiles = [];

photoInput.addEventListener("change", () => {
  const file = photoInput.files[0];
  if (!file) return;

  const allowedTypes = ["image/jpeg", "image/png"];
  if (!allowedTypes.includes(file.type)) {
    alert("Hanya gambar JPG, PNG, WEBP yang diperbolehkan.");
    return;
  }

  if (file.size > 10 * 1024 * 1024) {
    alert("Ukuran gambar maksimal 10MB.");
    return;
  }

  selectedFiles = [file];
  updatePreview();
  photoInput.value = "";
});

function updatePreview() {
  photoPreview.innerHTML = "";
  selectedFiles.forEach((file, idx) => {
    const wrapper = document.createElement("div");
    wrapper.className = "inline-block relative";

    const img = document.createElement("img");
    img.src = URL.createObjectURL(file);
    img.className = "max-h-32 rounded-md border";

    const removeBtn = document.createElement("button");
    removeBtn.type = "button";
    removeBtn.innerHTML = "&times;";
    removeBtn.className =
      "absolute top-1 right-1 bg-white bg-opacity-80 rounded-full text-red-500 text-lg w-6 h-6 flex items-center justify-center shadow hover:bg-red-100";
    removeBtn.style.zIndex = 10;
    removeBtn.onclick = function () {
      selectedFiles.splice(idx, 1);
      updatePreview();
    };

    wrapper.appendChild(img);
    wrapper.appendChild(removeBtn);
    photoPreview.appendChild(wrapper);
  });
}

// ============ FORM SUBMIT ============
export const postFormReview = () => {
  const reviewForm = document.getElementById("reviewForm");
  reviewForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const comment = document.getElementById("comment").value;

    const form = e.target;
    const formData = new FormData(form);

    const selectedTags = Array.from(
      form.querySelectorAll('input[name="tags[]"]:checked')
    ).map((tag) => tag.value);
    formData.append("tags", JSON.stringify(selectedTags));
    formData.append("rating", selectedRating);
    formData.append("comment", comment);
    formData.append("token", token);
    formData.append("user_id", userObject[0].id);
    formData.append("bon", currentBon);
    formData.append("nama_kasir", currentKasir);
    selectedFiles.forEach((file) => {
      formData.append("photos[]", file);
    });
    showLoading();
    console.log("SUCCESS")
    fetch("/src/api/review/send_review_pubs.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((result) => {
        if (result.status === "success") {
          Swal.fire({
            icon: "success",
            title: "Review Berhasil Dikirim",
            text: result.message,
            confirmButtonText: "OK",
            customClass: {
              popup: "rounded-xl shadow-xl border-2 border-pink-100",
              title: "text-pink-600 font-bold",
              content: "text-gray-600",
            },
          }).then(() => {
            hideLoading();
            window.location.reload();
          });

          // Reset form & tutup modal
          resetReviewForm("reviewModal");
        } else {
          hideLoading();
          Swal.fire("Gagal", result.message || "Terjadi kesalahan.", "error");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        Swal.fire("Error", "Gagal mengirim review.", "error");
        hideLoading();
      });
  });
};
function showLoading(message = "Mengirim review...") {
  const overlay = document.getElementById("loadingOverlay");
  overlay.querySelector("p").textContent = message;
  overlay.classList.remove("hidden");
}

function hideLoading() {
  document.getElementById("loadingOverlay").classList.add("hidden");
}
export default {
  openReviewModal,
  postFormReview,
  closeModalOutside,
  closeModalReview,
  handleReviewClick,
};
