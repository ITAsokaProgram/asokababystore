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

  // HTML Form untuk SweetAlert
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
    confirmButtonText: '<i class="fas fa-save mr-2"></i> Simpan',
    confirmButtonColor: "#db2777", // Pink-600
    cancelButtonText: "Batal",
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

      // Bungkus jadi array items
      const payload = {
        items: [{ ...data }],
        keterangan: keterangan,
        nama: data.nama,
        nama_user_cek: userCheck, // Tambahan
        kode_otorisasi: passAuth, // Tambahan
      };

      Swal.showLoading();

      return fetch("/src/api/margin/update_checking.php", {
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
          Swal.showValidationMessage(`${error.message}`);
        });
    },
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        icon: "success",
        title: "Berhasil",
        text: result.value, // Pesan dari server
        timer: 1500,
        showConfirmButton: false,
      }).then(async () => {
        await refreshTable(start, end, cabang);
      });
    }
  });
};

// Bulk Update
export const fetchBulkUpdateMargin = async (
  items,
  formValues, // Menerima object {keterangan, userCheck, passAuth}
  start,
  end,
  cabang
) => {
  const token = getCookie("admin_token");
  const payload = {
    items: items,
    keterangan: formValues.keterangan,
    nama_user_cek: formValues.userCheck,
    kode_otorisasi: formValues.passAuth,
    nama: sessionStorage.getItem("userName"),
  };

  Swal.showLoading();

  try {
    const response = await fetch("/src/api/margin/update_checking.php", {
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
