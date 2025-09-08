import { fetchMemberPoin } from "./fetch/fetch_poin.js";
import { filterMember, searchMember } from "./filter.js";
let currentPage = 1;
let limit = 15;

const init = async () => {
    const data = await fetchMemberPoin(currentPage, limit);
    filterMember();
    searchMember()
}
init();