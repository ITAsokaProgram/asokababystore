/**
 * Reward Handler
 * Handles reward exchange logic and validation
 */

import { state, setPendingReward, updateUserPoints, updateRewardStock, addToExchangeHistory } from "../state.js";
import { elements } from "../dom.js";
import { exchangeReward } from "../services/api.js";
import { showErrorModal, showConfirmModal, showSuccessModal, closeModal } from "../components/Modals.js";
import getCookie from "../../utils/cookies.js";

/**
 * Handle reward exchange initiation
 * @param {Object} reward - The reward to exchange
 */
export const handleExchange = (reward) => {
  if (!reward) return;

  if (reward.stock === 0) {
    showErrorModal("Maaf, stok hadiah ini sudah habis.", elements);
    return;
  }

  if (state.userPoints < reward.points) {
    showErrorModal(
      `Poin Anda tidak cukup. Anda memerlukan ${reward.points.toLocaleString(
        "id-ID"
      )} poin untuk hadiah ini.`,
      elements
    );
    return;
  }

  setPendingReward(reward);
  showConfirmModal(reward, elements);
};

/**
 * Confirm and process the exchange
 * @param {Function} updatePointsCallback - Callback to update points display
 * @param {Function} renderRewardsCallback - Callback to render rewards
 */
export const confirmExchange = async (updatePointsCallback, renderRewardsCallback) => {
  const token = getCookie("customer_token");
  const reward = state.pendingReward;
  if (!reward) return;

  const loadingBtn = elements.confirmExchangeBtn;
  if (!loadingBtn) return;

  const originalBtnText = loadingBtn.innerHTML;

  try {
    // Show loading state
    loadingBtn.disabled = true;
    loadingBtn.innerHTML =
      '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';

    // Process exchange
    const result = await exchangeReward(token, reward.id, reward.store, reward.plu, reward.nm_store);

    if (result.success) {
      // Update state
      updateUserPoints(state.userPoints - reward.points);
      updateRewardStock(reward.id);
      addToExchangeHistory({
        rewardId: reward.id,
        reward: reward.name,
        points: reward.points,
        code: result.code,
        expired_at: result.expires_at,
        date: new Date().toLocaleDateString('id-ID'),
        time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
      });

      // Update UI
      if (typeof updatePointsCallback === 'function') {
        updatePointsCallback();
      }
      if (typeof renderRewardsCallback === 'function') {
        renderRewardsCallback(state.currentLocation);
      }

      // Show success
      closeModal(elements.confirmModal);
      showSuccessModal(
        "Penukaran poin berhasil!",
        result.code,
        result.qr,
        elements,
        elements.closeModalBtn
      );
    } else {
      throw new Error("Exchange failed");
    }
  } catch (error) {
    console.error("Error processing exchange:", error);
    showErrorModal(
      "Terjadi kesalahan saat memproses penukaran. Silakan coba lagi.",
      elements
    );
  } finally {
    // Reset button state
    if (loadingBtn) {
      loadingBtn.disabled = false;
      loadingBtn.innerHTML = originalBtnText;
    }
  }
};

/**
 * Copy reward code to clipboard
 */
export const copyCode = () => {
  if (!elements.rewardCode) return;

  const code = elements.rewardCode.textContent.trim();
  if (!code) return;
  
  navigator.clipboard
    .writeText(code)
    .then(() => {
      const copyBtn = elements.copyCodeBtn;
      if (!copyBtn) return;

      const originalText = copyBtn.innerHTML;
      copyBtn.innerHTML = '<i class="fas fa-check mr-1"></i> Tersalin!';

      setTimeout(() => {
        copyBtn.innerHTML = originalText;
      }, 2000);
    })
    .catch((err) => {
      console.error("Could not copy text:", err);
    });
};
