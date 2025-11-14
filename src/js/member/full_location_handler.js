import * as api from "./member_api_service.js";
import * as charts from "./member_chart_service.js";

let currentFilter = "";
let currentStatus = "";
let locationChartInstance = null;
let currentLocationLevel = "city";
let selectedCity = null;
let selectedDistrict = null;

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

// --- FUNGSI showTopProductsModal DIHAPUS ---

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

function updateLocationHeader() {
  const header = document.getElementById("location-chart-header");
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
      selectedDistrict,
      "all" // Meminta semua data
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

document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  currentFilter = params.get("filter");
  currentStatus = params.get("status");

  const locationTableBody = document.getElementById(
    UI_ELEMENTS_LOCATION_TABLE.bodyId
  );
  if (locationTableBody) {
    locationTableBody.addEventListener("click", async (event) => {
      const row = event.target.closest("tr.location-table-row");
      if (!row) return;

      const locationName = row.dataset.locationName;
      if (!locationName) return;

      if (currentLocationLevel === "city") {
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

        // "Intip" data menggunakan limit "all" agar konsisten
        const peekResult = await api.getMemberByLocation(
          currentFilter,
          currentStatus,
          "district", // Cek level selanjutnya
          clickedCity,
          null,
          "all" // Konsisten dengan loadLocationData()
        );

        const hasNextLevelData =
          peekResult.success && peekResult.data.length > 0;
        Swal.close();

        // --- PERUBAHAN LOGIKA DI SINI ---
        if (hasNextLevelData) {
          Swal.fire({
            title: `Anda memilih: ${clickedCity}`,
            text: "Apa yang ingin Anda tampilkan selanjutnya?",
            icon: "question",
            showConfirmButton: true,
            confirmButtonText: "Lihat Laporan Produk Teratas", // Ganti teks
            showDenyButton: true,
            denyButtonText: "Tampilkan Data per Kecamatan",
            showCancelButton: true,
            cancelButtonText: "Batal",
          }).then((result) => {
            if (result.isConfirmed) {
              // Aksi 1: Redirect ke halaman laporan
              const targetUrl = `lokasi.php?filter=${encodeURIComponent(
                currentFilter
              )}&status=${encodeURIComponent(
                currentStatus
              )}&city=${encodeURIComponent(clickedCity)}`;
              window.location.href = targetUrl;
            } else if (result.isDenied) {
              // Aksi 2: Drill down
              currentLocationLevel = "district";
              selectedCity = clickedCity;
              selectedDistrict = null;
              loadLocationData();
            }
          });
        } else {
          // Aksi 1: Langsung redirect jika tidak ada data sub-level
          const targetUrl = `lokasi.php?filter=${encodeURIComponent(
            currentFilter
          )}&status=${encodeURIComponent(
            currentStatus
          )}&city=${encodeURIComponent(clickedCity)}`;
          window.location.href = targetUrl;
        }
        // --- AKHIR PERUBAHAN ---
      } else if (currentLocationLevel === "district") {
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

        // "Intip" data menggunakan limit "all" agar konsisten
        const peekResult = await api.getMemberByLocation(
          currentFilter,
          currentStatus,
          "subdistrict", // Cek level selanjutnya
          selectedCity,
          clickedDistrict,
          "all" // Konsisten dengan loadLocationData()
        );

        const hasNextLevelData =
          peekResult.success && peekResult.data.length > 0;
        Swal.close();

        // --- PERUBAHAN LOGIKA DI SINI ---
        if (hasNextLevelData) {
          Swal.fire({
            title: `Anda memilih: ${clickedDistrict}`,
            text: "Apa yang ingin Anda tampilkan selanjutnya?",
            icon: "question",
            showConfirmButton: true,
            confirmButtonText: "Lihat Laporan Produk Teratas", // Ganti teks
            showDenyButton: true,
            denyButtonText: "Tampilkan Data per Kelurahan",
            showCancelButton: true,
            cancelButtonText: "Batal",
          }).then((result) => {
            if (result.isConfirmed) {
              // Aksi 1: Redirect ke halaman laporan
              const targetUrl = `lokasi.php?filter=${encodeURIComponent(
                currentFilter
              )}&status=${encodeURIComponent(
                currentStatus
              )}&city=${encodeURIComponent(
                selectedCity
              )}&district=${encodeURIComponent(clickedDistrict)}`;
              window.location.href = targetUrl;
            } else if (result.isDenied) {
              // Aksi 2: Drill down
              currentLocationLevel = "subdistrict";
              selectedDistrict = clickedDistrict;
              loadLocationData();
            }
          });
        } else {
          // Aksi 1: Langsung redirect jika tidak ada data sub-level
          const targetUrl = `lokasi.php?filter=${encodeURIComponent(
            currentFilter
          )}&status=${encodeURIComponent(
            currentStatus
          )}&city=${encodeURIComponent(
            selectedCity
          )}&district=${encodeURIComponent(clickedDistrict)}`;
          window.location.href = targetUrl;
        }
        // --- AKHIR PERUBAHAN ---
      }
    });
  }

  if (currentFilter && currentStatus) {
    loadLocationData();

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
    setChartUIState(UI_ELEMENTS.location, "error", errorMsg);
    setLocationTableUIState("error", errorMsg);
  }
});
