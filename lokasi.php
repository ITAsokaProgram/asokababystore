<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lokasi ASOKA Baby Store</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/src/output2.css">

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

    <!-- Sidebar -->
    <div id="sidebar" class="w-96 bg-white shadow-lg p-5 overflow-y-auto">
        <h4 class="text-xl font-bold mb-3">Daftar Lokasi</h4>
        <div id="location-list" class="space-y-4"></div> <!-- List lokasi akan di-generate di sini -->
    </div>

    <!-- Map -->
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('map').setView([-6.1503038, 106.7107386], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        const asokaIcon = L.icon({
            iconUrl: 'public/images/marker2.png', // ganti dengan path ke ikon motor kamu
            iconSize: [32, 50], // ukuran ikon
            iconAnchor: [16, 32], // posisi anchor (titik yang menempel ke peta)
            popupAnchor: [0, -32] // posisi popup relatif terhadap icon
        });

        // Data lokasi toko
        const locations = [
            {
                name: "ASOKA Baby Store Pusat",
                address: "JL. UTAN JATI. BLOK LB 5, NO. 7-10, KALIDERES - JAKBAR (BELAKANG MALL DM)",
                phone: "",
                image: "public/images/logo.png",
                coords: [-6.1478576, 106.712504],
                mapLink: "https://maps.app.goo.gl/MtbinoTWpMer2Usw5"
            },
            {
                name: "ASOKA Baby Store Ciledug",
                address: "JL HOS COKROAMINOTO BLOK 0 NO. 18 RT.001/005 SUDIMARA TIMUR CILEDUG. TANGERANG - BANTEN",
                phone: "", image: "public/images/logo.png",
                coords: [-6.2274337, 106.7152493],
                mapLink: "https://maps.app.goo.gl/KtRvy51AnfW32SVYA"
            },
            {
                name: "ASOKA Baby Store Condet",
                address: "JL RAYA CONDET BLOK O NO. 39 RT.009/006 BATU AMPAR KRAMAT JATI, JAKARTA TIMUR",
                phone: "",
                image: "public/images/logo.png",
                coords: [-6.2707673, 106.8585867],
                mapLink: "https://maps.app.goo.gl/gND9QwMJMG3Gs6hM7"
            },
            {
                name: "ASOKA Baby Store Bintaro",
                address: "Jl. Bintaro Utama 5 Blok EA No. 21-23, East Jurang Manggu, Pondok Aren, South Tangerang City, Banten 15222",
                phone: "",
                image: "public/images/logo.png",
                coords: [-6.2701572, 106.7314572],
                mapLink: "https://maps.app.goo.gl/sAeiwLQHXUmfRVq39"
            },
            {
                name: "ASOKA Baby Store Cinere",
                address: "Jl cinere raya NC 17, Cinere, Kec. Cinere, Kota Depok, Jawa Barat 16514",
                phone: "",
                image: "public/images/logo.png",
                coords: [-6.3407371, 106.7767895],
                mapLink: "https://maps.app.goo.gl/UrCR8tyX4U1t1mGW8"
            },
            {
                name: "ASOKA Baby Store Pamulang",
                address: "Jl. Siliwangi No.9 Blok E, West Pamulang, Pamulang, South Tangerang City, Banten 15417",
                phone: "",
                image: "public/images/logo.png",
                coords: [-6.3433559, 106.7277418],
                mapLink: "https://maps.app.goo.gl/5q6oxhaaU2xxr7cN7"
            },
            {
                name: "ASOKA Baby Store Kartini",
                address: "Jl. Kartini No.43, Depok, Kec. Pancoran Mas, Kota Depok, Jawa Barat 16431",
                phone: "",
                image: "public/images/logo.png",
                coords: [-6.4024028, 106.8160667],
                mapLink: "https://maps.app.goo.gl/zgHqb3dn5tFSxsYf6"
            },
            {
                name: "ASOKA Baby Store Parung",
                address: "Jl. H. Mawi No.1A, Bojong Sempu, Kec. Parung, Kabupaten Bogor, Jawa Barat 16120",
                phone: "",
                image: "public/images/logo.png",
                coords: [-6.4387641, 106.6980704],
                mapLink: "https://maps.app.goo.gl/9dzKhh6LVdaoNGvg6"
            },
            {
                name: "ASOKA Baby Store Duren Sawit",
                address: "RT.5/RW.12, Pd. Bambu, Kec. Duren Sawit, Kota Jakarta Timur, Daerah Khusus Ibukota Jakarta",
                phone: "",
                image: "public/images/logo.png",
                coords: [-6.2428015, 106.9007402],
                mapLink: "https://maps.app.goo.gl/igtyQLDCJkYFMihR8"
            },
            {
                name: "ASOKA Baby Store Daan Mogot",
                address: "Perumahan Daan Mogot Baru, Jalan Gilimanuk No. 38, Kalideres, RT.8/RW.12, Kalideres, Kec. Kalideres, Kota Jakarta Barat, Daerah Khusus Ibukota Jakarta 11840",
                phone: "",
                image: "public/images/logo.png",
                coords: [-6.1503038, 106.7107386],
                mapLink: "https://maps.app.goo.gl/n1tqMKPZ9mtmrsan8"
            },
            {
                name: "ASOKA Baby Store Poris",
                address: "Garden, Jl. Raya Poris Indah Blok A1 No.3, RT.001/RW.006, Cipondoh Indah, Kec. Cipondoh, Kota Tangerang, Banten 15122",
                phone: "",
                image: "public/images/logo.png",
                coords: [-6.1745246, 106.6827735],
                mapLink: "https://maps.app.goo.gl/7wpQxLe4ktC6xsAB9"
            },
            {
                name: "ASOKA Baby Store Harapan Indah",
                address: "Ruko Boulevard Hijau, Jl. Boulevard Hijau Raya No.38, RT.9/RW.024, Pejuang, Kecamatan Medan Satria, Kota Bks, Jawa Barat 17131",
                phone: "",
                image: "public/images/logo.png",
                coords: [-6.1867495, 106.9794397],
                mapLink: "https://maps.app.goo.gl/NUJRdk2yqhRStvJK9"
            },
            {
                name: "ASOKA Baby Store Rawamangun",
                address: "Jl. Tawes No.27 3, RT.3/RW.7, Jati, Kec. Pulo Gadung, Kota Jakarta Timur, Daerah Khusus Ibukota Jakarta 13220",
                phone: "",
                image: "public/images/logo.png",
                coords: [-6.2005677, 106.8926293],
                mapLink: "https://maps.app.goo.gl/fTjhSErzLvnGCJ3K8"
            },
            {
                name: "ASOKA Baby Store Manggar",
                address: "Kurnia Jaya, Kec. Manggar, Kabupaten Belitung Timur, Kepulauan Bangka Belitung 33512",
                phone: "",
                image: "public/images/logo.png",
                coords: [-2.8607083,108.2837832,17],
                mapLink: "https://maps.app.goo.gl/Pz5hAyTJdx1BCE8bA"
            },
            {
                name: "ASOKA Baby Store Toboali",
                address: "Toboali, Kec. Toboali, Kabupaten Bangka Selatan, Kepulauan Bangka Belitung 33783",
                phone: "",
                image: "public/images/logo.png",
                coords: [-3.0106671, 106.4563138],
                mapLink: "https://maps.app.goo.gl/ZZmppYENnWK77zTA9"
            },
            {
                name: "ASOKA Baby Store Semabung",
                address: "Semabung Lama, Kec. Bukitintan, Kota Pangkal Pinang, Kepulauan Bangka Belitung 33684",
                phone: "",
                image: "public/images/logo.png",
                coords: [-2.1350629, 106.1202253],
                mapLink: "https://maps.app.goo.gl/YPqxE3RcWE3SdGAt5"
            },
            {
                name: "ASOKA Baby Store Taman Galaxy",
                address: "Jl. Pulosirih Tengah 17 No.149 Blok E, RT.002/RW.014, Pekayon Jaya, Kec. Bekasi Sel., Kota Bks, Jawa Barat 17148",
                phone: "",
                image: "public/images/logo.png",
                coords: [-6.2594662, 106.9679006],
                mapLink: "https://maps.app.goo.gl/hAWG2XdgLSiEcDZS7"
            },
            {
                name: "ASOKA Baby Store Jati Waringin",
                address: "Jl. Raya Jatiwaringin No.56, RT.002/RW.003, Jatiwaringin, Kec. Pd. Gede, Kota Bks, Jawa Barat 17411",
                phone: "",
                image: "public/images/logo.png",
                coords: [-6.2760389, 106.9101746],
                mapLink: "https://maps.app.goo.gl/y5vqmGFsXR1Zpfo37"
            },
            {
                name: "ASOKA Baby Store Ceger",
                address: "Jl. Ceger Raya No.22, Jurang Manggu Tim., Kec. Pd. Aren, Kota Tangerang Selatan, Banten 15222",
                phone: "",
                image: "public/images/logo.png",
                coords: [-6.26322, 106.7237342],
                mapLink: "https://maps.app.goo.gl/rwMFJkHXCtqSHhsr9"
            },
            {
                name: "ASOKA Baby Store Jati Asih",
                address: "Jl. Raya Jatiasih No.86, RT.003/RW.004, Jatiasih, Kec. Jatiasih, Kota Bks, Jawa Barat 17423",
                phone: "",
                image: "public/images/logo.png",
                coords: [-6.2933534, 106.9588403],
                mapLink: "https://maps.app.goo.gl/jLoHUfd1Uge2yRoC6"
            },
            {
                name: "ASOKA Baby Store Graha Raya",
                address: "Jl. Boulevard Graha Raya No.11a, RT.003/RW.004, Sudimara Pinang, Kec. Serpong Utara, Kota Tangerang Selatan, Banten",
                phone: "",
                image: "public/images/logo.png",
                coords: [-6.2360847, 106.6756861],
                mapLink: "https://maps.app.goo.gl/tAGdwpMbnix2V1Zv6"
            },
            {
                name: "ASOKA Baby Store Cibubur",
                address: "Jl. Lap. Tembak Cibubur No.131, RT.9/RW.1, Pekayon, Kec. Ciracas, Kota Jakarta Timur, Jawa Barat 13720",
                phone: "",
                image: "public/images/logo.png",
                coords: [-6.3475857, 106.8726729],
                mapLink: "https://maps.app.goo.gl/HVikHsndYmAVug3G9"
            },
            {
                name: "ASOKA Baby Store PIK 2",
                address: "Soho Orchard Boulevard Blok A No. 15, Salembaran, Kec. Kosambi, Kabupaten Tangerang, Banten 15214",
                phone: "",
                image: "public/images/logo.png",
                coords: [-6.0514482, 106.6860203],
                mapLink: "https://maps.app.goo.gl/mfwpU2LXB1ED9owS6"
            },
        ];

        const locationList = document.getElementById('location-list');

        // Tambahkan marker ke peta dan generate list di sidebar
        locations.forEach((loc, index) => {
            let marker = L.marker(loc.coords , {icon : asokaIcon}).addTo(map)
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

            // Generate sidebar item
            let listItem = document.createElement('div');
            listItem.className = "location-item flex-col p-4 rounded-lg shadow-xl flex gap-3 cursor-pointer hover:bg-gray-200";
            listItem.setAttribute("data-index", index);
            listItem.innerHTML = `
                <div class="flex justify-center">
                    <img src="${loc.image}" alt="logo" class="w-[10rem] h-[3rem]">
                </div>
                <div>
                    <h5 class="text-lg font-semibold">${loc.name}</h5>
                    <p class="text-sm text-gray-600">${loc.address}</p>
                    <p class="text-sm text-gray-600">${loc.phone}</p>
                    <div class="w-full px-3 py-4 rounded-lg text-center bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 text-white hover:uppercase hover:bg-teal-700 cursor-pointer transition duration-300 ease-out">
                        Cek Lokasinya
                    </div>
                </div>
            `;

            locationList.appendChild(listItem);

            // Event klik pada sidebar untuk memindahkan peta ke lokasi terkait
            listItem.addEventListener("click", () => {
                map.setView(loc.coords, 13);
                marker.openPopup();
            });
        });

    </script>
</body>

</html>