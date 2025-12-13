<?php

require_once __DIR__ . "/aa_kon_sett.php";
require_once __DIR__ . "/src/utils/Logger.php";
require_once __DIR__ . "/src/helpers/whatsapp_helper_link.php";
require_once __DIR__ . "/src/config/Config.php";
require_once __DIR__ . "/src/service/VerificationService.php";
require_once __DIR__ . "/src/service/ConversationService.php";
require_once __DIR__ . "/src/service/AutoReplyService.php";
require_once __DIR__ . "/src/handler/WebhookHandler.php";

$logger = new AppLogger('webhook_handler.log');
Config::load();

$verificationService = new VerificationService($conn, $logger);
$conversationService = new ConversationService($conn, $logger);
$autoReplyService = new AutoReplyService($conn, $logger);
$webhookHandler = new WebhookHandler($verificationService, $conversationService, $autoReplyService, $logger);

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'GET') {
    $webhookHandler->handleVerification();
    exit;
}

if ($requestMethod === 'POST') {
    $data = file_get_contents('php://input');
    $body = json_decode($data, true);

    // $logger->info("Received POST data: " . $data);

    if (json_last_error() === JSON_ERROR_NONE && isset($body['object']) && $body['object'] === 'whatsapp_business_account') {

        if (isset($body['entry'][0]['changes'][0]['value']['messages'])) {
            $webhookHandler->handleIncomingMessage($body);
        } elseif (isset($body['entry'][0]['changes'][0]['value']['statuses'])) {
            $webhookHandler->handleStatusUpdate($body);
        }

    } else {
        $logger->warning("Invalid POST data received or not a WhatsApp notification.");
    }

    http_response_code(200);
    exit;
}

http_response_code(405);
echo "Method Not Allowed";