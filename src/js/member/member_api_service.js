import { sendRequestGET } from "../utils/api_helpers.js";

const API_BASE_URL = "/src/api/member/product";
const API_TRANSACTION_URL = "/src/api/transaction";
// BARU: Tambahkan base URL untuk endpoint management
const API_MANAGEMENT_URL = "/src/api/member/management";

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

// BARU: Tambahkan fungsi untuk mengambil summary member
export const getMemberSummary = () => {
  return sendRequestGET(`${API_MANAGEMENT_URL}/get_summary.php`);
};
