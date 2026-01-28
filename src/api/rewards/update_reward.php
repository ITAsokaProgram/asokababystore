<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
require_once __DIR__ . "/../../../vendor/autoload.php";

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
date_default_timezone_set('Asia/Jakarta');
$env = parse_ini_file(__DIR__ . '/../../../.env');
// ================== Config Cloudinary ==================
Configuration::instance([
    'cloud' => [
        'cloud_name' => $env['CLOUDINARY_NAME'],
        'api_key' => $env['CLOUDINARY_KEY'],
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

$verif = authenticate_request();

$id = $_POST['id'] ?? 0;
$nama_hadiah = trim($_POST['nama_hadiah'] ?? '');
$tanggal_diubah = date("Y-m-d H:i:s");

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit;
}

// ================== Ambil Data Lama ==================
$stmt = $conn->prepare("SELECT nama_hadiah, poin, image_url, file_id FROM hadiah WHERE id_hadiah = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$old = $res->fetch_assoc();
$oldNama = $old['nama_hadiah'] ?? '';
$oldPoin = $old['poin'] ?? 0;
$oldImage = $old['image_url'] ?? '';
$oldfileId = $old['file_id'] ?? null;
$stmt->close();

// ================== Update Database ==================
$sql = "UPDATE hadiah 
        SET nama_hadiah=?, tanggal_diubah=? 
        WHERE id_hadiah=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $nama_hadiah, $tanggal_diubah, $id);

$executed = null;
// determine which fields will change (compare posted vs existing)
$changes = [];
if ($nama_hadiah !== $oldNama) $changes[] = 'nama_hadiah';

$executed = $stmt->execute();

if ($executed && $stmt->affected_rows > 0) {
    // insert into log_hadiah
    try {
    // use previously validated token
    $verif = verify_token($token);
    $id_user = $verif->id ?? $verif->kode ?? null;
    $logTime = date('Y-m-d H:i:s');
    $logActivity = 'UPDATE';
    if (!empty($changes)) $logActivity .= ' (' . implode(',', $changes) . ')';
        $logStmt = $conn->prepare("INSERT INTO log_hadiah (id_hadiah, id_user, log_activity, created_at) VALUES (?, NULLIF(?, ''), ?, ?)");
        if (!$logStmt) throw new Exception('prepare failed: ' . $conn->error);
        $idUserVal = $id_user === null ? '' : (string)$id_user;
    // ensure id_hadiah is an integer variable for bind_param
    $idInt = (int)$id;
    $logStmt->bind_param('isss', $idInt, $idUserVal, $logActivity, $logTime);
        if (!$logStmt->execute()) {
            $err = $logStmt->error ?: $conn->error;
            $logStmt->close();
            throw new Exception('execute failed: ' . $err);
        }
        $logStmt->close();
    } catch (Exception $e) {
        error_log('Failed to insert log_hadiah on update: ' . $e->getMessage());
    }

    echo json_encode(['success' => true, 'message' => 'Data berhasil diubah']);
} else {
    echo json_encode(['success' => false, 'message' => 'Tidak ada perubahan data']);
}

$stmt->close();
$conn->close();
