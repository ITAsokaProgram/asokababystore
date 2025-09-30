import { getReviewData } from "./fetch.js";
import { renderPagination } from "./pagination.js";
import { renderTableReview } from "./table.js";
let currentFilter = {
    rating: "all",
    page: 1,
    limit: 10
};
const fetchAndRenderPage = async (page = 1) => {
    currentFilter.page = page;
    try {
        document.getElementById("tableLoading").classList.remove("hidden");
        document.querySelector("table").classList.add("hidden");
        document.getElementById("paginationContainer").innerHTML = `<div class="animate-pulse h-8 bg-gray-200 rounded w-48"></div>`;
        const response = await getReviewData(currentFilter.page, currentFilter.limit, currentFilter.rating);
        if (response.rating_counts) {
            updateRatingCardCounts(response.rating_counts);
        }
        const offset = (response.pagination.current_page - 1) * currentFilter.limit;
        renderTableReview(response.data, offset);
        renderPagination(response.pagination, fetchAndRenderPage);
    } catch (error) {
        console.error('Gagal memuat data review:', error);
        Toastify({ text: "Gagal memuat data review", backgroundColor: "#EF4444" }).showToast();
    }
};
const setupRatingCardEvents = () => {
    const container = document.getElementById("ratingCardContainer");
    container.querySelectorAll(".rating-card").forEach(card => {
        card.addEventListener("click", function() {
            container.querySelectorAll(".rating-card").forEach(c => {
                c.classList.remove("active", "bg-gradient-to-r", "from-gray-100", "to-gray-200", "border-yellow-400", "shadow-lg");
                c.classList.add("bg-white", "hover:bg-yellow-50");
            });
            this.classList.remove("bg-white", "hover:bg-yellow-50");
            this.classList.add("active", "bg-gradient-to-r", "from-gray-100", "to-gray-200", "border-yellow-400", "shadow-lg");
            currentFilter.rating = this.dataset.rating;
            fetchAndRenderPage(1);
        });
    });
};
const updateRatingCardCounts = (counts) => {
    document.querySelectorAll(".rating-card").forEach(card => {
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
const init = async () => {
    setupRatingCardEvents();
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