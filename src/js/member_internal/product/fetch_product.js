import getCookie from "../../index/utils/cookies.js";
export const fetchProductFav = async () => {
  const token = getCookie("admin_token");
  try {
    const response = await fetch("/src/api/member/product/get_product_fav", {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });
    if (response.status === 200) {
      const data = await response.json();
      sessionStorage.setItem("kategori_invalid", JSON.stringify(data));
      Toastify({
        text: "Berhasil Memuat Data",
        duration: 1000,
        gravity: "top",
        position: "right",
        style: {
          background: "#34d399",
          color: "$fff",
        },
      }).showToast();
      return data;
    } else if (response.status === 204) {
      Toastify({
        text: "Data tidak ditemukan",
        duration: 1000,
        gravity: "top",
        position: "right",
        style: {
          background: "#f87171",
          color: "$fff",
        },
      }).showToast();
    } else if (response.status === 401) {
      Swal.fire({
        icon: "error",
        title: "Sesi Berakhir",
        text: "Silahkan Login Kembali",
        confirmButtonText: "Login",
      }).then(() => {
        window.location.href = "/in_login";
      });
    } else if (response.status === 500) {
      Toastify({
        text: "Server Error",
        duration: 1000,
        gravity: "top",
        position: "right",
        style: {
          background: "#f87171",
          color: "$fff",
        },
      }).showToast();
    }
  } catch (error) {
    Toastify({
      text: "Gagal mengambil data refresh halaman",
      duration: 1000,
      gravity: "top",
      position: "right",
      style: {
        background: "#f87171",
        color: "$fff",
      },
    }).showToast();
  }
};
export const filterProductFav = async (startDate, endDate) => {
  const token = getCookie("admin_token");
  try {
    const response = await fetch(
      `/src/api/member/product/get_product_fav?start_date=${startDate}&end_date=${endDate}`,
      {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
      }
    );
    if (response.status === 200) {
      const data = await response.json();
      return data;
    }
  } catch (error) {
    console.error("Error filtering product fav:", error);
    return null;
  }
};
export const fetchTopSales = async () => {
  const token = getCookie("admin_token");
  try {
    const response = await fetch("/src/api/member/product/get_top_sales", {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });
    if (response.status === 200) {
      const data = await response.json();
      return data;
    }
  } catch (error) {
    console.error("Error fetching top sales:", error);
    return null;
  }
};
export const fetchTopMember = async (startDate, endDate) => {
  const token = getCookie("admin_token");
  let queryString = "";
  if (startDate && endDate) {
    queryString = `?start_date=${startDate}&end_date=${endDate}`;
  }
  try {
    const response = await fetch(
      `/src/api/member/product/get_top_member.php${queryString}`,
      {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
      }
    );
    if (response.status === 200) {
      const data = await response.json();
      return data;
    } else if (response.status === 401) {
    } else if (response.status === 500) {
    } else {
      return await response.json();
    }
  } catch (error) {
    console.error("Error fetching top member:", error);
    return null;
  }
};
export const fetchPaginatedMembers = async (params = {}) => {
  const token = getCookie("admin_token");
  const queryParams = new URLSearchParams(params);
  try {
    const response = await fetch(
      `/src/api/member/product/get_paginated_members.php?${queryParams.toString()}`,
      {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
      }
    );
    if (response.status === 200) {
      return await response.json();
    } else if (response.status === 401) {
      Swal.fire({
        icon: "error",
        title: "Sesi Berakhir",
        text: "Silahkan Login Kembali",
        confirmButtonText: "Login",
      }).then(() => {
        window.location.href = "/in_login";
      });
    } else {
      return await response.json();
    }
  } catch (error) {
    console.error("Error fetching paginated members:", error);
    return { success: false, message: "Error koneksi." };
  }
};
export const fetchTrendOmzet = async () => {
  const token = getCookie("admin_token");
  try {
    const response = await fetch("/src/api/member/product/get_trend_omzet", {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });
    if (response.status === 200) {
      const data = await response.json();
      return data;
    }
  } catch (error) {
    console.error("Error fetching trend omzet:", error);
    return null;
  }
};
export const fetchOmzetSummary = async () => {
  const token = getCookie("admin_token");
  try {
    const response = await fetch("/src/api/member/product/get_omzet", {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });
    if (response.status === 200) {
      const data = await response.json();
      return data;
    }
  } catch (error) {
    console.error("Error fetching omzet summary:", error);
    return null;
  }
};
export default {
  fetchProductFav,
  fetchTopSales,
  fetchTopMember,
  fetchTrendOmzet,
  fetchOmzetSummary,
};
