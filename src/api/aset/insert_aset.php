<?php
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
header("Access-Control-Allow-Methods: POST");
header('Content-Type: application/json');

use Cloudinary\Cloudinary;
$env = parse_ini_file(__DIR__ . '/../../../config.env');
// Get token from Authorization header
$headers = getallheaders();
$auth_header = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $auth_header);

if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Request ditolak user tidak terdaftar']);
    exit;
}

$verif = verify_token($token);
if (!$verif) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Token tidak valid']);
    exit;
}

// Configure Cloudinary
$cloudinary = new \Cloudinary\Cloudinary([
    'cloud' => [
        'cloud_name' => $env['CLOUDINARY_NAME'],
        'api_key' => $env['CLOUDINARY_KEY'],
        'api_secret' => $env['CLOUDINARY_SECRET'],
    ],
]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Validate required fields
    $required_fields = ['nama_barang', 'merk', 'harga_beli', 'nama_toko', 'kd_store'];
    $missing_fields = [];

    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {
        throw new Exception('Missing required fields: ' . implode(', ', $missing_fields));
    }

    // Handle image upload to Cloudinary if present
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload = $cloudinary->uploadApi()->upload(
            $_FILES['image']['tmp_name'],
            ['folder' => 'aset_barang']
        );
        $image_url = $upload['secure_url'];
    }

    // Prepare SQL statement - use NULLIF to convert empty strings to NULL for DATETIME fields
    $sql = "INSERT INTO history_aset (
        nama_barang, 
        merk, 
        harga_beli, 
        nama_toko, 
        tanggal_beli, 
        tanggal_ganti, 
        mutasi_untuk, 
        mutasi_dari, 
        kd_store, 
        tanggal_perbaikan,
        tanggal_mutasi,
        tanggal_rusak,
        group_aset,
        status,
        image_url
    ) VALUES (?, ?, ?, ?, NULLIF(?, ''), NULLIF(?, ''), ?, ?, ?, NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $status = $_POST['status'] ?? 'Baru';

    // Prepare values for bind_param
    $nama_barang = $_POST['nama_barang'];
    $merk = $_POST['merk'];
    $harga_beli = $_POST['harga_beli'];
    $nama_toko = $_POST['nama_toko'];
    // Normalize datetime-local input (YYYY-MM-DDTHH:MM) to MySQL DATETIME (YYYY-MM-DD HH:MM:SS)
    $normalizeDatetime = function ($v) {
        if (!isset($v) || $v === null || $v === '')
            return '';
        // replace T with space (for datetime-local inputs)
        $v = str_replace('T', ' ', $v);
        // if only date provided (YYYY-MM-DD), append midnight time
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
            $v .= ' 00:00:00';
        }
        // ensure seconds for datetime without seconds
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $v)) {
            $v .= ':00';
        }
        return $v;
    };

    $tanggal_beli = $normalizeDatetime($_POST['tanggal_beli'] ?? '');
    $tanggal_ganti = $normalizeDatetime($_POST['tanggal_ganti'] ?? '');
    $mutasi_untuk = isset($_POST['mutasi_untuk']) ? $_POST['mutasi_untuk'] : '';
    $mutasi_dari = isset($_POST['mutasi_dari']) ? $_POST['mutasi_dari'] : '';
    $kd_store = $_POST['kd_store'];
    $tanggal_perbaikan = $normalizeDatetime($_POST['tanggal_perbaikan'] ?? '');
    $tanggal_mutasi = $normalizeDatetime($_POST['tanggal_mutasi'] ?? '');
    $tanggal_rusak = $normalizeDatetime($_POST['tanggal_rusak'] ?? '');
    $group_aset = isset($_POST['group_aset']) ? trim($_POST['group_aset']) : '';

    $stmt->bind_param(
    'sssssssssssssss',
        $nama_barang,
        $merk,
        $harga_beli,
        $nama_toko,
        $tanggal_beli,
        $tanggal_ganti,
        $mutasi_untuk,
        $mutasi_dari,
        $kd_store,
        $tanggal_perbaikan,
        $tanggal_mutasi,
        $tanggal_rusak,
    $group_aset,
        $status,
        $image_url
    );

    if (!$stmt->execute()) {
        throw new Exception("Failed to insert data: " . $stmt->error);
    }

    echo json_encode([
        'status' => true,
        'message' => 'Asset added successfully',
        'data' => [
            'id' => $stmt->insert_id,
            'image_url' => $image_url
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}