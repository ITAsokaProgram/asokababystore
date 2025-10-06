import {
  fetchKategoriByTgl,
  fetchDetailKategori,
  fetchCekData,
  fetchKeterangan,
} from "../fetch/all_kategori.js";
import { openDetailModal } from "./all_kategori.js";
import { paginationKat, paginationDetail } from "./pagination.js";
let start = "",
  end = "";
let selectValuePeriode = "";
let selectValueCabang = "";
const formatDate = (date) => {
  const d = new Date(date);
  const dd = String(d.getDate()).padStart(2, "0");
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const yyyy = d.getFullYear();
  return `${yyyy}-${mm}-${dd}`; // contoh: 03-07-2025
};

export const filterByTanggal = () => {
  const periodeSelect = document.getElementById("periodeFilter");
  const startDateInput = document.getElementById("startDate");
  const endDateInput = document.getElementById("endDate");
  const kategoriSelect = document.getElementById("kategori");
  const today = new Date();
  const yesterday = new Date(today);
  yesterday.setDate(today.getDate() - 1);
  startDateInput.value = formatDate(yesterday);
  endDateInput.value = formatDate(today);
  periodeSelect.addEventListener("change", () => {
    const today = new Date();
    const value = periodeSelect.value;
    switch (value) {
      case "harian":
        const yesterday = new Date();
        yesterday.setDate(today.getDate() - 1);
        start = formatDate(yesterday);
        end = formatDate(today);
        break;

      case "mingguan":
        const weekStart = new Date(today);
        weekStart.setDate(today.getDate() - 6);
        start = formatDate(weekStart);
        end = formatDate(today);
        break;

      case "bulanan":
        const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
        const monthEnd = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        start = formatDate(monthStart);
        end = formatDate(monthEnd);
        break;

      case "tahunan":
        const yearStart = new Date(today.getFullYear(), 0, 1);
        const yearEnd = new Date(today.getFullYear(), 11, 31);
        start = formatDate(yearStart);
        end = formatDate(yearEnd);
        break;

      default:
        start = "";
        end = "";
        break;
    }

    startDateInput.value = start;
    endDateInput.value = end;
  });
  const btn = document.getElementById("filterTanggalBtn");
  const selectPeriode = document.getElementById("periodeFilter");
  const selectCabang = document.getElementById("cabangFilter");
  btn.addEventListener("click", async (e) => {
    e.preventDefault();

    const kategoriValue = kategoriSelect.value;

    // Tunggu fetch selesai
    const data = await fetchKategoriByTgl(
      startDateInput.value,
      endDateInput.value,
      kategoriValue,
      selectPeriode.value,
      selectCabang.value
    );
    paginationKat(1, 10, "kategori_by_tanggal");
    initSearchFilter("kategori_by_tanggal", "kategori_search_tanggal");
  });

  document
    .getElementById("allTable")
    .addEventListener("click", async function (e) {
      const btn = e.target.closest(".lihat-detail");
      if (!btn) return;

      const kode = btn.getAttribute("data-kode");
      const kategori = btn.getAttribute("data-kat");
      const split = kategori.split(" ")[0];
      const likeKategori = `%${split}%`; 
      if (kode && kategori) {
        openDetailModal();
        await fetchDetailKategori(
          likeKategori,
          kode,
          startDateInput.value,
          endDateInput.value
        );
        paginationDetail(1, 10, "detail_kategori");
      }
    });
  document.addEventListener("click", async function (e) {
    const button = e.target.closest(".periksa");
    if (!button) return;
    const kode = button.getAttribute("data-kode");
    const kategori = button.getAttribute("data-kat");
    const data = {
      nama: sessionStorage.getItem("userName"),
      kasir: button.getAttribute("data-kode"),
      plu: button.getAttribute("data-barcode"),
      tgl: button.getAttribute("data-tglU"),
      jam: button.getAttribute("data-jam"),
      kd_store: button.getAttribute("data-toko"),
    };

    await fetchCekData(
      data,
      kategori,
      kode,
      startDateInput.value,
      endDateInput.value
    );
  });
  document.addEventListener("click", async function (e) {
    const button = e.target.closest(".lihat-keterangan");
    const showInformation = document.getElementById("informasi");
    const namaPIC = document.getElementById("nama_pic");
    const keterangan = document.getElementById("ketM");
    if (!button) return;
    const data = {
      kasir: button.getAttribute("data-kode"),
      plu: button.getAttribute("data-barcode"),
      tgl: button.getAttribute("data-tglU"),
      jam: button.getAttribute("data-jam"),
      cabang: button.getAttribute("data-toko"),
    };

    const ket = await fetchKeterangan(
      data.plu,
      data.kasir,
      data.tgl,
      data.jam,
      data.cabang
    );
    showInformation.classList.remove("hidden");
    namaPIC.textContent = ket.data[0].nama_cek;
    keterangan.textContent = ket.data[0].ket_cek;
  });
};

const searchFilter = (options) => {
  const {
    inputId = "search",
    sessionKey = "kategori_invalid",
    searchField = "kode",
    outputKey = "kategori_search",
    renderFunction = paginationKat,
  } = options;

  const search = document.getElementById(inputId);
  if (!search) return;

  search.addEventListener("input", () => {
    const keyword = search.value.trim().toLowerCase();
    const session = sessionStorage.getItem(sessionKey);
    if (!session) return;

    try {
      const parsed = JSON.parse(session);
      const data = parsed.data;

      const filtered = data.filter((item) => {
        const field = item[searchField];
        return field?.toString().toLowerCase().includes(keyword);
      });

      // simpan hasil pencarian
      sessionStorage.setItem(outputKey, JSON.stringify({ data: filtered }));

      // render ulang
      renderFunction(1, 10, outputKey);
    } catch (err) {
      console.error("Error parsing sessionStorage data:", err);
    }
  });
};

export const initSearchFilter = (mode = "kategori_invalid", output) => {
  const sessionKey = mode; // contoh: "kategori_filtered" atau "kategori_invalid"
  const outputKey = output;

  searchFilter({
    inputId: "search",
    sessionKey: sessionKey,
    searchField: "kode",
    outputKey: outputKey,
    renderFunction: paginationKat,
  });
};

export default { initSearchFilter, filterByTanggal };
