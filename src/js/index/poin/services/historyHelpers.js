/**
 * History Helper Functions
 * Business logic for history items (expired, claimed, formatting)
 */

/**
 * Check if item is expiring soon (less than 2 hours)
 * @param {string} expiredAt - Expiration date string
 * @returns {boolean} - True if expiring soon
 */
export const isExpiringSoon = (expiredAt) => {
  if (!expiredAt) return false;
  const expiredDate = new Date(expiredAt);
  const now = new Date();
  const diffMs = expiredDate - now;
  const twoHoursMs = 2 * 60 * 60 * 1000; // 2 hours in milliseconds

  return diffMs > 0 && diffMs <= twoHoursMs;
};

/**
 * Check if item is expired
 * @param {string} expiredAt - Expiration date string
 * @returns {boolean} - True if expired
 */
export const isExpired = (expiredAt) => {
  if (!expiredAt) return false;
  const expiredDate = new Date(expiredAt);
  const now = new Date();
  return expiredDate < now;
};

/**
 * Format remaining time until expiration
 * @param {string} expiredAt - Expiration date string
 * @returns {string|null} - Formatted remaining time
 */
export const formatRemainingTime = (expiredAt) => {
  if (!expiredAt) return null;

  const expiredDate = new Date(expiredAt);
  const now = new Date();
  const diffMs = expiredDate - now;

  if (diffMs <= 0) return "Sudah Expired";

  const totalSeconds = Math.floor(diffMs / 1000);
  const hours = Math.floor(totalSeconds / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);
  const seconds = totalSeconds % 60;

  return `${hours} jam ${minutes} menit ${seconds} detik`;
};

/**
 * Format expired date for display
 * @param {string} expiredAt - Expiration date string
 * @returns {string} - Formatted date string
 */
export const formatExpiredDate = (expiredAt) => {
  if (!expiredAt) return "";

  const date = new Date(expiredAt);
  const options = {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  };
  return date.toLocaleDateString("id-ID", options);
};

/**
 * Check if item is claimed
 * @param {string} ditukarTanggal - Claim date string
 * @returns {boolean} - True if claimed
 */
export const isClaimed = (ditukarTanggal) => {
  return ditukarTanggal && ditukarTanggal !== null;
};

/**
 * Format claimed date for display
 * @param {string} ditukarTanggal - Claim date string
 * @returns {string} - Formatted date string
 */
export const formatClaimedDate = (ditukarTanggal) => {
  if (!ditukarTanggal) return "";

  const date = new Date(ditukarTanggal);
  const options = {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  };
  return date.toLocaleDateString("id-ID", options);
};

/**
 * Get status info for a history item
 * @param {Object} item - History item object
 * @returns {Object} - Status information
 */
export const getItemStatus = (item) => {
  const expired = isExpired(item.expired_at);
  const expiringSoon = isExpiringSoon(item.expired_at);
  const claimed = isClaimed(item.ditukar_tanggal);
  const status = item.status || "unknown";

  return {
    expired,
    expiringSoon,
    claimed,
    remainingTime: formatRemainingTime(item.expired_at),
    expiredDateFormatted: formatExpiredDate(item.expired_at),
    claimedDateFormatted: formatClaimedDate(item.ditukar_tanggal),
    status,
  };
};
