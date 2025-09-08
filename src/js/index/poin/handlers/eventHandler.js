/**
 * Event Handler
 * Handles document events and event delegation
 */

import { state, setPendingReward } from "../state.js";
import { elements } from "../dom.js";
import { showRewardPreviewModal, closeModal } from "../components/Modals.js";
import { closeHistoryModal, openHistoryModal } from "../components/History.js";
import { handleExchange, confirmExchange, copyCode } from "./rewardHandler.js";
import { handleFilterClick } from "./filterHandler.js";

/**
 * Handle document click events with delegation
 * @param {Function} updatePointsCallback - Callback to update points display
 * @param {Function} renderRewardsCallback - Callback to render rewards
 */
export const handleDocumentClick = (updatePointsCallback, renderRewardsCallback) => {
  return (e) => {
    // Handle reward preview
    const previewBtn = e.target.closest("[data-preview-id]");
    if (previewBtn) {
      const rewardId = parseInt(previewBtn.getAttribute("data-preview-id"));
      const reward = state.rewards.find((r) => r.id === rewardId);
      if (reward) {
        setPendingReward(reward);
        showRewardPreviewModal(reward, {
          ...elements,
        }, state.userPoints);
      }
      return;
    }

    // Handle exchange button
    const exchangeBtn = e.target.closest(".tukar-btn");
    if (exchangeBtn && !exchangeBtn.disabled) {
      const rewardId = parseInt(exchangeBtn.getAttribute("data-reward-id"));
      if (rewardId) {
        const reward = state.rewards.find((r) => r.id === rewardId);
        if (reward) {
          setPendingReward(reward);
          handleExchange(reward);
        }
      }
      return;
    }

    // Handle modal close buttons
    const closeButtons = {
      closeRewardPreviewBtn: () => closeModal(elements.rewardPreviewModal),
      closeConfirmModalBtn: () => closeModal(elements.confirmModal),
      closeModalBtn: () => closeModal(elements.successModal),
      closeErrorModalBtn: () => closeModal(elements.errorModal),
      closeHistoryModalBtn: () => closeHistoryModal(),
    };

    for (const [id, handler] of Object.entries(closeButtons)) {
      if (e.target.closest(`#${id}`)) {
        handler();
        return;
      }
    }

    // Handle action buttons
    if (e.target.id === "exchangeFromPreviewBtn" && !e.target.disabled) {
      closeModal(elements.rewardPreviewModal);
      if (state.pendingReward) {
        handleExchange(state.pendingReward);
      }
    } else if (e.target.id === "confirmExchangeBtn" && !e.target.disabled) {
      confirmExchange(updatePointsCallback, renderRewardsCallback);
    } else if (e.target.id === "copyCodeBtn") {
      copyCode();
    }
  };
};

/**
 * Setup all event listeners
 * @param {Function} renderRewardsCallback - Callback to render rewards
 */
export const setupEventListeners = (renderRewardsCallback) => {
  // Event delegation for reward previews and actions
  const updatePointsCallback = () => {
    if (elements.userPoints) {
      elements.userPoints.textContent = state.userPoints.toLocaleString("id-ID");
    }
    if (elements.pointsDisplay) {
      elements.pointsDisplay.textContent = state.userPoints.toLocaleString("id-ID");
    }
  };

  document.addEventListener("click", handleDocumentClick(updatePointsCallback, renderRewardsCallback));

  // Modal close handlers
  elements.rewardPreviewModal?.addEventListener("click", (e) => {
    if (e.target === elements.rewardPreviewModal)
      closeModal(elements.rewardPreviewModal);
  });

  elements.successModal?.addEventListener("click", (e) => {
    if (e.target === elements.successModal) closeModal(elements.successModal);
  });

  elements.errorModal?.addEventListener("click", (e) => {
    if (e.target === elements.errorModal) closeModal(elements.errorModal);
  });

  elements.confirmModal?.addEventListener("click", (e) => {
    if (e.target === elements.confirmModal) closeModal(elements.confirmModal);
  });

  // Button event listeners
  elements.goBackBtn?.addEventListener("click", goBack);
  elements.historyFloatingBtn?.addEventListener("click", openHistoryModal);

  // Filter buttons
  if (elements.filterButtons?.length) {
    elements.filterButtons.forEach((btn) => {
      btn.addEventListener("click", () => handleFilterClick(btn, renderRewardsCallback));
    });
  }
};

/**
 * Handle back navigation
 */
export const goBack = () => {
  window.history.back();
};

/**
 * Prevent zoom on double tap for mobile devices
 */
export const setupDoubleTapPrevention = () => {
  let lastTouchEnd = 0;
  document.addEventListener(
    "touchend",
    (event) => {
      const now = Date.now();
      if (now - lastTouchEnd <= 300) {
        event.preventDefault();
      }
      lastTouchEnd = now;
    },
    { passive: false }
  );
};
