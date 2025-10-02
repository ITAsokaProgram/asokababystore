import { updateUI, loadInvalidTransaksi, loadReviewData } from "/src/js/dashboard/interface.js";

const API_URL = "/src/api/dashboard/get_data_transaction";

window.addEventListener("DOMContentLoaded", () => {
  // Fetch transaction data
  fetch(API_URL)
    .then((res) => {
      if (!res.ok) throw new Error("Network response was not ok");
      return res.json();
    })
    .then((data) => {
      updateUI(data.data);
    })
    .catch((err) => {
      console.error("Gagal fetch data:", err);
      updateUI(null);
    });

  // Load invalid transaksi data
  loadInvalidTransaksi();

  // Load review data
  loadReviewData();

  // Add event listeners for view all buttons
  directBtn("view-all-invalid", "/src/fitur/transaction/invalid_trans");
  directBtn("view-all-margin-minus", "/src/fitur/transaction/margin");
  directBtn("view-all-transaksi", "/src/fitur/transaction/transaksi_cabang");
  directBtn("view-all-retur", "/src/fitur/transaction/invalid_trans");
  directBtn("view-all-activity", "/src/fitur/laporan/in_customer");
  directBtn("view-all-review", "/src/fitur/laporan/in_review_cust");
});

const directBtn = (id, link) => {
  const btn = document.getElementById(id);
  if (btn) {
    btn.addEventListener("click", () => {
      window.location.href = link;
    });
  }
};