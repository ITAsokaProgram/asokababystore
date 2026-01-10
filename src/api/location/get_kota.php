<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

if (isset($_GET['provinsi'])) {
    // FIX: Encode parameter agar spasi menjadi valid (contoh: Jawa Barat -> Jawa+Barat)
    $provinsiCode = urlencode($_GET['provinsi']);

    $url = "https://wilayah.id/api/regencies/$provinsiCode.json";

    // Gunakan @ untuk suppress warning jika gagal, lalu handle logic di bawahnya
    $data = @file_get_contents($url);

    if ($data === false) {
        // Jika gagal fetch (misal 404 atau 400), kembalikan array kosong atau error
        echo json_encode(["data" => []]);
    } else {
        $json = json_decode($data, true);
        echo json_encode([
            "data" => $json['data'] ?? []
        ]);
    }
} else {
    echo json_encode(["data" => []]);
}
?>