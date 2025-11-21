<?php
header('Content-Type: application/json');
require_once __DIR__ . ("/../../../aa_kon_sett.php");
require_once __DIR__ . '/../../auth/middleware_login.php';

// Memuat Cloudinary SDK dan file .env
use Cloudinary\Cloudinary;

// Pastikan path .env benar relatif terhadap file ini
$env_path = __DIR__ . '/../../../.env';
if (file_exists($env_path)) {
    $env = parse_ini_file($env_path);
} else {
    // Fallback atau error handling jika .env tidak ketemu
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Konfigurasi server bermasalah (.env not found)']);
    exit;
}

// require_once __DIR__ . '/../../../vendor/autoload.php'; // Uncomment jika perlu autoloader manual

ini_set('display_errors', 0); // Matikan display error agar tidak merusak JSON
error_reporting(E_ALL);

// Konfigurasi Cloudinary
try {
    $cloudinary = new Cloudinary([
        'cloud' => [
            'cloud_name' => $env['CLOUDINARY_NAME'],
            'api_key'    => $env['CLOUDINARY_KEY'],
            'api_secret' => $env['CLOUDINARY_SECRET'],
        ],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal inisialisasi Cloudinary']);
    exit;
}

try {
    // Mengambil data dari POST request
    $date = date('Y-m-d H:i:s');
    $rating = $_POST['rating'] ?? 0;
    $comment = $_POST['comment'] ?? '';
    $token = $_POST['token'] ?? '';
    $id = $_POST['user_id'] ?? 0;
    $tagsJson = $_POST['tags'] ?? '[]';
    
    // Decode tags dengan aman
    $tags = json_decode($tagsJson, true);
    if (!is_array($tags)) $tags = [];
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
    
    // Cek apakah ada file yang dikirim
    if (isset($_FILES['photos']) && is_array($_FILES['photos']['tmp_name'])) {
        
        $files = $_FILES['photos'];
        $count = count($files['tmp_name']);

        for ($i = 0; $i < $count; $i++) {
            $tmp_name = $files['tmp_name'][$i];
            $error = $files['error'][$i];
            $name = $files['name'][$i];

            // Validasi file: tidak kosong, tidak ada error, dan benar file upload
            if (!empty($tmp_name) && $error === UPLOAD_ERR_OK && is_uploaded_file($tmp_name)) {
                
                try {
                    $uploadResult = $cloudinary->uploadApi()->upload($tmp_name, [
                        'folder' => 'review_photos',
                        'transformation' => [
                            ['width' => 1280, 'crop' => 'limit'],
                            ['quality' => 'auto:good']
                        ]
                    ]);
                    
                    $secure_url = $uploadResult['secure_url'];
                    $public_id = $uploadResult['public_id'];

                    // Insert ke tabel review_foto
                    $stmtFoto = $conn->prepare("INSERT INTO review_foto (review_id, nama_file, path_file) VALUES (?, ?, ?)");
                    if ($stmtFoto) {
                        $stmtFoto->bind_param('iss', $review_id, $public_id, $secure_url);
                        if ($stmtFoto->execute()) {
                            $uploaded_urls[] = $secure_url;
                        }
                        $stmtFoto->close();
                    }
                } catch (Exception $e_img) {
                    // Log error gambar tapi jangan hentikan proses review utama
                    error_log("Gagal upload gambar: " . $e_img->getMessage());
                }
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