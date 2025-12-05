import { fetchCekData } from "../fetch/all_kategori.js";

export const renderAllKategori = (data, offset = 0) => {
  const tbody = document.querySelector("tbody");
  const thead = document.getElementById("tanggal");
  const theadKat = document.getElementById("thKat");
  const theadKet = document.getElementById("keterangan");
  thead.classList.remove("hidden");
  theadKat.classList.add("hidden");
  theadKet.textContent = "Keterangan";

  tbody.innerHTML = "";
  let row = "";
  data.forEach((item, index) => {
    const formatDate = new Date(item.tgl);
    const tgl = formatDate.toLocaleDateString("id-ID", {
      day: "2-digit",
      month: "long",
      year: "numeric",
    });

    row += `
        <tr class="border-b hover:bg-gray-50">
            <td class='px-4 py-2'> ${offset + index + 1} </td>
            <td class='px-4 py-2'> ${item.kode} </td>
            <td class='px-4 py-2'> ${item.kasir} </td>
            <td class='px-4 py-2'> ${item.kategori} </td>
            <td class='px-4 py-2'> ${tgl} </td>
            <td class='px-4 py-2'> ${item.cabang} </td>
            <td class="px-4 py-2">${item.ket ?? "-"} </td>
        </tr>
        `;
  });
  tbody.innerHTML = row;
};

export const renderDetailAllKategori = (data, offset = 0) => {
  const tbody = document.querySelector("#detailTbody");

  // Reset UI Bulk Update
  const checkAll = document.getElementById("checkAllDetail");
  if (checkAll) checkAll.checked = false;
  document.getElementById("btnBulkUpdate")?.classList.add("hidden");

  tbody.innerHTML = "";
  let row = "";
  data.forEach((item, index) => {
    const formatDate = new Date(item.tgl);
    const tgl = formatDate.toLocaleDateString("id-ID", {
      day: "2-digit",
      month: "long",
      year: "numeric",
    });

    // Membuat string JSON untuk value checkbox agar mudah diambil
    const rowData = JSON.stringify({
      kasir: item.kode,
      plu: item.barcode,
      tgl: item.tgl.split(" ")[0], // format YYYY-MM-DD
      jam: item.jam,
      kd_store: item.kode_toko,
      cabang: item.cabang, // backup
    });

    row += `
        <tr class="border-b hover:bg-gray-50 text-center">
            <td class="px-2 py-2">
                 <input type="checkbox" class="check-detail-item cursor-pointer w-4 h-4 text-pink-600 bg-gray-100 border-gray-300 rounded focus:ring-pink-500" value='${rowData}'>
            </td>
            <td class='px-4 '> ${offset + index + 1} </td>
            <td class='px-4 '> ${item.kode} </td>
            <td class='px-4  text-center'> ${item.kasir} </td>
            <td class='px-4 '> ${item.barcode} </td>
            <td class='px-4 truncate' title="${item.no_trans}"> ${
      item.no_trans
    } </td>
            <td class='px-4  text-left truncate' title="${
              item.nama_product
            }"> ${item.nama_product} </td>
            <td class='px-4 text-left truncate' title="${item.ket}"> ${
      item.ket
    } </td>
            <td class='px-4  text-center truncate' title="${tgl}"> ${tgl} </td>
            <td class='px-4  text-center'> ${item.jam} </td>
            <td class='px-4   text-center'> ${item.cabang} </td>
            <td class="px-4 text-center">
                  ${
                    item.ket_cek
                      ? `<button class="text-green-600 hover:text-green-800 lihat-keterangan" 
                                data-keterangan="${item.ket_cek}"
                                data-kode="${item.kode}" 
                                data-barcode="${item.barcode}"
                                data-toko="${item.kode_toko}"
                                data-tglU="${item.tgl.split(" ")[0]}"
                                data-jam="${item.jam}" 
                                title="Lihat keterangan">
                            <i class="fas fa-check-circle text-xl"></i>
                        </button>`
                      : `<button class="text-red-600 hover:text-red-800 periksa" 
                                  data-kode="${item.kode}" 
                                  data-barcode="${item.barcode}"
                                  data-toko="${item.kode_toko}"
                                  data-tglU="${item.tgl.split(" ")[0]}"
                                  data-kat = "${item.kategori}"
                                  data-jam="${item.jam}">
                              <i class="fas fa-times-circle text-xl"></i>
                        </button>`
                  }
                </td>
            </td>
        </tr>
        `;
  });
  tbody.innerHTML = row;
};
export const renderFilterByTanggal = (data, offset = 0) => {
  const tbody = document.querySelector("tbody");
  const thead = document.getElementById("tanggal");
  const theadKet = document.getElementById("keterangan");
  const theadKat = document.getElementById("thKat");
  theadKat.classList.remove("hidden");
  thead.classList.remove("hidden");
  theadKet.textContent = "Detail";
  tbody.innerHTML = "";
  let row = "";
  data.forEach((item, index) => {
    const formatDate = new Date(item.tgl);
    row += `
        <tr class="border-b hover:bg-gray-50">
            <td class='px-4 py-2'> ${offset + index + 1} </td>
            <td class='px-4 py-2'> ${item.kode} </td>
            <td class='px-4 py-2'> ${item.kasir} </td>
            <td class='px-4 py-2'> ${item.kategori} </td>
            <td class='px-4 py-2'> ${item.jml_gagal} </td>
            <td class='px-4 py-2'> ${item.start_periode} - ${
      item.end_periode
    }</td>
            <td class='px-4 py-2'> ${item.cabang} </td>
            <td class="px-4 py-2">
          <button class="text-blue-500 hover:underline text-xs lihat-detail" data-kode="${
            item.kode
          }" data-kat="${item.kategori}" id="btnDetail">Lihat Detail</button>
        </td>
        </tr>
        `;
  });
  tbody.innerHTML = row;
};

export const openDetailModal = () => {
  document.getElementById("detailInvalid").classList.remove("hidden");
  const btnCloseModal = document.getElementById("closeModal");
  btnCloseModal.addEventListener("click", (e) => {
    e.preventDefault();
    closeDetailModal();
  });
};

export const closeDetailModal = () => {
  document.getElementById("detailInvalid").classList.add("hidden");
};

export const renderTopInvalid = (data, offset = 0, limit = 20) => {
  const tbody = document.getElementById("void-table-body");
  let row = "";
  const end = Math.min(offset + limit, data.length);
  for (let i = offset; i < end; i++) {
    const item = data[i];
    row += `
      <tr class="hover:bg-red-50 cursor-pointer"
        data-kasir="${item.kode}"
        data-void="%${item.kategori.substring(0, 4)}%"
        data-tanggal="${item.tanggal.split(" ")[0]}"
        onclick="showModal(this)">
        <td class="text-center">${i + 1}</td>
        <td class="text-center">${item.tanggal.split(" ")[0]}</td>
        <td class="text-center font-mono text-xs">${item.kategori}</td>
        <td class="truncate text-center" title="${item.cabang}">${
      item.cabang
    }</td>
        <td class="truncate text-center" title="${item.kasir}">${
      item.kasir
    }</td>
        <td class="text-center text-green-700 font-bold">${
          item.jml_gagal ?? 0
        }</td>
      </tr>
    `;
  }
  tbody.insertAdjacentHTML("beforeend", row);
};

export default {
  renderAllKategori,
  renderFilterByTanggal,
  closeDetailModal,
  openDetailModal,
};
