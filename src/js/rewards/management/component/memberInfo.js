// component/memberInfo.js
import { $, } from "../services/dom.js";
import { fmt } from "../services/format.js";

export function showMemberInfo(member) {
  $("memberNama").textContent = member.nama;
  $("memberDetail").textContent = `NIK: ${member.nik} | HP: ${member.phone}`;
  $("memberTerdaftar").textContent = `Member sejak: ${member.terdaftar}`;
  $("memberPoin").textContent = fmt.number(member.poin);
  $("poinTersedia").value = fmt.poin(member.poin);
  $("memberInfo").classList.remove("hidden");
}

export function hideMemberInfo() {
  $("memberInfo").classList.add("hidden");
}
