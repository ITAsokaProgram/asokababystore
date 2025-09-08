import { fetchMemberPoin, loadMemberAktif, loadMemberNonAktif } from "./fetch/fetch_poin.js";
import renderPagination from "./pagination.js";
import { renderTablePoin } from "./member_table.js";
let currentPage = 1;
let limit = 15;
export const filterMember = () => {
    const select = document.getElementById('status');
    select.addEventListener("change", () => {
        const selectValue = select.value;
        if (selectValue === "allStatus") {
            fetchMemberPoin(currentPage, limit)
        } else if (selectValue === "aktif") {
            loadMemberAktif(currentPage, limit)
        } else {
            loadMemberNonAktif(currentPage, limit)
        }
    })
}

export const searchMember = () => {
    const searchInput = document.getElementById('search');

    const fetchSearch = debounce((keyword, page = 1, limit = 15) => {
        if (keyword.length < 3) return;

        fetch(`/src/api/member/search_member?keyword=${encodeURIComponent(keyword)}&page=${page}&limit=${limit}`)
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    renderTablePoin({ data: result.data, page: result.page, limit: result.limit });
                    renderPagination(result.total, result.page, result.limit, (newPage) => {
                        fetchSearch(keyword, newPage, limit);
                    });
                }
            });
    }, 300);

    searchInput.addEventListener('input', () => {
        const keyword = searchInput.value.trim();
        if (keyword === "") {
            fetchMemberPoin(1, 15);
        } else {
            fetchSearch(keyword);
        }
    });
}

// Fungsi debounce
function debounce(func, delay) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), delay);
    };
}

export default { filterMember, searchMember };