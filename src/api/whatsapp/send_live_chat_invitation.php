<?php

require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../helpers/whatsapp_helper_link.php';
require_once __DIR__ . '/../../service/ConversationService.php';
require_once __DIR__ . '/../../utils/Logger.php';

header('Content-Type: application/json');
$logger = new AppLogger('send_live_chat_invitation.log');
$conversationService = new ConversationService($conn, $logger);

try {
    $headers = getallheaders();
    if (!isset($headers['Authorization']) || !preg_match('/^Bearer\s(\S+)$/', $headers['Authorization'], $matches)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token tidak ditemukan atau format salah.']);
        exit;
    }

    $token = $matches[1];
    $decoded = verify_token($token);
    $isTokenValidAdmin = (is_object($decoded) && isset($decoded->kode));

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

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['conversation_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Conversation ID tidak disertakan.']);
    exit;
}

$conversationId = filter_var($data['conversation_id'], FILTER_VALIDATE_INT);

if ($conversationId === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Conversation ID tidak valid.']);
    exit;
}
try {
    $stmt_get = $conn->prepare("SELECT nomor_telepon FROM wa_percakapan WHERE id = ?");
    $stmt_get->bind_param("i", $conversationId);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    $conversation = $result->fetch_assoc();
    $stmt_get->close();

    if (!$conversation) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Percakapan tidak ditemukan.']);
        exit;
    }

    $nomorTelepon = $conversation['nomor_telepon'];

    $conversationService->startLiveChat($nomorTelepon);

    $totalUnread = $conversationService->getTotalUnreadCount();

    $ws_url = 'http://127.0.0.1:8081/notify';
    $payload = json_encode([
        'event' => 'new_live_chat',
        'conversation_id' => (int) $conversationId,
        'phone' => $nomorTelepon,
        'total_unread_count' => $totalUnread
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
        $logger->error("WebSocket broadcast failed for starting live chat: " . $ws_e->getMessage());
    }


    echo json_encode(['success' => true, 'message' => 'Status percakapan diubah ke live chat.']);

} catch (Exception $e) {
    $logger->error("Error starting live chat: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>