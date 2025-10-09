<?php

class WebhookHandler {
    private $logger;
    private $verificationService;

    public function __construct(VerificationService $verificationService, $logger) {
        $this->verificationService = $verificationService;
        $this->logger = $logger;
    }


    public function handleVerification() {
        $verify_token = Config::get('WHATSAPP_VERIFY_TOKEN');
        
        if (isset($_GET['hub_mode']) && $_GET['hub_mode'] === 'subscribe' && $_GET['hub_verify_token'] === $verify_token) {
            echo $_GET['hub_challenge'];
            http_response_code(200);
            $this->logger->info("Webhook verified successfully.");
        } else {
            http_response_code(403);
            echo "Token salah.";
            $this->logger->warning("Webhook verification failed. Invalid token.");
        }
    }


    public function handleIncomingMessage($body) {
        $this->logger->info("Webhook received data: " . json_encode($body));

        $message = $body['entry'][0]['changes'][0]['value']['messages'][0] ?? null;

        if ($message && $message['type'] === 'text') {
            $this->processTextMessage($message);
        }
    }


    private function processTextMessage($message) {
        $nomorPengirim = $message['from'];
        $pesanMasuk = $message['text']['body'];

        preg_match('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $pesanMasuk, $matches);

        if (!empty($matches[0])) {
            $urlDiterima = $matches[0];
            $this->logger->info("URL found: {$urlDiterima} from {$nomorPengirim}");
            
            $queryParams = [];
            parse_str(parse_url($urlDiterima, PHP_URL_QUERY), $queryParams);

            if (isset($queryParams['token'])) {
                $tokenDariUser = $queryParams['token'];
                $this->verificationService->processToken($tokenDariUser);
            } else {
                $this->logger->warning("URL received but no token found in query: {$urlDiterima}");
            }
        }
    }
}