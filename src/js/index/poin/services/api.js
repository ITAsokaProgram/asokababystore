/**
 * API Service
 * Handles all API calls for the rewards system
 */

import { dataHadiah, dataPoin, historyTukar } from "./data_poin.js";

/**
 * Fetch rewards data from the API
 * @returns {Promise<Array>} - Promise that resolves to an array of rewards
 */
export const fetchRewards = async () => {
  try {
    const response = await dataHadiah();
    return Array.isArray(response) ? response : [];
  } catch (error) {
    console.error("Error fetching rewards:", error);
    return [];
  }
};

/**
 * Fetch user points from the API
 * @returns {Promise<number>} - Promise that resolves to the user's points
 */
export const fetchUserPoints = async () => {
  try {
    const response = await dataPoin();
    return response?.[0]?.total_poin_pk_pm
      ? parseInt(response[0].total_poin_pk_pm, 10)
      : 0;
  } catch (error) {
    console.error("Error fetching user points:", error);
    return 0;
  }
};

/**
 * Fetch exchange history from the API with pagination
 * @param {number} page - Page number (default: 1)
 * @param {number} limit - Items per page (default: 10)
 * @returns {Promise<Object>} - Promise that resolves to an object with data and pagination info
 */
export const fetchExchangeHistory = async (page = 1, limit = 10) => {
  try {
    const response = await historyTukar(page, limit);
    return response || { data: [], pagination: null };
  } catch (error) {
    console.error("Error fetching exchange history:", error);
    return { data: [], pagination: null };
  }
};

/**
 * Fetch all exchange history (for backward compatibility)
 * @returns {Promise<Array>} - Promise that resolves to an array of all exchange history items
 */
export const fetchAllExchangeHistory = async () => {
  try {
    let allData = [];
    let page = 1;
    let hasMore = true;
    
    while (hasMore) {
      const response = await fetchExchangeHistory(page, 50); // Get 50 items per page
      allData = [...allData, ...response.data];
      hasMore = response.pagination?.has_more || false;
      page++;
    }
    
    return allData;
  } catch (error) {
    console.error("Error fetching all exchange history:", error);
    return [];
  }
};

/**
 * Process reward data from API to application format
 * @param {Array} apiData - Raw reward data from API
 * @returns {Array} - Processed rewards array
 */
export const processRewardsData = (apiData) => {
  if (!Array.isArray(apiData)) return [];

  return apiData.map((item) => ({
    id: item.id,
    plu: item.plu || null,
    name: item.nama_hadiah,
    image: item.link_gambar,
    stock: parseInt(item.stok) || 0,
    points: parseInt(item.points) || 0,
    locations: ["member-area"], // Default location
    description: item.nama_hadiah,
    nm_store: item.nm_store || "Unknown Store",
    store: item.store || "Tidak Diketahui",
  }));
};

 /**
 * Simulate exchange API call
 * @param {number} rewardId - ID of the reward to exchange
 * @returns {Promise<Object>} - Promise that resolves to exchange result
 */
export const exchangeReward = async (token,rewardId,storeId,plu,cabang) => {
  const rewardCode = await generateRewardCode(token,rewardId,storeId,plu,cabang);
  return rewardCode; 
};

/**
 * Generate a random reward code
 * @returns {string} - Generated reward code
 */
const generateRewardCode = async (token,rewardId,storeId,plu,cabang) => {
  const request = await fetch("/src/api/poin/redeem_reward", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify({ reward_id: rewardId, store_id: storeId , plu: plu , cabang: cabang }),
  });

  const data = await request.json();
  return data;
};

export const generateQrCode = async (code) => {
  const request = await fetch(`/src/api/qr/code?number=${code}`);
  const blob = await request.blob();
  return URL.createObjectURL(blob);
}

export const handleExpiredDate = async () => {
    try {
        const request = await fetch("/src/api/poin/handle_expired_redemptions", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
        });

        if (!request.ok) {
            throw new Error("Failed to handle expired redemptions");
        }

        const response = await request.json();
        return response;
    } catch (error) {
        console.error("Error handling expired redemptions:", error);
    }
};