<?php

ini_set('display_errors', 0);
ini_set('log_errors', 1);

$rootDir = realpath(__DIR__ . '/../../..');
require_once $rootDir . '/vendor/autoload.php';
require_once $rootDir . '/src/config/Config.php';
require_once $rootDir . '/src/service/WhatsAppFlowCipher.php';
require_once $rootDir . '/src/utils/Logger.php';

$logger = new AppLogger('whatsapp_flow.log');

try {
    $keyPath = Config::get('WHATSAPP_PRIVATE_KEY_PATH');
    $cipher = new WhatsAppFlowCipher($keyPath);

    $input = file_get_contents('php://input');
    $request = $cipher->decryptRequest($input);

    if ($request === null) {
        http_response_code(200);
        echo "Health check OK";
        exit;
    }

    $data = $request['decoded_data'];
    $aesKey = $request['aes_key'];
    $iv = $request['iv'];

    $logger->logWithContext('info', "📥 Flow Request Received", $data);

    $action = $data['action'] ?? '';
    $screen = $data['screen'] ?? '';

    $responsePayload = new stdClass();


    if ($action === 'ping') {
        $responsePayload = [
            "data" => [
                "status" => "active"
            ]
        ];
    } elseif ($action === 'INIT') {
        $responsePayload = [
            "screen" => "SIGN_IN",
            "data" => new stdClass()
        ];
        $logger->info("🚀 Flow Initialized (INIT)");
    } elseif ($action === 'data_exchange') {
        if ($screen === 'SIGN_IN') {
            $email = $data['data']['email'] ?? '';
            $password = $data['data']['password'] ?? '';

            if (!empty($email) && !empty($password)) {
                $logger->info("✅ Login Success for: $email");
                $responsePayload = [
                    "screen" => "SIGN_IN",
                    "data" => [
                        "extension_message_response" => [
                            "params" => [
                                "flow_token" => $data['flow_token'],
                                "status" => "LOGIN_SUCCESS",
                                "email_user" => $email
                            ]
                        ]
                    ]
                ];
            } else {
                $responsePayload = [
                    "screen" => "SIGN_IN",
                    "data" => [
                        "error_message" => "Email dan Password wajib diisi."
                    ]
                ];
            }
        } elseif ($screen === 'SIGN_UP') {
            $responsePayload = [
                "screen" => "TERMS_AND_CONDITIONS",
                "data" => []
            ];
        }
    }

    $finalResponse = $cipher->encryptResponse($responsePayload, $aesKey, $iv);

    header('Content-Type: application/json');
    echo $finalResponse;

} catch (Exception $e) {
    if (isset($logger)) {
        $logger->critical("🔥 Flow Error: " . $e->getMessage());
    }
    http_response_code(500);
    echo "Server Error: " . $e->getMessage();
}