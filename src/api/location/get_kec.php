<?php

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // Optional, kalau dibutuhkan

if (isset($_GET['kota'])) {
    $kotaKode = $_GET['kota'];
    $url = "https://wilayah.id/api/districts/$kotaKode.json";
    $data = file_get_contents($url);
    $json = json_decode($data, true); // decode jadi array PHP

    echo json_encode([
        "data" => $json['data'] ?? []
    ]);
} else {
    echo json_encode(["data" => []]);
}