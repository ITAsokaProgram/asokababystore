<?php
header('Content-Type: application/json');
include "/var/www/asokababystore.com/aa_kon_sett.php";
require_once __DIR__ . '/../../auth/middleware_login.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$date = date('Y-m-d H:i:s');
$rating = $_POST['rating'] ?? 0;
$comment = $_POST['comment'] ?? '';
$token = $_POST['token'] ?? '';
$id = $_POST['user_id'] ?? 0;
$tagsJson = $_POST['tags'] ?? '[]';
$tags = json_decode($tagsJson, true);
$tagsStr = implode(',', $tags);
$bon = $_POST['bon'];
$verify_token = verify_token($token);
if (!$verify_token) {
    echo json_encode(['status' => 'error', 'message' => 'Token tidak valid.']);
    exit;
}

// Validasi token
if (empty($token)) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi tidak valid. Silakan masuk kembali.']);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['status' => 'error', 'message' => 'Rating Harus Di Klik']);
    exit;
}
// resize
function resizeImage($file, $destination, $maxDim = 1280, $quality = 80)
{
    list($width, $height, $type) = getimagesize($file);

    if ($width > $maxDim || $height > $maxDim) {
        $ratio = $width / $height;
        if ($ratio > 1) {
            $newWidth = $maxDim;
            $newHeight = $maxDim / $ratio;
        } else {
            $newHeight = $maxDim;
            $newWidth = $maxDim * $ratio;
        }

        if ($type == IMAGETYPE_JPEG) {
            $src = imagecreatefromjpeg($file);
        } elseif ($type == IMAGETYPE_PNG) {
            $src = imagecreatefrompng($file);
        } else {
            return false; // unsupported type
        }

        $dst = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        if ($type == IMAGETYPE_JPEG) {
            imagejpeg($dst, $destination, $quality);
        } elseif ($type == IMAGETYPE_PNG) {
            imagepng($dst, $destination, 6);
        }

        imagedestroy($src);
        imagedestroy($dst);
    } else {
        move_uploaded_file($file, $destination);
    }
}
// Simpan review
$stmt = $conn->prepare("INSERT INTO review (id_user,rating, komentar, dibuat_tgl, kategori, no_bon) VALUES (?,?, ?, ?,?,?)");
$stmt->bind_param('iissss', $id, $rating, $comment, $date, $tagsStr, $bon);
$stmt->execute();
$review_id = $stmt->insert_id;
$stmt->close();

$uploaded = [];
if (isset($_FILES['photos'])) {
    $uploadDirBase = '/var/www/SvrvFT/review_pubs/';
    $userFolder = $uploadDirBase . 'user_id_' . $id . '/';

    // Buat folder jika belum ada
    if (!is_dir($userFolder)) {
        mkdir($userFolder, 0777, true);
    }

    foreach ($_FILES['photos']['tmp_name'] as $i => $tmp) {
        $originalName = basename($_FILES['photos']['name'][$i]);

        // Buat nama unik untuk file agar tidak tertimpa
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $newName = uniqid('review_', true) . '.' . $ext;

        $destination = $userFolder . $newName;

        // Kompres dan resize dengan GD
        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $success = resizeImage($tmp, $destination, 1280, 80); // Resize maksimal 1280px dan kualitas 80%
        } else {
            // Fallback jika bukan format yang didukung
            $success = move_uploaded_file($tmp, $destination);
        }
        // Pindahkan file ke folder VPS
        if (move_uploaded_file($tmp, $destination)) {
            // Simpan nama file dan path ke database (tanpa blob)
            $stmt = $conn->prepare("INSERT INTO review_foto (review_id, nama_file, path_file) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $review_id, $originalName, $destination);
            $stmt->execute();
            $stmt->close();

            $uploaded[] = $newName;
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Review berhasil disimpan.',
        'photos_uploaded' => $uploaded
    ]);
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Tidak ada foto yang diunggah.']);
    exit;
}

$conn->close();
