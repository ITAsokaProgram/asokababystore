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
export const filterProductFav = async (filterParams, status) => {
  const token = getCookie("admin_token");
  const params = new URLSearchParams();
  if (filterParams) {
    if (filterParams.filter_type) {
      params.append("filter_type", filterParams.filter_type);
    }
    if (filterParams.filter) {
      params.append("filter", filterParams.filter);
    }
    if (filterParams.start_date) {
      params.append("start_date", filterParams.start_date);
    }
    if (filterParams.end_date) {
      params.append("end_date", filterParams.end_date);
    }
  }
  if (status) {
    params.append("status", status);
  }
  const queryString = params.toString();
  try {
    const response = await fetch(
      `/src/api/member/product/get_product_fav.php?${queryString}`,
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
export const fetchTopMember = async (filterParams, status) => {
  const token = getCookie("admin_token");
  const params = new URLSearchParams();
  if (filterParams) {
    if (filterParams.filter_type) {
      params.append("filter_type", filterParams.filter_type);
    }
    if (filterParams.filter) {
      params.append("filter", filterParams.filter);
    }
    if (filterParams.start_date) {
      params.append("start_date", filterParams.start_date);
    }
    if (filterParams.end_date) {
      params.append("end_date", filterParams.end_date);
    }
  }
  if (status) {
    params.append("status", status);
  }
  const queryString = params.toString();
  try {
    const response = await fetch(
      `/src/api/member/product/get_top_member.php${
        queryString ? `?${queryString}` : ""
      }`,
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
export const fetchTopProducts = async (filterParams, status) => {
  const token = getCookie("admin_token");
  const params = new URLSearchParams();
  if (filterParams) {
    if (filterParams.filter_type) {
      params.append("filter_type", filterParams.filter_type);
    }
    if (filterParams.filter) {
      params.append("filter", filterParams.filter);
    }
    if (filterParams.start_date) {
      params.append("start_date", filterParams.start_date);
    }
    if (filterParams.end_date) {
      params.append("end_date", filterParams.end_date);
    }
  }
  if (status) {
    params.append("status", status);
  }
  const queryString = params.toString();
  try {
    const response = await fetch(
      `/src/api/member/product/get_top_products.php${
        queryString ? `?${queryString}` : ""
      }`,
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
    console.error("Error fetching top products:", error);
    return null;
  }
};

export const fetchPaginatedProducts = async (params = {}) => {
  const token = getCookie("admin_token");
  const queryParams = new URLSearchParams(params);
  try {
    const response = await fetch(
      `/src/api/member/product/get_paginated_products.php?${queryParams.toString()}`,
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
    console.error("Error fetching paginated products:", error);
    return { success: false, message: "Error koneksi." };
  }
};
export default {
  fetchProductFav,
  fetchTopSales,
  fetchTopMember,
  fetchTrendOmzet,
  fetchOmzetSummary,
  fetchPaginatedMembers,
};
