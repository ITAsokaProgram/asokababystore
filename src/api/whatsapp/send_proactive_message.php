<?php
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../helpers/whatsapp_helper_link.php';
require_once __DIR__ . '/../../service/ConversationService.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../utils/Logger.php';
header('Content-Type: application/json');
$logger = new AppLogger('send_proactive_message.log');
$decoded = authenticate_request();

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['kd_cust']) || !isset($input['message']) || empty($input['message'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Input tidak valid. Diperlukan kd_cust dan message.']);
    exit;
}
$kd_cust = $input['kd_cust'];
$messageText = $input['message'];
$conversationService = new ConversationService($conn, $logger);
try {
    $stmt_cust = $conn->prepare("SELECT no_hp, nama_cust FROM customers WHERE kd_cust = ?");
    if (!$stmt_cust) {
        throw new Exception("Gagal prepare statement (customers): " . $conn->error);
    }
    $stmt_cust->bind_param("s", $kd_cust);
    $stmt_cust->execute();
    $result_cust = $stmt_cust->get_result()->fetch_assoc();
    $stmt_cust->close();
    $phoneNumber = $kd_cust;
    $profileName = $result_cust['nama_cust'] ?? 'Pelanggan';
    $phoneNumber = normalizePhoneNumber($phoneNumber);
    $conversation = $conversationService->getOrCreateConversation($phoneNumber, $profileName);
    $conversationId = $conversation['id'];
    if (!$conversationId) {
        throw new Exception("Gagal mendapatkan atau membuat conversation untuk {$phoneNumber}.");
    }
    $sendResult = kirimPesanTeks($phoneNumber, $messageText);
    $wamid = $sendResult['wamid'] ?? null;
    if (!$sendResult['success']) {
        throw new Exception("Gagal mengirim pesan via WhatsApp API.");
    }
    $savedMessage = $conversationService->saveMessage($conversationId, 'admin', 'text', $messageText, $wamid, 0);
    if (!$savedMessage) {
        throw new Exception("Pesan terkirim ke WA, tapi gagal disimpan ke DB lokal.");
    }
    $ws_url = 'http://127.0.0.1:8081/notify';
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
    echo json_encode(['success' => true, 'message' => 'Pesan berhasil terkirim.']);
} catch (Exception $e) {
    http_response_code(500);
    $logger->error("Error sending proactive message: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>