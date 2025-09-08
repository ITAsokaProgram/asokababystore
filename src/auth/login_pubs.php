<?php

require_once __DIR__ . '/middleware_login.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputJSON = file_get_contents("php://input");
    $data = json_decode($inputJSON, true);

    if (!isset($data['email']) || !isset($data['pass'])) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
        exit;
    }

    $email = $data['email'];
    $pass = $data['pass'];

    // Gunakan parameterized query dengan MySQLi
    $sql = "SELECT id_user,email, nama_lengkap, no_hp, password FROM user_asoka WHERE email = ?";
    $result = checkUser($sql, $pass, $email);
    
    echo json_encode(["User"=>$result]);
}
