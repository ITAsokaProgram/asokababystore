import * as api from "./member_api_service.js";
import { renderMemberChart } from "./member_chart_service.js";

function updatePlaceholder(id, value, isError = false) {
  const el = document.getElementById(id);
  if (el) {
    el.textContent = value;
    el.classList.remove("text-red-500");
    if (isError) {
      el.classList.add("text-red-500");
    }
    const spinner = el.querySelector(".fa-spinner");
    if (spinner) {
      spinner.remove();
    }
  }
}

async function loadActivityData() {
  const formatter = new Intl.NumberFormat("id-ID");
  try {
    const params = new URLSearchParams(window.location.search);
    const filter = params.get("filter");
    if (!filter) {
      console.log("Tidak ada filter, chart tidak dimuat.");
      return;
    }
    const result = await api.getMemberActivity(filter);
    if (result.success === true && result.data) {
      const data = result.data;
      updatePlaceholder(
        "total-member-placeholder",
        formatter.format(data.total)
      );
      updatePlaceholder(
        "active-member-placeholder",
        formatter.format(data.active)
      );
      updatePlaceholder(
        "inactive-member-placeholder",
        formatter.format(data.inactive)
      );
      renderMemberChart("memberActivityChart", data, filter);
    } else {
      throw new Error(result.message || "Gagal memuat data aktivitas");
    }
  } catch (error) {
    console.error("Error loading member activity:", error);
    updatePlaceholder("total-member-placeholder", "Gagal", true);
    updatePlaceholder("active-member-placeholder", "Gagal", true);
    updatePlaceholder("inactive-member-placeholder", "Gagal", true);
    const chartElement = document.getElementById("memberActivityChart");
    if (chartElement) {
      chartElement.innerHTML = `
                <div style="text-align: center; padding-top: 50px; color: red; font-family: Arial, sans-serif;">
                    <strong>Gagal memuat chart.</strong><br>
                    <span>${error.message || "Cek console untuk detail."}</span>
                </div>
            `;
    }
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const chartSection = document.getElementById("chart-section");
  if (chartSection) {
    loadActivityData();
  }
});
