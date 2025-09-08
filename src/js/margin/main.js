import { kodeCabang } from "./../kode_cabang/kd.js";
import { paginationMargin } from "./table/pagination.js";
import { fetchFilterMargin, fetchMargin } from "./fetch/get_margin.js";
import { fetchUpdateMargin } from "./fetch/post_cek.js";
import { getKeterangan } from "./fetch/get_keterangan.js";
let selectValueCabang;
const init = async () => {
  sessionStorage.removeItem("default_table");
  const cabang = await kodeCabang("cabangFilter");
  const margin = await fetchMargin();
  const btnFilter = document.getElementById("filter");
  const selectCabang = document.getElementById("cabangFilter");
  const today = new Date();
  const yesterday = new Date();
  yesterday.setDate(today.getDate() - 1);
  const start = document.getElementById("startDate");
  const end = document.getElementById("endDate");
  start.value = formatDate(yesterday);
  end.value = formatDate(today);
  selectCabang.addEventListener("change", (e) => {
    selectValueCabang = e.target.value;
  });
  btnFilter.addEventListener("click", async (e) => {
    const filterMargin = await fetchFilterMargin(
      start.value,
      end.value,
      selectValueCabang
    );
    paginationMargin(1, 10, "filter_table");
  });

  paginationMargin(1, 10, "default_table");

  document.addEventListener("click", function (e) {
    const button = e.target.closest(".checking");
    if (!button) return;

    const data = {
      plu: button.getAttribute("data-plu"),
      bon: button.getAttribute("data-bon"),
      barang: button.getAttribute("data-barang"),
      qty: button.getAttribute("data-qty"),
      gros: button.getAttribute("data-gros"),
      net: button.getAttribute("data-net"),
      avg: button.getAttribute("data-avg"),
      ppn: button.getAttribute("data-ppn"),
      margin: button.getAttribute("data-margin"),
      tgl: button.getAttribute("data-tgl"),
      cabang: button.getAttribute("data-cabang"),
      nama: sessionStorage.getItem("userName"),
      kd: button.getAttribute("data-store"),
    };

    // Kirim semua data ke fungsi update
    fetchUpdateMargin(data, start.value, end.value, selectValueCabang);
  });
  document.addEventListener("click", async function (e) {
    const button = e.target.closest(".lihat-keterangan");
    if (!button) return;

    const status = button.getAttribute("data-status");
    const plu = button.getAttribute("data-plu");
    const bon = button.getAttribute("data-bon");
    const kodeCabang = button.getAttribute("data-cabang");
    const ket = document.getElementById("keterangan");
    const namaPIC = document.getElementById("nama_pic");
    const tanggalCek = document.getElementById("tanggal_cek");
    const keterangan = await getKeterangan(plu, bon, kodeCabang);
    const showInformation = document.getElementById("informasi");

    showInformation.classList.remove("hidden");
    ket.textContent = keterangan.data[0].ket_cek;
    namaPIC.textContent = keterangan.data[0].nama_cek;
    tanggalCek.textContent = keterangan.data[0].tanggal_cek
      ? new Date(keterangan.data[0].tanggal_cek).toLocaleDateString("id-ID", {
          day: "2-digit",
          month: "long",
          year: "numeric",
        })
      : "-";
  });
};

const formatDate = (date) => {
  return date.toISOString().split("T")[0];
};
init();
