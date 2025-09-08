<?php
require_once '/var/www/asokababystore.com/vendor/autoload.php';
require_once 'aa_kon_sett.php';

date_default_timezone_set('Asia/Jakarta');

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Gunakan metode POST.']);
    exit;
}

if (empty($_POST['kd_cust'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nomor HP wajib diisi.']);
    exit;
}

$noHp = trim($_POST['kd_cust']);
$uploadedAt = date('Y-m-d H:i:s');

if (
    (!isset($_FILES['file_upload']) || !is_array($_FILES['file_upload']['tmp_name']))
    && (!isset($_FILES['add_file']) || !is_array($_FILES['add_file']['tmp_name']))
) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tidak ada file yang dikirim.']);
    exit;
}

$allowedMimeTypes = ['image/jpeg', 'image/png'];
$maxSize = 5 * 1024 * 1024;

try {
    $client = new Client();
    $client->setAuthConfig(__DIR__ . '/asoka-461004-c0b99b8beff3.json');
    $client->addScope(Drive::DRIVE);
    $service = new Drive($client);

    // Cek folder Google Drive berdasarkan kd_cust
    $query = sprintf("mimeType='application/vnd.google-apps.folder' and name='%s' and trashed=false", $noHp);
    $folders = $service->files->listFiles([
        'q' => $query,
        'spaces' => 'drive',
        'fields' => 'files(id, name)',
        'pageSize' => 1,
    ]);

    if (count($folders->files) > 0) {
        $folderId = $folders->files[0]->getId();
    } else {
        $folderMetadata = new DriveFile([
            'name' => $noHp,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => ['1aKu2He7LEYTQRoI_VOz_PT54uxKyvnKK']
        ]);
        $folder = $service->files->create($folderMetadata, ['fields' => 'id']);
        $folderId = $folder->id;
    }

    $results = [];

    function processUploadFiles($fileArray, $folderId, $conn, $service, $noHp, $uploadedAt, &$results, $allowedMimeTypes, $maxSize)
    {
        foreach ($fileArray['tmp_name'] as $index => $tmpPath) {
            if (!is_uploaded_file($tmpPath)) continue;

            $fileName = basename($fileArray['name'][$index]);
            $fileSize = $fileArray['size'][$index];
            $fileMime = mime_content_type($tmpPath);

            if (!in_array($fileMime, $allowedMimeTypes) || $fileSize > $maxSize) continue;

            // Cek duplikat di DB
            $stmtCheck = $conn->prepare("SELECT 1 FROM uploads WHERE kd_cust = ? AND file_name = ?");
            $stmtCheck->bind_param("ss", $noHp, $fileName);
            $stmtCheck->execute();
            $stmtCheck->store_result();

            if ($stmtCheck->num_rows > 0) {
                $stmtCheck->close();
                $results[] = [
                    'file_name' => $fileName,
                    'status' => 'duplikat',
                    'message' => 'File sudah pernah diupload.'
                ];
                continue;
            }
            $stmtCheck->close();

            // Upload ke Drive
            $fileMetadata = new DriveFile([
                'name' => $fileName,
                'parents' => [$folderId],
            ]);
            $uploadedFile = $service->files->create($fileMetadata, [
                'data' => file_get_contents($tmpPath),
                'mimeType' => $fileMime,
                'uploadType' => 'multipart',
                'fields' => 'id, name, webViewLink',
            ]);

            // Set publik
            $permission = new Permission([
                'type' => 'anyone',
                'role' => 'reader',
            ]);
            $service->permissions->create($uploadedFile->id, $permission);

            // Simpan ke DB
            $stmt = $conn->prepare("INSERT INTO uploads (kd_cust, file_name, file_id, file_link, uploaded_at, status) VALUES (?, ?, ?, ?, ?, 'checked')");
            $stmt->bind_param("sssss", $noHp, $uploadedFile->name, $uploadedFile->id, $uploadedFile->webViewLink, $uploadedAt);
            $stmt->execute();
            $stmt->close();

            $results[] = [
                'file_id' => $uploadedFile->id,
                'file_name' => $uploadedFile->name,
                'link' => "https://drive.google.com/uc?id=" . $uploadedFile->id
            ];
        }
    }

    // Proses upload untuk masing-masing input jika ada
    if (isset($_FILES['file_upload']) && is_array($_FILES['file_upload']['tmp_name'])) {
        processUploadFiles($_FILES['file_upload'], $folderId, $conn, $service, $noHp, $uploadedAt, $results, $allowedMimeTypes, $maxSize);
    }

    if (isset($_FILES['add_file']) && is_array($_FILES['add_file']['tmp_name'])) {
        processUploadFiles($_FILES['add_file'], $folderId, $conn, $service, $noHp, $uploadedAt, $results, $allowedMimeTypes, $maxSize);
    }

    if (empty($results)) {
        http_response_code(415);
        echo json_encode(['success' => false, 'message' => 'Tidak ada file valid yang berhasil diupload.']);
    } else {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => count($results) . ' file berhasil diupload.',
            'data' => $results,
            'folder_id' => $folderId,
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat upload ke Google Drive.',
        'error' => $e->getMessage(),
    ]);
}
