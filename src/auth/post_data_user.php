<?php
require "generate_token.php";
require_once __DIR__ . '/../../aa_kon_sett.php';
header("Access-Control-Allow-Origin: *"); // Optional, kalau dibutuhkan
header("Content-Type: application/json");

// Ambil body JSON dari request
$inputJSON = file_get_contents("php://input");
$data = json_decode($inputJSON, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($data['name']) || !isset($data['pass'])) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
        exit;
    }

    $inisial = $data['name'];
    $key = $data['pass'];


    // Gunakan parameterized query dengan MySQLi
    $stmt = $conn->prepare("SELECT kode, nama, hak, password,kd_store FROM user_account WHERE inisial = ?");
    $stmt->bind_param("s", $inisial);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($kode, $nama, $hak, $hashed_pass,$kd_store);
        $stmt->fetch();

        // Verifikasi password
        if (password_verify($key, $hashed_pass)) {
            $tokenData = generate_token(['kode' => $kode, 'nama' => $nama, 'username' => $inisial, 'role' => $hak , 'kd_store'=>$kd_store]);
            $token = $tokenData['token'];
            $created_at = date('Y-m-d H:i:s', $tokenData['issuedAt']);
            $expires_at = date('Y-m-d H:i:s', $tokenData['expiresAt']);
            $stmt = $conn->prepare("UPDATE user_account SET token = ?, expired_token = ? , created_token = ? WHERE kode = ?");
            $stmt->bind_param("sssi", $token, $expires_at, $created_at, $kode);
            $stmt->execute();

            setcookie('admin_token', $tokenData['token'], [
                'expires' => $tokenData['expiresAt'],
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'],
                'secure' => true, 
                'httponly' => false,
                'samesite' => 'Lax'
            ]);

            // Kirim response JSON
            echo json_encode([
                'status' => 'success',
                'message' => 'Token Berhasil Disimpan',
                'token' => $token,
                'created_at' => $created_at,
                'expires_at' => $expires_at
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Nama pengguna atau kata sandi salah.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Nama pengguna atau kata sandi salah.']);
    }

    $stmt->close();
}
