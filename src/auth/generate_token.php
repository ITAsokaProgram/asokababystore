<?php   

require_once '../config/JWT/JWT.php';
require_once '../config/JWT/Key.php';


use Firebase\JWT\JWT;


require "../config/JWT/config.php";

function generate_token($payload) {
    $issuedAt = time();
    $expiresAt = $issuedAt + (7 * 24 * 60 * 60);
    $tokenPayload = array_merge($payload, ["iat" => $issuedAt, "exp" => $expiresAt]);
    $token = JWT::encode($tokenPayload, JWT_SECRET_KEY, JWT_ALGO);
    return [
        'token' => $token,
        'issuedAt' => $issuedAt,
        'expiresAt' => $expiresAt
    ];
}


function generate_token_with_custom_expiration($payload, $expirationTime = 3600) {
    $issuedAt = time();
    $expiresAt = $issuedAt + $expirationTime; // Waktu expire dalam detik (timestamp)
    $tokenPayload = array_merge($payload, ["iat" => $issuedAt, "exp" => $expiresAt]);
    $token = JWT::encode($tokenPayload, JWT_SECRET_KEY, JWT_ALGO);
    return [
        'token' => $token,
        'issuedAt' => $issuedAt,
        'expiresAt' => $expiresAt
    ];
}