<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Optional, kalau dibutuhkan

$url = "https://wilayah.id/api/provinces.json";

$response = file_get_contents($url);

if ($response !== false) {
    echo $response;
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal mengambil data provinsi"
    ]);
}