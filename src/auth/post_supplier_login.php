<?php
require "generate_token.php";
require_once __DIR__ . '/../../aa_kon_sett.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
$inputJSON = file_get_contents("php://input");
$data = json_decode($inputJSON, true);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ((!isset($data['name']) && !isset($data['email'])) || !isset($data['pass'])) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
        exit;
    }
    $identifier = $data['name'] ?? $data['email']; 
    $password = $data['pass'];
    $stmt = $conn->prepare("SELECT kode, nama, email, password, wilayah FROM user_supplier WHERE nama = ? OR email = ?");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        if (password_verify($password, $user_data['password'])) {
            $tokenData = generate_token([
                'kode' => $user_data['kode'],
                'nama' => $user_data['nama'],
                'role' => 'supplier', 
                'wilayah' => $user_data['wilayah']
            ]);
            $token = $tokenData['token'];
            $created_at = date('Y-m-d H:i:s', $tokenData['issuedAt']);
            $expires_at = date('Y-m-d H:i:s', $tokenData['expiresAt']);
            $stmt_update = $conn->prepare("UPDATE user_supplier SET token = ?, expired_token = ?, created_token = ? WHERE kode = ?");
            $stmt_update->bind_param("sssi", $token, $expires_at, $created_at, $user_data['kode']);
            if ($stmt_update->execute()) {
                setcookie('supplier_token', $token, [
                    'expires' => $tokenData['expiresAt'],
                    'path' => '/',
                    'domain' => $_SERVER['HTTP_HOST'],
                    'secure' => true,
                    'httponly' => false,
                    'samesite' => 'Lax'
                ]);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Login Berhasil',
                    'token' => $token,
                    'user' => [
                        'nama' => $user_data['nama'],
                        'email' => $user_data['email']
                    ]
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal update token DB.']);
            }
            $stmt_update->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Password salah.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Akun supplier tidak ditemukan.']);
    }
    $stmt->close();
}
?>