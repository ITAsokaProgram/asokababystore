<?php
header('Content-Type: application/json');
$domain = $_SERVER['HTTP_HOST'];
$baseDomain = str_replace('www.', '', $domain);
$options = [
    'expires' => time() - 3600, 
    'path' => '/',
    'secure' => true,
    'httponly' => false, 
    'samesite' => 'Lax'
];
setcookie('token', '', array_merge($options, ['domain' => $domain]));
setcookie('token', '', array_merge($options, ['domain' => $baseDomain]));
setcookie('token', '', array_merge($options, ['domain' => '.' . $baseDomain]));
unset($options['domain']);
setcookie('token', '', $options);
if (isset($_COOKIE['token'])) {
    echo json_encode(['status' => 'success', 'message' => 'Logout berhasil']);
} else {
    echo json_encode(['status' => 'success', 'message' => 'Sesi berhasil diakhiri.']);
}