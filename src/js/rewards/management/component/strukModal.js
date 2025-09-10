// component/strukModal.js
import { $, } from "../services/dom.js";
import { fmt } from "../services/format.js";

export function showStrukModal(trx, currentMember) {
  // normalize transaction object fields to support API shape
  const trxId = trx.id ?? trx.trxId ?? 'N/A';
  const tanggal = trx.ditukar_tanggal ?? trx.dibuat_tanggal ?? trx.tanggal ?? '-';
  const jam = trx.jam ? ` ${trx.jam}` : '';
  const kasir = trx.kasir ?? '-';
  const cabang = trx.cabang ?? trx.kd_store ?? '-';
  const memberName = trx.nama_lengkap ?? trx.member ?? '-';
  const phone = trx.number_phone ?? trx.phone ?? '-';
  const totalPoin = trx.poin_tukar ?? trx.poin ?? 0;

  $("strukTrxId").textContent = trxId;
  $("strukTanggal").textContent = `${tanggal}${jam}`;
  $("strukKasir").textContent = kasir;
  $("strukCabang").textContent = cabang;
  $("strukMemberNama").textContent = memberName;
  $("strukMemberHp").textContent = phone;
  $("strukTotalPoin").textContent = fmt.poin(totalPoin);

  const itemsContainer = $("strukItems");
  // prefer explicit items array, otherwise build single-item from nama_hadiah
  const items = trx.items?.length ? trx.items : (trx.nama_hadiah ? [{ nama: trx.nama_hadiah, poin: totalPoin, qty: trx.qty ?? 1 }] : []);
  if (items.length) {
    itemsContainer.innerHTML = items.map(item => `
      <div class="flex justify-between items-center text-sm">
        <div class="flex-1">
          <p class="font-medium">${item.nama}</p>
          <p class="text-xs text-gray-500">${fmt.number(item.poin)} poin x ${item.qty ?? 1}</p>
        </div>
        <div class="text-right"><p class="font-semibold">${fmt.number((item.total ?? (item.poin * (item.qty ?? 1))))} poin</p></div>
      </div>
    `).join("");
  } else {
    itemsContainer.innerHTML = `<div class="text-sm text-gray-500">-</div>`;
  }

  // compute poin before & after if currentMember.poin available
  if (currentMember && typeof currentMember.poin === 'number') {
    const poinSebelum = currentMember.poin + totalPoin;
    const poinSesudah = currentMember.poin;
    $("strukPoinSebelum").textContent = fmt.poin(poinSebelum);
    $("strukPoinSesudah").textContent = fmt.poin(poinSesudah);
  } else {
    $("strukPoinSebelum").textContent = '-';
    $("strukPoinSesudah").textContent = '-';
  }

  $("modalStruk").classList.remove("hidden");
}

export function closeModal(id) {
  const el = $(id);
  if (el) el.classList.add("hidden");
}
