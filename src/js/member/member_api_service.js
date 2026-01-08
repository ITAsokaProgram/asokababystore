import { sendRequestGET, sendRequestJSON } from "../utils/api_helpers.js";
const API_BASE_URL = "/src/api/member/product";
const API_TRANSACTION_URL = "/src/api/transaction";
const API_MANAGEMENT_URL = "/src/api/member/management";
const API_MEMBER_URL = "/src/api/member";
const API_DASHBOARD_URL = "/src/api/dashboard";
const API_WHATSAPP_URL = "/src/api/whatsapp";
function appendFilterParams(params, filterParams) {
  if (filterParams && filterParams.filter_type) {
    params.append("filter_type", filterParams.filter_type);
    if (filterParams.filter_type === "custom") {
      params.append("start_date", filterParams.start_date);
      params.append("end_date", filterParams.end_date);
    } else {
      params.append("filter", filterParams.filter);
    }
  }
}
export const getProductFav = (startDate = null, endDate = null) => {
  const params = new URLSearchParams();
  if (startDate) params.append("start_date", startDate);
  if (endDate) params.append("end_date", endDate);
  const queryString = params.toString();
  const url = `${API_BASE_URL}/get_product_fav.php${queryString ? `?${queryString}` : ""
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
export const getMemberActivity = (filterParams) => {
  const params = new URLSearchParams();
  appendFilterParams(params, filterParams);
  const url = `${API_MANAGEMENT_URL}/get_member_activity.php?${params.toString()}`;
  return sendRequestGET(url);
};
export const getMemberByAge = (filterParams, status) => {
  const params = new URLSearchParams();
  appendFilterParams(params, filterParams);
  params.append("status", status);
  const url = `${API_MANAGEMENT_URL}/get_member_by_age.php?${params.toString()}`;
  return sendRequestGET(url);
};
export const getTopProductsByAge = (
  filterParams,
  ageGroup,
  page = 1,
  limit = 10,
  status = null
) => {
  const params = new URLSearchParams();
  appendFilterParams(params, filterParams);
  params.append("age_group", ageGroup);
  params.append("page", page);
  params.append("limit", limit);
  if (status) {
    params.append("status", status);
  }
  const url = `${API_MANAGEMENT_URL}/get_top_products_by_age.php?${params.toString()}`;
  return sendRequestGET(url);
};
export const getMemberByLocation = (
  filterParams,
  status,
  level,
  city = null,
  district = null,
  limit = "default"
) => {
  const params = new URLSearchParams();
  appendFilterParams(params, filterParams);
  params.append("status", status);
  params.append("level", level);
  params.append("limit", limit);
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
  filterParams,
  city,
  district,
  subdistrict,
  page = 1,
  limit = 10,
  status = null
) => {
  const params = new URLSearchParams();
  appendFilterParams(params, filterParams);
  if (city) {
    params.append("city", city);
  }
  if (district) {
    params.append("district", district);
  }
  if (subdistrict) {
    params.append("subdistrict", subdistrict);
  }
  if (status) {
    params.append("status", status);
  }
  params.append("page", page);
  params.append("limit", limit);
  const url = `${API_MANAGEMENT_URL}/get_top_products_by_location.php?${params.toString()}`;
  return sendRequestGET(url);
};
export const getTopMembersByFilter = (filterParams, status, limit = 10) => {
  const params = new URLSearchParams();
  appendFilterParams(params, filterParams);
  params.append("status", status);
  params.append("limit", limit);
  const url = `${API_MANAGEMENT_URL}/get_top_members_by_filter.php?${params.toString()}`;
  return sendRequestGET(url);
};
export const getTopMemberProductPairs = (filterParams, status, limit = 10) => {
  const params = new URLSearchParams();
  appendFilterParams(params, filterParams);
  params.append("status", status);
  params.append("limit", limit);
  const url = `${API_MANAGEMENT_URL}/get_top_member_product_pairs.php?${params.toString()}`;
  return sendRequestGET(url);
};
export const getTopProductsByCustomer = (
  filterParams,
  kdCust,
  page = 1,
  limit = 10
) => {
  const params = new URLSearchParams();
  appendFilterParams(params, filterParams);
  params.append("kd_cust", kdCust);
  params.append("page", page);
  params.append("limit", limit);
  const url = `${API_MANAGEMENT_URL}/get_top_products_by_customer.php?${params.toString()}`;
  return sendRequestGET(url);
};
export const getTopMembersByFrequency = (
  filterParams,
  status,
  limit = 10,
  page = 1,
  isExport = false
) => {
  const params = new URLSearchParams();
  appendFilterParams(params, filterParams);
  params.append("status", status);
  if (isExport) {
    params.append("export", "true");
  } else {
    params.append("limit", limit);
    params.append("page", page);
  }
  const url = `${API_MANAGEMENT_URL}/get_top_members_by_frequency.php?${params.toString()}`;
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
export const getTransactionDetails = (filterParams, kdCust, plu) => {
  const params = new URLSearchParams();
  appendFilterParams(params, filterParams);
  params.append("kd_cust", kdCust);
  params.append("plu", plu);
  const url = `${API_MANAGEMENT_URL}/get_transaction_details_by_product.php?${params.toString()}`;
  return sendRequestGET(url);
};
export const getExportCustomerProducts = (filterParams, kdCust) => {
  const params = new URLSearchParams();
  appendFilterParams(params, filterParams);
  params.append("kd_cust", kdCust);
  const url = `${API_MANAGEMENT_URL}/get_export_customer_products.php?${params.toString()}`;
  return sendRequestGET(url);
};
export const getTopCustomersByCity = (
  filterParams,
  kota = null,
  page = 1,
  limit = 10
) => {
  const params = new URLSearchParams();
  if (filterParams && filterParams.filter_type) {
    params.append("filter_type", filterParams.filter_type);
    if (filterParams.filter_type === "custom") {
      params.append("start_date", filterParams.start_date);
      params.append("end_date", filterParams.end_date);
    } else {
      params.append("filter", filterParams.filter);
    }
  }
  if (kota && kota !== 'all') {
    params.append("kota", kota);
  }
  params.append("page", page);
  params.append("limit", limit);
  const url = `${API_MANAGEMENT_URL}/get_top_customers_by_city.php?${params.toString()}`;
  return sendRequestGET(url);
};
export const getExportTopCustomersByCity = (filterParams, kota) => {
  const params = new URLSearchParams();
  if (filterParams && filterParams.filter_type) {
    params.append("filter_type", filterParams.filter_type);
    if (filterParams.filter_type === "custom") {
      params.append("start_date", filterParams.start_date);
      params.append("end_date", filterParams.end_date);
    } else {
      params.append("filter", filterParams.filter);
    }
  }
  if (kota && kota !== 'all') {
    params.append("kota", kota);
  }
  params.append("is_export", "true");
  const url = `${API_MANAGEMENT_URL}/get_top_customers_by_city.php?${params.toString()}`;
  return sendRequestGET(url);
};
export const getAvailableCities = () => {
  return sendRequestGET(`${API_MANAGEMENT_URL}/get_city_member_all.php`);
};
export const getCitiesSimple = () => {
  return sendRequestGET(`${API_MANAGEMENT_URL}/get_cities_simple.php`);
};
