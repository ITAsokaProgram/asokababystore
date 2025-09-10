/**
 * Reward Card Component
 * Renders a single reward card
 */

/**
 * Create HTML for a reward card
 * @param {Object} reward - Reward data
 * @param {number} userPoints - Current user points
 * @returns {string} - HTML string for the reward card
 */
export const createRewardCard = (reward, userPoints) => {
  const canAfford = userPoints >= reward.points;
  const isOutOfStock = reward.stock === 0;
  const stockStatusClass =
    reward.stock <= 5 ? "text-red-500 font-medium" : "text-green-600";
  const buttonConfig = getButtonConfig(canAfford, isOutOfStock);

  return `
    <div class="reward-card bg-white rounded-xl shadow-sm border overflow-hidden ${
      isOutOfStock ? "opacity-70" : ""
    }">
      <div class="relative">
        <div class="relative cursor-pointer" data-preview-id="${reward.id}">
          <img src="${
            reward.image || "https://via.placeholder.com/400x300?text=No+Image"
          }" 
               alt="${reward.name}" 
               class="w-full h-48 object-cover"
               data-preview-id="${reward.id}">
          <div class="absolute inset-0 bg-black/0 hover:bg-black/10 transition-colors flex items-center justify-center" data-preview-id="${
            reward.id
          }">
            <div class="bg-white/90 backdrop-blur-sm rounded-full px-3 py-1 text-xs font-medium text-gray-700 opacity-0 hover:opacity-100 transition-opacity">
              👁️ Lihat Detail
            </div>
          </div>
        </div>
      </div>
      
      <div class="p-4">
        <div class="mb-3">
          <h3 class="font-normal text-xs text-gray-800 mb-1 leading-tight capitalize line-clamp-2">${
            reward.name
          }</h3>
        </div>
        
        <div class="space-y-2 mb-4">
          <div class="flex items-center justify-between">
            <span class="text-xs text-gray-500">Poin:</span>
            <span class="font-bold text-blue-600 flex items-center gap-1 text-sm">
              <span class="text-yellow-500"><i class="fas fa-coins text-yellow-500 mr-1"></i></span>
              ${reward.points.toLocaleString("id-ID")}
            </span>
          </div>
          
          <div class="flex items-center justify-between">
            <span class="text-xs text-gray-500">Stok:</span>
            <span class="text-xs ${stockStatusClass}">
              ${reward.stock || 0} tersisa
            </span>
          </div>

          <div class="flex items-center justify-between">
            <span class="text-xs" data-store-id="${reward.store}">
                ${
                  reward.nm_store
                    ? reward.nm_store.toLowerCase().includes("asoka baby store")
                      ? reward.nm_store
                          .replace(/asoka baby store/gi, "")
                          .trim() || "Asoka"
                      : reward.nm_store
                    : "Tidak Diketahui"
                }
            </span>
          </div>
        </div>
        
        <button data-reward-id="${reward.id}" 
                class="tukar-btn w-full py-3 rounded-lg font-medium text-sm transition-all ${
                  buttonConfig.classes
                }"
                ${buttonConfig.disabled ? "disabled" : ""}>
          ${buttonConfig.text}
        </button>
      </div>
    </div>
  `;
};

/**
 * Get button configuration based on reward status
 * @private
 */
const getButtonConfig = (canAfford, isOutOfStock) => {
  if (isOutOfStock) {
    return {
      text: "❌ Stok Habis",
      classes: "bg-gray-200 text-gray-500 cursor-not-allowed",
      disabled: true,
    };
  }

  if (canAfford) {
    return {
      text: "🎁 Tukar Sekarang",
      classes:
        "bg-gradient-to-r from-blue-500 to-purple-600 text-white hover:from-blue-600 hover:to-purple-700",
      disabled: false,
    };
  }

  return {
    text: "💰 Poin Tidak Cukup",
    classes: "bg-gray-100 text-gray-500 cursor-not-allowed hover:bg-gray-100",
    disabled: true,
  };
};
