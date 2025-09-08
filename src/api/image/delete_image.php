<?php
header("Content-Type:application/json");
$dataFile = realpath(__DIR__ . '/../../../public/slider.json');
$promoFolder = realpath(__DIR__ . '/../../../public/images/promo') . '/';

$input = json_decode(file_get_contents('php://input'), true);
$filename = $input['filename'] ?? '';

if (!$dataFile) {
    echo json_encode(["success" => false, "error" => "slider.json not found"]);
    exit;
}

if ($filename) {
    // Ambil data JSON
    $data = json_decode(file_get_contents($dataFile), true);
    if (!$data) {
        echo json_encode(["success" => false, "error" => "Gagal membaca JSON"]);
        exit;
    }

    // Cari file yang akan dihapus
    $fileToDelete = null;
    $imageArray = [];
    
    foreach ($data as $item) {
        if ($item['filename'] === $filename) {
            $fileToDelete = $item;
        } else {
            $imageArray[] = $item;
        }
    }

    if (!$fileToDelete) {
        echo json_encode(["success" => false, "error" => "File tidak ditemukan dalam JSON"]);
        exit;
    }

    // Hapus file gambar fisiknya terlebih dahulu
    $filePath = $promoFolder . $filename;
    $fileDeleted = false;
    
    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            $fileDeleted = true;
        } else {
            echo json_encode([
                "success" => false,
                "error" => "Gagal menghapus file fisik",
                "path" => $filePath,
                "writable" => is_writable($filePath),
                "exists" => file_exists($filePath)
            ]);
            exit;
        }
    } else {
        // File tidak ada di folder, tapi tetap hapus dari JSON
        $fileDeleted = true;
    }

    // Simpan ulang JSON tanpa file yang dihapus
    $result = file_put_contents($dataFile, json_encode(array_values($imageArray), JSON_PRETTY_PRINT));
    
    if ($result === false) {
        echo json_encode([
            "success" => false,
            "error" => "Gagal menyimpan JSON",
            "path" => $dataFile,
            "writable" => is_writable($dataFile),
            "dir_writable" => is_writable(dirname($dataFile))
        ]);
        exit;
    }

    // Set permission yang benar untuk JSON
    chmod($dataFile, 0664);

    echo json_encode([
        "success" => true, 
        "message" => "File berhasil dihapus",
        "file_deleted" => $fileDeleted,
        "filename" => $filename
    ]);
} else {
    echo json_encode(["success" => false, "error" => "No filename provided"]);
}
