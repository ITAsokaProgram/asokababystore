<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: POST");
date_default_timezone_set('Asia/Jakarta');

use Cloudinary\Cloudinary;
use Cloudinary\Transformation\Resize;
use Cloudinary\Transformation\Gravity;
use Cloudinary\Transformation\FocusOn;
use Cloudinary\Transformation\Quality;

$env = parse_ini_file(__DIR__ . '/../../../config.env');




if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Gunakan metode POST.']);
    exit;
}
$headers = getallheaders();
$authHeader = $headers['Authorization'];
if (!$authHeader) {
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak user tidak terdaftar']);
    exit;
}
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1]; // ini yang aman dan baku
}
if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak user tidak terdaftar']);
    exit;
}
$verif = verify_token($token);

// Validate required fields
$requiredFields = ['nama_hadiah', 'plu', 'poin_dibutuhkan', 'qty_hadiah', 'cabang'];
$missingFields = [];

foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Field berikut harus diisi: ' . implode(', ', $missingFields)
    ]);
    exit;
}

// Sanitize and validate data
$nama_hadiah = trim($_POST['nama_hadiah']);
$plu = trim($_POST['plu']);
$poin_dibutuhkan = (int) $_POST['poin_dibutuhkan'];
$qty_hadiah = (int) $_POST['qty_hadiah'];
$cabang = $_POST['cabang'];
$createdAt = date('Y-m-d H:i:s');
// Validate numeric values
if ($poin_dibutuhkan <= 0 || $qty_hadiah <= 0 || $cabang <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Poin, qty, dan cabang harus berupa angka positif'
    ]);
    exit;
}

// Validate image file
if (!isset($_FILES['gambar_hadiah']) || $_FILES['gambar_hadiah']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File gambar harus diupload.']);
    exit;
}

$allowedMimeTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
$maxSize = 5 * 1024 * 1024; // 5MB

// Validate file type
$fileMime = mime_content_type($_FILES['gambar_hadiah']['tmp_name']);
if (!array_key_exists($fileMime, $allowedMimeTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tipe file tidak didukung. Gunakan format JPG atau PNG.']);
    exit;
}

// Validate file size
if ($_FILES['gambar_hadiah']['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 5MB.']);
    exit;
}

try {
    // Configure Cloudinary
    $cloudinary = new Cloudinary([
        'cloud' => [
            'cloud_name' => $env['CLOUDINARY_NAME'],
            'api_key' => $env['CLOUDINARY_KEY'],
            'api_secret' => $env['CLOUDINARY_SECRET']
        ]
    ]);

    // Start MySQLi transaction
    $conn->autocommit(FALSE);
    $conn->begin_transaction();

    try {
        // Generate a unique filename
        $fileExtension = $allowedMimeTypes[$fileMime];
        $uniqueFilename = 'reward_' . uniqid() . '.' . $fileExtension;

        // Upload to Cloudinary with optimization
        $uploadResult = $cloudinary->uploadApi()->upload(
            $_FILES['gambar_hadiah']['tmp_name'],
            [
                'public_id' => $uniqueFilename,
                'folder' => 'rewards',
                'quality' => 'auto',
                'fetch_format' => 'auto'
            ]
        );

        // Prepare upload result in the format expected by the rest of the code
        $uploadResult = [
            'file_id' => $uploadResult['public_id'],
            'public_url' => $uploadResult['secure_url']
        ];
        // Insert reward data
        $stmt = $conn->prepare(
            "INSERT INTO hadiah (nama_hadiah, kode_karyawan, nama_karyawan, plu, poin, qty, kd_store, file_id, image_url, tanggal_dibuat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "sisiiissss",
            $nama_hadiah,
            $verif->kode,
            $verif->nama,
            $plu,
            $poin_dibutuhkan,
            $qty_hadiah,
            $cabang,
            $uploadResult['file_id'],
            $uploadResult['public_url'],
            $createdAt
        );
        $stmt->execute();
        $rewardId = $conn->insert_id;
        $stmt->close();

        // insert activity log into log_hadiah
        try {
            $id_user = $verif->id ?? $verif->kode ?? '';
            $logTime = date('Y-m-d H:i:s');
            $logActivity = 'CREATE HADIAH ';
            // use NULLIF to allow empty id_user -> NULL (avoids empty-string FK issues)
            $logStmt = $conn->prepare("INSERT INTO log_hadiah (id_hadiah, id_user, log_activity, created_at) VALUES (?, NULLIF(?, ''), ?, ?)");
            if (!$logStmt) throw new Exception('prepare failed: ' . $conn->error);
            $idUserVal = $id_user === null ? '' : (string)$id_user;
            $logStmt->bind_param('isss', $rewardId, $idUserVal, $logActivity, $logTime);
            if (!$logStmt->execute()) {
                $err = $logStmt->error ?: $conn->error;
                $logStmt->close();
                throw new Exception('execute failed: ' . $err);
            }
            $logStmt->close();
        } catch (Exception $e) {
            // rollback and report
            error_log('Failed to insert log_hadiah: ' . $e->getMessage());
            $conn->rollback();
            $conn->autocommit(TRUE);
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan log aktivitas: ' . $e->getMessage()]);
            exit;
        }

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Data berhasil disimpan',
        ]);
        // If we get here, everything was successful
        $conn->commit();
    } catch (Exception $e) {
        // Something went wrong, rollback
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat mengupload dan simpan data: ' . $e->getMessage()]);
        exit;
    } finally {
        // Always turn auto-commit back on
        $conn->autocommit(TRUE);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat mengupload file: ' . $e->getMessage()]);
    exit;
}

$conn->close();