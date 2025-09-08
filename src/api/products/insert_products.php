<?php

require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: POST");
date_default_timezone_set('Asia/Jakarta');

use Cloudinary\Cloudinary;

$env = parse_ini_file(__DIR__ . '/../../../config.env');

$token = $_COOKIE['token'] ?? null;
if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit;
}

$verify = verify_token($token);
if (!$verify) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Invalid token']);
    exit;
}

try {
    $barcode = sanitize_input($_POST['barcode'] ?? null);
    $plu = sanitize_input($_POST['plu'] ?? null);
    $nama_produk = sanitize_input($_POST['nama_produk'] ?? null);
    $deskripsi = sanitize_input($_POST['deskripsi'] ?? null);
    // Read raw kategori, sanitize, then convert to PascalCase for storage
    $kategori_raw = $_POST['kategori'] ?? null;
    $kategori = sanitize_input($kategori_raw);
    $kategori_pascal = $kategori ? toPascalCase($kategori) : null;

    $cabang = sanitize_input($_POST['branch'] ?? null);
    $image_url = $_FILES['gambar-produk'];
    $tanggal_upload = date('Y-m-d H:i:s');

    if (!$image_url || !$barcode || !$plu || !$nama_produk || !$deskripsi || !$kategori || !$cabang) {
        http_response_code(400);
        echo json_encode(['status' => false, 'message' => 'All fields are required']);
        exit;
    }

    if ($image_url['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['status' => false, 'message' => 'Image upload failed']);
        exit;
    }

    $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    $fileMimeType = mime_content_type($image_url['tmp_name']);
    if (!array_key_exists($fileMimeType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['status' => false, 'message' => 'Invalid image type. Only JPG and PNG are allowed.']);
        exit;
    }

    if ($image_url['size'] > $maxSize) {
        http_response_code(400);
        echo json_encode(['status' => false, 'message' => 'Image size exceeds the maximum limit of 2MB.']);
        exit;
    }

    $cloudinary = new \Cloudinary\Cloudinary([
        'cloud' => [
            'cloud_name' => $env['CLOUDINARY_NAME'],
            'api_key' => $env['CLOUDINARY_KEY'],
            'api_secret' => $env['CLOUDINARY_SECRET'],
        ],
    ]);

    $conn->autocommit(FALSE);
    $conn->begin_transaction();

    // Generate a unique filename
    $fileExtension = $allowedTypes[$fileMimeType];
    $uniqueFilename = 'product_' . uniqid();

    // Upload to Cloudinary with optimization
    $uploadResult = $cloudinary->uploadApi()->upload(
        $_FILES['gambar-produk']['tmp_name'],
        [
            'public_id' => $uniqueFilename,
            'folder' => 'products',
            'quality' => 'auto',
            'fetch_format' => 'auto'
        ]
    );

    // Prepare upload result in the format expected by the rest of the code
    $imageUrl = $uploadResult['secure_url'];

    $sql = "INSERT INTO product_online (barcode,plu,nama_produk,deskripsi,kategori,image_url,tanggal_upload,kd_store) VALUES (?,?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssss', $barcode, $plu, $nama_produk, $deskripsi, $kategori_pascal, $imageUrl, $tanggal_upload, $cabang);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $conn->commit();
        http_response_code(201);
        echo json_encode(['status' => true, 'message' => 'Product inserted successfully']);
    } else {
        $conn->rollback();
        http_response_code(400);
        echo json_encode(['status' => false, 'message' => 'Failed to insert product']);
    }

    $stmt->close();
    $conn->close();
} catch (Throwable $e) {
    if (isset($conn)) $conn->rollback();
    error_log('Error inserting product: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'Internal Server Error']);
    exit;
}

function sanitize_input($data)
{
    // Accept null and return null (caller checks required fields)
    if ($data === null) return null;

    // Ensure we work with a string
    $data = (string) $data;

    // Remove leading/trailing unicode whitespace without using trim()
    $data = preg_replace('/^\s+|\s+$/u', '', $data);

    // Strip HTML tags then escape special characters safely
    $data = strip_tags($data);
    return htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function toPascalCase($string)
{
    if ($string === null) return null;

    // Normalize whitespace and convert to lowercase first
    $s = preg_replace('/\s+/u', ' ', $string);
    $s = mb_strtolower($s, 'UTF-8');

    // Tokenize into word sequences or allowed separators (& and -)
    // This preserves & and - between words while other characters are removed earlier by sanitizer
    preg_match_all('/[\p{L}\p{N}]+|[&-]/u', $s, $matches);
    $tokens = $matches[0] ?? [];

    $pascal = '';
    foreach ($tokens as $token) {
        if ($token === '&' || $token === '-') {
            // preserve separator as-is
            $pascal .= $token;
            continue;
        }
        // Capitalize first char (multibyte-safe) and append rest
        $first = mb_strtoupper(mb_substr($token, 0, 1, 'UTF-8'), 'UTF-8');
        $rest = mb_substr($token, 1, null, 'UTF-8');
        $pascal .= $first . $rest;
    }

    return $pascal;
}