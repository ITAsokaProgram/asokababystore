<?php

header('Content-Type: application/json');

// Hapus cookie token
setcookie('token', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => false,
    'samesite' => 'Strict'
]);

// Cek apakah cookie token tadi ada di request awal
if (isset($_COOKIE['token'])) {
    echo json_encode(['status' => 'success', 'message' => 'Logout berhasil']);
} else {
    echo json_encode(['status' => 'info', 'message' => 'Tidak ada sesi yang aktif di halaman ini']);
}