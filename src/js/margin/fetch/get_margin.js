import getCookie from "../../index/utils/cookies.js";

export const fetchMargin = async () => {
  const token = getCookie("token");
  const response = await fetch("/src/api/margin/margin_minus", {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
    },
  });
  if (response.status === 200) {
    const data = await response.json();
    sessionStorage.setItem("default_table", JSON.stringify(data));
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
};

export const fetchFilterMargin = async (start, end, cabang) => {
  const token = getCookie("token");
  const response = await fetch(
    `/src/api/margin/filter_margin?start=${start}&end=${end}&cabang=${cabang}`,
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
    sessionStorage.setItem("filter_table", JSON.stringify(data));
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
};

export default { fetchMargin, fetchFilterMargin };
