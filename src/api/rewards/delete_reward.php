<?php

require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
// Cloudinary SDK
require_once __DIR__ . '/../../../vendor/autoload.php';
// load env for cloudinary
$env = parse_ini_file(__DIR__ . '/../../../.env');
\Cloudinary\Configuration\Configuration::instance([
    'cloud' => [
        'cloud_name' => $env['CLOUDINARY_NAME'] ?? '',
        'api_key' => $env['CLOUDINARY_KEY'] ?? '',
        'api_secret' => $env['CLOUDINARY_SECRET'] ?? ''
    ],
    'url' => ['secure' => true]
]);

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: DELETE");


if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Gunakan metode DELETE.']);
    exit;
}

$verif = authenticate_request();


// [MODIFIKASI] Verifikasi token dan otorisasi role
if (!is_object($verif) || !isset($verif->role)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Otentikasi gagal. Token tidak valid atau tidak lengkap.']);
    exit;
}

// Otorisasi: hanya role 'IT' yang boleh menghapus
if ($verif->role !== 'IT') {
    http_response_code(403); // 403 Forbidden
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya akun IT yang memiliki izin untuk menghapus data.']);
    exit;
}
// [AKHIR MODIFIKASI]

$id = trim($_GET['id'] ?? 0);

if ($id == 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit;
}

// fetch image_url first to use for Cloudinary deletion later
$imageUrl = null;
$checkStmt = $conn->prepare("SELECT image_url FROM hadiah WHERE id_hadiah = ?");
if ($checkStmt) {
    $idInt = (int)$id;
    $checkStmt->bind_param('i', $idInt);
    $checkStmt->execute();
    $res = $checkStmt->get_result();
    $row = $res->fetch_assoc();
    $checkStmt->close();

    // if hadiah not found, return not found
    if (!$row) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Data tidak ditemukan']);
        exit;
    }

    $imageUrl = $row['image_url'] ?? null;
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal mengecek data: ' . $conn->error]);
    exit;
}

// If file_id is not available but image_url exists, try to extract Cloudinary public_id from the URL
function extract_public_id_from_cloudinary_url($url)
{
    if (empty($url)) return null;
    $parts = parse_url($url);
    if (!isset($parts['path'])) return null;
    // path example: /<version>/.../image/upload/v12345/folder1/folder2/public_id.jpg
    $path = $parts['path'];
    // find the /image/upload/ segment
    $pos = strpos($path, '/image/upload/');
    if ($pos === false) return null;
    $after = substr($path, $pos + strlen('/image/upload/'));
    // remove leading version segment like v12345/
    if (preg_match('#^v\d+/(.*)$#', $after, $m)) {
        $after = $m[1];
    }
    // remove extension
    $after = preg_replace('#\.[A-Za-z0-9]+$#', '', $after);
    // trim leading/trailing slashes
    $publicId = trim($after, '/');
    // decode URL-encoded parts
    return $publicId === '' ? null : urldecode($publicId);
}

// Determine the public id we will pass to Cloudinary destroy (best-effort)
$publicIdToDelete = null;
if (!empty($imageUrl)) {
    $extracted = extract_public_id_from_cloudinary_url($imageUrl);
    if ($extracted) {
        $publicIdToDelete = $extracted;
    }
}

// perform log + delete in a single transaction to avoid FK issues
$conn->autocommit(FALSE);
try {
    // 1) insert log while parent still exists
    $id_user = $verif->kode ?? null; // Menggunakan 'kode' dari token
    $logActivity = 'DELETE';
    $logTime = date('Y-m-d H:i:s');

    $logStmt = $conn->prepare("INSERT INTO log_hadiah (id_hadiah, id_user, log_activity, created_at) VALUES (?, NULLIF(?, ''), ?, ?)");
    if (!$logStmt) throw new Exception('log prepare failed: ' . $conn->error);
    $idUserVal = $id_user === null ? '' : (string)$id_user;
    $logStmt->bind_param('isss', $id, $idUserVal, $logActivity, $logTime);
    if (!$logStmt->execute()) {
        $err = $logStmt->error ?: $conn->error;
        $logStmt->close();
        throw new Exception('log execute failed: ' . $err);
    }
    $logStmt->close();

    // 2) delete the hadiah
    $delSql = "DELETE FROM hadiah WHERE id_hadiah = ?";
    $delStmt = $conn->prepare($delSql);
    if (!$delStmt) throw new Exception('delete prepare failed: ' . $conn->error);
    // ensure integer binding
    $idInt = (int)$id;
    $delStmt->bind_param('i', $idInt);
    if (!$delStmt->execute()) {
        $err = $delStmt->error ?: $conn->error;
        $delStmt->close();
        throw new Exception('delete execute failed: ' . $err);
    }

    if ($delStmt->affected_rows > 0) {
        $conn->commit();

        // attempt to delete file on Cloudinary (best-effort)
        try {
            $api = new \Cloudinary\Api\Upload\UploadApi();
            $candidates = [];
            if (!empty($publicIdToDelete)) $candidates[] = $publicIdToDelete;
            if (!empty($imageUrl)) {
                // raw URL as-is (some projects store public_id directly as image_url)
                $candidates[] = $imageUrl;
                // basename without extension
                $path = parse_url($imageUrl, PHP_URL_PATH) ?: '';
                $basename = pathinfo($path, PATHINFO_FILENAME);
                if (!empty($basename)) $candidates[] = $basename;
            }
            // unique candidates, preserve order
            $candidates = array_values(array_unique($candidates));
            foreach ($candidates as $candidate) {
                try {
                    $res = $api->destroy($candidate);
                    // if destroy returns result => 'ok' or 'not_found', consider it handled
                    if (is_array($res) && (isset($res['result']) && in_array($res['result'], ['ok','not_found']))) {
                        break;
                    }
                    // otherwise continue trying other candidates
                } catch (Exception $inner) {
                    // try next candidate
                    error_log('Cloudinary destroy attempt failed for candidate ' . $candidate . ': ' . $inner->getMessage());
                    continue;
                }
            }
        } catch (Exception $e) {
            error_log('Cloudinary destroy failed overall for reward id ' . $idInt . ': ' . $e->getMessage());
            // do not rollback DB delete; just log
        }

        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Data berhasil dihapus']);
    } else {
        // nothing deleted -> rollback the previously inserted log so we don't keep orphan logs for non-existing deletes
        $conn->rollback();
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Data tidak ditemukan']);
    }

    $delStmt->close();
} catch (Exception $e) {
    $conn->rollback();
    error_log('Delete flow failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus data: ' . $e->getMessage()]);
} finally {
    $conn->autocommit(TRUE);
    $conn->close();
}