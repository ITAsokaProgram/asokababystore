import { renderPointsList } from "./tableHelper.js";
// Filter points
let currentFilter = "all";

export const filterPoints = (type, data) => {
  currentFilter = type;

  // Update button states
  document.querySelectorAll(".filter-btn").forEach((btn) => {
    btn.classList.remove("active");
  });
  event.target.classList.add("active");

  // Filter data
  let filteredData = data;
 if (type === "plus") {
    filteredData = data.filter((item) => item.keterangan_struk === "Poin Masuk");
  } else if (type === "minus") {
    filteredData = data.filter((item) => item.keterangan_struk === "Poin Keluar");
  } else {
    filteredData = data.filter((item) => item.keterangan_struk); 
  }

  renderPointsList(filteredData);
}
