<?php

require_once __DIR__ . '/../../utils/Logger.php';
$logger = new AppLogger('put_image.log');

$dir = realpath(__DIR__ . '/../../../public/images/promo');
$urlPrefix = '/public/images/promo/';
$jsonFile = __DIR__ . '/../../../public/slider.json';

$oldData = [];
if (file_exists($jsonFile)) {
    $oldData = json_decode(file_get_contents($jsonFile), true) ?: [];
}

$uploadedFiles = $_FILES['promoImage'];
$promoDateStarts = $_POST['promoDateStart'] ?? [];
$promoDateEnds = $_POST['promoDateEnd'] ?? [];
$promoNames = $_POST['promoName'] ?? [];
$mainPromoDate = $_POST['mainPromoDate'] ?? null;
$mainPromoDateEnd = $_POST['mainPromoDateEnd'] ?? null;
$mainPromoName = $_POST['mainPromoName'] ?? null;

if (!empty($uploadedFiles['name'][0]) && is_dir($dir)) {
    for ($i = 0; $i < count($uploadedFiles['name']); $i++) {
        $originalName = $uploadedFiles['name'][$i];
        $tmpName = $uploadedFiles['tmp_name'][$i];
        
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $baseName = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $baseName); 
        $baseName = trim($baseName);
        
        $uniqueName = $baseName . '_' . uniqid() . '.' . $ext;
        
        $tanggal_mulai = isset($promoDateStarts[$i]) && $promoDateStarts[$i] ? $promoDateStarts[$i] : $mainPromoDate;
        $tanggal_selesai = isset($promoDateEnds[$i]) && $promoDateEnds[$i] ? $promoDateEnds[$i] : $mainPromoDateEnd;

        $promo_name = '';
        if (isset($promoNames[$i]) && strlen(trim($promoNames[$i])) > 0) {
            $promo_name = trim($promoNames[$i]);
        } elseif (!empty($mainPromoName)) {
            $promo_name = trim($mainPromoName);
        }
        $promo_name = preg_replace('/[\x00-\x1F\x7F]/u', '', $promo_name);
        
        $logger->info("Processing file: $originalName. Promo name determined: '$promo_name'");

        $targetPath = $dir . '/' . $uniqueName;
        
        if (move_uploaded_file($tmpName, $targetPath)) {
            chmod($targetPath, 0644);

            $found = false;
            foreach ($oldData as &$item) {
                if ($item['filename'] === $uniqueName || $item['original_name'] === $originalName) {
                    $item['filename'] = $uniqueName;
                    $item['path'] = $urlPrefix . $uniqueName;
                    $item['tanggal_mulai'] = $tanggal_mulai;
                    $item['tanggal_selesai'] = $tanggal_selesai;
                    
                    $item['promo_name'] = $promo_name;
                    
                    $found = true;
                    $logger->info("Updating existing item: $originalName -> $uniqueName");
                    break;
                }
            }
            unset($item);

            if (!$found) {
                $oldData[] = [
                    "filename" => $uniqueName,
                    "original_name" => $originalName,
                    "path" => $urlPrefix . $uniqueName,
                    "tanggal_mulai" => $tanggal_mulai,
                    "tanggal_selesai" => $tanggal_selesai,
                    "promo_name" => $promo_name 
                ];
                $logger->info("Adding new item: $uniqueName with promo: '$promo_name'");
            }
        } else {
             $logger->error("Failed to move uploaded file: $tmpName to $targetPath");
        }
    }

    $today = date('Y-m-d');
    $filteredData = [];
    foreach ($oldData as $item) {
        if (!empty($item['tanggal_selesai']) && $item['tanggal_selesai'] < $today) {
            $oldFile = $dir . '/' . $item['filename'];
            if (is_file($oldFile)) {
                if (@unlink($oldFile)) {
                     $logger->info("Removed expired file: {$item['filename']}");
                } else {
                     $logger->warning("Failed to remove expired file: {$item['filename']}");
                }
            }
        } else {
            $filteredData[] = $item;
        }
    }

    if (file_put_contents($jsonFile, json_encode($filteredData, JSON_PRETTY_PRINT))) {
        chmod($jsonFile, 0664);
        echo json_encode(["success" => true]);
        $logger->success("Successfully updated slider.json.");
    } else {
        echo json_encode(["success" => false, "error" => "Gagal menyimpan JSON"]);
         $logger->error("Failed to write to $jsonFile.");
    }
} else {
    echo json_encode(["success" => false, "error" => "Upload gagal atau folder tidak ditemukan"]);
    $logger->error("Upload failed. Files empty or dir '$dir' not found.");
}