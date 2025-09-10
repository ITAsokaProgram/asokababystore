// component/transaksiTable.js
import { $, } from "../services/dom.js";
import { fmt, statusUI } from "../services/format.js";
import { store } from "../services/state.js";

export function renderTransaksiTable(transaksiData, currentFilter) {
  const tableBody = $("tableBody");
  const { searchKeyword, filteredData } = store.getState();
  
  let data;
  
  // Tentukan data yang akan ditampilkan
  if (currentFilter === "search" && filteredData) {
    data = filteredData;
  } else if (currentFilter === "all" || currentFilter === "date_filtered") {
    data = transaksiData;
  } else {
    data = transaksiData.filter(t => t.status === currentFilter);
  }

  // Helper function untuk highlight search keyword
  function highlightText(text, keyword) {
    if (!keyword || !text) return text;
    const regex = new RegExp(`(${keyword})`, 'gi');
    return text.replace(regex, '<mark class="bg-yellow-200 px-1 rounded">$1</mark>');
  }

  tableBody.innerHTML = data.map((trx, idx) => `
    <tr class="hover:bg-blue-50/50 transition-colors duration-200">
      <td class="px-4 py-4 text-center">${idx + 1}</td>
      <td class="px-4 py-4"><span class="font-mono text-xs bg-blue-100 px-2 py-1 rounded">${trx.id || 'N/A'}</span></td>
      <td class="px-4 py-4">
        <div class="flex items-center gap-2">
          <div class="bg-blue-100 p-2 rounded-full"><i class="fas fa-user text-blue-600 text-xs"></i></div>
          <div>
            <p class="font-medium text-gray-800">${highlightText(trx.nama_lengkap || 'N/A', searchKeyword)}</p>
            <p class="text-xs text-gray-500">${highlightText(trx.number_phone || 'N/A', searchKeyword)}</p>
          </div>
        </div>
      </td>
      <td class="px-4 py-4">
        <div class="flex items-center gap-2">
          <i class="fas fa-gift text-blue-500"></i>
          <span class="font-medium">${highlightText(trx.nama_hadiah || 'N/A', searchKeyword)}</span>
        </div>
      </td>
      <td class="px-4 py-4 text-center"><span class="bg-gray-100 px-2 py-1 rounded text-xs font-medium">${trx.qty ?? 1}</span></td>
      <td class="px-4 py-4 text-center"><span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">${fmt.number(trx.poin_tukar || 0)}</span></td>
      <td class="px-4 py-4"><div class="text-xs"><div class="font-medium">${trx.dibuat_tanggal || 'N/A'}</div></div></td>
      <td class="px-4 py-4"><div class="text-xs"><div class="font-medium">${trx.ditukar_tanggal || '-'}</div></div></td>
      <td class="px-4 py-4"><div class="text-xs"><div class="font-medium">${trx.expired_at || '-'}</div></div></td>
      <td class="px-4 py-4 text-center">
        <span class="${statusUI.cls(trx.status)} px-3 py-1 rounded-full text-xs font-medium">
          <i class="${statusUI.icon(trx.status)} mr-1"></i>${highlightText(trx.status, searchKeyword)}
        </span>
      </td>
      <td class="px-4 py-4">${highlightText(trx.cabang || trx.kd_store || 'N/A', searchKeyword)}</td>
      <td class="px-4 py-4">
        <div class="flex items-center justify-center gap-1">
          <button onclick="cetakStruk(${trx.id})" class="p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors duration-200" title="Cetak Struk">
            <i class="fas fa-receipt text-xs"></i>
          </button>
          
          ${trx.status === "pending" ? `
          <button onclick="batalkanTransaksi(${trx.id})" class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors duration-200" title="Batalkan">
            <i class="fas fa-times text-xs"></i>
          </button>` : ``}
        </div>
      </td>
    </tr>
  `).join("");
}
