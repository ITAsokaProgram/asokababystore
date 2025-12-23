/**
 * Filter Handler
 * Handles all filtering logic for rewards
 */

import { state } from "../state.js";
import { elements } from "../dom.js";

/**
 * Handle filter button click
 * @param {HTMLElement} button - The clicked filter button
 * @param {Function} renderCallback - Callback to render rewards
 */
export const handleFilterClick = (button, renderCallback) => {
  // Update button states
  elements.filterButtons?.forEach((btn) => {
    btn.classList.toggle("bg-blue-600", btn === button);
    btn.classList.toggle("text-white", btn === button);
    btn.classList.toggle("bg-gray-100", btn !== button);
    btn.classList.toggle("text-gray-700", btn !== button);
  });

  // Get location dari data-location
  const location = button.dataset.location;
  if (location) {
    // Update state.currentLocation dengan nama store atau area
    state.currentLocation = location;
    
    // Render rewards dengan filter yang sudah terintegrasi
    if (typeof renderCallback === 'function') {
      renderCallback("member-area");
    }
  }
};

/**
 * Setup store filter functionality
 * @param {Function} filterRewardsByStore - Filter function from helpers
 * @param {Function} renderCallback - Callback to render rewards
 */
export const setupStoreFilter = (filterRewardsByStore, renderCallback) => {
  // Setup filter dengan callback yang terintegrasi dengan renderRewards
  filterRewardsByStore(state.rewards, 'branch-selector', (filteredRewards) => {
    // Jika tidak ada filter (reset), render semua rewards
    if (filteredRewards.length === state.rewards.length) {
      renderCallback("member-area");
    } else {
      // Jika ada filter, render dengan custom rewards
      renderCallback("member-area", filteredRewards);
    }
  });
};

/**
 * Apply filters to rewards based on current state
 * @param {Array} rewardsToUse - Rewards array to filter
 * @param {string} location - Location to filter by
 * @param {Array} customRewards - Custom rewards array for filtering
 * @returns {Array} Filtered rewards
 */
export const applyRewardFilters = (rewardsToUse, location = "member-area", customRewards = null) => {
  return rewardsToUse.filter((reward) => {
    // Filter out rewards with zero stock
    const hasStock = reward.stock > 0;
    
    // Jika ada customRewards (sudah difilter), cek location dan stock
    if (customRewards) {
      return reward.locations.includes(location) && hasStock;
    }
    
    // Filter normal berdasarkan location
    const locationMatch = reward.locations.includes(location);
    
    // Jika currentLocation adalah store_id, filter berdasarkan reward.store
    if (state.currentLocation && 
        state.currentLocation !== 'member-area') {
      const storeMatch = reward.store && reward.store === state.currentLocation;
      return locationMatch && storeMatch && hasStock;
    }
    
    return locationMatch && hasStock;
  });
};
