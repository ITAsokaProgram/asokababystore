/**
 * Modals Component
 * Handles all modal-related functionality
 */

/**
 * Show a modal
 * @param {HTMLElement} modal - The modal element to show
 */
export const showModal = (modal) => {
  if (!modal) return;

  modal.classList.remove("hidden");
  modal.classList.add("flex");
  document.body.style.overflow = "hidden";
};

/**
 * Close a modal
 * @param {HTMLElement} modal - The modal element to close
 */
export const closeModal = (modal) => {
  if (!modal) return;

  modal.classList.add("hidden");
  modal.classList.remove("flex");
  document.body.style.overflow = "auto";
};

/**
 * Show reward preview modal
 * @param {Object} reward - The reward to preview
 * @param {HTMLElement} elements - DOM elements
 */
export const showRewardPreviewModal = (reward, elements, userPoints) => {
  if (!reward || !elements) return;

  const {
    previewImage,
    previewTitle,
    previewDescription,
    previewPoints,
    previewStock,
    exchangeFromPreviewBtn,
  } = elements;

  if (previewImage)
    previewImage.src =
      reward.image || "https://via.placeholder.com/400x300?text=No+Image";
  if (previewTitle) previewTitle.textContent = reward.name;
  if (previewDescription) previewDescription.textContent = reward.description;
  if (previewPoints)
    previewPoints.textContent = reward.points.toLocaleString("id-ID");

  // Update stock display
  if (previewStock) {
    previewStock.textContent = `${reward.stock} tersisa`;
    const stockClass = reward.stock <= 5 ? "text-red-500" : "text-green-600";
    previewStock.className = `text-sm font-medium ${stockClass}`;
  }

  // Update exchange button state
  if (exchangeFromPreviewBtn) {
    const canAfford = userPoints >= reward.points;
    const isOutOfStock = reward.stock === 0;

    if (isOutOfStock) {
      exchangeFromPreviewBtn.textContent = "❌ Stok Habis";
      exchangeFromPreviewBtn.className =
        "w-full py-3 rounded-xl font-medium text-sm bg-gray-200 text-gray-500 cursor-not-allowed";
      exchangeFromPreviewBtn.disabled = true;
    } else if (canAfford) {
      exchangeFromPreviewBtn.textContent = "🎁 Tukar Sekarang";
      exchangeFromPreviewBtn.className =
        "w-full py-3 rounded-xl font-medium text-sm bg-gradient-to-r from-blue-500 to-purple-600 text-white hover:from-blue-600 hover:to-purple-700 transition-colors";
      exchangeFromPreviewBtn.disabled = false;
    } else {
      exchangeFromPreviewBtn.textContent = "💰 Poin Tidak Cukup";
      exchangeFromPreviewBtn.className =
        "w-full py-3 rounded-xl font-medium text-sm bg-gray-100 text-gray-500 cursor-not-allowed";
      exchangeFromPreviewBtn.disabled = true;
    }
  }

  showModal(elements.rewardPreviewModal);
};

/**
 * Show success modal with reward code
 * @param {string} message - Success message
 * @param {string} rewardCode - The reward code to display
 * @param {HTMLElement} elements - DOM elements
 */
export const showSuccessModal = (message, rewardCode, qrRewardCode, elements) => {
  const { successMessage, rewardCode: codeElement , qrRewardCode: codeQR} = elements;

  if (successMessage) successMessage.textContent = message;
  if (codeElement) codeElement.textContent = rewardCode;
  if (codeQR) codeQR.src = qrRewardCode;

  showModal(elements.successModal);
};

/**
 * Show error modal
 * @param {string} message - Error message
 * @param {HTMLElement} elements - DOM elements
 */
export const showErrorModal = (message, elements) => {
  const { errorMessage } = elements;

  if (errorMessage) errorMessage.textContent = message;
  showModal(elements.errorModal);
};

/**
 * Show confirmation modal for reward exchange
 * @param {Object} reward - The reward to confirm exchange for
 * @param {HTMLElement} elements - DOM elements
 */
export const showConfirmModal = (reward, elements) => {
  const { confirmEmoji, confirmRewardName, confirmPoints } =
    elements;

  if (confirmEmoji) confirmEmoji.textContent = "🎁";
  if (confirmRewardName) confirmRewardName.textContent = reward.name;
  if (confirmPoints)
    confirmPoints.textContent = reward.points.toLocaleString("id-ID");

  showModal(elements.confirmModal);
};
