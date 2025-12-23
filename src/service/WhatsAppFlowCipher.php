<?php
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\AES;
class WhatsAppFlowCipher
{
    private $privateKeyPem;
    public function __construct($privateKeyPath)
    {
        if (!file_exists($privateKeyPath)) {
            throw new Exception("Private Key not found at: " . $privateKeyPath);
        }
        $this->privateKeyPem = file_get_contents($privateKeyPath);
        if (!$this->privateKeyPem) {
            throw new Exception("Failed to read Private Key. Check permissions.");
        }
    }
    public function decryptRequest($inputJSON)
    {
        $body = json_decode($inputJSON, true);
        if (!isset($body['encrypted_flow_data'])) {
            return null;
        }
        $encryptedAesKey = $body['encrypted_aes_key'];
        $encryptedFlowData = $body['encrypted_flow_data'];
        $initialVector = $body['initial_vector'];
        try {
            $rsa = RSA::load($this->privateKeyPem)
                ->withPadding(RSA::ENCRYPTION_OAEP)
                ->withHash('sha256')
                ->withMGFHash('sha256');
            $decryptedAesKey = $rsa->decrypt(base64_decode($encryptedAesKey));
            if (!$decryptedAesKey) {
                throw new Exception("RSA Decryption returned false");
            }
        } catch (Exception $e) {
            throw new Exception("Failed to decrypt AES Key: " . $e->getMessage());
        }
        try {
            $iv = base64_decode($initialVector);
            $flowDataEncrypted = base64_decode($encryptedFlowData);
            $aes = new AES('gcm');
            $aes->setKey($decryptedAesKey);
            $aes->setNonce($iv);
            $tagLength = 16;
            $ciphertext = substr($flowDataEncrypted, 0, -$tagLength);
            $tag = substr($flowDataEncrypted, -$tagLength);
            $aes->setTag($tag);
            $decryptedJSON = $aes->decrypt($ciphertext);
            if (!$decryptedJSON) {
                throw new Exception("AES-GCM Decryption failed");
            }
            return [
                'decoded_data' => json_decode($decryptedJSON, true),
                'aes_key' => $decryptedAesKey,
                'iv' => $iv,
                'is_health_check' => false
            ];
        } catch (Exception $e) {
            throw new Exception("Flow Data Decryption Error: " . $e->getMessage());
        }
    }
    public function encryptResponse($responseArray, $aesKey, $iv)
    {
        $invertedIv = ~$iv;
        $jsonResponse = json_encode($responseArray);
        $tag = "";
        $encryptedData = openssl_encrypt(
            $jsonResponse,
            'aes-128-gcm',
            $aesKey,
            OPENSSL_RAW_DATA,
            $invertedIv,
            $tag
        );
        return base64_encode($encryptedData . $tag);
    }
}