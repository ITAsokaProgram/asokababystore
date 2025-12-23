import * as api from "./member_api_service.js";
import * as charts from "./member_chart_service.js";

// Objek state untuk menyimpan semua parameter
const state = {
  filterParams: {
    filter_type: null,
    filter: null,
    start_date: null,
    end_date: null,
  },
  currentStatus: null,
  currentLocationLevel: "city",
  selectedCity: null,
  selectedDistrict: null,
};

let locationChartInstance = null;

const UI_ELEMENTS = {
  location: {
    loadingId: "location-loading-spinner",
    containerId: "location-chart-container",
    errorId: "location-chart-error",
  },
};

const UI_ELEMENTS_LOCATION_TABLE = {
  loadingId: "location-table-loading-spinner",
  containerId: "location-table-container",
  errorId: "location-table-error",
  bodyId: "location-table-body",
  headerId: "location-table-header",
};

function setChartUIState(
  { loadingId, containerId, errorId },
  state,
  message = ""
) {
  const loadingEl = document.getElementById(loadingId);
  const containerEl = document.getElementById(containerId);
  const errorEl = document.getElementById(errorId);
  if (loadingEl) loadingEl.classList.toggle("hidden", state !== "loading");
  if (containerEl) containerEl.classList.toggle("hidden", state !== "success");
  if (errorEl) {
    errorEl.classList.toggle("hidden", state !== "error" && state !== "empty");
    if (state === "error" || state === "empty") {
      errorEl.textContent = message;
    }
  }
}

function setLocationTableUIState(state, message = "") {
  const loadingEl = document.getElementById(
    UI_ELEMENTS_LOCATION_TABLE.loadingId
  );
  const containerEl = document.getElementById(
    UI_ELEMENTS_LOCATION_TABLE.containerId
  );
  const errorEl = document.getElementById(UI_ELEMENTS_LOCATION_TABLE.errorId);
  if (loadingEl) loadingEl.classList.toggle("hidden", state !== "loading");
  if (containerEl) containerEl.classList.toggle("hidden", state !== "success");
  if (errorEl) {
    errorEl.classList.toggle("hidden", state !== "error" && state !== "empty");
    if (state === "error" || state === "empty") {
      errorEl.textContent = message;
    }
  }
}

function renderLocationTable(data) {
  const tableBody = document.getElementById(UI_ELEMENTS_LOCATION_TABLE.bodyId);
  const tableHeader = document.getElementById(
    UI_ELEMENTS_LOCATION_TABLE.headerId
  );
  if (!tableBody || !tableHeader) return;
  tableBody.innerHTML = "";
  const numberFormatter = new Intl.NumberFormat("id-ID");

  if (state.currentLocationLevel === "city") tableHeader.textContent = "Kota";
  else if (state.currentLocationLevel === "district")
    tableHeader.textContent = "Kecamatan";
  else if (state.currentLocationLevel === "subdistrict")
    tableHeader.textContent = "Kelurahan";

  if (!data || data.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4 text-gray-500">Tidak ada data.</td></tr>`;
    return;
  }

  const isClickable = state.currentLocationLevel !== "subdistrict";
  data.forEach((item) => {
    const topProduct = item.top_product_descp || "-";
    const topQty = item.top_product_qty
      ? numberFormatter.format(item.top_product_qty)
      : "-";
    const count = item.count ? numberFormatter.format(item.count) : "0";
    const locationName = item.location_name;
    const rowClasses = isClickable
      ? "hover:bg-gray-50 cursor-pointer location-table-row"
      : "hover:bg-gray-50";
    const dataAttribute = isClickable
      ? `data-location-name="${locationName}"`
      : "";
    const row = `
            <tr class="${rowClasses}" ${dataAttribute}>
                <td class="px-4 py-3 text-sm text-gray-700">${locationName}</td>
                <td class="px-4 py-3 text-sm font-medium text-gray-900">${count}</td>
                <td class="px-4 py-3 text-sm text-gray-700">${topProduct}</td>
                <td class="px-4 py-3 text-sm font-medium text-gray-900">${topQty}</td>
            </tr>
        `;
    tableBody.innerHTML += row;
  });
}

function updateLocationHeader() {
  const header = document.getElementById("location-chart-header");
  const breadcrumb = document.getElementById("location-breadcrumb");
  if (state.currentLocationLevel === "city") {
    header.classList.add("hidden");
  } else {
    header.classList.remove("hidden");
    if (state.currentLocationLevel === "district") {
      breadcrumb.textContent = `Kota: ${state.selectedCity}`;
    } else if (state.currentLocationLevel === "subdistrict") {
      breadcrumb.textContent = `Kota: ${state.selectedCity} > Kec: ${state.selectedDistrict}`;
    }
  }
}

/**
 * Helper untuk membuat query string filter dari objek state.
 * @returns {URLSearchParams}
 */
function buildFilterQueryString() {
  const params = new URLSearchParams();
  const { filterParams } = state;
  if (filterParams && filterParams.filter_type) {
    params.append("filter_type", filterParams.filter_type);
    if (filterParams.filter_type === "custom") {
      params.append("start_date", filterParams.start_date);
      params.append("end_date", filterParams.end_date);
    } else {
      params.append("filter", filterParams.filter);
    }
  }
  return params;
}

async function handleLocationClick(locationName) {
  if (!locationName) return;

  const buildUrl = (params) => {
    const urlParams = buildFilterQueryString(); // Gunakan helper
    urlParams.append("status", state.currentStatus);
    for (const key in params) {
      urlParams.append(key, params[key]);
    }
    return `lokasi.php?${urlParams.toString()}`;
  };

  if (state.currentLocationLevel === "city") {
    const clickedCity = locationName;
    Swal.fire({
      title: "Mengecek data kecamatan...",
      text: "Mohon tunggu...",
      icon: "info",
      showConfirmButton: false,
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });
    const peekResult = await api.getMemberByLocation(
      state.filterParams, // Kirim filterParams
      state.currentStatus,
      "district",
      clickedCity,
      null,
      "all"
    );
    const hasNextLevelData = peekResult.success && peekResult.data.length > 0;
    Swal.close();
    if (hasNextLevelData) {
      Swal.fire({
        title: `Anda memilih: ${clickedCity}`,
        text: "Apa yang ingin Anda tampilkan selanjutnya?",
        icon: "question",
        showConfirmButton: true,
        confirmButtonText: "Lihat Laporan Produk Teratas",
        showDenyButton: true,
        denyButtonText: "Tampilkan Data per Kecamatan",
        showCancelButton: true,
        cancelButtonText: "Batal",
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = buildUrl({ city: clickedCity });
        } else if (result.isDenied) {
          state.currentLocationLevel = "district";
          state.selectedCity = clickedCity;
          state.selectedDistrict = null;
          loadLocationData();
        }
      });
    } else {
      window.location.href = buildUrl({ city: clickedCity });
    }
  } else if (state.currentLocationLevel === "district") {
    const clickedDistrict = locationName;
    Swal.fire({
      title: "Mengecek data kelurahan...",
      text: "Mohon tunggu...",
      icon: "info",
      showConfirmButton: false,
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });
    const peekResult = await api.getMemberByLocation(
      state.filterParams, // Kirim filterParams
      state.currentStatus,
      "subdistrict",
      state.selectedCity,
      clickedDistrict,
      "all"
    );
    const hasNextLevelData = peekResult.success && peekResult.data.length > 0;
    Swal.close();
    if (hasNextLevelData) {
      Swal.fire({
        title: `Anda memilih: ${clickedDistrict}`,
        text: "Apa yang ingin Anda tampilkan selanjutnya?",
        icon: "question",
        showConfirmButton: true,
        confirmButtonText: "Lihat Laporan Produk Teratas",
        showDenyButton: true,
        denyButtonText: "Tampilkan Data per Kelurahan",
        showCancelButton: true,
        cancelButtonText: "Batal",
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = buildUrl({
            city: state.selectedCity,
            district: clickedDistrict,
          });
        } else if (result.isDenied) {
          state.currentLocationLevel = "subdistrict";
          state.selectedDistrict = clickedDistrict;
          loadLocationData();
        }
      });
    } else {
      window.location.href = buildUrl({
        city: state.selectedCity,
        district: clickedDistrict,
      });
    }
  }
}

async function loadLocationData() {
  setChartUIState(UI_ELEMENTS.location, "loading");
  setLocationTableUIState("loading");
  updateLocationHeader();
  try {
    const result = await api.getMemberByLocation(
      state.filterParams, // Kirim filterParams
      state.currentStatus,
      state.currentLocationLevel,
      state.selectedCity,
      state.selectedDistrict,
      "all"
    );
    if (result.success === true && result.data && result.data.length > 0) {
      const callbacks = {
        updateLocationState: async (level, city, district) => {
          let locationName;
          if (level === "district") {
            locationName = city;
          } else if (level === "subdistrict") {
            locationName = district;
          }
          if (locationName) {
            await handleLocationClick(locationName);
          } else {
            console.warn("updateLocationState called with invalid parameters");
          }
        },
      };
      locationChartInstance = charts.renderLocationChart(
        locationChartInstance,
        "memberLocationChart",
        result.data,
        state, // Kirim seluruh state
        callbacks
      );
      setChartUIState(UI_ELEMENTS.location, "success");
      if (locationChartInstance) locationChartInstance.resize();
      renderLocationTable(result.data);
      setLocationTableUIState("success");
    } else if (result.success === true && result.data.length === 0) {
      let levelText = "kota";
      if (state.currentLocationLevel === "district") levelText = "kecamatan";
      if (state.currentLocationLevel === "subdistrict") levelText = "kelurahan";
      const msg = `Tidak ada data member (${levelText}) untuk filter ini.`;
      setChartUIState(UI_ELEMENTS.location, "empty", msg);
      setLocationTableUIState("empty", msg);
    } else {
      throw new Error(result.message || "Gagal memuat data lokasi");
    }
  } catch (error) {
    console.error("Error loading member location data:", error);
    const errorMsg = `Gagal memuat chart lokasi: ${error.message}`;
    setChartUIState(UI_ELEMENTS.location, "error", errorMsg);
    setLocationTableUIState("error", errorMsg);
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);

  // Isi objek state.filterParams
  state.filterParams.filter_type = params.get("filter_type");
  state.filterParams.filter = params.get("filter");
  state.filterParams.start_date = params.get("start_date");
  state.filterParams.end_date = params.get("end_date");
  state.currentStatus = params.get("status");

  const locationTableBody = document.getElementById(
    UI_ELEMENTS_LOCATION_TABLE.bodyId
  );
  if (locationTableBody) {
    locationTableBody.addEventListener("click", async (event) => {
      const row = event.target.closest("tr.location-table-row");
      if (!row) return;
      const locationName = row.dataset.locationName;
      if (!locationName) return;
      await handleLocationClick(locationName);
    });
  }

  if (state.filterParams.filter_type && state.currentStatus) {
    loadLocationData();
    const backBtn = document.getElementById("location-back-btn");
    backBtn.addEventListener("click", () => {
      if (state.currentLocationLevel === "district") {
        state.currentLocationLevel = "city";
        state.selectedCity = null;
        loadLocationData();
      } else if (state.currentLocationLevel === "subdistrict") {
        state.currentLocationLevel = "district";
        state.selectedDistrict = null;
        loadLocationData();
      }
    });
  } else {
    console.error("Filter atau Status tidak ditemukan di URL.");
    const errorMsg = "Parameter filter atau status tidak ditemukan.";
    setChartUIState(UI_ELEMENTS.location, "error", errorMsg);
    setLocationTableUIState("error", errorMsg);
  }
});
