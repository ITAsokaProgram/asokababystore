<?php

require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../helpers/whatsapp_helper_link.php';
require_once __DIR__ . '/../../service/ConversationService.php';
require_once __DIR__ . '/../../auth/middleware_login.php';

use Cloudinary\Cloudinary;
$env = parse_ini_file(__DIR__ . '/../../../.env');

header('Content-Type: application/json');

$decoded = authenticate_request();


if (!isset($_POST['conversation_id']) || (empty($_POST['message']) && empty($_FILES['media']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Input tidak valid. Diperlukan conversation_id dan message atau media.']);
    exit;
}

$conversationId = $_POST['conversation_id'];
$messageText = $_POST['message'] ?? null;
$logger = new AppLogger('send_admin_reply.log');
$conversationService = new ConversationService($conn, $logger);
$savedMessages = [];

try {
    $stmt = $conn->prepare("SELECT nomor_telepon FROM wa_percakapan WHERE id = ?");
    $stmt->bind_param("i", $conversationId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $phoneNumber = $result['nomor_telepon'] ?? null;
    $stmt->close();

    if (!$phoneNumber) {
        throw new Exception("Conversation with ID {$conversationId} not found.");
    }

    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $env['CLOUDINARY_NAME'],
                'api_key' => $env['CLOUDINARY_KEY'],
                'api_secret' => $env['CLOUDINARY_SECRET'],
            ],
        ]);

        $file = $_FILES['media'];
        $mimeType = $file['type'];
        $originalName = $file['name'];

        if (strpos($mimeType, 'image') === 0) {
            $waType = 'image';
            $cloudResourceType = 'image';
        } elseif (strpos($mimeType, 'video') === 0) {
            $waType = 'video';
            $cloudResourceType = 'video';
        } else {
            $waType = 'document';
            $cloudResourceType = 'raw';
        }

        $uploadResult = $cloudinary->uploadApi()->upload($file['tmp_name'], [
            'folder' => 'whatsapp_cs_media',
            'resource_type' => $cloudResourceType,
            'use_filename' => true,
            'unique_filename' => true
        ]);

        $mediaUrl = $uploadResult['secure_url'];

        $sendResult = kirimPesanMedia($phoneNumber, $mediaUrl, $waType, $messageText, $originalName);
        $wamid = $sendResult['wamid'] ?? null;

        if ($waType === 'document') {
            $dbContent = json_encode([
                'url' => $mediaUrl,
                'filename' => $originalName
            ]);
        } else {
            $dbContent = $mediaUrl;
        }

        $savedMediaMessage = $conversationService->saveMessage($conversationId, 'admin', $waType, $dbContent, $wamid);
        if ($savedMediaMessage)
            $savedMessages[] = $savedMediaMessage;

    } elseif ($messageText) {
        $sendResult = kirimPesanTeks($phoneNumber, $messageText);
        $wamid = $sendResult['wamid'] ?? null;

        $savedTextMessage = $conversationService->saveMessage($conversationId, 'admin', 'text', $messageText, $wamid);
        if ($savedTextMessage)
            $savedMessages[] = $savedTextMessage;
    }


    if (!empty($savedMessages)) {
        $ws_url = 'http://127.0.0.1:8081/notify';

        foreach ($savedMessages as $savedMessage) {
            $payload = json_encode([
                'event' => 'new_admin_reply',
                'conversation_id' => (int) $conversationId,
                'phone' => $phoneNumber,
                'message' => $savedMessage
            ]);

            try {
                $ch = curl_init($ws_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($payload)
                ]);
                curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1000);
                curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
                curl_exec($ch);
                curl_close($ch);
            } catch (Exception $ws_e) {
                $logger->error("WebSocket broadcast failed: " . $ws_e->getMessage());
            }
        }
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    $logger->error("Error sending reply: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>