import getCookie from "../../index/utils/cookies.js";
export const getUser = async () => {
  const token = getCookie("admin_token");
  try {
    const response = await fetch("/src/api/user/get_user_in", {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });
    if (response.status === 200) {
      const data = await response.json();
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
      text: "Terjadi kesalahan saat mengambil data",
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
export const insertUser = async (data) => {
  const token = getCookie("admin_token");
  try {
    const response = await fetch("/src/api/user/post_new_user", {
      method: "POST",
      headers: {
        Authorization: `Bearer ${token}`,
      },
      body: data,
    });
    if (response.status === 201) {
      Swal.fire({
        icon: "success",
        title: "Berhasil",
        text: "User berhasil ditambahkan",
        confirmButtonText: "OK",
      }).then(() => {
        window.location.href = "/src/fitur/account/in_new_user";
      });
    } else if (response.status === 409) {
      const error = await response.json();
      Swal.fire({
        icon: "warning",
        title: "Data Duplikat",
        text: error.message,
        confirmButtonText: "OK",
      });
      throw new Error(error.message);
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
      throw new Error("Server Error");
    }
  } catch (error) {
    throw error;
  }
};
export const menuAccess = async () => {
  const token = getCookie("admin_token");
  try {
    const response = await fetch("/src/api/user/menu_access", {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });
    const allowedMenus = await response.json();
    return allowedMenus.data;
  } catch (error) {
    console.error("Error fetching menu access:", error);
    throw error;
  }
};
export const getUserEdit = async (kode) => {
  const token = getCookie("admin_token");
  try {
    const response = await fetch(
      `/src/api/user/get_user_edit_in?kode=${kode}`,
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
    } else if (response.status === 404) {
      Toastify({
        text: "User tidak ditemukan",
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
      text: "Terjadi kesalahan saat mengambil data user",
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
export const setOtorisasiUser = async (data) => {
  const token = getCookie("admin_token");
  try {
    const response = await fetch("/src/api/user/set_otorisasi.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify(data),
    });

    const result = await response.json();

    if (result.success) {
      Swal.fire({
        icon: "success",
        title: "Berhasil",
        text: result.message,
        timer: 1500,
        showConfirmButton: false,
      });
      return true;
    } else {
      throw new Error(result.message);
    }
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Gagal",
      text: error.message || "Terjadi kesalahan server",
    });
    return false;
  }
};
export default {
  getUser,
  insertUser,
  menuAccess,
  getUserEdit,
  setOtorisasiUser,
};
