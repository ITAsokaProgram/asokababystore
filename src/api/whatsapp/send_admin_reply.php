<?php

require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../helpers/whatsapp_helper_link.php';
require_once __DIR__ . '/../../service/ConversationService.php';
require_once __DIR__ . '/../../auth/middleware_login.php'; 

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

    
    $isTokenValidAdmin = false;
    if (is_object($decoded) && isset($decoded->kode)) {
        
        $isTokenValidAdmin = true;
    } elseif (is_array($decoded) && isset($decoded['status']) && $decoded['status'] === 'error') {
        
        $isTokenValidAdmin = false;
    }

    if (!$isTokenValidAdmin) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Token tidak valid atau bukan token admin.']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Token validation error: ' . $e->getMessage()]);
    exit;
}



$data = json_decode(file_get_contents('php://input'), true);


if (!$data || !isset($data['conversation_id']) || !isset($data['message'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Input tidak valid.']);
    exit;
}

$conversationId = $data['conversation_id'];
$message = $data['message'];
$logger = new AppLogger('send_admin_reply.log');

$conversationService = new ConversationService($conn, $logger);

$conversationService->saveMessage($conversationId, 'admin', 'text', $message);

$stmt = $conn->prepare("SELECT nomor_telepon FROM percakapan_whatsapp WHERE id = ?");
$stmt->bind_param("i", $conversationId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$phoneNumber = $result['nomor_telepon'];
$stmt->close();

if ($phoneNumber) {
    
    kirimPesanTeks($phoneNumber, $message);
} else {
    
    $logger->warning("Gagal mengirim balasan: conversation_id $conversationId tidak ditemukan.");
}


echo json_encode(['success' => true]);


if (isset($conn)) {
    $conn->close();
}
?>