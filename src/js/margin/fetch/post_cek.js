import getCookie from "../../index/utils/cookies.js";
import { paginationMargin } from "../table/pagination.js";
import { fetchMargin, fetchFilterMargin } from "./get_margin.js";

// Helper function untuk refresh
async function refreshTable(start, end, cabang) {
  const currentSource = sessionStorage.getItem("MarginPagination");
  if (currentSource === "filter_table" && start && end && cabang) {
    await fetchFilterMargin(start, end, cabang);
    paginationMargin(1, 10, "filter_table");
  } else {
    await fetchMargin();
    paginationMargin(1, 10, "default_table");
  }
}

// Single Update
export const fetchUpdateMargin = (data, start, end, cabang) => {
  const token = getCookie("admin_token");

  Swal.fire({
    title: "Masukkan Keterangan",
    input: "text",
    inputLabel: `PLU: ${data.plu} | Bon: ${data.bon}`,
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

      // Bungkus jadi array items
      const payload = {
        items: [{ ...data }],
        keterangan: keterangan,
        nama: data.nama,
      };

      Swal.showLoading();

      return fetch("/src/api/margin/update_checking", {
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
  }).then((result) => {
    if (result.isConfirmed && result.value) {
      Swal.fire("Tersimpan!", result.value, "success").then(async () => {
        await refreshTable(start, end, cabang);
      });
    }
  });
};

// Bulk Update
export const fetchBulkUpdateMargin = async (
  items,
  keterangan,
  start,
  end,
  cabang
) => {
  const token = getCookie("admin_token");
  const payload = {
    items: items,
    keterangan: keterangan,
    nama: sessionStorage.getItem("userName"),
  };

  Swal.showLoading();

  try {
    const response = await fetch("/src/api/margin/update_checking", {
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
      await refreshTable(start, end, cabang);
      return true;
    } else {
      throw new Error(res.message);
    }
  } catch (error) {
    Swal.fire("Gagal", error.message, "error");
    return false;
  }
};

export default { fetchUpdateMargin, fetchBulkUpdateMargin };
