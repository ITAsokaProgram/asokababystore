import { plus, minus, all } from "./utils/btnHelper.js";
import { renderPointsList } from "./utils/tableHelper.js";
import { fetchUserPoints, fetchHistoryPoints } from "./services/api.js";
import { elements } from "./dom.js";

document.addEventListener("DOMContentLoaded", async function () {
  const pointsData = await fetchUserPoints();
  const historyData = await fetchHistoryPoints();
  elements.totalPoints.textContent =
    pointsData[0].total_poin_pk_pm.toLocaleString();
  renderPointsList(historyData);
  plus(historyData);
  minus(historyData);
  all(historyData);
});
