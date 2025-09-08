import { getReviewData } from "./fetch.js";
import { paginationReviewInternal } from "./pagination.js";
import { renderTableReview } from "./table.js";

let allReviews = [];

const renderTableByRating = (rating) => {
  let filtered = allReviews;
  if (rating !== "all") {
    filtered = allReviews.filter(r => String(r.rating) === String(rating));
  }
  paginationReviewInternal(1, 10, filtered, renderTableReview);
  
  // Update statistics based on filtered data
  updateFilteredStatistics(filtered);
};

const updateFilteredStatistics = (filteredData) => {
  // Update total reviews for current filter
  const totalReviewsElement = document.getElementById("totalReviews");
  if (totalReviewsElement) {
    totalReviewsElement.innerHTML = filteredData.length;
  }

  // Update average rating for current filter
  const avgRatingElement = document.getElementById("avgRating");
  if (avgRatingElement && filteredData.length > 0) {
    const avgRating = (filteredData.reduce((sum, review) => sum + review.rating, 0) / filteredData.length).toFixed(1);
    avgRatingElement.innerHTML = avgRating;
  }

  // Update pending issues for current filter
  const pendingIssuesElement = document.getElementById("pendingIssues");
  if (pendingIssuesElement) {
    const pendingCount = filteredData.filter(r => r.rating <= 4).length;
    pendingIssuesElement.innerHTML = pendingCount;
  }
};

// Function to clear loading states
const clearLoadingStates = () => {
  // Clear viewData loading
  const viewDataElement = document.getElementById("viewData");
  if (viewDataElement) {
    viewDataElement.innerHTML = "";
  }
  
  // Clear pagination loading
  const paginationContainer = document.getElementById("paginationContainer");
  if (paginationContainer) {
    paginationContainer.innerHTML = "";
  }
};

const updateCardCounts = () => {
  const container = document.getElementById("ratingCardContainer");
  for (let i = 1; i <= 5; i++) {
    const card = container.querySelector(`.rating-card[data-rating='${i}']`);
    if (card) {
      const count = allReviews.filter(r => String(r.rating) === String(i)).length;
      card.innerHTML = `<i class="fa fa-star text-yellow-400"></i> <span class="font-medium">${i} Bintang</span> <span class="ml-1 text-xs text-gray-500">(${count})</span>`;
    }
  }
  // Card semua
  const allCard = container.querySelector(`.rating-card[data-rating='all']`);
  if (allCard) {
    allCard.innerHTML = `<i class="fas fa-list text-gray-600"></i> <span class="font-medium">Semua</span> <span class="ml-1 text-xs text-gray-500">(${allReviews.length})</span>`;
  }
};

const showLoadingCards = () => {
  const container = document.getElementById("ratingCardContainer");
  container.querySelectorAll(".rating-card").forEach(card => {
    card.innerHTML = `<div class="animate-pulse flex items-center space-x-2">
      <div class="w-4 h-4 bg-gray-300 rounded"></div>
      <div class="w-16 h-4 bg-gray-300 rounded"></div>
    </div>`;
  });
};

const updateStatistics = () => {
  // Update total reviews
  const totalReviewsElement = document.getElementById("totalReviews");
  if (totalReviewsElement) {
    totalReviewsElement.innerHTML = allReviews.length;
  }

  // Update average rating
  const avgRatingElement = document.getElementById("avgRating");
  if (avgRatingElement && allReviews.length > 0) {
    const avgRating = (allReviews.reduce((sum, review) => sum + review.rating, 0) / allReviews.length).toFixed(1);
    avgRatingElement.innerHTML = avgRating;
  }

  // Update pending issues (reviews with rating <= 4)
  const pendingIssuesElement = document.getElementById("pendingIssues");
  if (pendingIssuesElement) {
    const pendingCount = allReviews.filter(r => r.rating <= 4).length;
    pendingIssuesElement.innerHTML = pendingCount;
  }
};

const setupRatingCardEvents = () => {
  const container = document.getElementById("ratingCardContainer");
  container.querySelectorAll(".rating-card").forEach(card => {
    card.addEventListener("click", function() {
      // Remove highlight dari semua card
      container.querySelectorAll(".rating-card").forEach(c => {
        c.classList.remove("active", "bg-gradient-to-r", "from-gray-100", "to-gray-200", "border-yellow-400", "shadow-lg");
        c.classList.add("bg-white");
      });
      // Highlight card aktif
      this.classList.remove("bg-white");
      this.classList.add("active", "bg-gradient-to-r", "from-gray-100", "to-gray-200", "border-yellow-400", "shadow-lg");
      // Render tabel
      renderTableByRating(this.dataset.rating);
    });
  });
};

const init = async () => {
  try {
    // Show loading state
    showLoadingCards();
    
    const response = await getReviewData();
    allReviews = response.data;
    
    updateCardCounts();
    updateStatistics();
    clearLoadingStates();
    setupRatingCardEvents();
    renderTableByRating("all");
    paginationReviewInternal(1, 10, response.data, renderTableReview);
    
    // Initialize medium zoom

    // Add loading animation - remove this since we don't want body opacity loading
    // document.body.classList.add('opacity-100');
    
    // Show success notification
    Toastify({
      text: `Berhasil memuat ${allReviews.length} data review`,
      duration: 2000,
      gravity: "top",
      position: "right",
      backgroundColor: "#10B981",
      stopOnFocus: true
    }).showToast();
  } catch (error) {
    console.error('Error initializing review page:', error);
    Toastify({
      text: "Gagal memuat data review",
      duration: 3000,
      gravity: "top",
      position: "right",
      backgroundColor: "#EF4444",
      stopOnFocus: true
    }).showToast();
  }
};

init();
