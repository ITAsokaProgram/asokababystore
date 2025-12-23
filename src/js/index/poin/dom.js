/**
 * DOM Elements Management
 * Centralized DOM element references and DOM-related utilities
 */

export const elements = {
  // Main containers
  rewardsGrid: document.getElementById('rewardsGrid'),
  
  // Modals
  rewardPreviewModal: document.getElementById('rewardPreviewModal'),
  successModal: document.getElementById('successModal'),
  errorModal: document.getElementById('errorModal'),
  confirmModal: document.getElementById('confirmModal'),
  historyModal: document.getElementById('historyModal'),
  qrRewardCode: document.getElementById('qrRewardCode'),  
  // Preview modal elements
  previewImage: document.getElementById('previewImage'),
  previewTitle: document.getElementById('previewTitle'),
  previewDescription: document.getElementById('previewDescription'),
  previewPoints: document.getElementById('previewPoints'),
  previewStock: document.getElementById('previewStock'),
  exchangeFromPreviewBtn: document.getElementById('exchangeFromPreviewBtn'),
  
  // Confirm modal elements
  confirmEmoji: document.getElementById('confirmEmoji'),
  confirmRewardName: document.getElementById('confirmRewardName'),
  confirmRewardDesc: document.getElementById('confirmRewardDesc'),
  confirmPoints: document.getElementById('confirmPoints'),
  confirmExchangeBtn: document.getElementById('confirmExchangeBtn'),
  
  // Success modal elements
  successMessage: document.getElementById('successMessage'),
  rewardCode: document.getElementById('rewardCode'),
  copyCodeBtn: document.getElementById('copyCodeBtn'),
  closeModalBtn: document.getElementById('closeModalBtn'),
  // Error modal elements
  errorMessage: document.getElementById('errorMessage'),
  
  // User interface elements
  userPoints: document.getElementById('userPoints'),
  pointsDisplay: document.getElementById('pointsDisplay'),
  filterButtons: document.querySelectorAll('.filter-btn'),
  
  // Navigation elements
  goBackBtn: document.getElementById('goBackBtn'),
  historyFloatingBtn: document.getElementById('historyFloatingBtn')
  
};

/**
 * Safely query an element within a parent
 * @param {string} selector - CSS selector
 * @param {HTMLElement} [parent=document] - Parent element to search within
 * @returns {HTMLElement|null} - Found element or null
 */
export const $ = (selector, parent = document) => {
  return parent.querySelector(selector);
};

/**
 * Safely query all elements matching a selector within a parent
 * @param {string} selector - CSS selector
 * @param {HTMLElement} [parent=document] - Parent element to search within
 * @returns {NodeList} - NodeList of found elements
 */
export const $$ = (selector, parent = document) => {
  return parent.querySelectorAll(selector);
};
