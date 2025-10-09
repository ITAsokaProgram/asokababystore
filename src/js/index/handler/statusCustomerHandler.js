import fetchTransaksi from "../fetch/fetch_trans.js";
import { fetchStatusUser, fetchPointUser } from "../fetch/fetch_user.js";
import { getCookie } from "../utils/cookies.js";
import renderTransaksi from "./transaksiHandler.js";
const token = getCookie("token");

export const statusCustomerHandler = async () => {
  const response = await fetchStatusUser(token);
  const kode = response.data.phone_number;
  const updateBuddy = response.data.updated;
  if (!kode) {
    Swal.fire({
      icon: "info",
      title: "Nomor Handphone Diperlukan",
      html: `
            <p class="text-gray-700">Silakan masukkan nomor handphone Anda untuk melihat riwayat transaksi.</p>
            <p class="italic text-sm text-red-500 mt-2">* Hanya berlaku untuk member terdaftar.</p>
        `,
      confirmButtonText: "Mengerti",
      customClass: {
        popup: "rounded-xl shadow-lg",
        title: "text-pink-600 font-semibold",
        confirmButton:
          "bg-pink-500 hover:bg-pink-600 text-white px-4 py-2 rounded",
      },
    }).then(() => {
      window.location.href = "/customer/profile";
    });
  }

  if(updateBuddy === 1){
    Swal.fire({
      icon: 'info',
      title: 'Informasi',
      text: 'Data diri anda belum di isi yuk isi dulu',
      showConfirmButton: true
    }).then(() => {
      window.location.href = "/customer/profile";
    });
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
      const responsePoin = await fetchPointUser(token, kode);
      const responseTrans = await fetchTransaksi(token, kode);
      poin.textContent = responsePoin.data[0]?.total_poin_pk_pm ?? 0;

      if (responsePoin.data[0]?.total_poin_pk_pm <= 0) {
        masaBerlaku.textContent = "Belum punya poin? Saatnya belanja dan nikmati reward menarik";
      } else {
        masaBerlaku.textContent =
          bulan <= 6
            ? `Berakhir Pada : 30 Juni ${tahun}`
            : `Berakhir Pada : 31 Desember ${tahun}`;
      }
      renderTransaksi(responseTrans.data);
      // Sembunyikan loader setelah data dirender
      const loader = document.getElementById("transaksi-loader");
      if (loader) loader.style.display = "none";
    }
  } catch {
    console.log("error");
  }
};

export default statusCustomerHandler;
