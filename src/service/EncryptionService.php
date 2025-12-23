<?php

use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;

class EncryptionService {
    private $logger;
    private $privateKey;
    private $appSecret;

    public function __construct(AppLogger $logger) {
        $this->logger = $logger;
        $privateKeyPath = Config::get('WHATSAPP_PRIVATE_KEY_PATH');
        $privateKeyPassword = Config::get('WHATSAPP_PRIVATE_KEY_PASSWORD');
        $this->appSecret = Config::get('WHATSAPP_APP_SECRET');

        if (!file_exists($privateKeyPath)) {
            throw new Exception("Private key file not found at: {$privateKeyPath}");
        }
        
        $this->privateKey = PublicKeyLoader::load(file_get_contents($privateKeyPath), $privateKeyPassword);
    }

    public function validateSignature($requestBody, $signature) {
        list($algo, $hash) = explode('=', $signature, 2);
        $expectedHash = hash_hmac($algo, $requestBody, $this->appSecret);
        if (!hash_equals($hash, $expectedHash)) {
            $this->logger->warning('Signature validation failed.');
            throw new Exception('Invalid Signature', 401);
        }
        $this->logger->info('Signature validated successfully.');
    }

    public function decryptRequest($decodedBody) {
        // 1. Decrypt AES Key
        $encryptedAesKey = base64_decode($decodedBody['encrypted_aes_key']);
        $aesKey = $this->privateKey->withPadding(RSA::ENCRYPTION_OAEP)
                                   ->withMGFHash('sha256')
                                   ->withHash('sha256')
                                   ->decrypt($encryptedAesKey);
        if ($aesKey === false) {
            throw new Exception('Failed to decrypt AES key.');
        }

        // 2. Decrypt Flow Data
        $initialVector = base64_decode($decodedBody['initial_vector']);
        $encryptedFlowData = base64_decode($decodedBody['encrypted_flow_data']);
        $tagLength = 16;
        $encryptedData = substr($encryptedFlowData, 0, -$tagLength);
        $authTag = substr($encryptedFlowData, -$tagLength);
        
        $decryptedData = openssl_decrypt(
            $encryptedData, 'aes-128-gcm', $aesKey, OPENSSL_RAW_DATA,
            $initialVector, $authTag
        );
        if ($decryptedData === false) {
            throw new Exception('Failed to decrypt flow data.');
        }

        $requestData = json_decode($decryptedData, true);
        $requestData['aes_key'] = $aesKey;
        $requestData['initial_vector'] = $initialVector;

        return $requestData;
    }

    public function encryptResponse($responseData, $aesKey, $initialVector) {
        $payload = json_encode($responseData);
        $ivLength = strlen($initialVector);
        $flippedIv = '';
        for ($i = 0; $i < $ivLength; $i++) {
            $flippedIv .= ~$initialVector[$i];
        }
        $authTag = '';
        $encryptedResponse = openssl_encrypt(
            $payload, 'aes-128-gcm', $aesKey, OPENSSL_RAW_DATA,
            $flippedIv, $authTag, '', 16
        );
        return base64_encode($encryptedResponse . $authTag);
    }
}