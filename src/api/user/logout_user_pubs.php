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
setcookie('customer_token', '', array_merge($options, ['domain' => $domain]));
setcookie('customer_token', '', array_merge($options, ['domain' => $baseDomain]));
setcookie('customer_token', '', array_merge($options, ['domain' => '.' . $baseDomain]));
unset($options['domain']);
setcookie('customer_token', '', $options);
if (isset($_COOKIE['customer_token'])) {
    echo json_encode(['status' => 'success', 'message' => 'Logout berhasil']);
} else {
    echo json_encode(['status' => 'success', 'message' => 'Sesi berhasil diakhiri.']);
}