export function getCookie(name) {
  const value = document.cookie.match("(^|;)\\s*" + name + "\\s*=\\s*([^;]+)");
  if (value) return value[2];
  return null;
}

function getToken() {
  return getCookie("admin_token");
}

const handleResponse = async (response) => {
  if (response.status === 204) {
    Toastify({
      text: "Data tidak ditemukan",
      duration: 1500,
      gravity: "top",
      position: "right",
      style: { background: "#f87171", color: "$fff" },
    }).showToast();
    return { success: true, data: [] };
  }
  if (response.status === 401) {
    Swal.fire({
      icon: "error",
      title: "Sesi Berakhir",
      text: "Silahkan Login Kembali",
      confirmButtonText: "Login",
    }).then(() => {
      window.location.href = "/in_login";
    });
    throw new Error("Sesi Berakhir");
  }
  if (response.status === 500) {
    Toastify({
      text: "Server Error",
      duration: 1500,
      gravity: "top",
      position: "right",
      style: { background: "#f87171", color: "$fff" },
    }).showToast();
    throw new Error("Server Error");
  }
  const responseData = await response.json();
  if (!response.ok) {
    Toastify({
      text: responseData.message || "Terjadi kesalahan",
      duration: 1500,
      gravity: "top",
      position: "right",
      style: { background: "#f87171", color: "$fff" },
    }).showToast();
    throw new Error(
      responseData.message || `HTTP error! status: ${response.status}`
    );
  }
  if (responseData.status === false || responseData.success === false) {
    if (
      responseData.message === "Data Kosong" ||
      responseData.message === "Data belum tersedia"
    ) {
      return { success: true, data: [], message: responseData.message };
    }
    Toastify({
      text: responseData.message || "API mengembalikan error",
      duration: 1500,
      gravity: "top",
      position: "right",
      style: { background: "#f87171", color: "$fff" },
    }).showToast();
    throw new Error(responseData.message || "API returned a false status.");
  }
  return responseData;
};

export const sendRequestGET = async (url) => {
  try {
    const token = getToken();
    const headers = {
      Accept: "application/json",
    };
    if (token) {
      headers["Authorization"] = `Bearer ${token}`;
    } else {
      return handleResponse(new Response(null, { status: 401 }));
    }
    const response = await fetch(url, {
      method: "GET",
      headers: headers,
    });
    return handleResponse(response);
  } catch (error) {
    Toastify({
      text: "Gagal mengambil data, cek koneksi.",
      duration: 1500,
      gravity: "top",
      position: "right",
      style: { background: "#f87171", color: "$fff" },
    }).showToast();
    console.error("Fetch error:", error);
    throw error;
  }
};

export const sendRequestJSON = async (url, dataObject) => {
  try {
    const token = getToken();
    const headers = {
      "Content-Type": "application/json",
      Accept: "application/json",
    };
    if (token) {
      headers["Authorization"] = `Bearer ${token}`;
    } else {
      return handleResponse(new Response(null, { status: 401 }));
    }
    const response = await fetch(url, {
      method: "POST",
      headers: headers,
      body: JSON.stringify(dataObject),
    });
    return handleResponse(response);
  } catch (error) {
    Toastify({
      text: "Gagal mengirim data, cek koneksi.",
      duration: 1500,
      gravity: "top",
      position: "right",
      style: { background: "#f87171", color: "$fff" },
    }).showToast();
    console.error("Fetch error:", error);
    throw error;
  }
};
