// HAPUS atau COMMENT baris import ini karena kita akan pakai dynamic data
// import { locations } from "./data.js";

// Inisialisasi variabel global untuk menampung data
let locations = [];
const markers = [];
let marker, circle;

// --- 1. SETUP MAP ---
const map = L.map("map", {
  dragging: true,
  zoomControl: true,
  touchZoom: true,
}).setView([-6.1503038, 106.7107386], 10); // Default view Jakarta/Tangerang

L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
  attribution:
    '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
}).addTo(map);

const asokaIcon = L.icon({
  iconUrl: "public/images/marker2.png",
  iconSize: [32, 50],
  iconAnchor: [16, 32],
  popupAnchor: [0, -32],
});

// Handle touch gestures
map.getContainer().addEventListener("touchstart", (e) => {
  if (e.touches.length === 2) {
    map.dragging.enable();
  } else {
    map.dragging.disable();
  }
});
map.getContainer().addEventListener("touchend", (e) => {
  map.dragging.disable();
});

// --- 2. SETUP CONTROLS (Tombol Refresh & Nearby) ---
const refreshButton = L.control({ position: "topright" });
refreshButton.onAdd = function () {
  const button = L.DomUtil.create(
    "button",
    "leaflet-bar leaflet-control leaflet-control-custom"
  );
  button.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i>';
  // ... styling sama seperti sebelumnya ...
  button.style.backgroundColor = "white";
  button.style.width = "30px";
  button.style.height = "30px";
  button.style.borderRadius = "4px";
  button.style.cursor = "pointer";
  button.style.border = "1px solid #ccc";
  button.style.fontSize = "1.2rem";

  L.DomEvent.on(button, "click", function (e) {
    e.preventDefault();
    e.stopPropagation();
    if (marker) {
      const { lat, lng } = marker.getLatLng();
      map.setView([lat, lng], 15);
      showNearbyLocations(lat, lng);
    } else {
      getCurrentLocation();
    }
  });
  return button;
};
refreshButton.addTo(map);

const nearbyButton = L.control({ position: "topright" });
nearbyButton.onAdd = function () {
  const button = L.DomUtil.create(
    "button",
    "leaflet-bar leaflet-control leaflet-control-custom"
  );
  button.innerHTML = '<i class="fa-solid fa-list"></i>';
  // ... styling sama seperti sebelumnya ...
  button.style.backgroundColor = "white";
  button.style.width = "30px";
  button.style.height = "30px";
  button.style.marginTop = "10px";
  button.style.borderRadius = "4px";
  button.style.cursor = "pointer";
  button.style.border = "1px solid #ccc";
  button.style.fontSize = "1.2rem";

  L.DomEvent.on(button, "click", function (e) {
    e.preventDefault();
    e.stopPropagation();
    if (marker) {
      const { lat, lng } = marker.getLatLng();
      const nearby = findNearByLocations(lat, lng);

      if (nearby.length > 0) {
        let html = '<ol class="text-left pl-4 max-h-60 overflow-y-auto">';
        nearby.forEach((toko, i) => {
          html += `<li class='mb-2 border-b pb-2'><b>${i + 1}. ${
            toko.name
          }</b><br><span class='text-xs text-gray-600'>${
            toko.address
          }</span><br><span class='text-xs text-gray-500 font-bold'>Jarak: ${toko.distance.toFixed(
            2
          )} km</span></li>`;
        });
        html += "</ol>";

        Swal.fire({
          title: "Toko Terdekat",
          html: html,
          width: 400,
          confirmButtonText: "Tutup",
        });
      } else {
        Swal.fire("Info", "Tidak ada toko dalam radius 5 km.", "info");
      }
    } else {
      Swal.fire(
        "Info",
        "Silakan aktifkan lokasi Anda terlebih dahulu.",
        "warning"
      );
    }
  });
  return button;
};
nearbyButton.addTo(map);

// --- 3. CORE FUNCTIONS ---

const locationList = document.getElementById("location-list");
const citySelect = document.getElementById("search-city");

// Fungsi Utama: Mengambil data dari API
async function fetchLocations() {
  try {
    // Tampilkan loading state jika perlu (opsional)
    locationList.innerHTML =
      '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Memuat data toko...</div>';

    const response = await fetch("src/api/shared/get_stores_location.php");
    const result = await response.json();

    if (result.success) {
      // MAPPING: Ubah format Database ke format yang dipakai di JS (locations object)
      locations = result.data.map((item) => {
        return {
          id: item.Kd_Store,
          name: item.Nm_Store,
          address: item.alm_toko,
          city: item.kota, // Pastikan di DB kolomnya 'kota'
          phone: item.telp,
          image: "public/images/logo.png", // Gambar default
          coords: [parseFloat(item.latitude), parseFloat(item.longitude)],
          mapLink: item.map_link,
        };
      });

      // Populate Dropdown Kota secara otomatis berdasarkan data yang ada
      populateCityDropdown(locations);

      // Render awal semua lokasi
      renderFilteredLocations(locations);

      // Setup fitur pencarian setelah data tersedia
      setupSearchListener();
    } else {
      console.error("Gagal memuat data:", result.message);
      locationList.innerHTML =
        '<div class="text-center text-red-500">Gagal memuat data toko.</div>';
    }
  } catch (error) {
    console.error("Error fetching locations:", error);
    locationList.innerHTML =
      '<div class="text-center text-red-500">Terjadi kesalahan koneksi.</div>';
  }
}

function populateCityDropdown(data) {
  // Ambil list kota unik dari data
  const cities = [...new Set(data.map((item) => item.city))].sort();

  // Reset option, sisakan "Semua Kota"
  citySelect.innerHTML = '<option value="all">Semua Kota</option>';

  cities.forEach((city) => {
    if (city) {
      // Cek jika kota tidak kosong
      const option = document.createElement("option");
      option.value = city;
      option.textContent = city;
      citySelect.appendChild(option);
    }
  });
}

function createLocationItem(loc, index) {
  // 1. Buat Marker di Peta
  const markerItem = L.marker(loc.coords, { icon: asokaIcon })
    .addTo(map)
    .bindPopup(
      `
            <div class="text-center">
                <h3 class='text-lg font-bold mb-1 text-black'>${loc.name}</h3>
                <p class='text-sm text-gray-600 mb-2'>${loc.city}</p>
                <a href="${loc.mapLink}" target="_blank" 
                    class="inline-block px-4 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm">
                    Buka Maps
                </a>
            </div>
        `,
      { minWidth: 200 }
    );

  markers.push(markerItem); // Simpan referensi marker untuk dihapus nanti saat filter

  // 2. Buat Item di Sidebar List
  const listItem = document.createElement("div");
  listItem.className =
    "location-item bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 cursor-pointer transform hover:-translate-y-1 mb-3";

  // Format nomor HP untuk link WA (hapus karakter non-digit)
  const rawPhone = loc.phone ? loc.phone.replace(/\D/g, "") : "";
  const waLink = rawPhone
    ? `https://wa.me/62${rawPhone.replace(/^0/, "")}`
    : "#";
  const displayPhone = loc.phone || "-";

  listItem.innerHTML = `
        <div class="p-4">
            <div class="flex items-start justify-between mb-2">
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-800">${loc.name}</h3>
                    <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded mb-2">${
                      loc.city
                    }</span>
                    
                    <div class="flex items-start gap-2 mb-1">
                        <i class="fas fa-map-marker-alt text-pink-500 mt-1 text-xs"></i>
                        <p class="text-sm text-gray-600 leading-snug">${
                          loc.address
                        }</p>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-2 mt-3">
                <button onclick="window.open('${
                  loc.mapLink
                }', '_blank')" class="flex-1 bg-blue-50 text-blue-600 hover:bg-blue-100 text-xs font-semibold py-2 px-3 rounded-lg transition-colors flex items-center justify-center gap-1">
                    <i class="fas fa-map"></i> Maps
                </button>
                ${
                  rawPhone
                    ? `
                <a href="${waLink}" target="_blank" class="flex-1 bg-green-50 text-green-600 hover:bg-green-100 text-xs font-semibold py-2 px-3 rounded-lg transition-colors flex items-center justify-center gap-1">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>`
                    : ""
                }
            </div>
        </div>
    `;

  // Event saat list item diklik -> Zoom ke map
  listItem.addEventListener("click", () => {
    map.setView(loc.coords, 16);
    markerItem.openPopup();

    // Scroll map into view di mobile
    if (window.innerWidth < 1024) {
      document.getElementById("map").scrollIntoView({ behavior: "smooth" });
    }
  });

  locationList.appendChild(listItem);
}

function renderFilteredLocations(locationsToRender) {
  // 1. Hapus marker lama
  markers.forEach((m) => map.removeLayer(m));
  markers.length = 0; // Kosongkan array

  // 2. Hapus list lama
  locationList.innerHTML = "";

  const noResults = document.getElementById("no-results");

  // 3. Cek hasil
  if (locationsToRender.length === 0) {
    if (noResults) noResults.classList.remove("hidden");
  } else {
    if (noResults) noResults.classList.add("hidden");

    // 4. Render ulang
    locationsToRender.forEach((loc, index) => {
      createLocationItem(loc, index);
    });
  }
}

// --- 4. FILTERING & SEARCH ---

function setupSearchListener() {
  const searchInput = document.getElementById("search-toko");

  searchInput.addEventListener("input", function () {
    runFilter();
  });
}

// Event listener dropdown kota
citySelect.addEventListener("change", () => {
  runFilter();
});

function runFilter() {
  const searchInput = document.getElementById("search-toko");
  const query = searchInput.value.toLowerCase();
  const city = citySelect.value;

  const filtered = locations.filter((loc) => {
    const matchCity = city === "all" || loc.city === city;
    const matchName =
      loc.name.toLowerCase().includes(query) ||
      loc.address.toLowerCase().includes(query);
    return matchCity && matchName;
  });

  renderFilteredLocations(filtered);
}

// --- 5. GEOLOCATION UTILS ---

function onLocation(e) {
  const { latitude: lat, longitude: lng, accuracy } = e;
  const coords = [lat, lng];

  if (marker) {
    marker.setLatLng(coords);
    circle.setLatLng(coords).setRadius(accuracy);
  } else {
    getLocationName(lat, lng, (locationName) => {
      marker = L.marker(coords)
        .addTo(map)
        .bindPopup(`<b>Lokasi Anda</b><br>${locationName}`)
        .openPopup();

      circle = L.circle(coords, {
        color: "blue",
        fillColor: "#30a7ff",
        fillOpacity: 0.2,
        radius: accuracy,
      }).addTo(map);

      map.setView(coords, 14);
    });
  }

  // Optional: Otomatis tampilkan yang dekat saat lokasi ditemukan
  // showNearbyLocations(lat, lng);
}

function onLocationError(e) {
  // Kode error handling Anda sudah bagus, biarkan saja
  let msg = "Terjadi kesalahan mendapatkan lokasi.";
  if (e.code === 1) msg = "Izin lokasi diblokir browser.";
  else if (e.code === 2) msg = "Posisi tidak tersedia.";

  Swal.fire({ icon: "error", title: "Oops...", text: msg });
}

async function getLocationName(lat, lng, callBack) {
  try {
    const response = await fetch(
      `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`
    );
    const data = await response.json();
    callBack(data.display_name || "Lokasi Saya");
  } catch (error) {
    callBack("Lokasi Saya");
  }
}

function getCurrentLocation() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (pos) =>
        onLocation({
          latitude: pos.coords.latitude,
          longitude: pos.coords.longitude,
          accuracy: pos.coords.accuracy,
        }),
      onLocationError,
      { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
    );
  } else {
    alert("Browser tidak support Geolocation");
  }
}

// --- Helper Jarak ---
function getDistanceFromLatLonInKm(lat1, lon1, lat2, lon2) {
  const R = 6371; // Radius bumi (km)
  const dLat = deg2rad(lat2 - lat1);
  const dLon = deg2rad(lon2 - lon1);
  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(deg2rad(lat1)) *
      Math.cos(deg2rad(lat2)) *
      Math.sin(dLon / 2) *
      Math.sin(dLon / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return R * c;
}

function deg2rad(deg) {
  return deg * (Math.PI / 180);
}

function findNearByLocations(lat, lng, radiusKm = 10) {
  // Default radius 10km
  const nearbyLocations = locations
    .map((loc) => {
      const distance = getDistanceFromLatLonInKm(
        lat,
        lng,
        loc.coords[0],
        loc.coords[1]
      );
      return { ...loc, distance }; // Clone object dan tambah properti distance
    })
    .filter((loc) => loc.distance <= radiusKm);

  // Sort dari yang terdekat
  return nearbyLocations.sort((a, b) => a.distance - b.distance);
}

function showNearbyLocations(lat, lng) {
  const nearby = findNearByLocations(lat, lng, 15); // Cari radius 15km
  renderFilteredLocations(nearby);

  if (nearby.length === 0) {
    Swal.fire(
      "Info",
      "Tidak ditemukan toko terdekat dalam radius 15km.",
      "info"
    );
  } else {
    Swal.fire({
      icon: "success",
      title: "Ditemukan!",
      text: `Menampilkan ${nearby.length} toko terdekat dari lokasi Anda.`,
      timer: 2000,
      showConfirmButton: false,
    });
  }
}

// --- INISIALISASI ---
// Panggil fungsi fetch saat halaman dimuat
fetchLocations();
