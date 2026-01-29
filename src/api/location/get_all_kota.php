<?php

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$cacheFile = 'data_cities_cache.json';

if (file_exists($cacheFile)) {
    readfile($cacheFile);
} else {
    http_response_code(404);
    echo json_encode([
        "status" => "error",
        "message" => "Data belum tersedia. Harap jalankan 'generate_cache.php' terlebih dahulu di server."
    ]);
}
?>