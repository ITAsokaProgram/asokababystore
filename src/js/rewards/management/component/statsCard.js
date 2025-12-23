// component/statsCard.js
import { $ } from "../services/dom.js";
import { fmt } from "../services/format.js";
import { api } from "../services/api.js";

export async function renderStats(transaksiData = []) {
  try {
    // Ambil data stats dari API
    const statusData = await api.getStatusCard();

    if (statusData.status && statusData.data) {
      const { trans, poin_tukar, claimed } = statusData.data;

      // Update stats cards dengan data dari API
      $("transaksiHariIni").textContent = trans || 0;
      $("poinHariIni").textContent = fmt.number(poin_tukar || 0);
      $("hadiahTerdistribusi").textContent = claimed || 0;
    } else {
      console.warn("Invalid status data:", statusData);
    }

    // Update filter counts dari data transaksi lokal
    updateFilterCounts(transaksiData);
  } catch (error) {
    console.error("Error fetching status card:", error);
    updateFilterCounts(transaksiData);
  }
}

function updateFilterCounts(transaksiData) {
  $("countAll").textContent = transaksiData.length;
  $("countSuccess").textContent = transaksiData.filter(
    (t) => t.status === "claimed"
  ).length;
  $("countPending").textContent = transaksiData.filter(
    (t) => t.status === "pending"
  ).length;
  $("countCancelled").textContent = transaksiData.filter(
    (t) => t.status === "cancelled"
  ).length;
}
