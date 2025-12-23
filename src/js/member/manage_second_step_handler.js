import * as api from "./member_api_service.js";
import * as charts from "./member_chart_service.js";
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
let ageChartInstance = null;
let locationChartInstance = null;
let topMemberChartInstance = null;
let topMemberProductChartInstance = null;
let topMemberFrequencyChartInstance = null;
const UI_WIDGETS = {
  age: {
    chart: {
      loadingId: "loading-spinner",
      containerId: "age-chart-container",
      errorId: "age-chart-error",
    },
    table: {
      loadingId: "age-table-loading-spinner",
      containerId: "age-table-container",
      errorId: "age-table-error",
      bodyId: "age-table-body",
    },
    chartElementId: "memberAgeChart",
    viewAllBtnId: null,
  },
  location: {
    chart: {
      loadingId: "location-loading-spinner",
      containerId: "location-chart-container",
      errorId: "location-chart-error",
    },
    table: {
      loadingId: "location-table-loading-spinner",
      containerId: "location-table-container",
      errorId: "location-table-error",
      bodyId: "location-table-body",
      headerId: "location-table-header",
    },
    chartElementId: "memberLocationChart",
    viewAllBtnId: "view-all-locations-btn",
  },
  topMember: {
    chart: {
      loadingId: "top-member-chart-loading-spinner",
      containerId: "top-member-chart-container",
      errorId: "top-member-chart-error",
    },
    table: {
      loadingId: "top-member-table-loading-spinner",
      containerId: "top-member-table-container",
      errorId: "top-member-table-error",
      bodyId: "top-member-table-body",
    },
    chartElementId: "topMemberChart",
    viewAllBtnId: "view-all-top-member-btn",
  },
  topProduct: {
    chart: {
      loadingId: "top-product-chart-loading-spinner",
      containerId: "top-product-chart-container",
      errorId: "top-product-chart-error",
    },
    table: {
      loadingId: "top-product-table-loading-spinner",
      containerId: "top-product-table-container",
      errorId: "top-product-table-error",
      bodyId: "top-product-table-body",
    },
    chartElementId: "topMemberProductChart",
    viewAllBtnId: "view-all-top-product-btn",
  },
  topMemberFrequency: {
    chart: {
      loadingId: "top-member-frequency-chart-loading-spinner",
      containerId: "top-member-frequency-chart-container",
      errorId: "top-member-frequency-chart-error",
    },
    table: {
      loadingId: "top-member-frequency-table-loading-spinner",
      containerId: "top-member-frequency-table-container",
      errorId: "top-member-frequency-table-error",
      bodyId: "top-member-frequency-table-body",
    },
    chartElementId: "topMemberFrequencyChart",
    viewAllBtnId: "view-all-top-frequency-btn",
  },
};
/**
 * Mengatur status UI (loading, success, error, empty) untuk elemen-elemen
 * @param {object} uiElements - Objek berisi { loadingId, containerId, errorId }
 * @param {'loading' | 'success' | 'error' | 'empty'} state - Status yang diinginkan
 * @param {string} [message=""] - Pesan untuk status error atau empty
 */
function setUIState({ loadingId, containerId, errorId }, state, message = "") {
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
function renderAgeTable(data) {
  const tableBody = document.getElementById(UI_WIDGETS.age.table.bodyId);
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
  const tableBody = document.getElementById(UI_WIDGETS.location.table.bodyId);
  const tableHeader = document.getElementById(
    UI_WIDGETS.location.table.headerId
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
  const isClickable = true;
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
  const tableBody = document.getElementById(UI_WIDGETS.topMember.table.bodyId);
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
  const tableBody = document.getElementById(UI_WIDGETS.topProduct.table.bodyId);
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
        <tr class="hover:bg-gray-50 cursor-pointer top-product-table-row" 
            data-product-name="${item.descp}"
            data-kd-cust="${item.kd_cust}" 
            data-nama-cust="${item.nama_cust}">
            <td class="px-4 py-3 text-sm text-gray-700 font-medium">${item.descp}</td>
            <td class="px-4 py-3 text-sm text-gray-500">${item.nama_cust}</td>
            <td class="px-4 py-3 text-sm font-bold text-blue-600">${qty}</td>
        </tr>
    `;
    tableBody.innerHTML += row;
  });
}
function renderTopMemberFrequencyTable(data) {
  const tableBody = document.getElementById(
    UI_WIDGETS.topMemberFrequency.table.bodyId
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
    const totalTransactions = item.total_transactions
      ? numberFormatter.format(item.total_transactions)
      : "0";
    const row = `
            <tr class="hover:bg-gray-50 cursor-pointer top-member-frequency-table-row" 
                data-kd-cust="${item.kd_cust}" 
                data-nama-cust="${item.nama_cust}">
                <td class="px-4 py-3 text-sm text-gray-700 font-medium">${item.nama_cust}</td>
                <td class="px-4 py-3 text-sm text-gray-500">${item.kd_cust}</td>
                <td class="px-4 py-3 text-sm font-bold text-blue-600">${totalTransactions}</td>
            </tr>
        `;
    tableBody.innerHTML += row;
  });
}
function updateLocationHeader() {
  const header = document.getElementById("location-chart-header");
  const backBtn = document.getElementById("location-back-btn");
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
    const urlParams = buildFilterQueryString();
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
      state.filterParams,
      state.currentStatus,
      "district",
      clickedCity,
      null,
      "default"
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
      state.filterParams,
      state.currentStatus,
      "subdistrict",
      state.selectedCity,
      clickedDistrict,
      "default"
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
  } else if (state.currentLocationLevel === "subdistrict") {
    const clickedSubDistrict = locationName;
    Swal.fire({
      title: `Anda memilih: ${clickedSubDistrict}`,
      text: "Ini adalah level terendah. Buka laporan produk teratas untuk lokasi ini?",
      icon: "question",
      showConfirmButton: true,
      confirmButtonText: "Ya, Buka Laporan",
      showCancelButton: true,
      cancelButtonText: "Batal",
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = buildUrl({
          city: state.selectedCity,
          district: state.selectedDistrict,
          subdistrict: clickedSubDistrict,
        });
      }
    });
  }
}

async function loadWidgetData(
  widgetConfig,
  apiFunction,
  apiArgs,
  chartRenderFunction,
  chartInstance,
  tableRenderFunction,
  emptyMessage
) {
  setUIState(widgetConfig.chart, "loading");
  setUIState(widgetConfig.table, "loading");
  if (widgetConfig.viewAllBtnId) {
    const btn = document.getElementById(widgetConfig.viewAllBtnId);
    if (btn) btn.classList.add("hidden");
  }
  try {
    const result = await apiFunction.apply(null, apiArgs);
    if (result.success === true && result.data && result.data.length > 0) {
      const newChartInstance = chartRenderFunction(
        chartInstance,
        widgetConfig.chartElementId,
        result.data,
        state
      );
      setUIState(widgetConfig.chart, "success");
      if (newChartInstance) newChartInstance.resize();
      tableRenderFunction(result.data);
      setUIState(widgetConfig.table, "success");
      if (widgetConfig.viewAllBtnId) {
        const btn = document.getElementById(widgetConfig.viewAllBtnId);
        if (btn && widgetConfig.viewAllBtnId !== "view-all-locations-btn") {
          btn.classList.remove("hidden");
        }
      }
      return newChartInstance;
    } else if (result.success === true && result.data.length === 0) {
      setUIState(widgetConfig.chart, "empty", emptyMessage);
      setUIState(widgetConfig.table, "empty", emptyMessage);
    } else {
      throw new Error(result.message || "Gagal memuat data");
    }
  } catch (error) {
    console.error(
      `Error loading data for ${widgetConfig.chartElementId}:`,
      error
    );
    const msg = `Gagal memuat data: ${error.message}`;
    setUIState(widgetConfig.chart, "error", msg);
    setUIState(widgetConfig.table, "error", msg);
  }
  return chartInstance;
}
async function loadLocationData() {
  setUIState(UI_WIDGETS.location.chart, "loading");
  setUIState(UI_WIDGETS.location.table, "loading");
  updateLocationHeader();
  try {
    const result = await api.getMemberByLocation(
      state.filterParams,
      state.currentStatus,
      state.currentLocationLevel,
      state.selectedCity,
      state.selectedDistrict
    );
    if (result.success === true && result.data && result.data.length > 0) {
      const callbacks = {
        updateLocationState: async (level, city, district) => {
          let locationName;
          if (level === "district") {
            locationName = city;
          } else if (level === "subdistrict") {
            locationName = district;
          } else if (level === "subdistrict-final") {
            locationName = district;
          }
          if (locationName) {
            await handleLocationClick(locationName);
          } else {
            console.warn(
              "updateLocationState called with invalid params",
              level,
              city,
              district
            );
          }
        },
      };
      locationChartInstance = charts.renderLocationChart(
        locationChartInstance,
        UI_WIDGETS.location.chartElementId,
        result.data,
        state,
        callbacks
      );
      setUIState(UI_WIDGETS.location.chart, "success");
      if (locationChartInstance) locationChartInstance.resize();
      renderLocationTable(result.data);
      setUIState(UI_WIDGETS.location.table, "success");
    } else if (result.success === true && result.data.length === 0) {
      let levelText = "kota";
      if (state.currentLocationLevel === "district") levelText = "kecamatan";
      if (state.currentLocationLevel === "subdistrict") levelText = "kelurahan";
      const msg = `Tidak ada data member (${levelText}) untuk filter ini.`;
      setUIState(UI_WIDGETS.location.chart, "empty", msg);
      setUIState(UI_WIDGETS.location.table, "empty", msg);
    } else {
      throw new Error(result.message || "Gagal memuat data lokasi");
    }
  } catch (error) {
    console.error("Error loading member location data:", error);
    const errorMsg = `Gagal memuat chart lokasi: ${error.message}`;
    setUIState(UI_WIDGETS.location.chart, "error", errorMsg);
    setUIState(UI_WIDGETS.location.table, "error", errorMsg);
  }
}
document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  state.filterParams.filter_type = params.get("filter_type");
  state.filterParams.filter = params.get("filter");
  state.filterParams.start_date = params.get("start_date");
  state.filterParams.end_date = params.get("end_date");
  state.currentStatus = params.get("status");
  const ageTableBody = document.getElementById(UI_WIDGETS.age.table.bodyId);
  if (ageTableBody) {
    ageTableBody.addEventListener("click", (event) => {
      const row = event.target.closest("tr.age-table-row");
      if (row && state.filterParams && state.currentStatus) {
        const ageGroup = row.dataset.ageGroup;
        if (ageGroup) {
          const urlParams = buildFilterQueryString();
          urlParams.append("age_group", ageGroup);
          urlParams.append("status", state.currentStatus);
          const url = `umur.php?${urlParams.toString()}`;
          window.location.href = url;
        }
      }
    });
  }
  const locationTableBody = document.getElementById(
    UI_WIDGETS.location.table.bodyId
  );
  if (locationTableBody) {
    locationTableBody.addEventListener("click", async (event) => {
      const row = event.target.closest("tr.location-table-row");
      if (!row) return;
      const locationName = row.dataset.locationName;
      await handleLocationClick(locationName);
    });
  }
  const topMemberTableBody = document.getElementById(
    UI_WIDGETS.topMember.table.bodyId
  );
  if (topMemberTableBody) {
    topMemberTableBody.addEventListener("click", (event) => {
      const row = event.target.closest("tr.top-member-table-row");
      if (row && state.filterParams && state.currentStatus) {
        const kdCust = row.dataset.kdCust;
        const namaCust = row.dataset.namaCust;
        if (kdCust) {
          const urlParams = buildFilterQueryString();
          urlParams.append("status", state.currentStatus);
          urlParams.append("kd_cust", kdCust);
          urlParams.append("nama_cust", namaCust);
          const targetUrl = `customer.php?${urlParams.toString()}`;
          window.location.href = targetUrl;
        }
      }
    });
  }
  const topProductTableBody = document.getElementById(
    UI_WIDGETS.topProduct.table.bodyId
  );
  if (topProductTableBody) {
    topProductTableBody.addEventListener("click", (event) => {
      const row = event.target.closest("tr.top-product-table-row");
      if (row && state.filterParams && state.currentStatus) {
        const kdCust = row.dataset.kdCust;
        const namaCust = row.dataset.namaCust;
        if (kdCust) {
          const urlParams = buildFilterQueryString();
          urlParams.append("status", state.currentStatus);
          urlParams.append("kd_cust", kdCust);
          urlParams.append("nama_cust", namaCust);
          const targetUrl = `customer.php?${urlParams.toString()}`;
          window.location.href = targetUrl;
        }
      }
    });
  }
  const topMemberFrequencyTableBody = document.getElementById(
    UI_WIDGETS.topMemberFrequency.table.bodyId
  );
  if (topMemberFrequencyTableBody) {
    topMemberFrequencyTableBody.addEventListener("click", (event) => {
      const row = event.target.closest("tr.top-member-frequency-table-row");
      if (row && state.filterParams && state.currentStatus) {
        const kdCust = row.dataset.kdCust;
        const namaCust = row.dataset.namaCust;
        if (kdCust) {
          const urlParams = buildFilterQueryString();
          urlParams.append("status", state.currentStatus);
          urlParams.append("kd_cust", kdCust);
          urlParams.append("nama_cust", namaCust);
          const targetUrl = `customer.php?${urlParams.toString()}`;
          window.location.href = targetUrl;
        }
      }
    });
  }
  if (state.filterParams.filter_type && state.currentStatus) {
    loadWidgetData(
      UI_WIDGETS.age,
      api.getMemberByAge,
      [state.filterParams, state.currentStatus],
      charts.renderAgeChart,
      ageChartInstance,
      renderAgeTable,
      "Tidak ada data member (umur) untuk filter ini."
    ).then((instance) => (ageChartInstance = instance));
    loadWidgetData(
      UI_WIDGETS.topMember,
      api.getTopMembersByFilter,
      [state.filterParams, state.currentStatus],
      charts.renderTopMemberChart,
      topMemberChartInstance,
      renderTopMemberTable,
      "Tidak ada data top member untuk filter ini."
    ).then((instance) => (topMemberChartInstance = instance));
    loadWidgetData(
      UI_WIDGETS.topProduct,
      api.getTopMemberProductPairs,
      [state.filterParams, state.currentStatus],
      charts.renderTopProductChart,
      topMemberProductChartInstance,
      renderTopProductTable,
      "Tidak ada data pembelian produk untuk filter ini."
    ).then((instance) => (topMemberProductChartInstance = instance));
    loadWidgetData(
      UI_WIDGETS.topMemberFrequency,
      api.getTopMembersByFrequency,
      [state.filterParams, state.currentStatus],
      charts.renderTopMemberFrequencyChart,
      topMemberFrequencyChartInstance,
      renderTopMemberFrequencyTable,
      "Tidak ada data top member (frekuensi) untuk filter ini."
    ).then((instance) => (topMemberFrequencyChartInstance = instance));
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
    setUIState(UI_WIDGETS.age.chart, "error", errorMsg);
    setUIState(UI_WIDGETS.age.table, "error", errorMsg);
    setUIState(UI_WIDGETS.location.chart, "error", errorMsg);
    setUIState(UI_WIDGETS.location.table, "error", errorMsg);
    setUIState(UI_WIDGETS.topMember.chart, "error", errorMsg);
    setUIState(UI_WIDGETS.topMember.table, "error", errorMsg);
    setUIState(UI_WIDGETS.topProduct.chart, "error", errorMsg);
    setUIState(UI_WIDGETS.topProduct.table, "error", errorMsg);
    setUIState(UI_WIDGETS.topMemberFrequency.chart, "error", errorMsg);
    setUIState(UI_WIDGETS.topMemberFrequency.table, "error", errorMsg);
  }
});
