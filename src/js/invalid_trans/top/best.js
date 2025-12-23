import { renderTopInvalid } from "../../../js/invalid_trans/table/all_kategori.js";
import { kodeCabang } from "../../kode_cabang/kd.js";
import {
  fetchTopInvalid,
  fetchDetailKategori,
  fetchExportDetails,
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
    const selectedValue = this.value;

    filteredData =
      selectedValue === "" || selectedValue === "all"
        ? dataVoid.data
        : dataVoid.data.filter((d) => d.kode_cabang === selectedValue);

    loadedCount = 0;
    document.getElementById("void-table-body").innerHTML = "";
    loadMoreData();
  });
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
  .addEventListener("click", async function () {
    const btn = this;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';
    btn.disabled = true;
    try {
      const cabangSelect = document.getElementById("cabang-select").value;
      const detailData = await fetchExportDetails(cabangSelect);
      if (!detailData || detailData.length === 0) {
        Toastify({
          text: "Tidak ada data untuk di-export",
          style: { background: "#f87171" },
        }).showToast();
        return;
      }
      const wsData = [
        [
          "No",
          "Cabang",
          "Tanggal",
          "Jam",
          "No Bon",
          "Kode Kasir",
          "Nama Kasir",
          "Nama Produk (DESCP)",
          "Qty",
          "Harga",
          "Keterangan",
        ],
        ...detailData.map((row, idx) => [
          idx + 1,
          row.cabang || "",
          row.tanggal || "",
          row.jam || "",
          row.no_bon || "",
          row.kode_kasir || "",
          row.nama_kasir || "",
          row.nama_product || "",
          row.qty || 0,
          row.harga || 0,
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
        { wch: 5 },
        { wch: 15 },
        { wch: 12 },
        { wch: 10 },
        { wch: 15 },
        { wch: 12 },
        { wch: 15 },
        { wch: 35 },
        { wch: 8 },
        { wch: 12 },
        { wch: 25 },
      ];
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, "Data Detail Invalid");
      XLSX.writeFile(wb, "Detail_Invalid_Transaction.xlsx");
    } catch (err) {
      console.error(err);
      Toastify({
        text: "Gagal membuat file excel",
        style: { background: "#f87171" },
      }).showToast();
    } finally {
      btn.innerHTML = originalText;
      btn.disabled = false;
    }
  });
