import { locations } from "./data.js";

const map = L.map('map', {
    dragging: true,
    zoomControl: true,
    touchZoom: true
}).setView([-6.1503038, 106.7107386], 11);

map.getContainer().addEventListener("touchstart", (e) => {
    if (e.touches.length === 2) {
        map.dragging.enable();
    } else {
        map.dragging.disable();
    }
});
map.getContainer().addEventListener("touchend", (e) => {
    map.dragging.disable();
})

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);
const asokaIcon = L.icon({
    iconUrl: 'public/images/marker2.png', // ganti dengan path ke ikon motor kamu
    iconSize: [32, 50], // ukuran ikon
    iconAnchor: [16, 32], // posisi anchor (titik yang menempel ke peta)
    popupAnchor: [0, -32] // posisi popup relatif terhadap icon
});

const refreshButton = L.control({ position: 'topright' });
refreshButton.onAdd = function () {
    const button = L.DomUtil.create('button', 'leaflet-bar leaflet-control leaflet-control-custom');
    button.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i>';
    button.style.backgroundColor = 'white';
    button.style.width = '30px';
    button.style.height = '25px';
    button.style.borderRadius = '5px';
    button.style.boxShadow = '0 1px 5px rgba(0,0,0,0.65)';
    button.style.cursor = 'pointer';
    button.style.margin = '10px';
    button.style.border = 'none';
    button.style.outline = 'none';
    button.style.fontSize = '1.2rem';
    button.style.color = '#000';
    button.style.transition = 'background-color 0.3s, transform 0.3s';
    button.onmouseover = function () {
        button.style.backgroundColor = '#f0f0f0';
        button.style.transform = 'scale(1.1)';
    };
    button.onmouseout = function () {
        button.style.backgroundColor = 'white';
        button.style.transform = 'scale(1)';
    };

    L.DomEvent.on(button, 'click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (marker) {
            // Jika sudah ada marker lokasi user, zoom ke marker dan buka popup
            const { lat, lng } = marker.getLatLng();
            map.setView([lat, lng], 15);
            showNearbyLocations(lat, lng);
        } else {
            // Jika belum ada marker, dapatkan lokasi user
            getCurrentLocation();
        }
    });


    return button;
}

refreshButton.addTo(map);

// Tambahkan tombol baru untuk menampilkan daftar toko terdekat
const nearbyButton = L.control({ position: 'topright' });
nearbyButton.onAdd = function () {
    const button = L.DomUtil.create('button', 'leaflet-bar leaflet-control leaflet-control-custom');
    button.innerHTML = '<i class="fa-solid fa-list"></i>';
    button.title = 'Lihat Daftar Toko Terdekat';
    button.style.backgroundColor = 'white';
    button.style.width = '30px';
    button.style.height = '25px';
    button.style.borderRadius = '5px';
    button.style.boxShadow = '0 1px 5px rgba(0,0,0,0.65)';
    button.style.cursor = 'pointer';
    button.style.margin = '10px';
    button.style.border = 'none';
    button.style.outline = 'none';
    button.style.fontSize = '1.2rem';
    button.style.color = '#000';
    button.style.transition = 'background-color 0.3s, transform 0.3s';
    button.onmouseover = function () {
        button.style.backgroundColor = '#f0f0f0';
        button.style.transform = 'scale(1.1)';
    };
    button.onmouseout = function () {
        button.style.backgroundColor = 'white';
        button.style.transform = 'scale(1)';
    };
    L.DomEvent.on(button, 'click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (marker) {
            const { lat, lng } = marker.getLatLng();
            const nearby = findNearByLocations(lat, lng);
            if (nearby.length > 0) {
                let html = '<ol class="text-left pl-4">';
                nearby.forEach((toko, i) => {
                    html += `<li class='mb-2'><b>${i+1}. ${toko.name}</b><br><span class='text-xs text-gray-600'>${toko.address}</span><br><span class='text-xs text-gray-500'>Jarak: ${toko.distance.toFixed(2)} km</span></li>`;
                });
                html += '</ol>';
                html += `<div class='mt-2 text-xs text-yellow-600 italic'>*Jarak dihitung garis lurus, bukan rute jalan sebenarnya</div>`;
                Swal.fire({
                    icon: 'info',
                    title: 'Daftar Toko Terdekat',
                    html,
                    width: 420,
                    confirmButtonText: 'Tutup',
                    customClass: {
                        confirmButton: 'bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700',
                        popup: 'p-4 rounded-lg'
                    }
                });
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Tidak Ada Toko Terdekat',
                    text: 'Tidak ditemukan toko dalam radius 5 km dari lokasi Anda.'
                });
            }
        } else {
            Swal.fire({
                icon: 'info',
                title: 'Lokasi Belum Diketahui',
                text: 'Silakan klik tombol lokasi terlebih dahulu.'
            });
        }
    });
    return button;
};
nearbyButton.addTo(map);

const locationList = document.getElementById('location-list');
const markers = []; // simpan marker agar bisa dipakai ulang
const citySelect = document.getElementById('search-city');
let marker, circle;
function searchToko() {
    const searchInput = document.getElementById("search-toko");

    searchInput.addEventListener("input", function () {
        const query = this.value.toLowerCase();

        // Filter berdasarkan nama dan (jika citySelect tidak "all") juga kota
        const city = citySelect.value;
        const filtered = locations.filter(loc => {
            const matchCity = city === "all" || loc.city === city;
            const matchName = loc.name.toLowerCase().includes(query);
            return matchCity && matchName;
        });

        renderFilteredLocations(filtered);
    });
}

function createLocationItem(loc, index) {
    // Marker
    const marker = L.marker(loc.coords, { icon: asokaIcon }).addTo(map)
        .bindPopup(`
            <h3 class='text-lg font-bold mb-2 text-black'>${loc.name}</h3>
            <p class='text-sm text-gray-600'>${loc.address}</p>
            <p class='text-sm text-gray-500 flex items-center'><span class='mr-2'>📞</span>08${loc.phone}</p>
            <img src='${loc.image}' class='w-[10rem] h-[3rem] rounded-md block mx-auto' alt='Icon'>
            <a href="${loc.mapLink}" target="_blank" 
                class="block mt-3 py-2 bg-gradient-to-r from-green-400 via-green-500 to-green-600 hover:bg-gradient-to-br rounded-md text-white text-center uppercase">
                Buka Maps
            </a>
        `, { minWidth: 200 });

    markers.push(marker); // simpan

    // Sidebar
    const listItem = document.createElement('div');
    listItem.className = "location-item bg-white rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 cursor-pointer transform hover:-translate-y-1";
    listItem.setAttribute("data-index", index);
    listItem.innerHTML = `
        <div class="p-4">
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                    <h5 class="text-lg font-bold text-gray-800 mb-2">${loc.name}</h5>
                    <div class="flex items-start gap-2 mb-2">
                        <i class="fas fa-map-marker-alt text-pink-500 mt-1 text-sm"></i>
                        <p class="text-sm text-gray-600 leading-relaxed">${loc.address}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fab fa-whatsapp text-green-500 text-sm"></i>
                        <a href="https://wa.me/62${loc.phone}" class="text-sm text-gray-700 font-semibold hover:text-green-600 transition-colors">0${loc.phone}</a>
                    </div>
                </div>
                <div class="ml-4 flex-shrink-0"> 
                    <img src="${loc.image}" alt="logo" class="w-20 h-8 object-contain rounded">
                </div>
            </div>
            
            <div class="flex gap-2 mt-3">
                <button onclick="window.open('${loc.mapLink}', '_blank')" class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white text-xs font-semibold py-2 px-3 rounded-lg transition-all duration-300 flex items-center justify-center gap-1">
                    <i class="fas fa-map"></i>
                    <span>Maps</span>
                </button>
                <a href="https://wa.me/62${loc.phone}" class="flex-1 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white text-xs font-semibold py-2 px-3 rounded-lg transition-all duration-300 flex items-center justify-center gap-1">
                    <i class="fab fa-whatsapp"></i>
                    <span>WhatsApp</span>
                </a>
            </div>
        </div>
            </div>
        </div>
    `;

    listItem.addEventListener("click", () => {
        map.setView(loc.coords, 13);
        marker.openPopup();
    });

    locationList.appendChild(listItem);
}

// Awal: render semua
locations.forEach((loc, index) => {
    createLocationItem(loc, index);
});

// === FILTER ===
function handleCityChange(event) {
    const selectedCity = event.target.value;
    const filtered = filterLocationsByCity(selectedCity);

    renderFilteredLocations(filtered);
}
function filterLocationsByCity(city) {
    if (city === "all") return locations;
    return locations.filter(loc => loc.city === city);
}
function renderFilteredLocations(locationsToRender) {
    // Clear existing markers
    markers.forEach(marker => map.removeLayer(marker));
    markers.length = 0;

    // Clear existing list
    locationList.innerHTML = '';
    
    // Update location count
    const locationCount = document.getElementById('location-count');
    const noResults = document.getElementById('no-results');
    
    if (locationCount) {
        locationCount.textContent = locationsToRender.length;
    }
    
    // Show/hide no results message
    if (noResults) {
        if (locationsToRender.length === 0) {
            noResults.classList.remove('hidden');
            locationList.classList.add('hidden');
        } else {
            noResults.classList.add('hidden');
            locationList.classList.remove('hidden');
        }
    }

    // Render new locations
    locationsToRender.forEach((loc, index) => {
        createLocationItem(loc, index);
    });
}

// === LOCATION ===
function onLocation(e) {
    const { latitude: lat, longitude: lng, accuracy } = e;
    const coords = [lat, lng];

    // Update existing marker and circle if they exist
    if (marker) {
        marker.setLatLng(coords);
        circle.setLatLng(coords).setRadius(accuracy);
        return;
    }

    // Create new marker and circle only once
    getLocationName(lat, lng, locationName => {
        marker = L.marker(coords)
            .addTo(map)
            .bindPopup(`Lokasi Anda Saat Ini: ${locationName}`, {
                closeOnClick: true
            })
            .openPopup();

        circle = L.circle(coords, {
            color: 'blue',
            fillColor: '#30a7ff',
            fillOpacity: 0.5,
            radius: accuracy
        }).addTo(map);

        map.setView(coords, 15);
    });

    showNearbyLocations(lat, lng);
}

function onLocationError(e) {
    if (e.code === 1) { // PERMISSION_DENIED
        Swal.fire({
            icon: 'info',
            title: '<span class="text-blue-600 font-semibold text-lg">Izin Lokasi Diblokir</span>',
            html: `
        <div class="text-sm text-gray-700">
            <p class="mb-2">Browser menolak akses lokasi. Untuk mengaktifkannya kembali:</p>
            <ul class="list-disc pl-5 space-y-1 text-left">
                <li>
                    Klik ikon seperti ini 
                    <span class="inline-block bg-gray-800 text-white px-2 py-1 rounded text-xs ml-1">
                        <i class="fa-solid fa-sliders"></i>
                    </span> di sebelah kiri address bar
                </li>
                <li>Pilih <span class="text-blue-600 font-medium">Pengaturan Situs</span> / <i>Site Settings</i></li>
                <li>Untuk Hp Pilih <span class="text-blue-600 font-medium">Izin</span> / <i>Permission</i></li>
                <li>Ubah izin lokasi ke <span class="text-green-600 font-semibold">Izinkan</span> / <i>Allow</i></li>
                <li>Refresh halaman</li>
            </ul>
        </div>
    `,
            confirmButtonText: 'Saya Mengerti',
            customClass: {
                confirmButton: 'bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400',
                popup: 'p-4 rounded-lg'
            },
            width: 480
        });
    } else if (e.code === 2) { // POSITION_UNAVAILABLE
        Swal.fire({
            icon: 'warning',
            title: 'Lokasi Tidak Tersedia',
            text: 'Perangkat tidak dapat menemukan lokasi saat ini.'
        });
    } else if (e.code === 3) { // TIMEOUT
        Swal.fire({
            icon: 'info',
            title: 'Permintaan Lokasi Timeout',
            text: 'Coba lagi, mungkin sinyal GPS sedang lemah.'
        });
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Kesalahan Lokasi',
            text: 'Terjadi kesalahan saat mencoba mendapatkan lokasi.'
        });
    }
}

async function getLocationName(lat, lng, callBack) {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
        const data = await response.json();
        const address = data.display_name;
        callBack(address);
    } catch (error) {
        console.error("Error fetching location name:", error);
        callBack("Lokasi tidak ditemukan");
    }
}

function getDistanceFromLatLonInKm(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = deg2rad(lat2 - lat1);
    const dLon = deg2rad(lon2 - lon1);
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

function deg2rad(deg) {
    return deg * (Math.PI / 180);
}

function findNearByLocations(lat, lng, radiusKm = 5) {
    const nearbyLocations = locations.filter(loc => {
        const distance = getDistanceFromLatLonInKm(lat, lng, loc.coords[0], loc.coords[1]);
        loc.distance = distance;
        return distance <= radiusKm;
    });
    return nearbyLocations;
}

function showNearbyLocations(lat, lng) {
    const nearby = findNearByLocations(lat, lng);
    renderFilteredLocations(nearby, 5);
}

function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.watchPosition(
            function (position) {
                onLocation({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy
                });
            },
            onLocationError,
            {
                enableHighAccuracy: true,
                maximumAge: 1000,
                timeout: 5000
            }
        );
    } else {
        alert("Geolocation is not supported by this browser.");
    }
}

// Event listener
citySelect.addEventListener("change", handleCityChange);
searchToko();


