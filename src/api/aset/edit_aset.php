<?php
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';

header("Access-Control-Allow-Methods: POST");
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');

use Cloudinary\Cloudinary;
use Cloudinary\Api\Exception\NotFound;

$env = parse_ini_file(__DIR__ . '/../../../.env');

// Get token from Authorization header
$headers = getallheaders();
$auth_header = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $auth_header);

if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Request ditolak: user tidak terdaftar']);
    exit;
}

$verif = verify_token($token);
if (!$verif) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Token tidak valid']);
    exit;
}

// Configure Cloudinary
$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => $env['CLOUDINARY_NAME'],
        'api_key'    => $env['CLOUDINARY_KEY'],
        'api_secret' => $env['CLOUDINARY_SECRET'],
    ],
]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Validate required fields, including the asset ID
    $required_fields = ['edit_idhistory_aset', 'edit_nama_barang', 'edit_merk', 'edit_tanggal_beli' ,'edit_harga_beli', 'edit_nama_toko', 'edit_kd_store', 'edit_no_seri'];
    $missing_fields = [];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {
        throw new Exception('Field yang wajib diisi tidak lengkap: ' . implode(', ', $missing_fields));
    }

    $id_aset = $_POST['edit_idhistory_aset'];

    // 1. Fetch current image_url from DB to keep it if no new image is uploaded
    $stmt_select = $conn->prepare("SELECT image_url FROM history_aset WHERE idhistory_aset = ?");
    $stmt_select->bind_param('i', $id_aset);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $current_data = $result->fetch_assoc();
    $stmt_select->close();

    if (!$current_data) {
        throw new Exception("Aset dengan ID $id_aset tidak ditemukan.");
    }
    
    $image_url_to_save = $current_data['image_url'];

    // 2. Handle new image upload to Cloudinary if present
    if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] === UPLOAD_ERR_OK) {
        // Upload new image with compression
        $upload = $cloudinary->uploadApi()->upload(
            $_FILES['edit_image']['tmp_name'],
            [
                'folder' => 'aset_barang',
                'transformation' => [
                    ['width' => 1024, 'crop' => 'limit'],
                    ['quality' => 'auto:good']
                ]
            ]
        );
        $image_url_to_save = $upload['secure_url'];

        // 3. Delete old image from Cloudinary if it exists
        if (!empty($current_data['image_url'])) {
            try {
                // Extract public_id from the old URL
                $path_parts = pathinfo($current_data['image_url']);
                $public_id = 'aset_barang/' . $path_parts['filename'];
                $cloudinary->uploadApi()->destroy($public_id);
            } catch (NotFound $e) {
                // Ignore if the file is not found on Cloudinary
            }
        }
    }

    // Prepare SQL UPDATE statement
    $sql = "UPDATE history_aset SET 
        nama_barang = ?, merk = ?, harga_beli = ?, nama_toko = ?, tanggal_beli = NULLIF(?, ''), 
        tanggal_ganti = NULLIF(?, ''), mutasi_untuk = ?, mutasi_dari = ?, kd_store = ?, 
        tanggal_perbaikan = NULLIF(?, ''), tanggal_mutasi = NULLIF(?, ''), tanggal_rusak = NULLIF(?, ''), 
        group_aset = ?, status = ?, image_url = ?, no_seri = ?, keterangan = ?
        WHERE idhistory_aset = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    // Helper function to normalize datetime strings
    $normalizeDatetime = function ($v) {
        if (!isset($v) || $v === null || $v === '') return '';
        $v = str_replace('T', ' ', $v);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) $v .= ' 00:00:00';
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $v)) $v .= ':00';
        return $v;
    };

    // Prepare values from $_POST with 'edit_' prefix
    $nama_barang = $_POST['edit_nama_barang'];
    $merk = $_POST['edit_merk'];
    $harga_beli = $_POST['edit_harga_beli'];
    $nama_toko = $_POST['edit_nama_toko'];
    $tanggal_beli = $normalizeDatetime($_POST['edit_tanggal_beli'] ?? '');
    $tanggal_ganti = $normalizeDatetime($_POST['edit_tanggal_ganti'] ?? '');
    $mutasi_untuk = $_POST['edit_mutasi_untuk'] ?? '';
    $mutasi_dari = $_POST['edit_mutasi_dari'] ?? '';
    $kd_store = $_POST['edit_kd_store'];
    $tanggal_perbaikan = $normalizeDatetime($_POST['edit_tanggal_perbaikan'] ?? '');
    $tanggal_mutasi = $normalizeDatetime($_POST['edit_tanggal_mutasi'] ?? '');
    $tanggal_rusak = $normalizeDatetime($_POST['edit_tanggal_rusak'] ?? '');
    $group_aset = trim($_POST['edit_group_aset'] ?? '');
    $status = $_POST['edit_status'] ?? 'Baru';
    $no_seri = trim($_POST['edit_no_seri'] ?? '');
    $keterangan = trim($_POST['edit_keterangan'] ?? '');

    $stmt->bind_param(
        'sssssssssssssssssi',
        $nama_barang, $merk, $harga_beli, $nama_toko, $tanggal_beli,
        $tanggal_ganti, $mutasi_untuk, $mutasi_dari, $kd_store,
        $tanggal_perbaikan, $tanggal_mutasi, $tanggal_rusak,
        $group_aset, $status, $image_url_to_save, $no_seri, $keterangan,
        $id_aset
    );

    if (!$stmt->execute()) {
        throw new Exception("Gagal update data: " . $stmt->error);
    }

    echo json_encode([
        'status' => true,
        'message' => 'Aset berhasil diupdate',
        'data' => [
            'id' => $id_aset,
            'image_url' => $image_url_to_save
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