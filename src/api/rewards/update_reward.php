<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
require_once __DIR__ . "/../../../vendor/autoload.php";

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
date_default_timezone_set('Asia/Jakarta');
$env = parse_ini_file(__DIR__ . '/../../../config.env');
// ================== Config Cloudinary ==================
Configuration::instance([
    'cloud' => [
        'cloud_name' => $env['CLOUDINARY_NAME'],
        'api_key'    => $env['CLOUDINARY_KEY'],
        'api_secret' => $env['CLOUDINARY_SECRET']
    ],
    'url' => ['secure' => true]
]);

// ================== Validasi Method ==================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Gunakan metode POST.']);
    exit;
}

// ================== Validasi Token ==================
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
if (!$authHeader) {
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Tidak ada Authorization header']);
    exit;
}
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1];
}
if (!$token || !verify_token($token)) {
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Token tidak valid']);
    exit;
}

// ================== Ambil Data ==================
$id            = $_POST['id'] ?? 0;
$nama_hadiah   = trim($_POST['nama_hadiah'] ?? '');
$poin          = trim($_POST['poin'] ?? 0);
$tanggal_diubah = date("Y-m-d H:i:s");

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit;
}

// ================== Ambil Data Lama ==================
$stmt = $conn->prepare("SELECT file_id FROM hadiah WHERE id_hadiah = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$old = $res->fetch_assoc();
$oldfileId = $old['file_id'] ?? null;
$stmt->close();

// ================== Handle File Upload ==================
$newImageUrl = null;
$newfileId = null;
$uniqId = uniqid(); // contoh: 68abd40086c91
$customPublicId = "rewards/reward_" . $uniqId;
if (isset($_FILES['img']) && $_FILES['img']['tmp_name']) {
    try {
        // 1. Hapus gambar lama kalau ada
        if ($oldfileId) {
            (new UploadApi())->destroy($oldfileId);
        }

        // 2. Upload gambar baru ke folder rewards
        $uploadResult = (new UploadApi())->upload($_FILES['img']['tmp_name'], [
            "folder" => "rewards",
            "public_id" => $customPublicId,
            "resource_type" => "image"
        ]);

        $newImageUrl = $uploadResult['secure_url'];   // URL gambar baru
        $newfileId = $uploadResult['public_id'];    // file_id baru
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Upload gagal: ' . $e->getMessage()]);
        exit;
    }
}

// ================== Update Database ==================
if ($newImageUrl && $newfileId) {
    // Update semua termasuk image
    $sql = "UPDATE hadiah 
            SET nama_hadiah=?, tanggal_diubah=?, poin=?, image_url=?, file_id=? 
            WHERE id_hadiah=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssissi", $nama_hadiah, $tanggal_diubah, $poin, $newImageUrl, $newfileId, $id);
} else {
    // Tidak ada upload baru → update teks & poin saja
    $sql = "UPDATE hadiah 
            SET nama_hadiah=?, tanggal_diubah=?, poin=? 
            WHERE id_hadiah=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $nama_hadiah, $tanggal_diubah, $poin, $id);
}

$stmt->execute();

// ================== Response ==================
if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Data berhasil diubah']);
} else {
    echo json_encode(['success' => false, 'message' => 'Tidak ada perubahan data']);
}

$stmt->close();
$conn->close();
