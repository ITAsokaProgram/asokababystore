import getCookie from "../../index/utils/cookies.js";

/**
 * Fetch transaction data for member or non-member.
 * @param {Object} params - Parameters object
 * @param {string} [params.member] - Member/customer code (for member)
 * @param {string} [params.cabang] - Store code (for member)
 * @param {string} [params.no_trans] - Transaction code (for non-member)
 * @returns {Promise<Object|null>} - Response JSON or null on error
 */
export const fetchTransaction = async ({ member = "", cabang = "", no_trans = "" } = {}) => {
  const token = getCookie("admin_token");
  try {
    let url = "";
    if (member && cabang) {
      url = `/src/api/member/product/get_transaction?member=${encodeURIComponent(member)}&cabang=${encodeURIComponent(cabang)}`;
    } else if (no_trans) {
      url = `/src/api/member/product/get_transaction?kode=${encodeURIComponent(no_trans)}`;
    } else {
      // No valid parameter
      return null;
    }
    const response = await fetch(url, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });
    if (!response.ok) {
      // Optionally, you can throw or return null
      console.error("fetchTransaction: HTTP error", response.status, response.statusText);
      return null;
    }
    return await response.json();
  } catch (error) {
    console.error("fetchTransaction error:", error);
    return null;
  }
};
