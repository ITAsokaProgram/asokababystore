<?php

$dir = realpath(__DIR__ . '/../../../public/images/promo');
$urlPrefix = '/public/images/promo/';
$jsonFile = __DIR__ . '/../../../public/slider.json';

// Ambil data lama
$oldData = [];
if (file_exists($jsonFile)) {
    $oldData = json_decode(file_get_contents($jsonFile), true) ?: [];
}

$uploadedFiles = $_FILES['promoImage'];
$promoDateStarts = $_POST['promoDateStart'] ?? [];
$promoDateEnds = $_POST['promoDateEnd'] ?? [];
$mainPromoDate = $_POST['mainPromoDate'] ?? null;

if (!empty($uploadedFiles['name'][0]) && is_dir($dir)) {
    for ($i = 0; $i < count($uploadedFiles['name']); $i++) {
        $originalName = $uploadedFiles['name'][$i];
        $tmpName = $uploadedFiles['tmp_name'][$i];
        
        // Sanitasi nama file dan buat nama unik
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $baseName = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $baseName); // Hapus karakter khusus
        $baseName = trim($baseName);
        
        // Buat nama file unik dengan timestamp
        $uniqueName = $baseName . '_' . uniqid() . '.' . $ext;
        
        $tanggal_mulai = isset($promoDateStarts[$i]) && $promoDateStarts[$i] ? $promoDateStarts[$i] : $mainPromoDate;
        $tanggal_selesai = isset($promoDateEnds[$i]) && $promoDateEnds[$i] ? $promoDateEnds[$i] : $mainPromoDate;
        
        $targetPath = $dir . '/' . $uniqueName;
        
        // Upload file
        if (move_uploaded_file($tmpName, $targetPath)) {
            // Set permission yang benar
            chmod($targetPath, 0644);

            // Cek apakah file sudah ada di data lama (berdasarkan nama asli)
        $found = false;
        foreach ($oldData as &$item) {
                if ($item['filename'] === $uniqueName || $item['original_name'] === $originalName) {
                    $item['filename'] = $uniqueName;
                    $item['path'] = $urlPrefix . $uniqueName;
                $item['tanggal_mulai'] = $tanggal_mulai;
                $item['tanggal_selesai'] = $tanggal_selesai;
                $found = true;
                break;
            }
        }
        unset($item);

            // Jika belum ada, tambahkan entri baru
        if (!$found) {
            $oldData[] = [
                    "filename" => $uniqueName,
                    "original_name" => $originalName,
                    "path" => $urlPrefix . $uniqueName,
                "tanggal_mulai" => $tanggal_mulai,
                "tanggal_selesai" => $tanggal_selesai
            ];
            }
        }
    }

    // Hapus otomatis banner yang sudah lewat tanggal selesai penayangan
    $today = date('Y-m-d');
    $filteredData = [];
    foreach ($oldData as $item) {
        if (!empty($item['tanggal_selesai']) && $item['tanggal_selesai'] < $today) {
            // Hapus file fisik
            $oldFile = $dir . '/' . $item['filename'];
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
            // Lewati entri ini (tidak dimasukkan ke $filteredData)
        } else {
            $filteredData[] = $item;
        }
    }

    // Simpan JSON dengan permission yang benar
    if (file_put_contents($jsonFile, json_encode($filteredData, JSON_PRETTY_PRINT))) {
        chmod($jsonFile, 0664);
    echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Gagal menyimpan JSON"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Upload gagal atau folder tidak ditemukan"]);
}