// component/strukModal.js
import { $, } from "../services/dom.js";
import { fmt } from "../services/format.js";

export function showStrukModal(trx, currentMember) {
  $("strukTrxId").textContent = trx.trxId;
  $("strukTanggal").textContent = `${trx.tanggal}, ${trx.jam}`;
  $("strukKasir").textContent = trx.kasir;
  $("strukCabang").textContent = trx.cabang;
  $("strukMemberNama").textContent = trx.member;
  $("strukMemberNik").textContent = trx.nik;
  $("strukMemberHp").textContent = trx.phone;
  $("strukTotalPoin").textContent = fmt.poin(trx.poin);

  const itemsContainer = $("strukItems");
  if (trx.items?.length) {
    itemsContainer.innerHTML = trx.items.map(item => `
      <div class="flex justify-between items-center text-sm">
        <div class="flex-1">
          <p class="font-medium">${item.nama}</p>
          <p class="text-xs text-gray-500">${fmt.number(item.poin)} poin x ${item.qty}</p>
        </div>
        <div class="text-right"><p class="font-semibold">${fmt.number(item.total)} poin</p></div>
      </div>
    `).join("");
  } else {
    itemsContainer.innerHTML = `
      <div class="flex justify-between items-center text-sm">
        <div class="flex-1">
          <p class="font-medium">${trx.hadiah}</p>
          <p class="text-xs text-gray-500">${fmt.number(trx.poin)} poin x ${trx.qty}</p>
        </div>
        <div class="text-right"><p class="font-semibold">${fmt.number(trx.poin)} poin</p></div>
      </div>`;
  }

  const poinSebelum = trx.poin + (currentMember ? currentMember.poin : 1000);
  $("strukPoinSebelum").textContent = fmt.poin(poinSebelum);
  $("strukPoinSesudah").textContent = fmt.poin(currentMember ? currentMember.poin : (poinSebelum - trx.poin));

  $("modalStruk").classList.remove("hidden");
}

export function closeModal(id) {
  const el = $(id);
  if (el) el.classList.add("hidden");
}
