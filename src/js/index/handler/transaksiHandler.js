import {
  closeModalOutside,
  closeModalReview,
  postFormReview,
  handleReviewClick,
} from "../../customer_pubs/review.js";
import fetchTransaksi from "../fetch/fetch_trans.js";
import getCookie from "../utils/cookies.js";

const rupiah = (value) =>
  new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR" }).format(
    value
  );

const formatTanggal = (tgl) => {
  const options = { day: "2-digit", month: "long", year: "numeric" };
  return new Date(tgl).toLocaleDateString("id-ID", options);
};
const token = getCookie("token");

/* Render kecil (preview) - light mode only */
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
        : `<button class="btn-review text-pink-500 hover:underline text-md" data-bon="${item.no_faktur}">
                   Beri review
               </button>`;

      return `
        <h3 class="text-sm font-semibold text-gray-600 mb-0">
            ${formatTanggal(item.tanggal)}
        </h3>
        <a href="/customer/history" class="block">
        <div class="bg-pink-50 border border-pink-100 rounded-xl p-4 shadow-sm hover:shadow-md transition">
            <div class="flex justify-between items-center mb-2">
                <div>
                    <p class="font-medium text-gray-800">${item.no_faktur}</p>
                    <p class="text-xs text-gray-500">${
                      item.Nm_Store || "Toko Asoka"
                    }</p>
                </div>
                <span class="text-pink-600 font-bold text-sm">
                    ${rupiah(item.belanja)}
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

  // attach handlers
  document.querySelectorAll(".btn-review").forEach((button) => {
    button.addEventListener("click", () => {
      const bon = button.dataset.bon;
      handleReviewClick(bon);
    });
  });
  closeModalOutside("reviewModal");
  closeModalReview("reviewModal", "closeModal");
  postFormReview();
};

/* Render list transaksi lengkap - light mode only */
export const displayTransaksi = async () => {
  const kode = localStorage.getItem("kode");
  const data = await fetchTransaksi(token, kode);
  const countTrans = document.getElementById("transaction-count");
  if (countTrans) countTrans.textContent = (data.data && data.data.length) || 0;

  // Sembunyikan loader setelah data dirender
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
        : `<button class="btn-review font-bold group flex items-center gap-2 text-pink-500 text-lg font-medium transition-all duration-200 hover:scale-105" data-bon="${item.no_faktur}">
               <svg class="w-4 h-4 group-hover:animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
               </svg>
               Beri Rating
           </button>`;

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
            
            <!-- Header Section -->
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
                            ${rupiah(item.belanja)}
                        </span>
                    </div>
                </div>
                </div>
                
                
            </div>
            
            <!-- Rating/Review Section -->
            <div class="mb-4">
                ${stars}
            </div>
            
            <!-- Action Section -->
            <div class="flex justify-between items-center pt-3 border-t border-pink-100">
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Transaksi berhasil</span>
                </div>
                
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
`;
    })
    .join("");

  // attach handlers after render
  document.querySelectorAll(".btn-review").forEach((button) => {
    const bon = button.dataset.bon;
    button.addEventListener("click", () => {
      handleReviewClick(bon);
    });
  });

  closeModalOutside("reviewModal");
  closeModalReview("reviewModal", "closeModal");
  postFormReview();
};

// Fungsi bantu untuk buat ikon bintang (light-only)
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

export default renderTransaksi;
