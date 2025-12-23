<?php
require_once __DIR__ . ("/../../../aa_kon_sett.php");
require_once __DIR__ . ("/../../auth/middleware_login.php");
require_once __DIR__ . '/../../../vendor/autoload.php'; 

use Cloudinary\Cloudinary;
$env = parse_ini_file(__DIR__ . '/../../../.env');

header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST");

set_exception_handler(function($exception) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan internal pada server.',
        'error_detail' => $exception->getMessage()
    ]);
    exit;
});

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode yang diizinkan hanya POST']);
    exit;
}

try {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token tidak ditemukan']);
        exit;
    }
    
    $token = $matches[1];
    $verif = verify_token($token);
    
    if (!$verif) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token tidak valid']);
        exit;
    }

    $admin_id = $verif->id ?? $verif->kode ?? null;
    if (!$admin_id) {
         http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'ID Admin tidak ditemukan di token.']);
        exit;
    }

    $review_id = $_POST['review_id'] ?? null;
    $pesan = trim($_POST['pesan'] ?? '');
    $media = $_FILES['media'] ?? null;

    $tipe_pesan = 'text';
    $media_url = null;

    if (empty($pesan) && (empty($media) || $media['error'] !== UPLOAD_ERR_OK)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Pesan atau gambar wajib diisi']);
        exit;
    }

    if (!empty($media) && $media['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($media['tmp_name']);
        
        if (!in_array($file_type, $allowed_types)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Hanya file gambar (JPG, PNG, GIF, WEBP) yang diizinkan.']);
            exit;
        }

        if ($media['size'] > 5 * 1024 * 1024) { // 5 MB
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ukuran gambar maksimal adalah 5MB.']);
            exit;
        }

        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $env['CLOUDINARY_NAME'],
                'api_key'    => $env['CLOUDINARY_KEY'],
                'api_secret' => $env['CLOUDINARY_SECRET'],
            ],
        ]);

        $uploadResult = $cloudinary->uploadApi()->upload($media['tmp_name'], [
            'folder' => 'review_chat',
            'resource_type' => 'image' 
        ]);
        
        $media_url = $uploadResult['secure_url'];
        $tipe_pesan = 'image';
    }

    // Perhatikan: 'admin'
    $sql = "INSERT INTO review_conversation (review_id, pengirim_type, pengirim_id, pesan, tipe_pesan, media_url) 
            VALUES (?, 'admin', ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Prepare Gagal: " . $conn->error);

    $stmt->bind_param("issss", $review_id, $admin_id, $pesan, $tipe_pesan, $media_url);
    
    if (!$stmt->execute()) throw new Exception("Execute Gagal: " . $stmt->error);

    http_response_code(201);
    echo json_encode([
        'success' => true, 
        'message' => 'Pesan berhasil dikirim',
        'data' => [
            'review_id' => $review_id,
            'pesan' => $pesan,
            'tipe_pesan' => $tipe_pesan,
            'media_url' => $media_url
        ]
    ]);

} catch (Exception $e) {
    throw $e;
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>