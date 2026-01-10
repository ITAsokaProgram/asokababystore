<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

if (isset($_GET['kecamatan'])) {
    // FIX: Encode parameter
    $kecKode = urlencode($_GET['kecamatan']);

    $url = "https://wilayah.id/api/villages/$kecKode.json";

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