<?php
require_once __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;

$fileId = $_GET['id'] ?? '';
if (!$fileId) {
    http_response_code(400);
    echo "Missing file ID.";
    exit;
}

$client = new Google_Client();
$client->setAuthConfig('asoka-461004-c0b99b8beff3.json');
$client->addScope(Google_Service_Drive::DRIVE_READONLY);

$httpClient = $client->authorize(); // Authorized HTTP client

try {
    $res = $httpClient->request('GET', "https://www.googleapis.com/drive/v3/files/$fileId?alt=media");

    if ($res->getStatusCode() === 200) {
        header("Content-Type: " . $res->getHeaderLine('Content-Type'));
        echo $res->getBody();
    } else {
        http_response_code($res->getStatusCode());
        echo "Gagal mengambil file dari Google Drive.";
    }
} catch (Exception $e) {
    http_response_code(500);
    echo "Terjadi kesalahan: " . $e->getMessage();
}
