<?php

class FlowController {
    private $logger;
    private $encryptionService;

    public function __construct(AppLogger $logger, EncryptionService $encryptionService) {
        $this->logger = $logger;
        $this->encryptionService = $encryptionService;
    }

    public function handleRequest() {
        $this->logger->info('>>>>> Flow endpoint received a new request <<<<<');
        header('Content-Type: text/plain');

        $requestBody = file_get_contents('php://input');
        
        if (isset($_SERVER['HTTP_X_HUB_SIGNATURE_256'])) {
            $this->encryptionService->validateSignature($requestBody, $_SERVER['HTTP_X_HUB_SIGNATURE_256']);
        }
        
        $decodedBody = json_decode($requestBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON body');
        }

        
        $requestData = $this->encryptionService->decryptRequest($decodedBody);
        
        $action = $requestData['action'] ?? '';
        $screen = $requestData['screen'] ?? '';
        $data = $requestData['data'] ?? [];
        $aesKey = $requestData['aes_key'];
        $initialVector = $requestData['initial_vector'];

        $this->logger->info('Request decrypted. Action: "' . $action . '", Screen: "' . $screen . '"');
        if(!empty($data)) {
            $this->logger->info('Decrypted data payload: ' . json_encode($data));
        }

        $responsePayload = [];
        if ($action === 'ping') {
            $this->logger->info('Responding to ping health check.');
            $responsePayload = ['data' => ['status' => 'active']];
        } elseif ($action === 'data_exchange') {
            
            
            $this->logger->warning('No specific data_exchange handler for screen: "' . $screen . '". Sending empty response.');
            
            $responsePayload = [];
        }

        if (!empty($responsePayload)) {
            $this->logger->info('Encrypting and sending response.');
            $encryptedResponse = $this->encryptionService->encryptResponse($responsePayload, $aesKey, $initialVector);
            http_response_code(200);
            echo $encryptedResponse;
        } else {
            
            
            http_response_code(200);
            $this->logger->info('No response payload was generated. Sending 200 OK.');
        }
        $this->logger->info('<<<<< Request processing finished >>>>>');
    }

    
}