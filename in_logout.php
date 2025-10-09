<?php
setcookie('token', '', [
    'expires' => time() - 3600, // waktu di masa lalu
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => false,
    'samesite' => 'Lax'
]);
http_response_code(200);
echo json_encode(['status' => 'success', 'message' => 'Berhasil logout']);
header("Location: /in_login");
exit;
?>
