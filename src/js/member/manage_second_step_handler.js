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

const UI_ELEMENTS_AGE_TABLE = {
  loadingId: "age-table-loading-spinner",
  containerId: "age-table-container",
  errorId: "age-table-error",
  bodyId: "age-table-body",
};

const UI_ELEMENTS_LOCATION_TABLE = {
  loadingId: "location-table-loading-spinner",
  containerId: "location-table-container",
  errorId: "location-table-error",
  bodyId: "location-table-body",
  headerId: "location-table-header",
};

const UI_ELEMENTS_TOP_MEMBER_TABLE = {
  loadingId: "top-member-table-loading-spinner",
  containerId: "top-member-table-container",
  errorId: "top-member-table-error",
  bodyId: "top-member-table-body",
};

const UI_ELEMENTS_TOP_PRODUCT_TABLE = {
  loadingId: "top-product-table-loading-spinner",
  containerId: "top-product-table-container",
  errorId: "top-product-table-error",
  bodyId: "top-product-table-body",
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

function setTableUIState(state, message = "") {
  const loadingEl = document.getElementById(UI_ELEMENTS_AGE_TABLE.loadingId);
  const containerEl = document.getElementById(
    UI_ELEMENTS_AGE_TABLE.containerId
  );
  const errorEl = document.getElementById(UI_ELEMENTS_AGE_TABLE.errorId);
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

function setGeneralTableUIState(uiElements, state, message = "") {
  const loadingEl = document.getElementById(uiElements.loadingId);
  const containerEl = document.getElementById(uiElements.containerId);
  const errorEl = document.getElementById(uiElements.errorId);

  if (loadingEl) loadingEl.classList.toggle("hidden", state !== "loading");
  if (containerEl) containerEl.classList.toggle("hidden", state !== "success");
  if (errorEl) {
    errorEl.classList.toggle("hidden", state !== "error" && state !== "empty");
    if (state === "error" || state === "empty") {
      errorEl.textContent = message;
    }
  }
}

function renderAgeTable(data) {
  const tableBody = document.getElementById(UI_ELEMENTS_AGE_TABLE.bodyId);
  if (!tableBody) return;
  tableBody.innerHTML = "";
  const numberFormatter = new Intl.NumberFormat("id-ID");
  if (!data || data.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4 text-gray-500">Tidak ada data.</td></tr>`;
    return;
  }
  data.forEach((item) => {
    const topProduct = item.top_product_descp || "-";
    const topQty = item.top_product_qty
      ? numberFormatter.format(item.top_product_qty)
      : "-";
    const count = item.count ? numberFormatter.format(item.count) : "0";
    const row = `
                <tr class="hover:bg-gray-50 cursor-pointer age-table-row" data-age-group="${item.age_group}">
                    <td class="px-4 py-3 text-sm text-gray-700">${item.age_group}</td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">${count}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">${topProduct}</td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">${topQty}</td>
                </tr>
            `;
    tableBody.innerHTML += row;
  });
}

function renderLocationTable(data) {
  const tableBody = document.getElementById(UI_ELEMENTS_LOCATION_TABLE.bodyId);
  const tableHeader = document.getElementById(
    UI_ELEMENTS_LOCATION_TABLE.headerId
  );
  if (!tableBody || !tableHeader) return;
  tableBody.innerHTML = "";
  const numberFormatter = new Intl.NumberFormat("id-ID");
  if (currentLocationLevel === "city") tableHeader.textContent = "Kota";
  else if (currentLocationLevel === "district")
    tableHeader.textContent = "Kecamatan";
  else if (currentLocationLevel === "subdistrict")
    tableHeader.textContent = "Kelurahan";
  if (!data || data.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4 text-gray-500">Tidak ada data.</td></tr>`;
    return;
  }
  const isClickable = currentLocationLevel !== "subdistrict";
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

function renderTopMemberTable(data) {
  const tableBody = document.getElementById(
    UI_ELEMENTS_TOP_MEMBER_TABLE.bodyId
  );
  if (!tableBody) return;
  tableBody.innerHTML = "";
  const currencyFormatter = new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  });

  if (!data || data.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="3" class="text-center p-4 text-gray-500">Tidak ada data.</td></tr>`;
    return;
  }

  data.forEach((item) => {
    if (
      !item.nama_cust ||
      item.nama_cust.trim().toLowerCase() === "member dummy"
    )
      return;

    const totalSpent = item.total_spent
      ? currencyFormatter.format(item.total_spent)
      : "Rp 0";
    const row = `
        <tr class="hover:bg-gray-50 cursor-pointer top-member-table-row" 
            data-kd-cust="${item.kd_cust}" 
            data-nama-cust="${item.nama_cust}">
            <td class="px-4 py-3 text-sm text-gray-700 font-medium">${item.nama_cust}</td>
            <td class="px-4 py-3 text-sm text-gray-500">${item.kd_cust}</td>
            <td class="px-4 py-3 text-sm font-bold text-green-600">${totalSpent}</td>
        </tr>
    `;
    tableBody.innerHTML += row;
  });
}

function renderTopProductTable(data) {
  const tableBody = document.getElementById(
    UI_ELEMENTS_TOP_PRODUCT_TABLE.bodyId
  );
  if (!tableBody) return;
  tableBody.innerHTML = "";
  const numberFormatter = new Intl.NumberFormat("id-ID");

  if (!data || data.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="3" class="text-center p-4 text-gray-500">Tidak ada data.</td></tr>`;
    return;
  }

  data.forEach((item) => {
    if (
      !item.nama_cust ||
      item.nama_cust.trim().toLowerCase() === "member dummy"
    )
      return;

    const qty = item.total_item_qty
      ? numberFormatter.format(item.total_item_qty)
      : "0";
    const row = `
        <tr class="hover:bg-gray-50 cursor-pointer top-product-table-row" data-product-name="${item.descp}">
            <td class="px-4 py-3 text-sm text-gray-700 font-medium">${item.descp}</td>
            <td class="px-4 py-3 text-sm text-gray-500">${item.nama_cust}</td>
            <td class="px-4 py-3 text-sm font-bold text-blue-600">${qty}</td>
        </tr>
    `;
    tableBody.innerHTML += row;
  });
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
  setTableUIState("loading");
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
      renderAgeTable(result.data);
      setTableUIState("success");
    } else if (result.success === true && result.data.length === 0) {
      const msg = "Tidak ada data member (umur) untuk filter ini.";
      setChartUIState(UI_ELEMENTS.age, "empty", msg);
      setTableUIState("empty", msg);
    } else {
      throw new Error(result.message || "Gagal memuat data umur");
    }
  } catch (error) {
    console.error("Error loading member age data:", error);
    const errorMsg = `Gagal memuat chart umur: ${error.message}`;
    setChartUIState(UI_ELEMENTS.age, "error", errorMsg);
    setTableUIState("error", errorMsg);
  }
}

async function loadLocationData() {
  setChartUIState(UI_ELEMENTS.location, "loading");
  setLocationTableUIState("loading");
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
      renderLocationTable(result.data);
      setLocationTableUIState("success");
    } else if (result.success === true && result.data.length === 0) {
      let levelText = "kota";
      if (currentLocationLevel === "district") levelText = "kecamatan";
      if (currentLocationLevel === "subdistrict") levelText = "kelurahan";
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

async function loadTopMemberData() {
  setChartUIState(UI_ELEMENTS.topMember, "loading");
  setGeneralTableUIState(UI_ELEMENTS_TOP_MEMBER_TABLE, "loading");
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

      // Render Table
      renderTopMemberTable(result.data);
      setGeneralTableUIState(UI_ELEMENTS_TOP_MEMBER_TABLE, "success");

      if (topMemberChartInstance) topMemberChartInstance.resize();
    } else if (result.success === true && result.data.length === 0) {
      const msg = "Tidak ada data top member untuk filter ini.";
      setChartUIState(UI_ELEMENTS.topMember, "empty", msg);
      setGeneralTableUIState(UI_ELEMENTS_TOP_MEMBER_TABLE, "empty", msg);
    } else {
      throw new Error(result.message || "Gagal memuat data top member");
    }
  } catch (error) {
    console.error("Error loading top member data:", error);
    const msg = `Gagal memuat data: ${error.message}`;
    setChartUIState(UI_ELEMENTS.topMember, "error", msg);
    setGeneralTableUIState(UI_ELEMENTS_TOP_MEMBER_TABLE, "error", msg);
  }
}

async function loadTopProductData() {
  setChartUIState(UI_ELEMENTS.topProduct, "loading");
  setGeneralTableUIState(UI_ELEMENTS_TOP_PRODUCT_TABLE, "loading");
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

      // Render Table
      renderTopProductTable(result.data);
      setGeneralTableUIState(UI_ELEMENTS_TOP_PRODUCT_TABLE, "success");

      if (topMemberProductChartInstance) topMemberProductChartInstance.resize();
    } else if (result.success === true && result.data.length === 0) {
      const msg = "Tidak ada data pembelian produk untuk filter ini.";
      setChartUIState(UI_ELEMENTS.topProduct, "empty", msg);
      setGeneralTableUIState(UI_ELEMENTS_TOP_PRODUCT_TABLE, "empty", msg);
    } else {
      throw new Error(result.message || "Gagal memuat data produk");
    }
  } catch (error) {
    console.error("Error loading top product data:", error);
    const msg = `Gagal memuat data: ${error.message}`;
    setChartUIState(UI_ELEMENTS.topProduct, "error", msg);
    setGeneralTableUIState(UI_ELEMENTS_TOP_PRODUCT_TABLE, "error", msg);
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  currentFilter = params.get("filter");
  currentStatus = params.get("status");
  const ageTableBody = document.getElementById(UI_ELEMENTS_AGE_TABLE.bodyId);
  if (ageTableBody) {
    ageTableBody.addEventListener("click", (event) => {
      const row = event.target.closest("tr.age-table-row");
      if (row && currentFilter && currentStatus) {
        const ageGroup = row.dataset.ageGroup;
        if (ageGroup) {
          const url = `umur?age_group=${encodeURIComponent(
            ageGroup
          )}&filter=${encodeURIComponent(
            currentFilter
          )}&status=${encodeURIComponent(currentStatus)}`;
          window.location.href = url;
        }
      }
    });
  }
  const locationTableBody = document.getElementById(
    UI_ELEMENTS_LOCATION_TABLE.bodyId
  );
  if (locationTableBody) {
    locationTableBody.addEventListener("click", (event) => {
      const row = event.target.closest("tr.location-table-row");
      if (!row) return;
      const locationName = row.dataset.locationName;
      if (locationName) {
        if (currentLocationLevel === "city") {
          currentLocationLevel = "district";
          selectedCity = locationName;
          selectedDistrict = null;
          loadLocationData();
        } else if (currentLocationLevel === "district") {
          currentLocationLevel = "subdistrict";
          selectedDistrict = locationName;
          loadLocationData();
        }
      }
    });
  }

  // Event Listener for Top Member Table
  const topMemberTableBody = document.getElementById(
    UI_ELEMENTS_TOP_MEMBER_TABLE.bodyId
  );
  if (topMemberTableBody) {
    topMemberTableBody.addEventListener("click", (event) => {
      const row = event.target.closest("tr.top-member-table-row");
      if (row && currentFilter && currentStatus) {
        const kdCust = row.dataset.kdCust;
        const namaCust = row.dataset.namaCust;
        if (kdCust) {
          const targetUrl = `customer.php?filter=${encodeURIComponent(
            currentFilter
          )}&status=${encodeURIComponent(
            currentStatus
          )}&kd_cust=${encodeURIComponent(
            kdCust
          )}&nama_cust=${encodeURIComponent(namaCust)}`;
          window.location.href = targetUrl;
        }
      }
    });
  }

  // Event Listener for Top Product Table
  const topProductTableBody = document.getElementById(
    UI_ELEMENTS_TOP_PRODUCT_TABLE.bodyId
  );
  if (topProductTableBody) {
    topProductTableBody.addEventListener("click", (event) => {
      const row = event.target.closest("tr.top-product-table-row");
      if (row) {
        // Redirect to top_sales as requested
        window.location.href = "top_sales";
      }
    });
  }

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
    setTableUIState("error", errorMsg);
    setChartUIState(UI_ELEMENTS.location, "error", errorMsg);
    setLocationTableUIState("error", errorMsg);
    setChartUIState(UI_ELEMENTS.topMember, "error", errorMsg);
    setChartUIState(UI_ELEMENTS.topProduct, "error", errorMsg);
  }
});
