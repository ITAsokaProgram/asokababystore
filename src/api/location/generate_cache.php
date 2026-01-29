<?php
set_time_limit(0); 
header('Content-Type: text/plain');

echo "Sedang memulai proses pengambilan data... Mohon tunggu...\n";
flush(); 

$urlProvinces = "https://wilayah.id/api/provinces.json";
$dataProvinces = @file_get_contents($urlProvinces);

if (!$dataProvinces) {
    die("Gagal mengambil data provinsi. Cek koneksi internet.");
}

$jsonProvinces = json_decode($dataProvinces, true);
$allCities = [];
$count = 0;

if (isset($jsonProvinces['data'])) {
    foreach ($jsonProvinces['data'] as $prov) {
        $provCode = $prov['code'];
        $provName = $prov['name'];
        
        $urlCity = "https://wilayah.id/api/regencies/$provCode.json";
        $dataCity = @file_get_contents($urlCity);

        if ($dataCity) {
            $jsonCity = json_decode($dataCity, true);
            if (isset($jsonCity['data'])) {
                
                $cities = $jsonCity['data'];
                foreach ($cities as &$city) {
                    $city['province_name'] = $provName; 
                }
                
                $allCities = array_merge($allCities, $cities);
                $count += count($cities);
                
                echo "Berhasil mengambil data dari: $provName \n";
                flush();
            }
        }
        
        usleep(100000); 
    }
}

$fileSavePath = 'data_cities_cache.json';
$result = [
    "generated_at" => date("Y-m-d H:i:s"),
    "total" => $count,
    "data" => $allCities
];

if (file_put_contents($fileSavePath, json_encode($result))) {
    echo "\n------------------------------------------------\n";
    echo "SUKSES! Data berhasil disimpan ke '$fileSavePath'.\n";
    echo "Total Kota didapatkan: $count\n";
    echo "Sekarang kamu bisa menggunakan file 'get_all_kota.php'.";
} else {
    echo "Gagal menyimpan file lokal. Cek permission folder.";
}
?>