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
    echo json_encode(['success' => FALSE, 'message' => $e->getMessage()]);
    EXIT;
}

$ACTION = $_POST['action'] ?? '';

try {
    // Action 'get_recipients' dihapus karena fitur database contact dihilangkan.

    if ($ACTION === 'upload_media') {
        if (!isset($_FILES['media']) || $_FILES['media']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File media tidak ditemukan atau error upload.");
        }
        $FILE = $_FILES['media'];
        $mimeType = $FILE['type'];
        $originalFileName = $FILE['name'];

        $resourceType = 'raw';
        $waMediaType = 'document';

        if (strpos($mimeType, 'image') === 0) {
            $waMediaType = 'image';
            $resourceType = 'image';
        } elseif (strpos($mimeType, 'video') === 0) {
            $waMediaType = 'video';
            $resourceType = 'video';
        }

        $uploadResult = $cloudinary->uploadApi()->upload($FILE['tmp_name'], [
            'folder' => 'whatsapp_broadcast',
            'resource_type' => $resourceType,
            'use_filename' => TRUE,
            'unique_filename' => TRUE
        ]);

        echo json_encode([
            'success' => TRUE,
            'url' => $uploadResult['secure_url'],
            'wa_type' => $waMediaType,
            'filename' => $originalFileName
        ]);
        EXIT;
    }

    if ($ACTION === 'send_message') {
        $phone = $_POST['phone'] ?? '';

        // Memaksa logika hanya untuk Template
        $templateName = $_POST['template_name'] ?? '';
        $templateLang = $_POST['template_lang'] ?? 'id';
        $templateHeaderUrl = $_POST['template_header_url'] ?? '';
        $templateBodyVars = $_POST['template_body_vars'] ?? '';

        $logger = new AppLogger('broadcast.log');
        $conversationService = new ConversationService($conn, $logger);

        $conversation = $conversationService->getOrCreateConversation($phone);
        $convoId = $conversation['id'];

        $res = ['success' => FALSE];
        $wamid = NULL;

        if (empty($phone)) {
            throw new Exception("Nomor telepon kosong");
        }
        if (empty($templateName)) {
            throw new Exception("Nama template wajib diisi.");
        }

        // --- Susun Komponen Template ---
        $components = [];

        // 1. Header (Image)
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

        // 2. Body Variables (Dari CSV)
        if (!empty($templateBodyVars)) {
            $varsArray = preg_split('/,\s*/', $templateBodyVars);
            $params = [];
            foreach ($varsArray as $var) {
                if (TRIM($var) !== '') {
                    $params[] = [
                        'type' => 'text',
                        'text' => TRIM($var)
                    ];
                }
            }
            $components[] = [
                'type' => 'body',
                'parameters' => $params
            ];
        }

        // Kirim Pesan Template
        $res = kirimPesanTemplate($phone, $templateName, $templateLang, $components);

        if ($res['success']) {
            $wamid = $res['wamid'];
            $varText = empty($templateBodyVars) ? '(Tanpa Variabel)' : $templateBodyVars;

            $logContent = "Template: " . $templateName . "\n" .
                "Isi/Vars: " . $varText . "\n\n" .
                "Lampiran: " . ($templateHeaderUrl ?: '-');

            $conversationService->saveMessage($convoId, 'admin', 'broadcast', $logContent, $wamid, 1);
        }

        echo json_encode(['success' => $res['success'], 'phone' => $phone]);
        EXIT;
    }

    throw new Exception("Action tidak valid");

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => FALSE, 'message' => $e->getMessage()]);
}
?>