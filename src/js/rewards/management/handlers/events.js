// handlers/events.js
import { $, } from "../services/dom.js";
import * as Transaksi from "./transaksiHandlers.js";
import * as Filter from "./filterHandlers.js";
import * as Modal from "./modalHandlers.js";

export function bindEvents() {
  // Form
  $("formTransaksiBaru")?.addEventListener("submit", Transaksi.prosesTransaksiSubmit);

  // Cart
  $("qtyHadiah")?.addEventListener("change", () => {});
  $("pilihHadiah")?.addEventListener("change", () => {});
  
  // Delegasi ke window (kompatibel dgn onclick di HTML lama)
  Object.assign(window, {
    transaksiBaruModal: Modal.transaksiBaruModal,
    closeModal: Modal.closeModal,
    cariMember: Transaksi.cariMember,
    refreshPoin: Transaksi.refreshPoin,
    prosesTransaksiSubmit: Transaksi.prosesTransaksiSubmit,
    cetakStruk: Transaksi.cetakStruk,
    viewDetail: Transaksi.viewDetail,
    batalkanTransaksi: Transaksi.batalkanTransaksi,
    printStruk: Transaksi.printStruk,
    downloadStruk: Transaksi.downloadStruk,
    whatsappStruk: Transaksi.whatsappStruk,
    filterByStatus: Filter.filterByStatus,
    toggleDateFilter: Filter.toggleDateFilter,
    setToday: Filter.setToday,
    setThisWeek: Filter.setThisWeek,
    setThisMonth: Filter.setThisMonth,
    resetDateFilter: Filter.resetDateFilter,
    applyDateFilter: Filter.applyDateFilter,
  });

  // Tutup modal saat klik di backdrop
  document.addEventListener("click", (ev) => {
    ["modalTransaksiBaru", "modalStruk"].forEach(id => {
      const modal = $(id);
      if (ev.target === modal) Modal.closeModal(id);
    });
  });
}
