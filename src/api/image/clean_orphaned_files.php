<?php
header("Content-Type: application/json");

$dir = realpath(__DIR__ . '/../../../public/images/promo');
$jsonFile = __DIR__ . '/../../../public/slider.json';

if (!is_dir($dir) || !file_exists($jsonFile)) {
    echo json_encode(["success" => false, "error" => "Folder atau slider.json tidak ditemukan"]);
    exit;
}

// Ambil data dari JSON
$data = json_decode(file_get_contents($jsonFile), true) ?: [];
$jsonFilenames = array_column($data, 'filename');

// Ambil semua file di folder
$folderFiles = [];
if ($handle = opendir($dir)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != ".." && is_file($dir . '/' . $entry)) {
            $folderFiles[] = $entry;
        }
    }
    closedir($handle);
}

// Cari file yang ada di folder tapi tidak ada di JSON (orphaned files)
$orphanedFiles = array_diff($folderFiles, $jsonFilenames);
$deletedCount = 0;
$deletedFiles = [];

foreach ($orphanedFiles as $orphanedFile) {
    $filePath = $dir . '/' . $orphanedFile;
    if (unlink($filePath)) {
        $deletedCount++;
        $deletedFiles[] = $orphanedFile;
    }
}

echo json_encode([
    "success" => true,
    "message" => "Pembersihan selesai",
    "total_files_in_folder" => count($folderFiles),
    "total_files_in_json" => count($jsonFilenames),
    "orphaned_files_found" => count($orphanedFiles),
    "deleted_files" => $deletedCount,
    "deleted_file_list" => $deletedFiles
]); 