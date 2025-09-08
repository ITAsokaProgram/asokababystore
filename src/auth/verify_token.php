<?php
include '../config/JWT/JWT.php';
include '../config/JWT/Key.php';
include '../config/JWT/config.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$jwt = null;
$headers = getallheaders(); // Ambil semua headers yang dikirim

// Cek apakah ada header Authorization
if (isset($headers['Authorization'])) {
    // Ambil token dari header Authorization
    $jwt = $headers['Authorization'];
    list($type, $jwt) = explode(' ', $jwt); // Memisahkan Bearer dan tokennya
    if (strcasecmp($type, 'Bearer') !== 0) {
        // Kalau tipe bukan Bearer, return error
        echo json_encode([
            'status' => 'error',
            'message' => 'Format Authorization tidak sesuai.'
        ]);
        exit;
    }
} else {
    // Jika Authorization header tidak ditemukan
    echo json_encode([
        'status' => 'error',
        'message' => 'Token tidak ditemukan, silakan login terlebih dahulu.'
    ]);
    exit;
}

$secretKey = JWT_SECRET_KEY; // Gunakan kunci yang sama saat membuat token

try {
    // Decode token untuk mengambil data yang ada di dalamnya
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

    // Ambil kode user yang ada di token
    $kode_user = $decoded->kode;

    // Koneksi ke database
    include "../../aa_kon_sett.php";

    // Ambil expired_token dari database berdasarkan kode_user
    $stmt = $conn->prepare("SELECT expired_token FROM user_account WHERE kode = ?");
    $stmt->bind_param("s", $kode_user);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($expired_token);
        $stmt->fetch();

        // Bandingkan expired_token dengan waktu sekarang
        $current_time = date('Y-m-d H:i:s'); // Waktu saat ini
        if ($expired_token < $current_time) {
            // Jika token sudah kadaluarsa
            echo json_encode([
                'status' => 'error',
                'message' => 'Token sudah kadaluarsa, silakan login ulang.'
            ]);
            exit;
        } else {
            // Token valid, lanjutkan ke resource yang diminta
            echo json_encode([
                'status' => 'success',
                'message' => 'Token valid!',
                'data' => $decoded
            ]);
        }
    } else {
        // Jika user tidak ditemukan
        echo json_encode([
            'status' => 'error',
            'message' => 'User tidak ditemukan.'
        ]);
        exit;
    }

    $stmt->close();

} catch (Exception $e) {
    // Jika token tidak valid
    echo json_encode([
        'status' => 'error',
        'message' => 'Token tidak valid!',
        'error' => $e->getMessage()
    ]);
    exit;
}
?>
