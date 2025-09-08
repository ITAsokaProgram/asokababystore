<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/JWT/JWT.php';
require_once __DIR__ . '/../config/JWT/Key.php';
require_once __DIR__ . '/../config/JWT/config.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
function verify_token($jwt)
{
    $secretKey = JWT_SECRET_KEY; // Gunakan kunci yang sama saat membuat token
    try {
        // Decode token untuk mengambil data yang ada di dalamnya
        $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
        return $decoded;
    } catch (ExpiredException $e) {
        // Token expired
        return [
            'status' => 'error',
            'message' => 'Token expired, silakan login kembali.'
        ];
    } catch (Exception $e) {
        // Token tidak valid
        return [
            'status' => 'error',
            'message' => 'Token tidak valid, silahkan login kembali.'
        ];
    }
}