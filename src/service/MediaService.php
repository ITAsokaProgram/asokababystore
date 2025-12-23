<?php
require_once __DIR__ . '/../config/Config.php';
use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
class MediaService
{
    private $logger;
    private $cloudinary;
    private $whatsappAccessToken;
    public function __construct($logger)
    {
        $this->logger = $logger;
        $env = Config::getMultiple(['CLOUDINARY_NAME', 'CLOUDINARY_KEY', 'CLOUDINARY_SECRET', 'WHATSAPP_ACCESS_TOKEN']);
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $env['CLOUDINARY_NAME'],
                'api_key' => $env['CLOUDINARY_KEY'],
                'api_secret' => $env['CLOUDINARY_SECRET'],
            ],
        ]);
        $this->whatsappAccessToken = $env['WHATSAPP_ACCESS_TOKEN'];
    }
    public function downloadAndUpload($mediaId, $mediaType, $originalFilename = null)
    {
        $url = "https://graph.facebook.com/v24.0/{$mediaId}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $this->whatsappAccessToken]);
        $response = curl_exec($ch);
        $httpCodeInfo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($response, true);
        if ($httpCodeInfo !== 200 || !isset($data['url'])) {
            $this->logger->error("Gagal mendapatkan URL media dari WhatsApp untuk ID: {$mediaId}. HTTP Code: {$httpCodeInfo}. Response: " . $response);
            return ['error' => 'failed_to_get_url', 'details' => $response];
        }
        $mediaUrl = $data['url'];
        $chDownload = curl_init($mediaUrl);
        curl_setopt($chDownload, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chDownload, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $this->whatsappAccessToken]);
        $mediaContent = curl_exec($chDownload);
        $httpCodeDownload = curl_getinfo($chDownload, CURLINFO_HTTP_CODE);
        $downloadError = curl_error($chDownload);
        curl_close($chDownload);
        if ($httpCodeDownload !== 200) {
            $this->logger->error("Gagal mengunduh media dari URL: {$mediaUrl}. HTTP Code: {$httpCodeDownload}. cURL Error: {$downloadError}");
            return ['error' => 'download_failed', 'details' => "HTTP {$httpCodeDownload} - {$downloadError}"];
        }
        $fileSize = strlen($mediaContent);
        if ($mediaType === 'image' && $fileSize > 15 * 1024 * 1024) {
            return ['error' => 'file_too_large', 'limit' => '15 MB'];
        }
        if ($mediaType === 'video' && $fileSize > 50 * 1024 * 1024) {
            return ['error' => 'file_too_large', 'limit' => '50 MB'];
        }
        if ($mediaType === 'audio' && $fileSize > 10 * 1024 * 1024) {
            return ['error' => 'file_too_large', 'limit' => '10 MB'];
        }
        if ($mediaType === 'document' && $fileSize > 50 * 1024 * 1024) {
            return ['error' => 'file_too_large', 'limit' => '50 MB'];
        }
        try {
            $base64Data = 'data:' . $data['mime_type'] . ';base64,' . base64_encode($mediaContent);
            $options = [
                'folder' => 'whatsapp_media',
                'access_mode' => 'public'
            ];
            if ($mediaType === 'document' && $originalFilename) {
                $options['resource_type'] = 'raw';
                $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
                $baseName = pathinfo($originalFilename, PATHINFO_FILENAME);
                $uniqueSuffix = substr(bin2hex(random_bytes(6)), 0, 8);
                $options['public_id'] = $baseName . '_' . $uniqueSuffix;
                $options['format'] = $extension;
                $options['use_filename'] = false;
                $options['unique_filename'] = false;
                $options['overwrite'] = false;
            } elseif ($mediaType === 'audio') {
                $options['resource_type'] = 'video';
                $options['unique_filename'] = true;
            } else {
                $options['resource_type'] = 'auto';
                $options['unique_filename'] = true;
            }
            $uploadResult = $this->cloudinary->uploadApi()->upload($base64Data, $options);
            return ['url' => $uploadResult['secure_url']];
        } catch (Exception $e) {
            $this->logger->error("Gagal unggah ke Cloudinary: " . $e->getMessage());
            return ['error' => 'upload_to_cloudinary_failed', 'details' => $e->getMessage()];
        }
    }
}