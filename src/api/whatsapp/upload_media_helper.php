<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../utils/Logger.php';
require_once __DIR__ . '/../../config/Config.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
use Cloudinary\Cloudinary;
header('Content-Type: application/json');
$logger = new AppLogger('upload_media_debug.log');
$logger->info("=== Request Upload Dimulai ===");
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Method not allowed. Use POST.");
    }
    if (!isset($_FILES['file'])) {
        throw new Exception("Tidak ada file yang dikirim (key 'file' missing).");
    }
    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Upload Error Code: " . $_FILES['file']['error']);
    }
    $file = $_FILES['file'];
    $logger->info("File diterima: " . $file['name'] . " (" . $file['size'] . " bytes)");
    $env = [];
    try {
        $env = Config::getMultiple(['CLOUDINARY_NAME', 'CLOUDINARY_KEY', 'CLOUDINARY_SECRET']);
    } catch (Exception $e) {
        $env = parse_ini_file(__DIR__ . '/../../../.env');
    }
    if (empty($env['CLOUDINARY_NAME'])) {
        throw new Exception("Konfigurasi Cloudinary tidak ditemukan.");
    }
    $cloudinary = new Cloudinary([
        'cloud' => [
            'cloud_name' => $env['CLOUDINARY_NAME'],
            'api_key' => $env['CLOUDINARY_KEY'],
            'api_secret' => $env['CLOUDINARY_SECRET'],
        ],
    ]);
    $mimeType = mime_content_type($file['tmp_name']);
    $originalName = $file['name'];
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);

    if ($mimeType === 'image/webp' || strtolower($extension) === 'webp') {
        throw new Exception("Format gambar .webp tidak didukung oleh WhatsApp API. Mohon convert ke JPG atau PNG terlebih dahulu.");
    }

    $filenameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
    $resourceType = 'raw';
    if (strpos($mimeType, 'image') !== false) {
        $resourceType = 'image';
    } elseif (strpos($mimeType, 'video') !== false || strpos($mimeType, 'audio') !== false) {
        $resourceType = 'video';
    } else {
        $resourceType = 'raw';
    }
    $logger->info("Tipe File: $mimeType, Resource Type Cloudinary: $resourceType");
    $publicId = $filenameWithoutExt . '_' . time();
    if ($resourceType === 'raw' && !empty($extension)) {
        $publicId .= '.' . $extension;
    }
    $logger->info("Mulai upload ke Cloudinary dengan Public ID: $publicId");
    $uploadResult = $cloudinary->uploadApi()->upload($file['tmp_name'], [
        'folder' => 'whatsapp_bot_media',
        'resource_type' => $resourceType,
        'public_id' => $publicId,
        'use_filename' => true,
        'unique_filename' => false,
        'overwrite' => false
    ]);
    $secureUrl = $uploadResult['secure_url'];
    if ($resourceType === 'raw' && !str_ends_with(strtolower($secureUrl), strtolower($extension))) {
    }
    $logger->success("Upload Berhasil. URL: " . $secureUrl);
    echo json_encode([
        'success' => true,
        'url' => $secureUrl,
        'type' => $resourceType,
        'message' => 'Upload berhasil'
    ]);
} catch (Exception $e) {
    $logger->error("Upload Gagal: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>