<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Methods: PUT");


$verif = authenticate_request();

// Pastikan metode PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan']);
    exit;
}

// Ambil data JSON
$data = json_decode(file_get_contents("php://input"), true);
$id = isset($data['id']) ? (int) $data['id'] : 0;
$password = isset($data['pass']) ? trim($data['pass']) : null;
$confirmPass = isset($data['confirmPass']) ? trim($data['confirmPass']) : null;

// Checking Password Correct Or Not
if($password == null || $confirmPass == null){
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Masukan Password Dan Konfirmasi Password']);
    exit;
}

if($password != $confirmPass) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Password dan Konfirmasi tidak cocok']);
    exit;
}

// Hashing Password
$hashed_password = password_hash($confirmPass, PASSWORD_ARGON2I);

// Update Password
$stmt = $conn->prepare("UPDATE user_account SET password = ?  WHERE kode = ?");
$stmt->bind_param("si", $hashed_password,$id);
$stmt->execute();
$stmt->close();
$conn->close();
http_response_code(200);
echo json_encode(['status' => 'success', 'message' => 'Password berhasil dirubah']);
