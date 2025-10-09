<?php
header('Content-Type: application/json');
require_once __DIR__ . ("/../../../aa_kon_sett.php");
require_once __DIR__ . '/../../auth/middleware_login.php';

// Memuat Cloudinary SDK dan file .env
use Cloudinary\Cloudinary;
$env = parse_ini_file(__DIR__ . '/../../../.env');

// Pastikan autoloader composer sudah di-require di file koneksi Anda (aa_kon_sett.php) atau di sini
// require_once __DIR__ . '/../../../vendor/autoload.php'; 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Konfigurasi Cloudinary
$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => $env['CLOUDINARY_NAME'],
        'api_key'    => $env['CLOUDINARY_KEY'],
        'api_secret' => $env['CLOUDINARY_SECRET'],
    ],
]);

try {
    // Mengambil data dari POST request
    $date = date('Y-m-d H:i:s');
    $rating = $_POST['rating'] ?? 0;
    $comment = $_POST['comment'] ?? '';
    $token = $_POST['token'] ?? '';
    $id = $_POST['user_id'] ?? 0;
    $tagsJson = $_POST['tags'] ?? '[]';
    $tags = json_decode($tagsJson, true);
    $tagsStr = implode(',', $tags);
    $bon = $_POST['bon'] ?? null;
    $nama_kasir = $_POST['nama_kasir'] ?? '';

    // Validasi token dan input dasar
    if (empty($token) || !verify_token($token)) {
        throw new Exception('Token tidak valid atau sesi telah kedaluwarsa.');
    }
    if ($rating < 1 || $rating > 5) {
        throw new Exception('Rating harus dipilih (1-5 bintang).');
    }
    if (!$bon) {
        throw new Exception('Nomor Bon tidak ditemukan.');
    }

    $sudah_terpecahkan = ($rating <= 3) ? 0 : 1;

    // 1. Masukkan data review utama ke dalam tabel 'review'
    $stmt = $conn->prepare("INSERT INTO review (id_user, rating, komentar, dibuat_tgl, kategori, no_bon, nama_kasir, sudah_terpecahkan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Database error (prepare): " . $conn->error);
    }
    $stmt->bind_param('iisssssi', $id, $rating, $comment, $date, $tagsStr, $bon, $nama_kasir, $sudah_terpecahkan);
    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan data review: " . $stmt->error);
    }
    $review_id = $stmt->insert_id;
    $stmt->close();

    // 2. Proses unggah foto ke Cloudinary jika ada
    $uploaded_urls = [];
    
    // KONDISI UTAMA DIPERBAIKI DI SINI
    if (isset($_FILES['photos'])) {
        foreach ($_FILES['photos']['tmp_name'] as $i => $tmp_name) {
            // VALIDASI DIPINDAHKAN KE DALAM LOOP untuk setiap file
            // Hanya proses file yang punya tmp_name, tidak ada error, dan merupakan file upload yang sah
            if (!empty($tmp_name) && $_FILES['photos']['error'][$i] === 0 && is_uploaded_file($tmp_name)) {
                
                $uploadResult = $cloudinary->uploadApi()->upload($tmp_name, [
                    'folder' => 'review_photos',
                    'transformation' => [
                        ['width' => 1280, 'crop' => 'limit'],
                        ['quality' => 'auto:good']
                    ]
                ]);
                
                $secure_url = $uploadResult['secure_url'];
                $public_id = $uploadResult['public_id'];

                $stmtFoto = $conn->prepare("INSERT INTO review_foto (review_id, nama_file, path_file) VALUES (?, ?, ?)");
                if (!$stmtFoto) {
                    throw new Exception("Database error (prepare foto): " . $conn->error);
                }
                $stmtFoto->bind_param('iss', $review_id, $public_id, $secure_url);
                $stmtFoto->execute();
                $stmtFoto->close();

                $uploaded_urls[] = $secure_url;
            }
        }
    }

    // 3. Kirim respons sukses
    echo json_encode([
        'status' => 'success',
        'message' => 'Terima kasih! Review Anda berhasil dikirim.',
        'review_id' => $review_id,
        'photos_uploaded' => $uploaded_urls
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}