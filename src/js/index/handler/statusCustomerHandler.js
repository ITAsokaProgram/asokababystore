import fetchTransaksi from "../fetch/fetch_trans.js";
import { fetchStatusUser, fetchPointUser } from "../fetch/fetch_user.js";
import { getCookie } from "../utils/cookies.js";
import renderTransaksi from "./transaksiHandler.js";

const token = getCookie("customer_token");

export const statusCustomerHandler = async () => {
  const response = await fetchStatusUser(token);
  const kode = response.data.phone_number;
  const updateBuddy = response.data.updated;
  const isMember = response.data.status_member === "member";

  if (!kode) {
    Swal.fire({
      icon: "info",
      title: "Nomor Handphone Diperlukan",
      html: `
              <p class="text-gray-700">Silakan masukkan nomor handphone Anda untuk melihat riwayat transaksi.</p>
              <p class="italic text-sm text-red-500 mt-2">* Hanya berlaku untuk member terdaftar.</p>
          `,
      confirmButtonText: "Mengerti",
      allowOutsideClick: false,
      allowEscapeKey: false,
      customClass: {
        popup: "rounded-xl shadow-lg",
        title: "text-pink-600 font-semibold",
        confirmButton:
          "bg-pink-500 hover:bg-pink-600 text-white px-4 py-2 rounded",
      },
    }).then(() => {
      window.location.href = "/customer/profile";
    });
    return;
  }

  if (updateBuddy === 1 && isMember) {
    Swal.fire({
      icon: "info",
      title: "Lengkapi Data Diri Anda",
      html: `
              <p class="text-gray-700">Silakan lengkapi data diri Anda untuk menikmati semua fitur member.</p>
              <p class="italic text-sm text-blue-500 mt-2">* Data Anda akan membantu kami memberikan layanan yang lebih baik.</p>
          `,
      confirmButtonText: "Lengkapi Sekarang",
      allowOutsideClick: false,
      allowEscapeKey: false,
      customClass: {
        popup: "rounded-xl shadow-lg",
        title: "text-blue-600 font-semibold",
        confirmButton:
          "bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded",
      },
    }).then(() => {
      window.location.href = "/customer/profile";
    });
    return;
  }

  localStorage.setItem("kode", kode);
  const name = document.getElementById("nameUser");
  const poin = document.getElementById("poinCust");
  const masaBerlaku = document.getElementById("periodeBerlaku");
  const bulan = new Date().getMonth() + 1;
  const tahun = new Date().getFullYear();
  const status = document.getElementById("status");

  name.textContent = `Halo, ${response.data.nama_lengkap}`;
  status.textContent = response.data.status_member;

  try {
    if (response.status === "success") {
      const [responsePoin, responseTrans] = await Promise.all([
        fetchPointUser(token, kode),
        fetchTransaksi(token, kode, 3),
      ]);

      const totalPoin = responsePoin.data[0]?.total_poin_pk_pm ?? 0;
      poin.textContent = totalPoin;

      if (totalPoin <= 0) {
        masaBerlaku.textContent =
          "Belum punya poin? Saatnya belanja dan nikmati reward menarik";
      } else {
        masaBerlaku.textContent =
          bulan <= 6
            ? `Berakhir Pada : 30 Juni ${tahun}`
            : `Berakhir Pada : 31 Desember ${tahun}`;
      }

      renderTransaksi(responseTrans.data);
      const loader = document.getElementById("transaksi-loader");
      if (loader) loader.style.display = "none";
    }
  } catch (error) {
    console.log("error", error);
  }
};

export default statusCustomerHandler;
