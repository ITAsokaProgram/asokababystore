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
  end = null,
  cabang = ""
) => {
  const token = getCookie("admin_token");
  showLoading();
  try {
    const response = await fetch(
      `/src/api/invalid/detail_kategori?kategori=${kategori}&kode=${kode}&start=${start}&end=${end}&cabang=${cabang}`,
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
  const htmlContent = `
    <div class="flex flex-col gap-4 text-left">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Keterangan</label>
            <input id="swal-input-ket" class="swal2-input !m-0 !w-full" placeholder="Keterangan pengecekan">
        </div>
        <div class="p-3 bg-red-50 border border-red-100 rounded-lg">
            <h4 class="text-xs font-bold text-red-600 mb-2 border-b border-red-200 pb-1">OTORISASI USER CHECK</h4>
            <div class="mb-3">
                <label class="block text-xs font-semibold text-gray-700 mb-1">User Check (Inisial)</label>
                <input id="swal-input-user" class="swal2-input !m-0 !w-full !h-10 !text-sm" placeholder="Contoh: ADM" autocomplete="off">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Kode Otorisasi</label>
                <input type="password" id="swal-input-pass" class="swal2-input !m-0 !w-full !h-10 !text-sm" placeholder="Password Otorisasi">
            </div>
        </div>
    </div>
  `;
  Swal.fire({
    title: "Update Checking",
    html: htmlContent,
    showCancelButton: true,
    confirmButtonText: "Simpan",
    confirmButtonColor: "#db2777",
    cancelButtonText: "Batal",
    allowOutsideClick: false,
    focusConfirm: false,
    preConfirm: () => {
      const keterangan = document.getElementById("swal-input-ket").value;
      const userCheck = document.getElementById("swal-input-user").value;
      const passAuth = document.getElementById("swal-input-pass").value;
      if (!keterangan) {
        Swal.showValidationMessage("Keterangan tidak boleh kosong");
        return false;
      }
      if (!userCheck) {
        Swal.showValidationMessage("Nama User Check wajib diisi");
        return false;
      }
      if (!passAuth) {
        Swal.showValidationMessage("Kode Otorisasi wajib diisi");
        return false;
      }
      return {
        ...data,
        ket: keterangan,
        nama_user_cek: userCheck,
        kode_otorisasi: passAuth,
      };
    },
  }).then(async (result) => {
    if (result.isConfirmed && result.value) {
      const payload = result.value;
      Swal.showLoading();
      fetch("/src/api/invalid/update_checking", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(payload),
      })
        .then((response) => response.json())
        .then(async (res) => {
          if (res.status === "success") {
            await Swal.fire("Berhasil!", res.message, "success");
            await fetchDetailKategori(kategori, kode, startDate, endDate);
            paginationDetail(1, 100, "detail_kategori");
            await refreshParentTable();
          } else {
            throw new Error(res.message);
          }
        })
        .catch((error) => {
          Swal.fire("Gagal", error.message, "error");
        });
    }
  });
};
export const fetchBulkCekData = async (
  items,
  formValues,
  kategori,
  kode,
  startDate,
  endDate
) => {
  const token = getCookie("admin_token");
  const payload = {
    items: items,
    ket: formValues.keterangan,
    nama_user_cek: formValues.userCheck,
    kode_otorisasi: formValues.passAuth,
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
