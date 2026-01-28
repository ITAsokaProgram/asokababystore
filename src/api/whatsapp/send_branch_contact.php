<?php

require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../helpers/whatsapp_helper_link.php';
require_once __DIR__ . '/../../service/ConversationService.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../utils/Logger.php';

header('Content-Type: application/json');

$decoded = authenticate_request();

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['conversation_id']) || !isset($input['branch_name']) || !isset($input['phone_number'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
    exit;
}

$conversationId = $input['conversation_id'];
$branchName = $input['branch_name'];
$phoneNumber = $input['phone_number'];

$logger = new AppLogger('send_branch_contact.log');
$conversationService = new ConversationService($conn, $logger);

try {
    $stmt = $conn->prepare("SELECT nomor_telepon FROM wa_percakapan WHERE id = ?");
    $stmt->bind_param("i", $conversationId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $customerPhone = $result['nomor_telepon'] ?? null;
    $stmt->close();

    if (!$customerPhone) {
        throw new Exception("Conversation tidak ditemukan.");
    }

    $contactName = "Asoka Baby Store " . $branchName;

    $sendResult = kirimPesanKontak($customerPhone, $contactName, $phoneNumber);

    $messageContent = json_encode([
        'name' => $contactName,
        'phone' => $phoneNumber
    ]);

    $savedMessage = $conversationService->saveMessage(
        $conversationId,
        'admin',
        'contacts',
        $messageContent,
        $sendResult['wamid'] ?? null
    );

    if ($savedMessage) {
        $ws_url = 'http://127.0.0.1:8081/notify';
        $payload = json_encode([
            'event' => 'new_admin_reply',
            'conversation_id' => (int) $conversationId,
            'phone' => $customerPhone,
            'message' => $savedMessage
        ]);

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
    }

    echo json_encode(['success' => true, 'message' => 'Kontak berhasil dikirim.']);

} catch (Exception $e) {
    http_response_code(500);
    $logger->error("Error sending branch contact: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>