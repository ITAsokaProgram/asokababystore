// handlers/filterHandlers.js
import { store } from "../services/state.js";
import { $ } from "../services/dom.js";
import { renderTransaksiTable } from "../component/transaksiTable.js";
import { getRewardTrade } from "./transaksiHandlers.js";
import { api } from "../services/api.js";
import { updatePaginationInfo } from "../component/pagination.js";

export async function filterByStatus(status) {
  const { pagination, dateFilter } = store.getState();

  // Update filter dan reset pagination ke halaman 1
  store.set({
    currentFilter: status,
    pagination: {
      ...pagination,
      currentPage: 1,
    },
  });

  // Update UI filter chips
  document.querySelectorAll(".filter-chip").forEach((chip) => {
    chip.classList.remove("active");
    chip.classList.add("inactive");
  });
  const el = document.querySelector(`[data-status="${status}"]`);
  if (el) {
    el.classList.add("active");
    el.classList.remove("inactive");
  }

  // Jika sedang dalam mode date filter, clear date filter dan kembali ke normal
  if (dateFilter && status !== "date_filtered") {
    store.set({ dateFilter: null });
  }

  // Fetch data dengan filter baru
  await getRewardTrade({
    limit: pagination.limit,
    offset: 0,
  });
}

export function toggleDateFilter() {
  $("dateFilter").classList.toggle("hidden");
}

export function setToday() {
  const today = new Date().toISOString().split("T")[0];
  $("filterDateFrom").value = today;
  $("filterDateTo").value = today;
}

export function setThisWeek() {
  const d = new Date();
  const first = new Date(d);
  first.setDate(d.getDate() - d.getDay());
  const last = new Date(d);
  last.setDate(d.getDate() - d.getDay() + 6);
  $("filterDateFrom").value = first.toISOString().split("T")[0];
  $("filterDateTo").value = last.toISOString().split("T")[0];
}

export function setThisMonth() {
  const d = new Date();
  const first = new Date(d.getFullYear(), d.getMonth(), 1);
  const last = new Date(d.getFullYear(), d.getMonth() + 1, 0);
  $("filterDateFrom").value = first.toISOString().split("T")[0];
  $("filterDateTo").value = last.toISOString().split("T")[0];
}

export function resetDateFilter() {
  $("filterDateFrom").value = "";
  $("filterDateTo").value = "";
  $("filterCabang").value = "";
  $("filterKasir").value = "";

  // Clear date filter dari state dan kembali ke data normal
  const { pagination } = store.getState();
  store.set({
    dateFilter: null,
    currentFilter: "all",
    pagination: {
      ...pagination,
      currentPage: 1,
    },
  });

  // Reload data normal
  getRewardTrade({
    limit: pagination.limit,
    offset: 0,
  });
}

export async function applyDateFilter() {
  const startDate = $("filterDateFrom").value;
  const endDate = $("filterDateTo").value;
  const cabang = $("filterCabang").value || "";

  if (!startDate || !endDate) {
    Toastify({
      text: "Harap pilih tanggal mulai dan tanggal akhir!",
      duration: 3000,
      gravity: "top",
      position: "right",
      backgroundColor: "#f87171",
    }).showToast();
    return;
  }

  if (new Date(startDate) > new Date(endDate)) {
    Toastify({
      text: "Tanggal mulai tidak boleh lebih besar dari tanggal akhir!",
      duration: 3000,
      gravity: "top",
      position: "right",
      backgroundColor: "#f87171",
    }).showToast();
    return;
  }

  try {
    const { pagination } = store.getState();

    // Reset pagination dan set filter
    store.set({
      dateFilter: { start: startDate, end: endDate, kd_store: cabang },
      pagination: {
        ...pagination,
        currentPage: 1,
      },
      currentFilter: "date_filtered",
    });

    // Panggil API untuk filter tanggal
    const filterData = await api.getDataByFilter({
      start: startDate,
      end: endDate,
      kd_store: cabang,
      limit: pagination.limit,
      offset: 0,
    });

    if (filterData.status === "success") {
      // Transform data sesuai format yang diharapkan tabel
      const transformedData = filterData.data.map((item) => ({
        id: item.id,
        nama_lengkap: item.nama_lengkap || `Member ${item.id}`,
        number_phone: item.number_phone || "-",
        nama_hadiah: item.nama_hadiah,
        qty: item.qty || 1,
        poin_tukar: item.poin_tukar,
        dibuat_tanggal: item.dibuat_tanggal || "-",
        status: item.status,
        cabang: item.cabang || "-",
        kd_store: item.kd_store,
        expired_at: item.expired_at,
      }));

      store.set({ transaksiData: transformedData });
      renderTransaksiTable(transformedData, "date_filtered");

      // Update pagination info dengan meta data dari API
      if (filterData.meta) {
        updatePaginationInfo(filterData.meta.total);
      }
      Toastify({
        text: `Filter berhasil diterapkan! Ditemukan ${filterData.data.length} data.`,
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: "#22c55e",
      }).showToast();
    } else {
      Toastify({
        text: "Gagal menerapkan filter: " + filterData.message,
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: "#f87171",
      }).showToast();
    }
  } catch (error) {
    console.error("Error applying date filter:", error);
    Toastify({
      text: "Error saat menerapkan filter: " + error.message,
      duration: 3000,
      gravity: "top",
      position: "right",
      backgroundColor: "#f87171",
    }).showToast();
  }

  toggleDateFilter();
}

export async function fetchCabangOptions() {
  try {
    const cabangData = await api.getDataCabang();
    if (cabangData.status === true) {
      const cabangSelect = $("filterCabang");
      cabangSelect.innerHTML = '<option value="">Semua Cabang</option>';
      cabangData.data.forEach((cabang) => {
        const option = document.createElement("option");
        option.value = cabang.store;
        option.textContent = cabang.nama_cabang;
        cabangSelect.appendChild(option);
      });
    } else {
      console.error("Failed to fetch cabang data:", cabangData.message);
    }
  } catch (error) {
    console.error("Error fetching cabang options:", error);
  }
}
