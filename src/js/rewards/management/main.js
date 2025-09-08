// main.js
import { store } from "./services/state.js";
import { renderStats } from "./component/statsCard.js";
import { renderTransaksiTable } from "./component/transaksiTable.js";
import { bindEvents } from "./handlers/events.js";
import { getRewardTrade } from "./handlers/transaksiHandlers.js";
import { initPagination } from "./component/pagination.js";
import { 
  filterByStatus, 
  toggleDateFilter, 
  setToday, 
  setThisWeek, 
  setThisMonth, 
  resetDateFilter,
  applyDateFilter, 
  fetchCabangOptions
} from "./handlers/filterHandlers.js";
import { 
  setupSearchListeners, 
  searchFromState, 
  clearSearch 
} from "./handlers/searchHandlers.js";

document.addEventListener("DOMContentLoaded", async () => {
  // Initial render
  const { transaksiData, currentFilter } = store.getState();
  await renderStats(transaksiData);
  renderTransaksiTable(transaksiData, currentFilter);

  // Initialize pagination
  initPagination();

  // Initialize search functionality
  setupSearchListeners();

  // Events
  bindEvents();

  // Add event listener untuk tombol filterApply
  const filterApplyBtn = document.getElementById('filterApply');
  if (filterApplyBtn) {
    filterApplyBtn.addEventListener('click', applyDateFilter);
  }

  // Render Table Fetch
  await getRewardTrade();

  await fetchCabangOptions();
});

// Export functions to window for HTML access
window.filterByStatus = filterByStatus;
window.toggleDateFilter = toggleDateFilter;
window.setToday = setToday;
window.setThisWeek = setThisWeek;
window.setThisMonth = setThisMonth;
window.resetDateFilter = resetDateFilter;
window.applyDateFilter = applyDateFilter;
window.searchFromState = searchFromState;
window.clearSearch = clearSearch;
window.fetchCabangOptions = fetchCabangOptions;
