/**
 * Format number to Indonesian locale
 * @param {number} number - Number to format
 * @returns {string} Formatted number string
 */
export const formatNumber = (number) => {
  return new Intl.NumberFormat('id-ID').format(number);
};

/**
 * Generate a random reward code
 * @returns {string} Generated reward code
 */
export const generateRewardCode = () => {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  const codeLength = 8;
  let result = '';
  
  for (let i = 0; i < codeLength; i++) {
    if (i > 0 && i % 4 === 0) result += '-';
    result += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  
  return result;
};

/**
 * Set button loading state
 * @param {HTMLElement} button - Button element
 * @param {boolean} isLoading - Whether to show loading state
 * @param {string} loadingText - Text to show during loading
 * @param {string} originalText - Original button text
 */
export const setButtonLoadingState = (button, isLoading, loadingText, originalText) => {
  if (!button) return;
  
  button.disabled = isLoading;
  button.innerHTML = isLoading 
    ? `<span class="loading loading-spinner loading-xs"></span> ${loadingText}`
    : originalText;
};

/**
 * Handle back navigation
 */
export const goBack = () => {
    window.history.back();
  };

/**
 * Handle Filter by store name
 * @param {Array} rewards - Array of reward objects  
 * @param {string} selectId - ID of select element
 * @param {Function} updateCallback - Callback function to update UI
 * @returns {Array} Filtered rewards
 */
export const filterRewardsByStore = (rewards, selectId, updateCallback) => {
  const select = document.getElementById(selectId);
  if (!select) return;

  select.addEventListener('change', () => {
    const selectedStore = select.value;
    
    // Update state.currentLocation dengan nama store yang dipilih
    if (window.state) {
      window.state.currentLocation = selectedStore;
    }
    
    // Jika tidak ada store yang dipilih, reset ke member-area
    if (!selectedStore || selectedStore === '') {
      if (window.state) {
        window.state.currentLocation = 'member-area';
      }
      if (typeof updateCallback === 'function') {
        updateCallback(rewards);
      }
      return;
    }
    
    // Filter berdasarkan store_id (bukan nama store)
    const filteredRewards = rewards.filter(
      reward => reward.store && reward.store === selectedStore
    );
    
    if (typeof updateCallback === 'function') {
      updateCallback(filteredRewards);
    }
  });
}