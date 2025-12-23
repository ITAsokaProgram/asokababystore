import {
  fetchMemberPoin,
  loadMemberAktif,
  loadMemberNonAktif,
} from "./fetch/fetch_poin.js";
import renderPagination from "./pagination.js";
import { renderTablePoin } from "./member_table.js";
let currentPage = 1;
let limit = 15;
const loadCabangOptions = async () => {
  try {
    const response = await fetch("/src/api/option/get_cabang.php");
    const result = await response.json();
    if (result.success) {
      const select = document.getElementById("filterCabang");
      result.data.forEach((store) => {
        const option = document.createElement("option");
        option.value = store.Nm_Alias;
        option.textContent = store.Nm_Alias;
        select.appendChild(option);
      });
    }
  } catch (error) {
    console.error("Gagal memuat cabang:", error);
  }
};
export const filterMember = () => {
  const selectStatus = document.getElementById("status");
  const selectCabang = document.getElementById("filterCabang");
  loadCabangOptions();
  selectStatus.addEventListener("change", () => {
    fetchMemberPoin(1, 15);
  });
  selectCabang.addEventListener("change", () => {
    fetchMemberPoin(1, 15);
  });
};
export const searchMember = () => {
  const searchInput = document.getElementById("search");
  const fetchSearch = debounce((keyword, page = 1, limit = 15) => {
    if (keyword.length < 3) return;
    fetch(
      `/src/api/member/search_member?keyword=${encodeURIComponent(
        keyword
      )}&page=${page}&limit=${limit}`
    )
      .then((res) => res.json())
      .then((result) => {
        if (result.success) {
          renderTablePoin({
            data: result.data,
            page: result.page,
            limit: result.limit,
          });
          renderPagination(
            result.total,
            result.page,
            result.limit,
            (newPage) => {
              fetchSearch(keyword, newPage, limit);
            }
          );
        }
      });
  }, 300);
  searchInput.addEventListener("input", () => {
    const keyword = searchInput.value.trim();
    if (keyword === "") {
      fetchMemberPoin(1, 15);
    } else {
      fetchSearch(keyword);
    }
  });
};
function debounce(func, delay) {
  let timeout;
  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), delay);
  };
}
export default { filterMember, searchMember };
