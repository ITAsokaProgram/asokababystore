<?php

namespace Asoka\Constant;

class BranchConstants {

    
    public const REGION_SELECTION_MENU = [
        [
            'title' => 'PILIH WILAYAH',
            'rows' => [
                ['id' => 'DAFTAR_JABODETABEK', 'title' => 'Jabodetabek'],
                ['id' => 'DAFTAR_BELITUNG', 'title' => 'Bangka & Belitung'],
            ]
        ]
    ];

    public const REGION_SELECTION_MENU_LOKASI = [
        [
            'title' => 'PILIH WILAYAH LOKASI',
            'rows' => [
                ['id' => 'LOKASI_DAFTAR_JABODETABEK', 'title' => 'Jabodetabek'],
                ['id' => 'LOKASI_DAFTAR_BELITUNG', 'title' => 'Bangka & Belitung'],
            ]
        ]
    ];

    
    public const NOMOR_TELEPON_JABODETABEK = [
        'Daan Mogot'    => '6281808174105',
        'Poris'         => '6281806683401',
        'Harapan Indah' => '6287889552647',
        'Bintaro'       => '6287775692431',
        'Cinere'        => '6287787987127',
        'Pamulang'      => '6285947461478',
        'Ciledug'       => '6287849816901',
        'Kartini'       => '6287849816904',
        'Parung'        => '6287887689802',
        'Condet'        => '6287739974652',
        'Duren Sawit'   => '6285951449821',
        'Rawamangun'    => '6287773844521',
        'Cibubur'       => '6287863814646',
        'Ceger'         => '6285965847263',
        'Jatiwaringin'  => '6281998482529',
        'Graha Raya'    => '6287846959785',
        'Galaxy'        => '6285952415221',
        'Jatiasih'      => '6287856599869',
        'PIK 2'         => '6287772562015'
    ];

    public const LOKASI_JABODETABEK = [
        'Daan Mogot'    => ['latitude' => '-6.1503038', 'longitude' => '106.7107386', 'name' => 'ASOKA Baby Store Daan Mogot', 'address' => 'Perumahan Daan Mogot Baru, Jalan Gilimanuk No. 38, Kalideres, Kec. Kalideres, Kota Jakarta Barat'],
        'Poris'         => ['latitude' => '-6.1745246', 'longitude' => '106.6827735', 'name' => 'ASOKA Baby Store Poris', 'address' => 'Garden, Jl. Raya Poris Indah Blok A1 No.3, Cipondoh Indah, Kec. Cipondoh, Kota Tangerang'],
        'Harapan Indah' => ['latitude' => '-6.1867495', 'longitude' => '106.9794397', 'name' => 'ASOKA Baby Store Harapan Indah', 'address' => 'Ruko Boulevard Hijau, Jl. Boulevard Hijau Raya No.38, Pejuang, Kecamatan Medan Satria, Kota Bks'],
        'Bintaro'       => ['latitude' => '-6.2701572', 'longitude' => '106.7314572', 'name' => 'ASOKA Baby Store Bintaro', 'address' => 'Jl. Bintaro Utama 5 Blok EA No. 21-23, East Jurang Manggu, Pondok Aren, South Tangerang City'],
        'Cinere'        => ['latitude' => '-6.3407371', 'longitude' => '106.7767895', 'name' => 'ASOKA Baby Store Cinere', 'address' => 'Jl cinere raya NC 17, Cinere, Kec. Cinere, Kota Depok'],
        'Pamulang'      => ['latitude' => '-6.3433559', 'longitude' => '106.7277418', 'name' => 'ASOKA Baby Store Pamulang', 'address' => 'Jl. Siliwangi No.9 Blok E, West Pamulang, Pamulang, South Tangerang City'],
        'Ciledug'       => ['latitude' => '-6.2274337', 'longitude' => '106.7152493', 'name' => 'ASOKA Baby Store Ciledug', 'address' => 'JL HOS COKROAMINOTO BLOK 0 NO. 18 SUDIMARA TIMUR CILEDUG. TANGERANG'],
        'Kartini'       => ['latitude' => '-6.4024028', 'longitude' => '106.8160667', 'name' => 'ASOKA Baby Store Kartini', 'address' => 'Jl. Kartini No.43, Depok, Kec. Pancoran Mas, Kota Depok'],
        'Parung'        => ['latitude' => '-6.4387641', 'longitude' => '106.6980704', 'name' => 'ASOKA Baby Store Parung', 'address' => 'Jl. H. Mawi No.1A, Bojong Sempu, Kec. Parung, Kabupaten Bogor'],
        'Condet'        => ['latitude' => '-6.2707673', 'longitude' => '106.8585867', 'name' => 'ASOKA Baby Store Condet', 'address' => 'JL RAYA CONDET BLOK O NO. 39 BATU AMPAR KRAMAT JATI, JAKARTA TIMUR'],
        'Duren Sawit'   => ['latitude' => '-6.2428015', 'longitude' => '106.9007402', 'name' => 'ASOKA Baby Store Duren Sawit', 'address' => 'RT.5/RW.12, Pd. Bambu, Kec. Duren Sawit, Kota Jakarta Timur'],
        'Rawamangun'    => ['latitude' => '-6.2005677', 'longitude' => '106.8926293', 'name' => 'ASOKA Baby Store Rawamangun', 'address' => 'Jl. Tawes No.27 3, RT.3/RW.7, Jati, Kec. Pulo Gadung, Kota Jakarta Timur'],
        'Cibubur'       => ['latitude' => '-6.3475857', 'longitude' => '106.8726729', 'name' => 'ASOKA Baby Store Cibubur', 'address' => 'Jl. Lap. Tembak Cibubur No.131, Pekayon, Kec. Ciracas, Kota Jakarta Timur'],
        'Ceger'         => ['latitude' => '-6.26322', 'longitude' => '106.7237342', 'name' => 'ASOKA Baby Store Ceger', 'address' => 'Jl. Ceger Raya No.22, Jurang Manggu Tim., Kec. Pd. Aren, Kota Tangerang Selatan'],
        'Jatiwaringin'  => ['latitude' => '-6.2760389', 'longitude' => '106.9101746', 'name' => 'ASOKA Baby Store Jati Waringin', 'address' => 'Jl. Raya Jatiwaringin No.56, Jatiwaringin, Kec. Pd. Gede, Kota Bks'],
        'Graha Raya'    => ['latitude' => '-6.2360847', 'longitude' => '106.6756861', 'name' => 'ASOKA Baby Store Graha Raya', 'address' => 'Jl. Boulevard Graha Raya No.11a, Sudimara Pinang, Kec. Serpong Utara, Kota Tangerang Selatan'],
        'Galaxy'        => ['latitude' => '-6.2594662', 'longitude' => '106.9679006', 'name' => 'ASOKA Baby Store Taman Galaxy', 'address' => 'Jl. Pulosirih Tengah 17 No.149 Blok E, Pekayon Jaya, Kec. Bekasi Sel., Kota Bks'],
        'Jatiasih'      => ['latitude' => '-6.2933534', 'longitude' => '106.9588403', 'name' => 'ASOKA Baby Store Jati Asih', 'address' => 'Jl. Raya Jatiasih No.86, Jatiasih, Kec. Jatiasih, Kota Bks'],
        'PIK 2'         => ['latitude' => '-6.0514482', 'longitude' => '106.6860203', 'name' => 'ASOKA Baby Store PIK 2', 'address' => 'Soho Orchard Boulevard Blok A No. 15, Salembaran, Kec. Kosambi, Kabupaten Tangerang']
    ];

    public const CITIES_JABODETABEK = [
        ['id' => 'Daan Mogot', 'title' => 'Daan Mogot Baru, Jakbar'],
        ['id' => 'Poris', 'title' => 'Poris, Tangerang'],
        ['id' => 'Harapan Indah', 'title' => 'Harapan Indah, Bekasi'],
        ['id' => 'Bintaro', 'title' => 'Bintaro, Tangsel'],
        ['id' => 'Cinere', 'title' => 'Cinere, Depok'],
        ['id' => 'Pamulang', 'title' => 'Pamulang, Tangsel'],
        ['id' => 'Ciledug', 'title' => 'Ciledug, Tangerang'],
        ['id' => 'Kartini', 'title' => 'Kartini, Depok'],
        ['id' => 'Parung', 'title' => 'Parung, Bogor'],
        ['id' => 'Condet', 'title' => 'Condet, Jaktim'],
        ['id' => 'Duren Sawit', 'title' => 'Duren Sawit, Jaktim'],
        ['id' => 'Rawamangun', 'title' => 'Rawamangun, Jaktim'],
        ['id' => 'Cibubur', 'title' => 'Cibubur, Jakarta Timur'],
        ['id' => 'Ceger', 'title' => 'Ceger, Tangsel'],
        ['id' => 'Jatiwaringin', 'title' => 'Jatiwaringin, Jaktim'],
        ['id' => 'Graha Raya', 'title' => 'Graha Raya, Tangsel'],
        ['id' => 'Galaxy', 'title' => 'Taman Galaxy, Bekasi'],
        ['id' => 'Jatiasih', 'title' => 'Jatiasih, Bekasi'],
        ['id' => 'PIK 2', 'title' => 'PIK 2, Jakarta Utara']
    ];
    
    
    public const NOMOR_TELEPON_BELITUNG = [
        'Pangkal Pinang' => '6287896370431',
        'Merapin'       => '6287797561846',
        'Toboali'       => '6281995651279',
        'Semabung'      => '6281908239741',
        'Koba'          => '6285933237653',
        'Sungailiat'    => '6285933237651',
        'Tanjung Pandan'=> '6281929765780',
        'Air Raya'      => '6281929746487',
        'Manggar'       => '6287866839246',
    ];

    public const LOKASI_BELITUNG = [
        'Pangkal Pinang' => ['latitude' => '-2.13295', 'longitude' => '106.11545', 'name' => 'ASOKA Supermarket & Departemen Store Pangkal Pinang', 'address' => 'Jl. Ahmad Yani No.1, Batin Tikal, Kec. Taman Sari, Kota Pangkal Pinang, Kepulauan Bangka Belitung 33684, Indonesia'],
        'Merapin'       => ['latitude' => '-2.14881', 'longitude' => '106.13313', 'name' => 'ASOKA Baby Store - Merapin', 'address' => 'Ruko City Hill, Jl. Kampung Melayu No.Raya A1-A3, Bukit Merapin, Kec. Gerunggang, Kota Pangkal Pinang, Kepulauan Bangka Belitung 33123, Indonesia'],
        'Toboali'       => ['latitude' => '-3.0106671', 'longitude' => '106.4563138', 'name' => 'Asoka Toboali Bangka Selatan', 'address' => 'XFQ4+MGX, Toboali, Kec. Toboali, Kabupaten Bangka Selatan, Kepulauan Bangka Belitung 33783, Indonesia'],
        'Semabung'      => ['latitude' => '-2.1350629', 'longitude' => '106.1202253', 'name' => 'Asoka Baby Store Semabung', 'address' => 'Semabung Lama, Kec. Bukitintan, Kota Pangkal Pinang, Kepulauan Bangka Belitung 33684, Indonesia'],
        'Koba'          => ['latitude' => '-2.50462', 'longitude' => '106.30337', 'name' => 'ASOKA Supermarket Koba', 'address' => 'FCW3+485, Simpang Perlang, Kec. Koba, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung, Indonesia'],
        'Sungailiat'    => ['latitude' => '-1.85404', 'longitude' => '106.12196', 'name' => 'ASOKA Supermarket & Department Store Sungailiat', 'address' => 'Jl. Jenderal Sudirman No.127, Sungailiat, Sungai Liat, Kabupaten Bangka, Kepulauan Bangka Belitung 33215, Indonesia'],
        'Tanjung Pandan'=> ['latitude' => '-2.73752', 'longitude' => '107.63004', 'name' => 'ASOKA Baby Store - Tanjung Pandan', 'address' => '7J6J+X25, Parit, Kec. Tj. Pandan, Kabupaten Belitung, Kepulauan Bangka Belitung 33411, Indonesia'],
        'Air Raya'      => ['latitude' => '-2.74709', 'longitude' => '107.65842', 'name' => 'Asoka Baby Store Air Raya', 'address' => '7M35+59Q, Jl. Jend. Sudirman, Lesung Batang, Kec. Tj. Pandan, Kabupaten Belitung, Kepulauan Bangka Belitung 33412, Indonesia'],
        'Manggar'       => ['latitude' => '-2.8607083', 'longitude' => '108.2837832', 'name' => 'Asoka Baby Store Manggar', 'address' => '47QM+WXW, Kurnia Jaya, Kec. Manggar, Kabupaten Belitung Timur, Kepulauan Bangka Belitung 33512, Indonesia'],
    ];

    public const CITIES_BELITUNG = [
        ['id' => 'Pangkal Pinang', 'title' => 'Pangkal Pinang, Bangka'],
        ['id' => 'Merapin', 'title' => 'Merapin, Bangka'],
        ['id' => 'Toboali', 'title' => 'Toboali, Bangka'],
        ['id' => 'Semabung', 'title' => 'Semabung, Bangka'],
        ['id' => 'Koba', 'title' => 'Koba, Bangka'],
        ['id' => 'Sungailiat', 'title' => 'Sungailiat, Bangka'],
        ['id' => 'Tanjung Pandan', 'title' => 'Tanjung Pandan, Belitung'],
        ['id' => 'Air Raya', 'title' => 'Air Raya, Belitung'],
        ['id' => 'Manggar', 'title' => 'Manggar, Belitung'],
    ];

    
    
    public const ALL_NOMOR_TELEPON = self::NOMOR_TELEPON_JABODETABEK + self::NOMOR_TELEPON_BELITUNG;
    public const ALL_LOKASI_CABANG = self::LOKASI_JABODETABEK + self::LOKASI_BELITUNG;


    
    public const MAIN_MENU_SECTIONS = [
        [
            'title' => 'PILIHAN MENU UTAMA',
            'rows' => [
                ['id' => 'DAFTAR_NOMOR', 'title' => 'Kontak CS Cabang'],
                ['id' => 'DAFTAR_LOKASI', 'title' => 'Lokasi Toko Fisik'],
                ['id' => 'CHAT_CS', 'title' => 'Chat dengan CS Pusat']
            ]
        ]
    ];
}