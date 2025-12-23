// handlers/modalHandlers.js
import { store } from "../services/state.js";
import { $, } from "../services/dom.js";
import { hideMemberInfo } from "../component/memberInfo.js";

export function transaksiBaruModal() {
  $("modalTransaksiBaru").classList.remove("hidden");
  resetForm();
}

export function closeModal(modalId) {
  const el = $(modalId);
  if (el) el.classList.add("hidden");
}

export function resetForm() {
  $("formTransaksiBaru").reset();
  hideMemberInfo();
  store.set({ cartItems: [], currentMember: null });
  $("prosesTransaksi").disabled = true;
}
