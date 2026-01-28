<?php
header('Content-Type: application/json');
require_once 'vendor/autoload.php';
require_once 'aa_kon_sett.php';
use Cloudinary\Cloudinary;
date_default_timezone_set('Asia/Jakarta');
$env_path = __DIR__ . '/.env';
if (file_exists($env_path)) {
    $env = parse_ini_file($env_path);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Konfigurasi server bermasalah (.env not found)']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Gunakan metode POST.']);
    exit;
}
if (empty($_POST['kd_cust'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nomor HP wajib diisi.']);
    exit;
}
$noHp = trim($_POST['kd_cust']);
$uploadedAt = date('Y-m-d H:i:s');
if (
    (!isset($_FILES['file_upload']) || !is_array($_FILES['file_upload']['tmp_name']))
    && (!isset($_FILES['add_file']) || !is_array($_FILES['add_file']['tmp_name']))
) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tidak ada file yang dikirim.']);
    exit;
}
try {
    $cloudinary = new Cloudinary([
        'cloud' => [
            'cloud_name' => $env['CLOUDINARY_NAME'],
            'api_key' => $env['CLOUDINARY_KEY'],
            'api_secret' => $env['CLOUDINARY_SECRET'],
        ],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal inisialisasi Cloudinary']);
    exit;
}
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/jpg'];
$maxSize = 5 * 1024 * 1024;
$results = [];
function processUploadFiles($fileArray, $conn, $cloudinary, $noHp, $uploadedAt, &$results, $allowedMimeTypes, $maxSize)
{
    foreach ($fileArray['tmp_name'] as $index => $tmpPath) {
        if (empty($tmpPath) || $fileArray['error'][$index] !== UPLOAD_ERR_OK || !is_uploaded_file($tmpPath)) {
            continue;
        }
        $fileName = basename($fileArray['name'][$index]);
        $fileSize = $fileArray['size'][$index];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileMime = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);
        if (!in_array($fileMime, $allowedMimeTypes) || $fileSize > $maxSize) {
            continue;
        }
        $stmtCheck = $conn->prepare("SELECT 1 FROM uploads WHERE kd_cust = ? AND file_name = ?");
        $stmtCheck->bind_param("ss", $noHp, $fileName);
        $stmtCheck->execute();
        $stmtCheck->store_result();
        if ($stmtCheck->num_rows > 0) {
            $stmtCheck->close();
            $results[] = [
                'file_name' => $fileName,
                'status' => 'duplikat',
                'message' => 'File sudah pernah diupload.'
            ];
            continue;
        }
        $stmtCheck->close();
        try {
            $uploadResult = $cloudinary->uploadApi()->upload($tmpPath, [
                'folder' => 'customer_uploads/' . $noHp,
                'transformation' => [
                    ['quality' => 'auto:good']
                ]
            ]);
            $secure_url = $uploadResult['secure_url'];
            $public_id = $uploadResult['public_id'];
            $stmt = $conn->prepare("INSERT INTO uploads (kd_cust, file_name, file_id, file_link, uploaded_at, status) VALUES (?, ?, ?, ?, ?, 'checked')");
            $stmt->bind_param("sssss", $noHp, $fileName, $public_id, $secure_url, $uploadedAt);
            if ($stmt->execute()) {
                $results[] = [
                    'file_id' => $public_id,
                    'file_link' => $secure_url,
                    'file_name' => $fileName,
                    'link' => $secure_url
                ];
            }
            $stmt->close();
        } catch (Exception $e) {
            continue;
        }
    }
}
if (isset($_FILES['file_upload']) && is_array($_FILES['file_upload']['tmp_name'])) {
    processUploadFiles($_FILES['file_upload'], $conn, $cloudinary, $noHp, $uploadedAt, $results, $allowedMimeTypes, $maxSize);
}
if (isset($_FILES['add_file']) && is_array($_FILES['add_file']['tmp_name'])) {
    processUploadFiles($_FILES['add_file'], $conn, $cloudinary, $noHp, $uploadedAt, $results, $allowedMimeTypes, $maxSize);
}
if (empty($results)) {
    http_response_code(415);
    echo json_encode(['success' => false, 'message' => 'Tidak ada file valid yang berhasil diupload atau terjadi error server.']);
} else {
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => count($results) . ' file berhasil diupload ke Cloudinary.',
        'data' => $results
    ]);
}
?>