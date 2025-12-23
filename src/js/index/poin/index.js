/**
 * Main Application Entry Point
 * Initializes and coordinates all components
 */

import {
  state,
  updateUserPoints,
  setRewards,
} from "./state.js";
import {
  fetchRewards,
  fetchUserPoints,
  handleExpiredDate,
  processRewardsData,
} from "./services/api.js";
import { filterRewardsByStore } from "./utils/helpers.js";
import { setupEventListeners, setupDoubleTapPrevention } from "./handlers/eventHandler.js";
import { setupStoreFilter } from "./handlers/filterHandler.js";
import { renderRewards, updatePointsDisplay } from "./handlers/renderHandler.js";
import { showErrorModal } from "./components/Modals.js";
import { elements } from "./dom.js";

/**
 * Initialize the application
 */
const init = async () => {
  try {
    // Expose state to window for access from helpers
    window.state = state;
    
    setupEventListeners(renderRewards);
    await loadInitialData();
    setupDoubleTapPrevention();
    setupStoreFilter(filterRewardsByStore, renderRewards);
  } catch (error) {
    console.error("Error initializing application:", error);
    showErrorModal("Gagal memuat data. Silakan refresh halaman.", elements);
  }
};

/**
 * Load initial data
 */
const loadInitialData = async () => {
  const [rewardsData, pointsData] = await Promise.all([
    fetchRewards(),
    fetchUserPoints(),
  ]);
  
  // Process and set rewards
  const processedRewards = processRewardsData(rewardsData);
  setRewards(processedRewards);

  // Set user points
  updateUserPoints(pointsData);
  updatePointsDisplay();

  // Render initial view
  renderRewards();

  // update expired points display
  await handleExpiredDate();
};

// Initialize the application when the DOM is loaded
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", init);
} else {
  init();
}
