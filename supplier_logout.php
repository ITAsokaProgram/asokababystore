<?php
header('Content-Type: application/json');

setcookie('supplier_token', '', [
    'expires' => time() - 3600, 
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => false,
    'samesite' => 'Lax'
]);

echo json_encode(['status' => 'success', 'message' => 'Berhasil logout supplier']);
exit;
?>