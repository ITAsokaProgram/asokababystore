<?php

require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../helpers/whatsapp_helper_link.php';
require_once __DIR__ . '/../../service/ConversationService.php';
require_once __DIR__ . '/../../auth/middleware_login.php';

use Cloudinary\Cloudinary;
$env = parse_ini_file(__DIR__ . '/../../../.env');

header('Content-Type: application/json');

try {
    $headers = getallheaders();
    if (!isset($headers['Authorization']) || !preg_match('/^Bearer\s(\S+)$/', $headers['Authorization'], $matches)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token tidak ditemukan atau format salah.']);
        exit;
    }
    $token = $matches[1];
    $decoded = verify_token($token);
    $isTokenValidAdmin = is_object($decoded) && isset($decoded->kode);

    if (!$isTokenValidAdmin) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Token tidak valid.']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Token validation error: ' . $e->getMessage()]);
    exit;
}

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
                'api_key'    => $env['CLOUDINARY_KEY'],
                'api_secret' => $env['CLOUDINARY_SECRET'],
            ],
        ]);

        $file = $_FILES['media'];
        $resourceType = strpos($file['type'], 'video') === 0 ? 'video' : 'image';

        $uploadResult = $cloudinary->uploadApi()->upload($file['tmp_name'], [
            'folder' => 'whatsapp_cs_media',
            'resource_type' => $resourceType
        ]);
        
        $mediaUrl = $uploadResult['secure_url'];
        
        kirimPesanMedia($phoneNumber, $mediaUrl, $resourceType, $messageText);
        
        $savedMediaMessage = $conversationService->saveMessage($conversationId, 'admin', $resourceType, $mediaUrl);
        if ($savedMediaMessage) $savedMessages[] = $savedMediaMessage;

        if ($messageText) {
             $savedTextMessage = $conversationService->saveMessage($conversationId, 'admin', 'text', $messageText);
             if ($savedTextMessage) $savedMessages[] = $savedTextMessage;
        }

    } elseif ($messageText) {
        kirimPesanTeks($phoneNumber, $messageText);
        
        $savedTextMessage = $conversationService->saveMessage($conversationId, 'admin', 'text', $messageText);
        if ($savedTextMessage) $savedMessages[] = $savedTextMessage;
    }


    if (!empty($savedMessages)) {
        $ws_url = 'http://127.0.0.1:8081/notify'; 
        
        foreach ($savedMessages as $savedMessage) {
            $payload = json_encode([
                'event' => 'new_admin_reply', 
                'conversation_id' => (int)$conversationId,
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
                curl_setopt($ch, CURLOPT_TIMEOUT_MS, 100); 
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