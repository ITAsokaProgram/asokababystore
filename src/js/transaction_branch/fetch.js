import { getCookie } from "../index/utils/cookies.js";
export const getTransCabang = async () => {
  try {
    const token = getCookie("token");
    const response = await fetch("/src/api/transaction/get_transaction_branch", {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });
    if (response.status === 200) {
      const data = await response.json();
      sessionStorage.setItem("transaksi_cabang", JSON.stringify(data));
      
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
export const getTransCabangDetail = async (cabang) => {
  try {
    const token = getCookie("token");
    const response = await fetch(`/src/api/transaction/get_transaction_branch_detail?cabang=${cabang}`, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });
    if (response.status === 200) {
      const data = await response.json();
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
export const getTransDashboard = async () => {
  try {
    const token = getCookie("token");
    const response = await fetch("/src/api/dashboard/get_data_transaction", {
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

export default { getTransCabang, getTransCabangDetail, getTransDashboard };