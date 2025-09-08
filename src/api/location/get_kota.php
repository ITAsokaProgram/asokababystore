<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // Optional, kalau dibutuhkan

if (isset($_GET['provinsi'])) {
    $provinsiCode = $_GET['provinsi'];
    $url = "https://wilayah.id/api/regencies/$provinsiCode.json";
    $data = file_get_contents($url);
    $json = json_decode($data, true); // decode jadi array PHP

    echo json_encode([
        "data" => $json['data'] ?? []
    ]);
} else {
    echo json_encode(["data" => []]);
}
?>