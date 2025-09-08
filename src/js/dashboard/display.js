import { updateUI, loadInvalidTransaksi } from "/src/js/dashboard/interface.js";
const API_URL = "/src/api/dashboard/get_data_transaction";
window.addEventListener("DOMContentLoaded", () => {

    // Fetch dari API jika tidak ada cache atau expired
    fetch(API_URL)
      .then((res) => {
        if (!res.ok) throw new Error("Network response was not ok");
        return res.json();
      })
      .then((data) => {
        // Simpan ke cache
        updateUI(data.data);
      })
      .catch((err) => {
        console.error("Gagal fetch data:", err);
        updateUI(null); // fallback ke tampilan kosong
      });


  // Load invalid transaksi data
  loadInvalidTransaksi();

  // Add event listener for view all button
  directBtn("view-all-invalid", "/src/fitur/transaction/invalid_trans");
  directBtn("view-all-margin-minus", "/src/fitur/transaction/margin");
  directBtn("view-all-transaksi", "/src/fitur/transaction/transaksi_cabang");
  directBtn("view-all-retur", "/src/fitur/transaction/invalid_trans");
  directBtn("view-all-activity", "/src/fitur/laporan/in_customer");
});

const directBtn = (id,link) => {
  const btn = document.getElementById(id);
  if (btn) {
    btn.addEventListener("click", () => {
      window.location.href = link;
    });
  }
}
