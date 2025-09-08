import { fetchAllKategori } from "./fetch/all_kategori.js";
import { filterByTanggal, initSearchFilter } from "./table/filter.js";
import pagination, { paginationKat } from "./table/pagination.js";
import { kodeCabang } from "../kode_cabang/kd.js";
const init = async () => {
  sessionStorage.removeItem("kategori_invalid");
  sessionStorage.removeItem("kategori_filtered");
  sessionStorage.removeItem("kategori_by_tanggal");

  const allKategori = await fetchAllKategori();
  const cabang = await kodeCabang("cabangFilter");
  const resetBtn = document.getElementById("reset");
  resetBtn.addEventListener("click", (e) => {
    e.preventDefault();
    paginationKat(1, 10, "kategori_invalid");
    initSearchFilter("kategori_invalid", "kategori_search");    
  });
  paginationKat(1, 10, "kategori_invalid");
  initSearchFilter("kategori_invalid", "kategori_search");
  filterByTanggal();
};
init();
