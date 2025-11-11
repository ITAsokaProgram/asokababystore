import { sendRequestGET, sendRequestJSON } from "../utils/api_helpers.js";

const API_BASE_URL = "/src/api/member/product";
const API_TRANSACTION_URL = "/src/api/transaction";
const API_MANAGEMENT_URL = "/src/api/member/management";
const API_MEMBER_URL = "/src/api/member";
const API_DASHBOARD_URL = "/src/api/dashboard";
const API_WHATSAPP_URL = "/src/api/whatsapp"; // <-- Tambahkan ini

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

export const getTransactionDashboardData = () => {
  return sendRequestGET(`${API_DASHBOARD_URL}/get_data_transaction.php`);
};

export const getTopMemberBySales = () => {
  return sendRequestGET(`${API_BASE_URL}/get_top_member.php`);
};
export const getCityMemberAll = () => {
  return sendRequestGET(`${API_MANAGEMENT_URL}/get_city_member_all.php`);
};

export const getMemberActivity = (filter) => {
  const params = new URLSearchParams();
  params.append("filter", filter);

  const url = `${API_MANAGEMENT_URL}/get_member_activity.php?${params.toString()}`;
  return sendRequestGET(url);
};

export const getMemberByAge = (filter, status) => {
  const params = new URLSearchParams();
  params.append("filter", filter);
  params.append("status", status);

  const url = `${API_MANAGEMENT_URL}/get_member_by_age.php?${params.toString()}`;
  return sendRequestGET(url);
};

export const getTopProductsByAge = (filter, ageGroup, page = 1, limit = 10) => {
  const params = new URLSearchParams();
  params.append("filter", filter);
  params.append("age_group", ageGroup);
  params.append("page", page);
  params.append("limit", limit);

  const url = `${API_MANAGEMENT_URL}/get_top_products_by_age.php?${params.toString()}`;
  return sendRequestGET(url);
};

export const getMemberByLocation = (
  filter,
  status,
  level,
  city = null,
  district = null
) => {
  const params = new URLSearchParams();
  params.append("filter", filter);
  params.append("status", status);
  params.append("level", level);
  if (city) {
    params.append("city", city);
  }
  if (district) {
    params.append("district", district);
  }

  const url = `${API_MANAGEMENT_URL}/get_member_by_location.php?${params.toString()}`;
  return sendRequestGET(url);
};

export const getTopProductsByLocation = (
  filter,
  city,
  district,
  subdistrict,
  page = 1,
  limit = 10
) => {
  const params = new URLSearchParams();
  params.append("filter", filter);
  params.append("city", city);
  params.append("district", district);
  params.append("subdistrict", subdistrict);
  params.append("page", page);
  params.append("limit", limit);

  const url = `${API_MANAGEMENT_URL}/get_top_products_by_location.php?${params.toString()}`;
  return sendRequestGET(url);
};

export const getTopMembersByFilter = (filter, status, limit = 10) => {
  const params = new URLSearchParams();
  params.append("filter", filter);
  params.append("status", status);
  params.append("limit", limit);

  const url = `${API_MANAGEMENT_URL}/get_top_members_by_filter.php?${params.toString()}`;
  return sendRequestGET(url);
};

export const getTopMemberProductPairs = (filter, status, limit = 10) => {
  const params = new URLSearchParams();
  params.append("filter", filter);
  params.append("status", status);
  params.append("limit", limit);

  const url = `${API_MANAGEMENT_URL}/get_top_member_product_pairs.php?${params.toString()}`;
  return sendRequestGET(url);
};

export const getTopProductsByCustomer = (
  filter,
  kdCust,
  page = 1,
  limit = 10
) => {
  const params = new URLSearchParams();
  params.append("filter", filter);
  params.append("kd_cust", kdCust);
  params.append("page", page);
  params.append("limit", limit);

  const url = `${API_MANAGEMENT_URL}/get_top_products_by_customer.php?${params.toString()}`;
  return sendRequestGET(url);
};

export const sendProactiveMessage = (kd_cust, message) => {
  const url = `${API_WHATSAPP_URL}/send_proactive_message.php`;
  const data = {
    kd_cust: kd_cust,
    message: message,
  };
  return sendRequestJSON(url, data);
};
