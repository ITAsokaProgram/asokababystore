import { el } from "./../services/dom.js";
import {api} from "./../services/api.js";
export const summaryHandler = async () => {
   const res = await api.getSummary();
   el.TOTAL_MEMBER.textContent = res.data.total_member;
   el.MEMBER_NON_AKTIF.textContent = res.data.non_active_member;
   el.MEMBER_AKTIF.textContent = res.data.active_member;
}

