import { kodeCabang } from "./../kode_cabang/kd.js";
import { paginationMargin } from "./table/pagination.js";
import { fetchFilterMargin, fetchMargin } from "./fetch/get_margin.js";
import { fetchUpdateMargin } from "./fetch/post_cek.js";
import { getKeterangan } from "./fetch/get_keterangan.js";
const init = async () => {
  // ! INI KALO ABIS INSERT DATA TIDAK DI TEMUKAN YG ENDPOINT NYA PREFIX NYA FILTER, FIX
  sessionStorage.removeItem("default_table");
  await kodeCabang("cabangFilter");
  const margin = await fetchMargin();
  const btnFilter = document.getElementById("filter");
  const selectCabang = document.getElementById("cabangFilter");
  const start = document.getElementById("startDate");
  const end = document.getElementById("endDate");
  const today = new Date();
  const yesterday = new Date();
  yesterday.setDate(today.getDate() - 1);
  start.value = formatDate(yesterday);
  end.value = formatDate(today);
  btnFilter.addEventListener("click", async (e) => {
    const cabangValue = selectCabang.value;
    if (!cabangValue) {
      Toastify({
        text: "Silahkan pilih cabang terlebih dahulu",
        duration: 2000,
        style: { background: "#f59e0b" },
      }).showToast();
      return;
    }
    const filterMargin = await fetchFilterMargin(
      start.value,
      end.value,
      cabangValue
    );
    paginationMargin(1, 10, "filter_table");
  });
  paginationMargin(1, 10, "default_table");
  document.addEventListener("click", function (e) {
    const button = e.target.closest(".checking");
    if (!button) return;
    const currentCabang = selectCabang.value;
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
    fetchUpdateMargin(data, start.value, end.value, currentCabang);
  });
  document.addEventListener("click", async function (e) {
    const button = e.target.closest(".lihat-keterangan");
    if (!button) return;
    const plu = button.getAttribute("data-plu");
    const bon = button.getAttribute("data-bon");
    const kodeCabangAttr = button.getAttribute("data-cabang");
    const ket = document.getElementById("keterangan");
    const namaPIC = document.getElementById("nama_pic");
    const tanggalCek = document.getElementById("tanggal_cek");
    const keterangan = await getKeterangan(plu, bon, kodeCabangAttr);
    const showInformation = document.getElementById("informasi");
    showInformation.classList.remove("hidden");
    ket.textContent = keterangan?.data?.[0]?.ket_cek || "-";
    namaPIC.textContent = keterangan?.data?.[0]?.nama_cek || "-";
    tanggalCek.textContent = keterangan?.data?.[0]?.tanggal_cek
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
