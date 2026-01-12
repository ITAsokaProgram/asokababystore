<?php
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../helpers/whatsapp_helper_link.php';
require_once __DIR__ . '/../../service/ConversationService.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
use Cloudinary\Cloudinary;
header('Content-Type: application/json');
$env = parse_ini_file(__DIR__ . '/../../../.env');
$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => $env['CLOUDINARY_NAME'],
        'api_key' => $env['CLOUDINARY_KEY'],
        'api_secret' => $env['CLOUDINARY_SECRET'],
    ],
]);
try {
    $headers = getallheaders();
    if (!isset($headers['Authorization']) || !preg_match('/^Bearer\s(\S+)$/', $headers['Authorization'], $matches)) {
        throw new Exception('Unauthorized: Token tidak ditemukan.');
    }
    $decoded = verify_token($matches[1]);
    if (!(is_object($decoded) && isset($decoded->kode))) {
        throw new Exception('Invalid Token');
    }
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
$action = $_POST['action'] ?? '';
try {
    if ($action === 'get_recipients') {
        $targetType = $_POST['target_type'] ?? 'manual';
        $manualNumbers = $_POST['manual_numbers'] ?? '';
        $recipients = [];
        if ($targetType === 'manual') {
            $rawNumbers = preg_split('/[\r\n,]+/', $manualNumbers, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($rawNumbers as $num) {
                $clean = preg_replace('/[^0-9]/', '', trim($num));
                if (substr($clean, 0, 1) === '0') {
                    $clean = '62' . substr($clean, 1);
                }
                if (!empty($clean)) {
                    $recipients[] = $clean;
                }
            }
            $recipients = array_unique($recipients);
        } else {
            $stmt = $conn->prepare("SELECT DISTINCT nomor_telepon FROM wa_percakapan WHERE nomor_telepon IS NOT NULL AND nomor_telepon != ''");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $recipients[] = $row['nomor_telepon'];
            }
        }
        echo json_encode(['success' => true, 'data' => array_values($recipients)]);
        exit;
    }
    if ($action === 'upload_media') {
        if (!isset($_FILES['media']) || $_FILES['media']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File media tidak ditemukan atau error upload.");
        }
        $file = $_FILES['media'];
        $mimeType = $file['type'];
        $originalFileName = $file['name'];
        $resourceType = 'raw';
        $waMediaType = 'document';
        if (strpos($mimeType, 'image') === 0) {
            $waMediaType = 'image';
            $resourceType = 'image';
        } elseif (strpos($mimeType, 'video') === 0) {
            $waMediaType = 'video';
            $resourceType = 'video';
        }
        $uploadResult = $cloudinary->uploadApi()->upload($file['tmp_name'], [
            'folder' => 'whatsapp_broadcast',
            'resource_type' => $resourceType,
            'use_filename' => true,
            'unique_filename' => true
        ]);
        echo json_encode([
            'success' => true,
            'url' => $uploadResult['secure_url'],
            'wa_type' => $waMediaType,
            'filename' => $originalFileName
        ]);
        exit;
    }
    if ($action === 'send_message') {
        $phone = $_POST['phone'] ?? '';
        $messageType = $_POST['message_type'] ?? 'text';
        $logger = new AppLogger('broadcast.log');
        $conversationService = new ConversationService($conn, $logger);
        $conversation = $conversationService->getOrCreateConversation($phone);
        $convoId = $conversation['id'];
        $res = ['success' => false];
        $wamid = null;
        if (empty($phone)) {
            throw new Exception("Nomor telepon kosong");
        }
        if ($messageType === 'template') {
            $templateName = $_POST['template_name'] ?? '';
            $templateLang = $_POST['template_lang'] ?? 'id';
            $templateHeaderUrl = $_POST['template_header_url'] ?? '';
            $templateBodyVars = $_POST['template_body_vars'] ?? '';
            if (empty($templateName)) {
                throw new Exception("Nama template wajib diisi.");
            }
            $components = [];
            if (!empty($templateHeaderUrl)) {
                $components[] = [
                    'type' => 'header',
                    'parameters' => [
                        [
                            'type' => 'image',
                            'image' => ['link' => $templateHeaderUrl]
                        ]
                    ]
                ];
            }
            if (!empty($templateBodyVars)) {
                $varsArray = preg_split('/,\s*/', $templateBodyVars); 
                $params = [];
                foreach ($varsArray as $var) {
                    if (trim($var) !== '') {
                        $params[] = [
                            'type' => 'text',
                            'text' => trim($var)
                        ];
                    }
                }
                $components[] = [
                    'type' => 'body',
                    'parameters' => $params
                ];
            }
            $res = kirimPesanTemplate($phone, $templateName, $templateLang, $components);
            if ($res['success']) {
                $wamid = $res['wamid'];
                $logContent = "[Template: $templateName | Vars: $templateBodyVars]";
                $conversationService->saveMessage($convoId, 'admin', 'text', $logContent, $wamid, 1);
            }
        } else {
            $messageText = $_POST['message'] ?? '';
            if (!empty($messageText)) {
                $res = kirimPesanTeks($phone, $messageText);
                if ($res['success']) {
                    $wamid = $res['wamid'];
                    $conversationService->saveMessage($convoId, 'admin', 'text', $messageText, $wamid, 1);
                }
            } else {
                throw new Exception("Pesan teks kosong.");
            }
        }
        echo json_encode(['success' => $res['success'], 'phone' => $phone]);
        exit;
    }
    throw new Exception("Action tidak valid");
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>