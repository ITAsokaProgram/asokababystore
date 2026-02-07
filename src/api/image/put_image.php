<?php
require_once __DIR__ . '/../../utils/Logger.php';
$logger = new AppLogger('put_image.log');
$dir = realpath(__DIR__ . '/../../../public/images/promo');
$urlPrefix = '/public/images/promo/';
$jsonFile = __DIR__ . '/../../../public/slider.json';
if (!$dir || !is_dir($dir)) {
    if (!mkdir($concurrentDirectory = __DIR__ . '/../../../public/images/promo', 0755, true) && !is_dir($concurrentDirectory)) {
        echo json_encode(["success" => false, "error" => "Direktori penyimpanan gagal dibuat."]);
        exit;
    }
    $dir = realpath($concurrentDirectory);
}
$oldData = [];
if (file_exists($jsonFile)) {
    $content = file_get_contents($jsonFile);
    $oldData = json_decode($content, true) ?: [];
}
$uploadedFiles = $_FILES['promoImage'] ?? [];
$promoDateStarts = $_POST['promoDateStart'] ?? [];
$promoDateEnds = $_POST['promoDateEnd'] ?? [];
$promoNames = $_POST['promoName'] ?? [];
if (!empty($uploadedFiles['name'][0])) {
    $totalFiles = count($uploadedFiles['name']);
    for ($i = 0; $i < $totalFiles; $i++) {
        if ($uploadedFiles['error'][$i] !== UPLOAD_ERR_OK) continue;
        $originalName = $uploadedFiles['name'][$i];
        $tmpName = $uploadedFiles['tmp_name'][$i];
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $baseName = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $baseName); 
        $baseName = trim($baseName);
        $uniqueName = $baseName . '_' . uniqid() . '.' . $ext;
        $tanggal_mulai = $promoDateStarts[$i] ?? date('Y-m-d'); 
        $tanggal_selesai = $promoDateEnds[$i] ?? date('Y-m-d', strtotime('+7 days'));
        $promo_name = '';
        if (isset($promoNames[$i])) {
            $promo_name = trim($promoNames[$i]);
        }
        $promo_name = preg_replace('/[\x00-\x1F\x7F]/u', '', $promo_name);
        $targetPath = $dir . '/' . $uniqueName;
        if (move_uploaded_file($tmpName, $targetPath)) {
            chmod($targetPath, 0644);
            $found = false;
            foreach ($oldData as &$item) {
                $existingOriginal = isset($item['original_name']) ? $item['original_name'] : '';
                if ($item['filename'] === $uniqueName || $existingOriginal === $originalName) {
                    $item['filename'] = $uniqueName;
                    $item['path'] = $urlPrefix . $uniqueName;
                    $item['tanggal_mulai'] = $tanggal_mulai;
                    $item['tanggal_selesai'] = $tanggal_selesai;
                    $item['promo_name'] = $promo_name;
                    $item['original_name'] = $originalName;
                    $found = true;
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
            }
        }
    }
    $today = date('Y-m-d');
    $filteredData = [];
    foreach ($oldData as $item) {
        if (!empty($item['tanggal_selesai']) && $item['tanggal_selesai'] < $today) {
            $oldFile = $dir . '/' . $item['filename'];
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        } else {
            $filteredData[] = $item;
        }
    }
    if (file_put_contents($jsonFile, json_encode($filteredData, JSON_PRETTY_PRINT))) {
        chmod($jsonFile, 0664);
        echo json_encode(["success" => true]);
    } else {
        error_log("Gagal menulis ke $jsonFile");
        echo json_encode(["success" => false, "error" => "Gagal menyimpan data (Permission Denied)"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Tidak ada file yang dipilih"]);
}