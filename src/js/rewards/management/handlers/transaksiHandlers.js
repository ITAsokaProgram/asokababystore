// handlers/transaksiHandlers.js
import { store } from "../services/state.js";
import { api } from "../services/api.js";
import { $, } from "../services/dom.js";
import { nowParts } from "../services/dates.js";
import { renderTransaksiTable } from "../component/transaksiTable.js";
import { renderStats } from "../component/statsCard.js";
import { showStrukModal, closeModal } from "../component/strukModal.js";
import { renderPagination, updatePaginationInfo } from "../component/pagination.js";

export async function cariMember() {
  const search = $("searchMember").value.trim();
  if (!search) return alert("Masukkan NIK atau No. HP member");
  const member = await api.cariMember(search);
  store.set({ currentMember: member });
}

export async function getRewardTrade(options = {}) {
  try {
    const { pagination, currentFilter, dateFilter } = store.getState();
    
    // Jika sedang dalam mode date filter, jangan gunakan API normal
    if (currentFilter === "date_filtered" && dateFilter) {
      console.log("Currently in date filter mode, use applyDateFilter instead");
      return;
    }
    
    const apiOptions = {
      limit: pagination.limit,
      offset: (pagination.currentPage - 1) * pagination.limit,
      ...options
    };
    
    // Jika filter bukan "all", kirim ke API
    if (currentFilter && currentFilter !== "all") {
      apiOptions.status = currentFilter;
    }
    
    const rewardData = await api.getRewardTrade(apiOptions);
    if (rewardData.status === "success") {
      // Transform data structure to match table component expectations
      const transformedData = rewardData.data.map((item, index) => ({
        id: item.id,
        nama_lengkap: item.nama_lengkap || `Member ${item.id}`,
        number_phone: item.number_phone || "-",
        nama_hadiah: item.nama_hadiah,
        qty: item.qty || 1,
        poin_tukar: item.poin_tukar,
        dibuat_tanggal: item.dibuat_tanggal || "-" ,
        status: item.status,
        cabang: item.cabang || "-",
        kd_store: item.kd_store,
        expired_at: item.expired_at
      }));
      
      store.set({ transaksiData: transformedData });
      renderTransaksiTable(transformedData, currentFilter);
      
      // Update pagination info jika ada meta data dari API
      if (rewardData.meta) {
        updatePaginationInfo(rewardData.meta.total || rewardData.data.length);
      } else {
        // Fallback jika tidak ada meta, gunakan length data
        updatePaginationInfo(rewardData.data.length);
      }
    } else {
      console.error("API Error:", rewardData.message);
      alert("Gagal mengambil data hadiah: " + (rewardData.message || "Unknown error"));
    }
  } catch (error) {
    console.error("Fetch Error:", error);
    alert("Error saat mengambil data: " + error.message);
  }
}

export function refreshPoin() {
  const { currentMember } = store.getState();
  if (currentMember) alert("Poin berhasil di-refresh");
}


export function cetakStruk(trxId) {
  const { transaksiData, currentMember } = store.getState();
  const trx = transaksiData.find(t => t.id === trxId);
  if (trx) showStrukModal(trx, currentMember);
}

export function viewDetail(trxId) {
  const { transaksiData } = store.getState();
  const t = transaksiData.find(x => x.id === trxId);
  if (!t) return;
  alert(`Detail Transaksi ${t.trxId}:\n\nMember: ${t.member}\nHadiah: ${t.hadiah}\nPoin: ${t.poin}\nStatus: ${t.status}\nTanggal: ${t.tanggal} ${t.jam}`);
}

export function batalkanTransaksi(trxId) {
  if (!confirm("Yakin ingin membatalkan transaksi ini?\nPoin akan dikembalikan ke member.")) return;

  store.update(s => {
    const idx = s.transaksiData.findIndex(t => t.id === trxId);
    if (idx >= 0 && s.transaksiData[idx].status === "success") {
      const trx = { ...s.transaksiData[idx], status: "cancelled" };
      s.transaksiData = [...s.transaksiData.slice(0, idx), trx, ...s.transaksiData.slice(idx + 1)];
      if (s.currentMember && s.currentMember.nik === trx.nik) {
        s.currentMember = { ...s.currentMember, poin: s.currentMember.poin + trx.poin };
      }
    }
    return s;
  });

  const { transaksiData, currentFilter } = store.getState();
  renderStats(transaksiData);
  renderTransaksiTable(transaksiData, currentFilter);
  alert("Transaksi berhasil dibatalkan dan poin dikembalikan ke member!");
}

export function printStruk() { window.print(); }

export function downloadStruk() { alert("Fitur download akan segera tersedia!"); }

export function whatsappStruk() {
  const { transaksiData } = store.getState();
  const t = transaksiData[0];
  const msg = `*STRUK PENUKARAN HADIAH*\n\nID Transaksi: ${t.trxId}\nMember: ${t.member}\nHadiah: ${t.hadiah}\nPoin Digunakan: ${t.poin}\n\nTerima kasih atas kepercayaan Anda!`;
  const phone = (t.phone || "").replace(/\D/g, "");
  const url = `https://wa.me/${phone}?text=${encodeURIComponent(msg)}`;
  window.open(url, "_blank");
}
