import { renderTopInvalid } from "../../../js/invalid_trans/table/all_kategori.js";
import { kodeCabang } from "../../kode_cabang/kd.js";
import {
  fetchTopInvalid,
  fetchDetailKategori,
} from "../../../js/invalid_trans/fetch/all_kategori.js";
let loadedCount = 0;
const pageSize = 20;
let dataVoid = { data: [] };
let filteredData = [];
const init = async () => {
  dataVoid = await fetchTopInvalid();
  filteredData = dataVoid.data;
  loadedCount = 0;
  await kodeCabang("cabang-select");
  const totalVoid = dataVoid.summaryTotalVoid[0].total_void;
  document.getElementById("total-void").textContent = totalVoid;
  const topCabang =
    dataVoid.summaryTopCabang[0].nm_alias +
    " (" +
    dataVoid.summaryTopCabang[0].total_void +
    ")";
  document.getElementById("top-cabang").textContent = topCabang;
  document.getElementById("void-table-body").innerHTML = "";
  loadMoreData();
  // Infinite scroll handler
  const tableContainer = document.getElementById("table-scroll-container");
  tableContainer.addEventListener("scroll", function () {
    const { scrollTop, scrollHeight, clientHeight } = tableContainer;
    if (
      scrollTop + clientHeight >= scrollHeight - 10 &&
      loadedCount < filteredData.length
    ) {
      loadMoreData();
    }
  });

  const cabangSelect = document.getElementById("cabang-select");
  cabangSelect.addEventListener("change", function () {
    filteredData =
      this.value === "all"
        ? dataVoid.data
        : dataVoid.data.filter((d) => d.kode_cabang === this.value);
    loadedCount = 0;
    document.getElementById("void-table-body").innerHTML = "";
    loadMoreData();
  });
  // Modal logic
  window.showModal = async function (row) {
    const kode = row.getAttribute("data-kasir");
    const kategori = row.getAttribute("data-void");
    const tanggal = row.getAttribute("data-tanggal");
    const today = new Date().toISOString().split("T")[0];
    const tbody = document.getElementById("modal-detail-tbody");
    const data = await fetchDetailKategori(kategori, kode, tanggal, today);
    tbody.innerHTML = "";
    data.data.forEach((item, index) => {
      tbody.innerHTML += `
      <tr class="hover:bg-pink-50 transition-colors duration-200">
    <td class="text-center font-bold text-pink-600">${index + 1}</td>
    <td class="font-semibold text-gray-800 truncate" title="${
      item.nama_product
    }">${item.nama_product}</td>
    <td class="text-center font-mono text-blue-700 truncate" title="${
      item.no_trans
    }">${item.no_trans}</td>
    <td class="text-center text-xs text-gray-500">${item.jam}</td>
    <td class="text-center text-indigo-600 font-semibold">${item.cabang}</td>
    <td class="text-center text-gray-700">${item.kasir}</td>
    <td class="text-center text-xs text-gray-500">${item.kode}</td>
    <td class="text-center text-xs text-gray-500">${item.tgl.split(" ")[0]}</td>
    <td class="text-center">
      ${
        item.ket
          ? `<span class='inline-block bg-pink-100 text-pink-700 px-2 py-1 rounded-full text-xs font-semibold'>${item.ket}</span>`
          : ""
      }
    </td>
  </tr>
      `;
    });
    document.getElementById("modal-detail").classList.remove("hidden");
  };
  window.closeModal = function () {
    document.getElementById("modal-detail").classList.add("hidden");
  };
};
function loadMoreData() {
  renderTopInvalid(filteredData, loadedCount, pageSize);
  loadedCount += pageSize;
}
init();
document
  .getElementById("btn-export-excel")
  .addEventListener("click", function () {
    const exportData = filteredData.slice(0, loadedCount);
    const wsData = [
      [
        "No",
        "Nama Produk",
        "No Bon",
        "Jam",
        "Cabang",
        "Kasir",
        "Kode Kasir",
        "Tanggal",
        "Keterangan",
      ],
      ...exportData.map((row, idx) => [
        idx + 1,
        row.nama_product || "",
        row.no_bon || "",
        row.jam || "",
        row.cabang || "",
        row.kasir || "",
        row.kode_kasir || "",
        row.tanggal || "",
        row.keterangan || "",
      ]),
    ];
    const ws = XLSX.utils.aoa_to_sheet(wsData);
    const range = XLSX.utils.decode_range(ws["!ref"]);
    for (let C = range.s.c; C <= range.e.c; ++C) {
      const cell = ws[XLSX.utils.encode_cell({ r: 0, c: C })];
      if (cell) {
        cell.s = {
          font: { bold: true, color: { rgb: "FFFFFF" } },
          fill: { fgColor: { rgb: "228B22" } },
          alignment: { horizontal: "center", vertical: "center" },
        };
      }
    }
    ws["!cols"] = [
      { wch: 5 }, // No
      { wch: 20 }, // Nama Produk
      { wch: 15 }, // No Bon
      { wch: 10 }, // Jam
      { wch: 15 }, // Cabang
      { wch: 15 }, // Kasir
      { wch: 15 }, // Kode Kasir
      { wch: 12 }, // Tanggal
      { wch: 30 }, // Keterangan
    ];
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Detail Invalid VOID");
    XLSX.writeFile(wb, "Detail_Invalid_VOID.xlsx");
  });
