import { fetchAllKategori, fetchKategoriByTgl } from "./fetch/all_kategori.js";
import { filterByTanggal, initSearchFilter } from "./table/filter.js";
import pagination, { paginationKat } from "./table/pagination.js";
import { kodeCabang } from "../kode_cabang/kd.js";
const init = async () => {
  sessionStorage.removeItem("kategori_invalid");
  sessionStorage.removeItem("kategori_filtered");
  sessionStorage.removeItem("kategori_by_tanggal");
  sessionStorage.removeItem("kategori_source");
  const cabang = await kodeCabang("cabangFilter");
  filterByTanggal();
  const urlParams = new URLSearchParams(window.location.search);
  const hasFilterParams = urlParams.has("start") && urlParams.has("end");
  if (hasFilterParams) {
    sessionStorage.setItem("kategori_source", "kategori_by_tanggal");
    const startVal = urlParams.get("start");
    const endVal = urlParams.get("end");
    const katVal = urlParams.get("kategori") || "allKategori";
    const perVal = urlParams.get("periode") || "";
    const cabVal = urlParams.get("cabang") || "";
    document.getElementById("startDate").value = startVal;
    document.getElementById("endDate").value = endVal;
    document.getElementById("kategori").value = katVal;
    document.getElementById("periodeFilter").value = perVal;
    const cabEl = document.getElementById("cabangFilter");
    if (cabEl) cabEl.value = cabVal;
    await fetchKategoriByTgl(startVal, endVal, katVal, perVal, cabVal);
    paginationKat(1, 10, "kategori_by_tanggal");
    initSearchFilter("kategori_by_tanggal", "kategori_search_tanggal");
  } else {
    sessionStorage.setItem("kategori_source", "kategori_invalid");
    const allKategori = await fetchAllKategori();
    paginationKat(1, 10, "kategori_invalid");
    initSearchFilter("kategori_invalid", "kategori_search");
  }
  const resetBtn = document.getElementById("reset");
  resetBtn.addEventListener("click", (e) => {
    e.preventDefault();
    sessionStorage.setItem("kategori_source", "kategori_invalid");
    window.history.pushState({}, "", window.location.pathname);
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(today.getDate() - 1);
    document.getElementById("startDate").value = yesterday
      .toISOString()
      .split("T")[0];
    document.getElementById("endDate").value = today
      .toISOString()
      .split("T")[0];
    document.getElementById("kategori").value = "allKategori";
    document.getElementById("periodeFilter").value = "";
    document.getElementById("cabangFilter").value = "";
    fetchAllKategori().then(() => {
      paginationKat(1, 10, "kategori_invalid");
      initSearchFilter("kategori_invalid", "kategori_search");
    });
  });
};
init();