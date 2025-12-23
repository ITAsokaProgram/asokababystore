<?php
$dir = realpath(__DIR__ . '/../../../public/images/promo');
$jsonFile = __DIR__ . '/../../../public/slider.json';

if (!is_dir($dir) || !file_exists($jsonFile)) {
    echo json_encode(["success" => false, "error" => "Folder atau slider.json tidak ditemukan"]);
    exit;
}

$data = json_decode(file_get_contents($jsonFile), true) ?: [];
$today = date('Y-m-d');
$filteredData = [];
$deleted = 0;

foreach ($data as $item) {
    if (!empty($item['tanggal_selesai']) && $item['tanggal_selesai'] < $today) {
        $oldFile = $dir . '/' . $item['filename'];
        if (is_file($oldFile)) {
            @unlink($oldFile);
        }
        $deleted++;
        // Lewati entri expired
    } else {
        $filteredData[] = $item;
    }
}

file_put_contents($jsonFile, json_encode($filteredData, JSON_PRETTY_PRINT));
echo json_encode(["success" => true, "deleted" => $deleted]); 