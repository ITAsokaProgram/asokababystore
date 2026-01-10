<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

if (isset($_GET['kota'])) {
    // FIX: Encode parameter
    $kotaKode = urlencode($_GET['kota']);

    $url = "https://wilayah.id/api/districts/$kotaKode.json";

    $data = @file_get_contents($url);

    if ($data === false) {
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