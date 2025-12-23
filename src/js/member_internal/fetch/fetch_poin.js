import renderPagination from "../pagination.js";
import { renderTablePoin } from "../member_table.js";
export const fetchMemberPoin = async (page = 1, limit = 15) => {
  showProgressBar();
  const statusVal = document.getElementById("status").value;
  const cabangVal = document.getElementById("filterCabang").value;
  try {
    const url = `/src/api/member/member_poin_fetch.php?page=${page}&limit=${limit}&status=${statusVal}&cabang=${encodeURIComponent(
      cabangVal
    )}`;
    const response = await fetch(url, {
      method: "GET",
      headers: { "Content-Type": "application/json" },
    });
    const data = await response.json();
    if (!data.success) {
      throw new Error(data.message);
    }
    if (data.stats) {
      const formatter = new Intl.NumberFormat("id-ID");
    }
    renderTablePoin({ data: data.data, page: data.page, limit: data.limit });
    renderPagination(data.total, page, limit, fetchMemberPoin);
  } catch (error) {
    Toastify({
      text: "Gagal memuat data",
      duration: 2000,
      style: { background: "#f87171" },
    }).showToast();
    console.error(error);
  } finally {
    completeProgressBar();
  }
};
export const loadMemberNonAktif = async (page = 1, limit = 10) => {
  try {
    const res = await fetch(
      `/src/api/member/member_poin_non_active?page=${page}&limit=${limit}`
    );
    const result = await res.json();
    if (res.status !== 200) {
      Toastify({
        text: "Data gagal dimuat",
        duration: 1000,
        gravity: "top",
        position: "right",
        style: {
          background: "#f87171",
          color: "$fff",
        },
      }).showToast();
    } else {
      const totalNonActive = document.getElementById("totalNonActive");
      if (totalNonActive) totalNonActive.textContent = result.total;
      renderTablePoin({
        data: result.data,
        page: result.page,
        limit: result.limit,
      });
      renderPagination(result.total, page, limit, loadMemberNonAktif);
    }
  } catch {
    Toastify({
      text: "Server Error",
      duration: 1000,
      gravity: "top",
      position: "right",
      style: {
        background: "#f87171",
        color: "$fff",
      },
    }).showToast();
    return;
  }
};
export const loadMemberAktif = async (page = 1, limit = 10) => {
  try {
    const res = await fetch(
      `/src/api/member/member_poin_active?page=${page}&limit=${limit}`
    );
    const result = await res.json();
    if (res.status !== 200) {
      Toastify({
        text: "Data gagal dimuat",
        duration: 1000,
        gravity: "top",
        position: "right",
        style: {
          background: "#f87171",
          color: "$fff",
        },
      }).showToast();
    } else {
      const totalActive = document.getElementById("activeMembers");
      if (totalActive) totalActive.textContent = result.total;
      renderTablePoin({
        data: result.data,
        page: result.page,
        limit: result.limit,
      });
      renderPagination(result.total, page, limit, loadMemberAktif);
    }
  } catch {
    Toastify({
      text: "Server Error",
      duration: 1000,
      gravity: "top",
      position: "right",
      style: {
        background: "#f87171",
        color: "$fff",
      },
    }).showToast();
    return;
  }
};
export const detailPoin = async (kode) => {
  const response = await fetch(
    `/src/api/member/member_poin_detail?kd_cust=${kode}`,
    {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Authorization: `Bearer ${localStorage.getItem("token")}`,
      },
    }
  );
  const data = await response.json();
  return data;
};
export default { fetchMemberPoin, loadMemberNonAktif, detailPoin };
