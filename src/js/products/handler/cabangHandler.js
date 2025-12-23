import { cabang } from "../services/api.js";

export const cabangHandler = {
  selectCabang: async (cabangId) => {
    try {
      const response = await cabang.getCabangData();

      cabangId.innerHTML = ""; // Clear existing options

      // Add default option
      const defaultOption = document.createElement("option");
      defaultOption.value = "";
      defaultOption.textContent = "Pilih Cabang";
      cabangId.appendChild(defaultOption);

      // Check if response has data property
      const data = response.data || response;

      if (Array.isArray(data) && data.length > 0) {
        data.forEach((item) => {
          const option = document.createElement("option");
          option.value = item.store;
          option.textContent = item.nama_cabang;
          cabangId.appendChild(option);
        });
      } else {
        console.warn("No cabang data found or invalid format");
      }
    } catch (error) {
      console.error("Error loading cabang data:", error);
      // Add error option
      const errorOption = document.createElement("option");
      errorOption.value = "";
      errorOption.textContent = "Error loading data";
      cabangId.appendChild(errorOption);
    }
  },
};
