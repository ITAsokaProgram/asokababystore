<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lokasi ASOKA Baby Store</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/src/output2.css">
    <link rel="icon" type="image/png" href="/images/logo1.png" />


    <style>
        #map {
            height: 100vh;
            flex-grow: 1;
        }

        .leaflet-popup-content {
            width: 15rem;
        }
    </style>
</head>

<body class="flex h-screen bg-gray-100" style="font-family: 'Poppins', sans-serif;">

    <div id="sidebar" class="w-96 bg-white shadow-lg p-5 overflow-y-auto">
        <h4 class="text-xl font-bold mb-3">Daftar Lokasi</h4>
        <div id="location-list" class="space-y-4">
            <p class="text-gray-400 text-sm text-center py-10">Memuat data...</p>
        </div>
    </div>

    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // 1. Inisialisasi Peta
        const map = L.map('map').setView([-6.1503038, 106.7107386], 11);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        const asokaIcon = L.icon({
            iconUrl: 'public/images/marker2.png',
            iconSize: [32, 50],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });

        const locationList = document.getElementById('location-list');

        // 2. Fungsi Ambil Data dari Database (API)
        async function fetchLocations() {
            try {
                // Panggil file PHP
                const response = await fetch('src/api/shared/get_stores_location.php');
                const result = await response.json();

                if (result.success) {
                    // Jika sukses, render data ke layar
                    renderLocations(result.data);
                } else {
                    locationList.innerHTML = `<p class="text-red-500 text-center">Gagal: ${result.message}</p>`;
                }
            } catch (error) {
                console.error(error);
                locationList.innerHTML = `<p class="text-red-500 text-center">Gagal koneksi database.</p>`;
            }
        }

        // 3. Fungsi Render (Membuat Tampilan sesuai Design Anda)
        function renderLocations(data) {
            // Kosongkan list (hapus tulisan "Memuat data...")
            locationList.innerHTML = '';

            data.forEach((item, index) => {
                // A. Mapping Data Database ke Variabel Design
                // Pastikan nama kolom database sesuai dengan PHP (Nm_Store, alm_toko, dll)
                const loc = {
                    name: item.Nm_Store,
                    address: item.alm_toko,
                    phone: item.telp || '-',
                    image: "public/images/logo.png", // Gambar default
                    coords: [parseFloat(item.latitude), parseFloat(item.longitude)],
                    mapLink: item.map_link || '#'
                };

                // Validasi koordinat (jaga-jaga jika kosong)
                if (isNaN(loc.coords[0]) || isNaN(loc.coords[1])) return;

                // B. Buat Marker di Peta
                let marker = L.marker(loc.coords, { icon: asokaIcon }).addTo(map)
                    .bindPopup(`
                        <h3 class='text-lg font-bold mb-2 text-black'>${loc.name}</h3>
                        <p class='text-sm text-gray-600'>${loc.address}</p>
                        <p class='text-sm text-gray-500 flex items-center'><span class='mr-2'>📞</span>${loc.phone}</p>
                        <img src='${loc.image}' class='w-[10rem] h-[3rem] rounded-md block mx-auto' alt='Icon'>
                        <a href="${loc.mapLink}" target="_blank" 
                           class="block mt-3 py-2 bg-gradient-to-r from-green-400 via-green-500 to-green-600 hover:bg-gradient-to-br rounded-md text-white text-center uppercase">
                           Buka Maps
                        </a>
                    `, { minWidth: 200 });

                // C. Buat Item di Sidebar (Design Gradient Teal Anda)
                let listItem = document.createElement('div');
                listItem.className = "location-item flex-col p-4 rounded-lg shadow-xl flex gap-3 cursor-pointer hover:bg-gray-200 bg-white border border-gray-100"; // Saya tambah bg-white agar jelas

                listItem.innerHTML = `
                    <div class="flex justify-center">
                        <img src="${loc.image}" alt="logo" class="w-[10rem] h-[3rem] object-contain">
                    </div>
                    <div>
                        <h5 class="text-lg font-semibold">${loc.name}</h5>
                        <p class="text-sm text-gray-600 mt-1">${loc.address}</p>
                        <p class="text-sm text-gray-500 mt-1 mb-3">Telp: ${loc.phone}</p>
                        <div class="w-full px-3 py-4 rounded-lg text-center bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 text-white hover:uppercase hover:bg-teal-700 cursor-pointer transition duration-300 ease-out">
                            Cek Lokasinya
                        </div>
                    </div>
                `;

                // Event klik pada sidebar untuk memindahkan peta
                listItem.addEventListener("click", () => {
                    map.setView(loc.coords, 16); // Zoom level saya ubah ke 16 biar lebih dekat
                    marker.openPopup();
                });

                locationList.appendChild(listItem);
            });
        }

        // Jalankan fetch saat halaman dibuka
        fetchLocations();

    </script>
</body>

</html>