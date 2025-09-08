/**
 * Application State Management
 * Centralized state management for the rewards exchange feature
 */

export const state = {
  // User points balance
  userPoints: 0,

  // Available rewards
  rewards: [],

  // Exchange history
  exchangeHistory: [],

  // Current location filter
  currentLocation: "member-area",

  // Currently selected reward (for preview/modal)
  pendingReward: null,
};

/**
 * Update user points
 * @param {number} points - New points value
 */
export const updateUserPoints = (points) => {
  state.userPoints = points;
};

/**
 * Set rewards data
 * @param {Array} rewards - Array of reward objects
 */
export const setRewards = (rewards) => {
  state.rewards = rewards;
};

/**
 * Update reward stock after exchange
 * @param {number} rewardId - ID of the reward
 */
export const updateRewardStock = (rewardId) => {
  const reward = state.rewards.find((r) => r.id === rewardId);
  if (reward && reward.stock > 0) {
    reward.stock--;
  }
};

/**
 * Add exchange to history
 * @param {Object} exchange - Exchange details
 */
export const addToExchangeHistory = (exchange) => {
  state.exchangeHistory.unshift({
    id: Date.now(),
    ...exchange,
    status: "success",
  });
};

/**
 * Set pending reward
 * @param {Object|null} reward - Reward object or null to clear
 */
export const setPendingReward = (reward) => {
  state.pendingReward = reward;
};
