import getCookie from "../../index/utils/cookies.js";
import { paginationMargin } from "../table/pagination.js";
import { fetchMargin, fetchFilterMargin } from "./get_margin.js";

export const fetchUpdateMargin = (data, start, end , cabang) => {
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

      const payload = {
        ...data,
        keterangan: keterangan,
      };

      // Tampilkan loading
      Swal.showLoading();

      return fetch("/src/api/margin/update_checking", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${token}`,
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
          await fetchFilterMargin(start, end, cabang);
          paginationMargin(1, 10, "filter_table");
      });
    }
  });
};

export default { fetchUpdateMargin };
