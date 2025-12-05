import { paginationDetail } from "../table/pagination.js";
import getCookie from "./../../index/utils/cookies.js";
const showLoading = () => {
  Swal.fire({
    title: "",
    html: "",
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });
};
export const refreshParentTable = async () => {
  try {
    const source = sessionStorage.getItem("kategori_source");
    const { paginationKat } = await import("../table/pagination.js");
    showLoading();
    if (source === "kategori_by_tanggal") {
      const start = document.getElementById("startDate").value;
      const end = document.getElementById("endDate").value;
      const kat = document.getElementById("kategori").value;
      const per = document.getElementById("periodeFilter").value;
      const cab = document.getElementById("cabangFilter").value;
      await fetchKategoriByTgl(start, end, kat, per, cab, false);
      paginationKat(1, 10, "kategori_by_tanggal");
    } else {
      await fetchAllKategori(false);
      paginationKat(1, 10, "kategori_invalid");
    }
    Swal.close();
  } catch (error) {
    console.warn("Soft refresh failed, forcing browser reload...", error);
    window.location.reload();
  }
};
export const fetchAllKategori = async (useLoading = true) => {
  const token = getCookie("admin_token");
  if (useLoading) showLoading();
  try {
    const response = await fetch("/src/api/invalid/all_kategori", {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
    });
    if (useLoading) Swal.close();
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
    if (useLoading) Swal.close();
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
export const fetchDetailKategori = async (
  kategori,
  kode,
  start = null,
  end = null
) => {
  const token = getCookie("admin_token");
  showLoading();
  try {
    const response = await fetch(
      `/src/api/invalid/detail_kategori?kategori=${kategori}&kode=${kode}&start=${start}&end=${end}`,
      {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
      }
    );
    Swal.close();
    if (response.status === 200) {
      const data = await response.json();
      sessionStorage.setItem("detail_kategori", JSON.stringify(data));
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
    Swal.close();
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
export const fetchKategoriByTgl = async (
  start,
  end,
  kategori,
  periode,
  cabang,
  useLoading = true
) => {
  const token = getCookie("admin_token");
  if (useLoading) showLoading();
  try {
    const response = await fetch(
      `/src/api/invalid/filter_tgl_kat?start=${start}&end=${end}&kategori=${kategori}&periode=${periode}&cabang=${cabang}`,
      {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
      }
    );
    if (useLoading) Swal.close();
    if (response.status === 200) {
      const data = await response.json();
      sessionStorage.setItem("kategori_by_tanggal", JSON.stringify(data));
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
    if (useLoading) Swal.close();
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
export const fetchCekData = async (
  data,
  kategori,
  kode,
  startDate,
  endDate
) => {
  const token = getCookie("admin_token");
  Swal.fire({
    title: "Masukkan Keterangan",
    input: "text",
    inputLabel: `PLU: ${data.plu}`,
    inputPlaceholder: "Tulis keterangan di sini...",
    showCancelButton: true,
    confirmButtonText: "Kirim",
    confirmButtonColor: "#d33",
    allowOutsideClick: false,
    allowEscapeKey: false,
    preConfirm: (keterangan) => {
      if (!keterangan) {
        Swal.showValidationMessage("Keterangan tidak boleh kosong");
        return false;
      }
      const payload = {
        ...data,
        ket: keterangan,
      };
      Swal.showLoading();
      return fetch("/src/api/invalid/update_checking", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(payload),
      })
        .then((response) => response.json())
        .then((res) => {
          if (res.status === "success") {
            return res.message;
          } else {
            throw new Error(res.message);
          }
        })
        .catch((error) => {
          Swal.showValidationMessage(`Gagal: ${error.message}`);
        });
    },
  }).then(async (result) => {
    if (result.isConfirmed && result.value) {
      await Swal.fire("Tersimpan!", result.value, "success");
      await fetchDetailKategori(kategori, kode, startDate, endDate);
      paginationDetail(1, 100, "detail_kategori");
      await refreshParentTable();
    }
  });
};
export const fetchBulkCekData = async (
  items,
  keterangan,
  kategori,
  kode,
  startDate,
  endDate
) => {
  const token = getCookie("admin_token");
  const payload = {
    items: items,
    ket: keterangan,
    nama: sessionStorage.getItem("userName"),
  };
  Swal.showLoading();
  try {
    const response = await fetch("/src/api/invalid/update_checking", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify(payload),
    });
    const res = await response.json();
    if (res.status === "success") {
      await Swal.fire("Berhasil!", res.message, "success");
      await fetchDetailKategori(kategori, kode, startDate, endDate);
      await refreshParentTable();
      return true;
    } else {
      throw new Error(res.message);
    }
  } catch (error) {
    Swal.fire("Gagal", error.message, "error");
    return false;
  }
};
export const fetchKeterangan = async (plu, kasir, tgl, jam, store) => {
  const token = getCookie("admin_token");
  try {
    const response = await fetch(
      `/src/api/invalid/get_keterangan?plu=${plu}&kasir=${kasir}&tgl=${tgl}&jam=${jam}&cabang=${store}`,
      {
        method: "GET",
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    );
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
export const fetchTopInvalid = async () => {
  const token = getCookie("admin_token");
  try {
    const response = await fetch("/src/api/invalid/top_invalid", {
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
export const fetchTopRetur = async () => {
  const token = getCookie("admin_token");
  try {
    const response = await fetch("/src/api/invalid/top_retur", {
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
export const fetchExportDetails = async (cabang) => {
  const token = getCookie("admin_token");
  showLoading();
  try {
    const response = await fetch(
      `/src/api/invalid/export_details.php?cabang=${cabang || ""}`,
      {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
      }
    );
    Swal.close();
    if (response.status === 200) {
      const result = await response.json();
      return result.data;
    }
    return [];
  } catch (error) {
    console.error(error);
    Swal.close();
    Toastify({
      text: "Gagal mengambil data export",
      style: { background: "#f87171" },
    }).showToast();
    return [];
  }
};
export default {
  fetchAllKategori,
  fetchDetailKategori,
  fetchKategoriByTgl,
  fetchCekData,
  fetchBulkCekData,
  fetchKeterangan,
  fetchTopInvalid,
  fetchTopRetur,
  fetchExportDetails,
  refreshParentTable,
};
