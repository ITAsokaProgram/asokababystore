<?php
require_once __DIR__ . '/../../auth/generate_token.php';
require_once __DIR__ . '/../../../aa_kon_sett.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }
    $inputJSON = file_get_contents("php://input");
    $data = json_decode($inputJSON, true);
    if (!isset($data['email']) || !isset($data['password'])) {
        throw new Exception('Email dan Password wajib diisi.');
    }
    $email = trim($data['email']);
    $password = $data['password'];
    if (!isset($conn)) {
        throw new Exception("Koneksi database gagal (Variable conn tidak ditemukan).");
    }
    $stmt = $conn->prepare("SELECT kode, nama, email, wilayah, password FROM user_supplier WHERE email = ?");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception('Email tidak ditemukan.');
    }
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $payload = [
            'kode' => $user['kode'],
            'nama' => $user['nama'],
            'email' => $user['email'],
            'role' => 'supplier' 
        ];
        $tokenData = generate_token_with_custom_expiration($payload, 86400); 
        setcookie('supplier_token', $tokenData['token'], [
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
            'token' => $tokenData['token'],
            'user' => [
                'nama' => $user['nama'],
                'email' => $user['email'],
                'wilayah' => $user['wilayah']
            ]
        ]);
    } else {
        throw new Exception('Password salah.');
    }
    $stmt->close();
    $conn->close();
} catch (Throwable $e) { 
    http_response_code(401); 
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>