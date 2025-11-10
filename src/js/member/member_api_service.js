import { sendRequestGET } from "../utils/api_helpers.js";

const API_BASE_URL = "/src/api/member/product";
const API_TRANSACTION_URL = "/src/api/transaction";
const API_MANAGEMENT_URL = "/src/api/member/management";
const API_MEMBER_URL = "/src/api/member";
const API_DASHBOARD_URL = "/src/api/dashboard"; // <-- TAMBAHKAN INI

export const getProductFav = (startDate = null, endDate = null) => {
  const params = new URLSearchParams();
  if (startDate) params.append("start_date", startDate);
  if (endDate) params.append("end_date", endDate);

  const queryString = params.toString();
  const url = `${API_BASE_URL}/get_product_fav.php${
    queryString ? `?${queryString}` : ""
  }`;
  return sendRequestGET(url);
};

export const getTrendPembelian = () => {
  return sendRequestGET(`${API_BASE_URL}/get_trend_pembelian.php`);
};

export const getProductPerforma = () => {
  return sendRequestGET(`${API_BASE_URL}/get_product_performa.php`);
};

export const getTransactionBranchDetail = (cabang) => {
  const url = `${API_TRANSACTION_URL}/get_transaction_branch_detail.php?cabang=${encodeURIComponent(
    cabang
  )}`;
  return sendRequestGET(url);
};

export const getMemberSummary = () => {
  return sendRequestGET(`${API_MANAGEMENT_URL}/get_summary.php`);
};

export const getMemberPoinList = (limit = 5, page = 1) => {
  const params = new URLSearchParams();
  params.append("limit", limit);
  params.append("page", page);
  return sendRequestGET(
    `${API_MEMBER_URL}/member_poin_fetch.php?${params.toString()}`
  );
};

export const getCityMember = () => {
  return sendRequestGET(`${API_MANAGEMENT_URL}/get_city_member.php`);
};

// --- TAMBAHKAN FUNGSI BARU DI BAWAH INI ---
export const getTransactionDashboardData = () => {
  return sendRequestGET(`${API_DASHBOARD_URL}/get_data_transaction.php`);
};
