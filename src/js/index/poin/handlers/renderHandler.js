/**
 * Render Handler
 * Handles rendering logic for rewards grid
 */

import { state, setPendingReward } from "../state.js";
import { elements } from "../dom.js";
import { createRewardCard } from "../components/RewardCard.js";
import { showRewardPreviewModal } from "../components/Modals.js";
import { handleExchange } from "./rewardHandler.js";
import { applyRewardFilters } from "./filterHandler.js";

/**
 * Render rewards grid
 * @param {string} [location='member-area'] - Location to filter by
 * @param {Array} [customRewards=null] - Custom rewards array for filtering
 */
export const renderRewards = (location = "member-area", customRewards = null) => {
  if (!elements.rewardsGrid) return;

  const rewardsToUse = customRewards || state.rewards;
  
  // Apply filters using filter handler
  const filteredRewards = applyRewardFilters(rewardsToUse, location, customRewards);

  if (filteredRewards.length === 0) {
    elements.rewardsGrid.innerHTML = `
      <div class="col-span-full text-center py-12">
        <div class="text-6xl mb-4">📍</div>
        <h3 class="text-lg font-medium text-gray-700 mb-2">Belum Ada Hadiah</h3>
        <p class="text-sm text-gray-500">Untuk lokasi yang dipilih saat ini</p>
      </div>
    `;
    return;
  }

  elements.rewardsGrid.innerHTML = filteredRewards
    .map((reward) => {
      // Create the reward card with just the points
      const cardHtml = createRewardCard(reward, state.userPoints);

      // Create a temporary container to add event listeners
      const temp = document.createElement("div");
      temp.innerHTML = cardHtml;

      // Add event listeners to the card
      const cardElement = temp.firstElementChild;
      if (cardElement) {
        // Add click handler for preview
        const previewElement = cardElement.querySelector("[data-preview-id]");
        if (previewElement) {
          previewElement.addEventListener("click", () => {
            setPendingReward(reward);
            showRewardPreviewModal(reward, {
              ...elements,
            }, state.userPoints);
          });
        }

        // Add click handler for exchange button
        const exchangeBtn = cardElement.querySelector(".exchange-btn");
        if (exchangeBtn) {
          exchangeBtn.addEventListener("click", () => handleExchange(reward));
        }
      }

      return cardElement ? cardElement.outerHTML : "";
    })
    .join("");
};

/**
 * Update points display
 */
export const updatePointsDisplay = () => {
  if (elements.userPoints) {
    elements.userPoints.textContent = state.userPoints.toLocaleString("id-ID");
  }
  if (elements.pointsDisplay) {
    elements.pointsDisplay.textContent = state.userPoints.toLocaleString("id-ID");
  }
};
