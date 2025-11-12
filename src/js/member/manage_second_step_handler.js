import * as api from "./member_api_service.js";
import * as charts from "./member_chart_service.js";

let currentFilter = "";
let currentStatus = "";
let ageChartInstance = null;
let locationChartInstance = null;
let topMemberChartInstance = null;
let topMemberProductChartInstance = null;
let currentLocationLevel = "city";
let selectedCity = null;
let selectedDistrict = null;

const UI_ELEMENTS = {
  age: {
    loadingId: "loading-spinner",
    containerId: "age-chart-container",
    errorId: "age-chart-error",
  },
  location: {
    loadingId: "location-loading-spinner",
    containerId: "location-chart-container",
    errorId: "location-chart-error",
  },
  topMember: {
    loadingId: "top-member-chart-loading-spinner",
    containerId: "top-member-chart-container",
    errorId: "top-member-chart-error",
  },
  topProduct: {
    loadingId: "top-product-chart-loading-spinner",
    containerId: "top-product-chart-container",
    errorId: "top-product-chart-error",
  },
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
function updateLocationHeader() {
  const header = document.getElementById("location-chart-header");
  const backBtn = document.getElementById("location-back-btn");
  const breadcrumb = document.getElementById("location-breadcrumb");
  if (currentLocationLevel === "city") {
    header.classList.add("hidden");
  } else {
    header.classList.remove("hidden");
    if (currentLocationLevel === "district") {
      breadcrumb.textContent = `Kota: ${selectedCity}`;
    } else if (currentLocationLevel === "subdistrict") {
      breadcrumb.textContent = `Kota: ${selectedCity} > Kec: ${selectedDistrict}`;
    }
  }
}

async function loadAgeData() {
  setChartUIState(UI_ELEMENTS.age, "loading");
  try {
    const result = await api.getMemberByAge(currentFilter, currentStatus);
    if (result.success === true && result.data && result.data.length > 0) {
      ageChartInstance = charts.renderAgeChart(
        ageChartInstance,
        "memberAgeChart",
        result.data,
        currentFilter
      );
      setChartUIState(UI_ELEMENTS.age, "success");
      if (ageChartInstance) ageChartInstance.resize();
    } else if (result.success === true && result.data.length === 0) {
      setChartUIState(
        UI_ELEMENTS.age,
        "empty",
        "Tidak ada data member (umur) untuk filter ini."
      );
    } else {
      throw new Error(result.message || "Gagal memuat data umur");
    }
  } catch (error) {
    console.error("Error loading member age data:", error);
    setChartUIState(
      UI_ELEMENTS.age,
      "error",
      `Gagal memuat chart umur: ${error.message}`
    );
  }
}

async function loadLocationData() {
  setChartUIState(UI_ELEMENTS.location, "loading");
  updateLocationHeader();
  try {
    const result = await api.getMemberByLocation(
      currentFilter,
      currentStatus,
      currentLocationLevel,
      selectedCity,
      selectedDistrict
    );
    if (result.success === true && result.data && result.data.length > 0) {
      const state = {
        currentLocationLevel,
        selectedCity,
        selectedDistrict,
        currentFilter,
        currentStatus,
      };
      const callbacks = {
        updateLocationState: (level, city, district) => {
          currentLocationLevel = level;
          selectedCity = city;
          selectedDistrict = district;
          loadLocationData();
        },
      };
      locationChartInstance = charts.renderLocationChart(
        locationChartInstance,
        "memberLocationChart",
        result.data,
        state,
        callbacks
      );
      setChartUIState(UI_ELEMENTS.location, "success");
      if (locationChartInstance) locationChartInstance.resize();
    } else if (result.success === true && result.data.length === 0) {
      let levelText = "kota";
      if (currentLocationLevel === "district") levelText = "kecamatan";
      if (currentLocationLevel === "subdistrict") levelText = "kelurahan";
      setChartUIState(
        UI_ELEMENTS.location,
        "empty",
        `Tidak ada data member (${levelText}) untuk filter ini.`
      );
    } else {
      throw new Error(result.message || "Gagal memuat data lokasi");
    }
  } catch (error) {
    console.error("Error loading member location data:", error);
    setChartUIState(
      UI_ELEMENTS.location,
      "error",
      `Gagal memuat chart lokasi: ${error.message}`
    );
  }
}

async function loadTopMemberData() {
  setChartUIState(UI_ELEMENTS.topMember, "loading");
  try {
    const result = await api.getTopMembersByFilter(
      currentFilter,
      currentStatus
    );
    if (result.success === true && result.data && result.data.length > 0) {
      const state = { currentFilter, currentStatus };
      topMemberChartInstance = charts.renderTopMemberChart(
        topMemberChartInstance,
        "topMemberChart",
        result.data,
        state
      );
      setChartUIState(UI_ELEMENTS.topMember, "success");
      if (topMemberChartInstance) topMemberChartInstance.resize();
    } else if (result.success === true && result.data.length === 0) {
      setChartUIState(
        UI_ELEMENTS.topMember,
        "empty",
        "Tidak ada data top member untuk filter ini."
      );
    } else {
      throw new Error(result.message || "Gagal memuat data top member");
    }
  } catch (error) {
    console.error("Error loading top member data:", error);
    setChartUIState(
      UI_ELEMENTS.topMember,
      "error",
      `Gagal memuat data: ${error.message}`
    );
  }
}

async function loadTopProductData() {
  setChartUIState(UI_ELEMENTS.topProduct, "loading");
  try {
    const result = await api.getTopMemberProductPairs(
      currentFilter,
      currentStatus
    );
    if (result.success === true && result.data && result.data.length > 0) {
      const state = { currentFilter, currentStatus };
      topMemberProductChartInstance = charts.renderTopProductChart(
        topMemberProductChartInstance,
        "topMemberProductChart",
        result.data,
        state
      );
      setChartUIState(UI_ELEMENTS.topProduct, "success");
      if (topMemberProductChartInstance) topMemberProductChartInstance.resize();
    } else if (result.success === true && result.data.length === 0) {
      setChartUIState(
        UI_ELEMENTS.topProduct,
        "empty",
        "Tidak ada data pembelian produk untuk filter ini."
      );
    } else {
      throw new Error(result.message || "Gagal memuat data produk");
    }
  } catch (error) {
    console.error("Error loading top product data:", error);
    setChartUIState(
      UI_ELEMENTS.topProduct,
      "error",
      `Gagal memuat data: ${error.message}`
    );
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  currentFilter = params.get("filter");
  currentStatus = params.get("status");
  if (currentFilter && currentStatus) {
    loadAgeData();
    loadLocationData();
    loadTopMemberData();
    loadTopProductData();
    const backBtn = document.getElementById("location-back-btn");
    backBtn.addEventListener("click", () => {
      if (currentLocationLevel === "district") {
        currentLocationLevel = "city";
        selectedCity = null;
        loadLocationData();
      } else if (currentLocationLevel === "subdistrict") {
        currentLocationLevel = "district";
        selectedDistrict = null;
        loadLocationData();
      }
    });
  } else {
    console.error("Filter atau Status tidak ditemukan di URL.");
    const errorMsg = "Parameter filter atau status tidak ditemukan.";
    setChartUIState(UI_ELEMENTS.age, "error", errorMsg);
    setChartUIState(UI_ELEMENTS.location, "error", errorMsg);
    setChartUIState(UI_ELEMENTS.topMember, "error", errorMsg);
    setChartUIState(UI_ELEMENTS.topProduct, "error", errorMsg);
  }
});
